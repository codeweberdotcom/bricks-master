# Hooks Reference

Complete catalog of custom WordPress filters and actions provided by the CodeWeber theme. Use these hooks to customize theme behavior without modifying core files.

---

## Filters

### Page Header Selection

**Hook:** `codeweber_header_post_id`

**File:** `header.php` (line 98)

**Purpose:** Override the header CPT post ID for any page/post.

**Parameters:**
```php
apply_filters(
    'codeweber_header_post_id',
    $header_post_id,    // Current header ID (int or empty string)
    [
        'post_type' => $post_type,  // Current page's post type
        'post_id'   => $post_id,    // Current page's post ID
    ]
);
```

**Example:** Use custom header for specific post type

```php
add_filter('codeweber_header_post_id', function($header_id, $context) {
    if ($context['post_type'] === 'testimonials') {
        return 123; // Use header post #123 for testimonials
    }
    return $header_id;
}, 10, 2);
```

---

### Post Card Template Routing

**Hook:** `codeweber_template_prefix_map`

**File:** `functions/post-card-templates.php` (line ~40)

**Purpose:** Map template prefixes to template directories. Customize where post card templates are loaded from.

**Default Value:**
```php
[
    'card-' => 'templates/post-cards/',
    'testimonial-' => 'templates/testimonials/',
]
```

**Example:** Add custom template prefix

```php
add_filter('codeweber_template_prefix_map', function($map) {
    $map['custom-'] = 'templates/custom-cards/';
    return $map;
});
```

---

**Hook:** `codeweber_post_type_template_map`

**File:** `functions/post-card-templates.php` (line ~50)

**Purpose:** Map post types to custom card template directories.

**Default Value:**
```php
[
    'staff' => 'templates/staff-cards/',
    'testimonials' => 'templates/testimonial-cards/',
]
```

**Example:** Use different template directory for projects

```php
add_filter('codeweber_post_type_template_map', function($map) {
    $map['projects'] = 'templates/case-study-cards/';
    return $map;
});
```

---

**Hook:** `codeweber_post_card_template_dir`

**File:** `functions/post-card-templates.php` (line ~60)

**Purpose:** Final override for post card template directory before loading.

**Parameters:**
```php
apply_filters(
    'codeweber_post_card_template_dir',
    $template_dir,      // Directory path (string)
    $template_name,     // Template name (e.g., 'card-staff')
    $post_type,         // Post type (e.g., 'staff')
    $post_data          // Post object
);
```

**Example:** Conditional template directory

```php
add_filter('codeweber_post_card_template_dir', function($dir, $name, $type, $post) {
    // Use featured template for posts with specific category
    if ($type === 'staff' && has_term('featured', 'departments', $post->ID)) {
        return 'templates/featured-staff/';
    }
    return $dir;
}, 10, 4);
```

---

### Image Size Customization

**Hook:** `codeweber_allowed_image_sizes`

**File:** `functions/images.php` (line ~35)

**Purpose:** Customize available image sizes for featured images.

**Parameters:**
```php
apply_filters(
    'codeweber_allowed_image_sizes',
    $default_sizes,  // Array of default sizes
    $post_type,      // Current post type
    $post_id         // Current post ID
);
```

**Example:** Add custom image size for testimonials

```php
add_filter('codeweber_allowed_image_sizes', function($sizes, $type, $id) {
    if ($type === 'testimonials') {
        $sizes[] = 'custom_testimonial_large';
    }
    return $sizes;
}, 10, 3);
```

---

**Hook:** `codeweber_allowed_image_sizes_{post_type}`

**File:** `functions/images.php` (line ~45)

**Purpose:** Post-type-specific image size filter.

**Example:** Override sizes only for staff CPT

```php
add_filter('codeweber_allowed_image_sizes_staff', function($sizes, $id) {
    return ['codeweber_staff_large', 'codeweber_staff_small'];
}, 10, 2);
```

---

**Hook:** `codeweber_allowed_image_sizes_default`

**File:** `functions/images.php` (line ~50)

**Purpose:** Filter image sizes for post types not explicitly configured.

---

### Pagination

**Hook:** `codeweber_posts_pagination`

**File:** `functions/global.php` (line ~80)

**Purpose:** Customize pagination HTML output.

**Parameters:**
```php
apply_filters(
    'codeweber_posts_pagination',
    $pagination_html,  // HTML string
    $args              // Array of pagination args
);
```

**Example:** Add custom CSS class to pagination

```php
add_filter('codeweber_posts_pagination', function($html, $args) {
    return str_replace('pagination', 'pagination custom-pagination', $html);
}, 10, 2);
```

---

### vCard Generation

**Hook:** `codeweber_vcard_version`

**File:** `functions/qr-code.php` (line ~85)

**Purpose:** Set vCard version (3.0 or 4.0) for staff contact export.

**Parameters:**
```php
apply_filters(
    'codeweber_vcard_version',
    '4.0',        // Default version
    $for_qrcode   // Boolean: true if generating for QR code
);
```

**Example:** Use vCard 3.0 for wider compatibility

```php
add_filter('codeweber_vcard_version', function($version, $for_qrcode) {
    return '3.0'; // Older clients might not support 4.0
}, 10, 2);
```

---

### Form Consent Labels

**Hook:** `codeweber_forms_custom_consent_labels`

**File:** `functions/integrations/cf7-consents-panel.php` (line ~120)

**Purpose:** Override default consent checkbox labels in forms.

**Default Value:**
```php
[
    'consent_marketing' => 'I consent to marketing emails',
    'consent_gdpr'      => 'I accept the privacy policy',
    'consent_terms'     => 'I agree to the terms of service',
]
```

**Example:** Add custom consent type

```php
add_filter('codeweber_forms_custom_consent_labels', function($labels) {
    $labels['consent_data_processing'] = 'I allow processing of my data';
    return $labels;
});
```

---

### Social Share Networks

**Hook:** `codeweber_share_networks`

**File:** `functions/bootstrap/bootstrap_share-page.php` (line ~45)

**Purpose:** Customize available social networks for share buttons.

**Parameters:**
```php
apply_filters(
    'codeweber_share_networks',
    $networks,  // Array of networks (name => config)
    $region     // User's region
);
```

**Default Networks:**
```php
[
    'facebook' => [...],
    'twitter' => [...],
    'linkedin' => [...],
    'whatsapp' => [...],
]
```

**Example:** Add custom sharing network

```php
add_filter('codeweber_share_networks', function($networks, $region) {
    $networks['telegram'] = [
        'name' => 'Telegram',
        'icon' => 'bi-telegram',
        'url'  => 'https://t.me/share/url?url=',
    ];
    return $networks;
}, 10, 2);
```

---

### Document Tabulator Options

**Hook:** `codeweber_documents_tabulator_options`

**File:** `functions/cpt/cpt-documents.php` (line ~180)

**Purpose:** Customize Tabulator.js table options for documents display.

**Parameters:**
```php
apply_filters(
    'codeweber_documents_tabulator_options',
    $default_options  // Array of Tabulator config
);
```

**Example:** Customize table columns

```php
add_filter('codeweber_documents_tabulator_options', function($options) {
    $options['layout'] = 'fitDataTable';
    $options['height'] = '500px';
    return $options;
});
```

---

### WooCommerce Dashboard Cards

**Hook:** `codeweber_my_account_dashboard_card_items`

**File:** `functions/woocommerce.php` (line ~95)

**Purpose:** Customize dashboard cards on WooCommerce My Account page.

**Parameters:**
```php
apply_filters(
    'codeweber_my_account_dashboard_card_items',
    $card_items  // Array of card definitions
);
```

**Example:** Hide specific dashboard card

```php
add_filter('codeweber_my_account_dashboard_card_items', function($items) {
    unset($items['my-downloads']);
    return $items;
});
```

---

### Schema.org JSON-LD Graph

**Hook:** `codeweber_schema_graph`

**File:** `functions/seo/seo-schema.php`

**Purpose:** Extend the JSON-LD `@graph` array with custom Schema.org nodes. All CPT-specific schemas use this filter.

**Parameters:**
```php
apply_filters(
    'codeweber_schema_graph',
    $graph    // array — current @graph nodes (WebSite, Organization, BreadcrumbList, WebPage)
);
```

**Example:** Add custom schema for a new CPT
```php
add_filter( 'codeweber_schema_graph', function ( array $graph ): array {
    if ( ! is_singular( 'my_cpt' ) ) {
        return $graph;
    }

    $graph[] = [
        '@type' => 'Thing',
        '@id'   => get_permalink() . '#thing',
        'name'  => get_the_title(),
    ];

    return $graph;
} );
```

**Built-in CPT filters using this hook:**
- `schema-article.php` → Article (post)
- `schema-event.php` → Event (events) — single + archive ItemList
- `schema-staff.php` → Person (staff) — single + archive ItemList
- `schema-vacancy.php` → JobPosting (vacancies) — single + archive ItemList
- `schema-office.php` → LocalBusiness (offices) — single + archive ItemList
- `schema-service.php` → Service (services) — single + archive ItemList
- `schema-testimonial.php` → Review (single) + AggregateRating (archive)
- `schema-faq.php` → FAQPage — single + archive
- `schema-project.php` → CreativeWork (projects) — single + archive ItemList
- `schema-document.php` → DigitalDocument (documents) — single + archive ItemList

**Documentation:** `doc_claude/seo/SCHEMA_MODULE.md`

---

## Actions

### Forms: Lifecycle Hooks

**Hook:** `codeweber_form_before_send`

**File:** `functions/integrations/codeweber-forms/codeweber-forms-hooks.php`

**Purpose:** Runs before form submission is processed.

**Parameters:**
```php
do_action(
    'codeweber_form_before_send',
    $form_id,       // Form post ID
    $form_data,     // Form configuration array
    $fields         // Submitted field values
);
```

**Example:** Validate custom field before submission

```php
add_action('codeweber_form_before_send', function($form_id, $form_data, $fields) {
    if ($form_id === 123 && empty($fields['custom_field'])) {
        throw new Exception('Custom field is required');
    }
}, 10, 3);
```

---

**Hook:** `codeweber_form_after_send`

**File:** `functions/integrations/codeweber-forms/codeweber-forms-hooks.php`

**Purpose:** Runs after form is successfully processed (email sent, data saved).

**Parameters:**
```php
do_action(
    'codeweber_form_after_send',
    $form_id,       // Form post ID
    $form_data,     // Form configuration
    $submission_id  // Database submission ID
);
```

**Example:** Trigger external API after form submission

```php
add_action('codeweber_form_after_send', function($form_id, $form_data, $sub_id) {
    if ($form_id === 123) {
        // Send to CRM, webhook, etc.
        wp_remote_post('https://api.example.com/leads', [
            'body' => json_encode(['submission' => $sub_id])
        ]);
    }
}, 10, 3);
```

---

**Hook:** `codeweber_form_saved`

**File:** `functions/integrations/codeweber-forms/codeweber-forms-hooks.php`

**Purpose:** Runs when form submission is saved to database.

**Parameters:**
```php
do_action(
    'codeweber_form_saved',
    $submission_id,  // Unique submission ID
    $form_id,        // Form post ID
    $form_data       // Form configuration
);
```

---

**Hook:** `codeweber_form_send_error`

**File:** `functions/integrations/codeweber-forms/codeweber-forms-hooks.php`

**Purpose:** Runs when form submission encounters an error.

**Parameters:**
```php
do_action(
    'codeweber_form_send_error',
    $form_id,   // Form post ID
    $form_data, // Form configuration
    $error      // Error message/object
);
```

**Example:** Log form errors

```php
add_action('codeweber_form_send_error', function($form_id, $form_data, $error) {
    error_log("Form $form_id error: " . $error);
}, 10, 3);
```

---

**Hook:** `codeweber_form_opened`

**File:** `functions/integrations/codeweber-forms/codeweber-forms-hooks.php`

**Purpose:** Runs when form is loaded/opened on page.

**Parameters:**
```php
do_action(
    'codeweber_form_opened',
    $form_id  // Form post ID
);
```

**Example:** Track form impressions

```php
add_action('codeweber_form_opened', function($form_id) {
    // Log to analytics, increment counter, etc.
}, 10, 1);
```

---

### Sidebar Display

**Hook:** `codeweber_before_sidebar`

**File:** `sidebar-left.php`, `sidebar-right.php`

**Purpose:** Fires **always** before sidebar content (regardless of whether user added widgets). Used for default CPT sidebar widgets that auto-disable when user adds their own widgets to the widget area.

**Parameters:**
```php
do_action(
    'codeweber_before_sidebar',
    $sidebar_id  // Sidebar identifier (e.g., 'sidebar-1', 'events', 'legal')
);
```

**Built-in listeners (default CPT widgets):**

| Function | CPT | Description |
|----------|-----|-------------|
| `codeweber_sidebar_widget_legal` | `legal` | Navigation list of legal documents |
| `codeweber_sidebar_widget_vacancies` | `vacancies` | Vacancy details card (image, meta, author, map, buttons) |
| `codeweber_sidebar_widget_faq` | `faq` | FAQ categories navigation |
| `codeweber_sidebar_widget_events` | `events` | Event details card (countdown, seats, registration form, map) |

All default widgets check `is_active_sidebar($cpt)` — if user added widgets to the area, the default widget is skipped.

**Example:** Add custom content before sidebar

```php
add_action('codeweber_before_sidebar', function($sidebar_id) {
    if ($sidebar_id === 'staff') {
        echo '<div class="sidebar-notice">Staff Directory</div>';
    }
}, 10, 1);
```

**Disable default widget from child theme:**

```php
remove_action('codeweber_before_sidebar', 'codeweber_sidebar_widget_events');
```

---

**Hook:** `codeweber_after_sidebar`

**File:** `sidebar-left.php`, `sidebar-right.php`

**Purpose:** Fires **always** after sidebar content (regardless of whether user added widgets).

**Parameters:**
```php
do_action(
    'codeweber_after_sidebar',
    $sidebar_id  // Sidebar identifier
);
```

---

### Yandex Maps

**Hook:** `codeweber_yandex_maps_init`

**File:** `functions/integrations/yandex-maps/class-codeweber-yandex-maps.php` (line ~150)

**Purpose:** Runs after Yandex Maps object is initialized on page.

**Parameters:**
```php
do_action(
    'codeweber_yandex_maps_init',
    $maps_object  // Codeweber_Yandex_Maps instance
);
```

**Example:** Add custom placemarks to map

```php
add_action('codeweber_yandex_maps_init', function($maps) {
    // Access map instance and customize
    $maps->add_custom_placemark([
        'coords'  => [55.751244, 37.618423],
        'title'   => 'Custom Location',
        'icon'    => 'custom-icon.png',
    ]);
}, 10, 1);
```

---

## Hook Usage Patterns

### Pattern 1: Filter Hook with Backward Compatibility

Ensure old code still works while adding new functionality:

```php
$result = apply_filters('my_filter', $old_value, $new_param);

// Later, in add_filter callback:
add_filter('my_filter', function($old, $new) {
    // Check if called with new parameter
    if (func_num_args() > 1) {
        // New behavior
    } else {
        // Old behavior
    }
});
```

### Pattern 2: Conditional Action Hook

Only run hooks for specific post types:

```php
do_action("my_action_{$post_type}", $post_id);

// Then listen for specific type:
add_action('my_action_staff', function($post_id) {
    // Only runs for staff posts
}, 10, 1);
```

### Pattern 3: Chaining Filters

Apply multiple filters in sequence:

```php
$value = apply_filters('filter_1', $value);
$value = apply_filters('filter_2', $value);
$value = apply_filters('filter_3', $value);

// Or in callback:
add_filter('filter_1', function($value) {
    return apply_filters('filter_2', $value);
});
```

### Pattern 4: Action Priority

Use priority to control execution order:

```php
// Run first
add_action('hook', 'func_1', 5);

// Run second
add_action('hook', 'func_2', 10);

// Run last
add_action('hook', 'func_3', 99);
```

---

## Adding Custom Hooks

If you're extending the theme, add hooks for child themes to customize:

```php
<?php
// In parent theme:

// Before processing
do_action('myfeature_before_process', $data);

// Filtered data
$data = apply_filters('myfeature_data', $data);

// After processing
do_action('myfeature_after_process', $data);
```

Then in child theme or plugin:

```php
add_action('myfeature_before_process', 'my_preprocess');
add_filter('myfeature_data', 'my_transform');
add_action('myfeature_after_process', 'my_postprocess');
```

---

## Testing Hooks

Use WordPress debugging to verify hooks are firing:

```php
// In wp-config.php or functions.php:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// In your hook callback:
add_filter('codeweber_header_post_id', function($id, $context) {
    error_log('Header hook fired: ' . print_r($context, true));
    return $id;
}, 10, 2);
```

Check logs in `/wp-content/debug.log`

---

## Related Documentation

- **[REST_API_REFERENCE.md](REST_API_REFERENCE.md)** — REST endpoints (alternative to hooks)
- **[AJAX_FETCH_SYSTEM.md](AJAX_FETCH_SYSTEM.md)** — AJAX actions (not WordPress hooks)
- **[CPT_HOW_TO_ADD.md](../cpt/CPT_HOW_TO_ADD.md)** — Hooks for custom post types
- **[CODEWEBER_FORMS.md](../forms/CODEWEBER_FORMS.md)** — Form-specific hooks
