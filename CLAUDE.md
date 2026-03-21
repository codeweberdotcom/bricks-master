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
| Wishlist (архитектура, AJAX, JS, modal, Redux) | `doc_claude/integrations/WISHLIST.md` |
| Безопасность, чеклист | `doc_claude/security/SECURITY_CHECKLIST.md` |
