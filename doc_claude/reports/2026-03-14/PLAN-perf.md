# Audit Plan — perf — 2026-03-14

Скорректированный план по результатам повторного сканирования скиллами `wp-performance` и `wp-performance-review`.

## Найдено проблем: 20
- 🔴 Критично: 7
- 🟡 Важно: 7
- 🔵 Рекомендации: 6

## Изменения относительно PLAN-all от 2026-03-13

| Старый ID | Статус | Причина |
|-----------|--------|---------|
| PERF001 | ⬆️ Уточнён | Пересчёт: **77 вхождений** (73 тема + 4 плагин), план указывал 59 |
| PERF005 | ⬆️ Уточнён | **12 вызовов get_post_meta** на итерацию цикла, не просто "N+1" |
| PERF009 | ⬆️ Повышен до 🔴 | **Ноль** использований `no_found_rows` во всём проекте — каждый WP_Query делает лишний SQL |
| — | ➕ Новый PERF014 | Тройной запрос в ajax-search: main + count(-1) + per-type count(-1) |

---

## Проблемы

### 🔴 Критичные (7)

| ID | Файл | Описание | Тип |
|----|------|----------|-----|
| PERF001 | 77 мест (45 файлов темы + 3 файла плагина) | `posts_per_page => -1` — загрузка всех записей. Ключевые: `ajax-filter.php:59`, `ajax-search.php:127,336,419`, `archive-faq.php:88,103,175`, `cpt-notifications.php:124,396,678,852`, `cpt-offices.php:189,511,555`, `modal-container.php:56`, `yandex-map/render.php:91`, `Plugin.php:2118` | auto |
| PERF002 | 5 мест в 4 файлах | `numberposts => -1` в get_posts(): `codeweber-forms-document-version.php:72`, `codeweber-forms-consent-helper.php:78`, `matomo-forms-integration.php:496,502`, `newsletter-settings.php:78` | auto |
| PERF003 | `ajax-filter.php:59` | AJAX-фильтр `posts_per_page => -1` без пагинации, все результаты отдаются одним HTML-блоком | auto |
| PERF004 | `ajax-search.php:127,336,419` | AJAX-поиск: `handle_ajax_search_load_all()` грузит всё, плюс дополнительные count-запросы с `-1` | auto |
| PERF005 | `yandex-map/render.php:137-174` | N+1: **12 вызовов `get_post_meta()`** на итерацию. 50 офисов = ~600 запросов. Решение: `update_post_meta_cache()` перед циклом | auto |
| PERF006 | `class-codeweber-dadata.php:125,284` | Таймауты DaData: `clean_address` = **15 сек**, `suggest_address` = **10 сек** — блокируют UI | auto |
| PERF007 | Весь проект | **Ноль** использований `no_found_rows => true`. Каждый WP_Query делает лишний `SELECT FOUND_ROWS()` даже когда пагинация не нужна | auto |

### 🟡 Важные (7)

| ID | Файл | Описание | Тип |
|----|------|----------|-----|
| PERF008 | `dadata-ajax.php:15-25` | Rate limiting через transients вместо object cache. Не атомарно — race condition обходит лимит | auto |
| PERF009 | `get_oauth_token.php:80` | `session_start()` — отправляет `Set-Cookie` и `Cache-Control: private`, ломает page cache. Файл standalone, влияет только на OAuth flow | manual |
| PERF010 | 5 AJAX-обработчиков | POST для read-операций: `ajax-filter`, `ajax-search`, `ajax-search-load-all`, `dadata-suggest`, `fetch-action` — не кэшируется CDN | auto |
| PERF011 | `dadata-ajax.php` | Повторные `Redux::get_option()` в каждом обработчике | manual |
| PERF012 | 23+ мест в теме | Transients без проверки наличия object cache — всё пишется в `wp_options`, создаёт write contention | manual |
| PERF013 | 10+ файлов | Heredoc с переменными без escaping | auto |
| PERF014 | `ajax-search.php:330-433` | **Новый**: Тройной запрос — main query + count с `-1` + per-type count с `-1`. Count-запрос дублирует `$query->found_posts` | manual |

### 🔵 Рекомендации (6)

| ID | Файл | Описание | Тип |
|----|------|----------|-----|
| REC001 | Блоки с WP_Query | Нет `update_post_meta_cache()` / `update_post_thumbnail_cache()` после запросов | manual |
| REC002 | ajax-filter, ajax-search, fetch-handler | Перевести read-only AJAX на REST API GET | manual |
| REC003 | `ajax-filter.php` | Добавить пагинацию к результатам фильтра | manual |
| REC004 | REST-эндпоинты плагина | Кэшировать ответы (Cache-Control / transient) | manual |
| REC005 | `enqueues.php` | Условная загрузка ассетов по блокам на странице | manual |
| REC006 | `tabulator/edit.js:176` | `setInterval` — проверить что не утекает на фронт | manual |

---

## Итого: что можно исправить автоматически

**auto (11):** PERF001, PERF002, PERF003, PERF004, PERF005, PERF006, PERF007, PERF008, PERF010, PERF013

**manual (9):** PERF009, PERF011, PERF012, PERF014, REC001-REC006
