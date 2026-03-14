# Audit Plan — all — 2026-03-14

Скорректированный сводный план по результатам повторного сканирования всеми скиллами.
Базовый план: `2026-03-13/PLAN-all.md`.

## Найдено проблем: 52
- 🔴 Критично: 15 (blocks: 3, api: 5, perf: 7)
- 🟡 Важно: 19 (blocks: 3, api: 9, perf: 7)
- 🔵 Рекомендации: 18 (blocks: 4, api: 8, perf: 6)

## Snapshot (точка отката): `1a46c30`

---

## Сравнение с планом от 2026-03-13

| Метрика | 13 марта | 14 марта | Δ |
|---------|----------|----------|---|
| Всего проблем | 46 | 52 | +6 |
| 🔴 Критично | 14 | 15 | +1 |
| 🟡 Важно | 17 | 19 | +2 |
| 🔵 Рекомендации | 15 | 18 | +3 |

### Снятые пункты (3)
| ID | Причина |
|----|---------|
| P004 | `logo` и `post-grid` работают через `"render"` в block.json — wp-scripts копирует автоматически |
| P011 | 3 категории блоков — намеренная структура, не баг |
| R004(old) | Endpoint `/wp/v2/modal/cf-{id}` — GET, не POST. Риск ниже |

### Понижены (3)
| ID | Было | Стало | Причина |
|----|------|-------|---------|
| R003 | 🔴 | 🟡 | Nonce корректно проверяется + honeypot + rate limiting |
| P009 | 🟡 | 🔵 | `$schema` не влияет на работу блоков |
| P012 | 🟡 | 🔵 | `render_callback` работает, просто нестандартный паттерн |

### Повышены (1)
| ID | Было | Стало | Причина |
|----|------|-------|---------|
| PERF009→PERF007 | 🟡 | 🔴 | **Ноль** использований `no_found_rows` во всём проекте |

### Новые находки (+9)
| ID | Приоритет | Описание |
|----|-----------|----------|
| R004(new) | 🔴 | POST `/documents/send-email` с `__return_true` — email abuse |
| R005(new) | 🔴 | POST `/social-icons-preview` без авторизации |
| R006(new) | 🔴 | Matomo endpoints `/cf7-form-opened`, `/cf7-form-error` без args |
| R013(new) | 🟡 | Staff VCF создаёт temp-файлы без rate limiting |
| R020 | 🟡 | `sanitize_text_field` для JSON — повреждает данные |
| R022 | 🔵 | Nonce для load-more генерируется, но не проверяется |
| R023 | 🔵 | `/wp/v2/modal/cf-{id}` без проверки существования формы |
| PERF007 | 🔴 | `no_found_rows` нигде не используется (повышен) |
| PERF014 | 🟡 | Тройной запрос в ajax-search |

### Уточнения
| ID | Уточнение |
|----|-----------|
| PERF001 | 77 вхождений (было 59) — пересчёт |
| PERF005 | 12 вызовов `get_post_meta()` на итерацию (уточнено) |

---

## БЛОКИ — 10 проблем

| ID | Приоритет | Файл | Описание | Тип |
|----|-----------|------|----------|-----|
| P001 | 🔴 | `src/blocks/form/block.json:2` | apiVersion 2 → нужен 3 | auto |
| P002 | 🔴 | `src/blocks/divider/block.json:2` | apiVersion 2 → нужен 3 | auto |
| P003 | 🔴 | `src/blocks/form/block.json:216` | allowedBlocks: `codeweber-gutenberg-blocks/heading-subtitle` — неверный namespace | auto |
| P005 | 🟡 | `package.json:44` | Невалидная версия `"^21.10.0start"` | auto |
| P006 | 🟡 | `src/blocks/cta/block.json` | textdomain `"codeweber-blocks"` → `"codeweber-gutenberg-blocks"` | auto |
| P007 | 🟡 | `src/blocks/label-plus/block.json` | Отсутствует textdomain | auto |
| P008 | 🔵 | 21 render.php | Отсутствует `get_block_wrapper_attributes()` | manual |
| P009 | 🔵 | Все 48 block.json | Отсутствует `$schema` | auto |
| P010 | 🔵 | heading-subtitle, menu | Есть `style.scss`, но нет `"style"` в block.json | manual |
| P012 | 🔵 | 4 blog-блока | `render_callback` в Plugin.php вместо `"render"` в block.json | manual |

---

## REST API — 22 проблемы

| ID | Приоритет | Файл | Описание | Тип |
|----|-----------|------|----------|-----|
| R001 | 🔴 | `codeweber-forms-api.php:66` | POST `/form-opened` `__return_true`, без args, без nonce | auto |
| R002 | 🔴 | `inc/LoadMoreAPI.php:19` | POST `/load-more` `__return_true`, JSON→WP_Query без ограничения post_type | manual |
| R004 | 🔴 | `cpt-documents.php:1003` | POST `/documents/send-email` `__return_true` — email abuse | auto |
| R005 | 🔴 | `inc/Plugin.php:1491` | POST `/social-icons-preview` `__return_true` — editor-only | auto |
| R006 | 🔴 | `matomo-forms-integration.php:206,445` | POST Matomo endpoints без args | auto |
| R003 | 🟡 | `testimonial-form-api.php:31` | POST `/submit-testimonial` — nonce ОК, но паттерн `permission_callback` неконсистентен | auto |
| R007 | 🟡 | `inc/Plugin.php:1966` | `edit_posts` вместо `edit_post` | manual |
| R008 | 🟡 | `inc/Plugin.php:1459` | Shortcode без whitelist | manual |
| R009 | 🟡 | `restapi.php:9` | Публичный GET `/options` — раскрывает настройки | auto |
| R010 | 🟡 | `VideoThumbnailAPI.php:18,33` | Proxy без rate limiting | auto |
| R011 | 🟡 | `inc/Plugin.php:1947-1965` | CSV без `sanitize_callback` | auto |
| R013 | 🟡 | `cpt-staff.php:472`, `qr-code.php:967` | VCF temp-файлы без rate limiting | manual |
| R014 | 🟡 | `modal-rest-api.php:94` | Route discoverable без CodeweberForms | auto |
| R020 | 🟡 | `LoadMoreAPI.php:33` | `sanitize_text_field` для JSON | auto |
| R015 | 🔵 | Все endpoints | Нет response schema | manual |
| R016 | 🔵 | Множество | Непоследовательная nonce-стратегия | manual |
| R017 | 🔵 | `codeweber-forms-api.php:66` | `/form-opened` без args schema | auto |
| R018 | 🔵 | `modal-rest-api.php:121` | `/filepond-translations` без документации | auto |
| R019 | 🔵 | `inc/Plugin.php:1994` | `/navbar-preview` 27+ параметров без validate_callback | manual |
| R021 | 🔵 | `inc/Plugin.php` множество | Публичные GET раскрывают конфигурацию | manual |
| R022 | 🔵 | `LoadMoreAPI.php:19` | Nonce генерируется, но не проверяется | auto |
| R023 | 🔵 | `modal-rest-api.php:94` | Нет проверки существования формы | auto |

---

## ПРОИЗВОДИТЕЛЬНОСТЬ — 20 проблем

| ID | Приоритет | Файл | Описание | Тип |
|----|-----------|------|----------|-----|
| PERF001 | 🔴 | 77 мест (45 файлов темы + 3 плагина) | `posts_per_page => -1` | auto |
| PERF002 | 🔴 | 5 мест в 4 файлах | `numberposts => -1` | auto |
| PERF003 | 🔴 | `ajax-filter.php:59` | AJAX-фильтр без пагинации | auto |
| PERF004 | 🔴 | `ajax-search.php:127,336,419` | AJAX-поиск без лимита | auto |
| PERF005 | 🔴 | `yandex-map/render.php:137-174` | 12× `get_post_meta()` на итерацию | auto |
| PERF006 | 🔴 | `class-codeweber-dadata.php:125,284` | Таймауты 15с/10с | auto |
| PERF007 | 🔴 | Весь проект | **0** использований `no_found_rows => true` | auto |
| PERF008 | 🟡 | `dadata-ajax.php:15-25` | Rate limiting через transients, race condition | auto |
| PERF009 | 🟡 | `get_oauth_token.php:80` | `session_start()` ломает page cache | manual |
| PERF010 | 🟡 | 5 AJAX-обработчиков | POST для read-операций | auto |
| PERF011 | 🟡 | `dadata-ajax.php` | Повторные `Redux::get_option()` | manual |
| PERF012 | 🟡 | 23+ мест | Transients без object cache check | manual |
| PERF013 | 🟡 | 10+ файлов | Heredoc без escaping | auto |
| PERF014 | 🟡 | `ajax-search.php:330-433` | Тройной запрос: main + count(-1) + per-type(-1) | manual |
| REC001 | 🔵 | Блоки с WP_Query | Нет cache priming | manual |
| REC002 | 🔵 | AJAX-обработчики | Перевести на REST API GET | manual |
| REC003 | 🔵 | `ajax-filter.php` | Добавить пагинацию | manual |
| REC004 | 🔵 | REST-эндпоинты | Кэшировать ответы | manual |
| REC005 | 🔵 | `enqueues.php` | Условная загрузка ассетов | manual |
| REC006 | 🔵 | `tabulator/edit.js:176` | `setInterval` — проверить фронт | manual |

---

## Итого: auto vs manual

**auto (31):**
- Блоки: P001, P002, P003, P005, P006, P007, P009
- API: R001, R003, R004, R005, R006, R009, R010, R011, R014, R017, R018, R020, R022, R023
- Perf: PERF001, PERF002, PERF003, PERF004, PERF005, PERF006, PERF007, PERF008, PERF010, PERF013

**manual (21):**
- Блоки: P008, P010, P012
- API: R002, R007, R008, R013, R015, R016, R019, R021
- Perf: PERF009, PERF011, PERF012, PERF014, REC001-REC006

---

## Статус выполнения

| ID | Статус |
|----|--------|
| P001 | ⏳ ожидает |
| P002 | ⏳ ожидает |
| P003 | ⏳ ожидает |
| P005 | ⏳ ожидает |
| P006 | ⏳ ожидает |
| P007 | ⏳ ожидает |
| P009 | ⏳ ожидает |
| R001 | ⏳ ожидает |
| R003 | ⏳ ожидает |
| R004 | ⏳ ожидает |
| R005 | ⏳ ожидает |
| R006 | ⏳ ожидает |
| R009 | ⏳ ожидает |
| R010 | ⏳ ожидает |
| R011 | ⏳ ожидает |
| R014 | ⏳ ожидает |
| R017 | ⏳ ожидает |
| R018 | ⏳ ожидает |
| R020 | ⏳ ожидает |
| R022 | ⏳ ожидает |
| R023 | ⏳ ожидает |
| PERF001 | ⏳ ожидает |
| PERF002 | ⏳ ожидает |
| PERF003 | ⏳ ожидает |
| PERF004 | ⏳ ожидает |
| PERF005 | ⏳ ожидает |
| PERF006 | ⏳ ожидает |
| PERF007 | ⏳ ожидает |
| PERF008 | ⏳ ожидает |
| PERF010 | ⏳ ожидает |
| PERF013 | ⏳ ожидает |
