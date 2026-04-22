# Панели администратора

## Карта страниц в WordPress Admin

```
Внешний вид
└── Параметры темы (Redux Framework)   ← Главный центр настроек темы
    ├── General
    ├── Header Settings
    ├── Footer Settings
    ├── Blog / Archive
    ├── Single Post
    ├── Colors
    ├── Typography
    ├── Integrations
    └── ...другие разделы

Подписки на рассылку                   ← Newsletter-модуль
├── Подписчики
├── Настройки
└── Импорт/экспорт

Документация темы                       ← Собственная вкладка помощи
(вкладка в верхнем меню Help)

Инструменты
├── Экспорт персональных данных        ← WordPress Privacy + Personal Data V2
└── Удаление персональных данных       ← WordPress Privacy + Personal Data V2

Инструменты
└── PD Providers Test                  ← (только при WP_DEBUG=true)
```

---

## Redux Framework — основная страница настроек

**Путь:** Внешний вид → Параметры темы
**Ключ опции:** `redux_demo`
**Файл конфигурации:** `redux-framework/sample/theme-config.php` + `redux-framework/theme-settings/theme-settings.php`

Читать настройки:
```php
// Через обёртку (предпочтительно)
$value = Codeweber_Options::get('setting_key');

// Напрямую через Redux
global $opt_name;
$value = Redux::get_option($opt_name, 'setting_key', 'default');
```

Redux инициализируется на `after_setup_theme` с приоритетом 30. До этого момента `Redux::get_option()` вернёт null.

---

## Newsletter: страницы в Admin

Файлы: `functions/integrations/newsletter-subscription/admin/`

| Страница | Класс | Slug | Что делает |
|---------|-------|------|-----------|
| Список подписчиков | `NewsletterSubscriptionAdmin` | `newsletter-subscriptions` | Таблица подписчиков с поиском и фильтром по статусу |
| Настройки | `NewsletterSubscriptionSettings` | `newsletter-settings` | Тема письма, шаблон, тексты страницы отписки |
| Импорт/экспорт | `NewsletterSubscriptionImportExport` | `newsletter-import-export` | CSV-импорт и экспорт базы подписчиков |

Все страницы добавляются как submenu к корневому пункту «Подписки на рассылку» (`newsletter-subscriptions`).

---

## Admin Menu: кастомные пункты меню

### Настройки пунктов навигации (`admin_menu.php`)

Добавляет дополнительные поля в редактор пунктов меню (`Внешний вид → Меню`):

| Поле | Meta-ключ | Описание |
|------|----------|---------|
| Smooth Scroll | `_custom_menu_checkbox` | Включить плавную прокрутку для этого пункта |
| Mega Menu | `_mega_menu` | Отобразить sub-меню как мега-меню |

### Профиль пользователя (`admin_user_profil.php`)

Добавляет дополнительные поля на страницу профиля пользователя (`Пользователи → Профиль`).

### Медиабиблиотека (`admin_media.php`)

Дополнения к медиабиблиотеке.

### Gutenberg (`admin-gutenberg.php`)

Настройки редактора Gutenberg.

### Privacy (`admin_privacy.php`)

Дополнения к разделу конфиденциальности.

---

## CF7: панели в редакторе формы

При активном Contact Form 7 добавляются вкладки в редактор форм (`Контактные формы → редактировать`):

| Вкладка | Файл | Описание |
|---------|------|---------|
| Form Type | `cf7.php` | Тип формы → `data-form-type` атрибут |
| Consents | `cf7-consents-panel.php` | Привязка документов согласий к acceptance-полям |

---

## Страница документации темы

**Файл:** `functions/documentation.php`

Добавляет вкладку **"Документация"** в стандартную панель помощи WordPress (кнопка Help в правом верхнем углу). Описывает возможности темы для авторов контента.

---

## Personal Data V2: тестовая страница

**Файл:** `functions/integrations/personal-data-v2/test-providers.php`
**Условие загрузки:** только при `WP_DEBUG=true`

Создаёт страницу в меню **Инструменты → PD Providers Test**. Показывает список зарегистрированных GDPR-провайдеров и позволяет проверить наличие данных по email.

Для деактивации: убрать `define('WP_DEBUG', true)` из `wp-config.php` или закомментировать строку в `functions.php`:
```php
// if (defined('WP_DEBUG') && WP_DEBUG) {
//     require_once .../test-providers.php;
// }
```

---

## Projects Settings — страница настроек проектов

**Файл:** `functions/admin/projects-settings.php`
**Путь в admin:** Projects → Settings (submenu к CPT `projects`)
**Ключ опции:** `codeweber_projects_settings` (WordPress Settings API)

Читать настройку:
```php
$value = codeweber_projects_settings_get('key', 'default');
```

### Секция: Map

| Поле | Ключ | Тип | Описание |
|------|------|-----|---------|
| Show map | `show_map` | checkbox | Включить карту на страницах проекта |

### Секция: Floating map button (mobile)

Плавающая кнопка открытия карты — отображается только на мобильных (`d-md-none`).

| Поле | Ключ | Тип | По умолч. | Описание |
|------|------|-----|----------|---------|
| Enable | `map_float_enabled` | checkbox | `0` | Включить плавающую кнопку |
| Button type | `map_float_type` | select | `icon` | `icon` / `text` / `icon_text` |
| Icon | `map_float_icon` | icon picker | `map` | CSS-класс иконки Unicons (без `uil-`) |
| Text | `map_float_text` | text | `Map` | Текст для типов `text` и `icon_text` |
| Color | `map_float_color` | select | `primary` | `primary` / `soft-primary` / `secondary` / `soft-secondary` / `dark` / `white` |
| Shape | `map_float_shape` | select | `rounded-pill` | `rounded-pill` / `rounded` / `rounded-0` |
| Z-index | `map_float_zindex` | number | `1040` | CSS z-index кнопки |
| Bottom offset | `map_float_offset_bottom` | number | `24` | Отступ снизу в px |
| Left offset | `map_float_offset_left` | number | `16` | Отступ слева в px |

#### Валидация при sanitize

- `map_float_type`: whitelist `['icon', 'text', 'icon_text']`
- `map_float_color`: whitelist `['primary', 'soft-primary', 'secondary', 'soft-secondary', 'dark', 'white']`
- `map_float_shape`: whitelist `['rounded-pill', 'rounded', 'rounded-0']`
- `map_float_icon`: `sanitize_html_class()`
- `map_float_zindex`, `map_float_offset_bottom`, `map_float_offset_left`: `absint()`

#### Icon picker (admin UI)

Иконки загружаются из `font_icon.js` (Unicons). Список кешируется в WP transient `codeweber_projects_icon_list_v1`. CSS-шрифт генерируется из `selection.json` и кешируется в transient `codeweber_admin_unicons_css_v1`, подключается только на странице настроек проектов через `admin_enqueue_scripts`.

Функция: `codeweber_projects_get_icon_list()` — возвращает массив `['icon_name', ...]`.

#### Функция рендеринга кнопки

```php
codeweber_projects_map_float_button(): void
```

- Проверяет: `show_map='1'`, `class_exists('Codeweber_Yandex_Maps')`, `map_float_enabled='1'`
- На single `projects`: рендерит только если у записи заданы lat/lng
- Атрибут `data-project-map` на кнопке — JS открывает offcanvas карты по делегации
- Вывод HTML:
  ```html
  <div class="codeweber-projects-map-float d-md-none position-fixed"
       style="bottom:24px;left:16px;z-index:1040;">
    <button type="button" class="btn btn-circle btn-primary" data-project-map>
      <i class="uil uil-map"></i>
    </button>
  </div>
  ```
- Тип `icon` → `btn-circle btn-{color}`
- Тип `text` → `btn btn-{shape} btn-{color}`
- Тип `icon_text` → `btn btn-icon btn-icon-start btn-{shape} btn-{color}`

Вызывается из шаблонов:
- `templates/archives/projects/projects_1.php`, `projects_2.php`, `projects_3.php`, `projects_4.php`
- `templates/singles/projects/default.php`, `projects_2.php`, `projects_3.php`

---

## Image Canvas Editor — редактор изображений

**Файлы:**
- `functions/admin/image-canvas-editor.php` — PHP-класс `CW_Image_Canvas_Editor`
- `functions/admin/image-canvas-editor.js` — Canvas-редактор (vanilla JS)
- `functions/admin/image-canvas-editor.css` — стили диалога и метабокса

**Подключение:** `functions.php` → `require_once .../image-canvas-editor.php`

### Точки входа

| Где | Метабокс | Позиция |
|-----|---------|--------|
| Страница товара WooCommerce (`product`) | «Image Canvas Editor» | side / low |
| Страница редактирования вложения (`attachment`) | «Image Canvas Editor» | side / low |

Метабокс показывает миниатюры (60×60) всех изображений товара (главное + галерея `_product_image_gallery`). Кнопка «Edit» рядом с каждой.

### UI редактора

Открывается нативным `<dialog>` (без Bootstrap, без jQuery). Два режима переключаются кнопками **Pad / Crop**:

#### Режим Pad
- **Canvas size** — итоговый размер квадратного холста в px. «Square» подставляет `max(imgW, imgH)`.
- **Background** — цвет фона (color picker). По умолчанию белый.
- **Padding** — отступ от краёв в px. Изображение масштабируется и центрируется.
- Canvas является финальным output'ом — отправляется на сервер напрямую.

#### Режим Crop
- **Crop size** — размер квадратной рамки в px. «Square» ставит `min(imgW, imgH)` и центрирует.
- Автоматически инициализируется на **80% от меньшей стороны**, центрируется.
- **Перетаскивание внутри рамки** — позиционирование (курсор `grab`).
- **Перетаскивание за угловые маркеры** — изменение размера (курсоры `nw-resize` и т.д.), квадрат сохраняется.
- Рамка может **выходить за границы изображения** — серая зона; при сохранении заполняется белым.

### Отображение на canvas (crop)

Поверх изображения рисуются:
- Тёмный оверлей (`rgba(0,0,0,0.52)`) в 4 прямоугольниках вне рамки
- Белая рамка + тёмная обводка
- 4 белых квадратных маркера (6px) в углах

### AJAX-обработчик `cw_img_editor_save`

| Параметр | Тип | Описание |
|---------|-----|---------|
| `attachment_id` | int | ID вложения |
| `image_data` | string | base64 data URI (`data:image/jpeg;base64,...`) |
| `mime_type` | string | `image/jpeg` или `image/png` |
| `nonce` | string | `cw_img_editor` |

**Права:** `upload_files`

**Алгоритм:**
1. Если S3 активен (`Codeweber\S3Storage\Services\RestoreService`) и файл отсутствует локально — `RestoreService::restore_attachment($id)`
2. Декодирует base64, перезаписывает оригинальный файл (`file_put_contents`)
3. `wp_generate_attachment_metadata()` — регенерирует все thumbnail-размеры
4. `wp_update_attachment_metadata()` — триггерит S3 deferred offload автоматически через хук `Uploader`

**Ответ при успехе:** `{ url: 'новый URL', message: '...' }`

### URL изображения в метабоксе

`data-url` кнопки всегда содержит **локальный uploads URL** (обходит S3 URL rewriting):
```php
$local_url = str_replace(
    wp_normalize_path( $upload_dir['basedir'] ),
    $upload_dir['baseurl'],
    wp_normalize_path( $file_path )
);
```
Это предотвращает CORS-ошибки при загрузке изображения в `<canvas>` (S3 Beget не возвращает `Access-Control-Allow-Origin`).

### Enqueue

Скрипт и стиль подключаются только на `post.php` / `post-new.php` при `post_type === 'product'` или `'attachment'`. Версия = `filemtime()` файла.

---

## Быстрый доступ к ключевым страницам

| Задача | URL в Admin |
|--------|------------|
| Настройки темы | `wp-admin/themes.php?page=redux_demo` |
| Список подписчиков newsletter | `wp-admin/admin.php?page=newsletter-subscriptions` |
| Настройки newsletter | `wp-admin/admin.php?page=newsletter-settings` |
| Настройки проектов | `wp-admin/edit.php?post_type=projects&page=projects-settings` |
| Экспорт персональных данных | `wp-admin/tools.php?page=export_personal_data` |
| Удаление персональных данных | `wp-admin/tools.php?page=remove_personal_data` |
| Тест GDPR-провайдеров | `wp-admin/tools.php?page=pd-providers-test` |
| Canvas Editor (товар) | `wp-admin/post.php?post=ID&action=edit` → метабокс «Image Canvas Editor» |
| Canvas Editor (медиа) | `wp-admin/post.php?post=ATTACH_ID&action=edit` → метабокс «Image Canvas Editor» |
