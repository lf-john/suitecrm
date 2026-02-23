#!/bin/bash
# SuiteCRM v8 Installation Verification Script
# This script verifies that SuiteCRM v8 is properly installed and configured

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Counters
PASSED=0
FAILED=0
WARNINGS=0

print_header() {
    echo -e "${BLUE}=================================${NC}"
    echo -e "${BLUE} SuiteCRM v8 Installation Check ${NC}"
    echo -e "${BLUE}=================================${NC}"
    echo ""
}

print_status() {
    echo -e "${GREEN}[PASS]${NC} $1"
    ((PASSED++))
}

print_error() {
    echo -e "${RED}[FAIL]${NC} $1"
    ((FAILED++))
}

print_warning() {
    echo -e "${YELLOW}[WARN]${NC} $1"
    ((WARNINGS++))
}

print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

# Check Docker services
check_docker_services() {
    print_info "Checking Docker services..."
    
    # Check if containers are running
    if docker-compose ps | grep -q "Up"; then
        print_status "Docker containers are running"
    else
        print_error "Docker containers are not running"
        return 1
    fi
    
    # Check individual services
    local services=("suitecrm_app" "suitecrm_nginx" "suitecrm_mysql" "suitecrm_redis")
    for service in "${services[@]}"; do
        if docker-compose ps | grep "$service" | grep -q "Up"; then
            print_status "Service $service is running"
        else
            print_error "Service $service is not running"
        fi
    done
}

# Check database connectivity
check_database() {
    print_info "Checking database connectivity..."
    
    if docker-compose exec -T db mysqladmin ping -h localhost -u suitecrm_user -psuitecrm_password --silent 2>/dev/null; then
        print_status "Database is accessible"
        
        # Check if SuiteCRM tables exist
        local table_count=$(docker-compose exec -T db mysql -u suitecrm_user -psuitecrm_password suitecrm_db -e "SHOW TABLES;" 2>/dev/null | wc -l)
        if [ "$table_count" -gt 10 ]; then
            print_status "SuiteCRM database tables exist ($table_count tables)"
        else
            print_warning "SuiteCRM database tables not found or incomplete"
        fi
    else
        print_error "Cannot connect to database"
    fi
}

# Check Redis connectivity
check_redis() {
    print_info "Checking Redis connectivity..."
    
    if docker-compose exec -T redis redis-cli ping 2>/dev/null | grep -q "PONG"; then
        print_status "Redis is accessible"
    else
        print_error "Cannot connect to Redis"
    fi
}

# Check web server
check_web_server() {
    print_info "Checking web server..."
    
    # Check HTTP response
    local http_status=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 2>/dev/null || echo "000")
    if [ "$http_status" = "200" ] || [ "$http_status" = "302" ]; then
        print_status "Web server is responding (HTTP $http_status)"
    else
        print_error "Web server not responding (HTTP $http_status)"
    fi
    
    # Check HTTPS response
    local https_status=$(curl -s -k -o /dev/null -w "%{http_code}" https://localhost:8443 2>/dev/null || echo "000")
    if [ "$https_status" = "200" ] || [ "$https_status" = "302" ]; then
        print_status "HTTPS is working (HTTP $https_status)"
    else
        print_warning "HTTPS not responding (HTTP $https_status)"
    fi
}

# Check SuiteCRM files
check_suitecrm_files() {
    print_info "Checking SuiteCRM files..."
    
    # Check if SuiteCRM directory exists
    if [ -d "./suitecrm" ]; then
        print_status "SuiteCRM directory exists"
        
        # Check key files
        local key_files=("index.php" "composer.json" "package.json" "bin/console")
        for file in "${key_files[@]}"; do
            if [ -f "./suitecrm/$file" ]; then
                print_status "Found $file"
            else
                print_error "Missing $file"
            fi
        done
        
        # Check vendor directory
        if [ -d "./suitecrm/vendor" ]; then
            print_status "Composer vendor directory exists"
        else
            print_warning "Composer vendor directory not found"
        fi
        
        # Check node_modules
        if [ -d "./suitecrm/node_modules" ]; then
            print_status "Node modules directory exists"
        else
            print_warning "Node modules directory not found"
        fi
        
        # Check dist directory (built assets)
        if [ -d "./suitecrm/dist" ]; then
            print_status "Built assets directory exists"
        else
            print_warning "Built assets directory not found"
        fi
        
    else
        print_error "SuiteCRM directory not found"
    fi
}

# Check file permissions
check_permissions() {
    print_info "Checking file permissions..."
    
    # Check writable directories
    local writable_dirs=("cache" "tmp" "upload" "logs")
    for dir in "${writable_dirs[@]}"; do
        if docker-compose exec -T suitecrm test -w "/var/www/html/$dir" 2>/dev/null; then
            print_status "Directory $dir is writable"
        else
            print_warning "Directory $dir may not be writable"
        fi
    done
}

# Check SuiteCRM installation
check_suitecrm_installation() {
    print_info "Checking SuiteCRM installation status..."
    
    # Try to access the console
    if docker-compose exec -T suitecrm php bin/console --version 2>/dev/null | grep -q "SuiteCRM"; then
        print_status "SuiteCRM console is working"
    else
        print_warning "SuiteCRM console not accessible"
    fi
    
    # Check if installation is complete
    if docker-compose exec -T suitecrm test -f "/var/www/html/config.php" 2>/dev/null; then
        print_status "SuiteCRM config.php exists"
    else
        print_warning "SuiteCRM config.php not found"
    fi
}

# Check admin user
check_admin_user() {
    print_info "Checking admin user..."
    
    # Try to find admin user in database
    local admin_count=$(docker-compose exec -T db mysql -u suitecrm_user -psuitecrm_password suitecrm_db -e "SELECT COUNT(*) FROM users WHERE user_name='admin';" 2>/dev/null | tail -n 1 || echo "0")
    if [ "$admin_count" -gt 0 ]; then
        print_status "Admin user exists in database"
    else
        print_warning "Admin user not found in database"
    fi
}

# Check logs for errors
check_logs() {
    print_info "Checking for critical errors in logs..."
    
    # Check SuiteCRM logs
    if [ -d "./logs/suitecrm" ]; then
        local error_count=$(find ./logs/suitecrm -name "*.log" -exec grep -i "error\|fatal\|exception" {} \; 2>/dev/null | wc -l)
        if [ "$error_count" -eq 0 ]; then
            print_status "No critical errors in SuiteCRM logs"
        else
            print_warning "Found $error_count potential errors in SuiteCRM logs"
        fi
    else
        print_warning "SuiteCRM log directory not found"
    fi
}

# Print summary
print_summary() {
    echo ""
    echo -e "${BLUE}=================================${NC}"
    echo -e "${BLUE}        Verification Summary      ${NC}"
    echo -e "${BLUE}=================================${NC}"
    echo ""
    echo -e "Passed: ${GREEN}$PASSED${NC}"
    echo -e "Failed: ${RED}$FAILED${NC}"
    echo -e "Warnings: ${YELLOW}$WARNINGS${NC}"
    echo ""
    
    if [ $FAILED -eq 0 ]; then
        echo -e "${GREEN}✅ SuiteCRM v8 installation appears to be working!${NC}"
        echo ""
        echo -e "${BLUE}Access URLs:${NC}"
        echo "  • SuiteCRM: http://localhost:8080"
        echo "  • HTTPS: https://localhost:8443"
        echo "  • PHPMyAdmin: http://localhost:8081"
        echo "  • Redis Commander: http://localhost:8082"
        echo ""
        echo -e "${BLUE}Default Credentials:${NC}"
        echo "  • Username: admin"
        echo "  • Password: admin123"
        echo ""
        if [ $WARNINGS -gt 0 ]; then
            echo -e "${YELLOW}⚠️  Please review the warnings above${NC}"
        fi
    else
        echo -e "${RED}❌ Installation verification failed with $FAILED errors${NC}"
        echo -e "${YELLOW}Please check the failed items above and run the installation again${NC}"
        return 1
    fi
}

# Main verification process
main() {
    print_header
    
    check_docker_services
    check_database
    check_redis
    check_web_server
    check_suitecrm_files
    check_permissions
    check_suitecrm_installation
    check_admin_user
    check_logs
    
    print_summary
}

# Run main function
main "$@"
