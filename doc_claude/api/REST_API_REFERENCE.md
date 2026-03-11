# REST API Reference

Complete catalog of custom REST API endpoints provided by the CodeWeber theme. All endpoints return JSON responses and require proper authentication/nonce validation.

---

## Overview

The theme provides two main API namespaces:

| Namespace | Purpose | Base URL |
|-----------|---------|----------|
| `codeweber/v1` | Theme-specific endpoints | `/wp-json/codeweber/v1/` |
| `codeweber-forms/v1` | Form submission & management | `/wp-json/codeweber-forms/v1/` |
| `wp/v2` | Standard WordPress REST API | `/wp-json/wp/v2/` (extended) |

---

## Common Request/Response Format

### Request Headers

```http
POST /wp-json/codeweber-forms/v1/submit HTTP/1.1
Content-Type: application/json
X-WP-Nonce: abc123def456...
```

### Error Response

```json
{
  "code": "rest_invalid_param",
  "message": "Invalid parameter(s): form_id",
  "data": {
    "status": 400
  }
}
```

### Success Response

Most endpoints return:

```json
{
  "success": true,
  "data": { /* endpoint-specific data */ }
}
```

---

## Form Endpoints (`codeweber-forms/v1`)

### Submit Form

**Endpoint:** `POST /wp-json/codeweber-forms/v1/submit`

**Authentication:** Requires valid `X-WP-Nonce` header (WordPress REST nonce)

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `form_id` | integer | ✓ | Form post ID |
| `fields` | object | ✓ | Form field values |
| `honeypot` | string | | Spam check field (should be empty) |
| `file_ids` | array | | Uploaded file attachment IDs |

**Request Example:**

```bash
curl -X POST https://example.com/wp-json/codeweber-forms/v1/submit \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: abc123..." \
  -d '{
    "form_id": 123,
    "fields": {
      "name": "John Doe",
      "email": "john@example.com",
      "message": "Hello"
    },
    "file_ids": ["file_uuid_1"]
  }'
```

**Response:**

```json
{
  "success": true,
  "data": {
    "message": "Form submitted successfully",
    "submission_id": "sub_xyz789"
  }
}
```

**Error Responses:**

```json
{
  "success": false,
  "data": {
    "message": "Invalid form ID",
    "code": "invalid_form"
  }
}
```

---

### List Forms

**Endpoint:** `GET /wp-json/codeweber-forms/v1/forms`

**Authentication:** Requires `edit_posts` capability (admin only)

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `per_page` | integer | Items per page (default: 10) |
| `page` | integer | Page number (default: 1) |

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "title": "Contact Form",
      "config": { /* form configuration */ }
    },
    {
      "id": 124,
      "title": "Newsletter Signup",
      "config": { /* form configuration */ }
    }
  ],
  "total": 42,
  "pages": 5
}
```

---

### Get Form Configuration

**Endpoint:** `GET /wp-json/codeweber-forms/v1/forms/{id}`

**Authentication:** Requires `edit_posts` capability

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | integer | ✓ | Form post ID |

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 123,
    "title": "Contact Form",
    "fields": [
      {
        "name": "email",
        "label": "Email Address",
        "type": "email",
        "required": true
      },
      {
        "name": "message",
        "label": "Message",
        "type": "textarea"
      }
    ]
  }
}
```

---

### Track Form Opening

**Endpoint:** `POST /wp-json/codeweber-forms/v1/form-opened`

**Authentication:** None required (public)

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `form_id` | integer | ✓ | Form post ID |

**Purpose:** Track when forms are loaded for analytics/reporting.

**Response:**

```json
{
  "success": true,
  "data": {
    "tracked": true
  }
}
```

---

### Upload File

**Endpoint:** `POST /wp-json/codeweber-forms/v1/upload`

**Authentication:** Requires valid `X-WP-Nonce` header

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `file` | file | ✓ | File to upload |
| `form_id` | integer | | Form ID for context |

**Request Example:**

```bash
curl -X POST https://example.com/wp-json/codeweber-forms/v1/upload \
  -H "X-WP-Nonce: abc123..." \
  -F "file=@document.pdf" \
  -F "form_id=123"
```

**Response:**

```json
{
  "success": true,
  "data": {
    "file_id": "file_abc123",
    "filename": "document.pdf",
    "size": 102400,
    "mime_type": "application/pdf"
  }
}
```

---

### Delete Uploaded File

**Endpoint:** `DELETE /wp-json/codeweber-forms/v1/upload/{id}`

**Authentication:** Requires valid nonce

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | string | ✓ | File UUID |

**Response:**

```json
{
  "success": true,
  "data": {
    "deleted": true
  }
}
```

---

### CF7 Form Opened (Matomo)

**Endpoint:** `POST /wp-json/codeweber-forms/v1/cf7-form-opened`

**Authentication:** None

**Purpose:** Track Contact Form 7 form impressions for Matomo analytics.

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `form_id` | integer | CF7 form ID |
| `form_name` | string | Form title/name |

---

### CF7 Form Error (Matomo)

**Endpoint:** `POST /wp-json/codeweber-forms/v1/cf7-form-error`

**Authentication:** None

**Purpose:** Track CF7 form validation errors for analytics.

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `form_id` | integer | CF7 form ID |
| `errors` | object | Field errors |

---

## Staff Endpoints (`codeweber/v1`)

### Download vCard (Staff)

**Endpoint:** `GET /wp-json/codeweber/v1/staff/{id}/download-vcf`

**Authentication:** None (public)

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | integer | ✓ | Staff post ID |

**Response:** Binary vCard file download (`.vcf` format)

**Headers:**

```
Content-Type: text/vcard
Content-Disposition: attachment; filename=staff_name.vcf
```

**Example:**

```bash
# Returns downloadable .vcf file
curl -O https://example.com/wp-json/codeweber/v1/staff/123/download-vcf
```

---

### Get vCard URL (Staff)

**Endpoint:** `GET /wp-json/codeweber/v1/staff/{id}/vcf-url`

**Authentication:** None

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | integer | ✓ | Staff post ID |

**Response:**

```json
{
  "success": true,
  "data": {
    "vcf_url": "https://example.com/staff/john-doe.vcf",
    "qr_code": "data:image/png;base64,iVBORw0KGgo..."
  }
}
```

**Purpose:** Get vCard URL and QR code for staff contact sharing.

---

## Document Endpoints (`codeweber/v1`)

### Get Document Download URL

**Endpoint:** `GET /wp-json/codeweber/v1/documents/{id}/download-url`

**Authentication:** None

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | integer | ✓ | Document post ID |

**Response:**

```json
{
  "success": true,
  "data": {
    "download_url": "https://example.com/wp-content/uploads/2024/doc.pdf",
    "filename": "document.pdf",
    "size": "2.5 MB"
  }
}
```

---

### Send Document via Email

**Endpoint:** `POST /wp-json/codeweber/v1/documents/send-email`

**Authentication:** Requires `X-WP-Nonce` header

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `document_id` | integer | ✓ | Document post ID |
| `email` | string | ✓ | Recipient email |
| `name` | string | | Recipient name |
| `message` | string | | Optional message |

**Request Example:**

```bash
curl -X POST https://example.com/wp-json/codeweber/v1/documents/send-email \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: abc123..." \
  -d '{
    "document_id": 456,
    "email": "recipient@example.com",
    "name": "John",
    "message": "Here is the document you requested"
  }'
```

**Response:**

```json
{
  "success": true,
  "data": {
    "message": "Document sent successfully",
    "email": "recipient@example.com"
  }
}
```

---

## Vacancy Endpoints (`codeweber/v1`)

### Download Vacancy Application

**Endpoint:** `GET /wp-json/codeweber/v1/vacancies/{id}/download-url`

**Authentication:** None

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | integer | ✓ | Vacancy post ID |

**Response:**

```json
{
  "success": true,
  "data": {
    "application_form_id": 789,
    "form_url": "https://example.com/#apply-form"
  }
}
```

---

## Modal Endpoints (`wp/v2` with custom extensions)

### Get Modal Content

**Endpoint:** `GET /wp-json/wp/v2/modal/doc-{id}`

**Authentication:** None

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | integer | ✓ | Modal post ID (without "doc-" prefix in actual requests) |

**Response:**

```json
{
  "id": 123,
  "title": {
    "rendered": "Modal Title"
  },
  "content": {
    "rendered": "<div class=\"modal-content\">...</div>"
  }
}
```

---

### Add Testimonial (Modal)

**Endpoint:** `POST /wp-json/wp/v2/modal/add-testimonial`

**Authentication:** Requires valid `X-WP-Nonce` header

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `author_name` | string | ✓ | Testimonial author |
| `author_title` | string | | Author's position/company |
| `content` | string | ✓ | Testimonial text |
| `rating` | integer | | Star rating (1-5) |
| `email` | string | | Author email (for verification) |

**Request Example:**

```bash
curl -X POST https://example.com/wp-json/wp/v2/modal/add-testimonial \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: abc123..." \
  -d '{
    "author_name": "Jane Smith",
    "author_title": "CEO, Company Inc.",
    "content": "Great service!",
    "rating": 5
  }'
```

**Response:**

```json
{
  "success": true,
  "data": {
    "testimonial_id": 124,
    "message": "Thank you for your testimonial. It will be reviewed before publishing."
  }
}
```

---

## Success Message Template Endpoint (`codeweber/v1`)

### Get Success Message

**Endpoint:** `GET /wp-json/codeweber/v1/success-message-template`

**Authentication:** None

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `type` | string | Message type (form_submitted, document_sent, etc.) |
| `form_id` | integer | Form ID (if applicable) |

**Response:**

```json
{
  "success": true,
  "data": {
    "template": "<div class=\"success-alert\">Thank you!</div>",
    "title": "Success",
    "message": "Your request has been processed"
  }
}
```

---

## Contact Form 7 Endpoints (Custom)

### Get CF7 Form Title

**Endpoint:** `GET /wp-json/custom/v1/cf7-title/{id}`

**Authentication:** None

**Parameters:**

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `id` | integer | ✓ | CF7 form ID |

**Response:**

```json
{
  "title": "Contact Form",
  "description": "Send us a message"
}
```

---

## Standard WordPress CPT Endpoints

### List Posts by Type

**Endpoint:** `GET /wp-json/wp/v2/{post_type}`

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `per_page` | integer | Items per page (default: 10) |
| `page` | integer | Page number |
| `search` | string | Search query |
| `orderby` | string | Sort by (date, title, modified, id) |
| `order` | string | ASC or DESC |

**Examples:**

```bash
# List all staff members
GET /wp-json/wp/v2/staff?per_page=20

# List services by title
GET /wp-json/wp/v2/services?orderby=title&order=asc

# Search projects
GET /wp-json/wp/v2/projects?search=website
```

---

### Get Single Post

**Endpoint:** `GET /wp-json/wp/v2/{post_type}/{id}`

**Example:**

```bash
GET /wp-json/wp/v2/testimonials/42
```

**Response:**

```json
{
  "id": 42,
  "date": "2024-03-11T10:30:00",
  "date_gmt": "2024-03-11T09:30:00",
  "guid": { "rendered": "https://example.com/?p=42" },
  "modified": "2024-03-11T11:45:00",
  "modified_gmt": "2024-03-11T10:45:00",
  "slug": "john-smith",
  "status": "publish",
  "type": "testimonials",
  "title": { "rendered": "John Smith" },
  "content": { "rendered": "Great experience!" },
  "excerpt": { "rendered": "" },
  "featured_media": 789,
  "template": "",
  "link": "https://example.com/testimonials/john-smith/"
}
```

---

### Create Post (Admin)

**Endpoint:** `POST /wp-json/wp/v2/{post_type}`

**Authentication:** Requires `edit_posts` capability

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `title` | string | Post title |
| `content` | string | Post content (HTML) |
| `status` | string | publish, draft, pending |
| `featured_media` | integer | Featured image attachment ID |
| `meta` | object | Custom field values |

**Example:**

```bash
curl -X POST https://example.com/wp-json/wp/v2/testimonials \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: abc123..." \
  -d '{
    "title": "New Testimonial",
    "content": "This client is very satisfied",
    "status": "publish"
  }'
```

---

## Authentication

### Nonce-Based (Frontend Forms)

All POST requests from frontend forms require a WordPress REST nonce:

```javascript
// In header.php or enqueued script:
const nonce = document.querySelector('[name="_wpnonce"]').value;

// In fetch request:
fetch('/wp-json/codeweber-forms/v1/submit', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': nonce
  },
  body: JSON.stringify({...})
});
```

### Cookie-Based (Admin)

Authenticated users get a WordPress cookie that's automatically sent with requests.

### Capability-Based

Some endpoints check user capabilities:

```php
// Endpoint requires 'edit_posts' capability
'permission_callback' => function() {
    return current_user_can('edit_posts');
}
```

---

## Common Response Codes

| Code | Meaning | Example |
|------|---------|---------|
| 200 | Success | Form submitted, data retrieved |
| 201 | Created | New post created |
| 400 | Bad Request | Missing required parameter |
| 403 | Forbidden | Insufficient permissions or invalid nonce |
| 404 | Not Found | Post/resource doesn't exist |
| 500 | Server Error | PHP error, database issue |

---

## Pagination

Paginated endpoints include headers:

```http
X-Total: 42
X-Total-Pages: 5
Link: <https://example.com/wp-json/...?page=2>; rel="next"
```

**Example: Get next page**

```javascript
const response = await fetch('/wp-json/wp/v2/staff?page=1');
const total = response.headers.get('X-Total');
const pages = response.headers.get('X-Total-Pages');
const nextLink = response.headers.get('Link');
```

---

## Rate Limiting

No built-in rate limiting, but WordPress Core can be configured via:

- `.htaccess` (Apache)
- Server-level throttling
- Security plugins (e.g., Wordfence)

---

## Debugging API Requests

### Enable REST API Logging

Add to `wp-config.php`:

```php
define('WP_DEBUG_LOG', true);
define('REST_REQUEST', true);
```

Check `/wp-content/debug.log` for API request details.

### Test with cURL

```bash
# Simple GET
curl -i https://example.com/wp-json/wp/v2/posts

# POST with nonce
curl -X POST \
  -H "X-WP-Nonce: abc123..." \
  -d "form_id=123" \
  https://example.com/wp-json/codeweber-forms/v1/forms
```

### Test with Postman

Import REST endpoints into Postman:

1. Create new collection
2. Add requests with proper headers
3. Set up environment variables for `nonce`, `site_url`
4. Use pre-request scripts to generate fresh nonces

---

## Related Documentation

- **[AJAX_FETCH_SYSTEM.md](AJAX_FETCH_SYSTEM.md)** — AJAX endpoints (alternative)
- **[HOOKS_REFERENCE.md](HOOKS_REFERENCE.md)** — Filters for API customization
- **[CODEWEBER_FORMS.md](../forms/CODEWEBER_FORMS.md)** — Form submission architecture
- **[CPT_CATALOG.md](../cpt/CPT_CATALOG.md)** — Post types & REST endpoints
