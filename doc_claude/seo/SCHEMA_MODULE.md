# Schema.org JSON-LD Module

Модуль генерации структурированных данных Schema.org для всех CPT и стандартных типов контента.

---

## Архитектура

### Стратегия разделения ответственности

| Задача | Кто отвечает |
|--------|-------------|
| OpenGraph (og:*), Twitter Cards | SEO-плагин (Rank Math / Yoast) |
| Title, Meta Description, Canonical | SEO-плагин |
| Robots (noindex/nofollow), Sitemap | SEO-плагин |
| **Schema.org JSON-LD** | **Тема CodeWeber** |
| WooCommerce Product Schema | WooCommerce (`WC_Structured_Data`) |

### Файловая структура

```
functions/seo/
├── seo-detect.php          # Детектор SEO-плагинов + подавление их Schema
├── seo-meta-tags.php       # Хелперы title/description (читают из Rank Math/Yoast)
├── seo-schema.php          # Базовый @graph: WebSite, Organization, BreadcrumbList, WebPage
└── schema/
    ├── schema-article.php      # Article (post)
    ├── schema-event.php        # Event (events CPT) — single + archive
    ├── schema-staff.php        # Person (staff CPT) — single + archive
    ├── schema-vacancy.php      # JobPosting (vacancies CPT) — single + archive
    ├── schema-office.php       # LocalBusiness (offices CPT) — single + archive
    ├── schema-service.php      # Service (services CPT) — single + archive
    ├── schema-testimonial.php  # Review (single) + AggregateRating (archive)
    ├── schema-faq.php          # FAQPage — single + archive
    ├── schema-project.php      # CreativeWork (projects CPT) — single + archive
    └── schema-document.php     # DigitalDocument (documents CPT) — single + archive
```

### Подключение в functions.php

```php
// ── SEO ──────────────────────────────────────────────────────────────────────
require_once get_template_directory() . '/functions/seo/seo-detect.php';
require_once get_template_directory() . '/functions/seo/seo-meta-tags.php';
require_once get_template_directory() . '/functions/seo/seo-schema.php';
require_once get_template_directory() . '/functions/seo/schema/schema-article.php';
require_once get_template_directory() . '/functions/seo/schema/schema-event.php';
require_once get_template_directory() . '/functions/seo/schema/schema-staff.php';
require_once get_template_directory() . '/functions/seo/schema/schema-vacancy.php';
require_once get_template_directory() . '/functions/seo/schema/schema-office.php';
require_once get_template_directory() . '/functions/seo/schema/schema-service.php';
require_once get_template_directory() . '/functions/seo/schema/schema-testimonial.php';
require_once get_template_directory() . '/functions/seo/schema/schema-faq.php';
require_once get_template_directory() . '/functions/seo/schema/schema-project.php';
require_once get_template_directory() . '/functions/seo/schema/schema-document.php';
```

---

## seo-detect.php — Детектор и подавление Schema

### Функции

#### `codeweber_has_seo_plugin(): bool`

Проверяет наличие активного SEO-плагина. Результат кешируется в static.

Детектирует: Rank Math, Yoast SEO, SEOPress, All in One SEO.

### Подавление Schema у плагинов

Хук `init` отключает **только Schema JSON-LD** у SEO-плагинов:

| Плагин | Фильтр |
|--------|--------|
| Rank Math | `rank_math/json_ld → __return_empty_array` |
| Yoast SEO | `wpseo_json_ld_output → __return_empty_array` |
| SEOPress | `seopress_schemas_auto_disable → __return_true` |
| All in One SEO | `aioseo_disable_schema_output → __return_true` |

OG, Twitter Cards, title, description, canonical, robots — **не затрагиваются**.

---

## seo-meta-tags.php — Хелперы данных

### `codeweber_get_seo_title( ?int $post_id = null ): string`

Приоритет: `rank_math_title` → `_yoast_wpseo_title` → `get_the_title()`.

### `codeweber_get_seo_description( ?int $post_id = null ): string`

Приоритет: `rank_math_description` → `_yoast_wpseo_metadesc` → excerpt → обрезанный content (160 символов).

Очистка content: удаление Gutenberg-комментариев, шорткодов, `html_entity_decode` перед `wp_strip_all_tags`.

---

## seo-schema.php — Базовый каркас

Выводит единый `<script type="application/ld+json">` в `wp_footer` (приоритет 20) с `@graph`:

### WebSite (все страницы)

- `name`, `url`, `potentialAction` (SearchAction)

### Organization (все страницы)

Данные из Redux (`redux_demo`):

| Поле Schema | Redux-ключ |
|-------------|-----------|
| `name` | `legal_entity_short` (fallback: `bloginfo('name')`) |
| `description` | `text-about-company` |
| `logo` | `opt-dark-logo` |
| `telephone` | `phone_01` |
| `email` | `e-mail` |
| `address` | `juri-street`, `juri-house`, `juri-office`, `juri-city`, `juri-region`, `juri-postal`, `juri-country` |
| `sameAs` | `facebook`, `instagram`, `twitter`, `linkedin`, `youtube`, `tiktok`, `telegram`, `vk`, `github`, `pinterest`, `vimeo`, `odnoklassniki`, `rutube`, `yandex-dzen` |

### BreadcrumbList (все кроме главной)

Собирает хлебные крошки из контекста: Home → CPT archive → Parent pages → Current.

Поддержка: singular (CPT, post, page), post type archive, taxonomy, category, tag, search, WooCommerce (shop → product_cat → product).

### WebPage (все страницы)

`name`, `url`, `description`, `primaryImageOfPage`, `datePublished`, `dateModified`, `isPartOf → WebSite`, `breadcrumb`.

---

## Фильтр `codeweber_schema_graph`

```php
apply_filters( 'codeweber_schema_graph', array $graph ): array
```

**Файл:** `seo-schema.php`

**Назначение:** Все CPT-схемы добавляют свои ноды через этот фильтр.

**Пример добавления своей схемы:**

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

---

## CPT-схемы

### Article (`schema-article.php`)

- **Триггер:** `is_singular('post')`
- **Тип:** `Article`
- **Поля:** headline, description, image (с размерами), author (Person), publisher → Organization, datePublished, dateModified, wordCount, keywords (категории), articleSection

### Event (`schema-event.php`)

- **Single:** `is_singular('events')` → `Event`
  - Поля: startDate, endDate, location (Place с geo), organizer, offers (price + availability), eventAttendanceMode (Online/Offline/Mixed из таксономии `event_format`), eventStatus, image
  - Доступность: проверяет `_event_max_participants` vs количество регистраций + `_event_fake_registered`
- **Archive:** `is_post_type_archive('events')` → `ItemList` с Event-элементами

### Person (`schema-staff.php`)

- **Single:** `is_singular('staff')` → `Person`
  - Поля: name, jobTitle, telephone, email, image, address, worksFor → Organization, sameAs (соцсети), memberOf (departments taxonomy)
- **Archive:** `is_post_type_archive('staff')` → `ItemList` с Person-элементами

### JobPosting (`schema-vacancy.php`)

- **Single:** `is_singular('vacancies')` → `JobPosting`
  - Поля: title, description, datePosted, baseSalary, jobLocation (с geo), employmentType (из `vacancy_type` taxonomy, маппинг slug → FULL_TIME/PART_TIME/CONTRACTOR/INTERN), jobLocationType (TELECOMMUTE из `vacancy_schedule`), hiringOrganization, experienceRequirements, educationRequirements, skills, image
  - `_vacancy_company` переопределяет hiringOrganization
- **Archive:** `is_post_type_archive('vacancies')` → `ItemList` с JobPosting-элементами

### LocalBusiness (`schema-office.php`)

- **Single:** `is_singular('offices')` → `LocalBusiness`
  - Поля: name, address (PostalAddress из `_office_*`), geo (координаты), telephone (массив `_office_phone` + `_office_phone_2`), email, fax, openingHours, image, sameAs (website), parentOrganization → Organization
- **Archive:** `is_post_type_archive('offices')` → `ItemList` с LocalBusiness-элементами

### Service (`schema-service.php`)

- **Single:** `is_singular('services')` → `Service`
  - Поля: name, description, provider → Organization, category (`service_category`), serviceType (`types_of_services`), offers (price из `_service_price_info`), image
- **Archive:** `is_post_type_archive('services')` → `ItemList` с Service-элементами

### Review + AggregateRating (`schema-testimonial.php`)

- **Single:** `is_singular('testimonials')` → `Review`
  - Поля: author (Person с jobTitle, image), reviewRating (1-5), reviewBody, name, itemReviewed → Organization, datePublished, video (VideoObject если `_testimonial_video_url`)
- **Archive:** `is_post_type_archive('testimonials')` → `Organization` с `AggregateRating`
  - Агрегация: запрос **всех** testimonials с rating > 0 (не только текущая страница), средний рейтинг, количество

### FAQPage (`schema-faq.php`)

- **Single:** `is_singular('faq')` → `FAQPage` с одной Q/A парой (title = вопрос, content = ответ)
- **Archive:** `is_post_type_archive('faq')` → `FAQPage` со всеми Q/A парами текущей страницы пагинации

### CreativeWork (`schema-project.php`)

- **Single:** `is_singular('projects')` → `CreativeWork`
  - Поля: name, description, image, datePublished, dateModified, creator → Organization, author, keywords/genre (из `projects_category`)
- **Archive:** `is_post_type_archive('projects')` → `ItemList` с CreativeWork-элементами

### DigitalDocument (`schema-document.php`)

- **Single:** `is_singular('documents')` → `DigitalDocument`
  - Поля: name, description, contentUrl (из `_document_file`), encodingFormat (MIME-тип по расширению), datePublished, dateModified, publisher → Organization, keywords (`document_category`), additionalType (`document_type`), image
  - **Примечание:** single documents отключён 404-redirect в `cpt-documents.php`, schema single не активируется
- **Archive:** `is_post_type_archive('documents')` → `ItemList` с DigitalDocument-элементами

---

## Вспомогательные функции

| Функция | Файл | Назначение |
|---------|------|-----------|
| `codeweber_schema_organization()` | `seo-schema.php` | Собирает Organization из Redux |
| `codeweber_schema_postal_address($prefix)` | `seo-schema.php` | PostalAddress из Redux (`juri-*` или `fact-*`) |
| `codeweber_schema_same_as()` | `seo-schema.php` | Массив соцсетей из Redux (с дедупликацией) |
| `codeweber_schema_breadcrumblist()` | `seo-schema.php` | BreadcrumbList из контекста страницы |
| `codeweber_schema_webpage($site_url)` | `seo-schema.php` | WebPage для текущей страницы |
| `codeweber_schema_current_url()` | `seo-schema.php` | URL для не-singular страниц |
| `codeweber_schema_datetime($datetime)` | `schema-event.php` | Конвертация datetime-local в ISO 8601 |
| `codeweber_schema_event_registration_count($event_id)` | `schema-event.php` | Подсчёт регистраций на мероприятие |
| `codeweber_schema_document_url($post_id)` | `schema-document.php` | Получение URL файла документа |

---

## WooCommerce Product

Schema для WooCommerce Product **не генерируется** темой. Используется встроенный класс `WC_Structured_Data`. Фильтры в `seo-detect.php` его не затрагивают.

---

## Расширение: добавление Schema для нового CPT

1. Создать файл `functions/seo/schema/schema-{cpt}.php`
2. Добавить `add_filter('codeweber_schema_graph', ...)` с проверкой `is_singular('{cpt}')` и/или `is_post_type_archive('{cpt}')`
3. Подключить в `functions.php`:
   ```php
   require_once get_template_directory() . '/functions/seo/schema/schema-{cpt}.php';
   ```

---

## Расширение: Schema из Gutenberg-блоков

**Планируемый подход:** динамические блоки добавляют schema через глобальный массив при рендере в `render.php`, хук в `wp_footer` собирает и выводит.

**Статус:** не реализовано. CPT-схемы работают, блоки — следующий этап.
