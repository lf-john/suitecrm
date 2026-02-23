# SuiteCRM V7 to V8 Theme Migration - Quick Start Guide

Get your SuiteCRM V7 theme migrated to V8.6.1 in under 30 minutes!

## 🚀 Prerequisites Check

Before starting, ensure you have:
- [ ] SuiteCRM 8.6.1 running (Docker or local)
- [ ] Node.js 18+ installed
- [ ] Your V7 theme files accessible
- [ ] Basic command line knowledge

## ⚡ Quick Migration (3 Simple Steps)

### Step 1: Extract Your V7 Theme Assets (5 minutes)

```bash
# Download this migration guide
git clone https://github.com/your-repo/suitecrm-v7-to-v8-migration-guide.git
cd suitecrm-v7-to-v8-migration-guide

# Extract your V7 theme
./scripts/extract-v7-assets.sh /path/to/your/v7-theme ./my-theme-assets

# ✅ Result: Theme assets extracted to ./my-theme-assets/
```

### Step 2: Set Up V8 Extension (10 minutes)

```bash
# For Docker setup:
export SUITECRM_CONTAINER=suitecrm_app  # Your container name
./scripts/setup-extension.sh my-custom-theme /var/www/html ./my-theme-assets

# For local setup:
./scripts/setup-extension.sh my-custom-theme /path/to/suitecrm ./my-theme-assets

# ✅ Result: Extension created in SuiteCRM extensions directory
```

### Step 3: Deploy Theme (5 minutes)

```bash
# Quick deploy (Docker)
./scripts/quick-deploy.sh my-custom-theme suitecrm_app

# Quick deploy (Local)
./scripts/quick-deploy.sh my-custom-theme

# ✅ Result: Theme deployed and ready at http://localhost:8080
```

## 🎯 That's It!

Your theme should now be live at http://localhost:8080

## 🔧 If Something Goes Wrong

### Quick Fixes

**Blank Page Error:**
```bash
# Check and fix extension deployment
./scripts/quick-deploy.sh my-custom-theme suitecrm_app
```

**Theme Colors Not Showing:**
```bash
# Direct CSS override (immediate fix)
curl -s http://localhost:8080/dist/styles.css.backup || \
  docker exec suitecrm_app cp /var/www/html/public/dist/styles.css /var/www/html/public/dist/styles.css.backup

# Add your colors directly
cat >> /path/to/suitecrm/public/dist/styles.css << 'EOF'

/* Your Theme Colors */
:root {
  --primary-color: #YOUR_PRIMARY_COLOR !important;
  --secondary-color: #YOUR_SECONDARY_COLOR !important;
}

body {
  background: linear-gradient(135deg, #YOUR_PRIMARY_COLOR 0%, #YOUR_SECONDARY_COLOR 100%) !important;
}
EOF
```

**Extension Won't Build:**
```bash
# Check angular.json configuration
grep -A 10 "my-custom-theme" /path/to/suitecrm/angular.json

# If missing, add this to angular.json projects section:
# See full configuration in the main README.md
```

## 📚 Need More Help?

- **Full Guide**: See [README.md](README.md) for complete instructions
- **Troubleshooting**: Check [docs/troubleshooting.md](docs/troubleshooting.md)
- **Real Example**: See [examples/logical-front-example.md](examples/logical-front-example.md)

## 🛠️ Advanced Usage

### Custom Color Migration
```bash
# After extraction, edit your colors:
nano my-theme-assets/_variables.scss

# Update these values:
$primary-color: #YOUR_COLOR;
$secondary-color: #YOUR_COLOR;
```

### Multiple Themes
```bash
# Create multiple extensions:
./scripts/setup-extension.sh theme-blue /var/www/html ./blue-assets
./scripts/setup-extension.sh theme-green /var/www/html ./green-assets

# Deploy specific theme:
./scripts/quick-deploy.sh theme-blue suitecrm_app
```

### Production Deployment
```bash
# Build for production:
docker exec suitecrm_app npm run build-prod:my-custom-theme

# Or locally:
npm run build-prod:my-custom-theme
```

## ✅ Success Checklist

After migration, verify:
- [ ] SuiteCRM loads without errors at http://localhost:8080
- [ ] Your brand colors are visible
- [ ] Navigation and buttons use your theme
- [ ] No JavaScript console errors
- [ ] All pages load correctly

## 📊 Migration Time Estimates

| Task | Time | Difficulty |
|------|------|------------|
| Asset Extraction | 5 min | Easy |
| Extension Setup | 10 min | Easy |
| Initial Deployment | 5 min | Easy |
| Color Customization | 10 min | Medium |
| Advanced Styling | 30-60 min | Medium |
| Troubleshooting | 10-30 min | Medium |

**Total: 20-30 minutes for basic migration**

## 🎨 Common Theme Elements

The migration preserves:
- ✅ Primary and secondary colors
- ✅ Typography (fonts, sizes, weights)
- ✅ Spacing and layout patterns
- ✅ Card and widget styling
- ✅ Navigation appearance
- ✅ Button designs
- ✅ Table formatting
- ✅ Form styling
- ✅ Background patterns/gradients

## 🔍 Verification Commands

```bash
# Check extension status
docker exec suitecrm_app ls -la /var/www/html/extensions/my-custom-theme/

# Verify build output
docker exec suitecrm_app ls -la /var/www/html/public/extensions/my-custom-theme/

# Check theme colors in CSS
curl -s "http://localhost:8080/extensions/my-custom-theme/styles.css" | grep -i "YOUR_COLOR_HEX"

# Test SuiteCRM accessibility
curl -I http://localhost:8080
```

## 🎉 Success!

If you've followed these steps, your SuiteCRM V7 theme is now running on V8.6.1!

**What's Different in V8:**
- Modern Angular architecture
- Better performance
- Enhanced security
- Mobile-responsive design
- Future-proof technology stack

**What Stayed the Same:**
- Your brand colors and identity
- Visual design and layout
- User experience
- Business functionality

Ready to explore SuiteCRM 8's new features while keeping your familiar theme!