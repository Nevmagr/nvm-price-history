# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress/WooCommerce plugin called "Nevma Price History for WooCommerce" that tracks price changes for products and maintains historical data for analysis. The plugin automatically records price changes when products are saved and provides methods to retrieve minimum prices over 30 days and full price history.

## Development Commands

### PHP Code Quality
```bash
# Run PHP linting
npm run php:lint
# or
npm run lint

# Fix PHP code style issues
npm run php:fix
# or 
npm run fix

# Check PHP compatibility
npm run php:compat
```

### Build and Development
```bash
# Generate autoloader and build plugin
npm run build

# Copy files to build directory
npm run copy

# Watch for changes during development
npm run watch
```

### Manual Commands
```bash
# Generate Composer autoloader
composer dumpautoload -o

# Run Grunt tasks directly
grunt build
grunt copy:all
grunt watch:all
```

## Architecture Overview

### Main Plugin Structure
- **Entry Point**: `nvm-history-price.php` - Main plugin file with WordPress hooks and plugin metadata
- **Core Class**: `classes/class-woo-history-price.php` - Contains the main price tracking logic
- **WP-CLI Support**: `classes/class-wp-cli.php` - Provides command-line interface for bulk operations

### Key Components

#### Price Tracking System
The plugin uses a hook-based architecture:
- Hooks into `save_post_product` to track price changes automatically
- Stores price history in `_nvm_price_history` meta field
- Maintains 30-day minimum price in `_nvm_min_price_30` meta field
- Automatically purges entries older than 100 days

#### Product Type Support
- **Simple Products**: Direct price tracking with regular and sale prices
- **Variable Products**: Tracks each variation individually and calculates parent product minimums based on in-stock variations

#### Data Storage Format
Price history entries contain:
```php
[
    'regular_price' => 'price',
    'sale_price' => 'actual_price', 
    'date' => 'mysql_datetime'
]
```

### Namespace Structure
- Root namespace: `Nvm\Price_History`
- Main classes use PSR-4 autoloading with custom autoloader in main plugin file
- Vendor dependencies are isolated in `prefixed/` directory

### Code Standards
- Follows WooCommerce coding standards (see `phpcs.xml`)
- Uses WordPress coding conventions
- Supports PHP 7.4+
- Compatible with WooCommerce HPOS (High-Performance Order Storage)

## WP-CLI Commands
```bash
# Process all products and update price history
wp price-history
```

## File Structure Notes
- `build/` - Contains built plugin ready for distribution
- `prefixed/` - Isolated vendor dependencies (replaces standard `vendor/`)
- `classes/` - Plugin class files with PSR-4 autoloading
- Configuration files use WooCommerce and WordPress standards for linting/compatibility

## Important Hooks and Methods
- `save_post_product` - Automatically triggers price tracking
- `before_woocommerce_init` - Declares HPOS compatibility
- `add_meta_boxes` - Adds price history display to product edit screen

Price retrieval methods available on product objects:
- `get_price_min_30()` - Gets minimum price from last 30 days
- `get_history_price()` - Gets full price history array