# SuiteCRM V7 to V8.6.1 Theme Migration Guide

Complete guide for migrating custom themes from SuiteCRM Version 7 to Version 8.6.1, including the Logical Front Dynamics theme migration process.

## 📋 Table of Contents

- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Architecture Changes](#architecture-changes)
- [Migration Process](#migration-process)
- [Step-by-Step Instructions](#step-by-step-instructions)
- [Troubleshooting](#troubleshooting)
- [Examples](#examples)
- [Scripts](#scripts)

## 🎯 Overview

This guide provides a complete process for migrating custom themes from SuiteCRM V7's static HTML/CSS/JS architecture to SuiteCRM V8.6.1's Angular-based frontend architecture.

### What This Guide Covers:
- ✅ Extracting design assets from V7 themes
- ✅ Creating Angular-based frontend extensions
- ✅ Migrating color schemes and styling
- ✅ Deploying and testing themes in V8
- ✅ Troubleshooting common issues

## 🔧 Prerequisites

### System Requirements:
- **SuiteCRM 8.6.1** installed and running
- **Node.js** (version 18.x or higher)
- **npm** or **yarn** package manager
- **Docker** (if using containerized setup)
- **Angular CLI** (version 16.x)

### Knowledge Requirements:
- Basic understanding of CSS/SCSS
- Familiarity with Angular concepts (components, modules)
- Command line interface experience
- Basic Docker knowledge (if using containers)

### Files You'll Need:
- Your existing SuiteCRM V7 theme files
- Access to SuiteCRM 8.6.1 source code
- Administrative access to the system

## 🏗️ Architecture Changes

### SuiteCRM V7 Architecture:
```
V7 Theme Structure:
├── styles.css          # Static CSS files
├── images/             # Static assets
├── templates/          # PHP/Smarty templates
└── javascript/         # jQuery-based scripts
```

### SuiteCRM V8.6.1 Architecture:
```
V8 Extension Structure:
├── extensions/
│   └── your-theme-ext/
│       ├── app/
│       │   ├── src/
│       │   │   ├── styles.scss      # SCSS with imports
│       │   │   ├── assets/          # Static assets
│       │   │   └── components/      # Angular components
│       │   └── webpack.config.js    # Module Federation config
│       └── config/
│           └── extension.php        # Extension configuration
└── public/extensions/               # Built extension files
```

## 🚀 Migration Process

### Phase 1: Design Asset Extraction
1. **Analyze V7 Theme Structure**
2. **Extract Color Variables & Design Tokens**
3. **Convert CSS to SCSS with Variables**
4. **Preserve Typography & Spacing Systems**
5. **Copy Static Assets (logos, images)**

### Phase 2: Extension Setup
1. **Create Angular Frontend Extension**
2. **Configure Module Federation**
3. **Set Up Build Environment**
4. **Configure Extension Registration**

### Phase 3: Theme Integration
1. **Import Design Assets to Extension**
2. **Apply Theme Overrides**
3. **Build and Deploy Extension**
4. **Test Theme Application**

### Phase 4: Deployment & Testing
1. **Enable Extension in SuiteCRM**
2. **Clear Caches**
3. **Verify Theme Loading**
4. **Fix Any Issues**

## 📝 Step-by-Step Instructions

### Step 1: Analyze Your V7 Theme

First, examine your existing V7 theme structure:

```bash
# Navigate to your V7 theme directory
cd /path/to/your/v7-theme/

# List all theme files
find . -name "*.css" -o -name "*.scss" -o -name "*.js" -o -name "*.png" -o -name "*.jpg" -o -name "*.svg"
```

### Step 2: Extract Design Assets

Use the provided script to extract design assets:

```bash
# Run the extraction script
./scripts/extract-v7-assets.sh /path/to/v7-theme /output/directory
```

This creates:
- `_variables.scss` - Color scheme, spacing, typography
- `_components.scss` - UI component styles
- `_typography.scss` - Text styling and animations
- `assets/` - Images and static files

### Step 3: Access SuiteCRM V8 Container (Docker Setup)

```bash
# Find your SuiteCRM container
docker ps | grep suite

# Access the container
docker exec -it <container-name> bash

# Verify SuiteCRM version
cat VERSION  # Should show 8.6.1
```

### Step 4: Create Extension Structure

```bash
# Navigate to extensions directory
cd /var/www/html/extensions/

# Create your theme extension
mkdir -p your-theme-ext/app/src/theme-assets
mkdir -p your-theme-ext/config
```

### Step 5: Copy Design Assets

```bash
# Copy extracted assets to extension
cp -r /path/to/extracted-assets/* /var/www/html/extensions/your-theme-ext/app/src/theme-assets/
```

### Step 6: Configure Extension

Create extension configuration:

```bash
# Create extension.php
cat > /var/www/html/extensions/your-theme-ext/config/extension.php << 'EOF'
<?php
use Symfony\Component\DependencyInjection\Container;

if (!isset($container)) {
    return;
}

$extensions = $container->getParameter('extensions') ?? [];

$extensions['your-theme-ext'] = [
    'remoteEntry' => '../extensions/your-theme-ext/remoteEntry.js',
    'remoteName' => 'your-theme-ext',
    'enabled' => true,
    'extension_name' => 'Your Custom Theme',
    'extension_uri' => 'https://yourcompany.com',
    'description' => 'Custom theme migrated from SuiteCRM V7',
    'version' => '1.0.0',
    'author' => 'Your Name',
    'author_uri' => 'https://yourcompany.com',
    'license' => 'GPL3'
];

$container->setParameter('extensions', $extensions);
EOF
```

### Step 7: Update Extension Styles

```bash
# Update the main styles file
cat > /var/www/html/extensions/your-theme-ext/app/src/styles.scss << 'EOF'
/* Import your migrated theme */
@import './theme-assets/main';

/* SuiteCRM 8 compatibility overrides */
:root {
  --primary-color: #125EAD !important;
  --primary-color-text: #ffffff !important;
  --surface-ground: #f3f2f1 !important;
  --surface-section: #ffffff !important;
  --surface-card: #ffffff !important;
  --text-color: #323130 !important;
  --text-color-secondary: #605e5c !important;
}

/* Angular component overrides */
.p-component {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
}

.p-button-primary {
  background-color: var(--primary-color) !important;
  border-color: var(--primary-color) !important;
}
EOF
```

### Step 8: Build Extension

```bash
# Build the extension
npm run build-dev:your-theme-ext
```

### Step 9: Verify Deployment

```bash
# Check if files are deployed
ls -la /var/www/html/public/extensions/your-theme-ext/

# Verify remoteEntry.js exists
curl -I http://localhost:8080/extensions/your-theme-ext/remoteEntry.js
```

### Step 10: Clear Cache and Test

```bash
# Clear SuiteCRM cache
php bin/console cache:clear

# Test the site
curl -s http://localhost:8080 | grep "<title>"
```

## 🎨 Color Scheme Migration Example

### V7 CSS Variables:
```css
:root {
  --primary-color: #125EAD;
  --secondary-color: #4BB74E;
  --gray-light: #f3f2f1;
}
```

### V8 SCSS Variables:
```scss
// _variables.scss
$lf-primary: #125EAD;
$lf-secondary: #4BB74E;
$dynamics-gray-100: #f3f2f1;

// CSS Custom Properties for SuiteCRM 8
:root {
  --primary-color: #{$lf-primary} !important;
  --lf-primary: #{$lf-primary};
  --lf-secondary: #{$lf-secondary};
}
```

## 📁 File Structure After Migration

```
your-suitecrm-8/
├── extensions/
│   └── your-theme-ext/
│       ├── app/
│       │   ├── src/
│       │   │   ├── theme-assets/
│       │   │   │   ├── _variables.scss
│       │   │   │   ├── _components.scss
│       │   │   │   ├── _typography.scss
│       │   │   │   ├── main.scss
│       │   │   │   └── logo.png
│       │   │   ├── styles.scss
│       │   │   └── assets/
│       │   └── webpack.config.js
│       └── config/
│           └── extension.php
└── public/
    └── extensions/
        └── your-theme-ext/
            ├── remoteEntry.js
            ├── styles.css
            └── assets/
```

## 🔧 Build Commands

```bash
# Build extension for development
npm run build-dev:your-theme-ext

# Build all SuiteCRM components
npm run build-dev

# Build for production
npm run build-prod:your-theme-ext
```

## 🚨 Common Issues & Solutions

### Issue: Blank Page with Console Error
**Error:** `Failed to fetch dynamically imported module: remoteEntry.js`

**Solution:**
1. Verify extension is built: `ls /var/www/html/public/extensions/your-theme-ext/remoteEntry.js`
2. Check HTTP accessibility: `curl -I http://localhost:8080/extensions/your-theme-ext/remoteEntry.js`
3. Rebuild if missing: `npm run build-dev:your-theme-ext`

### Issue: Styles Not Applied
**Problem:** Theme colors not showing

**Solution:**
1. Verify CSS overrides in main stylesheet
2. Add `!important` to CSS custom properties
3. Check browser dev tools for CSS conflicts

### Issue: Extension Not Loading
**Problem:** Extension appears disabled

**Solution:**
1. Check extension.php has `'enabled' => true`
2. Clear cache: `php bin/console cache:clear`
3. Verify extension configuration syntax

## 📊 Comparison: V7 vs V8

| Aspect | SuiteCRM V7 | SuiteCRM V8.6.1 |
|--------|-------------|-----------------|
| **Frontend** | PHP/Smarty templates | Angular + TypeScript |
| **Styling** | Static CSS/SASS | SCSS with Angular |
| **Build Process** | Simple SASS compilation | Webpack + Module Federation |
| **Customization** | Direct file modification | Extension system |
| **Deployment** | File copy | Angular build + deployment |

## 🎯 Best Practices

### Theme Development:
1. **Use SCSS variables** for consistent theming
2. **Leverage CSS custom properties** for runtime overrides
3. **Follow Angular component patterns** for extensibility
4. **Test thoroughly** in different browsers
5. **Document customizations** for future maintenance

### Performance:
1. **Optimize assets** (compress images, minify CSS)
2. **Use efficient selectors** (avoid deep nesting)
3. **Lazy load components** when possible
4. **Monitor bundle sizes** during development

## 📚 Additional Resources

- [SuiteCRM 8 Frontend Architecture Documentation](https://docs.suitecrm.com/8.x/developer/architecture/front-end-architecture/)
- [Angular Module Federation Guide](https://webpack.js.org/concepts/module-federation/)
- [SCSS Documentation](https://sass-lang.com/documentation)
- [SuiteCRM Extension Development](https://docs.suitecrm.com/8.x/developer/extensions/)

## 📄 License

This migration guide is provided under the MIT License. See individual component licenses for specific terms.

## 🤝 Contributing

To contribute to this guide:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📞 Support

If you encounter issues:
1. Check the [Troubleshooting Guide](docs/troubleshooting.md)
2. Review [Common Issues](#common-issues--solutions)
3. Search existing issues in the repository
4. Create a new issue with detailed reproduction steps

---

**Last Updated:** September 2025
**SuiteCRM Version:** 8.6.1
**Guide Version:** 1.0.0