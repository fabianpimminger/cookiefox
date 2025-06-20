# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Build System
```bash
# Development
npm run watch        # Watch mode for development with hot reload
npm run build        # Build both modern and legacy versions for production
npm run build:modern # Build modern ES2017+ bundle only
npm run build:legacy # Build legacy ES5 bundle with polyfills only

# Dependencies
npm install          # Install Node.js dependencies
composer install     # Install PHP dependencies (CMB2)

# Release
npm run release      # Copy files to release directory
npm run release:zip  # Create distribution ZIP file for WordPress.org
npm run release:test # Deploy to test environment
```

### No Testing Framework
This codebase does not have automated tests. Development relies on manual testing in WordPress environments.

## Architecture Overview

### Dual Build System
The plugin uses a sophisticated build system targeting both modern and legacy browsers:
- **Modern build**: ES2017+ features for modern browsers (main.js)
- **Legacy build**: ES5 with polyfills for IE11+ support (legacy.js)
- Frontend built with Svelte and Rollup, using PostCSS for styling

### PHP Backend Architecture
- **Singleton pattern**: Main class uses singleton (`class-main.php`)
- **Autoloading**: PSR-4 style autoloading for classes in `/includes/`
- **CMB2 dependency**: Admin interface built with CMB2 meta box framework
- **WordPress integration**: Extensive use of hooks, filters, and WordPress APIs

### Frontend JavaScript (Svelte)
- **Entry point**: `src/js/main.js` initializes the Svelte application
- **Main components**: `CookieFox.svelte`, `SingleConsent.svelte`, `CategoryConsent.svelte`
- **State management**: Svelte stores in `src/js/stores.js`
- **Utilities**: Focus trap for accessibility, event system, cookie management

### Key File Locations
- **Main plugin file**: `cookiefox.php` (defines constants, loads main class)
- **Core classes**: `/includes/class-*.php` (main functionality)
- **Frontend source**: `/src/js/` (Svelte components and utilities)
- **Built assets**: `/assets/frontend/` and `/assets/admin/` (compiled files)
- **CMB2 extensions**: `/includes/cmb2/` (custom field types)

### WordPress Integration Points
- **REST API**: Custom endpoints at `/wp-json/cookiefox/v1/cookies`
- **Settings page**: CMB2-based admin interface
- **Frontend rendering**: Smart script enqueuing with conditional loading
- **Internationalization**: Full i18n support with `/languages/` directory

### Performance Considerations
- **Minimal footprint**: JavaScript bundle size should be minimal
- **Conditional loading**: Scripts only load when cookie notice is needed
- **CSS delivery options**: External, inline, or custom CSS loading strategies
- **Version hashing**: Asset versioning for cache busting

### Browser Support Strategy
- Modern browsers receive optimized ES2017+ code
- Legacy browsers (IE11+) receive transpiled code with polyfills
- Graceful degradation ensures functionality across all supported browsers

## Development Notes

### Dependencies
- **CMB2**: Loaded via Composer for admin meta boxes
- **Svelte**: Frontend framework for reactive components
- **Focus-trap**: Accessibility library for modal dialogs
- **js-cookie**: Cookie manipulation library

### Build Configuration
- **Rollup**: Module bundler with Babel for legacy support
- **PostCSS**: CSS processing and optimization
- **Terser**: JavaScript minification for production builds

### Release Process
The plugin uses a manual release process:
1. Build production assets with `npm run build`
2. Generate release files with `npm run release`
3. Create distribution ZIP with `npm run release:zip`
4. No automated CI/CD - relies on manual QA testing