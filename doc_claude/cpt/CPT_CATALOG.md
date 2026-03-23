# Custom Post Type Catalog

Complete reference for all 18 Custom Post Type (CPT) definitions in the CodeWeber theme. Each CPT is defined in `/functions/cpt/cpt-*.php` and registered on the `init` hook.

---

## Navigation by Category

### 🎨 **Structural CPTs** (not visible on frontend)
- [Header](#header)
- [Footer](#footer)
- [Page Header](#page-header)

### 🎭 **Content & Display CPTs**
- [Modal](#modal)
- [HTML Block](#html-block)
- [Notification](#notification)

### 👥 **People & Organization**
- [Staff](#staff)
- [Clients](#clients)
- [Vacancies](#vacancies)
- [Offices](#offices)

### 📋 **Information & Catalog**
- [FAQ](#faq)
- [Testimonials](#testimonials)
- [Legal Documents](#legal-documents)
- [Documents](#documents)

### 💼 **Business & Services**
- [Services](#services)
- [Projects](#projects)
- [Price Lists](#price-lists)
- [Price Packages](#price-packages)

---

## Structural CPTs

### Header

**File:** `functions/cpt/cpt-header.php`

| Property | Value |
|----------|-------|
| **Slug** | `header` |
| **Public** | ✅ true (admin only) |
| **Publicly Queryable** | ❌ false |
| **Has Archive** | ❌ false |
| **Supports** | title, editor |
| **Hierarchical** | ❌ false |
| **Excludes from Search** | ✅ yes |

**Purpose:** Define site header layouts. Can be selected per-page in Redux theme options. Single/Archive pages trigger 404 on frontend.

**Use Case:** Store alternate header designs (classic, fancy, centered logo) and choose via page meta or theme settings.

**Related Files:**
- `templates/header/` — Header template variations
- `redux-framework/` — Header selection UI
- Shortcode: `[codeweber_header]` — Renders selected header

---

### Footer

**File:** `functions/cpt/cpt-footer.php`

| Property | Value |
|----------|-------|
| **Slug** | `footer` |
| **Public** | ✅ true (admin only) |
| **Publicly Queryable** | ❌ false |
| **Has Archive** | ❌ false |
| **Supports** | title, editor |
| **Hierarchical** | ❌ false |
| **Excludes from Search** | ✅ yes |

**Purpose:** Define site footer layouts. Selected globally or per-page.

**Related Files:**
- `templates/footer-*.php` — Footer template variations
- Redux options for default footer selection

---

### Page Header

**File:** `functions/cpt/cpt-page-header.php`

| Property | Value |
|----------|-------|
| **Slug** | `page-header` |
| **Public** | ✅ true (admin only) |
| **Publicly Queryable** | ❌ false |
| **Has Archive** | ❌ false |
| **Supports** | title, editor |
| **Hierarchical** | ❌ false |
| **Excludes from Search** | ✅ yes |

**Purpose:** Hero sections / page title areas shown at top of content pages.

**Related Files:**
- `templates/page-header/` — Page header template variations
- Page meta: Link to a page-header post to display at top

---

## Content & Display CPTs

### Modal

**File:** `functions/cpt/cpt-modals.php`

| Property | Value |
|----------|-------|
| **Slug** | `modal` |
| **Public** | ✅ true (admin only) |
| **Publicly Queryable** | ❌ false |
| **Has Archive** | ❌ false |
| **Supports** | title, editor |
| **Hierarchical** | ❌ false |
| **REST API** | ✅ yes (`codeweber/v1/modals`) |

**Purpose:** Reusable modal/popup content (login, newsletter signup, contact form, etc.).

**Usage:**
```php
// Get modal by post ID
$modal_id = 123;
$modal = get_post($modal_id);
echo $modal->post_content;
```

---

### HTML Block

**File:** `functions/cpt/cpt-html_blocks.php`

| Property | Value |
|----------|-------|
| **Slug** | `html_blocks` |
| **Public** | ✅ true (admin only) |
| **Publicly Queryable** | ❌ false |
| **Has Archive** | ❌ false |
| **Supports** | title, editor |
| **Hierarchical** | ❌ false |

**Purpose:** Store reusable HTML content blocks (banners, testimonial rows, promo sections). Shortcode: `[html_block id="123"]`

**Custom Meta Fields:**
- `html_block_height` — CSS height
- `html_block_bg_color` — Background color
- `html_block_padding` — Padding

---

### Notification

**File:** `functions/cpt/cpt-notifications.php`

| Property | Value |
|----------|-------|
| **Slug** | `notifications` |
| **Public** | ✅ true |
| **Publicly Queryable** | ❌ false |
| **Has Archive** | ❌ false |
| **Supports** | title, editor |
| **Hierarchical** | ❌ false |
| **REST API** | ✅ yes |

**Purpose:** Store system notifications (alerts, announcements) displayed via JavaScript on specific pages.

**Custom Meta Fields:**
- `notification_type` — error, success, info, warning
- `notification_display_pages` — array of page IDs or post types
- `notification_schedule_start` / `notification_schedule_end` — Date-time stamps
- `notification_icon` — Icon class (e.g., `bi-exclamation-circle`)

---

## People & Organization CPTs

### Staff

**File:** `functions/cpt/cpt-staff.php`

| Property | Value |
|----------|-------|
| **Slug** | `staff` |
| **Public** | ✅ true |
| **Publicly Queryable** | ✅ true |
| **Has Archive** | ✅ true (`/staff/`) |
| **Supports** | title, thumbnail, editor, revisions |
| **Hierarchical** | ❌ false |
| **REST API** | ✅ yes |

**Taxonomy:** `departments` (hierarchical)

**Custom Meta Fields:**
- `staff_position` — Job title
- `staff_phone` — Contact phone
- `staff_email` — Email address
- `staff_social_networks` — Array of social links
- `staff_description_short` — One-line bio

**Related Files:**
- `templates/post-cards/card-staff.php` — Staff card template
- `functions/fetch/assets/js/fetch-handler.js` — AJAX load more staff
- `functions/qr-code.php` — Generate vCard/QR codes for staff
- REST endpoint: `GET /wp-json/codeweber/v1/staff/{id}/vcf` — Download vCard

**Special Features:**
- QR code generation (vCard format)
- Department filtering via taxonomy
- Archive pagination with AJAX load more

---

### Clients

**File:** `functions/cpt/cpt-clients.php`

| Property | Value |
|----------|-------|
| **Slug** | `clients` |
| **Public** | ✅ true |
| **Publicly Queryable** | ✅ true |
| **Has Archive** | ✅ true (`/clients/`) |
| **Supports** | title, thumbnail, editor |
| **Hierarchical** | ❌ false |

**Taxonomy:** `clients_category` (non-hierarchical)

**Custom Meta Fields:**
- `clients_logo_url` — Company logo (usually featured image)
- `clients_url` — Website link
- `clients_description` — Company description

---

### Vacancies

**File:** `functions/cpt/cpt-vacancies.php`

| Property | Value |
|----------|-------|
| **Slug** | `vacancies` |
| **Public** | ✅ true |
| **Publicly Queryable** | ✅ true |
| **Has Archive** | ✅ true (`/vacancies/`) |
| **Supports** | title, editor, thumbnail, revisions |
| **Hierarchical** | ❌ false |

**Taxonomies:**
- `vacancy_type` (Job type: Full-time, Part-time, Contract, etc.)
- `vacancy_schedule` (Work schedule: Office, Remote, Hybrid)

**Custom Meta Fields:**
- `vacancy_position` — Job title (may differ from post title)
- `vacancy_salary_from` — Min salary (number)
- `vacancy_salary_to` — Max salary (number)
- `vacancy_location` — Office location
- `vacancy_expiration_date` — Application deadline
- `vacancy_form_id` — Associated application form ID
- `vacancy_status` — active, closed, on_hold

**Related Files:**
- `templates/post-cards/card-vacancies.php` — Vacancy card
- `functions/integrations/codeweber-forms/` — Application form integration
- Archive: Filterable by type and schedule

---

### Offices

**File:** `functions/cpt/cpt-offices.php`

| Property | Value |
|----------|-------|
| **Slug** | `offices` |
| **Public** | ✅ true |
| **Publicly Queryable** | ✅ true |
| **Has Archive** | ✅ true (`/offices/`) |
| **Supports** | title, editor, thumbnail, revisions |
| **Hierarchical** | ❌ false |

**Taxonomy:** `towns` (City/region)

**Custom Meta Fields:**
- `office_address` — Full street address
- `office_phone` — Main phone number
- `office_email` — Contact email
- `office_working_hours` — JSON schedule (day → hours)
- `office_map_lat` / `office_map_long` — Coordinates
- `office_departments` — Which departments work here

**Related Files:**
- `functions/integrations/yandex-maps/` — Map rendering
- Yandex Maps embedded on single office page

---

## Information & Catalog CPTs

### FAQ

**File:** `functions/cpt/cpt-faq.php`

| Property | Value |
|----------|-------|
| **Slug** | `faq` |
| **Public** | ✅ true |
| **Publicly Queryable** | ✅ true |
| **Has Archive** | ✅ true (`/faq/`) |
| **Supports** | title, editor |
| **Hierarchical** | ✅ true (supports parent/child) |

**Taxonomies:**
- `faq_categories` (Main category: General, Technical, Billing, etc.)
- `faq_tag` (Tags for cross-categorization)

**Custom Meta Fields:**
- `faq_answer` — Full answer text (usually in post_content)
- `faq_order` — Sort order within category

**Related Files:**
- `templates/post-cards/card-faq.php` — FAQ item card
- Accordion-style display on archive

---

### Testimonials

**File:** `functions/cpt/cpt-testimonials.php`

| Property | Value |
|----------|-------|
| **Slug** | `testimonials` |
| **Public** | ✅ true |
| **Publicly Queryable** | ✅ true |
| **Has Archive** | ✅ true (`/testimonials/`) |
| **Supports** | title, thumbnail, editor, revisions |
| **Hierarchical** | ❌ false |

**Custom Meta Fields:**
- `testimonial_author_name` — Client/reviewer name
- `testimonial_author_title` — Position/company
- `testimonial_rating` — Star rating (1-5)
- `testimonial_content_short` — Quote snippet
- `testimonial_video_url` — Embedded video testimonial (optional)

**Related Files:**
- `templates/post-cards/card-testimonials.php` — Testimonial card
- Swiper carousel integration for frontend display
- REST endpoint: `GET /wp-json/codeweber/v1/testimonials` — List with pagination

**Special Features:**
- Video testimonials support (Vimeo/YouTube)
- Star rating system
- Swiper carousel on homepage

---

### Legal Documents

**File:** `functions/cpt/cpt-legal.php`

| Property | Value |
|----------|-------|
| **Slug** | `legal` |
| **Public** | ✅ true |
| **Publicly Queryable** | ✅ true |
| **Has Archive** | ✅ true (`/legal/`) |
| **Supports** | title, editor |
| **Hierarchical** | ❌ false |

**Custom Meta Fields:**
- `legal_doc_type` — Privacy Policy, Terms of Service, Cookie Policy, etc.
- `legal_doc_version` — Version number
- `legal_doc_effective_date` — When it becomes effective
- `legal_doc_archive_url` — Link to previous versions

**Related Files:**
- Usually displayed via page builder or simple template
- GDPR compliance helpers in `functions/integrations/personal-data-v2/`

---

### Documents

**File:** `functions/cpt/cpt-documents.php`

| Property | Value |
|----------|-------|
| **Slug** | `documents` |
| **Public** | ✅ true |
| **Publicly Queryable** | ✅ true |
| **Has Archive** | ✅ true (`/documents/`) |
| **Supports** | title, editor, thumbnail, revisions |
| **Hierarchical** | ❌ false |

**Taxonomies:**
- `document_category` (Contracts, Reports, Guides, etc.)
- `document_type` (PDF, Word, Spreadsheet, etc.)

**Custom Meta Fields:**
- `document_file_url` — Direct link to PDF/file
- `document_file_size` — File size in MB
- `document_file_type` — MIME type
- `document_version` — Version number
- `document_published_date` — Publication date (separate from post date)

**Related Files:**
- `templates/post-cards/card-documents.php` — Document card with download button
- REST endpoint: `POST /wp-json/codeweber-forms/v1/documents/send-email` — Email document to user

**Special Features:**
- File versioning
- Email document via form submission
- Archive by category and type

---

## Business & Services CPTs

### Services

**File:** `functions/cpt/cpt-services.php`

| Property | Value |
|----------|-------|
| **Slug** | `services` |
| **Public** | ✅ true |
| **Publicly Queryable** | ✅ true |
| **Has Archive** | ✅ true (`/services/`) |
| **Supports** | title, editor, thumbnail, revisions |
| **Hierarchical** | ❌ false |

**Taxonomies:**
- `service_category` (Main service category)
- `types_of_services` (Sub-type/specific service)

**Custom Meta Fields:**
- `service_description_short` — One-line description
- `service_icon` — Icon class (e.g., `bi-gear`)
- `service_features` — Array of key features
- `service_price_info` — Pricing structure or note
- `service_call_to_action` — CTA text/link

**Related Files:**
- `templates/post-cards/card-services.php` — Service card
- Archive: Grid layout with category filtering
- Single: Full service page with related services sidebar

---

### Projects

**File:** `functions/cpt/cpt-projects.php`

| Property | Value |
|----------|-------|
| **Slug** | `projects` |
| **Public** | ✅ true |
| **Publicly Queryable** | ✅ true |
| **Has Archive** | ✅ true (`/projects/`) |
| **Supports** | title, editor, thumbnail, revisions |
| **Hierarchical** | ❌ false |

**Taxonomy:** `projects_category` (Project type: Web Design, Development, Branding, etc.)

**Custom Meta Fields:**
- `project_client_name` — Client name (may link to Clients CPT)
- `project_date_completed` — Project completion date
- `project_technologies` — Array of tech stack used
- `project_link` — Live project URL
- `project_gallery_ids` — Array of attachment IDs for gallery
- `project_result_metrics` — Key results (e.g., "40% increase in conversions")

**Related Files:**
- `templates/post-cards/card-projects.php` — Project card with thumbnail
- Single page: Full case study with gallery and details
- Archive: Grid with category filtering

---

### Price Lists

**File:** `functions/cpt/cpt-price_list.php`

| Property | Value |
|----------|-------|
| **Slug** | `price_lists` |
| **Public** | ✅ true |
| **Publicly Queryable** | ✅ false |
| **Has Archive** | ❌ false |
| **Supports** | title, editor |
| **Hierarchical** | ❌ false |

**Custom Meta Fields:**
- `price_list_table_data` — JSON structure of pricing table
- `price_list_currency` — Currency code (USD, RUB, EUR)
- `price_list_description` — Table caption
- `price_list_notes` — Footnotes/disclaimers

**Usage:**
```php
// Render price list by ID
echo do_shortcode('[price_list id="123"]');

// Or in template
$price_list = get_post(123);
$table_data = get_post_meta(123, 'price_list_table_data', true);
```

**Related Files:**
- Shortcode: `[price_list id="123"]` — Render as table
- Gutenberg block: `codeweber/price-list` — Insert into pages

---

### Price Packages

**File:** `functions/cpt/cpt-price_package.php`

| Property | Value |
|----------|-------|
| **Slug** | `price` |
| **Public** | ✅ true |
| **Publicly Queryable** | ✅ false |
| **Has Archive** | ❌ false |
| **Supports** | title, editor |
| **Hierarchical** | ❌ false |

**Custom Meta Fields:**
- `price_package_name` — Package name (e.g., "Starter", "Pro", "Enterprise")
- `price_package_price` — Numeric price
- `price_package_currency` — Currency code
- `price_package_period` — Billing period (month, year, one-time)
- `price_package_features` — Array of included features
- `price_package_featured` — Boolean: highlight this package
- `price_package_cta_text` — Button text
- `price_package_cta_url` — Button link

**Usage:**
```php
// Display pricing table
echo do_shortcode('[price_packages columns="3"]');
```

**Related Files:**
- Shortcode: `[price_packages]` — Render pricing card grid
- Gutenberg block: `codeweber/pricing-table` — Insert into pages

---

## Events & Registrations CPTs

### Events

**File:** `functions/cpt/cpt-events.php`

| Property | Value |
|----------|-------|
| **Slug** | `events` |
| **Public** | ✅ true |
| **Publicly Queryable** | ✅ true |
| **Has Archive** | ✅ true (`/events/`) |
| **Supports** | title, editor, thumbnail, excerpt, revisions, author |
| **Hierarchical** | ❌ false |
| **REST API** | ✅ yes |
| **Icon** | dashicons-calendar-alt |

**Taxonomies:**
- `event_category` — иерархическая (конференция, семинар, вебинар…)
- `event_format` — плоская (офлайн, онлайн, гибрид)

**Meta Fields:**
| Meta key | Тип | Описание |
|---|---|---|
| `_event_date_start` | datetime | Начало мероприятия |
| `_event_date_end` | datetime | Окончание мероприятия |
| `_event_registration_open` | datetime | Начало приёма заявок |
| `_event_registration_close` | datetime | Окончание приёма заявок |
| `_event_location` | text | Место проведения |
| `_event_address` | text | Адрес |
| `_event_organizer` | text | Организатор |
| `_event_price` | text | Стоимость |
| `_event_registration_enabled` | bool | Включить встроенную форму |
| `_event_max_participants` | int | Максимальное число мест (0 = без ограничений) |
| `_event_registration_url` | url | Внешняя ссылка регистрации |
| `_event_video_type` | select: url/upload | Тип видео |
| `_event_video_url` | url | Ссылка на видео (YouTube/Vimeo/Rutube/VK) |
| `_event_video_file` | attachment ID | Загруженный видеофайл |
| `_event_gallery` | int[] | Галерея (attachment IDs, FilePond + SortableJS) |

**Helper functions:**
- `codeweber_events_get_registration_status($event_id)` — статус регистрации + label + show_form
- `codeweber_events_get_registration_count($event_id)` — кол-во заявок
- `codeweber_events_get_video_glightbox($event_id)` — данные для GLightbox
- `codeweber_get_event_gallery_ids($event_id)` — IDs галереи в порядке

**REST endpoints (codeweber/v1):**
- `POST /events/register` — подать заявку
- `GET /events/calendar` — FullCalendar feed (params: start, end, category)

**Settings:** `get_option('codeweber_events_settings')` — страница «Мероприятия → Настройки»

**Related Files:**
- `functions/events/event-registration-api.php` — REST API
- `functions/admin/events-settings.php` — страница настроек
- `functions/integrations/event-gallery-metabox.php` — галерея FilePond
- `archive-events.php` — архив с dual view (FullCalendar / Table)
- `single-events.php` — страница мероприятия
- `templates/post-cards/events/card-events.php` — карточка

---

### Event Registrations

**File:** `functions/cpt/cpt-event-registrations.php`

| Property | Value |
|----------|-------|
| **Slug** | `event_registrations` |
| **Public** | ❌ false (admin only) |
| **Publicly Queryable** | ❌ false |
| **Has Archive** | ❌ false |
| **Show in menu** | под «Мероприятия» (`edit.php?post_type=events`) |
| **Supports** | title |

**Custom Post Statuses:**
| Slug | Label | Цвет |
|---|---|---|
| `reg_pending` | Новая | жёлтый |
| `reg_confirmed` | Подтверждена | зелёный |
| `reg_cancelled` | Отменена | красный |
| `reg_awaiting` | Ожидает оплаты | синий |

**Meta Fields:**
| Meta key | Описание |
|---|---|
| `_reg_event_id` | ID мероприятия |
| `_reg_name` | Имя участника |
| `_reg_email` | Email |
| `_reg_phone` | Телефон |
| `_reg_message` | Комментарий |

**Admin features:**
- Кастомные колонки: мероприятие, имя, email, телефон, статус, дата
- Фильтрация по мероприятию (dropdown)
- Bulk actions: подтвердить / отменить
- Badge в меню с кол-вом новых заявок
- Метабокс смены статуса на странице заявки

---

## Summary Table

| CPT | Type | Public | Archive | Taxonomy | Supports |
|-----|------|--------|---------|----------|----------|
| Header | Structural | ✅ (admin) | ❌ | — | title, editor |
| Footer | Structural | ✅ (admin) | ❌ | — | title, editor |
| Page Header | Structural | ✅ (admin) | ❌ | — | title, editor |
| Modal | Display | ✅ | ❌ | — | title, editor |
| HTML Block | Display | ✅ | ❌ | — | title, editor |
| Notification | Display | ✅ | ❌ | — | title, editor |
| Staff | People | ✅ | ✅ | departments | title, thumbnail, editor, revisions |
| Clients | People | ✅ | ✅ | clients_category | title, thumbnail, editor |
| Vacancies | People | ✅ | ✅ | vacancy_type, vacancy_schedule | title, editor, thumbnail, revisions |
| Offices | People | ✅ | ✅ | towns | title, editor, thumbnail, revisions |
| FAQ | Info | ✅ | ✅ | faq_categories, faq_tag | title, editor |
| Testimonials | Info | ✅ | ✅ | — | title, thumbnail, editor, revisions |
| Legal Docs | Info | ✅ | ✅ | — | title, editor |
| Documents | Info | ✅ | ✅ | document_category, document_type | title, editor, thumbnail, revisions |
| Services | Business | ✅ | ✅ | service_category, types_of_services | title, editor, thumbnail, revisions |
| Projects | Business | ✅ | ✅ | projects_category | title, editor, thumbnail, revisions |
| Price Lists | Business | ✅ | ❌ | — | title, editor |
| Price Packages | Business | ✅ | ❌ | — | title, editor |

---

## Common Patterns

### Archive Pages

Most CPTs with archives follow this template:
```php
// archive-{post_type}.php
<?php get_header(); ?>
<div class="container py-5">
    <h1><?php echo get_the_archive_title(); ?></h1>
    <div class="row">
        <?php while (have_posts()) : the_post(); ?>
            <?php cw_render_post_card(); // Uses POST_CARDS_SYSTEM ?>
        <?php endwhile; ?>
    </div>
    <?php the_posts_pagination(); ?>
</div>
<?php get_footer(); ?>
```

### Single Pages

```php
// single-{post_type}.php
<?php get_header(); ?>
<?php
    // Optional: Render page-header CPT if linked
    if ($page_header_id = get_post_meta(get_the_ID(), 'page_header_id', true)) {
        echo get_post_field('post_content', $page_header_id);
    }
?>
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <article>
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </article>
        </div>
        <aside class="col-md-4">
            <?php get_sidebar(); ?>
        </aside>
    </div>
</div>
<?php get_footer(); ?>
```

### REST API Access

Most CPTs are exposed via REST API. Access example:

```bash
# Get all staff members
GET /wp-json/wp/v2/staff?per_page=10

# Get staff filtered by department
GET /wp-json/wp/v2/staff?departments=managers&per_page=5

# Get single staff member
GET /wp-json/wp/v2/staff/{id}
```

---

## Related Documentation

- **[CPT_HOW_TO_ADD.md](CPT_HOW_TO_ADD.md)** — Step-by-step guide to adding a new CPT
- **[HOOKS_REFERENCE.md](../api/HOOKS_REFERENCE.md)** — Filters for CPT behavior
- **[REST_API_REFERENCE.md](../api/REST_API_REFERENCE.md)** — Custom REST endpoints
- **[POST_CARDS_SYSTEM.md](../templates/POST_CARDS_SYSTEM.md)** — How to render CPT cards
- **[ARCHIVE_SINGLE_PATTERNS.md](../templates/ARCHIVE_SINGLE_PATTERNS.md)** — Common template patterns
