# AJAX Fetch System Architecture

Complete guide to the CodeWeber theme's AJAX data-fetching architecture. This system enables dynamic content loading (infinite scroll, filtering, load-more buttons) without full page reloads.

---

## Overview: How Fetch Works

```
Frontend HTML                      PHP Handler                  Response
────────────────────             ─────────────────             ──────────

<button data-fetch="getPosts"     fetch-handler.php
  data-params='{...}'>              │
  Load More                          ├─ Check nonce
</button>                            ├─ Sanitize actionType
  │                                  ├─ Parse params
  │ (click)                          │
  ↓                                  └─ Switch & call handler:
                                        - getPosts()
fetch-handler.js                       - loadMoreItems()
  │ (send AJAX)                        - getHotspotContent()
  ├─ action: "fetch_action"           - etc.
  ├─ actionType: "getPosts"            │
  ├─ params: {...}                     └─ Return JSON:
  ├─ nonce: "..."                        {
  │                                       "status": "success",
  ↓                                       "data": "<html>..." or {...}
/wp-admin/admin-ajax.php             }
                                       │
                                       ↓
                                    fetch-handler.js
                                       │
                                       └─ Update DOM
                                       └─ Show response
                                       └─ Hide loader
```

---

## Architecture Layers

### 1. Frontend: HTML Attributes

Trigger AJAX with data attributes:

```html
<button class="btn btn-primary"
  data-fetch="getPosts"
  data-params='{"type":"staff","perpage":12}'
  data-wrapper="#posts-container">
  Load More
</button>

<div id="posts-container"></div>
```

**Attributes:**
- `data-fetch` — Action type (required)
- `data-params` — JSON object of parameters (optional)
- `data-wrapper` — Selector for DOM element to update with response (optional)

### 2. Frontend: JavaScript Handler

**File:** `functions/fetch/assets/js/fetch-handler.js`

Listens for clicks on elements with `data-fetch`:

```javascript
document.querySelectorAll("[data-fetch]").forEach((button) => {
  button.addEventListener("click", async (e) => {
    e.preventDefault();

    const action = button.getAttribute("data-fetch");      // actionType
    const params = JSON.parse(button.getAttribute("data-params") || "{}");
    const wrapperSelector = button.getAttribute("data-wrapper");
    const wrapperElement = document.querySelector(wrapperSelector);

    // Show loading spinner
    if (wrapperElement) {
      wrapperElement.innerHTML = '<div class="spinner"></div>';
    }

    // Send AJAX request
    const formData = new FormData();
    formData.append("action", "fetch_action");
    formData.append("actionType", action);
    formData.append("params", JSON.stringify(params));
    formData.append("nonce", fetch_vars.nonce);

    const response = await fetch(fetch_vars.ajaxurl, {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    // Update DOM
    if (result.status === "success") {
      wrapperElement.innerHTML = result.data;
    } else {
      wrapperElement.innerHTML = `<p>Error: ${result.message}</p>`;
    }
  });
});
```

**Global Variable:** `fetch_vars`

Provided via `wp_localize_script()` in `functions/enqueues.php`:

```php
wp_localize_script('fetch-handler', 'fetch_vars', [
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce'   => wp_create_nonce('fetch_action_nonce'),
]);
```

### 3. Backend: Request Handler

**File:** `functions/fetch/fetch-handler.php`

AJAX entry point that routes requests to specific handlers:

```php
namespace Codeweber\Functions\Fetch;

add_action('wp_ajax_fetch_action', 'handle_fetch_action');
add_action('wp_ajax_nopriv_fetch_action', 'handle_fetch_action');

function handle_fetch_action()
{
    // Security: verify nonce
    if (!check_ajax_referer('fetch_action_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Security check failed.'], 403);
    }

    // Parse input
    $actionType = sanitize_text_field(wp_unslash($_POST['actionType'] ?? ''));
    $params = json_decode(wp_unslash($_POST['params'] ?? '[]'), true);

    // Dispatch to handler
    switch ($actionType) {
        case 'getPosts':
            $response = getPosts($params);
            break;
        case 'loadMoreItems':
            $response = loadMoreItems($params);
            break;
        case 'getHotspotContent':
            $response = getHotspotContent($params);
            break;
        default:
            wp_send_json_error(['message' => 'Unknown action'], 400);
            return;
    }

    wp_send_json($response);
}
```

**Execution Flow:**
1. `wp_ajax_{action}` hook fires (WordPress AJAX)
2. Verify nonce (CSRF protection)
3. Sanitize + parse request
4. Dispatch to correct handler function
5. Return JSON response

### 4. Backend: Handler Functions

**Directory:** `functions/fetch/`

Each handler is a separate file in the `Codeweber\Functions\Fetch` namespace:

```php
// functions/fetch/getPosts.php
namespace Codeweber\Functions\Fetch;

function getPosts($params)
{
    // Sanitize params
    $type = sanitize_key($params['type'] ?? 'post');
    $perpage = absint($params['perpage'] ?? 5);

    // Build WP_Query
    $query = new \WP_Query([
        'post_type'      => $type,
        'posts_per_page' => $perpage,
        'post_status'    => 'publish',
    ]);

    // Generate HTML
    if ($query->have_posts()) {
        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('templates/content/single');
        }
        wp_reset_postdata();
        $html = ob_get_clean();

        return [
            'status' => 'success',
            'data'   => $html,
        ];
    }

    return [
        'status'  => 'error',
        'message' => 'Posts not found.',
    ];
}
```

---

## Built-in Action Types

| Action | File | Purpose | Returns |
|--------|------|---------|---------|
| `getPosts` | `getPosts.php` | Fetch posts by type | HTML (rendered template) |
| `loadMoreItems` | `loadMoreItems.php` | Paginated load-more (infinite scroll) | HTML (post cards) |
| `getHotspotContent` | `getHotspotContent.php` | Get content for image hotspots | HTML |
| `getPostsForHotspot` | `getPostsForHotspot.php` | Related posts for hotspot | JSON array |
| `getPostCardTemplates` | `getPostCardTemplates.php` | Render post card by template name | HTML (card) |

---

## Request Format

### JavaScript Call

```javascript
const params = {
    type: 'staff',
    perpage: 12,
    paged: 2,
    category: 'managers'
};

const formData = new FormData();
formData.append('action', 'fetch_action');
formData.append('actionType', 'getPosts');  // ← Handler name
formData.append('params', JSON.stringify(params));
formData.append('nonce', fetch_vars.nonce);

fetch(fetch_vars.ajaxurl, {
    method: 'POST',
    body: formData
}).then(r => r.json());
```

### POST Data Sent

```
POST /wp-admin/admin-ajax.php

action=fetch_action&
actionType=getPosts&
params={"type":"staff","perpage":12,"paged":2}&
nonce=abc123def456
```

---

## Response Format

### Success Response

```json
{
  "success": true,
  "data": "<div class='card'>...</div><div class='card'>...</div>"
}
```

Or for data endpoints:

```json
{
  "success": true,
  "data": {
    "posts": [
      { "id": 1, "title": "Post 1", "link": "..." },
      { "id": 2, "title": "Post 2", "link": "..." }
    ],
    "total": 42,
    "pages": 4
  }
}
```

### Error Response

```json
{
  "success": false,
  "data": {
    "message": "Security check failed."
  }
}
```

---

## Adding a New Action Type

Follow these steps to add a new AJAX action handler.

### Step 1: Create Handler File

**File:** `functions/fetch/myNewAction.php`

```php
<?php

namespace Codeweber\Functions\Fetch;

function myNewAction($params)
{
    // Sanitize all inputs
    $search = sanitize_text_field($params['search'] ?? '');
    $limit = absint($params['limit'] ?? 10);

    // Validate
    if (empty($search) || strlen($search) < 3) {
        return [
            'status'  => 'error',
            'message' => 'Search term must be at least 3 characters.',
        ];
    }

    // Process
    $results = [];
    $query = new \WP_Query([
        's'       => $search,
        'post_type' => 'post',
        'posts_per_page' => $limit,
    ]);

    foreach ($query->posts as $post) {
        $results[] = [
            'id'    => $post->ID,
            'title' => $post->post_title,
            'url'   => get_permalink($post->ID),
        ];
    }

    return [
        'status' => 'success',
        'data'   => $results,
    ];
}
```

### Step 2: Require in Fetch Handler

**File:** `functions/fetch/fetch-handler.php`

Add to the top (in namespace):

```php
require_once __DIR__ . '/myNewAction.php';
```

### Step 3: Add to Switch Statement

**File:** `functions/fetch/fetch-handler.php`

Add case to `handle_fetch_action()`:

```php
switch ($actionType) {
    case 'getPosts':
        $response = getPosts($params);
        break;

    case 'myNewAction':  // ← ADD THIS
        $response = myNewAction($params);
        break;

    default:
        wp_send_json_error(['message' => 'Unknown action'], 400);
        return;
}
```

### Step 4: Use in Template

```html
<button class="btn btn-search"
  data-fetch="myNewAction"
  data-params='{"search":"wordpress","limit":5}'
  data-wrapper="#search-results">
  Search
</button>

<div id="search-results"></div>
```

---

## Security Considerations

### Nonce Verification

All handlers verify the request nonce to prevent CSRF attacks:

```php
if (!check_ajax_referer('fetch_action_nonce', 'nonce', false)) {
    wp_send_json_error(['message' => 'Security check failed.'], 403);
}
```

The nonce is generated in `functions/enqueues.php` via `wp_create_nonce()`.

### Input Sanitization

Always sanitize user input:

```php
// Text input
$text = sanitize_text_field($params['text'] ?? '');

// Numbers
$count = absint($params['count'] ?? 0);

// Keys/select values
$type = sanitize_key($params['type'] ?? 'default');

// URLs
$url = esc_url_raw($params['url'] ?? '');

// Array of IDs
$ids = array_map('absint', (array)$params['ids'] ?? []);
```

### Whitelist Validation

Restrict allowed values:

```php
// Whitelist allowed post types
$allowed_types = get_post_types(['public' => true]);
$type = sanitize_key($params['type'] ?? 'post');
if (!in_array($type, $allowed_types, true)) {
    $type = 'post'; // Fallback
}

// Whitelist allowed categories
$allowed_categories = get_terms([
    'taxonomy' => 'category',
    'fields'   => 'ids',
]);
$category = absint($params['category'] ?? 0);
if ($category && !in_array($category, $allowed_categories, true)) {
    wp_send_json_error(['message' => 'Invalid category'], 400);
}
```

### Capability Checks

For privileged actions (edit, delete), check user capabilities:

```php
if (!current_user_can('edit_posts')) {
    wp_send_json_error(['message' => 'Insufficient permissions'], 403);
}
```

---

## Performance Optimization

### Caching Results

For expensive queries, cache the response:

```php
$cache_key = 'my_action_' . md5(json_encode($params));
$cached = wp_cache_get($cache_key);

if ($cached !== false) {
    return $cached;
}

// Expensive computation here
$result = [/* ... */];

// Cache for 1 hour
wp_cache_set($cache_key, $result, '', 3600);

return $result;
```

### Limit Data Volume

Always paginate and limit results:

```php
$limit = absint($params['limit'] ?? 10);
if ($limit > 100) $limit = 100; // Cap at 100

$query = new \WP_Query([
    'posts_per_page' => $limit,
    'paged'          => absint($params['paged'] ?? 1),
]);
```

### Minimize Database Calls

Use `get_posts()` with appropriate fields:

```php
// Slower: Fetches entire posts
$posts = get_posts(['post_type' => 'post']);

// Faster: Fetch only IDs
$post_ids = get_posts([
    'post_type' => 'post',
    'fields'    => 'ids',
]);

// Then get only what you need
foreach ($post_ids as $id) {
    $title = get_the_title($id);
}
```

---

## Frontend Integration Examples

### Load More Button

```html
<div id="posts-grid" class="row g-4">
  <!-- Initial posts rendered via template -->
</div>

<div class="mt-4">
  <button class="btn btn-primary"
    id="load-more"
    data-fetch="loadMoreItems"
    data-params='{"page":2,"type":"staff"}'
    data-wrapper="#posts-grid">
    Load More
  </button>
</div>

<script>
  document.getElementById('load-more').addEventListener('click', function() {
    const nextPage = parseInt(this.getAttribute('data-params').page) + 1;
    this.setAttribute('data-params', JSON.stringify({
      page: nextPage,
      type: 'staff'
    }));
  });
</script>
```

### Search Filter

```html
<form id="search-form">
  <input type="text" id="search-input" placeholder="Search...">
  <button type="submit" class="btn btn-primary">Search</button>
</form>

<div id="search-results"></div>

<script>
  document.getElementById('search-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const query = document.getElementById('search-input').value;

    const formData = new FormData();
    formData.append('action', 'fetch_action');
    formData.append('actionType', 'myNewAction');
    formData.append('params', JSON.stringify({ search: query }));
    formData.append('nonce', fetch_vars.nonce);

    const response = await fetch(fetch_vars.ajaxurl, {
      method: 'POST',
      body: formData
    });

    const result = await response.json();
    const container = document.getElementById('search-results');

    if (result.success) {
      container.innerHTML = result.data.map(item =>
        `<a href="${item.url}">${item.title}</a>`
      ).join('');
    } else {
      container.innerHTML = `<p>Error: ${result.data.message}</p>`;
    }
  });
</script>
```

### Infinite Scroll

```html
<div id="posts-container" class="row g-4"></div>
<div id="loading" style="display:none;">Loading...</div>

<script>
  let currentPage = 1;
  let isLoading = false;

  function loadMore() {
    if (isLoading) return;
    isLoading = true;

    const formData = new FormData();
    formData.append('action', 'fetch_action');
    formData.append('actionType', 'loadMoreItems');
    formData.append('params', JSON.stringify({
      page: currentPage + 1,
      type: 'staff'
    }));
    formData.append('nonce', fetch_vars.nonce);

    fetch(fetch_vars.ajaxurl, { method: 'POST', body: formData })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          document.getElementById('posts-container').innerHTML += data.data;
          currentPage++;
        }
        isLoading = false;
      });
  }

  // Trigger load when user scrolls near bottom
  window.addEventListener('scroll', () => {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
      loadMore();
    }
  });
</script>
```

---

## Troubleshooting

### "Nonce check failed"

**Symptom:** Response: `{"success": false, "data": {"message": "Security check failed."}}`

**Solutions:**
- Verify nonce is included in request: `formData.append('nonce', fetch_vars.nonce)`
- Check that `fetch_vars` is available (should be output via `wp_localize_script()`)
- Verify nonce name in handler matches: `check_ajax_referer('fetch_action_nonce', 'nonce', false)`

### "Unknown action"

**Symptom:** Response: `{"success": false, "data": {"message": "Unknown action"}}`

**Solutions:**
- Verify action type is registered in fetch-handler.php switch statement
- Check spelling: `data-fetch="myNewAction"` must match case in switch
- Verify handler file is require_once in fetch-handler.php

### "No data returned"

**Symptom:** Response data is empty or error message

**Solutions:**
- Check `WP_DEBUG_LOG` for PHP errors
- Add logging: `error_log("Debug: " . print_r($params, true));`
- Verify query parameters are correct
- Check post status: queries should include `'post_status' => 'publish'`

### Frontend Not Updating

**Symptom:** JS sends request but DOM doesn't update

**Solutions:**
- Verify `data-wrapper` selector matches actual element ID
- Check browser console for JS errors
- Verify response has `"success": true` and `"data"` has content
- Check that wrapper element exists in DOM before click

---

## Related Documentation

- **[CPT_HOW_TO_ADD.md](../cpt/CPT_HOW_TO_ADD.md)** — Step 8: Add AJAX filtering
- **[HOOKS_REFERENCE.md](HOOKS_REFERENCE.md)** — Filters for fetch actions
- **[REST_API_REFERENCE.md](REST_API_REFERENCE.md)** — Alternative to AJAX (REST endpoints)
- **[FILE_LOADING_ORDER.md](../architecture/FILE_LOADING_ORDER.md)** — When fetch-handler.php loads
