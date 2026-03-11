# Theme Architecture Overview

Complete guide to the CodeWeber theme's design, structure, and how all components fit together.

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         WORDPRESS CORE                          │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│              functions.php (Entry Point)                        │
│              Loads all modules in dependency order              │
└─────────────────────────────────────────────────────────────────┘
                              ↓
        ┌─────────────┬──────────────┬──────────────┐
        ↓             ↓              ↓              ↓
   ┌────────┐  ┌─────────┐  ┌──────────────┐  ┌──────────┐
   │ CPT    │  │ Classes │  │ Integrations │  │ Redux    │
   │ System │  │ & Nav   │  │ & Features   │  │ Framework│
   │        │  │ Walkers │  │              │  │          │
   └────────┘  └─────────┘  └──────────────┘  └──────────┘
        ↓             ↓              ↓              ↓
   ┌─────────────────────────────────────────────────────────┐
   │          Enqueues: Scripts & Styles                     │
   │  (Child-first resolution via brk_get_dist_file_url)   │
   └─────────────────────────────────────────────────────────┘
        ↓
   ┌─────────────────────────────────────────────────────────┐
   │         Templates System                                │
   │    ├─ Single/Archive templates                         │
   │    ├─ Post Card Templates                              │
   │    ├─ Header/Footer/PageHeader selection               │
   │    └─ Components (reusable parts)                       │
   └─────────────────────────────────────────────────────────┘
        ↓
   ┌─────────────────────────────────────────────────────────┐
   │         Frontend Output (Bootstrap 5 HTML)              │
   └─────────────────────────────────────────────────────────┘
```

## Module Load Order (functions.php)

The theme loads modules in this order (see FILE_LOADING_ORDER.md for full dependency graph):

### 1. Custom Post Types (Lines 8-14)
```
cpt-header.php
cpt-footer.php
cpt-page-header.php
cpt-modals.php
cpt-html_blocks.php
cpt-clients.php
cpt-notifications.php
```
Also: faq.php, staff.php, testimonials.php, vacancies.php, offices.php, services.php, projects.php, documents.php, price_list.php, legal.php, price_package.php

### 2. Core Setup (Lines 16-18)
```
setup.php          → Theme support, features
roles.php          → Custom user roles
gulp.php           → Gulp integration for child themes
```

### 3. Plugin Activation (Lines 20-21)
```
TGM Plugin Activation for required plugins
```

### 4. Core Classes & Registry (Lines 23-30)
```
class-codeweber-options.php    → Redux wrapper
enqueues.php                   → Script/style registration
images.php                     → Image sizes
navmenus.php                   → Navigation menus
sidebars.php                   → Sidebar widgets
```

### 5. Helper Libraries (Lines 32-35)
```
Nav walkers (Bootstrap, dropdown, collapse)
Navigation helper (codeweber-nav.php)
```

### 6. Core Functions (Lines 36-39)
```
global.php         → Global helper functions
breadcrumbs.php    → Breadcrumb navigation
cleanup.php        → Theme cleanup hooks
custom.php         → Custom hooks & filters
```

### 7. Integrations (Lines 41-172)
- Contact Form 7 (conditional)
- Admin settings
- AJAX fetch handler
- WooCommerce (conditional)
- DaData integration
- User & translation functions
- Personal Data V2 (GDPR)
- Newsletter subscription
- Image licenses
- AJAX search & Matomo
- CodeWeber Forms
- Yandex Maps
- Modal container & REST API
- Testimonial forms

### 8. Demo Data (Lines 152-162)
```
Optional demo post types (clients, faq, staff, etc.)
```

## Key Classes

### Codeweber_Options

File: `/functions/class-codeweber-options.php`

Wrapper around Redux Framework for clean, safe access to theme options.

**Static Methods:**

```php
// Get a theme option (from Redux)
$value = Codeweber_Options::get('setting_key', 'default_value');

// Get post meta stored in Redux
$meta = Codeweber_Options::get_post_meta($post_id, 'meta_key');

// Check if Redux is initialized
if (Codeweber_Options::is_ready()) {
    // Safe to use Redux
}
```

**Usage Examples:**

```php
// Get header settings
$header_id = Codeweber_Options::get('header_select', false);

// With fallback
$enable_breadcrumbs = Codeweber_Options::get('breadcrumbs_enable', true);

// Post-specific meta
$custom_value = Codeweber_Options::get_post_meta($post_id, 'staff_position');
```

**Option Key**: All options stored with Redux key `redux_demo`

### Nav Walkers

Located in `/functions/lib/`

| Walker | File | Purpose |
|--------|------|---------|
| WP_Bootstrap_Navwalker | class-wp-bootstrap-navwalker.php | Bootstrap 5 menu structure |
| Codeweber_Vertical_Dropdown_Walker | class-codeweber-vertical-dropdown-walker.php | Vertical dropdown menus |
| Codeweber_Menu_Collapse_Walker | class-codeweber-menu-collapse-walker.php | Collapsible menus |

**Usage:**
```php
wp_nav_menu([
    'theme_location' => 'primary',
    'walker'         => new WP_Bootstrap_Navwalker(),
    'depth'          => 2
]);
```

## Directory Structure

### /functions/
Core functionality split by feature:

```
functions/
├── cpt/                        # Custom post type definitions
│   ├── cpt-header.php
│   ├── cpt-footer.php
│   ├── cpt-staff.php          # +17 more CPTs
│   └── redux_cpt.php          # Redux panels for CPTs
├── fetch/                      # AJAX handlers (Codeweber\Functions\Fetch namespace)
│   ├── fetch-handler.php       # Main dispatcher
│   ├── Fetch.php               # Base class
│   ├── getPosts.php
│   ├── loadMoreItems.php
│   └── [more action handlers]
├── integrations/               # Third-party integrations
│   ├── cf7.php                 # Contact Form 7
│   ├── dadata/                 # Address standardization
│   ├── yandex-maps/            # Map integration
│   ├── smsru/                  # SMS sending
│   ├── personal-data-v2/       # GDPR compliance
│   ├── codeweber-forms/        # Custom form system
│   └── [more integrations]
├── admin/                      # WordPress admin pages
│   ├── admin_settings.php      # Main admin page
│   ├── admin_menu.php
│   ├── admin_media.php
│   └── [more admin pages]
├── lib/                        # Helper libraries
│   ├── class-wp-bootstrap-navwalker.php
│   ├── class-codeweber-vertical-dropdown-walker.php
│   ├── comments-helper.php
│   └── [more helpers]
├── bootstrap/                  # Bootstrap 5 helpers
│   ├── bootstrap-single-parts.php
│   ├── bootstrap_pagination.php
│   ├── bootstrap_nav-menu.php
│   └── [more Bootstrap helpers]
├── enqueues.php                # Script/style registration
├── class-codeweber-options.php # Redux wrapper
├── images.php                  # Image sizes
├── sidebars.php                # Sidebar widgets
├── navmenus.php                # Navigation menus
├── global.php                  # Global helpers
├── setup.php                   # Theme setup
└── [other core functions]
```

### /redux-framework/
Redux Framework configuration:

```
redux-framework/
├── redux-core/                 # Redux Framework library
├── sample/
│   └── theme-config.php        # Main Redux configuration
└── theme-settings/
    └── theme-settings.php      # Panel definitions
```

### /templates/
Template files for display:

```
templates/
├── post-cards/                 # Post card templates by type
│   ├── clients/                # Client post card templates
│   ├── staff/                  # Staff post card templates
│   ├── testimonials/           # Testimonial templates
│   ├── offices/                # Office templates
│   ├── post/                   # Generic post templates
│   ├── faq/                    # FAQ templates
│   └── [more post card folders]
├── header/                     # Header template variations
│   ├── classic.php
│   ├── fancy.php
│   ├── extended.php
│   └── [header layouts]
├── components/                 # Reusable template parts
│   ├── button.php
│   ├── social-links.php
│   └── [more components]
└── [root level templates: page.php, single.php, archive.php]
```

### Root Level Template Files

```
wp-content/themes/codeweber/
├── single.php                  # Single post/CPT display
├── archive.php                 # Archive listing
├── page.php                    # Page display
├── archive-{cpt}.php          # CPT-specific archives (archive-staff.php, etc.)
├── single-{cpt}.php           # CPT-specific singles (single-staff.php, etc.)
├── 404.php                    # 404 error page
├── footer.php                 # Global footer
├── header.php                 # Global header
└── [other template files]
```

## Asset Resolution Pattern (brk_get_dist_file_url)

All assets are resolved with **child-first** pattern:

```php
// In functions/enqueues.php
function brk_get_dist_file_url($file_path) {
    if (is_child_theme()) {
        $child_file = get_stylesheet_directory() . '/' . $file_path;
        if (file_exists($child_file)) {
            return get_stylesheet_directory_uri() . '/' . $file_path;
        }
    }

    $parent_file = get_template_directory() . '/' . $file_path;
    if (file_exists($parent_file)) {
        return get_template_directory_uri() . '/' . $file_path;
    }

    return false;
}
```

**Usage:**
```php
// Automatically checks child theme dist/ first, then parent
$css_url = brk_get_dist_file_url('dist/assets/css/style.css');
wp_enqueue_style('codeweber-style', $css_url);
```

**Resolution Order:**
1. Child theme: `/wp-content/themes/child-theme/dist/assets/css/style.css`
2. Parent theme: `/wp-content/themes/codeweber/dist/assets/css/style.css`
3. Return false if not found

## Redux Framework Integration

**Key**: `redux_demo`

Redux Framework is initialized in `functions.php` (line 107-124):

```php
function codeweber_initialize_redux() {
    // Load Redux framework
    require_once get_template_directory() . '/redux-framework/redux-core/framework.php';

    // Load theme configuration
    $opt_name = 'redux_demo';
    require_once get_template_directory() . '/redux-framework/sample/theme-config.php';
    require_once get_template_directory() . '/functions/cpt/redux_cpt.php';
    require_once get_template_directory() . '/redux-framework/theme-settings/theme-settings.php';
}
add_action('after_setup_theme', 'codeweber_initialize_redux', 30);
```

**Accessing Settings:**

```php
// Using the wrapper class (recommended)
$header_id = Codeweber_Options::get('header_select');

// Direct Redux access (if needed)
global $opt_name;
$value = Redux::get_option($opt_name, 'key');
```

## Custom Post Types System

All 18 custom post types are registered in `/functions/cpt/`:

- Core template CPTs: Header, Footer, PageHeader
- Feature CPTs: Modal, HTMLBlock, Notification
- Content CPTs: Client, Staff, FAQ, Testimonial, Vacancy, Office, Service, Project
- Document CPTs: Document, LegalDoc, PriceList, PricePackage

Each CPT file:
1. Registers the post type
2. Adds Redux metabox panels
3. Defines custom columns
4. Sets up filters/actions

See **CPT_CATALOG.md** for complete list.

## Template Selection System

### Header/Footer Selection

Users select header/footer via Redux options. Theme loads selected CPT:

```php
$header_id = Codeweber_Options::get('header_select');
echo wp_kses_post(get_post_field('post_content', $header_id));
```

### PageHeader Selection

Per-page page header via Redux post meta:

```php
$pageheader_id = Codeweber_Options::get_post_meta($post_id, 'page_header_select');
```

### Post Card Template Resolution

Flexible system using:
- Template name prefix (e.g., `client-card`)
- Post type mapping (e.g., `testimonials` → templates/post-cards/testimonials/)
- Filters for custom override

See **POST_CARDS_SYSTEM.md** for details.

## Coding Patterns

### Prefix Convention

All custom functions, filters, and hooks use `codeweber_` prefix:

```php
// Functions
function codeweber_initialize_redux() { ... }
function codeweber_asset_version() { ... }
function cw_render_post_card() { ... }

// Filters
apply_filters('codeweber_header_post_id', $header_id, $context);
apply_filters('codeweber_template_prefix_map', $map);

// Actions
do_action('codeweber_form_after_send', $form_id, $result);
```

### Namespace Usage

AJAX handlers use namespace for organization:

```php
namespace Codeweber\Functions\Fetch;

function handle_fetch_action() { ... }
function getPosts($params) { ... }
```

### Security Pattern

All user input is verified before use:

```php
if (!check_ajax_referer('fetch_action_nonce', 'nonce', false)) {
    wp_send_json_error(['message' => 'Security check failed.'], 403);
}

$actionType = sanitize_text_field(wp_unslash($_POST['actionType'] ?? ''));
$params = json_decode(wp_unslash($_POST['params'] ?? '[]'), true);
```

## Data Flow

### Frontend → Backend (AJAX)

```
Browser JavaScript (fetch_vars)
    ↓
    POST /wp-admin/admin-ajax.php?action=fetch_action
    {
        nonce: fetch_action_nonce,
        actionType: 'getPosts',
        params: {...}
    }
    ↓
handle_fetch_action() in fetch-handler.php
    ↓
    Dispatcher switch statement
    ↓
getPosts() in functions/fetch/getPosts.php
    ↓
    wp_send_json(response)
    ↓
Browser receives JSON response
```

See **AJAX_FETCH_SYSTEM.md** for complete flow.

### Settings → Output

```
Redux Options (redux_demo)
    ↓
    Codeweber_Options::get()
    ↓
    Template logic
    ↓
    Frontend HTML
```

## Performance Considerations

### Asset Versioning

Development vs. production versioning in `codeweber_asset_version()`:

```php
if (defined('WP_DEBUG') && WP_DEBUG && $file_path && file_exists($file_path)) {
    return filemtime($file_path);  // Use file mtime for cache-busting
}
return wp_get_theme()->get('Version');  // Use theme version on production
```

### Child Theme Compilation

When using a child theme, Gulp compiles SCSS directly to child theme's `dist/` folder:

```
Child Theme/
└── dist/
    ├── assets/css/
    ├── assets/js/
    └── assets/images/
```

Asset enqueue checks child first via `brk_get_dist_file_url()`.

## File Paths Summary

| Purpose | Path |
|---------|------|
| Main entry | `wp-content/themes/codeweber/functions.php` |
| CPT definitions | `wp-content/themes/codeweber/functions/cpt/` |
| Options wrapper | `wp-content/themes/codeweber/functions/class-codeweber-options.php` |
| AJAX handlers | `wp-content/themes/codeweber/functions/fetch/fetch-handler.php` |
| Redux config | `wp-content/themes/codeweber/redux-framework/sample/theme-config.php` |
| Templates | `wp-content/themes/codeweber/templates/` |
| Post cards | `wp-content/themes/codeweber/templates/post-cards/` |

---

**Next Steps**:
- Read **FILE_LOADING_ORDER.md** to understand dependencies
- Read **CHILD_THEME_GUIDE.md** if creating a child theme
- See **REDUX_OPTIONS.md** for theme settings reference
