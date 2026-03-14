# Audit Plan — api — 2026-03-14

Скорректированный план по результатам повторного сканирования скиллом `wp-rest-api`.

## Найдено проблем: 22
- 🔴 Критично: 5
- 🟡 Важно: 9
- 🔵 Рекомендации: 8

## Изменения относительно PLAN-all от 2026-03-13

| Старый ID | Статус | Причина |
|-----------|--------|---------|
| R003 | ⬇️ Понижен до 🟡 | Nonce корректно верифицируется на строке 226 + honeypot + rate limiting. Исходный план был неточен |
| R004 | ⬇️ Уточнён | Endpoint `/wp/v2/modal/cf-{id}` — это GET, не POST. Риск ниже, чем указано |
| — | ➕ Новый R004* | `POST /documents/send-email` с `__return_true` — отправка email без авторизации |
| — | ➕ Новый R005* | `POST /social-icons-preview` с `__return_true` — должен требовать `edit_posts` |
| — | ➕ Новый R006* | Matomo-эндпоинты `/cf7-form-opened` и `/cf7-form-error` без args |
| — | ➕ Новый R013* | Staff VCF endpoints создают temp-файлы без rate limiting |
| — | ➕ Новый R020* | `sanitize_text_field` для JSON-параметра в LoadMoreAPI |
| — | ➕ Новый R022* | Nonce генерируется для load-more, но не проверяется |

**Всего найдено 40 endpoint-ов** (17 в плагине, 21 в теме, 2 в настройках плагина).

---

## Проблемы

### 🔴 Критичные (5)

| ID | Файл | Описание | Тип |
|----|------|----------|-----|
| R001 | `codeweber-forms-api.php:66` | POST `/form-opened` с `__return_true`, без args, без nonce | auto |
| R002 | `inc/LoadMoreAPI.php:19` | POST `/load-more` с `__return_true`. `block_attributes` (JSON) декодируется и используется для WP_Query — атакующий может запросить любой post_type | manual |
| R004 | `cpt-documents.php:1003` | POST `/documents/send-email` с `__return_true` — отправка email на произвольные адреса. Nonce проверяется внутри callback, но endpoint доступен ботам | auto |
| R005 | `inc/Plugin.php:1491` | POST `/social-icons-preview` с `__return_true` — editor-only endpoint без авторизации | auto |
| R006 | `matomo-forms-integration.php:206,445` | POST `/cf7-form-opened` и `/cf7-form-error` с `__return_true` без args — инъекция фейковых данных в Matomo | auto |

### 🟡 Важные (9)

| ID | Файл | Описание | Тип |
|----|------|----------|-----|
| R003 | `testimonial-form-api.php:31` | POST `/submit-testimonial` с `__return_true` — nonce проверяется (строка 226), есть honeypot и rate limiting. Риск низкий, но `permission_callback` стоит привести к единому паттерну | auto |
| R007 | `inc/Plugin.php:1966` | `edit_posts` вместо `edit_post` для `/documents/{id}/spreadsheet` — любой редактор может изменить любой документ | manual |
| R008 | `inc/Plugin.php:1459` | `/render-shortcode` выполняет `do_shortcode()` без whitelist — потенциальная эскалация привилегий | manual |
| R009 | `restapi.php:9` | GET `/options` публичный — раскрывает настройки темы, ID форм, модалов | auto |
| R010 | `inc/VideoThumbnailAPI.php:18,33` | Proxy к Rutube/VK без rate limiting — исходящие запросы можно использовать для DoS | auto |
| R011 | `inc/Plugin.php:1947-1965` | CSV-эндпоинты без `sanitize_callback`, `/documents-csv` без args | auto |
| R013 | `cpt-staff.php:472`, `qr-code.php:967` | `/staff/{id}/vcf-url` создаёт файлы на диске без rate limiting — риск исчерпания диска | manual |
| R014 | `modal-rest-api.php:94` | `/wp/v2/modal/cf-{id}` — GET, но route остаётся discoverable если CodeweberForms не активен | auto |
| R020 | `inc/LoadMoreAPI.php:33` | `sanitize_text_field` для JSON-параметра — может повредить данные | auto |

### 🔵 Рекомендации (8)

| ID | Файл | Описание | Тип |
|----|------|----------|-----|
| R015 | `inc/Plugin.php` и все endpoints | REST endpoints без response schema | manual |
| R016 | Множество файлов | Непоследовательная стратегия nonce: в permission_callback / в callback / нигде | manual |
| R017 | `codeweber-forms-api.php:66` | `/form-opened` без args schema | auto |
| R018 | `modal-rest-api.php:121` | `/filepond-translations` без документации | auto |
| R019 | `inc/Plugin.php:1994` | `/navbar-preview` — 27+ параметров без validate_callback | manual |
| R021 | `inc/Plugin.php` множество | Публичные GET endpoints раскрывают конфигурацию (phones, logos, contacts) — данные уже на сайте, но структурированный доступ облегчает scraping | manual |
| R022 | `inc/LoadMoreAPI.php:19` | Nonce генерируется (`Plugin.php:705`), но не проверяется — впустую | auto |
| R023 | `modal-rest-api.php:94` | `/wp/v2/modal/cf-{id}` — нет проверки существования формы | auto |

---

## Итого: что можно исправить автоматически

**auto (14):** R001, R003, R004, R005, R006, R009, R010, R011, R014, R017, R018, R020, R022, R023

**manual (8):** R002, R007, R008, R013, R015, R016, R019, R021
