#!/bin/bash

# SuiteCRM V8 Theme Extension Setup Script
# Usage: ./setup-extension.sh <extension-name> <suitecrm-path> <theme-assets-path>

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
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
if [ $# -ne 3 ]; then
    print_error "Usage: $0 <extension-name> <suitecrm-path> <theme-assets-path>"
    print_error "Example: $0 my-custom-theme /var/www/html ./extracted-theme-assets"
    exit 1
fi

EXTENSION_NAME="$1"
SUITECRM_PATH="$2"
THEME_ASSETS_PATH="$3"

# Validate inputs
if [[ ! "$EXTENSION_NAME" =~ ^[a-zA-Z][a-zA-Z0-9_-]*$ ]]; then
    print_error "Extension name must start with a letter and contain only letters, numbers, hyphens, and underscores"
    exit 1
fi

if [ ! -d "$SUITECRM_PATH" ]; then
    print_error "SuiteCRM path does not exist: $SUITECRM_PATH"
    exit 1
fi

if [ ! -d "$THEME_ASSETS_PATH" ]; then
    print_error "Theme assets path does not exist: $THEME_ASSETS_PATH"
    exit 1
fi

# Check if running in Docker context
DOCKER_CONTEXT=""
if [ -n "$SUITECRM_CONTAINER" ]; then
    DOCKER_CONTEXT="docker exec $SUITECRM_CONTAINER"
    print_status "Using Docker container: $SUITECRM_CONTAINER"
fi

print_status "Setting up SuiteCRM 8 theme extension..."
print_status "Extension Name: $EXTENSION_NAME"
print_status "SuiteCRM Path: $SUITECRM_PATH"
print_status "Theme Assets: $THEME_ASSETS_PATH"

# Create extension directory structure
EXTENSION_DIR="$SUITECRM_PATH/extensions/$EXTENSION_NAME"
print_status "Creating extension directory structure..."

$DOCKER_CONTEXT mkdir -p "$EXTENSION_DIR"/{app/src/theme-assets,app/src/assets/images,config}

# Check if this is a fresh extension or update
if [ -f "$EXTENSION_DIR/config/extension.php" ]; then
    print_warning "Extension already exists. This will update the existing extension."
    read -p "Continue? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Operation cancelled"
        exit 1
    fi
fi

# Copy theme assets
print_status "Copying theme assets..."
if command -v rsync >/dev/null 2>&1; then
    # Use rsync if available (better for Docker contexts)
    rsync -av "$THEME_ASSETS_PATH/" "$EXTENSION_DIR/app/src/theme-assets/"
else
    # Fallback to cp
    cp -r "$THEME_ASSETS_PATH"/* "$EXTENSION_DIR/app/src/theme-assets/"
fi

print_success "Theme assets copied successfully"

# Create extension.php configuration
print_status "Creating extension configuration..."
$DOCKER_CONTEXT tee "$EXTENSION_DIR/config/extension.php" > /dev/null << EOF
<?php

use Symfony\Component\DependencyInjection\Container;

if (!isset(\$container)) {
    return;
}

/** @var Container \$container */
\$extensions = \$container->getParameter('extensions') ?? [];

\$extensions['$EXTENSION_NAME'] = [
    'remoteEntry' => '../extensions/$EXTENSION_NAME/remoteEntry.js',
    'remoteName' => '$EXTENSION_NAME',
    'enabled' => true,
    'extension_name' => '$(echo $EXTENSION_NAME | sed 's/-/ /g' | sed 's/\b\w/\U&/g') Theme',
    'extension_uri' => 'https://suitecrm.com',
    'description' => 'Custom theme migrated from SuiteCRM V7',
    'version' => '1.0.0',
    'author' => 'Custom Theme',
    'author_uri' => 'https://suitecrm.com',
    'license' => 'GPL3'
];

\$container->setParameter('extensions', \$extensions);
EOF

print_success "Extension configuration created"

# Create styles.scss
print_status "Creating main styles file..."
$DOCKER_CONTEXT tee "$EXTENSION_DIR/app/src/styles.scss" > /dev/null << 'EOF'
/* SuiteCRM 8 Custom Theme - Main Stylesheet */
/* Import the complete theme system from migrated assets */

@import './theme-assets/main';

/* Additional SuiteCRM 8 compatibility overrides */
:root {
  /* These will be automatically set by your _variables.scss, but can be overridden here */
  --primary-color: var(--primary-color, #007bff) !important;
  --primary-color-text: #ffffff !important;
  --surface-ground: var(--surface-ground, #f8f9fa) !important;
  --surface-section: #ffffff !important;
  --surface-card: #ffffff !important;
  --text-color: var(--text-color, #212529) !important;
  --text-color-secondary: var(--text-color-secondary, #6c757d) !important;
}

/* Ensure Angular components use theme fonts */
.p-component {
  font-family: var(--font-family-base, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif) !important;
}

/* Dashboard and layout components */
.widget-container,
.dashboard-container,
.list-container {
  .card {
    @include card-base;

    &:hover {
      box-shadow: var(--shadow-md, 0 4px 8px rgba(0,0,0,0.12));
      transform: translateY(-1px);
    }
  }
}

/* Data tables */
.p-datatable {
  .p-datatable-table {
    font-size: var(--font-size-sm, 12px);

    th {
      background: var(--gray-100, #f8f9fa) !important;
      color: var(--gray-700, #495057) !important;
      font-weight: 500 !important;
      padding: 10px !important;
    }

    td {
      padding: 10px !important;
      color: var(--gray-800, #343a40) !important;
    }

    tr:hover {
      background: var(--gray-50, #f9f9f9) !important;
    }
  }
}

/* Navigation components */
.navbar,
.p-menubar {
  background-color: #ffffff !important;
  border-bottom: 1px solid var(--border-color, #dee2e6) !important;
  box-shadow: var(--shadow-sm, 0 2px 4px rgba(0,0,0,0.08)) !important;

  .p-menubar-root-list > .p-menuitem > .p-menuitem-link {
    color: var(--gray-600, #6c757d) !important;

    &:hover {
      color: var(--primary-color, #007bff) !important;
      background-color: var(--gray-50, #f9f9f9) !important;
    }
  }
}

/* Button styling */
.p-button {
  &.p-button-primary {
    background-color: var(--primary-color, #007bff) !important;
    border-color: var(--primary-color, #007bff) !important;

    &:hover {
      background-color: var(--primary-color-dark, #0056b3) !important;
      border-color: var(--primary-color-dark, #0056b3) !important;
    }
  }

  &.p-button-secondary {
    background-color: var(--secondary-color, #6c757d) !important;
    border-color: var(--secondary-color, #6c757d) !important;

    &:hover {
      background-color: var(--secondary-color-dark, #5a6268) !important;
      border-color: var(--secondary-color-dark, #5a6268) !important;
    }
  }
}

/* Links */
a:not(.p-button) {
  color: var(--primary-color, #007bff) !important;

  &:hover {
    color: var(--primary-color-dark, #0056b3) !important;
  }
}

/* Form components */
.p-inputtext,
.p-dropdown,
.p-multiselect {
  border-color: var(--border-color, #ced4da) !important;

  &:focus {
    border-color: var(--primary-color, #007bff) !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
  }
}

/* Toast messages */
.p-toast .p-toast-message {
  &.p-toast-message-success {
    background: var(--success-color, #d4edda) !important;
    border-color: var(--success-color, #c3e6cb) !important;
    color: var(--success-color-dark, #155724) !important;
  }

  &.p-toast-message-error {
    background: var(--error-color-light, #f8d7da) !important;
    border-color: var(--error-color, #f5c6cb) !important;
    color: var(--error-color-dark, #721c24) !important;
  }
}
EOF

print_success "Main styles file created"

# Copy existing defaultExt structure if needed
if [ -d "$SUITECRM_PATH/extensions/defaultExt/app" ]; then
    print_status "Using existing extension structure as template..."

    # Copy essential files from defaultExt
    FILES_TO_COPY=(
        "tsconfig.app.json"
        "tsconfig.spec.json"
        "tslint.json"
        "webpack.config.js"
        "webpack.prod.config.js"
        "karma.conf.js"
    )

    for file in "${FILES_TO_COPY[@]}"; do
        if [ -f "$SUITECRM_PATH/extensions/defaultExt/app/$file" ]; then
            # Update webpack config for new extension name
            if [[ "$file" == *"webpack"* ]]; then
                $DOCKER_CONTEXT sed "s/defaultExt/$EXTENSION_NAME/g" \
                    "$SUITECRM_PATH/extensions/defaultExt/app/$file" > \
                    "$EXTENSION_DIR/app/$file"
            else
                $DOCKER_CONTEXT cp "$SUITECRM_PATH/extensions/defaultExt/app/$file" "$EXTENSION_DIR/app/$file"
            fi
            print_success "Copied $file"
        fi
    done

    # Copy src structure
    SRC_DIRS=(
        "src/app"
        "src/components"
        "src/containers"
        "src/environments"
        "src/fields"
        "src/services"
        "src/views"
    )

    for dir in "${SRC_DIRS[@]}"; do
        if [ -d "$SUITECRM_PATH/extensions/defaultExt/app/$dir" ]; then
            $DOCKER_CONTEXT cp -r "$SUITECRM_PATH/extensions/defaultExt/app/$dir" "$EXTENSION_DIR/app/src/"
            print_success "Copied $dir structure"
        fi
    done

    # Copy essential src files
    SRC_FILES=(
        "src/bootstrap.ts"
        "src/extension.module.ts"
        "src/favicon.ico"
        "src/index.html"
        "src/main.ts"
        "src/polyfills.ts"
        "src/test.ts"
    )

    for file in "${SRC_FILES[@]}"; do
        if [ -f "$SUITECRM_PATH/extensions/defaultExt/app/$file" ]; then
            $DOCKER_CONTEXT cp "$SUITECRM_PATH/extensions/defaultExt/app/$file" "$EXTENSION_DIR/app/$file"
            print_success "Copied $file"
        fi
    done

else
    print_warning "No existing defaultExt found. You'll need to set up the Angular structure manually."
    print_status "Consider using the defaultExt as a template or refer to the SuiteCRM documentation."
fi

# Update angular.json to include new extension
print_status "Checking angular.json configuration..."
ANGULAR_JSON="$SUITECRM_PATH/angular.json"

if [ -f "$ANGULAR_JSON" ]; then
    # Check if extension already exists in angular.json
    if $DOCKER_CONTEXT grep -q "\"$EXTENSION_NAME\":" "$ANGULAR_JSON"; then
        print_warning "Extension '$EXTENSION_NAME' already exists in angular.json"
    else
        print_status "Extension configuration needs to be added to angular.json manually"
        print_warning "Please add the following configuration to angular.json projects section:"

        cat << EOF

    "$EXTENSION_NAME": {
      "projectType": "application",
      "schematics": {
        "@schematics/angular:component": {
          "style": "scss"
        }
      },
      "root": "extensions/$EXTENSION_NAME",
      "sourceRoot": "extensions/$EXTENSION_NAME/app/src",
      "prefix": "app",
      "architect": {
        "build": {
          "builder": "ngx-build-plus:browser",
          "options": {
            "namedChunks": true,
            "commonChunk": false,
            "sourceMap": true,
            "aot": true,
            "outputPath": "extensions/$EXTENSION_NAME/public",
            "index": "extensions/$EXTENSION_NAME/app/src/index.html",
            "main": "extensions/$EXTENSION_NAME/app/src/main.ts",
            "polyfills": "extensions/$EXTENSION_NAME/app/src/polyfills.ts",
            "tsConfig": "extensions/$EXTENSION_NAME/app/tsconfig.app.json",
            "assets": [
              "extensions/$EXTENSION_NAME/app/src/favicon.ico",
              "extensions/$EXTENSION_NAME/app/src/assets"
            ],
            "styles": [
              "extensions/$EXTENSION_NAME/app/src/styles.scss"
            ],
            "scripts": [],
            "extraWebpackConfig": "extensions/$EXTENSION_NAME/app/webpack.config.js"
          },
          "configurations": {
            "production": {
              "fileReplacements": [
                {
                  "replace": "extensions/$EXTENSION_NAME/app/src/environments/environment.ts",
                  "with": "extensions/$EXTENSION_NAME/app/src/environments/environment.prod.ts"
                }
              ],
              "optimization": true,
              "outputHashing": "all",
              "sourceMap": false,
              "namedChunks": true,
              "extractLicenses": true,
              "vendorChunk": false,
              "buildOptimizer": true,
              "extraWebpackConfig": "extensions/$EXTENSION_NAME/app/webpack.prod.config.js"
            },
            "dev": {
              "outputPath": "public/extensions/$EXTENSION_NAME"
            }
          }
        }
      }
    },

And add this to the scripts section of package.json:

    "build-dev:$EXTENSION_NAME": "ng build $EXTENSION_NAME --configuration dev",
    "build-prod:$EXTENSION_NAME": "ng build $EXTENSION_NAME --configuration production",

EOF
    fi
else
    print_error "angular.json not found at $ANGULAR_JSON"
    print_error "Make sure you're running this from the SuiteCRM root directory"
fi

# Create README for the extension
print_status "Creating extension documentation..."
$DOCKER_CONTEXT tee "$EXTENSION_DIR/README.md" > /dev/null << EOF
# $EXTENSION_NAME Theme Extension

Custom SuiteCRM 8 theme extension migrated from SuiteCRM V7.

## Structure

\`\`\`
$EXTENSION_NAME/
├── app/
│   ├── src/
│   │   ├── theme-assets/        # Migrated theme assets
│   │   │   ├── _variables.scss  # Color scheme & design tokens
│   │   │   ├── _components.scss # UI component styles
│   │   │   ├── _typography.scss # Text styles & animations
│   │   │   └── main.scss        # Main import file
│   │   ├── styles.scss          # Extension main stylesheet
│   │   └── assets/              # Images and static files
│   └── webpack.config.js        # Module Federation config
└── config/
    └── extension.php            # Extension registration
\`\`\`

## Building

\`\`\`bash
# Build for development
npm run build-dev:$EXTENSION_NAME

# Build for production
npm run build-prod:$EXTENSION_NAME
\`\`\`

## Deployment

The extension is automatically deployed to:
- Development: \`public/extensions/$EXTENSION_NAME/\`
- Production: \`extensions/$EXTENSION_NAME/public/\`

## Customization

### Colors
Edit \`app/src/theme-assets/_variables.scss\` to customize:
- Primary and secondary colors
- Gray scale palette
- Success, warning, error colors
- Typography settings
- Spacing values

### Components
Modify \`app/src/theme-assets/_components.scss\` to customize:
- Card styling
- Navigation appearance
- Button designs
- Table layouts
- Form elements

### Typography
Update \`app/src/theme-assets/_typography.scss\` for:
- Font families
- Font sizes and weights
- Text utilities
- Animations

## Status

- ✅ Extension structure created
- ✅ Theme assets imported
- ✅ Basic configuration completed
- ⚠️  Angular.json configuration required (see setup instructions)
- 🔄 Testing and refinement needed

## Next Steps

1. Update angular.json with the configuration provided by the setup script
2. Build the extension: \`npm run build-dev:$EXTENSION_NAME\`
3. Test in SuiteCRM and refine styling as needed
4. Deploy to production when satisfied

## Generated

- Created: $(date)
- Extension Name: $EXTENSION_NAME
- SuiteCRM Path: $SUITECRM_PATH
- Theme Assets: $THEME_ASSETS_PATH
EOF

print_success "Extension documentation created"

# Create deployment script
print_status "Creating deployment helper script..."
$DOCKER_CONTEXT tee "$EXTENSION_DIR/deploy.sh" > /dev/null << EOF
#!/bin/bash

# Quick deployment script for $EXTENSION_NAME theme extension
# Usage: ./deploy.sh [dev|prod]

MODE=\${1:-dev}
EXTENSION_NAME="$EXTENSION_NAME"

if [[ "\$MODE" != "dev" && "\$MODE" != "prod" ]]; then
    echo "Usage: \$0 [dev|prod]"
    exit 1
fi

echo "Building \$EXTENSION_NAME extension for \$MODE..."

if [ "\$MODE" = "dev" ]; then
    npm run build-dev:\$EXTENSION_NAME
else
    npm run build-prod:\$EXTENSION_NAME
fi

if [ \$? -eq 0 ]; then
    echo "✅ Build successful"

    # Clear cache
    php bin/console cache:clear

    echo "✅ Cache cleared"
    echo "🚀 Extension deployed successfully!"
    echo ""
    echo "Check your SuiteCRM at: http://localhost:8080"

else
    echo "❌ Build failed"
    exit 1
fi
EOF

$DOCKER_CONTEXT chmod +x "$EXTENSION_DIR/deploy.sh"
print_success "Deployment script created"

# Final summary and instructions
print_success "Theme extension setup completed successfully!"
echo ""
print_status "📁 Extension created at: $EXTENSION_DIR"
print_status "📝 Configuration file: $EXTENSION_DIR/config/extension.php"
print_status "🎨 Theme assets: $EXTENSION_DIR/app/src/theme-assets/"
print_status "📋 Documentation: $EXTENSION_DIR/README.md"
echo ""
print_status "🚀 Next steps:"
echo "  1. Add angular.json configuration (see output above)"
echo "  2. Add package.json scripts (see output above)"
echo "  3. Build extension: npm run build-dev:$EXTENSION_NAME"
echo "  4. Test at: http://localhost:8080"
echo ""
print_warning "⚠️  Manual steps required:"
echo "  - Update angular.json with provided configuration"
echo "  - Add build scripts to package.json"
echo "  - Review and customize theme variables"
echo "  - Test and refine styling"
echo ""
print_success "✅ Setup complete! Your extension is ready for development."
EOF