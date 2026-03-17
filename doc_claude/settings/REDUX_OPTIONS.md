# Redux Options Reference

Complete catalog of all theme settings stored in Redux Framework (`redux_demo` option).

---

## Overview

CodeWeber stores all theme settings in **Redux Framework** under the option key `redux_demo`. Settings are organized in sections within the WordPress admin under **Theme Settings** (Appearance → CodeWeber Theme Settings).

**Access in code:**
```php
// Using Codeweber_Options wrapper
$value = Codeweber_Options::get('option_key', 'default_value');

// Or direct Redux
$opts = get_option('redux_demo', []);
$value = $opts['option_key'] ?? 'default_value';

// Check if Redux is ready
if (Codeweber_Options::is_ready()) { ... }
```

---

## Settings Structure

All settings organized by section:

| Section | Purpose | Keys | File |
|---------|---------|------|------|
| Header | Header configuration | `global-header-type`, `custom-header`, ... | `header.php` |
| Page Header | Page header configuration | `global_page_header_type`, `custom_page_header`, ... | `page-header.php` |
| Footer | Footer configuration | `global_footer_type`, `custom-footer`, ... | `footer.php` |
| Theme Style | Colors, fonts, buttons | `opt-select-color-theme`, `opt_button_select_style`, ... | `style.php` |
| Company Details | Company information | `text-about-company`, `juri-country`, ... | `company-details.php` |
| Contacts | Contact information | `e-mail`, `phone_01`, ... | `contacts.php` |
| API Settings | External API keys | `dadata`, `yandexapi`, `smsruapi` | `api.php` |
| Social Networks | Social media links | `facebook`, `twitter`, `instagram`, ... | `socials.php` |
| Yandex Maps | Map configuration | `yandex_map`, `yandex_coordinates`, ... | `yandex-maps-settings.php` |
| Custom Post Types | Per-CPT settings | `single_header_select_*`, `archive_template_select_*`, ... | `cpt-type.php` |

---

## Header Settings

**Section ID:** `header`
**File:** `redux-framework/sample/sections/codeweber/header.php`

### Global Header Type

```php
'global-header-type' => '1' | '2'
// '1' = Base (use built-in header models with styling)
// '2' = Custom (use custom header CPT post)
```

### Base Header Options (when global-header-type = '1')

```php
'header-rounded' => '1' | '2' | '3'
// '1' = rounded (default)
// '2' = rounded-pill
// '3' = none (sharp corners)

'header-color-text' => '1' | '2'
// '1' = Dark text on light background
// '2' = Light text on dark background
// Default: '1'

'header-background' => '1' | '2' | '3'
// '1' = Solid-Color
// '2' = Soft-Color (pastel)
// '3' = Transparent
// Default: '1'

'solid-color-header' => 'primary' | 'secondary' | ... (from colors.json)
// Color for solid background
// Default: 'light'

'soft-color-header' => 'soft-primary' | 'soft-secondary' | ...
// Color for soft (pastel) background
// Default: 'soft-light'

'global-header-model' => '1' | '2' | ... | '9'
// Built-in header design model (not used when global-header-type = '1')
```

### Custom Header (when global-header-type = '2')

```php
'custom-header' => 123  // Post ID of custom header CPT
```

### Per-Post-Type Header (Base Mode Only)

```php
'single_header_select_{post_type}' => 'default' | 'custom_header_id' | ''
// Example: 'single_header_select_staff' => 456
// Override for single posts of this type

'archive_header_select_{post_type}' => 'default' | 'custom_header_id' | ''
// Example: 'archive_header_select_post' => 789
// Override for archive pages of this type
```

### Mobile Menu

```php
'social-icon-type-mobile-menu' => '1' | '2' | '3'
// Icon style in mobile menu: filled | outlined | etc.

'social-button-style-mobile-menu' => '1' | '2' | '3'
// Button style: default | solid | etc.

'social-button-size-mobile-menu' => 'sm' | 'md' | 'lg'
// Icon size

'mobile-menu-background' => 'primary' | 'secondary' | ... (from colors.json)
// Mobile menu background color
// Default: 'white'
```

### Off-Canvas Panel

```php
'global-header-offcanvas-right' => true | false
// Show off-canvas panel (slide-out menu) on right side
// Default: true

'social-icon-type' => '1' | '2' | '3'
// Social icon style in off-canvas

'social-button-style-offcanvas' => '1' | '2' | '3'
// Social button style in off-canvas

'social-button-size-offcanvas' => 'sm' | 'md' | 'lg'
// Social icon size in off-canvas

'sort-offcanvas-right' => [...] (repeater)
// Order of items in right off-canvas (widgets, sidebars, etc.)
```

### Top Bar

```php
'header-topbar-enable' => true | false
// Show header top bar above main navigation
// Default: false
```

---

## Page Header Settings

**Section ID:** `global-page-header`
**File:** `redux-framework/sample/sections/codeweber/page-header.php`

### Global Page Header Type

```php
'global_page_header_type' => '1' | '2'
// '1' = Base (use built-in page header models)
// '2' = Custom (use custom page-header CPT post)
// Default: '1'
```

### Base Page Header Options (when global_page_header_type = '1')

```php
'global_page_header_model' => '1' | '2' | ... | '9'
// Built-in page header design
// Files: templates/pageheader/pageheader-{N}.php
// Default: '1'

'global-page-header-aligns' => '1' | '2' | '3'
// '1' = Left aligned
// '2' = Center aligned
// '3' = Right aligned
// Default: '1'
```

### Custom Page Header (when global_page_header_type = '2')

```php
'custom_page_header' => 456  // Post ID of custom page-header CPT
```

### Per-Post-Type Page Header (Base Mode Only)

```php
'single_page_header_select_{post_type}' => 'default' | 'custom_id' | ''
// Example: 'single_page_header_select_staff' => 789
// Override for single posts of this type

'archive_page_header_select_{post_type}' => 'default' | 'custom_id' | ''
// Override for archive pages of this type
```

### Breadcrumbs

```php
'global-page-header-breadcrumb-enable' => true | false
// Show breadcrumb navigation
// Default: true

'global-page-header-breadcrumb-color' => 'dark' | 'light' | ''
// Text color for breadcrumbs
// Default: '' (inherit)

'global-page-header-breadcrumb-bg-color' => 'primary' | 'secondary' | ... (from colors.json)
// Background color for breadcrumb area
// Default: '' (no background)

'global-bredcrumbs-aligns' => '1' | '2' | '3'
// '1' = Left aligned
// '2' = Center aligned
// '3' = Right aligned
// Default: '1'
```

---

## Footer Settings

**Section ID:** `footer`
**File:** `redux-framework/sample/sections/codeweber/footer.php`

### Global Footer Type

```php
'global_footer_type' => '1' | '2'
// '1' = Base (use built-in footer model)
// '2' = Custom (use custom footer CPT post)
// Default: '1'
```

### Base Footer Options (when global_footer_type = '1')

```php
'global-footer-model' => 1
// Built-in footer design (usually just model 1)
// Default: 1

'footer_color_text' => '1' | '2'
// '1' = Dark text
// '2' = Light text
// Default: '1'

'footer_background' => '1' | '2' | '3'
// '1' = Solid-Color
// '2' = Soft-Color
// '3' = Transparent
// Default: '1'

'footer_solid_color' => 'primary' | 'secondary' | ... (from colors.json)
// Solid background color
// Default: 'light'

'footer_soft_color' => 'soft-primary' | 'soft-secondary' | ...
// Soft background color
// Default: 'soft-light'

'footer-logo-color' => 'primary' | 'secondary' | ...
// Logo color
// Default: 'primary'
```

### Custom Footer (when global_footer_type = '2')

```php
'custom-footer' => 789  // Post ID of custom footer CPT
```

### Per-Post-Type Footer (Base Mode Only)

```php
'single_footer_select_{post_type}' => 'default' | 'custom_id' | 'disable'
// Example: 'single_footer_select_testimonials' => 654
// Override for single posts; 'disable' = no footer

'archive_footer_select_{post_type}' => 'default' | 'custom_id' | 'disable'
// Override for archive pages
```

### Social Icons (Footer)

```php
'social-icon-type-footer' => '1' | '2' | '3'
// Icon style

'social-button-style-footer' => '1' | '2' | '3'
// Button styling

'social-button-size-footer' => 'sm' | 'md' | 'lg'
// Icon size
```

### Off-Canvas Footer

```php
'global_footer_offcanvas_right' => true | false
// Enable off-canvas panel in footer
// Default: true

'sort_offcanvas_footer' => [...] (repeater)
// Order of items in footer off-canvas
```

### Footer Top Bar

```php
'footer_bottomobar_enable' => true | false
// Show bottom bar above actual footer
// Default: true
```

---

## Theme Style Settings

**Section ID:** `themestyle`
**File:** `redux-framework/sample/sections/codeweber/style.php`

### Colors & Logo

```php
'opt-select-color-theme' => 'default' | 'color_name' | ...
// Select theme color scheme (from dist/assets/css/colors/)
// Default: 'default'

'opt-dark-logo' => [
    'url' => 'https://example.com/logo-dark.png',
    'id' => 123,  // Attachment ID
    'title' => 'logo-dark.png'
]
// Dark version of logo (media upload)
// Default: empty

'opt-light-logo' => [...]
// Light version of logo (media upload)
// Default: empty
```

### Buttons & UI Elements

```php
'opt_button_select_style' => '1' | '2' | '3' | '4'
// '1' = Pill (fully rounded, elongated)
// '2' = Rounded (moderate border radius)
// '3' = Rounder (larger border radius)
// '4' = Square (no border radius)
// Default: '1'

'opt_card_image_border_radius' => '2' | '3' | '4'
// Border radius for post cards and images
// '2' = Rounded, '3' = Rounder, '4' = Square
// Default: '2'

'opt_form_border_radius' => '1' | '2' | '3' | '4'
// Border radius for form elements (input, textarea, select)
// Default: '1'
```

### Typography

```php
// Font settings (from redux-fonts.php)
'opt_font_body' => [...]
// Font family, size, weight, line-height for body text

'opt_font_heading' => [...]
// Font settings for headings

'opt_font_logo' => [...]
// Font settings for logo text
```

### Gulp Build Configuration

```php
// Theme Gulp settings (from theme_gulp.php)
'gulp_build_path' => '/dist'
// Output directory for compiled assets

'gulp_src_scss' => '/src/scss'
// Source SCSS directory

'gulp_use_browsersync' => true | false
// Enable BrowserSync during development
```

---

## Company Details

**Section ID:** `company-details`
**File:** `redux-framework/sample/sections/codeweber/company-details.php`

### About Company

```php
'text-about-company' => 'Company description text...'
// Company description (textarea)
// Shortcode: [redux_option key="text-about-company"]
```

### Legal Address (Юридический адрес)

```php
'juri-country' => 'Россия'      // Country
'juri-region' => 'Краснодарский край'   // Region/State
'juri-city' => 'Краснодар'      // City
'juri-street' => 'ул. Ленина'   // Street
'juri-house' => '123'           // House number
'juri-office' => 'офис 45'      // Office/suite
'juri-postal' => '350000'       // Postal code
```

**Shortcode:** `[address type="juri"]` or full format: `[address type="juri" separator=", " fallback="Address not set"]`

### Actual Address (Фактический адрес)

```php
'fact-country' => '...'  // Same structure as legal address
'fact-region' => '...'
'fact-city' => '...'
'fact-street' => '...'
'fact-house' => '...'
'fact-office' => '...'
'fact-postal' => '...'
```

**Shortcode:** `[address]` or `[address type="fact"]`

---

## Contacts

**Section ID:** `contacts`
**File:** `redux-framework/sample/sections/codeweber/contacts.php`

### Email

```php
'e-mail' => 'contact@example.com'
// Main contact email
// Shortcode: [get_contact field="e-mail"]
// Link: [get_contact field="e-mail" type="link"]
// Styled link: [get_contact field="e-mail" type="link" class="custom-class"]
// Default: 'test@mail.com'
```

### Phone Numbers

```php
'phone_01' => '+7(495)000-00-00'  // Primary phone
'phone_02' => '+7(495)000-00-00'  // Secondary phone
'phone_03' => '+7(495)000-00-00'  // Tertiary phone
'phone_04' => '+7(495)000-00-00'  // Quaternary phone
'phone_05' => '+7(495)000-00-00'  // Quinary phone
```

**Format:** +7(495)XXX-XX-XX, 8(800)XXX-XX-XX, or simple XXX
**Shortcode:** `[get_contact field="phone_01"]`
**Link:** `[get_contact field="phone_01" type="link"]`

### Social Media Profiles

```php
'skype' => 'contact@example.com'
'viber' => '+7(495)000-00-00'
'whatsapp' => '+7(495)000-00-00'
'telegram' => '@mycompany'
'facebook' => 'https://facebook.com/mycompany'
'instagram' => 'https://instagram.com/mycompany'
'twitter' => 'https://twitter.com/mycompany'
'linkedin' => 'https://linkedin.com/company/mycompany'
'youtube' => 'https://youtube.com/channel/...'
'tiktok' => 'https://tiktok.com/@mycompany'
```

**Shortcode:** `[get_contact field="skype"]`

---

## API Settings

**Section ID:** `api`
**File:** `redux-framework/sample/sections/codeweber/api.php`

### DaData (Address Suggestions)

```php
'dadata_enabled' => true | false
// Enable/disable DaData service integration
// Default: false

'dadata' => 'API_KEY_HERE'
// DaData API key (from dadata.ru)

'dadata_secret' => 'SECRET_KEY_HERE'
// DaData secret key

'dadata_scenarios' => 'address'
// Scenario: address, email, phone, name, bank, party
// Default: 'address'
```

**Use case:** Address field auto-completion in forms

### Yandex API

```php
'yandexapi' => 'API_KEY_HERE'
// Yandex API key (for Yandex Maps, Geocoding, etc.)
```

### SMS.ru API

```php
'smsruapi' => 'API_KEY_HERE'
// SMS.ru API key (for sending SMS via SMS.ru service)
```

---

## Social Networks

**Section ID:** `socials`
**File:** `redux-framework/sample/sections/codeweber/socials.php`

```php
'facebook' => 'https://facebook.com/mycompany'
'twitter' => 'https://twitter.com/mycompany'
'instagram' => 'https://instagram.com/mycompany'
'linkedin' => 'https://linkedin.com/company/mycompany'
'youtube' => 'https://youtube.com/channel/...'
'tiktok' => 'https://tiktok.com/@mycompany'
'telegram' => 'https://t.me/mycompany'
'viber' => '+7(495)000-00-00'
'whatsapp' => '+7(495)000-00-00'
'pinterest' => 'https://pinterest.com/mycompany'
'vimeo' => 'https://vimeo.com/mycompany'
'github' => 'https://github.com/mycompany'
```

These are used by:
- Header social icons
- Footer social icons
- Social share buttons
- Contact sidebar

---

## Yandex Maps Settings

**Section ID:** `geomap`
**File:** `redux-framework/sample/sections/codeweber/yandex-maps-settings.php`

### Map Display

```php
'yandex_map' => true | false
// Enable/disable Yandex Maps display
// Default: true

'yandex_coordinates' => [55.751244, 37.618423]
// Latitude and longitude for map center
// Default: Moscow coordinates

'yandex_zoom' => 12
// Zoom level (1-19)
// Default: 12

'yandex_address' => 'Moscow, Russia'
// Display address/placemark label
```

### Map Customization

```php
'yandex_map_height' => '400px'  // Container height

'yandex_map_style' => [...]     // Custom map styling

'yandex_placemarks' => [...]    // Custom placemarks/markers
```

---

## Custom Post Type Settings

**Section ID:** Per-CPT (e.g., `cpt_staff`, `cpt_testimonials`)
**File:** `redux-framework/sample/sections/codeweber/cpt-type.php`

### Per-CPT Options (Dynamic)

For each registered CPT, Redux creates options:

```php
// Template selection (for archive.php, single.php files)
'archive_template_select_{post_type}' => 'default' | 'custom_id'
// Which archive template file to use
// Example: 'archive_template_select_staff'

'single_template_select_{post_type}' => 'default' | 'custom_id'
// Which single template file to use
// Example: 'single_template_select_staff'

// Post type display settings
'custom_title_{post_type}' => 'Custom Title'
// Custom post type name (displayed in admin, etc.)
// Example: 'custom_title_testimonials' => 'Client Testimonials'

'custom_subtitle_{post_type}' => 'Short Description'
// Subtitle for this post type

// Sidebar configuration
'sidebar_position_single_{post_type}' => 'none' | 'left' | 'right'
// Sidebar position on single pages
// Default: 'right'

'sidebar_position_archive_{post_type}' => 'none' | 'left' | 'right'
// Sidebar position on archive pages
// Default: 'right'

// Header/Footer per-post-type (see Header/Footer sections above)
'single_header_select_{post_type}' => '...'
'archive_header_select_{post_type}' => '...'
'single_footer_select_{post_type}' => '...'
'archive_footer_select_{post_type}' => '...'
'single_page_header_select_{post_type}' => '...'
'archive_page_header_select_{post_type}' => '...'
```

### Examples

For `staff` CPT:
```php
'archive_template_select_staff' => 'staff_1'
'single_template_select_staff' => 'staff_single_1'
'custom_title_staff' => 'Our Team'
'sidebar_position_single_staff' => 'right'
'single_header_select_staff' => 456  // Custom header for staff
```

For `testimonials` CPT:
```php
'archive_template_select_testimonials' => 'testimonials_2'
'custom_title_testimonials' => 'Client Reviews'
'custom_subtitle_testimonials' => 'What our clients say'
```

---

## Additional Sections

### Legal Settings

**File:** `legal.php`
Legal page configuration, privacy policy links, GDPR consent settings

### Tracking & Metrics

**File:** `tracking-metrics.php`
- Google Analytics tracking IDs
- Yandex Metrica IDs
- Custom pixel codes (Facebook, etc.)
- Heatmap tracking (Hotjar, etc.)

### WooCommerce

**Section ID:** `woocommerce-settings` (parent), `woocommerce-archive` (subsection)
**File:** `redux-framework/sample/sections/codeweber/woocommerce.php`

#### Archive — Default Columns per Breakpoint

```php
'woo_cols_xs' => '1' | '2'          // Mobile < 576px.  Default: '1'
'woo_cols_sm' => '1' | '2'          // SM ≥ 576px.       Default: '2'
'woo_cols_md' => '1' | '2' | '3'    // MD ≥ 768px.       Default: '2'
'woo_cols_lg' => '1' | '2' | '3' | '4'  // LG ≥ 992px.  Default: '3'
'woo_cols_xl' => '1' | '2' | '3' | '4'  // XL ≥ 1200px. Default: '4'
```

Значения применяются в `woocommerce/archive-product.php` как дефолтный Bootstrap-класс сетки:

```php
// Результирующий класс:
"row-cols-{xs} row-cols-sm-{sm} row-cols-md-{md} row-cols-lg-{lg} row-cols-xl-{xl}"
```

Применяется только когда в URL нет параметра `?per_row=N`. При его наличии — используется фиксированный маппинг переключателя (2/3/4 колонки).

#### Archive — остальные настройки

```php
'archive_template_select_product' => 'shop2' | ...
// Шаблон карточки товара (сканирует templates/post-cards/product/)

'woo_show_archive_title' => true | false   // Заголовок над сеткой. Default: false

'woo_shop_load_more' => 'pagination' | 'load_more' | 'both'  // Default: 'pagination'

'woo_show_per_page' => true | false        // Переключатель кол-ва товаров. Default: true
'woo_per_page_values' => '12,24,48'        // Допустимые значения. Default: '12,24,48'

'woo_show_per_row' => true | false         // Переключатель колонок. Default: true
'woo_per_row_values' => ['2'=>'1','3'=>'1','4'=>'1']  // Доступные варианты

'woo_show_ordering' => true | false        // Сортировка. Default: true
'woo_ordering_options' => [...]            // Варианты сортировки
```

### Email (SMTP)

**File:** `smtp.php`
- SMTP server configuration
- Email sender settings
- Mail template options

### Repeater Fields

**File:** `repeater.php`
Examples of repeater field usage for:
- Team members
- Testimonials
- Services
- Portfolio items

### Child Theme Creator

**File:** `childtheme.php`
Interface for creating child themes directly from admin

---

## Accessing Options in Code

### Using Codeweber_Options Class (Recommended)

```php
use Codeweber_Options;

// Get simple value
$email = Codeweber_Options::get('e-mail', 'default@example.com');

// Get nested array value
$header_type = Codeweber_Options::get('global-header-type', '1');

// Get post meta (returns false if not set)
$custom_header = Codeweber_Options::get_post_meta($post_id, 'this-custom-post-header');

// Check if Redux is initialized
if (Codeweber_Options::is_ready()) {
    $options = Codeweber_Options::get('all');
}
```

### Direct Redux Access

```php
global $opt_name;
if (empty($opt_name)) {
    $opt_name = 'redux_demo';
}

if (class_exists('Redux')) {
    $value = Redux::get_option($opt_name, 'option_key');
    $post_meta = Redux::get_post_meta($opt_name, $post_id, 'meta_key');
}
```

### Using WordPress get_option()

```php
$all_options = get_option('redux_demo', []);
$email = $all_options['e-mail'] ?? 'default@example.com';
```

---

## Using Options in Templates

### Shortcodes

```php
// Display Redux option
[redux_option key="text-about-company"]

// Display contact info
[get_contact field="e-mail"]
[get_contact field="phone_01" type="link"]
[get_contact field="skype"]

// Display address
[address]
[address type="juri"]
[address type="fact" separator=", "]
```

### In PHP Templates

```php
<?php
$email = Codeweber_Options::get('e-mail');
$company = Codeweber_Options::get('text-about-company');
$phones = [
    Codeweber_Options::get('phone_01'),
    Codeweber_Options::get('phone_02'),
];
?>

<h2><?php echo esc_html($company); ?></h2>
<a href="mailto:<?php echo esc_attr($email); ?>">
    <?php echo esc_html($email); ?>
</a>
```

### In JavaScript (Frontend)

Redux options are usually not exposed to JS. If needed, use AJAX:

```javascript
fetch('/wp-admin/admin-ajax.php', {
    method: 'POST',
    body: new FormData([
        ['action', 'get_theme_option'],
        ['key', 'phone_01'],
    ])
})
.then(r => r.json())
.then(data => console.log(data.value));
```

---

## Performance Notes

### Caching Recommendations

Redux options are cached by WordPress. On high-traffic sites:

```php
// Cache for 24 hours
$value = wp_cache_remember(
    'theme_option_email',
    DAY_IN_SECONDS,
    function() {
        return Codeweber_Options::get('e-mail');
    }
);
```

### Avoid in Loops

Don't call `Codeweber_Options::get()` inside loops:

```php
// BAD
foreach ($posts as $post) {
    $header = Codeweber_Options::get('custom-header');  // Queries Redux each time
}

// GOOD
$header = Codeweber_Options::get('custom-header');
foreach ($posts as $post) {
    // Use $header
}
```

---

## Common Patterns

### Conditionally Show Elements

```php
<?php
if (Codeweber_Options::get('header-topbar-enable')) {
    get_template_part('parts/header-topbar');
}
?>
```

### Set Default for Missing Option

```php
<?php
$logo_dark = Codeweber_Options::get('opt-dark-logo', []);
$logo_url = $logo_dark['url'] ?? get_template_directory_uri() . '/logo.png';
?>
```

### Build Dynamic Selectors

```php
<?php
$post_types = get_post_types(['public' => true]);
foreach ($post_types as $type) {
    $key = "single_header_select_{$type}";
    $header_id = Codeweber_Options::get($key);
    if ($header_id) {
        echo "Post type '$type' uses header $header_id\n";
    }
}
?>
```

---

## Related Documentation

- **[TEMPLATE_SYSTEM.md](TEMPLATE_SYSTEM.md)** — How options are used for template selection
- **[HOOKS_REFERENCE.md](../api/HOOKS_REFERENCE.md)** — Filter hooks that modify options
- **[CPT_CATALOG.md](../cpt/CPT_CATALOG.md)** — Custom post types used by templates
- **[ADMIN_PANELS.md](ADMIN_PANELS.md)** — Admin interface organization
