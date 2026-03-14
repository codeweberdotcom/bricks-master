# Audit Plan — blocks — 2026-03-14

Скорректированный план по результатам повторного сканирования скиллом `wp-block-development`.

## Найдено проблем: 10
- 🔴 Критично: 3
- 🟡 Важно: 3
- 🔵 Рекомендации: 4

## Изменения относительно PLAN-all от 2026-03-13

| Старый ID | Статус | Причина |
|-----------|--------|---------|
| P004 | ❌ Снят | `logo` и `post-grid` имеют `"render": "file:./render.php"` в block.json — wp-scripts копирует автоматически. В build/ файлы на месте |
| P009 | 🔽 Понижен до 🔵 | `$schema` — рекомендация WordPress, но не влияет на работу блоков |
| P011 | ❌ Снят | 3 категории (`elements`, `blocks`, `widgets`) — намеренная структура, не баг |
| P012 | 🔽 Понижен до 🟡 | 4 blog-блока работают через `render_callback` — функционально, но лучше перевести на `render` в block.json |

---

## Проблемы

| ID | Приоритет | Файл | Описание | Тип |
|----|-----------|------|----------|-----|
| P001 | 🔴 | `src/blocks/form/block.json:2` | apiVersion 2 — нужен 3 для iframe-редактора WP 7.0 | auto |
| P002 | 🔴 | `src/blocks/divider/block.json:2` | apiVersion 2 — нужен 3 | auto |
| P003 | 🔴 | `src/blocks/form/block.json:216` | allowedBlocks: `codeweber-gutenberg-blocks/heading-subtitle` — неверный namespace (должен быть `codeweber-blocks/heading-subtitle`) | auto |
| P005 | 🟡 | `package.json:44` | Невалидная версия зависимости `"^21.10.0start"` для `@wordpress/stylelint-config` | auto |
| P006 | 🟡 | `src/blocks/cta/block.json` | textdomain `"codeweber-blocks"` вместо `"codeweber-gutenberg-blocks"` | auto |
| P007 | 🟡 | `src/blocks/label-plus/block.json` | Отсутствует поле `textdomain` | auto |
| P008 | 🔵 | 21 render.php файлов | Отсутствует `get_block_wrapper_attributes()` — блоки работают, но не поддерживают кастомные классы/стили через editor supports | manual |
| P009 | 🔵 | Все 48 block.json | Отсутствует поле `$schema` — не влияет на работу, но полезно для IDE-валидации | auto |
| P010 | 🔵 | `heading-subtitle`, `menu` | Есть `style.scss`, но нет `"style"` поля в block.json — фронтовые стили не подключаются через block registration | manual |
| P012 | 🔵 | blog-category/post/tag/year-widget | `render_callback` в Plugin.php вместо `"render"` в block.json — работает, но нестандартный паттерн | manual |

---

## Блоки без `get_block_wrapper_attributes()` (P008)

avatar, blog-category-widget, blog-post-widget, blog-tag-widget, blog-year-widget, contacts, form, form-field, header-widgets, html-blocks, lists, logo, menu, navbar, search, shortcode-render, social-icons, swiper, tables, tabulator, top-header

**Примечание:** accordion использует комментарий "формируем атрибуты вручную, чтобы избежать проблем с контекстом блока". Нужна ручная проверка каждого случая — не все render.php могут безопасно использовать `get_block_wrapper_attributes()`.

---

## Итого: что можно исправить автоматически

**auto (6):** P001, P002, P003, P005, P006, P007, P009

**manual (3):** P008, P010, P012
