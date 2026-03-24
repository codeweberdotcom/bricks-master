# Events — полная документация

Кастомный модуль мероприятий: CPT + регистрации + REST API + FullCalendar + ICS.

---

## Файловая структура

```
functions/cpt/cpt-events.php                        # Регистрация CPT, таксономий, всех meta-боксов, enqueue скриптов
functions/events/event-registration-api.php         # REST API: форма для модала, submit регистрации, FullCalendar feed
functions/events/event-registrations.php            # CPT event_registrations (приватный), статусы, admin UI
functions/events/event-ics.php                      # ICS-скачивание по ?ics=1
functions/admin/events-settings.php                 # Страница глобальных настроек (Events → Settings)
functions/integrations/event-gallery-metabox.php    # Галерея через FilePond + SortableJS

templates/singles/events/default.php                # Single-шаблон события
templates/singles/events/registration-form.php      # Partial: форма регистрации (modal / inline)
templates/archives/events/events_1.php              # Archive: таблица + FullCalendar, переключение
templates/post-cards/events/card-events.php         # Карточка события (grid)

src/assets/js/event-registration-form.js            # JS: submit формы → REST API, success state
single-events.php                                   # Загружает single.php → default.php
archive-events.php                                  # Загружает events_1.php
```

---

## CPT и таксономии

### Post Type `events`

| Параметр | Значение |
|----------|---------|
| Slug | `events` |
| Has archive | `events` |
| Rewrite slug | `events` |
| Поддержка | title, editor, thumbnail, excerpt, comments, author |
| Gutenberg | **отключён** (classic editor + `wp_editor()`) |
| Показан в REST | да |
| Menu position | 5 (после Posts) |
| Menu icon | `dashicons-calendar-alt` |

### Таксономия `event_category`

Иерархическая (как категории). Slug: `event-category`. Показана в REST.

### Таксономия `event_format`

Плоская (как теги). Slug: `event-format`. Показана в REST.

---

## Все мета-поля (post_meta)

### Даты

| Ключ | Тип | Описание |
|------|-----|---------|
| `_event_date_start` | datetime-local | Дата и время начала |
| `_event_date_end` | datetime-local | Дата и время окончания |
| `_event_registration_open` | datetime-local | Дата открытия регистрации |
| `_event_registration_close` | datetime-local | Дата закрытия регистрации |

### Детали события

| Ключ | Тип | Описание |
|------|-----|---------|
| `_event_location` | text | Место проведения (название) |
| `_event_address` | text | Адрес (показывается рядом с location через `, `) |
| `_event_organizer` | text | Организатор |
| `_event_price` | text | Цена (свободный текст: «500 руб», «Бесплатно») |

### Регистрация

| Ключ | Тип | Значения | Описание |
|------|-----|---------|---------|
| `_event_registration_enabled` | radio | `0`, `1`, `modal` | Режим регистрации |
| `_event_max_participants` | int | — | Максимум участников (0 = без лимита) |
| `_event_fake_registered` | int | — | Добавляется к реальному count (visual padding) |
| `_event_registration_url` | url | — | URL внешней регистрации (только при `external`) |
| `_event_reg_form_title` | select | — | Заголовок формы (переопределяет глобальный) |
| `_event_reg_button_label` | select | — | Метка кнопки (переопределяет глобальный) |
| `_event_reg_email_required` | checkbox | `1` | Сделать email обязательным (пока не используется — email всегда required в HTML) |
| `_event_reg_phone_required` | checkbox | `1` | Телефон обязательный |
| `_event_reg_show_comment` | checkbox | `1` | Показать поле «Комментарий» |
| `_event_reg_show_seats` | checkbox | `1` | Показать поле «Количество мест» |
| `_event_reg_consents` | array | `[{label, document_id, required}]` | Чекбоксы согласий |

**Режимы регистрации (`_event_registration_enabled`):**
- `0` — регистрация отключена (кнопка не показывается)
- `1` — inline-форма в сайдбаре single-страницы
- `modal` — кнопка открывает `#modal` через REST API

### Видео

| Ключ | Тип | Описание |
|------|-----|---------|
| `_event_video_type` | select | `youtube`, `vimeo`, `file` |
| `_event_video_url` | url | URL видео (YouTube/Vimeo) |
| `_event_video_file` | attachment ID | Видеофайл (для type=file) |

### Отчёт (после события)

| Ключ | Тип | Описание |
|------|-----|---------|
| `_event_report_text` | wp_editor | Замещает основной контент после окончания события |

### Карта

| Ключ | Тип | Описание |
|------|-----|---------|
| `_event_show_map` | `1` | Показывать Яндекс-карту в сайдбаре |
| `_event_latitude` | float | Широта маркера |
| `_event_longitude` | float | Долгота маркера |
| `_event_zoom` | int | Зум (default: 15) |
| `_event_yandex_address` | text | Текст в balloon маркера |

### Управление элементами

| Ключ | Тип | Описание |
|------|-----|---------|
| `_event_sidebar_hide_author` | checkbox | Скрыть блок автора в сайдбаре |
| `_event_sidebar_disable_image` | checkbox | Скрыть thumbnail в сайдбаре (thumbnail показывается в контенте) |
| `_event_hide_seats_counter` | checkbox | Скрыть счётчик мест для этого события |
| `_event_hide_add_to_calendar` | checkbox | Скрыть кнопки «Apple Calendar» и «Google Calendar» |

### Галерея

| Ключ | Тип | Описание |
|------|-----|---------|
| `_event_gallery` | array of int | Массив attachment ID в порядке сортировки |

---

## Глобальные настройки (Events → Settings)

Ключ опции: `codeweber_events_settings`. Хелпер: `codeweber_events_settings_get( $key, $default )`.

### Frontend Display

| Ключ | Default | Описание |
|------|---------|---------|
| `show_seats_taken` | `1` | Показывать «N зарегистрировалось» |
| `show_seats_left` | `1` | Показывать «N мест осталось» |
| `show_seats_progress` | `1` | Показывать прогресс-бар заполнения |

### Registration Form

| Ключ | Default | Описание |
|------|---------|---------|
| `reg_form_title` | Register | Заголовок формы по умолчанию |
| `btn_register_text` | Register | Метка кнопки по умолчанию |
| `no_seats_text` | — | Текст при отсутствии мест |
| `success_message` | — | Сообщение после успешной регистрации |

### Notifications

| Ключ | Default | Описание |
|------|---------|---------|
| `notify_email` | admin_email | Email для уведомлений об администраторских регистрациях |

---

## Статус регистрации (`codeweber_events_get_registration_status()`)

Функция в `cpt-events.php`. Возвращает массив `['status' => string, 'label' => string, 'show_form' => bool]`.

| status | label | Условие |
|--------|-------|---------|
| `open` | Открыта / Registration Open | Регистрация включена, даты в норме, есть места |
| `not_open_yet` | Регистрация скоро / Opens soon | `reg_open` ещё не наступила |
| `registration_closed` | Регистрация закрыта | `reg_close` прошла |
| `no_seats` | Мест нет | `max_participants` достигнут |
| `event_ended` | Мероприятие завершено | `date_end` прошла |
| `external` | — | `enabled=1` + есть `registration_url` → redirect |
| `modal` | — | `enabled=modal` |
| `show_form` = true | — | `enabled=1` без внешнего URL → inline форма в сайдбаре |

**Логика счётчика мест:**

```php
$registered_count = codeweber_events_get_registration_count( $event_id );
// = confirmed + pending registrations + fake_registered

$show_any_seats = $show_bar || $show_left || $show_taken;
// Показывается только если: $show_any_seats && ! $hide_seats_counter
```

- `$show_bar` — глобал `show_seats_progress=1` **И** `max_participants > 0`
- `$show_left` — глобал `show_seats_left=1` **И** `max_participants > 0`
- `$show_taken` — глобал `show_seats_taken=1` **И** `registered_count > 0`
- `$hide_seats_counter` — per-event override (мета `_event_hide_seats_counter`)

Глобальные настройки и per-event флаг работают через **AND**: оба должны быть разрешены.

---

## REST API endpoints

Регистрируются в `Codeweber_Event_Registration_API` (`event-registration-api.php`).

### `GET wp/v2/modal/event-reg-{id}`

Возвращает HTML формы для загрузки в модальное окно. Используется `restapi.js` при открытии модала с `data-value="event-reg-{id}"`.

**Ответ:** `{ success: true, html: "...", title: "..." }`

Рендерит partial `templates/singles/events/registration-form.php` через `ob_start()`.

### `POST codeweber/v1/events/register`

Submit регистрации.

**Payload:**
```json
{
  "event_id": 123,
  "name": "...",
  "email": "...",
  "phone": "...",
  "message": "...",
  "seats": 1,
  "nonce": "...",
  "honeypot": "",
  "consents": { "42": "1" }
}
```

**Валидация:**
1. Nonce (form nonce `codeweber_event_register` или REST nonce `wp_rest`)
2. Honeypot (если заполнен — тихий success)
3. Event ID существует и тип `events`
4. Регистрация открыта (`status === open`)
5. Дубль: одинаковый email + event_id → ошибка

**При успехе:**
1. Создаётся пост `event_registrations` со статусом `reg_pending`
2. Пользователь создаётся/находится через `codeweber_forms_get_or_create_user()`
3. Согласия сохраняются в post_meta (`_reg_consents`) и user_meta
4. Newsletter интеграция через `codeweber_forms_newsletter_integration()`
5. Уведомление администратору (HTML-таблица с данными)
6. Ответ: `{ success: true, message: "...", registration_id: 456 }`

### `GET codeweber/v1/events/calendar`

FullCalendar feed.

**Query params:** `start`, `end` (ISO dates), `category` (term_id, опционально)

**Ответ:** массив объектов FullCalendar:
```json
[{
  "id": "123",
  "title": "Event Name",
  "start": "2026-03-15T10:00:00",
  "end": "2026-03-15T18:00:00",
  "url": "https://...",
  "backgroundColor": "#3b82f6",
  "extendedProps": {
    "location": "...",
    "registrationStatus": "open"
  }
}]
```

Цвет берётся из term_meta `event_color` категории (первая категория события).

---

## CPT `event_registrations`

Приватный CPT, видимый только в админке под меню Events.

### Статусы регистраций

| Slug | Метка | Цвет в UI |
|------|-------|----------|
| `reg_pending` | New | жёлтый |
| `reg_confirmed` | Confirmed | зелёный |
| `reg_cancelled` | Cancelled | красный |
| `reg_awaiting` | Awaiting Payment | синий |

### Admin столбцы

Event, Name, Email, Phone, Seats, Status (цветной badge), Date.

Фильтр по событию (dropdown). Сортировка по статусу и дате.

### Bulk actions

- Confirm selected → устанавливает `reg_confirmed`
- Cancel selected → устанавливает `reg_cancelled`

### Meta box деталей

Event, Name, Email, Phone, Seats, Message, Consents (с версией документа), ссылка на WP-пользователя.

### Badge в меню

Число регистраций в статусе `reg_pending` отображается как красный bubble в пункте меню Events.

---

## Single-страница события

Файл: [templates/singles/events/default.php](templates/singles/events/default.php)

Загружается через `single.php` → `get_template_part('templates/singles/events/default')`.

### Макет

```
.row.gx-lg-8
├── .col-lg-8  (основной контент)
│   ├── Featured image (только если sidebar_disable_image)
│   ├── Контент (или report_text если событие завершено)
│   ├── Галерея (Swiper с thumbs)
│   ├── Видео (glightbox)
│   └── Кнопка «Поделиться»
└── .col-lg-4  (sticky сайдбар)
    ├── .card (основная карточка)
    │   ├── Thumbnail
    │   ├── Категории + форматы (badges)
    │   ├── Event Details (даты, место, организатор, цена)
    │   ├── Countdown timer (JS, inline)
    │   ├── Add to Calendar (Apple + Google)
    │   ├── Автор
    │   ├── Seats counter (progress bar + счётчики)
    │   └── Кнопка регистрации (modal / external)
    ├── .card (форма inline, если show_form)
    └── Яндекс-карта (если включена)
```

### Кнопки регистрации в сайдбаре

```php
// Inline-форма (status === 'show_form'):
<form class="event-registration-form" data-event-id="...">

// Modal (status === 'modal'):
<a data-bs-toggle="modal" data-bs-target="#modal"
   data-value="event-reg-{$event_id}">

// External (status === 'external'):
<a href="{external_reg_url}" target="_blank">
```

### Countdown timer

Отображается при `status === open` (обратный отсчёт до `reg_close`) или `status === not_open_yet` (до `reg_open`). Реализован inline JS в конце шаблона, обновляет DOM каждую секунду.

---

## Форма регистрации

Partial: [templates/singles/events/registration-form.php](templates/singles/events/registration-form.php)

Используется для modal-режима (рендерится через REST API endpoint `wp/v2/modal/event-reg-{id}`).

Поля: name (required), email (required), phone, comment.
Honeypot: `event_reg_honeypot` (hidden, tabindex=-1).
Nonce: `event_reg_nonce` (form nonce `codeweber_event_register`).

---

## JS: обработка формы

Файл: [src/assets/js/event-registration-form.js](src/assets/js/event-registration-form.js)

Enqueue в `cpt-events.php` (только на `is_singular('events')`). Локализация: `codeweberEventReg.restUrl`, `codeweberEventReg.nonce`.

**Инициализация:**
- `DOMContentLoaded` → `initAllForms()` → инициализирует все `.event-registration-form`
- `shown.bs.modal` → реинициализация для форм внутри модала (с delay 100ms)
- `window.initEventRegForm` — публичный метод для ручного вызова

**Submit flow:**
1. HTML5 `checkValidity()` — Bootstrap `was-validated`
2. POST `codeweber/v1/events/register` (JSON)
3. Успех → `replaceModalContentWithEnvelope()` → fetch success-template → replace `modal-body` innerHTML → `bsModal.hide()` через 2с
4. Если нет модала → `showMessage()` inline в `.event-reg-form-messages`
5. Успех → `updateSeatsCounter()` — обновляет DOM счётчика мест без перезагрузки
6. Диспатч кастомного события `codeweberEventRegSubmitted`

---

## Archive-страница

Файл: [templates/archives/events/events_1.php](templates/archives/events/events_1.php)

### Два режима просмотра

Переключаются кнопками, выбор сохраняется в `localStorage` с ключом `cw_events_view`.

**Table (default):** Bootstrap-таблица с колонками Date, Event+Badge, Location, Format, Price, Details-button. Пагинация через `codeweber_pagination()`.

**Calendar:** FullCalendar `dayGridMonth` + `listMonth`. Инициализируется лениво при первом переключении. Данные через `GET codeweber/v1/events/calendar` с диапазоном дат и фильтром категории.

### Category filter

Ряд Bootstrap pill-кнопок `btn-soft-primary`. «All» + по одной на каждый непустой `event_category`. Активный класс через PHP `is_tax()`.

---

## Карточка события

Файл: [templates/post-cards/events/card-events.php](templates/post-cards/events/card-events.php)

`.col-md-6.col-lg-4`. Содержит: thumbnail, formats (badges), дата начала, title (stretched-link), excerpt (18 слов), price, location, status badge.

---

## Галерея события

Файл: [functions/integrations/event-gallery-metabox.php](functions/integrations/event-gallery-metabox.php)

**Admin:** meta box "Event Gallery". Загрузка через FilePond (AJAX action `codeweber_event_gallery_upload`). Перетаскивание для сортировки через SortableJS. Хранится в `_event_gallery` (массив attachment ID).

**Frontend:** Swiper с thumbnails (размеры `codeweber_event_1070-668` и `codeweber_event_140-88`). Lightbox через glightbox с галереей `event-gallery-{id}`.

---

## Add to Calendar / ICS

Файл: [functions/events/event-ics.php](functions/events/event-ics.php)

Срабатывает на `template_redirect` при `?ics=1` на single events. Генерирует VCALENDAR с VEVENT. Даты конвертируются в UTC. Скачивание браузером (`Content-Disposition: attachment`).

Ссылки в сайдбаре (скрываются при `_event_hide_add_to_calendar`):
- **Apple Calendar** → `{permalink}?ics=1`
- **Google Calendar** → `https://calendar.google.com/calendar/render?action=TEMPLATE&...`

---

## Видео

Хранится в `_event_video_type` + `_event_video_url` / `_event_video_file`.

Функция `codeweber_events_get_video_glightbox($event_id)` возвращает:
```php
[
  'href'        => '...',      // URL для glightbox
  'glightbox'   => '...',      // data-glightbox атрибут
  'inline_html' => '...',      // embed-код для file-видео (null для URL)
]
```

На frontend: кнопка с `data-glightbox` → открывает видео в lightbox.

---

## Яндекс-карта в сайдбаре

Показывается при `_event_show_map=1` + заполненные `_event_latitude`, `_event_longitude` + существует `Codeweber_Yandex_Maps` с API-ключом.

Параметры: `height=250`, без sidebar, без маршрута, без кластеризации. Marker с `hintContent` из `_event_yandex_address`.

---

## Согласия (Consents)

Мета `_event_reg_consents` — массив объектов:
```php
[
  ['label' => 'Я согласен с {document_title_url}', 'document_id' => '42', 'required' => '1'],
]
```

- `document_id` ссылается на CPT Documents
- `{document_title_url}` в label заменяется ссылкой на документ
- Auto-label fetch через AJAX `codeweber_forms_get_default_label` (при добавлении нового consent в admin)
- При регистрации сохраняются в `_reg_consents` поста `event_registrations` и в user_meta
- В `form-submit-universal.js` поля `reg_consent_{document_id}` собираются в объект `eventConsents` и передаются в `data.consents`

---

## Зависимости

| Зависимость | Для чего |
|-------------|---------|
| `Codeweber_Yandex_Maps` | Карта в сайдбаре |
| `Codeweber_Options` | card-radius, button-style, form-radius, phone-mask |
| `codeweber-forms` (integration) | Создание пользователя, newsletter, согласия |
| FilePond | Загрузка галереи (через `Codeweber\Blocks\Plugin::enqueue_filepond_assets_admin()`) |
| SortableJS | Сортировка галереи (CDN) |
| FullCalendar | Calendar view на archive (enqueue в cpt-events.php через условие `is_post_type_archive`) |
| glightbox | Галерея + видео |
| Swiper | Галерея с thumbs |

---

## Числа регистраций

Функция `codeweber_events_get_registration_count( $event_id )`:

```php
WP_Query([
  'post_type'   => 'event_registrations',
  'post_status' => ['reg_pending', 'reg_confirmed'],
  'meta_query'  => [['key' => '_event_id', 'value' => $event_id]],
])
→ found_posts + fake_registered
```

JS обновляет счётчик в DOM после успешной регистрации без перезагрузки (`updateSeatsCounter()`).
