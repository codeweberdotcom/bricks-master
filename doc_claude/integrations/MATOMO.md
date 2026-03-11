# Matomo — интеграция аналитики

## Что делает этот модуль

Интегрирует WordPress-плагин [Matomo Analytics](https://wordpress.org/plugins/matomo/) с двумя подсистемами темы:

1. **Отслеживание форм** — события открытия форм, успешных отправок, ошибок. Работает для CodeWeber Forms и Contact Form 7.
2. **Отслеживание поиска** — поисковые запросы из AJAX-поиска сайта.

Все события отправляются через REST API Matomo-плагина: `POST /wp-json/matomo/v1/hit/`

---

## Файлы модуля

| Файл | Назначение |
|------|-----------|
| `functions/integrations/codeweber-forms/matomo-forms-integration.php` | Трекинг форм (CodeWeber Forms + CF7) |
| `functions/integrations/ajax-search-module/matomo-search-integration.php` | Трекинг поисковых запросов |

---

## Зависимости

- Плагин **Matomo** должен быть активен (`matomo/matomo.php`)
- Для трекинга CF7 — плагин **Contact Form 7** должен быть активен
- Активность проверяется через `is_plugin_active()` перед регистрацией хуков

---

## Модуль 1: Трекинг форм

**Файл:** [functions/integrations/codeweber-forms/matomo-forms-integration.php](../functions/integrations/codeweber-forms/matomo-forms-integration.php)

### Tracked Events (события в Matomo)

| Событие | Когда | Категория | Действие |
|---------|-------|-----------|---------|
| Открытие формы | Форма загружена на странице | `Codeweber Forms` / `Contact Form 7` | `Form Opened` |
| Успешная отправка | После отправки | — | `Form Submission` |
| Ошибка валидации | При невалидных полях CF7 | — | `Validation Error` |
| Ошибка сервера | При `wpcf7mailfailed` | — | `Server Error` |
| Спам | При `wpcf7spam` | — | `Spam Detected` |
| Ошибка отправки | При `codeweber_form_send_error` | — | `Form Error` |

**Название события** в Matomo: `{Название формы} (ID: {form_id})`, для CF7 добавляется суффикс `[CF7]`.

### Источники данных для хуков

| Форма | Хук/событие открытия | Хук отправки | Хук ошибки |
|-------|---------------------|--------------|-----------|
| CodeWeber Forms | `codeweber_form_opened` (PHP) | `codeweber_form_after_send` (PHP) | `codeweber_form_send_error` (PHP) |
| CF7 | `wpcf7-form` DOM-наблюдение (JS → REST) | `codeweber_form_after_send` (PHP) | `wpcf7invalid`, `wpcf7mailfailed`, `wpcf7spam` (JS → REST) |

### REST Endpoints (для CF7)

| Endpoint | Метод | Назначение |
|----------|-------|-----------|
| `POST /wp-json/codeweber-forms/v1/cf7-form-opened` | Публичный | Трекинг открытия CF7 формы |
| `POST /wp-json/codeweber-forms/v1/cf7-form-error` | Публичный | Трекинг ошибки CF7 формы |

Параметры запроса `cf7-form-opened`:
```json
{
    "form_id": "123",
    "url": "https://example.com/contact/"
}
```

Параметры запроса `cf7-form-error`:
```json
{
    "form_id": "123",
    "form_name": "Контактная форма",
    "error_type": "validation",  // validation | server | spam
    "error_count": 2,
    "url": "https://example.com/contact/"
}
```

### Visitor ID

Модуль идентифицирует посетителя по cookie Matomo `_pk_id_*`:

```php
// Читает первые 16 символов из cookie _pk_id_SITE_DOMAIN
// Если cookie нет — fallback: md5(IP + User-Agent)[:16]
$visitor_id = codeweber_forms_matomo_get_consistent_visitor_id();
```

### Настройки (wp_options)

| Option | По умолчанию | Описание |
|--------|-------------|---------|
| `codeweber_forms_matomo_track_forms` | `1` | Включить трекинг форм |
| `codeweber_forms_matomo_debug_mode` | `0` | Debug-режим (логирование в WP debug log) |

Управляются через страницу **Codeweber → Matomo Integration** в админке.

### Константа

```php
define('CODEWEBER_FORMS_MATOMO_SITE_ID', 1);  // ID сайта в Matomo
```

> Значение жёстко прописано в коде. Если сайт имеет другой ID в Matomo — нужно изменить константу в файле `matomo-forms-integration.php`.

---

## Модуль 2: Трекинг поиска

**Файл:** [functions/integrations/ajax-search-module/matomo-search-integration.php](../functions/integrations/ajax-search-module/matomo-search-integration.php)

Подключается к хуку `before_save_search_query` (из модуля AJAX-поиска) и отправляет поисковые события в Matomo.

### Событие в Matomo

| Категория | Действие | Название | Значение |
|-----------|---------|---------|---------|
| `Search` | `Query` | Текст поискового запроса | Количество результатов |

### Настройки (wp_options)

| Option | По умолчанию | Описание |
|--------|-------------|---------|
| `matomo_track_searches` | `1` | Включить трекинг поиска |
| `matomo_debug_mode` | `0` | Debug-режим |

Страница настроек: **Search Statistics → Matomo Integration** в админке.

### Константа

```php
define('MATOMO_SITE_ID', 1);  // ID сайта в Matomo
```

---

## Архитектура отправки событий

```
Событие (PHP хук или JS событие)
    ↓
codeweber_forms_matomo_track_form_event() или matomo_track_search_event()
    ↓
wp_remote_post('/wp-json/matomo/v1/hit/', [...params])
    ↓ (blocking: false — асинхронно, без ожидания ответа)
Matomo API endpoint (из плагина Matomo)
    ↓
Matomo записывает событие в БД
```

**Параметры POST-запроса к Matomo:**

```php
[
    'idsite'     => 1,               // ID сайта в Matomo
    'rec'        => 1,               // Обязательный флаг записи
    'ua'         => 'Mozilla/...',   // User-Agent
    '_id'        => 'abc123def456',  // Visitor ID (16 символов hex)
    'e_c'        => 'Codeweber Forms',  // Event Category
    'e_a'        => 'Form Submission',  // Event Action
    'e_n'        => 'Контактная форма (ID: 5)',  // Event Name
    'e_v'        => 1,               // Event Value
    'url'        => 'https://...',   // URL страницы с формой
    'urlref'     => 'https://...',   // Referrer
    'send_image' => 0,
]
```

**Таймаут для форм:** 2 сек, `blocking: false` (не блокирует PHP-процесс)
**Таймаут для поиска:** 10 сек

---

## JS-трекинг CF7 форм

При активном CF7 в `wp_footer` (приоритет 999) добавляется inline-скрипт, который:

1. Находит все `form.wpcf7-form` на странице и отправляет событие `Form Opened` через REST API
2. Использует `MutationObserver` для отслеживания динамически добавленных форм (в модальных окнах)
3. Слушает DOM-события CF7:
   - `wpcf7invalid` — ошибка валидации
   - `wpcf7mailfailed` — ошибка отправки письма
   - `wpcf7spam` — определён спам

Форма отмечается атрибутом `data-matomo-opened="true"` чтобы не отправлять событие дважды.

---

## Страницы в админке

| Страница | Путь |
|---------|------|
| Настройки трекинга форм | Codeweber → Matomo Integration |
| Настройки трекинга поиска | Search Statistics → Matomo Integration |

Обе страницы показываются **только если плагин Matomo активен**.

---

## Быстрая диагностика

**Matomo не записывает события — что проверить:**

1. Плагин Matomo активен (`is_plugin_active('matomo/matomo.php')`)
2. `codeweber_forms_matomo_track_forms = 1` в wp_options
3. `CODEWEBER_FORMS_MATOMO_SITE_ID` соответствует реальному ID сайта в Matomo
4. REST-endpoint `/wp-json/matomo/v1/hit/` доступен (плагин Matomo регистрирует его)
5. Включить debug mode → смотреть WP debug log (`wp-content/debug.log`)

**CF7 открытие не трекится:**
- Скрипт добавляется только если `class_exists('WPCF7')` и `codeweber_forms_matomo_is_plugin_active()`
- Проверить в DevTools → Network: должен быть POST на `cf7-form-opened`
