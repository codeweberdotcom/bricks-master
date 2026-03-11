# How to Add a New Custom Post Type

8-step recipe for adding a new CPT (Custom Post Type) to the CodeWeber theme. Follow these steps in order to ensure proper initialization, template integration, and frontend display.

---

## Overview: CPT Lifecycle

```
Step 1: Register CPT definition (cpt-{name}.php)
            ↓
Step 2: Include in functions.php (require_once)
            ↓
Step 3: Create archive template (archive-{post_type}.php)
            ↓
Step 4: Create post card template (card-{post_type}.php)
            ↓
Step 5: Create single post template (single-{post_type}.php)
            ↓
Step 6: Add Redux theme options (optional)
            ↓
Step 7: Register image sizes (functions/images.php)
            ↓
Step 8: Add AJAX filtering (functions/fetch/ — optional)
```

---

## Step 1: Create CPT Registration File

**File:** `functions/cpt/cpt-{post_type}.php`

Create a new file following the naming convention. Use a CPT that matches your data model (e.g., "testimonials", "partners", "case_studies").

### Minimal Example: `cpt-testimonials.php`

```php
<?php

function cptui_register_my_cpts_testimonials()
{
    /**
     * Post Type: Testimonials
     */
    $labels = [
        "name" => esc_html__("Testimonials", "codeweber"),
        "singular_name" => esc_html__("Testimonial", "codeweber"),
        "menu_name" => esc_html__("Testimonials", "codeweber"),
        "all_items" => esc_html__("All Testimonials", "codeweber"),
        "add_new" => esc_html__("Add New Testimonial", "codeweber"),
        "add_new_item" => esc_html__("Add New Testimonial", "codeweber"),
        "edit_item" => esc_html__("Edit Testimonial", "codeweber"),
        "new_item" => esc_html__("New Testimonial", "codeweber"),
        "view_item" => esc_html__("View Testimonial", "codeweber"),
        "view_items" => esc_html__("View Testimonials", "codeweber"),
        "search_items" => esc_html__("Search Testimonials", "codeweber"),
        "not_found" => esc_html__("No testimonials found", "codeweber"),
        "not_found_in_trash" => esc_html__("No testimonials found in Trash", "codeweber"),
    ];

    $args = [
        "label" => esc_html__("Testimonials", "codeweber"),
        "labels" => $labels,
        "description" => "Customer testimonials and reviews",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "has_archive" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "can_export" => true,
        "rewrite" => ["slug" => "testimonials", "with_front" => true],
        "query_var" => true,
        "supports" => ["title", "editor", "thumbnail", "revisions"],
        "show_in_graphql" => false,
    ];

    register_post_type("testimonials", $args);
}

add_action('init', 'cptui_register_my_cpts_testimonials');
```

### Key Parameters Explained

| Parameter | Value | When | Why |
|-----------|-------|------|-----|
| `public` | true | Most CPTs | Makes it queryable + shows UI |
| `publicly_queryable` | true | Items have single/archive pages on frontend | false = admin-only CPT (Header, Footer) |
| `has_archive` | true | CPT has listing page | false = no `/testimonials/` URL |
| `show_in_rest` | true | Always | Enables REST API + Gutenberg block support |
| `rewrite` | `["slug" => "testimonials"]` | Most CPTs | URL structure: `/testimonials/` |
| `supports` | `["title", "editor", "thumbnail"]` | Customize editor fields | Pick what the editor needs |
| `exclude_from_search` | false | Usually | Include in global WP search (Dashboard Search) |

### Optional: Add Taxonomy

If your CPT needs categorization, add a taxonomy registration to the same file:

```php
function cptui_register_my_taxes_testimonial_category()
{
    /**
     * Taxonomy: Testimonial Category
     */
    $labels = [
        "name" => esc_html__("Categories", "codeweber"),
        "singular_name" => esc_html__("Category", "codeweber"),
    ];

    $args = [
        "label" => esc_html__("Categories", "codeweber"),
        "labels" => $labels,
        "public" => true,
        "hierarchical" => false, // true = categories, false = tags
        "show_ui" => true,
        "show_in_rest" => true,
        "rewrite" => ["slug" => "testimonial-category", "with_front" => true],
    ];

    register_taxonomy("testimonial_category", ["testimonials"], $args);
}

add_action('init', 'cptui_register_my_taxes_testimonial_category');
```

---

## Step 2: Include in functions.php

**File:** `functions.php` (lines 7–14)

Add a `require_once` statement in the CPT section:

```php
// Подключение файлов CPT
require_once get_template_directory() . '/functions/cpt/cpt-header.php';
require_once get_template_directory() . '/functions/cpt/cpt-footer.php';
require_once get_template_directory() . '/functions/cpt/cpt-testimonials.php'; // ← ADD THIS
// ... other CPTs
```

**Order matters:** Load CPTs early, before other modules that might reference them.

### Verification
After adding the line, refresh WordPress admin. You should see the new CPT in the sidebar menu.

---

## Step 3: Create Archive Template

**File:** `archive-{post_type}.php` (or `archive-testimonials.php` in theme root)

This template displays the listing page at `/{post_type}/`.

### Simple Archive Template

```php
<?php
/**
 * Archive Template for Testimonials
 * URL: /testimonials/
 * Displays all testimonial posts with pagination
 */
get_header();
?>

<div class="container py-5">
    <h1><?php echo get_the_archive_title(); ?></h1>

    <div class="row g-4">
        <?php
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                // Render post card (see Step 4)
                cw_render_post_card();
            }
        } else {
            echo '<p>' . esc_html__('No testimonials found.', 'codeweber') . '</p>';
        }
        ?>
    </div>

    <!-- Pagination -->
    <div class="row mt-5">
        <div class="col-12">
            <?php the_posts_pagination(['screen_reader_text' => __('Posts pagination', 'codeweber')]); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
```

### With Sidebar

```php
<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <!-- Post cards here -->
        </div>
        <aside class="col-md-4">
            <?php get_sidebar('testimonials'); // sidebar-testimonials.php ?>
        </aside>
    </div>
</div>
```

---

## Step 4: Create Post Card Template

**File:** `templates/post-cards/card-{post_type}.php` (or `card-testimonials.php`)

This template renders a **single card** for the post (used on archive, homepage carousel, etc.).

### How It Works

The theme uses the `cw_render_post_card()` function to automatically find and render the correct card template based on post type. See [POST_CARDS_SYSTEM.md](../templates/POST_CARDS_SYSTEM.md) for details.

### Simple Card Template Example

```php
<?php
/**
 * Post Card: Testimonial
 * Variables:
 *   - $post (global) - current post object
 *   - $post_id (if passed) - optional post ID
 */

$post_id = get_the_ID();
$author_name = get_post_meta($post_id, 'testimonial_author_name', true);
$author_title = get_post_meta($post_id, 'testimonial_author_title', true);
$rating = intval(get_post_meta($post_id, 'testimonial_rating', true) ?? 0);
?>

<div class="col-sm-6 col-lg-4">
    <div class="card h-100">
        <!-- Featured Image -->
        <?php if (has_post_thumbnail()) : ?>
            <div class="card-img-wrapper">
                <?php the_post_thumbnail('codeweber_staff', ['class' => 'card-img-top']); ?>
            </div>
        <?php endif; ?>

        <div class="card-body">
            <!-- Quote -->
            <p class="card-text small">
                "<?php echo wp_trim_words(get_the_content(), 20); ?>"
            </p>

            <!-- Author -->
            <footer class="card-footer pt-0 border-top-0 bg-transparent">
                <strong><?php echo esc_html($author_name); ?></strong>
                <br>
                <small class="text-muted"><?php echo esc_html($author_title); ?></small>

                <!-- Star Rating -->
                <?php if ($rating > 0) : ?>
                    <div class="mt-2">
                        <?php
                        for ($i = 0; $i < $rating; $i++) {
                            echo '<i class="bi bi-star-fill"></i>';
                        }
                        for ($i = $rating; $i < 5; $i++) {
                            echo '<i class="bi bi-star"></i>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </footer>
        </div>

        <!-- Link to Single Page -->
        <a href="<?php the_permalink(); ?>" class="stretched-link"></a>
    </div>
</div>
```

### Bootstrap Classes

- `.card` — Bootstrap card component
- `.col-*` — Grid column (responsive)
- `.card-img-top`, `.card-body`, `.card-footer` — Card sections
- `.stretched-link` — Makes entire card clickable

---

## Step 5: Create Single Post Template

**File:** `single-{post_type}.php` (or `single-testimonials.php` in theme root)

This template displays the full detail page for a single post at `/{post_type}/{slug}/`.

### Single Page Template Example

```php
<?php
/**
 * Single Template for Testimonials
 * URL: /testimonials/{slug}/
 */
get_header();
?>

<div class="container py-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <article class="post-content">
                <header class="mb-4">
                    <h1><?php the_title(); ?></h1>
                    <small class="text-muted">
                        <?php echo esc_html(get_post_meta(get_the_ID(), 'testimonial_author_title', true)); ?>
                    </small>
                </header>

                <!-- Featured Image -->
                <?php if (has_post_thumbnail()) : ?>
                    <div class="mb-4">
                        <?php the_post_thumbnail('codeweber_extralarge', ['class' => 'img-fluid']); ?>
                    </div>
                <?php endif; ?>

                <!-- Content -->
                <?php the_content(); ?>

                <!-- Meta Information -->
                <footer class="post-footer mt-5 pt-5 border-top">
                    <div class="row">
                        <div class="col-sm-6">
                            <strong><?php esc_html_e('Published:', 'codeweber'); ?></strong>
                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                <?php echo esc_html(get_the_date('F j, Y')); ?>
                            </time>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <strong><?php esc_html_e('Category:', 'codeweber'); ?></strong>
                            <?php the_terms(get_the_ID(), 'testimonial_category', '', ', '); ?>
                        </div>
                    </div>
                </footer>
            </article>

            <!-- Navigation to Next/Previous -->
            <nav class="row mt-5">
                <div class="col-sm-6">
                    <?php previous_post_link('&larr; %link', '', false, '', 'testimonial_category'); ?>
                </div>
                <div class="col-sm-6 text-end">
                    <?php next_post_link('%link &rarr; ', '', false, '', 'testimonial_category'); ?>
                </div>
            </nav>
        </div>

        <!-- Sidebar -->
        <aside class="col-md-4">
            <?php get_sidebar(); ?>
        </aside>
    </div>
</div>

<?php get_footer(); ?>
```

---

## Step 6: Add Redux Theme Options (Optional)

**File:** `redux-framework/theme-settings/theme-settings.php`

If you want admins to configure CPT behavior in **Theme Settings** (e.g., items per page, featured archive, excluded categories), add a Redux field.

### Example: CPT Display Options

```php
// In the appropriate Redux panel (e.g., 'testimonials_panel'):
[
    'id' => 'testimonials_per_page',
    'type' => 'number',
    'title' => esc_html__('Testimonials Per Page', 'codeweber'),
    'default' => 12,
    'min' => 1,
    'max' => 100,
],
[
    'id' => 'testimonials_featured',
    'type' => 'select',
    'title' => esc_html__('Featured Testimonial', 'codeweber'),
    'options' => wp_list_pluck(
        get_posts(['post_type' => 'testimonials', 'posts_per_page' => -1]),
        'post_title',
        'ID'
    ),
],
```

### Access in Templates

```php
// In archive-testimonials.php:
$per_page = Codeweber_Options::get('testimonials_per_page', 12);
$featured_id = Codeweber_Options::get('testimonials_featured');
```

See [REDUX_OPTIONS.md](../settings/REDUX_OPTIONS.md) for full Redux integration guide.

---

## Step 7: Register Image Sizes

**File:** `functions/images.php`

Define custom image sizes for your CPT's featured images. These are used by `the_post_thumbnail()` with the size name.

### Add Image Sizes

```php
if (!function_exists('codeweber_image_settings')) {
    function codeweber_image_settings()
    {
        // ... existing sizes ...

        // CPT Testimonials
        add_image_size('codeweber_testimonial_400-400', 400, 400, true);
        add_image_size('codeweber_testimonial_card', 300, 300, true);

        // ... rest of function ...
    }
}
```

### Parameters

```php
add_image_size(
    'codeweber_testimonial_400-400', // ← Name used in templates
    400,                              // ← Width (pixels)
    400,                              // ← Height (pixels)
    true                              // ← Hard crop (vs. soft scale)
);
```

### Use in Template

```php
// In card-testimonials.php:
<?php the_post_thumbnail('codeweber_testimonial_400-400', ['class' => 'img-fluid']); ?>
```

---

## Step 8: Add AJAX Filtering (Optional)

**File:** `functions/fetch/{NewActionType}.php` + `functions/fetch/fetch-handler.php`

If your archive needs **dynamic filtering** (e.g., "Show testimonials by category"), add an AJAX action handler.

### Create Fetch Handler

**File:** `functions/fetch/getTestimonialsByCategory.php`

```php
<?php

namespace Codeweber\Functions\Fetch;

class GetTestimonialsByCategory {
    public static function handle($params) {
        // Sanitize input
        $category = sanitize_key($params['category'] ?? '');
        $paged = absint($params['paged'] ?? 1);
        $per_page = absint($params['per_page'] ?? 12);

        // Whitelist
        $categories = get_terms(['taxonomy' => 'testimonial_category', 'fields' => 'ids']);
        if (!empty($category) && !in_array($category, $categories, true)) {
            $category = '';
        }

        // Query
        $args = [
            'post_type' => 'testimonials',
            'posts_per_page' => $per_page,
            'paged' => $paged,
            'post_status' => 'publish',
        ];

        if (!empty($category)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'testimonial_category',
                    'field' => 'term_id',
                    'terms' => $category,
                ]
            ];
        }

        $query = new \WP_Query($args);

        // Response
        $posts = [];
        foreach ($query->posts as $post) {
            $posts[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'excerpt' => wp_trim_words($post->post_content, 20),
                'link' => get_permalink($post->ID),
                'thumbnail' => get_the_post_thumbnail_url($post->ID, 'codeweber_testimonial_card'),
            ];
        }

        return [
            'posts' => $posts,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'paged' => $paged,
        ];
    }
}
```

### Register in Fetch Handler

**File:** `functions/fetch/fetch-handler.php`

Add to the switch statement:

```php
function handle_fetch_action() {
    if (!check_ajax_referer('fetch_action_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Security check failed.'], 403);
    }

    $actionType = sanitize_text_field(wp_unslash($_POST['actionType'] ?? ''));
    $params = json_decode(wp_unslash($_POST['params'] ?? '[]'), true);

    switch ($actionType) {
        case 'getPosts':
            require_once get_template_directory() . '/functions/fetch/getPosts.php';
            $response = GetPosts::handle($params);
            break;

        case 'getTestimonialsByCategory': // ← ADD THIS
            require_once get_template_directory() . '/functions/fetch/getTestimonialsByCategory.php';
            $response = GetTestimonialsByCategory::handle($params);
            break;

        default:
            wp_send_json_error(['message' => 'Unknown action'], 400);
            return;
    }

    wp_send_json_success($response);
}
```

### Frontend JS Call

```javascript
// In your archive or homepage script:
fetch('/wp-admin/admin-ajax.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        action: 'fetch_action',
        actionType: 'getTestimonialsByCategory',
        nonce: fetch_vars.nonce,
        params: JSON.stringify({
            category: categoryId,
            paged: 2,
            per_page: 12,
        }),
    }),
})
    .then(r => r.json())
    .then(data => {
        console.log(data.data.posts);
        // Update DOM with new posts
    });
```

See [AJAX_FETCH_SYSTEM.md](../api/AJAX_FETCH_SYSTEM.md) for full AJAX architecture.

---

## Final Checklist

Before launching, verify:

- [ ] **Step 1:** CPT file created at `functions/cpt/cpt-{type}.php` with register_post_type()
- [ ] **Step 2:** require_once added to `functions.php` (lines 7–14)
- [ ] **Step 3:** Archive template created (`archive-{type}.php`)
- [ ] **Step 4:** Post card template created (`templates/post-cards/card-{type}.php`)
- [ ] **Step 5:** Single template created (`single-{type}.php`)
- [ ] **Step 6:** Redux options added (if needed for admin settings)
- [ ] **Step 7:** Image sizes registered in `functions/images.php`
- [ ] **Step 8:** AJAX handler created (if archive needs filtering)
- [ ] WordPress admin refreshed; new CPT appears in sidebar menu
- [ ] Create a test post in the new CPT
- [ ] Archive page displays at `/{post_type}/`
- [ ] Single post page displays at `/{post_type}/{slug}/`
- [ ] Featured images display at correct sizes
- [ ] No PHP errors in WP_DEBUG_LOG

---

## Troubleshooting

### "Post type not showing in menu"
- Check that `show_in_menu` is true in `register_post_type()`
- Verify require_once was added to functions.php
- Clear any caching plugins

### "Archive page shows 404"
- Check that `has_archive` is true
- Verify `rewrite` slug matches your expected URL
- Go to WordPress **Settings → Permalinks** and click "Save Changes" to flush rewrite rules

### "Card template not rendering"
- Verify file path: `templates/post-cards/card-{post_type}.php`
- Check naming: CPT slug must match the filename (e.g., `card-testimonials.php` for slug "testimonials")
- Ensure `cw_render_post_card()` is called in archive template

### "REST API not accessible"
- Check `show_in_rest => true` in register_post_type()
- Verify REST endpoint: `GET /wp-json/wp/v2/{post_type}`
- Check user capabilities if getting 403 errors

---

## Related Documentation

- **[CPT_CATALOG.md](CPT_CATALOG.md)** — Reference for all 18 existing CPT types
- **[POST_CARDS_SYSTEM.md](../templates/POST_CARDS_SYSTEM.md)** — Detailed card rendering system
- **[ARCHIVE_SINGLE_PATTERNS.md](../templates/ARCHIVE_SINGLE_PATTERNS.md)** — Template code patterns
- **[REDUX_OPTIONS.md](../settings/REDUX_OPTIONS.md)** — Theme settings configuration
- **[AJAX_FETCH_SYSTEM.md](../api/AJAX_FETCH_SYSTEM.md)** — AJAX filtering architecture
- **[HOOKS_REFERENCE.md](../api/HOOKS_REFERENCE.md)** — Filters for CPT customization
