# CLAUDE.md — Тема CodeWeber

Инструкции для Claude Code при работе с темой и WordPress-проектом.

## Обзор проекта

WordPress-сайт, работающий локально через **Laragon** на Windows. Вся кастомизация сосредоточена в двух местах:

- **Тема:** `wp-content/themes/codeweber` — кастомная тема на Bootstrap 5, собирается через Gulp + SCSS
- **Плагин:** `wp-content/plugins/codeweber-gutenberg-blocks` — плагин кастомных блоков Gutenberg, собирается через `@wordpress/scripts`

**Окружение:** Laragon, PHP 8.x, MySQL (БД `codeweber2026`, пользователь `root`, без пароля), `WP_DEBUG=true`.

---

## Разработка темы (`wp-content/themes/codeweber/`)

```bash
npm install          # Установить зависимости
npm start            # Режим разработки: gulp serve (BrowserSync + watch)
npm run build        # Продакшен: gulp build:dist → компилирует в dist/ активной дочерней темы
```

Gulp автоматически определяет активную дочернюю тему через PHP-скрипт (`functions/gulp-theme-info.php`) и выводит скомпилированные ассеты туда. Всегда запускать Gulp из директории родительской темы.

Директория `src/` темы содержит HTML-демо-шаблоны (не WordPress-шаблоны). WordPress-шаблоны находятся в корне (`page.php`, `single.php`, `archive-*.php` и т.д.) и в `templates/`.

---

## Разработка Gutenberg-плагина (`wp-content/plugins/codeweber-gutenberg-blocks/`)

```bash
npm install
npm run start        # Режим разработки с горячей перезагрузкой (wp-scripts start)
npm run build        # Продакшен-сборка; скомпилированный build/ должен попасть в коммит
npm run lint:js      # Линтинг JS/JSX — ошибки уровня error блокируют merge
npm run lint:css     # Линтинг SCSS
npm run format       # Применить Prettier
npm run i18n:update  # Перегенерировать POT + скомпилировать переводы
```

**Директория `build/` коммитится в git.** Всегда запускать `npm run build` перед коммитом.

---

## Архитектура

### Структура темы

| Путь | Назначение |
|------|-----------|
| `functions.php` | Подключает все модули через `require_once` |
| `functions/cpt/` | Пользовательские типы записей (Header, Footer, PageHeader, Modals, HTML Blocks, Clients, Notifications, Staff, FAQ, Testimonials, Vacancies, Offices, Services, Projects, Legal, Documents, Price List) |
| `functions/enqueues.php` | Регистрация скриптов и стилей; сначала проверяется дочерняя тема, затем родительская |
| `functions/integrations/` | CF7, DaData, Яндекс Карты, SMS.ru, Matomo, GDPR Personal Data V2, Newsletter, CodeWeber Forms, AJAX-поиск, лицензии изображений |
| `functions/admin/` | Страницы настроек в админке |
| `functions/fetch/` | Обработчики AJAX-запросов |
| `functions/lib/` | Nav-walkers (Bootstrap, вертикальный dropdown, collapse), вспомогательные функции комментариев |
| `redux-framework/` | Redux Framework для настроек темы (ключ опции: `redux_demo`) |
| `templates/header/` | Несколько вариантов шапки (classic, fancy, extended, center-logo и их комбинации) |
| `templates/components/` | Переиспользуемые части шаблонов (кнопка «наверх», соцсети и т.д.) |
| `templates/post-cards/` | Система шаблонов карточек постов |

### Структура Gutenberg-плагина

| Путь | Назначение |
|------|-----------|
| `plugin.php` | Точка входа, автозагрузчик, хуки |
| `inc/Plugin.php` | Основной класс: регистрация блоков, категории, REST API |
| `src/blocks/` | Исходники отдельных блоков (accordion, swiper, form, menu, navbar, yandex-map и др.) |
| `src/components/` | Общие компоненты для Inspector/Sidebar (background, spacing, animation, colors, layout и др.) |
| `src/utilities/` | Генераторы классов, утилиты цвета, иконок, типов ссылок |
| `build/blocks/` | Скомпилированный вывод — коммитится в git |
| `settings/` | Страница настроек плагина в админке |
| `doc/` | Полная документация (PLUGIN_OVERVIEW.md, BLOCKS_REFERENCE.md, GUTENBERG_BLOCK_STANDARDS.md, DEV_WORKFLOW.md) |

---

## Ключевые правила для Gutenberg-блоков

- **На фронте — только классы Bootstrap.** Использовать `btn`, `row`, `col-*`, `card`, `container` и т.д. из темы codeweber/Bootstrap 5. Не писать кастомные стили для того, что уже покрыто темой.
- **`@wordpress/components` — только в Inspector/Sidebar.** Никогда не использовать для рендеринга на фронте.
- **Префикс кастомных CSS-классов:** `cwgb-` или `codeweber-` для любых специфичных для блока классов.
- **Структура файлов каждого блока:**
  - `block.json` — источник правды для `name`, `title`, `attributes`, `supports`
  - `index.js` — `registerBlockType`
  - `edit.js` — UI редактора + Inspector Controls
  - `save.js` — статическое сохранение (или `null` для динамических блоков с `render.php`)
  - `style.scss` — стили фронта; `editor.scss` — стили только для редактора
- **Динамические блоки** требуют `render_callback`, зарегистрированного в `inc/Plugin.php`, и файла `render.php`, который копируется в `build/` через скрипт `copy-php`.
- **Устаревшие блоки:** при изменении структуры атрибутов добавлять запись в массив `deprecated` в `index.js`.

## Настройки темы (Redux)

Ключ опции Redux Framework — `redux_demo`. Доступ к настройкам темы через `get_option('redux_demo')`. Настройки регистрируются в `redux-framework/sample/theme-config.php` и `redux-framework/theme-settings/theme-settings.php`.

## Правило согласования плана

**КРИТИЧНО — нарушение недопустимо:**

Перед ЛЮБЫМ изменением файлов — изложить план пошагово и дождаться явного одобрения пользователя. Не начинать реализацию без явного «да».

- Читающие действия (Read, Grep, Glob) без изменений — можно без согласования
- Если в процессе план меняется — снова согласовать, не действовать самостоятельно
- Никаких инициативных «заодно исправлю» — только то, что явно одобрено

---

## Git-правила

**ОБЯЗАТЕЛЬНО перед любыми правками в коде:**

1. Проверить `git status`
2. Если есть незакоммиченные изменения — **предложить пользователю сделать коммит** и дождаться подтверждения
3. Только после коммита (или явного отказа пользователя) приступать к изменениям

Это правило действует всегда: баги, новые фичи, рефакторинг, модификация блоков — любые изменения файлов.

---

## WP-CLI

WP-CLI доступен. Условие в `wp-config.php` (`if (!defined('WP_CLI'))`) гарантирует, что `WP_SITEURL`/`WP_HOME` устанавливаются динамически только при веб-запросах.

---

## Документация темы

Подробная задачно-ориентированная документация находится в `wp-content/themes/codeweber/doc_claude/`.

**При работе с темой** — перед началом задачи читай соответствующий файл документации:

| Задача | Файл документации |
|--------|------------------|
| Архитектура, точка входа, паттерны | `doc_claude/architecture/THEME_OVERVIEW.md` |
| Порядок загрузки файлов | `doc_claude/architecture/FILE_LOADING_ORDER.md` |
| Дочерняя тема | `doc_claude/architecture/CHILD_THEME_GUIDE.md` |
| Правила AI для дочерней темы (CPT, шаблоны, блоки, WooCommerce) | `doc_claude/architecture/CHILD_THEME_AI_RULES.md` |
| Сборка Gulp, SCSS, ассеты | `doc_claude/development/BUILD_SYSTEM.md` |
| Локальная разработка, WP-CLI | `doc_claude/development/LOCAL_SETUP.md` |
| Соглашения по коду, безопасность | `doc_claude/development/CODING_STANDARDS.md` |
| Все data-атрибуты темы (модалы, слайдер, анимации, формы, медиа) | `doc_claude/development/DATA_ATTRIBUTES.md` |
| Добавить новый CPT | `doc_claude/cpt/CPT_HOW_TO_ADD.md` |
| Каталог всех CPT | `doc_claude/cpt/CPT_CATALOG.md` |
| Шаблоны archive/single | `doc_claude/templates/ARCHIVE_SINGLE_PATTERNS.md` |
| Карточки постов | `doc_claude/templates/POST_CARDS_SYSTEM.md` |
| Выбор header/footer через Redux | `doc_claude/templates/TEMPLATE_SYSTEM.md` |
| Настройки Redux, Codeweber_Options | `doc_claude/settings/REDUX_OPTIONS.md` |
| Страницы в админке | `doc_claude/settings/ADMIN_PANELS.md` |
| AJAX fetch-архитектура | `doc_claude/api/AJAX_FETCH_SYSTEM.md` |
| REST API endpoints | `doc_claude/api/REST_API_REFERENCE.md` |
| Модальные окна (архитектура, жизненный цикл, API) | `doc_claude/api/MODAL_SYSTEM.md` |
| Хуки фильтров и actions | `doc_claude/api/HOOKS_REFERENCE.md` |
| CodeWeber Forms | `doc_claude/forms/CODEWEBER_FORMS.md` |
| Contact Form 7 | `doc_claude/forms/CF7_INTEGRATION.md` |
| Куки-баннер (архитектура, GDPR/РФ, версионирование) | `doc_claude/integrations/COOKIE_BANNER.md` |
| DaData, Яндекс Карты, SMS.ru и др. | `doc_claude/integrations/` |
| WooCommerce фильтры (функции, шаблоны, JS, CSS) | `doc_claude/integrations/WC_FILTERS.md` |
| WooCommerce Quick View (AJAX, Modal, Swiper, вариации, свотчи) | `doc_claude/integrations/WC_QUICK_VIEW.md` |
| WooCommerce Offcanvas Cart (AJAX, fragments, двойное добавление, CSS) | `doc_claude/integrations/WC_CART_OFFCANVAS.md` |
| WooCommerce Cart Page (Bootstrap макет, AJAX удаление, CSS) | `doc_claude/integrations/WC_CART.md` |
| WooCommerce Checkout (form-floating, select2, CSS) | `doc_claude/integrations/WC_CHECKOUT.md` |
| Wishlist (архитектура, AJAX, JS, modal, Redux) | `doc_claude/integrations/WISHLIST.md` |
| WooCommerce Product Video (метабокс, GLightbox, VK/Rutube inline, постер) | `doc_claude/integrations/WC_PRODUCT_VIDEO.md` |
| Text Inverse (class text-inverse, приоритеты, $headings-color, header вне main) | `doc_claude/integrations/TEXT_INVERSE.md` |
| WooCommerce Single Product Gallery (Swiper, skeleton, shimmer thumbs, видео-слайд) | `doc_claude/integrations/WC_SINGLE_GALLERY.md` |
| Events (CPT, регистрации, REST API, FullCalendar, ICS, галерея, карта) | `doc_claude/integrations/EVENTS.md` |
| S3 Storage модуль (кастомный S3-сервер, Redux gate, offload/restore/wipe, Content-Type, логи) | `doc_claude/integrations/S3_STORAGE.md` |
| Schema.org JSON-LD (модуль, CPT-схемы, фильтр, расширение) | `doc_claude/seo/SCHEMA_MODULE.md` |
| Безопасность, чеклист | `doc_claude/security/SECURITY_CHECKLIST.md` |
| Sidebar-виджеты CPT (архитектура, хуки, управление из child theme) | `doc_claude/components/SIDEBAR_WIDGETS.md` |
| Социальные иконки (функции, типы, шорткод, staff/vacancy) | `doc_claude/components/SOCIAL_LINKS.md` |
| Транслитерация кириллицы в слагах и именах файлов | `doc_claude/components/CYR_TO_LAT.md` |
| Размеры изображений, фильтрация по CPT, регенерация миниатюр (Redux UI, AJAX, логирование) | `doc_claude/media/MEDIA_TOOLS.md` |
| Таксономия image_tag + каскадный фильтр медиатеки (grid/list/gallery, AJAX, autocomplete) | `doc_claude/media/MEDIA_FILTERS.md` |
