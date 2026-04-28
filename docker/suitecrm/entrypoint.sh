#!/bin/bash
set -e

echo "Starting SuiteCRM v8.6.1 container..."

# Create necessary directories with proper ownership
mkdir -p /var/www/html/cache/dev /var/www/html/cache/prod /var/www/html/upload /var/www/html/tmp /var/www/html/logs/dev /var/www/html/logs/prod /var/www/html/public/extensions /var/www/html/public/bundles
chown -R www-data:www-data /var/www/html/cache /var/www/html/upload /var/www/html/tmp /var/www/html/logs /var/www/html/custom /var/www/html/public/extensions /var/www/html/public/bundles
chmod -R 775 /var/www/html/cache /var/www/html/upload /var/www/html/tmp /var/www/html/logs /var/www/html/custom /var/www/html/public/extensions /var/www/html/public/bundles

# Fix public/ directory so Symfony cache warmup can manage extensions/bundles subdirectories
chgrp www-data /var/www/html/public/
chmod 775 /var/www/html/public/

# Fix legacy writable directories
chown -R www-data:www-data /var/www/html/public/legacy/custom/ /var/www/html/public/legacy/cache/ /var/www/html/public/legacy/upload/ 2>/dev/null || true
chmod -R 775 /var/www/html/public/legacy/custom/ /var/www/html/public/legacy/cache/ /var/www/html/public/legacy/upload/ 2>/dev/null || true

echo "Directories created and ownership set"

# Get database credentials from environment or use defaults
DB_HOST="${DB_HOST:-db}"
DB_USER="${DB_USER:-suitecrm_user}"
DB_PASSWORD="${DB_PASSWORD:-suitecrm_password}"
DB_NAME="${DB_NAME:-suitecrm_db}"

# Extract from DATABASE_URL if provided
if [ -n "$DATABASE_URL" ]; then
    # Parse DATABASE_URL: mysql://user:password@host:port/database
    DB_USER=$(echo $DATABASE_URL | sed 's/.*:\/\/\([^:]*\):.*/\1/')
    DB_PASSWORD=$(echo $DATABASE_URL | sed 's/.*:\/\/[^:]*:\([^@]*\)@.*/\1/')
    DB_HOST=$(echo $DATABASE_URL | sed 's/.*@\([^:]*\):.*/\1/')
    DB_NAME=$(echo $DATABASE_URL | sed 's/.*\/\(.*\)/\1/')
fi

echo "Database credentials: $DB_USER@$DB_HOST/$DB_NAME"

# Create log directory
mkdir -p /var/log/suitecrm
chmod 755 /var/log/suitecrm

echo "Waiting for database to be ready..."

DB_RETRY_COUNT=0
DB_MAX_RETRIES=30
DB_READY=false

while [ $DB_RETRY_COUNT -lt $DB_MAX_RETRIES ]; do
    if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASSWORD" --connect-timeout=10 --disable-ssl -e "SELECT 1;" "$DB_NAME" >/dev/null 2>&1; then
        echo "Database is ready!"
        DB_READY=true
        break
    fi

    DB_RETRY_COUNT=$((DB_RETRY_COUNT + 1))
    echo "Database not ready, waiting... (attempt $DB_RETRY_COUNT/$DB_MAX_RETRIES)"

    if [ $DB_RETRY_COUNT -gt 25 ]; then
        mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASSWORD" --connect-timeout=5 --disable-ssl -e "SELECT 1;" "$DB_NAME" 2>&1 || true
    fi

    sleep 3
done

if [ "$DB_READY" = false ]; then
    echo "ERROR: Database connection failed after $DB_MAX_RETRIES attempts"
    exit 1
fi

# Check if SuiteCRM v8 is properly installed
if [ ! -f "/var/www/html/composer.json" ] || [ ! -f "/var/www/html/VERSION" ]; then
    echo "SuiteCRM v8 not found. Downloading SuiteCRM v8.6.1..."

    # Create temp directory for download
    TEMP_DIR=$(mktemp -d)
    cd $TEMP_DIR

    # Download SuiteCRM v8.6.1 from the CORRECT URL
    echo "Downloading SuiteCRM v8.6.1 from GitHub releases..."
    wget -O suitecrm-8.6.1.zip "https://github.com/SuiteCRM/SuiteCRM-Core/releases/download/v8.6.1/SuiteCRM-8.6.1.zip"

    if [ $? -eq 0 ]; then
        echo "Extracting SuiteCRM v8.6.1..."
        unzip -q suitecrm-8.6.1.zip

        # Check if key SuiteCRM v8 files exist in current directory
        if [ -f "./composer.json" ] && [ -f "./angular.json" ]; then
            echo "SuiteCRM v8.6.1 files found in current directory"
            echo "Installing SuiteCRM v8.6.1 files..."

            # Copy all files except the zip to web root
            find . -mindepth 1 -maxdepth 1 ! -name "suitecrm-8.6.1.zip" -exec cp -r {} /var/www/html/ \;

            # Copy hidden files
            find . -mindepth 1 -maxdepth 1 -name ".*" -exec cp -r {} /var/www/html/ \; 2>/dev/null || true

        else
            echo "ERROR: Required SuiteCRM v8 files not found after extraction"
            ls -la
            exit 1
        fi

        # Cleanup
        cd /var/www/html
        rm -rf $TEMP_DIR

        # Verify this is v8 by checking for key v8 files
        if [ -f "/var/www/html/bin/console" ] && [ -f "/var/www/html/angular.json" ]; then
            echo "SuiteCRM v8.6.1 installed successfully!"
        else
            echo "ERROR: Installation verification failed - missing key v8 files"
            echo "Current files:"
            ls -la /var/www/html/
            exit 1
        fi
    else
        echo "ERROR: Failed to download SuiteCRM v8.6.1"
        exit 1
    fi
else
    echo "SuiteCRM v8 found, checking installation status..."
fi

# Set proper permissions
echo "Setting file permissions..."
find /var/www/html -type f -exec chmod 644 {} \; 2>/dev/null || true
find /var/www/html -type d -exec chmod 755 {} \; 2>/dev/null || true

# Ensure critical directories have proper permissions
for dir in cache upload tmp logs custom public/extensions public/bundles public/legacy/custom public/legacy/cache public/legacy/upload; do
    if [ ! -d "/var/www/html/$dir" ]; then
        mkdir -p "/var/www/html/$dir"
    fi

    # Create cache subdirectories for Symfony
    if [ "$dir" = "cache" ]; then
        mkdir -p "/var/www/html/$dir/dev" "/var/www/html/$dir/prod" 2>/dev/null || true
    fi

    # Create logs subdirectories
    if [ "$dir" = "logs" ]; then
        mkdir -p "/var/www/html/$dir/dev" "/var/www/html/$dir/prod" 2>/dev/null || true
    fi

    chown -R www-data:www-data "/var/www/html/$dir" 2>/dev/null || true
    chmod -R 775 "/var/www/html/$dir" 2>/dev/null || {
        chmod -R 777 "/var/www/html/$dir" 2>/dev/null || true
    }
    echo "Set permissions for $dir directory"
done

# Install Composer dependencies if needed
if [ -f "/var/www/html/composer.json" ] && [ ! -d "/var/www/html/vendor" ]; then
    echo "Installing Composer dependencies..."
    cd /var/www/html
    composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || {
        echo "Trying composer install with platform requirements ignored..."
        composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs 2>/dev/null || true
    }
fi

# Build frontend assets if needed
if [ -f "/var/www/html/package.json" ]; then
    echo "Building frontend assets..."
    cd /var/www/html

    # Install npm dependencies
    echo "Installing npm dependencies..."
    npm ci 2>/dev/null || npm install 2>/dev/null || {
        echo "Trying npm install with legacy peer deps..."
        npm install --legacy-peer-deps 2>/dev/null || true
    }

    # Build frontend assets if not already built
    if [ ! -d "/var/www/html/public/dist" ] || [ -z "$(ls -A /var/www/html/public/dist 2>/dev/null)" ]; then
        echo "Building Angular frontend..."

        # Install Angular CLI if not present
        if ! command -v ng >/dev/null 2>&1; then
            npm install -g @angular/cli@latest 2>/dev/null || true
        fi

        # Build projects individually
        ng build common 2>/dev/null || true
        ng build core 2>/dev/null || true
        ng build defaultExt 2>/dev/null || true
        ng build shell 2>/dev/null || true

        # Main build
        ng build --configuration=production 2>/dev/null || ng build --prod 2>/dev/null || {
            echo "Angular build failed, trying npm scripts..."
            npm run build:prod 2>/dev/null || npm run build 2>/dev/null || true
        }
    else
        echo "Frontend assets already built"
    fi
fi

# Check installation state
if [ -f "/var/www/html/config.php" ]; then
    echo "SuiteCRM appears to be installed"

    # Run maintenance tasks as www-data to avoid root-owned cache files
    if [ -f "/var/www/html/bin/console" ]; then
        echo "Running maintenance tasks..."
        su -s /bin/bash www-data -c 'php /var/www/html/bin/console cache:clear --env=prod' 2>/dev/null || true
    fi

    # Final permission fix to catch anything created as root during startup
    chown -R www-data:www-data /var/www/html/cache/ /var/www/html/public/legacy/cache/ 2>/dev/null || true
    chmod -R 775 /var/www/html/cache/ /var/www/html/public/legacy/cache/ 2>/dev/null || true
else
    echo "SuiteCRM ready for web installation at http://localhost:8080/install.php"
fi

# Create ready marker
touch /var/www/html/.suitecrm-ready

echo "SuiteCRM v8.6.1 container initialization complete."

# Set umask so PHP-FPM creates cache directories readable by nginx (www-data group)
umask 0002

# Start PHP-FPM
exec "$@"
