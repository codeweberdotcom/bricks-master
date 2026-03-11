# Post Card Templates System

Complete guide to rendering post cards and custom content through CodeWeber's flexible template system.

---

## Overview

The post card system centralizes how posts and custom content are rendered across the theme. Instead of duplicating code in different places, use `cw_render_post_card()` to render any post type with any template.

**Key benefits:**
- **Single function:** One entry point for all post rendering
- **Template routing:** Smart directory mapping for templates
- **Display control:** Granular settings for what to show (date, categories, comments, etc.)
- **Post-type support:** Special handling for staff, testimonials, clients, and more
- **Backward compatibility:** Automatic fallback for old template names
- **Shortcodes:** Built-in `[cw_blog_posts_slider]` and `[cw_clients]` shortcodes

---

## Core Functions

### `cw_render_post_card()`

Renders a post card HTML using a specific template.

**Signature:**
```php
function cw_render_post_card(
    $post,                 // WP_Post object or post ID
    $template_name = 'default',  // Template name (e.g., 'default', 'card', 'card-content')
    $display_settings = [], // Controls what elements to show
    $template_args = []    // Additional template-specific arguments
): string                   // Returns HTML or empty string if no post
```

**Example usage:**
```php
// Minimal - use default template with all defaults
echo cw_render_post_card($post);

// With custom template
echo cw_render_post_card($post, 'card', [], ['image_size' => 'large']);

// Full control
echo cw_render_post_card($post, 'default', [
    'show_title' => true,
    'show_date' => true,
    'show_category' => true,
    'show_comments' => false,
    'title_length' => 50,    // Truncate title at 50 chars
    'excerpt_length' => 20,  // Show excerpt (0 = hide)
    'title_tag' => 'h3',
    'title_class' => 'custom-title',
], [
    'image_size' => 'codeweber_single',
    'hover_classes' => 'overlay overlay-5',
    'border_radius' => 'rounded-lg',
    'enable_lift' => true,
]);
```

**Return value:**
- HTML string on success
- Empty string if post doesn't exist

---

### `cw_get_post_card_data()`

Extracts data from a post for use in card templates.

**Signature:**
```php
function cw_get_post_card_data(
    $post,                    // WP_Post object or post ID
    $image_size = 'full',     // WordPress image size
    $enable_link = false      // For clients: use Company URL instead of permalink
): array|null                 // Returns standardized data array or null
```

**Returned data (standard posts):**
```php
[
    'id' => 123,                              // Post ID
    'title' => 'Post Title',                  // Post title
    'excerpt' => 'Post excerpt text...',      // Post excerpt
    'link' => 'https://example.com/post/',    // Permalink
    'date' => '15 Mar 2026',                  // Formatted date
    'date_format' => '03/15/2026',            // Alternative date format
    'comments_count' => 5,                    // Number of comments
    'category' => WP_Term,                    // First category term object
    'category_link' => 'https://example.com/category/tech/', // Category URL
    'image_url' => 'https://example.com/image.jpg', // Featured image URL
    'image_alt' => 'Image alt text',          // Image alt text
    'post_type' => 'post',                    // Post type
]
```

**Special data for staff posts:**
```php
[
    // ... standard fields ...
    'name' => 'John',                         // First name from meta
    'surname' => 'Doe',                       // Last name from meta
    'full_name' => 'John Doe',                // Combined name
    'position' => 'Lead Developer',           // Position from meta
    'department' => 'Engineering',            // Department from taxonomy
    'email' => 'john@example.com',            // Email from meta
    'phone' => '+1-555-0123',                 // Phone from meta
    'company' => 'CodeWeber Inc',             // Company name
    'image_url_2x' => '...',                  // High-res image for retina displays
]
```

**Special data for testimonials:**
```php
[
    // ... standard fields ...
    'text' => 'Great product!',               // Testimonial text (from post content)
    'author_name' => 'Jane Smith',            // Author name
    'author_role' => 'CEO',                   // Author role/title
    'company' => 'Tech Startup Inc',          // Author company
    'rating' => 5,                            // Rating 1-5
    'rating_class' => 'five',                 // CSS class for rating display
    'avatar_url' => '...',                    // Author avatar
    'avatar_url_2x' => '...',                 // Retina avatar
]
```

**Special data for clients:**
```php
[
    'id' => 123,
    'title' => 'Client Name',
    'link' => 'https://example.com/client/' | 'https://client-website.com',  // Permalink or Company URL
    'image_url' => 'https://example.com/logo.jpg',
    'image_alt' => 'Logo',
    'post_type' => 'clients',
    // Note: No category, date, or comments for clients
]
```

---

### `cw_get_post_card_display_settings()`

Builds a normalized display settings array with defaults.

**Signature:**
```php
function cw_get_post_card_display_settings($args = []): array
```

**Available settings:**
```php
[
    'show_title' => true,       // bool: Show post title
    'show_date' => true,        // bool: Show publication date
    'show_category' => true,    // bool: Show category/taxonomy
    'show_comments' => true,    // bool: Show comment count
    'title_length' => 0,        // int: Truncate title (0 = no limit)
    'excerpt_length' => 0,      // int: Excerpt length (0 = hide)
    'title_tag' => 'h2',        // string: HTML tag for title (h1-h6, p, div, span)
    'title_class' => '',        // string: Additional CSS class for title
]
```

**Example:**
```php
$settings = cw_get_post_card_display_settings([
    'show_date' => false,
    'title_length' => 40,
    'title_tag' => 'h3',
]);
// Result: settings array with defaults + overrides
```

---

## Template Directory Structure

Templates are organized by post type and prefix:

```
templates/post-cards/
├── helpers.php                      # Shared helper functions
├── post/                            # Default post type templates
│   ├── default.php                 # Standard card layout
│   ├── card.php                    # Card variant
│   ├── card-content.php            # Content-only card
│   ├── slider.php                  # Swiper slider card
│   ├── default-clickable.php       # Clickable card with lift effect
│   └── overlay-5.php               # Overlay hover effect
├── staff/
│   ├── default.php                 # Default staff card
│   ├── card.php                    # Card variant
│   ├── circle.php                  # Circular profile
│   ├── circle_center.php           # Centered circular profile
│   └── circle_center_alt.php       # Alternative centered variant
├── testimonials/
│   ├── default.php                 # Default testimonial
│   ├── card.php                    # Card variant
│   ├── blockquote.php              # Blockquote with rating
│   └── icon.php                    # Icon-based testimonial
├── clients/
│   ├── simple.php                  # Logo only
│   ├── grid.php                    # Logo in grid
│   └── card.php                    # Card variant
├── documents/
│   ├── card.php
│   └── card_download.php
├── vacancies/
│   ├── card.php
│   ├── grid-card.php
│   ├── list-item.php
│   ├── style3-card.php
│   ├── style4-card.php
│   ├── style5-card.php
│   └── style6-card.php
├── offices/
│   └── card.php
└── faq/
    └── default.php
```

---

## Template Routing Logic

`cw_render_post_card()` determines which template file to use through a smart routing system:

### Step 1: Check Template Name Prefix

If the template name starts with a known prefix, extract it:

```php
// Maps prefixes to directories
'client-' => 'clients',
'testimonial-' => 'testimonials',
'document-' => 'documents',
'faq-' => 'faq',
'staff-' => 'staff',
'office-' => 'offices',
'vacancy-' => 'post',  // Vacancies use post templates
```

**Example:**
- `client-simple` → `clients/simple.php`
- `testimonial-blockquote` → `testimonials/blockquote.php`
- `staff-circle` → `staff/circle.php`

### Step 2: Check Post Type Mapping

If no prefix matched, check if the post type has a mapping:

```php
// Maps post types to directories
'clients' => 'clients',
'testimonials' => 'testimonials',
'documents' => 'documents',
'faq' => 'faq',
'staff' => 'staff',
'offices' => 'offices',
'vacancies' => 'post',
```

**Example:**
- `get_post_type() === 'staff'` → Use `staff/` directory

### Step 3: Apply Filter Override

Use the `codeweber_post_card_template_dir` filter for dynamic overrides:

```php
$template_dir = apply_filters(
    'codeweber_post_card_template_dir',
    $template_dir,     // Current directory (from prefix or post type)
    $template_name,    // Template name (e.g., 'card-staff')
    $post_type,        // Post type (e.g., 'staff')
    $post_data         // Post data array
);
```

### Step 4: Fallback Chain

If template not found, try in this order:

1. New location: `templates/post-cards/{dir}/{name}.php`
2. Old location (backward compat): `templates/post-cards/{name}.php`
3. Default template: `templates/post-cards/{dir}/default.php`
4. Ultimate fallback: `templates/post-cards/post/default.php`

---

## Hooks for Customization

### Filter: `codeweber_template_prefix_map`

Customize or add template prefixes.

**File:** `functions/post-card-templates.php` (~line 42)

**Parameters:**
```php
apply_filters(
    'codeweber_template_prefix_map',
    [
        'client-' => 'clients',
        'testimonial-' => 'testimonials',
        // ... defaults ...
    ]
);
```

**Example:** Add custom prefix for promo cards:

```php
add_filter('codeweber_template_prefix_map', function($map) {
    $map['promo-'] = 'promos';  // Template names starting with 'promo-' use promos/ directory
    return $map;
});
```

### Filter: `codeweber_post_type_template_map`

Customize post type to directory mapping.

**File:** `functions/post-card-templates.php` (~line 53)

**Parameters:**
```php
apply_filters(
    'codeweber_post_type_template_map',
    [
        'clients' => 'clients',
        'testimonials' => 'testimonials',
        // ... defaults ...
    ]
);
```

**Example:** Use custom template dir for projects:

```php
add_filter('codeweber_post_type_template_map', function($map) {
    $map['projects'] = 'projects';  // projects post type uses projects/ directory
    return $map;
});
```

### Filter: `codeweber_post_card_template_dir`

Final override for template directory before loading.

**File:** `functions/post-card-templates.php` (~line 81)

**Parameters:**
```php
apply_filters(
    'codeweber_post_card_template_dir',
    $template_dir,   // Current directory
    $template_name,  // Template name (e.g., 'card-staff')
    $post_type,      // Post type (e.g., 'staff')
    $post_data       // Post data
);
```

**Example:** Use featured template for important staff:

```php
add_filter('codeweber_post_card_template_dir', function($dir, $name, $type, $post) {
    if ($type === 'staff' && has_term('featured', 'departments', $post->ID)) {
        return 'staff-featured';  // Use staff-featured/ directory for featured staff
    }
    return $dir;
}, 10, 4);
```

---

## Built-in Shortcodes

### `[cw_blog_posts_slider]`

Displays blog posts in a slider or grid layout.

**File:** `functions/post-card-templates.php` (~line 327)

**Attributes:**
```php
[cw_blog_posts_slider
    posts_per_page="4"           // Number of posts (default: 4)
    category="tech,design"       // Category slugs (comma-separated)
    tag="wordpress,coding"       // Tag slugs (comma-separated)
    post_type="post"             // Post type (default: post)
    orderby="date"               // Order by: date, title, menu_order, rand
    order="DESC"                 // ASC or DESC
    image_size="codeweber_single" // Image size from add_image_size()
    excerpt_length="20"          // Excerpt word count (0 = no excerpt)
    title_length="0"             // Title char limit (0 = no limit)
    template="default"           // Template: default, card, slider, overlay-5, default-clickable
    show_title="true"            // Show post title
    show_date="true"             // Show publication date
    show_category="true"         // Show category
    show_comments="true"         // Show comment count
    title_tag="h2"               // HTML tag for title
    title_class=""               // Additional CSS class
    enable_lift="false"          // Add lift effect on hover
    enable_hover_scale="false"   // Add scale effect on hover
    layout="swiper"              // swiper (carousel) or grid
    gap="30"                     // Gap between items in pixels (grid only)
    items_xl="3"                 // Items on extra-large screens
    items_lg="3"                 // Items on large screens
    items_md="2"                 // Items on medium screens
    items_sm="2"                 // Items on small screens
    items_xs="1"                 // Items on extra-small screens
    items_xxs="1"                // Items on ultra-small screens
    margin="30"                  // Margin between slides (swiper only)
    dots="true"                  // Show pagination dots (swiper only)
    nav="false"                  // Show prev/next arrows (swiper only)
    autoplay="false"             // Auto-play carousel (swiper only)
    loop="false"                 // Loop carousel (swiper only)
]
```

**Examples:**

Display 4 tech posts in a slider:
```php
[cw_blog_posts_slider posts_per_page="4" category="tech" template="slider"]
```

Display all posts in a 3-column grid:
```php
[cw_blog_posts_slider posts_per_page="-1" layout="grid" items_md="3" items_lg="4"]
```

Auto-playing carousel with custom styling:
```php
[cw_blog_posts_slider
    posts_per_page="6"
    template="default-clickable"
    layout="swiper"
    items_lg="3"
    items_md="2"
    autoplay="true"
    loop="true"
    show_date="false"
    enable_lift="true"
]
```

---

### `[cw_clients]`

Displays client logos in a slider or grid.

**File:** `functions/post-card-templates.php` (~line 378)

**Attributes:**
```php
[cw_clients
    posts_per_page="-1"          // Number of clients (-1 = all, default: -1)
    orderby="menu_order"         // Order by: menu_order, title, date, rand
    order="ASC"                  // ASC or DESC
    template="client-simple"     // Template: client-simple, client-grid, client-card
    image_size="codeweber_clients_300-200" // Image size
    layout="swiper"              // swiper, grid, grid-cards
    enable_link="false"          // Link to client website (uses Company URL if available)

    // Swiper layout options
    items_xl="7"
    items_lg="6"
    items_md="4"
    items_sm="2"
    items_xs="2"
    margin="0"
    dots="false"
    nav="false"
    autoplay="false"
    loop="true"

    // Grid layout options
    columns_xl="4"
    columns_md="2"
    gap="12"                     // Gap in Bootstrap spacing units
]
```

**Examples:**

Display clients in a carousel:
```php
[cw_clients layout="swiper" items_lg="6" autoplay="true"]
```

Display clients in a 4-column grid:
```php
[cw_clients layout="grid" columns_xl="4" columns_md="2"]
```

Display clients as cards with links to their websites:
```php
[cw_clients template="client-card" layout="grid-cards" enable_link="true"]
```

---

## Writing Custom Templates

### Template File Structure

Template files receive variables in global scope:

```php
<?php
/**
 * Template: My Custom Post Card
 *
 * @param array $post_data Data from cw_get_post_card_data()
 * @param array $display_settings Settings from cw_get_post_card_display_settings()
 * @param array $template_args Additional arguments passed to cw_render_post_card()
 */

if (!isset($post_data) || !$post_data) {
    return;  // Exit if no post data
}

// Normalize settings with defaults
$display = cw_get_post_card_display_settings($display_settings ?? []);
$template_args = wp_parse_args($template_args ?? [], [
    'custom_option' => 'default_value',
]);

// Now build your HTML
?>

<div class="my-card">
    <?php if ($display['show_title'] && $post_data['title']) : ?>
        <h3><?php echo esc_html($post_data['title']); ?></h3>
    <?php endif; ?>

    <?php if ($post_data['image_url']) : ?>
        <img src="<?php echo esc_url($post_data['image_url']); ?>"
             alt="<?php echo esc_attr($post_data['image_alt']); ?>" />
    <?php endif; ?>
</div>
```

### Key Rules

1. **Always check `$post_data` exists** (first line of template)
2. **Normalize settings** using `cw_get_post_card_display_settings()`
3. **Use `wp_parse_args()`** to merge template_args with defaults
4. **Escape all output:**
   - `esc_html()` for text
   - `esc_url()` for URLs
   - `esc_attr()` for HTML attributes
   - `wp_kses_post()` for rich content (text, HTML tags)
5. **Use post data** instead of making direct post queries (already extracted)

### Post Data Variables Reference

All templates have access to:

```php
$post_data['id']                 // int: Post ID
$post_data['title']              // string: Post title
$post_data['link']               // string: Post URL
$post_data['image_url']          // string: Featured image URL
$post_data['image_alt']          // string: Image alt text
$post_data['post_type']          // string: Post type

// Standard posts
$post_data['excerpt']            // string: Excerpt
$post_data['date']               // string: Formatted date
$post_data['comments_count']     // int: Comment count
$post_data['category']           // WP_Term: Category object
$post_data['category_link']      // string: Category URL

// Staff posts
$post_data['name']               // string: First name
$post_data['surname']            // string: Last name
$post_data['full_name']          // string: Full name
$post_data['position']           // string: Job title
$post_data['department']         // string: Department name
$post_data['email']              // string: Email
$post_data['phone']              // string: Phone
$post_data['company']            // string: Company name
$post_data['image_url_2x']       // string: High-res image

// Testimonials
$post_data['text']               // string: Testimonial text (HTML)
$post_data['author_name']        // string: Author name
$post_data['author_role']        // string: Author role
$post_data['rating']             // int: 1-5
$post_data['rating_class']       // string: CSS class (one, two, three, four, five)
$post_data['avatar_url']         // string: Avatar image URL
$post_data['avatar_url_2x']      // string: High-res avatar

// Clients (minimal data)
// Only: id, title, link, image_url, image_alt, post_type
```

### Example: Custom Template for Projects

Create `templates/post-cards/projects/card.php`:

```php
<?php
if (!isset($post_data) || !$post_data) {
    return;
}

$display = cw_get_post_card_display_settings($display_settings ?? []);
$template_args = wp_parse_args($template_args ?? [], [
    'show_technologies' => true,
    'badge_color' => 'primary',
]);
?>

<div class="project-card card shadow-sm">
    <?php if ($post_data['image_url']) : ?>
        <div class="card-img-wrapper overflow-hidden" style="max-height: 300px;">
            <a href="<?php echo esc_url($post_data['link']); ?>">
                <img src="<?php echo esc_url($post_data['image_url']); ?>"
                     alt="<?php echo esc_attr($post_data['image_alt']); ?>"
                     class="card-img-top" />
            </a>
        </div>
    <?php endif; ?>

    <div class="card-body">
        <?php if ($display['show_category'] && $post_data['category']) : ?>
            <span class="badge bg-<?php echo esc_attr($template_args['badge_color']); ?>">
                <?php echo esc_html($post_data['category']->name); ?>
            </span>
        <?php endif; ?>

        <?php if ($display['show_title']) : ?>
            <h4 class="card-title mt-2">
                <a href="<?php echo esc_url($post_data['link']); ?>" class="link-dark">
                    <?php echo esc_html($post_data['title']); ?>
                </a>
            </h4>
        <?php endif; ?>

        <?php if (!empty($post_data['excerpt'])) : ?>
            <p class="card-text text-muted small">
                <?php echo wp_kses_post($post_data['excerpt']); ?>
            </p>
        <?php endif; ?>

        <a href="<?php echo esc_url($post_data['link']); ?>" class="btn btn-sm btn-outline-primary">
            <?php esc_html_e('View Project', 'codeweber'); ?>
        </a>
    </div>
</div>
```

Then use it:

```php
echo cw_render_post_card($post, 'project-card', [
    'show_category' => true,
], [
    'badge_color' => 'info',
    'show_technologies' => true,
]);
```

---

## Usage Examples

### In Archive Templates

Display staff in different layouts based on Redux settings:

```php
<?php
// archive-staff.php

$template_style = get_option('redux_demo')['staff_layout'] ?? 'default';
$staff_query = new WP_Query([
    'post_type' => 'staff',
    'posts_per_page' => 12,
    'orderby' => 'menu_order',
    'order' => 'ASC',
]);

if ($staff_query->have_posts()) {
    echo '<div class="row row-cols-1 row-cols-md-3 g-4">';
    while ($staff_query->have_posts()) {
        $staff_query->the_post();
        echo '<div class="col">';
        echo cw_render_post_card(get_post(), "staff-{$template_style}", [
            'show_title' => true,
            'show_category' => false,  // Hide for staff
        ]);
        echo '</div>';
    }
    echo '</div>';
    wp_reset_postdata();
}
?>
```

### In Gutenberg Blocks

Render custom post cards in block render callback:

```php
// inc/Plugin.php or similar
public function render_post_grid_block($attributes, $content) {
    $args = [
        'post_type' => $attributes['postType'] ?? 'post',
        'posts_per_page' => $attributes['postsPerPage'] ?? 6,
        'orderby' => $attributes['orderBy'] ?? 'date',
        'order' => $attributes['order'] ?? 'DESC',
    ];

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '';
    }

    ob_start();
    echo '<div class="row row-cols-md-3 g-4">';

    while ($query->have_posts()) {
        $query->the_post();
        echo '<div class="col">';
        echo cw_render_post_card(
            get_post(),
            $attributes['template'] ?? 'default',
            [
                'show_date' => $attributes['showDate'] ?? true,
                'show_category' => $attributes['showCategory'] ?? true,
            ],
            [
                'image_size' => $attributes['imageSize'] ?? 'large',
            ]
        );
        echo '</div>';
    }

    echo '</div>';
    wp_reset_postdata();

    return ob_get_clean();
}
```

### In Shortcode Handler

Custom shortcode using the card system:

```php
function my_project_showcase_shortcode($atts) {
    $atts = shortcode_atts([
        'category' => '',
        'count' => 6,
        'template' => 'project-card',
    ], $atts);

    $args = [
        'post_type' => 'projects',
        'posts_per_page' => intval($atts['count']),
        'post_status' => 'publish',
    ];

    if (!empty($atts['category'])) {
        $args['tax_query'] = [[
            'taxonomy' => 'project_category',
            'field' => 'slug',
            'terms' => explode(',', $atts['category']),
        ]];
    }

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '';
    }

    ob_start();
    ?>
    <div class="projects-showcase">
        <div class="container">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-5">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="col">
                        <?php echo cw_render_post_card(
                            get_post(),
                            $atts['template']
                        ); ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('my_projects', 'my_project_showcase_shortcode');
```

---

## Image Sizes

The system respects WordPress image sizes registered in `functions/images.php`. Common sizes used:

```php
'codeweber_single'         // Main content area images
'codeweber_clients_300-200' // Client logos
'codeweber_staff_large'    // Staff profile images
'thumbnail'                // Default WordPress thumbnail
'medium'                   // Default WordPress medium
'full'                     // Original size
```

Use these in template_args:

```php
echo cw_render_post_card($post, 'card', [], [
    'image_size' => 'codeweber_single',  // Uses registered size
]);
```

---

## Performance Considerations

### Avoid N+1 Queries

Instead of:
```php
// BAD: Calls cw_get_post_card_data() which queries featured image, terms, meta
while ($query->have_posts()) {
    $query->the_post();
    echo cw_render_post_card(get_post());  // N queries for N posts
}
```

Use Gutenberg blocks with server-side rendering or handle batch fetching.

### Cache Template Results

For expensive templates, cache the output:

```php
$cache_key = 'post_card_' . $post->ID . '_' . md5(json_encode($template_args));
$cached = wp_cache_get($cache_key);

if ($cached) {
    return $cached;
}

$output = cw_render_post_card($post, $template_name, $display_settings, $template_args);
wp_cache_set($cache_key, $output, '', 3600);  // Cache for 1 hour

return $output;
```

### Image Optimization

Use smaller sizes for card thumbnails, larger for single posts:

```php
// In archive: smaller image
echo cw_render_post_card($post, 'card', [], [
    'image_size' => 'medium',  // Lighter HTTP payload
]);

// In single: larger image
echo cw_render_post_card($post, 'card', [], [
    'image_size' => 'large',   // Better quality
]);
```

---

## Debugging

### Check Template File Path

Add error logging to see which template was loaded:

```php
// In functions/post-card-templates.php after template_path is determined:
if (WP_DEBUG) {
    error_log('Post card template: ' . $template_path);
}
```

### Verify Post Data

Dump the data being passed to template:

```php
// In template file:
error_log('Post data: ' . print_r($post_data, true));
error_log('Display settings: ' . print_r($display, true));
```

### Check Hooks Are Firing

Use WordPress debugging to verify hook execution:

```php
add_filter('codeweber_post_card_template_dir', function($dir, $name, $type, $post) {
    error_log("Hook fired: template_dir=$dir, name=$name, type=$type");
    return $dir;
}, 10, 4);
```

Check logs in `/wp-content/debug.log`

---

## Backward Compatibility

The system supports old template names for existing code:

```php
// Old code still works:
cw_render_post_card($post, 'client-simple');   // Auto-maps to clients/simple.php
cw_render_post_card($post, 'default');         // Auto-maps to post/default.php

// New code can be explicit:
cw_render_post_card($post, 'default');         // Still works, more explicit
```

Old templates in `templates/post-cards/` root are still found if new location doesn't exist.

---

## Related Documentation

- **[HOOKS_REFERENCE.md](../api/HOOKS_REFERENCE.md)** — Hooks for customizing post card behavior
- **[CPT_CATALOG.md](../cpt/CPT_CATALOG.md)** — Post types and their data structures
- **[TEMPLATE_SYSTEM.md](TEMPLATE_SYSTEM.md)** — Overall theme template selection (header, footer, page layout)
- **[ARCHIVE_SINGLE_PATTERNS.md](ARCHIVE_SINGLE_PATTERNS.md)** — Common patterns for archive and single post templates
