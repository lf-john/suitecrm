#!/bin/bash
# SuiteCRM v8 Post-Installation Setup Script
# This script performs additional configuration after SuiteCRM v8 installation

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

echo -e "${BLUE}====================================${NC}"
echo -e "${BLUE} SuiteCRM v8 Post-Installation Setup ${NC}"
echo -e "${BLUE}====================================${NC}"
echo ""

# Configure system settings
configure_system_settings() {
    print_status "Configuring system settings..."
    
    # Update system settings in database
    docker-compose exec -T db mysql -u suitecrm_user -psuitecrm_password suitecrm_db << 'EOF'
-- Update site URL
UPDATE config SET value = 'http://localhost:8080' WHERE category = 'site' AND name = 'site_url';

-- Configure upload settings
UPDATE config SET value = '30' WHERE category = 'system' AND name = 'upload_maxsize';

-- Configure session settings
UPDATE config SET value = 'redis' WHERE category = 'system' AND name = 'session_handler';

-- Configure cache settings
UPDATE config SET value = 'redis' WHERE category = 'system' AND name = 'external_cache_disabled';

-- Set timezone
UPDATE config SET value = 'UTC' WHERE category = 'system' AND name = 'default_timezone';

-- Configure search settings
UPDATE config SET value = 'true' WHERE category = 'search' AND name = 'elasticsearch_enabled';

-- Configure legacy support for migration
UPDATE config SET value = 'true' WHERE category = 'system' AND name = 'legacy_enabled';

COMMIT;
EOF
    
    print_status "System settings configured"
}

# Set up user preferences
configure_user_preferences() {
    print_status "Configuring user preferences..."
    
    # Configure admin user preferences
    docker-compose exec -T db mysql -u suitecrm_user -psuitecrm_password suitecrm_db << 'EOF'
-- Update admin user preferences for better UX
UPDATE user_preferences SET contents = '{"timezone":"UTC","dateformat":"m/d/Y","timeformat":"H:i","currency":"USD","default_locale_name_format":"s f l"}' 
WHERE assigned_user_id = (SELECT id FROM users WHERE user_name = 'admin' LIMIT 1) 
AND category = 'global';

COMMIT;
EOF
    
    print_status "User preferences configured"
}

# Configure security settings
configure_security() {
    print_status "Configuring security settings..."
    
    # Set up basic security configurations
    docker-compose exec suitecrm php bin/console cache:clear --env=prod
    
    # Configure CSRF protection and other security measures
    docker-compose exec -T db mysql -u suitecrm_user -psuitecrm_password suitecrm_db << 'EOF'
-- Configure security settings
UPDATE config SET value = 'true' WHERE category = 'security' AND name = 'verify_client_ip';
UPDATE config SET value = 'false' WHERE category = 'security' AND name = 'disable_export';
UPDATE config SET value = 'false' WHERE category = 'developer' AND name = 'developerMode';

COMMIT;
EOF
    
    print_status "Security settings configured"
}

# Configure email settings
configure_email() {
    print_status "Configuring email settings..."
    
    # Set up basic email configuration
    docker-compose exec -T db mysql -u suitecrm_user -psuitecrm_password suitecrm_db << 'EOF'
-- Configure email settings
UPDATE config SET value = 'smtp' WHERE category = 'email' AND name = 'email_default_client';
UPDATE config SET value = 'html' WHERE category = 'email' AND name = 'email_default_editor';
UPDATE config SET value = 'UTF-8' WHERE category = 'email' AND name = 'email_default_charset';

COMMIT;
EOF
    
    print_status "Email settings configured"
}

# Set up modules for migration
prepare_modules_for_migration() {
    print_status "Preparing modules for migration..."
    
    # Enable legacy API for migration
    docker-compose exec suitecrm php -r "
        \$config = [];
        if (file_exists('/var/www/html/config_override.php')) {
            include '/var/www/html/config_override.php';
        }
        \$config['legacy_enabled'] = true;
        \$config['legacy_url_routing'] = true;
        \$config['legacy_api_enabled'] = true;
        file_put_contents('/var/www/html/config_override.php', '<?php' . PHP_EOL . '\$sugar_config = ' . var_export(\$config, true) . ';');
    "
    
    # Create custom directories for migration
    docker-compose exec suitecrm mkdir -p /var/www/html/custom/modules
    docker-compose exec suitecrm mkdir -p /var/www/html/custom/Extension
    docker-compose exec suitecrm mkdir -p /var/www/html/custom/application
    docker-compose exec suitecrm mkdir -p /var/www/html/migration
    
    print_status "Modules prepared for migration"
}

# Configure cache and optimization
configure_cache_optimization() {
    print_status "Configuring cache and optimization..."
    
    # Clear all caches
    docker-compose exec suitecrm php bin/console cache:clear --env=prod
    
    # Warm up cache
    docker-compose exec suitecrm php bin/console cache:warmup --env=prod
    
    # Configure Redis cache
    docker-compose exec redis redis-cli CONFIG SET maxmemory-policy allkeys-lru
    
    print_status "Cache and optimization configured"
}

# Set up logging
configure_logging() {
    print_status "Configuring logging..."
    
    # Create log directories
    docker-compose exec suitecrm mkdir -p /var/www/html/logs
    docker-compose exec suitecrm mkdir -p /var/log/suitecrm
    
    # Set up log rotation configuration
    docker-compose exec --user root suitecrm bash -c "
        cat > /etc/logrotate.d/suitecrm << 'LOGEOF'
/var/www/html/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    sharedscripts
    postrotate
        /usr/bin/docker-compose exec suitecrm php bin/console cache:clear --env=prod > /dev/null 2>&1 || true
    endscript
}
LOGEOF
    "
    
    print_status "Logging configured"
}

# Create migration database schema
create_migration_schema() {
    print_status "Creating migration tracking schema..."
    
    # Create migration tracking tables
    docker-compose exec -T db mysql -u suitecrm_user -psuitecrm_password suitecrm_db << 'EOF'
-- Create migration log table
CREATE TABLE IF NOT EXISTS migration_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration_type VARCHAR(100) NOT NULL,
    source_table VARCHAR(100),
    target_table VARCHAR(100),
    record_count INT DEFAULT 0,
    status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_migration_type (migration_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create custom field mapping table
CREATE TABLE IF NOT EXISTS v7_custom_field_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    v7_module VARCHAR(100) NOT NULL,
    v7_field_name VARCHAR(100) NOT NULL,
    v8_module VARCHAR(100) NOT NULL,
    v8_field_name VARCHAR(100) NOT NULL,
    field_type VARCHAR(50),
    migration_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_mapping (v7_module, v7_field_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create relationship mapping table
CREATE TABLE IF NOT EXISTS v7_relationship_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    v7_relationship_name VARCHAR(100) NOT NULL,
    v8_relationship_name VARCHAR(100) NOT NULL,
    lhs_module VARCHAR(100),
    rhs_module VARCHAR(100),
    relationship_type VARCHAR(50),
    migration_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    migration_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_v7_relationship (v7_relationship_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
EOF
    
    print_status "Migration schema created"
}

# Final permissions and cleanup
final_setup() {
    print_status "Performing final setup..."
    
    # Set final permissions
    docker-compose exec --user root suitecrm chown -R suitecrm:www-data /var/www/html
    docker-compose exec --user root suitecrm find /var/www/html -type f -exec chmod 644 {} \;
    docker-compose exec --user root suitecrm find /var/www/html -type d -exec chmod 755 {} \;
    docker-compose exec --user root suitecrm chmod -R 775 /var/www/html/cache /var/www/html/tmp /var/www/html/upload /var/www/html/logs
    
    # Clear and rebuild cache one final time
    docker-compose exec suitecrm php bin/console cache:clear --env=prod
    
    print_status "Final setup completed"
}

# Main setup process
main() {
    configure_system_settings
    configure_user_preferences
    configure_security
    configure_email
    prepare_modules_for_migration
    configure_cache_optimization
    configure_logging
    create_migration_schema
    final_setup
    
    echo ""
    echo -e "${GREEN}====================================${NC}"
    echo -e "${GREEN} Post-Installation Setup Complete! ${NC}"
    echo -e "${GREEN}====================================${NC}"
    echo ""
    echo -e "${BLUE}✅ SuiteCRM v8 is now ready for migration!${NC}"
    echo ""
    echo -e "${BLUE}Next Steps:${NC}"
    echo "  1. Verify installation: ./scripts/verify-installation.sh"
    echo "  2. Access SuiteCRM: http://localhost:8080"
    echo "  3. Login with admin/admin123"
    echo "  4. Run migration scripts to import v7 data"
    echo ""
    echo -e "${YELLOW}Migration Preparation:${NC}"
    echo "  • Migration tracking tables created"
    echo "  • Custom directories prepared"
    echo "  • Legacy API support enabled"
    echo "  • Cache and optimization configured"
    echo ""
}

# Run main function
main "$@"
