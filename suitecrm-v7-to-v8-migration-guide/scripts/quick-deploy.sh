#!/bin/bash

# Quick Deploy Script for SuiteCRM V8 Theme Extension
# Usage: ./quick-deploy.sh <extension-name> [container-name]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check arguments
if [ $# -lt 1 ]; then
    print_error "Usage: $0 <extension-name> [container-name]"
    print_error "Example: $0 my-theme-ext suitecrm_app"
    exit 1
fi

EXTENSION_NAME="$1"
CONTAINER_NAME="${2:-}"

# Set command prefix for Docker if container specified
CMD_PREFIX=""
if [ -n "$CONTAINER_NAME" ]; then
    CMD_PREFIX="docker exec $CONTAINER_NAME"
    print_status "Using Docker container: $CONTAINER_NAME"
else
    print_status "Running locally (no container specified)"
fi

print_status "Quick deploying extension: $EXTENSION_NAME"

# Function to run command with optional Docker prefix
run_cmd() {
    if [ -n "$CMD_PREFIX" ]; then
        $CMD_PREFIX $@
    else
        $@
    fi
}

# Check if extension exists
print_status "Checking extension exists..."
if run_cmd test -d "/var/www/html/extensions/$EXTENSION_NAME"; then
    print_success "Extension directory found"
else
    print_error "Extension not found: $EXTENSION_NAME"
    print_error "Available extensions:"
    run_cmd ls -1 /var/www/html/extensions/
    exit 1
fi

# Clean previous build
print_status "Cleaning previous build artifacts..."
run_cmd rm -rf "/var/www/html/public/extensions/$EXTENSION_NAME" 2>/dev/null || true
print_success "Build artifacts cleaned"

# Build extension
print_status "Building extension..."
if run_cmd npm run "build-dev:$EXTENSION_NAME"; then
    print_success "Extension built successfully"
else
    print_error "Build failed"
    exit 1
fi

# Verify build output
print_status "Verifying build output..."
if run_cmd test -f "/var/www/html/public/extensions/$EXTENSION_NAME/remoteEntry.js"; then
    print_success "remoteEntry.js created"
else
    print_error "remoteEntry.js not found after build"
    exit 1
fi

# Check HTTP accessibility (if not in container)
if [ -z "$CONTAINER_NAME" ]; then
    print_status "Checking HTTP accessibility..."
    if curl -f -s "http://localhost:8080/extensions/$EXTENSION_NAME/remoteEntry.js" > /dev/null; then
        print_success "Extension accessible via HTTP"
    else
        print_warning "Extension may not be accessible via HTTP"
    fi
fi

# Clear cache
print_status "Clearing SuiteCRM cache..."
if run_cmd php bin/console cache:clear; then
    print_success "Cache cleared"
else
    print_warning "Cache clear failed (this might be okay)"
fi

# Verify extension is enabled
print_status "Checking extension status..."
if run_cmd grep -q "enabled.*true" "/var/www/html/extensions/$EXTENSION_NAME/config/extension.php"; then
    print_success "Extension is enabled"
else
    print_warning "Extension appears to be disabled"
    print_status "Enabling extension..."
    run_cmd sed -i "s/'enabled' => false/'enabled' => true/" "/var/www/html/extensions/$EXTENSION_NAME/config/extension.php"
    print_success "Extension enabled"
fi

# Final verification
print_status "Running final checks..."

# Check build files
BUILD_FILES=("remoteEntry.js" "styles.css" "main.js")
for file in "${BUILD_FILES[@]}"; do
    if run_cmd test -f "/var/www/html/public/extensions/$EXTENSION_NAME/$file"; then
        SIZE=$(run_cmd stat -f%z "/var/www/html/public/extensions/$EXTENSION_NAME/$file" 2>/dev/null || run_cmd stat -c%s "/var/www/html/public/extensions/$EXTENSION_NAME/$file" 2>/dev/null || echo "unknown")
        print_success "$file exists (${SIZE} bytes)"
    else
        print_warning "$file missing"
    fi
done

# Check for common issues
print_status "Checking for common issues..."

# Check if styles contain theme colors (basic check)
if run_cmd test -f "/var/www/html/public/extensions/$EXTENSION_NAME/styles.css"; then
    COLOR_COUNT=$(run_cmd grep -o '#[0-9A-Fa-f]\{6\}' "/var/www/html/public/extensions/$EXTENSION_NAME/styles.css" | wc -l || echo "0")
    if [ "$COLOR_COUNT" -gt 0 ]; then
        print_success "Styles contain $COLOR_COUNT color definitions"
    else
        print_warning "No color definitions found in styles"
    fi
fi

# Test SuiteCRM accessibility (if not in container)
if [ -z "$CONTAINER_NAME" ]; then
    print_status "Testing SuiteCRM accessibility..."
    if curl -f -s "http://localhost:8080" > /dev/null; then
        print_success "SuiteCRM is accessible"
    else
        print_error "SuiteCRM is not accessible at http://localhost:8080"
    fi
fi

echo
print_success "🚀 Quick deployment completed!"
echo
print_status "Summary:"
echo "  📦 Extension: $EXTENSION_NAME"
echo "  🏗️  Build: Successful"
echo "  📁 Files: $(run_cmd ls -1 "/var/www/html/public/extensions/$EXTENSION_NAME" 2>/dev/null | wc -l || echo "0") files deployed"
echo "  ⚡ Status: Enabled"
echo
print_status "Next steps:"
echo "  1. Open http://localhost:8080 in your browser"
echo "  2. Check for any console errors"
echo "  3. Verify theme is applied correctly"
echo "  4. Test functionality across different pages"
echo
if [ -n "$CONTAINER_NAME" ]; then
    print_status "Docker commands:"
    echo "  View logs: docker logs $CONTAINER_NAME"
    echo "  Shell access: docker exec -it $CONTAINER_NAME bash"
fi

print_success "✅ Deployment complete!"