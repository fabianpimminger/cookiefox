# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Build System
```bash
# Development
npm run watch        # Watch mode for development with hot reload
npm run build        # Production frontend bundle (assets/frontend)

# Dependencies
npm install          # Install Node.js dependencies
composer install     # Install PHP dependencies (CMB2)

# Release
npm run release      # Copy files to release directory
npm run release:zip  # Create distribution ZIP file for WordPress.org
npm run release:test # Deploy to test environment
```

### Tests
```bash
npm run playground   # WordPress Playground at http://127.0.0.1:9400
npm run test:e2e     # Playwright consent/embed tests (starts Playground)
npm run configurator       # Static server; standalone configurator at http://127.0.0.1:9401/configurator/
npm run test:configurator  # Playwright tests for the configurator (no WordPress)
```

### Standalone configurator
- Source in `src/configurator/` (Svelte), built by the second Rollup entry into `configurator/build/` (gitignored, excluded from the WordPress.org release).
- `src/configurator/snippet.js` mirrors the PHP defaults (`Helper::default_settings()`, `Frontend::prepare_data()`) and `Consent_Mode::print_head()`. Keep it in sync when those change.
- Usage docs: `docs/STANDALONE.md`.
- Deployed to GitHub Pages by `.github/workflows/pages.yml` on pushes to main; `npm run build:pages` assembles the same `_site/` locally.

## Architecture Overview

### Build System
- Frontend built with Svelte 4 and Rollup 4 (ES2017+), using PostCSS for styling
- Single production bundle: `assets/frontend/js/main.js`

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

### Browser Support
- Modern evergreen browsers (ES2017+). Internet Explorer is not supported.

## Development Notes

### Dependencies
- **CMB2**: Loaded via Composer for admin meta boxes
- **Svelte**: Frontend framework for reactive components
- **Focus-trap**: Accessibility library for modal dialogs
- **js-cookie**: Cookie manipulation library

### Build Configuration
- **Rollup**: Module bundler
- **PostCSS**: CSS processing and optimization
- **Terser**: JavaScript minification for production builds

### Release Process
```bash
npm run build
npm run release:wporg:dry
npm run release:wporg
```