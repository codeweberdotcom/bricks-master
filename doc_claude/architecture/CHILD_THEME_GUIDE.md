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

## Step 6: Create SCSS Source Files

Child theme uses the **parent's Gulp** — no separate `gulpfile.js` needed. Gulp detects the active child theme via WordPress and compiles assets into `child-theme/dist/`.

### ⚠️ Critical: style.scss MUST exist in child theme

SASS resolves `@import 'user-variables'` **relative to the current file first** (before checking includePaths).
If the child theme has no `style.scss`, Gulp falls back to the parent's `style.scss`, which resolves `_user-variables.scss` from the **parent's** scss directory — your child's overrides are silently ignored.

**Solution**: create `src/assets/scss/style.scss` in the child theme that mirrors the parent's import chain.

**File**: `my-awesome-site/src/assets/scss/style.scss`

```scss
/*!
Theme Name: my-awesome-site (child of codeweber)
*/

// sassIncludePaths: [child/src/assets/scss, parent/src/assets/scss]
// So @import 'user-variables' resolves to THIS theme's _user-variables.scss

@import "../../../../codeweber/node_modules/bootstrap/scss/functions";

@import "theme-colors";        // from parent via includePaths
@import 'user-variables';      // ← resolves to child's _user-variables.scss (this file's dir)
@import "variables";           // from parent via includePaths

@import "../../../../codeweber/node_modules/bootstrap/scss/variables";
@import "../../../../codeweber/node_modules/bootstrap/scss/variables-dark";
@import "theme/maps";
@import "../../../../codeweber/node_modules/bootstrap/scss/mixins";
@import "../../../../codeweber/node_modules/bootstrap/scss/utilities";

@import "theme/functions";
@import "theme/mixins";
@import "theme/utilities";
@import "theme/root";

@import "bootstrap";
@import "theme/theme";
@import 'user';
```

> **Why copy the full import chain?** Because SCSS `@import` resolution is relative to the file containing the import. If parent's `style.scss` is the entry point, `@import 'user-variables'` finds parent's file. Child's `style.scss` with this same chain makes SASS start in the child directory, so `@import 'user-variables'` finds the child's `_user-variables.scss` first.

### Create _user-variables.scss

**File**: `my-awesome-site/src/assets/scss/_user-variables.scss`

```scss
// IMPORTANT: $primary and $white are NOT available here (defined later in _variables.scss)
// Use $blue instead of $primary, #ffffff instead of $white

$blue: #ff5500;   // → $primary
$body-bg: #ffffff;
$body-color: #1a1a2e;

$font-size-root: 16px;       // parent default: 20px
$font-size-base: 1rem;       // 16px
$font-weight-normal: 400;    // parent default: 500

//START IMPORT FONTS
// @import url('https://fonts.googleapis.com/css2?family=YourFont:wght@300;400;600&display=swap');
//END IMPORT FONTS
```

**Import order**: `_theme-colors.scss` → `_user-variables.scss` → `_variables.scss`

### Run the build

```bash
cd wp-content/themes/codeweber   # Always from PARENT directory
npm run build                    # Gulp auto-detects active child theme
```

Gulp outputs to `my-awesome-site/dist/` automatically.

## Step 7: Activate in WordPress

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
| `_user-variables.scss` ignored | **Child theme MUST have `src/assets/scss/style.scss`** — without it Gulp uses parent's entry point and parent's variables win |
| CSS variables show parent defaults (wrong colors/sizes) | See above — create `style.scss` in child copying the import chain from `hoger` or another child theme |
| `--bs-primary` correct in source but wrong in browser | Redux → Appearance → Color Scheme is set to e.g. "aqua" — it loads `aqua.css` after `style.css` and overrides `--bs-primary`. Set to **Default** (no scheme) to let compiled primary win |
| Functions not executing | Ensure `functions.php` is in child theme root, not subfolder |
| Templates not overriding | Check path matches exactly: `templates/post-cards/staff/default.php` |
| Assets 404 errors | Run `npm run build` from the **parent** theme directory to generate `dist/` |
| Parent updates break child | Use filters instead of copying functions |

## Example: Complete Child Theme

See example in this documentation for reference implementation.

---

**Next Steps**:
- Run `npm start` for development
- See **POST_CARDS_SYSTEM.md** for template customization
- Check **HOOKS_REFERENCE.md** for available filters
- Review **BUILD_SYSTEM.md** for Gulp optimization
