# SuiteCRM 8.6.1 Theme Assets - Logical Front Dynamics

This folder contains the extracted design assets from your SuiteCRM V7 theme, prepared for migration to SuiteCRM 8.6.1.

## Design System Overview

Your V7 theme uses a sophisticated design system combining:
- **Logical Front** branding colors (primary blue #125EAD, secondary green #4BB74E)
- **Microsoft Dynamics** gray scale palette
- Modern card-based layouts with subtle shadows and animations
- Consistent typography scale and spacing system

## Files Structure

### Core Design Files
- `_variables.scss` - All color, spacing, typography, and design tokens
- `_components.scss` - Widget, table, navigation, and UI component styles
- `_typography.scss` - Text styling, animations, and utility classes
- `logo.png` - Brand logo (48px height, preserved aspect ratio)

### Key Design Features Preserved

#### Color System
- **Primary Colors**: Logical Front blue (#125EAD) with light/dark variants
- **Secondary Colors**: Logical Front green (#4BB74E) with variants
- **Gray Scale**: Microsoft Dynamics 9-step gray palette
- **Semantic Colors**: Success, warning, error, info states

#### Component Architecture
- **Modern Cards**: Subtle shadows, hover animations, rounded corners
- **Dashboard Widgets**: Half-width widgets with gray headers and data tables
- **Navigation**: Clean header with search, active states, hover effects
- **Data Tables**: Hover animations, consistent padding, styled pagination

#### Typography & Layout
- **Font Stack**: System fonts (-apple-system, Segoe UI, etc.)
- **Scale**: 7-step font size scale (11px to 30px)
- **Spacing**: 6-step spacing scale (4px to 48px)
- **Animations**: Loading shimmer, pulse, fade-in effects

## Migration to SuiteCRM 8.6.1

### Next Steps
1. **Set up SuiteCRM 8.6.1 development environment**
2. **Create Angular frontend extension**
3. **Import these SCSS files into the extension**
4. **Build Angular components using these design patterns**

### Architecture Notes
- SuiteCRM 8 uses Angular + Module Federation
- Components are built as TypeScript/Angular rather than static HTML
- SCSS files will be imported into Angular component stylesheets
- Logo will be placed in Angular assets folder

### Component Mapping for V8
- **Dashboard Widgets** → Angular dashboard components
- **Data Tables** → Angular table components
- **Navigation** → Angular header/nav components
- **Search** → Angular search component
- **Pagination** → Angular pagination component

## Color Palette Reference

### Primary Colors
- LF Primary: `#125EAD`
- LF Primary Light: `#4A90E2`
- LF Primary Dark: `#0A3D6B`
- LF Secondary: `#4BB74E`
- LF Secondary Light: `#7BC97B`
- LF Secondary Dark: `#2F7D32`

### Microsoft Dynamics Grays
- Gray 50: `#faf9f8` (lightest)
- Gray 100: `#f3f2f1`
- Gray 200: `#edebe9`
- Gray 300: `#e1dfdd`
- Gray 400: `#d2d0ce`
- Gray 500: `#a19f9d`
- Gray 600: `#8a8886`
- Gray 700: `#605e5c`
- Gray 800: `#323130`
- Gray 900: `#201f1e` (darkest)

## Usage in SuiteCRM 8

Import the variables file in your Angular component SCSS:
```scss
@import '../../../suitecrm8-theme-assets/variables';
@import '../../../suitecrm8-theme-assets/components';
@import '../../../suitecrm8-theme-assets/typography';
```

All your design system tokens will then be available as SCSS variables and mixins for use in Angular components.