# Template Selection System

Complete guide to how CodeWeber selects and displays header, footer, and page header templates on different pages using Redux Framework settings.

---

## Overview

CodeWeber uses a **hierarchical template selection system** where different page types can have different headers, footers, and page headers. The system supports:

1. **Global templates** – Apply to all pages/posts by default
2. **Per-post-type templates** – Different for staff, testimonials, posts, etc.
3. **Per-page overrides** – Individual pages/posts can override defaults
4. **Base vs Custom modes** – Choose between built-in models or custom post-based designs

The system is powered by **Redux Framework** (stored in `redux_demo` option) and implemented in `header.php`, `footer.php`, and `pageheader.php`.

---

## Architecture

### Entry Points

| File | Purpose | Determines |
|------|---------|-----------|
| `header.php` | Main theme header template | Which header CPT to render |
| `pageheader.php` | Included from page.php | Which page-header CPT to render |
| `footer.php` | Main theme footer template | Which footer CPT to render |
| `pageheader.php` | Called from page content template | Page header display |

### Decision Flow

For each template, the system checks in order:

```
1. Is this page allowed to have a template? (check if disabled/404/homepage)
   ↓
2. Check individual post/page overrides (post meta)
   ↓ (if not found or disabled)
3. Check per-post-type settings (from Redux) based on page type (single/archive)
   ↓ (if not found or disabled)
4. Check global template setting (Base vs Custom mode)
   ↓ (if Custom mode)
5. Use global custom template from Custom Header/Footer CPT
   ↓ (fallback)
6. Use Base models (predefined template models)
```

---

## Redux Settings Structure

All settings stored under `redux_demo` option key. Grouped in sections:

### Settings Header

**File:** `redux-framework/sample/sections/codeweber/header.php`

**Global header type:**
```php
'global-header-type' => '1' | '2'
// '1' = Base (use built-in models)
// '2' = Custom (use custom header CPT)
```

**Base header configuration (when global-header-type = '1'):**
```php
'header-rounded' => '1' | '2' | '3'
// Style: rounded | rounded-pill | none

'header-color-text' => '1' | '2'
// '1' = Dark text on light background
// '2' = Light text on dark background

'header-background' => '1' | '2' | '3'
// '1' = Solid color
// '2' = Soft color (pastel)
// '3' = Transparent

'solid-color-header' => 'primary' | 'secondary' | ... // From colors.json
'soft-color-header' => 'soft-primary' | 'soft-secondary' | ...
```

**Custom header configuration (when global-header-type = '2'):**
```php
'custom-header' => 123  // Post ID of custom header CPT
```

**Per-post-type overrides (only for Base mode):**
```php
'single_header_select_{post_type}' => 'default' | 'custom_header_id'
// For single posts/pages of this type
// Example: 'single_header_select_staff' => 456

'archive_header_select_{post_type}' => 'default' | 'custom_header_id'
// For archive pages of this type
// Example: 'archive_header_select_staff' => 789
```

---

### Settings Page Header

**File:** `redux-framework/sample/sections/codeweber/page-header.php`

**Global page header type:**
```php
'global_page_header_type' => '1' | '2'
// '1' = Base (use built-in models)
// '2' = Custom (use custom page-header CPT)
```

**Base page header configuration (when global_page_header_type = '1'):**
```php
'global_page_header_model' => '1' | '2' | ... | '9'
// Different page header design models (9 available)

'global-page-header-aligns' => '1' | '2' | '3'
// '1' = Left aligned
// '2' = Center aligned
// '3' = Right aligned
```

**Custom page header configuration (when global_page_header_type = '2'):**
```php
'custom_page_header' => 456  // Post ID of custom page-header CPT
```

**Per-post-type overrides (only for Base mode):**
```php
'single_page_header_select_{post_type}' => 'default' | 'custom_pageheader_id'
// For single posts/pages of this type

'archive_page_header_select_{post_type}' => 'default' | 'custom_pageheader_id'
// For archive pages of this type
```

**Breadcrumb configuration:**
```php
'global-page-header-breadcrumb-enable' => '1' | '0'
'global-page-header-breadcrumb-color' => 'dark' | 'light' | ''
'global-page-header-breadcrumb-bg-color' => 'primary' | 'secondary' | ...
'global-bredcrumbs-aligns' => '1' | '2' | '3'  // Left, center, right
```

---

### Settings Footer

Similar structure to header (Redux config in `footer` section):

```php
'global_footer_type' => '1' | '2'
// '1' = Base (use built-in model)
// '2' = Custom (use custom footer CPT)

'global-footer-model' => 1  // Built-in footer model (usually just 1)
'custom-footer' => 789  // Custom footer CPT post ID

'single_footer_select_{post_type}' => ...
'archive_footer_select_{post_type}' => ...
```

---

## Header Selection Logic

Implementation in `header.php` (lines 37-114)

### Step 1: Determine Page Type

```php
$post_type = universal_get_post_type();  // Gets current post type (post, page, staff, etc.)
$post_id = get_the_ID();                 // Gets current post ID
$header_post_id = '';                    // Will hold CPT post ID if using custom header
```

### Step 2: Get Global Settings

```php
$global_header_type = Codeweber_Options::get('global-header-type');
// '1' = Base, '2' = Custom
```

### Step 3: Check Individual Post Override (Post Meta)

```php
$this_header_type = Codeweber_Options::get_post_meta($post_id, 'this-header-type');
// '1' = Use per-post-type setting
// '2' = Disable (don't use any header)
// '3' = Use per-post-type setting (same as '1')
// '4' = Use global Base settings (override per-post-type)
// (empty) = Fallback to per-post-type setting
```

If individual override set to use custom header:
```php
$this_header_post_id = Codeweber_Options::get_post_meta($post_id, 'this-custom-post-header');
// Post ID of custom header for this page
```

### Step 4: Check Per-Post-Type Setting

For single pages/posts:
```php
if (is_single() || is_singular($post_type)) {
    $header_post_id = Codeweber_Options::get('single_header_select_' . $post_type);
    // Example: 'single_header_select_staff' = 456
}
```

For archive pages:
```php
if (is_archive() || is_post_type_archive($post_type)) {
    // Only if global type is NOT Custom
    if ($global_header_type !== '2') {
        $header_post_id = Codeweber_Options::get('archive_header_select_' . $post_type);
    }
}
```

### Step 5: Use Global Custom Header

If no header determined yet and global type = Custom:
```php
if ($global_header_type === '2') {
    $global_custom_header = Codeweber_Options::get('custom-header');
    if (!empty($global_custom_header)) {
        $header_post_id = $global_custom_header;
    }
}
```

### Step 6: Apply Filter

```php
$header_post_id = apply_filters(
    'codeweber_header_post_id',
    $header_post_id,
    [
        'post_type' => $post_type,
        'post_id' => $post_id,
        'is_single' => is_single() || is_singular($post_type),
        'is_archive' => is_archive() || is_post_type_archive($post_type),
        'is_404' => is_404(),
    ]
);
```

Child themes can override header selection through this filter.

### Step 7: Render Header

If `$header_post_id` is set and not 'default' or 'disable':
```php
$header_post = get_post($header_post_id);
setup_postdata($header_post);
the_content();  // Output the header CPT content (Gutenberg blocks, HTML, etc.)
```

Otherwise, use Base mode rendering from `templates/header/` directory.

---

## Page Header Selection Logic

Implementation in `pageheader.php` (lines 2-70)

**Similar to header but with key differences:**

1. **Per-post-type selection keys include "page-header":**
   ```php
   'single_page_header_select_staff' => 456
   'archive_page_header_select_staff' => 789
   ```

2. **Base models are named differently:**
   ```php
   'global_page_header_model' => '1' | '2' | ... | '9'
   // Renders: templates/pageheader/pageheader-{N}.php
   ```

3. **Can be disabled individually:**
   ```php
   'this-page-header-type' === '3' => Don't show page header
   ```

4. **Homepage (front page) never shows page header:**
   ```php
   if (is_front_page()) return;
   ```

---

## Footer Selection Logic

Implementation in `footer.php` (lines 26-78)

**Similar hierarchy but with unique meta keys:**

```php
$global_footer_type = Codeweber_Options::get('global_footer_type');
$global_template_footer = Codeweber_Options::get('global-footer-model');
$global_custom_template_footer = Codeweber_Options::get('custom-footer');

$single_footer_id = Codeweber_Options::get('single_footer_select_' . $post_type);
$archive_footer_id = Codeweber_Options::get('archive_footer_select_' . $post_type);

$footer_for_this_page_bool = Codeweber_Options::get_post_meta($post_id, 'this-post-footer-type');
// '1' = Use per-post-type setting (default)
// '2' = Use custom footer for this post
// '3' = Disable footer for this post

$footer_for_this_page_id = Codeweber_Options::get_post_meta($post_id, 'custom-post-footer');
// Post ID of custom footer for this post
```

**Key difference:** Footer can be completely disabled per-post, or use different CPT per page.

---

## Available Base Models

### Header Models (Base Mode)

Template files in `templates/header/`:

| Model | File | Description |
|-------|------|-------------|
| 1 | `header-classic.php` | Classic horizontal navigation |
| 2 | `header-fancy.php` | Fancy navigation with styling |
| 3 | `header-center-logo.php` | Logo centered with nav on sides |
| 4 | `header-fancy-center-logo.php` | Fancy variant with centered logo |
| 5 | `header-extended.php` | Extended header with more space |
| 6 | `header-extended-center-logo.php` | Extended with centered logo |
| + Others | Various | Additional variants |

Total: **9 header variants**

### Page Header Models (Base Mode)

Template files in `templates/pageheader/`:

| Model | File | Description |
|-------|------|-------------|
| 1 | `pageheader-1.php` | Simple page header |
| 2 | `pageheader-2.php` | With background image |
| 3 | `pageheader-3.php` | With breadcrumbs |
| ... | ... | ... |
| 9 | `pageheader-9.php` | Advanced layout |

Total: **9 page header variants**

### Footer Models (Base Mode)

Usually just **1 default footer model** (can be styled via Redux options).

---

## Custom Post Types

Three special CPTs used for template storage:

### `header` CPT

**File:** `functions/cpt/cpt-header.php`

- Stores custom header designs
- Can be built with Gutenberg blocks
- Contains full HTML/block markup
- Title: Header name/label

**Usage:** Selected in Redux `custom-header` option

**Admin page:** `wp-admin/edit.php?post_type=header`

### `page-header` CPT

**File:** `functions/cpt/cpt-page-header.php`

- Stores custom page header designs
- Used for breadcrumb areas, title sections, etc.
- Selected in Redux `custom_page_header` option

**Admin page:** `wp-admin/edit.php?post_type=page-header`

### `footer` CPT

**File:** `functions/cpt/cpt-footer.php`

- Stores custom footer designs
- Can be built with Gutenberg blocks
- Selected in Redux `custom-footer` option

**Admin page:** `wp-admin/edit.php?post_type=footer`

---

## Using `Codeweber_Options` Class

Centralized helper for accessing Redux settings.

**File:** `functions/class-codeweber-options.php`

### Getting Settings

```php
use Codeweber_Options;

// Get theme setting with default value
$header_type = Codeweber_Options::get('global-header-type', '1');

// Get post meta (returns false if not set)
$custom_header = Codeweber_Options::get_post_meta($post_id, 'this-custom-post-header');

// Check if Redux is ready
if (Codeweber_Options::is_ready()) {
    // Safe to use get() calls
}
```

### Common Settings Keys

```php
// Header
Codeweber_Options::get('global-header-type')        // '1' or '2'
Codeweber_Options::get('custom-header')             // Header CPT post ID
Codeweber_Options::get('single_header_select_staff') // Staff single header
Codeweber_Options::get('archive_header_select_post') // Post archive header

// Page Header
Codeweber_Options::get('global_page_header_type')       // '1' or '2'
Codeweber_Options::get('global_page_header_model')      // Model number 1-9
Codeweber_Options::get('custom_page_header')            // PageHeader CPT post ID

// Footer
Codeweber_Options::get('global_footer_type')        // '1' or '2'
Codeweber_Options::get('custom-footer')             // Footer CPT post ID
Codeweber_Options::get('single_footer_select_staff') // Staff single footer
```

---

## Practical Examples

### Example 1: Different Staff Header

Display custom header only for Staff post type:

1. **Create custom header:**
   - Go to `wp-admin/edit.php?post_type=header`
   - Click "Add New"
   - Design with blocks or HTML
   - Publish → Note the Post ID (e.g., 456)

2. **Configure in Redux:**
   - Go to Theme Settings → Header
   - Set "Header Type" to "Base"
   - Go to subsection "Per Post Type" (if available) or use post meta
   - Set "Single Header for Staff" to post ID 456

3. **Result:** All staff single pages use this custom header

### Example 2: Override Homepage Header

Disable header on homepage, use custom header on posts:

1. **In header.php**, the homepage check:
   ```php
   if (is_front_page()) {
       // No page header on homepage
   }
   ```

2. **To customize:** Use child theme filter:
   ```php
   add_filter('codeweber_header_post_id', function($header_id, $context) {
       if (is_front_page()) {
           return 'homepage_custom_header_id';
       }
       return $header_id;
   }, 10, 2);
   ```

### Example 3: Different Footer for Testimonials

1. **Create custom footer:**
   - `wp-admin/edit.php?post_type=footer`
   - Publish with ID 789

2. **Set per-post-type:**
   - Theme Settings → Footer
   - Set "Single Footer for Testimonials" to 789

3. **Or override per-post:**
   - Edit testimonial post
   - Scroll to "Footer" metabox
   - Select custom footer

### Example 4: Switch Global Template Mode

Change from Base models to Custom for entire site:

1. **In Theme Settings:**
   - Header section: Change "Header Type" to "Custom"
   - Select custom header from dropdown
   - All pages now use this one header

2. **Or in code:**
   ```php
   // After initial setup, update Redux
   $opts = get_option('redux_demo', []);
   $opts['global-header-type'] = '2';  // Custom mode
   $opts['custom-header'] = 456;        // Custom header CPT ID
   update_option('redux_demo', $opts);
   ```

---

## Customizing via Filters

### Filter: `codeweber_header_post_id`

Override header selection for specific page types.

**Parameters:**
```php
apply_filters(
    'codeweber_header_post_id',
    $header_post_id,  // Current selected header ID or empty
    [
        'post_type' => 'post',      // Current post type
        'post_id' => 123,            // Current post ID
        'is_single' => true,         // Is single post/page
        'is_archive' => false,       // Is archive
        'is_404' => false,           // Is 404 page
    ]
);
```

**Example:** Use different header for featured posts:

```php
add_filter('codeweber_header_post_id', function($header_id, $context) {
    // If post has 'featured' category, use special header
    if ($context['is_single'] && has_term('featured', 'category', $context['post_id'])) {
        return 999;  // ID of featured header CPT
    }
    return $header_id;
}, 10, 2);
```

---

## Post Meta Fields

Individual posts can override template selection through post meta:

### Header Meta

| Meta Key | Values | Purpose |
|----------|--------|---------|
| `this-header-type` | '1', '2', '3', '4' | Override mode |
| `this-custom-post-header` | Post ID | Custom header for this post |

**Values for `this-header-type`:**
- `'1'` or `'4'`: Use base settings (per-post-type or global)
- `'2'`: Use custom header from `this-custom-post-header`
- `'3'`: Disable header for this post

### Page Header Meta

| Meta Key | Values | Purpose |
|----------|--------|---------|
| `this-page-header-type` | '1', '2', '3' | Override mode |
| `this-custom-page-header` | Post ID | Custom page header |

**Values for `this-page-header-type`:**
- `'1'` (default): Use global/per-post-type setting
- `'2'`: Use custom from `this-custom-page-header`
- `'3'`: Disable page header

### Footer Meta

| Meta Key | Values | Purpose |
|----------|--------|---------|
| `this-post-footer-type` | '1', '2', '3' | Override mode |
| `custom-post-footer` | Post ID | Custom footer |

**Values for `this-post-footer-type`:**
- `'1'`: Use per-post-type setting
- `'2'`: Use custom from `custom-post-footer`
- `'3'`: Disable footer

---

## Admin Meta Boxes

Users can configure templates per-post through Redux metaboxes in WordPress admin.

**Files:**
- `redux-framework/sample/metaboxes.php` – Defines metabox fields

**Displayed on:**
- Edit post/page screens
- Below main editor

**Fields include:**
- Header type selector (dropdown: Base/Custom/Disable)
- Custom header picker (if Custom selected)
- Same for page header and footer

---

## Disabling Templates

### Disable Header on Specific Posts

1. **Via admin:** Edit post → Header metabox → Select "Disable"
2. **Via code:**
   ```php
   update_post_meta($post_id, 'this-header-type', '3');
   ```
3. **Via filter:**
   ```php
   add_filter('codeweber_header_post_id', function($id, $ctx) {
       if ($ctx['post_id'] === 123) {  // Specific post
           return 'disable';
       }
       return $id;
   }, 10, 2);
   ```

### Disable Footer for Archive

1. In Theme Settings → Footer
2. Set "Archive Footer for {post_type}" to "Disable"

---

## When Templates Are NOT Used

Templates are skipped in these cases:

| Condition | Why |
|-----------|-----|
| `is_front_page()` | Never shows page header |
| `is_404()` | Always uses base model |
| `is_home()` | Blog page special handling |
| Header disabled | `$header_post_id === 'disable'` |
| Footer disabled | `$footer_for_this_page_bool === '3'` |

---

## Performance Optimization

### Caching Headers

Custom header CPT is rendered via `the_content()`, which triggers all filters and shortcode processing. For high-traffic sites:

1. **Cache custom header HTML:**
   ```php
   $cache_key = 'custom_header_' . $header_post_id;
   $cached = wp_cache_get($cache_key);
   if ($cached) {
       echo $cached;
   } else {
       ob_start();
       the_content();
       $html = ob_get_clean();
       wp_cache_set($cache_key, $html, '', 3600);
       echo $html;
   }
   ```

2. **Use persistent cache (Redis, Memcached):**
   ```php
   wp_cache_set($cache_key, $html, '', 86400);  // Cache 24 hours
   ```

### Reducing Database Queries

- Use `wp_cache_get/set()` for Redux settings
- Avoid calling `Codeweber_Options::get()` repeatedly
- Cache post meta lookups

---

## Debugging

### Check Redux Settings

View current settings:
```php
$opts = get_option('redux_demo', []);
echo '<pre>';
print_r($opts);
echo '</pre>';
```

### Log Template Selection

Add to `header.php`:
```php
if (WP_DEBUG) {
    error_log('Header selected: ' . $header_post_id .
              ', Type: ' . $post_type .
              ', Post: ' . $post_id);
}
```

Check logs in `/wp-content/debug.log`

### Verify Post Meta

```php
$meta = Codeweber_Options::get_post_meta($post_id, 'this-header-type');
var_dump($meta);  // false if not set
```

---

## Related Documentation

- **[HOOKS_REFERENCE.md](../api/HOOKS_REFERENCE.md)** — `codeweber_header_post_id` filter
- **[CPT_CATALOG.md](../cpt/CPT_CATALOG.md)** — Header, Footer, PageHeader CPT details
- **[REDUX_OPTIONS.md](../settings/REDUX_OPTIONS.md)** — Complete Redux options reference
- **[ARCHIVE_SINGLE_PATTERNS.md](ARCHIVE_SINGLE_PATTERNS.md)** — Using templates in archive/single
