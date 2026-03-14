# Система сборки — Gulp + SCSS

## Быстрый старт

```bash
cd wp-content/themes/codeweber
npm install
npm start         # gulp serve — сборка dist + BrowserSync watch
npm run build     # gulp build:dist — продакшен-сборка
```

> **Всегда запускать из директории родительской темы** `codeweber/`, даже если активна дочерняя тема.

---

## Как Gulp определяет активную тему

При запуске `gulpfile.js` вызывает PHP-скрипт `functions/gulp-theme-info.php` через `execSync`. Скрипт подключает `wp-load.php`, получает активную тему WordPress и возвращает JSON:

```json
{
  "is_child": true,
  "child_theme_name": "codeweber-child",
  "child_theme_path": "C:/laragon/www/.../themes/codeweber-child",
  "parent_theme_path": "C:/laragon/www/.../themes/codeweber"
}
```

На основании этого:
- **`src/`** всегда берётся из **родительской** темы
- **`dist/`** создаётся в **активной** теме (дочерней, если она активна)

---

## Структура путей

```
src/assets/
├── scss/
│   ├── style.scss          # Точка входа стилей
│   ├── _user-variables.scss # Переменные (цвета, шрифты)
│   ├── theme/
│   │   └── _colors.scss    # Цветовые схемы
│   ├── colors/             # Отдельные CSS-файлы схем
│   └── fonts/              # @font-face SCSS
├── js/
│   ├── theme.js            # Основной JS темы
│   ├── vendor/             # Сторонние JS-библиотеки
│   ├── restapi.js          # REST API-запросы
│   ├── notification-triggers.js
│   ├── ajax-filter.js
│   ├── ajax-download.js
│   ├── share-buttons.js
│   ├── form-validation.js
│   ├── cf7-*.js            # CF7-специфичные скрипты
│   └── dadata-address.js   # DaData виджет
├── fonts/                  # Шрифтовые файлы
├── img/                    # Изображения (оптимизируются)
└── media/                  # Прочие медиафайлы

dist/assets/               # Скомпилированный вывод
├── css/
│   ├── style.css           # Минифицированные стили
│   ├── style.css.map       # Source map
│   ├── plugins.css         # Vendor CSS
│   ├── fonts/              # Скомпилированные шрифтовые CSS
│   └── colors/             # Цветовые схемы
└── js/
    ├── plugins.js          # Bootstrap + vendor JS (uglified)
    ├── theme.js
    ├── restapi.js
    └── ...остальные JS
```

---

## Gulp-задачи

### Основные команды

| Команда | Задача Gulp | Описание |
|---------|------------|---------|
| `npm start` | `gulp serve` | BrowserSync + watch |
| `npm run build` | `gulp build:dist` | Полная продакшен-сборка |
| `gulp build:dev` | `gulp build:dev` | Сборка в `dev/` (без минификации) |
| `gulp build:html` | `gulp html:dist` | Только HTML-шаблоны |
| `gulp cache:clear` | `gulp cache:clear` | Очистить кеш imagemin |

### Таблица всех задач

| Задача | Что делает | Трансформации |
|--------|-----------|--------------|
| `css:dist` | SCSS → CSS | sass → sassUnicode → autoprefixer → cleanCSS → sourcemaps |
| `fontcss:dist` | Шрифтовые SCSS → CSS | sass → autoprefixer → cleanCSS |
| `colorcss:dist` | Цветовые SCSS → CSS | sass → autoprefixer → cleanCSS |
| `vendorcss:dist` | Vendor CSS → plugins.css | concat → cleanCSS |
| `pluginsjs:dist` | Bootstrap + vendor → plugins.js | jsImport → concat → **uglify** |
| `themejs:dist` | theme.js | Копирование (uglify закомментирован) |
| `restapijs:dist` | restapi.js | Копирование |
| `*js:dist` | Остальные JS-файлы | Копирование (uglify закомментирован) |
| `image:dist` | Изображения | imagemin (jpeg-recompress max 90, pngquant) |
| `fonts:dist` | Шрифты | Копирование (с `newer`) |
| `clean:dist` | Очистка dist/ | del |

### Watch (срабатывает при изменениях)

| Файл | Задача |
|------|--------|
| `src/**/*.scss` (не fonts/colors) | `css:dist` |
| `src/assets/scss/fonts/*.scss` | `fontcss:dist` |
| `src/assets/scss/colors/*.scss` | `colorcss:dist` |
| `src/assets/js/vendor/*` | `pluginsjs:dist` |
| `src/assets/js/theme.js` | `themejs:dist` |
| Каждый именованный JS-файл | Его `:dist`-задача |
| `src/assets/img/**` | `image:dist` |
| `src/assets/fonts/**` | `fonts:dist` |

---

## SCSS: переменные и инклюды

### `_user-variables.scss`

Файл переопределяет переменные Bootstrap и темы. При дочерней теме Gulp ищет `_user-variables.scss` в **дочерней** теме первой (через `sassIncludePaths`):

```scss
// В дочерней теме: src/assets/scss/_user-variables.scss
// Переопределяет цвета, шрифты, брейкпоинты
$primary: #1e3a5f;
$font-family-base: 'Urbanist', sans-serif;
```

Если в дочерней теме нет `src/`, берётся `_user-variables.scss` из родительской.

### `style.scss` — точка входа

При дочерней теме Gulp использует `style.scss` дочерней темы, если он существует. Это позволяет переопределить порядок импортов.

---

## Подключение ассетов в WordPress

### `codeweber_get_dist_file_url($file_path)` / `codeweber_get_dist_file_path($file_path)`

Функции из `functions/enqueues.php` — реализуют паттерн child-first:

```php
// 1. Ищем в дочерней теме
if (is_child_theme()) {
    $child_file = get_stylesheet_directory() . '/' . $file_path;
    if (file_exists($child_file)) {
        return get_stylesheet_directory_uri() . '/' . $file_path;
    }
}
// 2. Fallback на родительскую
$parent_file = get_template_directory() . '/' . $file_path;
```

### `codeweber_asset_version($file_path)`

```php
// WP_DEBUG=true → filemtime() для автоматической инвалидации кеша
// WP_DEBUG=false → версия из style.css (нет stat() на каждый запрос)
function codeweber_asset_version($file_path) {
    if (defined('WP_DEBUG') && WP_DEBUG && $file_path && file_exists($file_path)) {
        return filemtime($file_path);
    }
    return wp_get_theme()->get('Version');
}
```

### Порядок подключения скриптов (приоритеты `wp_enqueue_scripts`)

| Приоритет | Функция | Что подключает |
|-----------|---------|---------------|
| default (10) | `codeweber_styles_scripts()` | `plugins.css`, `style.css`, `plugins.js`, `theme.js` |
| 20 | `codeweber_enqueue_restapi_script()` | `restapi.js` + `wpApiSettings` |
| 20 | `codeweber_enqueue_testimonial_form()` | `form-submit-universal.js` + локализация |
| 20 | `codeweber_enqueue_ajax_filter()` | `ajax-filter.js` + `codeweberFilter` |
| 20 | `codeweber_enqueue_share_buttons()` | `share-buttons.js` |
| 25 | `codeweber_enqueue_notification_triggers()` | `notification-triggers.js` |
| 25 | `codeweber_enqueue_dadata_address()` | `dadata-address.js` (только WooCommerce) |
| default (10) | `theme_enqueue_fetch_assets()` | `fetch-handler.js` + `fetch_vars` |

### Добавление нового JS-файла из src/

1. Добавить путь в `gulpfile.js` в секцию `path.src`:
   ```js
   path.src.mynewjs = srcPrefix + '/assets/js/my-new.js';
   ```

2. Создать задачи `:dev` и `:dist` по образцу существующих.

3. Добавить в `build:dev` и `build:dist` задачи.

4. Добавить в `gulp.watch`.

5. В `enqueues.php` подключить через `codeweber_get_dist_file_url`.

---

## BrowserSync (режим serve)

BrowserSync настроен на `baseDir: './dist'` — это HTML-демо режим для статических шаблонов `src/*.html`. Для разработки WordPress-шаблонов BrowserSync используется только для автоматической перекомпиляции: после изменения SCSS/JS → dist/ обновляется → WordPress отдаёт новую версию при следующем запросе (версия filemtime меняется).

---

## Дочерняя тема: что можно переопределить

| Файл дочерней темы | Эффект |
|-------------------|--------|
| `src/assets/scss/_user-variables.scss` | Переопределяет переменные Bootstrap |
| `src/assets/scss/style.scss` | Полностью кастомный порядок импортов |
| `dist/assets/css/style.css` | Финальный скомпилированный CSS (если не нужен Gulp) |
| `dist/assets/js/*.js` | Переопределённые скрипты |

Gulp при активной дочерней теме компилирует **из src родительской** → **в dist дочерней**.
