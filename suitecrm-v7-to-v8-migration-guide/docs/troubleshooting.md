# SuiteCRM V7 to V8 Theme Migration - Troubleshooting Guide

This guide covers common issues encountered during SuiteCRM theme migration and their solutions.

## 🚨 Common Issues

### Issue 1: Blank Page with Console Error

**Symptoms:**
- SuiteCRM shows a blank page
- Browser console shows error: `Failed to fetch dynamically imported module: remoteEntry.js`
- Error: `Failed to load resource: the server responded with a status of 404 (Not Found)`

**Root Cause:**
The extension's `remoteEntry.js` file is missing or not accessible.

**Solution:**

1. **Check if remoteEntry.js exists:**
   ```bash
   # Check if file exists in public directory
   ls -la /var/www/html/public/extensions/your-extension/remoteEntry.js

   # Or via Docker
   docker exec your-container ls -la /var/www/html/public/extensions/your-extension/remoteEntry.js
   ```

2. **Verify HTTP accessibility:**
   ```bash
   curl -I http://localhost:8080/extensions/your-extension/remoteEntry.js
   ```

3. **Rebuild extension if missing:**
   ```bash
   npm run build-dev:your-extension
   ```

4. **Check build configuration:**
   - Ensure `angular.json` has correct `outputPath` for dev builds
   - Should be: `"outputPath": "public/extensions/your-extension"`

**Complete Fix:**
```bash
# 1. Temporarily disable extension
sed -i "s/'enabled' => true/'enabled' => false/" extensions/your-extension/config/extension.php

# 2. Clear cache
php bin/console cache:clear

# 3. Rebuild extension
npm run build-dev:your-extension

# 4. Verify deployment
ls -la public/extensions/your-extension/remoteEntry.js

# 5. Re-enable extension
sed -i "s/'enabled' => false/'enabled' => true/" extensions/your-extension/config/extension.php

# 6. Clear cache again
php bin/console cache:clear
```

---

### Issue 2: Styles Not Applied

**Symptoms:**
- SuiteCRM loads but theme colors/styles are not visible
- Default blue theme is still showing instead of custom colors

**Root Cause:**
Theme styles are not being loaded or overridden properly.

**Diagnosis:**
```bash
# Check if your CSS is in the compiled stylesheet
curl -s "http://localhost:8080/dist/styles.css" | grep -i "your-primary-color-hex"

# Example: Look for #125EAD (Logical Front blue)
curl -s "http://localhost:8080/dist/styles.css" | grep -i "125EAD"
```

**Solutions:**

**Option A: Extension-based styling**
1. Ensure extension is enabled and built
2. Check styles.scss imports your theme assets:
   ```scss
   @import './theme-assets/main';
   ```

**Option B: Direct CSS override (immediate fix)**
```bash
# Backup original styles
cp /var/www/html/public/dist/styles.css /var/www/html/public/dist/styles.css.backup

# Append your theme to main stylesheet
cat >> /var/www/html/public/dist/styles.css << 'EOF'

/* Custom Theme Override */
:root {
  --primary-color: #125EAD !important;
  --secondary-color: #4BB74E !important;
  --text-color: #323130 !important;
}

body {
  background: linear-gradient(135deg, #125EAD 0%, #4BB74E 100%) !important;
}

.p-button-primary {
  background-color: #125EAD !important;
  border-color: #125EAD !important;
}
EOF
```

---

### Issue 3: Extension Not Loading

**Symptoms:**
- Extension appears in config but doesn't load
- No console errors but theme not applied

**Diagnosis:**
```bash
# Check extension configuration
cat extensions/your-extension/config/extension.php

# Verify extension is enabled
grep -i "enabled.*true" extensions/your-extension/config/extension.php

# Check cache status
php bin/console cache:clear
```

**Solutions:**

1. **Verify extension.php syntax:**
   ```php
   <?php
   use Symfony\Component\DependencyInjection\Container;

   if (!isset($container)) {
       return;
   }

   $extensions = $container->getParameter('extensions') ?? [];

   $extensions['your-extension'] = [
       'remoteEntry' => '../extensions/your-extension/remoteEntry.js',
       'remoteName' => 'your-extension',
       'enabled' => true,  // Must be true
       'extension_name' => 'Your Theme Name',
       // ... other config
   ];

   $container->setParameter('extensions', $extensions);
   ```

2. **Clear all caches:**
   ```bash
   php bin/console cache:clear
   rm -rf var/cache/* 2>/dev/null || true
   rm -rf tmp/* 2>/dev/null || true
   ```

3. **Check file permissions:**
   ```bash
   # Ensure files are readable
   chmod -R 755 extensions/your-extension/
   chmod -R 755 public/extensions/your-extension/
   ```

---

### Issue 4: Build Failures

**Symptoms:**
- `npm run build-dev:your-extension` fails
- TypeScript or Angular compilation errors
- Webpack build errors

**Common Build Errors:**

**Error: `Cannot find module 'your-extension'`**
```bash
# Check angular.json has your extension configured
grep -A 20 "your-extension" angular.json

# Ensure package.json has build script
grep "build-dev:your-extension" package.json
```

**Error: `Module not found: Error: Can't resolve './theme-assets/main'`**
```bash
# Check if theme assets exist
ls -la extensions/your-extension/app/src/theme-assets/main.scss

# Fix import path in styles.scss
sed -i "s|@import './theme-assets/main'|@import './theme-assets/main.scss'|" \
  extensions/your-extension/app/src/styles.scss
```

**Error: Webpack Module Federation issues**
```bash
# Check webpack.config.js
cat extensions/your-extension/app/webpack.config.js

# Ensure name matches extension name
grep "name: 'your-extension'" extensions/your-extension/app/webpack.config.js
```

**Solution: Complete rebuild**
```bash
# Clean build artifacts
rm -rf extensions/your-extension/public/
rm -rf public/extensions/your-extension/

# Reinstall dependencies
npm install

# Rebuild everything
npm run build-dev

# Rebuild specific extension
npm run build-dev:your-extension
```

---

### Issue 5: Docker-specific Issues

**Symptoms:**
- Commands work locally but fail in Docker
- File permission errors
- Path resolution issues

**Solutions:**

1. **File permissions in Docker:**
   ```bash
   # Fix ownership
   docker exec your-container chown -R www-data:www-data /var/www/html/extensions/
   docker exec your-container chown -R www-data:www-data /var/www/html/public/extensions/

   # Fix permissions
   docker exec your-container chmod -R 755 /var/www/html/extensions/
   docker exec your-container chmod -R 755 /var/www/html/public/extensions/
   ```

2. **Copy files to Docker:**
   ```bash
   # Copy theme assets to container
   docker cp ./extracted-assets/ your-container:/var/www/html/extensions/your-extension/app/src/theme-assets/
   ```

3. **Execute commands in Docker:**
   ```bash
   # All commands should be prefixed with docker exec
   docker exec your-container npm run build-dev:your-extension
   docker exec your-container php bin/console cache:clear
   ```

---

### Issue 6: Angular Version Conflicts

**Symptoms:**
- Build warnings about Angular versions
- Module federation compatibility issues
- TypeScript compilation errors

**Solutions:**

1. **Check Angular version compatibility:**
   ```bash
   # Check current Angular version
   ng version

   # SuiteCRM 8.6.1 uses Angular 16.x
   # Ensure compatibility in package.json
   ```

2. **Update webpack config for correct Angular version:**
   ```javascript
   // In webpack.config.js, update shared dependencies
   shared: {
     '@angular/core': {
       singleton: true,
       requiredVersion: '^16.1.1'  // Match SuiteCRM version
     },
     // ... other shared modules
   }
   ```

---

### Issue 7: SCSS Compilation Issues

**Symptoms:**
- SCSS variables not resolving
- Import errors for theme assets
- CSS not generating correctly

**Solutions:**

1. **Check SCSS import paths:**
   ```scss
   // In styles.scss - use relative paths
   @import './theme-assets/variables';
   @import './theme-assets/components';
   @import './theme-assets/typography';
   ```

2. **Verify SCSS file structure:**
   ```bash
   # Check all required files exist
   ls -la extensions/your-extension/app/src/theme-assets/
   # Should contain: _variables.scss, _components.scss, _typography.scss, main.scss
   ```

3. **Check for SCSS syntax errors:**
   ```bash
   # Validate SCSS syntax
   npx sass extensions/your-extension/app/src/styles.scss --check
   ```

---

## 🔧 Diagnostic Commands

### Quick Health Check
```bash
#!/bin/bash
# Theme Extension Health Check

EXTENSION_NAME="your-extension"
SUITECRM_PATH="/var/www/html"

echo "=== SuiteCRM Theme Extension Health Check ==="
echo

# Check extension exists
if [ -d "$SUITECRM_PATH/extensions/$EXTENSION_NAME" ]; then
    echo "✅ Extension directory exists"
else
    echo "❌ Extension directory missing"
    exit 1
fi

# Check configuration
if [ -f "$SUITECRM_PATH/extensions/$EXTENSION_NAME/config/extension.php" ]; then
    echo "✅ Extension config exists"
    if grep -q "enabled.*true" "$SUITECRM_PATH/extensions/$EXTENSION_NAME/config/extension.php"; then
        echo "✅ Extension is enabled"
    else
        echo "⚠️  Extension is disabled"
    fi
else
    echo "❌ Extension config missing"
fi

# Check theme assets
if [ -d "$SUITECRM_PATH/extensions/$EXTENSION_NAME/app/src/theme-assets" ]; then
    echo "✅ Theme assets directory exists"

    ASSET_FILES=("_variables.scss" "_components.scss" "_typography.scss" "main.scss")
    for file in "${ASSET_FILES[@]}"; do
        if [ -f "$SUITECRM_PATH/extensions/$EXTENSION_NAME/app/src/theme-assets/$file" ]; then
            echo "✅ $file exists"
        else
            echo "❌ $file missing"
        fi
    done
else
    echo "❌ Theme assets directory missing"
fi

# Check build output
if [ -f "$SUITECRM_PATH/public/extensions/$EXTENSION_NAME/remoteEntry.js" ]; then
    echo "✅ remoteEntry.js deployed"

    # Check HTTP accessibility
    if curl -f -s "http://localhost:8080/extensions/$EXTENSION_NAME/remoteEntry.js" > /dev/null; then
        echo "✅ remoteEntry.js accessible via HTTP"
    else
        echo "❌ remoteEntry.js not accessible via HTTP"
    fi
else
    echo "❌ remoteEntry.js not deployed"
fi

# Check styles
if [ -f "$SUITECRM_PATH/public/extensions/$EXTENSION_NAME/styles.css" ]; then
    echo "✅ Extension styles compiled"
else
    echo "⚠️  Extension styles not found"
fi

echo
echo "=== Health Check Complete ==="
```

### CSS Debug Check
```bash
# Check if your theme colors are in the main CSS
curl -s "http://localhost:8080/dist/styles.css" | grep -i "your-hex-color" | wc -l

# Check extension-specific CSS
curl -s "http://localhost:8080/extensions/your-extension/styles.css" | head -20
```

### Extension Debug Info
```bash
# List all extensions
ls -la /var/www/html/extensions/

# Check build timestamps
ls -la /var/www/html/public/extensions/your-extension/

# Check configuration
cat /var/www/html/extensions/your-extension/config/extension.php

# Check angular.json entry
grep -A 30 "your-extension" /var/www/html/angular.json
```

---

## 🚀 Performance Tips

### Build Optimization
```bash
# Use production builds for better performance
npm run build-prod:your-extension

# Monitor build bundle sizes
npm run build-dev:your-extension -- --stats-json
```

### Cache Management
```bash
# Clear all caches when troubleshooting
php bin/console cache:clear
rm -rf var/cache/* 2>/dev/null || true

# Browser cache - force refresh with Ctrl+Shift+R
```

### Development Workflow
```bash
# Create a quick rebuild script
cat > rebuild-theme.sh << 'EOF'
#!/bin/bash
npm run build-dev:your-extension
php bin/console cache:clear
echo "Theme rebuilt and cache cleared"
EOF

chmod +x rebuild-theme.sh
```

---

## 📞 Getting Help

### Before Asking for Help
1. Run the health check script above
2. Check browser console for JavaScript errors
3. Verify all files exist and have correct permissions
4. Try the solutions in this guide

### What to Include in Support Requests
- SuiteCRM version (should be 8.6.1)
- Node.js and npm versions
- Complete error messages
- Output of health check script
- Browser console errors
- Docker setup details (if applicable)

### Useful Commands for Support
```bash
# System information
cat /var/www/html/VERSION
node --version
npm --version
ng version

# Extension status
ls -la extensions/your-extension/
ls -la public/extensions/your-extension/

# Recent logs
tail -f logs/suitecrm.log
```

---

## 🔄 Recovery Procedures

### Complete Extension Reset
```bash
# Disable extension
sed -i "s/'enabled' => true/'enabled' => false/" extensions/your-extension/config/extension.php

# Remove build artifacts
rm -rf extensions/your-extension/public/
rm -rf public/extensions/your-extension/

# Clear caches
php bin/console cache:clear

# Start over with setup
./scripts/setup-extension.sh your-extension /var/www/html ./theme-assets
```

### Restore Original SuiteCRM
```bash
# Restore original styles if modified
cp /var/www/html/public/dist/styles.css.backup /var/www/html/public/dist/styles.css

# Disable all custom extensions
find extensions/ -name "extension.php" -exec sed -i "s/'enabled' => true/'enabled' => false/" {} \;

# Clear cache
php bin/console cache:clear
```

This troubleshooting guide should help resolve most common issues encountered during the migration process. Keep it handy during your migration work!