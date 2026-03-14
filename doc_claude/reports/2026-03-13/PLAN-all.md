# Audit Plan — all — 2026-03-13

## Найдено проблем: 46
- 🔴 Критично: 14 (blocks: 3, api: 4, perf: 7)
- 🟡 Важно: 17 (blocks: 5, api: 6, perf: 6)
- 🔵 Рекомендации: 15 (blocks: 4, api: 5, perf: 6)

## Snapshot (точка отката): `1a46c30`

---

## БЛОКИ (wp-block-development)

| ID | Приоритет | Файл | Описание | Тип |
|----|-----------|------|----------|-----|
| P001 | 🔴 | `src/blocks/form/block.json:2` | apiVersion 2 (устарел, нужен 3) | auto |
| P002 | 🔴 | `src/blocks/divider/block.json:2` | apiVersion 2 (устарел, нужен 3) | auto |
| P003 | 🔴 | `src/blocks/form/block.json:216` | Несоответствие namespace в allowedBlocks | manual |
| P004 | 🟡 | `package.json:27` | post-grid и logo render.php не копируются при сборке | auto |
| P005 | 🟡 | `package.json:44` | Невалидная версия зависимости `"^21.10.0start"` | auto |
| P006 | 🟡 | `src/blocks/cta/block.json` | textdomain `"codeweber-blocks"` вместо `"codeweber-gutenberg-blocks"` | auto |
| P007 | 🟡 | `src/blocks/label-plus/block.json` | Отсутствует поле textdomain | auto |
| P008 | 🟡 | 9 render.php файлов | Отсутствует `get_block_wrapper_attributes()` | manual |
| P009 | 🔵 | Все block.json | Отсутствует поле `$schema` в большинстве блоков | auto |
| P010 | 🔵 | menu, label-plus, heading-subtitle | Нет `"style"` поля — frontend-стили не загрузятся | manual |
| P011 | 🔵 | Все блоки | Непоследовательные значения category | manual |
| P012 | 🔵 | 4 blog-блока | render_callback вместо `"render"` в block.json | manual |

---

## REST API (wp-rest-api)

| ID | Приоритет | Файл | Описание | Тип |
|----|-----------|------|----------|-----|
| R001 | 🔴 | `functions/integrations/codeweber-forms/codeweber-forms-api.php:66` | POST /form-opened с `__return_true` — нет авторизации | auto |
| R002 | 🔴 | `inc/LoadMoreAPI.php:19` | JSON параметр без schema-валидации | manual |
| R003 | 🔴 | `functions/testimonials/testimonial-form-api.php:31` | Nonce принимается но не верифицируется | auto |
| R004 | 🔴 | `functions/integrations/modal-rest-api.php:92` | Публичный POST без защиты | auto |
| R005 | 🟡 | `inc/Plugin.php:1966` | edit_posts вместо edit_post для документа | manual |
| R006 | 🟡 | `inc/Plugin.php:1458` | Shortcode выполняется без whitelist | manual |
| R007 | 🟡 | `settings/options_page/restapi.php:9` | Публичные options-эндпоинты без схемы | auto |
| R008 | 🟡 | `inc/LoadMoreAPI.php:19` | load-more публичный POST без защиты от DoS | auto |
| R009 | 🟡 | `inc/VideoThumbnailAPI.php:16` | Прокси к Rutube/VK без авторизации | auto |
| R010 | 🟡 | `inc/Plugin.php:1952` | CSV-эндпоинт без `type` и `sanitize_callback` | auto |
| R011 | 🔵 | `functions/integrations/codeweber-forms/codeweber-forms-api.php:65` | /form-opened без args schema | auto |
| R012 | 🔵 | `functions/integrations/modal-rest-api.php:119` | filepond-translations эндпоинт без документации | auto |
| R013 | 🔵 | `inc/Plugin.php` (множество) | REST-эндпоинты без response schema | manual |
| R014 | 🔵 | `functions/integrations/codeweber-forms/codeweber-forms-api.php` | Непоследовательная стратегия nonce | manual |
| R015 | 🔵 | `inc/Plugin.php:1994` | /navbar-preview — 27+ параметров без validate_callback | manual |

---

## ПРОИЗВОДИТЕЛЬНОСТЬ (wp-performance + wp-performance-review)

| ID | Приоритет | Файл | Описание | Тип |
|----|-----------|------|----------|-----|
| PERF001 | 🔴 | 59 мест в теме и плагине | `posts_per_page => -1` — загрузка всех постов | auto |
| PERF002 | 🔴 | 5 мест | `numberposts => -1` в get_posts() | auto |
| PERF003 | 🔴 | `functions/ajax-filter.php:59` | AJAX-фильтр без пагинации — грузит все посты | auto |
| PERF004 | 🔴 | `functions/integrations/ajax-search-module/ajax-search.php:127` | AJAX-поиск без лимита результатов | auto |
| PERF005 | 🔴 | `src/blocks/yandex-map/render.php:137` | N+1 запросов для meta в цикле | auto |
| PERF006 | 🔴 | `functions/integrations/dadata/class-codeweber-dadata.php:122` | Таймаут DaData 15 сек → блокирует пользователя | auto |
| PERF007 | 🟡 | `functions/integrations/dadata/dadata-ajax.php:15` | Rate limiting через transients вместо object cache | auto |
| PERF008 | 🟡 | `src/assets/php/PHPMailer/get_oauth_token.php:80` | `session_start()` — блокирует page cache | manual |
| PERF009 | 🟡 | Все WP_Query | Нигде не используется `no_found_rows => true` | auto |
| PERF010 | 🟡 | 10 файлов | Heredoc/nowdoc с переменными без escaping | auto |
| PERF011 | 🟡 | `functions/integrations/dadata/dadata-ajax.php` | Повторные Redux::get_option() без кэша | manual |
| PERF012 | 🟡 | ajax-filter, ajax-search, dadata | POST для read-операций — не кэшируется CDN | auto |
| PERF013 | 🟡 | Везде где transients | Transients без проверки наличия object cache | manual |
| REC001 | 🔵 | Блоки с WP_Query | Нет cache priming после запроса | manual |
| REC002 | 🔵 | AJAX-обработчики | Перевести на REST API GET-запросы | manual |
| REC003 | 🔵 | ajax-filter | Добавить пагинацию к результатам фильтра | manual |
| REC004 | 🔵 | REST-эндпоинты плагина | Кэшировать ответы REST API | manual |
| REC005 | 🔵 | `functions/enqueues.php` | Условная загрузка ассетов по блокам | manual |
| REC006 | 🔵 | `src/blocks/tabulator/edit.js:176` | setInterval — проверить что не на фронте | manual |

---

## Итого: что можно исправить автоматически

**auto-пункты (23):**
P001, P002, P004, P005, P006, P007, P009 — блоки
R001, R003, R004, R007, R008, R009, R010, R011, R012 — REST API
PERF001, PERF002, PERF003, PERF004, PERF005, PERF006, PERF007, PERF009 — производительность

**manual-пункты (23) — требуют решения:**
P003, P008, P010, P011, P012
R002, R005, R006, R013, R014, R015
PERF008, PERF010, PERF011, PERF012, PERF013, REC001–REC006

---

## Статус выполнения

| ID | Статус |
|----|--------|
| P001 | ⏳ ожидает |
| P002 | ⏳ ожидает |
| ... | ... |
