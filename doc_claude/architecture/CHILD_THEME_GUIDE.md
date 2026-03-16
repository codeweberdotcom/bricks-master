# Child Theme Guide

Step-by-step guide for creating and configuring a child theme.

## What is a Child Theme?

A child theme is a lightweight theme that extends a parent theme (codeweber). Benefits:

- **Override parent files** without modifying originals
- **Safe updates** — update parent theme without losing customizations
- **Asset isolation** — compile child theme SCSS separately
- **Production deployments** — manage child theme independently

## Step 1: Create Child Theme Directory

Create a new directory for your child theme:

```bash
cd /c/laragon/www/codeweber2026/wp-content/themes
mkdir my-awesome-site
cd my-awesome-site
```

## Step 2: Create style.css

Minimal child theme stylesheet with header:

**File**: `/wp-content/themes/my-awesome-site/style.css`

```css
/*
Theme Name: My Awesome Site
Theme URI: https://example.com
Description: Child theme of CodeWeber
Author: Your Name
Author URI: https://example.com
Template: codeweber
Version: 1.0.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: my-awesome-site
Domain Path: /languages
*/

/* Your custom styles here - override parent styles */
```

**Critical fields:**
- `Template: codeweber` — MUST match parent theme folder name
- `Theme Name` — your child theme name (displays in WordPress)
- `Version` — for asset cache-busting

## Step 3: Create CLAUDE.md и settings.json

Создай два файла для Claude Code, чтобы разрешения работали на любом компьютере через git.

**Файл**: `/wp-content/themes/my-awesome-site/CLAUDE.md`

```markdown
# CLAUDE.md — Child Theme: My Awesome Site

Child theme of CodeWeber.
```

**Файл**: `/wp-content/themes/my-awesome-site/.claude/settings.json`

```json
{
  "permissions": {
    "allow": [
      "Read",
      "Edit(**/*.md)",
      "Write(**/*.md)"
    ]
  }
}
```

> Оба файла коммитятся в git — разрешения сохраняются при клонировании на другой машине.

---

## Step 4: Create functions.php

Child theme functions file that loads parent theme first:

**File**: `/wp-content/themes/my-awesome-site/functions.php`

```php
<?php
/**
 * My Awesome Site Child Theme
 * Child theme of CodeWeber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load parent theme styles
 * Using codeweber_get_dist_file_url for child-first asset resolution
 */
add_action('wp_enqueue_scripts', function() {
    // Parent theme styles loaded by parent (no need to duplicate)

    // Child theme custom styles
    $child_style_path = codeweber_get_dist_file_path('dist/assets/css/style.css');
    $child_style_url = codeweber_get_dist_file_url('dist/assets/css/style.css');

    if ($child_style_url) {
        wp_enqueue_style('my-awesome-site', $child_style_url, [],
            codeweber_asset_version($child_style_path));
    }
});

/**
 * Load child theme text domain for translation
 */
add_action('after_setup_theme', function() {
    load_child_theme_textdomain('my-awesome-site',
        get_stylesheet_directory() . '/languages');
}, 10);

// Add child-specific customizations here
```

**Key points:**
- Parent theme functions load automatically — don't call `parent_functions()`
- Use `codeweber_get_dist_file_url()` for child-first asset resolution
- Child theme's `functions.php` loads AFTER parent's
- Use `get_stylesheet_directory()` for child paths, `get_template_directory()` for parent

## Step 5: Create Directory Structure

Child theme can override ANY parent theme file by replicating the same path.

**Basic structure:**

```
my-awesome-site/
├── style.css                      # Required: theme header
├── functions.php                  # Optional: custom functions
├── templates/
│   ├── post-cards/
│   │   ├── staff/
│   │   │   └── default.php       # Override parent staff cards
│   │   └── clients/
│   │       └── default.php       # Override parent client cards
│   └── header/
│       └── custom.php             # Custom header template
├── dist/                          # Compiled assets (from Gulp)
│   └── assets/
│       ├── css/
│       │   └── style.css
│       ├── js/
│       │   └── script.js
│       └── images/
├── src/                           # Source files for Gulp
│   ├── scss/
│   │   └── style.scss
│   ├── js/
│   │   └── script.js
│   └── images/
├── functions/                     # Optional: custom functions
│   └── custom-cpt.php
└── gulpfile.js                    # Gulp configuration
```

**What you CAN override:**
- Any template file: `templates/`, single.php, archive.php, etc.
- Any function file in `functions/`
- CSS/SCSS in `dist/` and `src/`
- Images, assets, anything

**What you CANNOT override:**
- Parent theme's `functions.php` (instead, add filters/actions in child's `functions.php`)
- Redux Framework configuration (modify via filters instead)

## Step 6: Setup Gulp for Child Theme

Copy Gulp configuration from parent and adapt for child:

**File**: `/wp-content/themes/my-awesome-site/gulpfile.js`

```javascript
const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const browserSync = require('browser-sync').create();
const uglify = require('gulp-uglify');
const cleanCSS = require('gulp-clean-css');
const autoprefixer = require('gulp-autoprefixer');
const rename = require('gulp-rename');

const DIST = './dist';
const SRC = './src';

// SCSS Compilation
gulp.task('scss', function() {
    return gulp.src(`${SRC}/scss/**/*.scss`)
        .pipe(sass().on('error', sass.logError))
        .pipe(autoprefixer({ browsers: ['last 2 versions'] }))
        .pipe(cleanCSS())
        .pipe(gulp.dest(`${DIST}/assets/css`))
        .pipe(browserSync.stream());
});

// JavaScript Bundling
gulp.task('js', function() {
    return gulp.src(`${SRC}/js/**/*.js`)
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest(`${DIST}/assets/js`))
        .pipe(browserSync.stream());
});

// Image optimization
gulp.task('images', function() {
    return gulp.src(`${SRC}/images/**/*`)
        .pipe(gulp.dest(`${DIST}/assets/images`));
});

// Watch files
gulp.task('watch', function() {
    gulp.watch(`${SRC}/scss/**/*.scss`, gulp.series('scss'));
    gulp.watch(`${SRC}/js/**/*.js`, gulp.series('js'));
    gulp.watch(`${SRC}/images/**/*`, gulp.series('images'));
});

// BrowserSync
gulp.task('serve', function() {
    browserSync.init({
        proxy: 'localhost/codeweber2026',
        port: 3000,
        open: false
    });

    gulp.watch(`${SRC}/scss/**/*.scss`, gulp.series('scss'));
    gulp.watch(`${SRC}/js/**/*.js`, gulp.series('js'));
});

// Build task
gulp.task('build', gulp.parallel('scss', 'js', 'images'));

// Default task
gulp.task('default', gulp.series('build', 'serve'));
```

**Setup npm for child theme:**

```bash
cd /wp-content/themes/my-awesome-site
npm init -y
npm install --save-dev gulp gulp-sass gulp-uglify gulp-clean-css gulp-autoprefixer gulp-rename browser-sync sass
```

**Create src directory:**

```bash
mkdir -p src/scss src/js src/images
mkdir -p dist/assets/{css,js,images}
```

**Create initial SCSS file** (`src/scss/style.scss`):

```scss
// Child theme custom styles
// Override parent theme variables and styles here

// Example: customize Bootstrap colors
// $primary: #003366;
// $secondary: #ff6600;

// Import parent styles if needed
// @import "../../codeweber/src/scss/style.scss";

// Add child-specific styles
body {
    // custom body styles
}
```

## Step 7: package.json Scripts

Update `package.json` for convenient development:

```json
{
  "name": "my-awesome-site",
  "version": "1.0.0",
  "description": "Child theme of CodeWeber",
  "scripts": {
    "start": "gulp serve",
    "build": "gulp build",
    "watch": "gulp watch"
  },
  "dependencies": {},
  "devDependencies": {
    "gulp": "^4.0.0",
    "gulp-sass": "^5.0.0",
    "gulp-uglify": "^3.0.0",
    "gulp-clean-css": "^4.3.0",
    "gulp-autoprefixer": "^8.0.0",
    "gulp-rename": "^2.0.0",
    "browser-sync": "^2.27.0",
    "sass": "^1.50.0"
  }
}
```

**Usage:**

```bash
npm start      # Run Gulp with BrowserSync
npm run build  # Production build
npm run watch  # Watch files without BrowserSync
```

## Step 8: Activate in WordPress

1. Go to WordPress Admin → Appearance → Themes
2. Find "My Awesome Site" (child theme)
3. Click "Activate"

Child theme is now active. Any files in child theme override parent.

## Step 9: Override Parent Files

### Override a Template

Copy parent template to child maintaining same path:

**Parent**: `codeweber/templates/post-cards/staff/default.php`
**Child**: `my-awesome-site/templates/post-cards/staff/default.php`

```php
<?php
// Child theme override of staff card template
// This replaces parent theme's version
?>

<div class="staff-card custom-staff-styling">
    <!-- Your custom HTML -->
</div>
```

### Override a Function

Don't copy the function. Instead, use filters/actions:

**Parent** (`codeweber/functions/custom.php`):
```php
apply_filters('codeweber_staff_card_html', $html, $post_id);
```

**Child** (`my-awesome-site/functions.php`):
```php
add_filter('codeweber_staff_card_html', function($html, $post_id) {
    // Modify the HTML before display
    return str_replace('class="staff-card"', 'class="staff-card custom"', $html);
}, 10, 2);
```

### Override Redux Options

Don't modify parent Redux files. Use filters:

**Child** (`my-awesome-site/functions.php`):
```php
add_filter('redux/options/redux_demo/defaults', function($defaults) {
    $defaults['custom_setting'] = 'child_value';
    return $defaults;
});
```

## Recommended Child Theme Structure

For a production site, organize child theme like this:

```
my-awesome-site/
├── style.css                      # Theme info
├── functions.php                  # Load functions + text domain
├── functions/
│   ├── hooks.php                  # Custom hooks/filters
│   ├── helpers.php                # Utility functions
│   ├── cpt-custom.php             # Custom CPTs (if needed)
│   └── redux.php                  # Redux customization
├── templates/
│   ├── post-cards/
│   │   ├── staff/
│   │   │   ├── default.php
│   │   │   └── card.php
│   │   └── clients/
│   │       └── default.php
│   ├── header/
│   │   └── custom.php
│   └── components/
│       └── footer-custom.php
├── src/
│   ├── scss/
│   │   ├── _variables.scss
│   │   ├── _components.scss
│   │   └── style.scss
│   ├── js/
│   │   ├── custom-forms.js
│   │   ├── animations.js
│   │   └── script.js
│   └── images/
│       └── logo.png
├── dist/
│   └── assets/
│       ├── css/
│       │   └── style.css
│       ├── js/
│       │   └── script.js
│       └── images/
├── gulpfile.js
├── package.json
└── README.md
```

## Best Practices

### 1. Use Filters, Not Overrides

Instead of copying functions:

```php
// Good: Use filter in child functions.php
add_filter('codeweber_header_post_id', function($header_id, $context) {
    // Custom logic
    return $custom_id;
}, 10, 2);

// Bad: Copy entire function to functions.php
// This creates maintenance burden
```

### 2. Namespace Custom Functions

```php
// Child functions - use child-specific prefix
function my_awesome_site_custom_function() {
    // Implementation
}

add_filter('codeweber_*', 'my_awesome_site_*');
```

### 3. Keep Gulp Builds in dist/

Always run `npm run build` before deployment:

```bash
npm run build
git add dist/
git commit -m "Update child theme assets"
```

dist/ should be committed to git for production deployments.

### 4. Don't Modify Parent's functions.php

Parent updates will overwrite your changes. Use filters instead:

```php
// Child functions.php - safe for updates
add_filter('my_filter', function($value) {
    return modified_value;
});
```

### 5. Document Customizations

Add comments explaining why you override:

```php
<?php
/**
 * Override: Staff card with custom badge
 *
 * Reason: Add "Featured Staff" badge to certain employees
 * Hook: codeweber_staff_card_html filter adds the badge
 *
 * To revert: Delete this file
 */
```

## Asset Loading in Child Theme

### JavaScript

Enqueue child-specific scripts in `functions.php`:

```php
add_action('wp_enqueue_scripts', function() {
    $js_path = codeweber_get_dist_file_path('dist/assets/js/script.js');
    $js_url = codeweber_get_dist_file_url('dist/assets/js/script.js');

    if ($js_url) {
        wp_enqueue_script('my-awesome-site-js', $js_url,
            ['jquery'], codeweber_asset_version($js_path));
    }
});
```

### CSS

Override parent CSS by using higher specificity or enqueuing after parent:

```php
add_action('wp_enqueue_scripts', function() {
    // Child CSS enqueues after parent (higher priority)
    $css_path = codeweber_get_dist_file_path('dist/assets/css/style.css');
    $css_url = codeweber_get_dist_file_url('dist/assets/css/style.css');

    if ($css_url) {
        wp_enqueue_style('my-awesome-site', $css_url,
            ['codeweber-style'], codeweber_asset_version($css_path));
    }
}, 20); // Priority 20 to load after parent (default 10)
```

## Deploying Child Theme

### Development → Staging

```bash
cd /wp-content/themes/my-awesome-site

# Build production assets
npm run build

# Commit to git
git add dist/
git commit -m "Production build"
git push origin main
```

### Staging → Production

Pull on production server:

```bash
cd /wp-content/themes/my-awesome-site
git pull origin main
npm install  # Install dependencies if needed
```

Since `dist/` is committed, no need to rebuild on production.

## Updating Parent Theme

Child themes are protected from parent updates:

1. Update parent theme via WordPress (Themes → Updates)
2. Child theme remains unchanged
3. Override any parent files that need changes
4. Test thoroughly

If parent adds new CPTs or features:
- Use filters to customize behavior
- Don't copy parent files unless necessary

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Child styles not loading | Verify `style.css` header has correct `Template:` field |
| Functions not executing | Ensure `functions.php` is in child theme root, not subfolder |
| Templates not overriding | Check path matches exactly: `templates/post-cards/staff/default.php` |
| Assets 404 errors | Run `npm run build` to generate `dist/` files |
| Parent updates break child | Use filters instead of copying functions |

## Example: Complete Child Theme

See example in this documentation for reference implementation.

---

**Next Steps**:
- Run `npm start` for development
- See **POST_CARDS_SYSTEM.md** for template customization
- Check **HOOKS_REFERENCE.md** for available filters
- Review **BUILD_SYSTEM.md** for Gulp optimization
