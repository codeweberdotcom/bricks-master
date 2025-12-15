# Redux Options для управления CPT

Это руководство описывает все настройки Redux Framework для управления Custom Post Types (CPT) в теме Codeweber.

## 📍 Где находятся настройки

Настройки CPT находятся в админ-панели WordPress:

**Путь:** `Redux Framework → Custom Post Types → {Название CPT}`

## 🔧 Управление CPT

### Включение/выключение CPT

**Опция:** `cpt_switch_{post_type}`

**Тип:** Switch (переключатель)

**Расположение:** `Redux Framework → Custom Post Types` (основная секция)

**Описание:** Включает или выключает конкретный тип записи. Когда CPT выключен, его настройки не отображаются.

**Пример:**
- `cpt_switch_staff` - для Staff
- `cpt_switch_vacancies` - для Vacancies
- `cpt_switch_testimonials` - для Testimonials

**Использование в коде:**
```php
global $opt_name;
$is_enabled = Redux::get_option($opt_name, 'cpt_switch_staff');
if ($is_enabled) {
    // CPT включен
}
```

**Важно:** 
- Некоторые CPT (header, footer, page-header, legal) всегда включены
- После включения CPT автоматически появляется секция с его настройками

## 📄 Управление шаблонами

### Архивные шаблоны

**Опция:** `archive_template_select_{post_type}`

**Тип:** Select (выпадающий список)

**Описание:** Выбор шаблона для отображения архивной страницы CPT.

**Доступные опции:**
- `default` - Шаблон по умолчанию
- `{template_name}_1`, `{template_name}_2`, и т.д. - Кастомные шаблоны из папки `templates/archives/{post_type}/`

**Пример:**
- `archive_template_select_staff` - для Staff
- `archive_template_select_vacancies` - для Vacancies

**Использование в коде:**
```php
global $opt_name;
$post_type = 'staff';
$template = Redux::get_option($opt_name, 'archive_template_select_' . $post_type);

// Если шаблон не выбран, используем по умолчанию
if (empty($template) || $template === 'default') {
    $template = 'staff_1'; // или другой шаблон по умолчанию
}

$template_file = "templates/archives/{$post_type}/{$template}.php";
if (locate_template($template_file)) {
    get_template_part("templates/archives/{$post_type}/{$template}");
}
```

**Где находятся шаблоны:**
```
templates/archives/{post_type}/
├── {post_type}_1.php
├── {post_type}_2.php
└── default.php
```

### Single шаблоны

**Опция:** `single_template_select_{post_type}`

**Тип:** Select (выпадающий список)

**Описание:** Выбор шаблона для отображения отдельной записи CPT.

**Доступные опции:**
- `default` - Шаблон по умолчанию
- `{template_name}_1`, `{template_name}_2`, и т.д. - Кастомные шаблоны из папки `templates/singles/{post_type}/`

**Пример:**
- `single_template_select_staff` - для Staff
- `single_template_select_vacancies` - для Vacancies

**Использование в коде:**
```php
global $opt_name;
$post_type = 'staff';
$template = Redux::get_option($opt_name, 'single_template_select_' . $post_type);

$template_file = "templates/singles/{$post_type}/{$template}.php";
if (locate_template($template_file)) {
    get_template_part("templates/singles/{$post_type}/{$template}");
} else {
    // Fallback на default
    get_template_part("templates/singles/{$post_type}/default");
}
```

**Где находятся шаблоны:**
```
templates/singles/{post_type}/
├── {post_type}_1.php
├── {post_type}_2.php
└── default.php
```

## 📑 Управление заголовками страниц (Page Headers)

### Заголовок для Single страниц

**Опция:** `single_page_header_select_{post_type}`

**Тип:** Select (выпадающий список)

**Описание:** Выбор заголовка страницы для single страниц CPT.

**Доступные опции:**
- `disabled` - Отключить заголовок страницы
- `default` - Использовать заголовок по умолчанию
- `{ID}` - ID записи типа `page-header` (кастомный заголовок)

**Пример:**
- `single_page_header_select_staff` - для Staff
- `single_page_header_select_vacancies` - для Vacancies

**Использование в коде:**
```php
global $opt_name;
$post_type = 'staff';
$pageheader_id = Redux::get_option($opt_name, 'single_page_header_select_' . $post_type);

// Проверяем, не отключен ли заголовок
if ($pageheader_id === 'disabled') {
    $show_page_header = false;
} elseif ($pageheader_id === 'default') {
    // Используем глобальный заголовок
    $show_page_header = true;
} else {
    // Используем кастомный заголовок
    $pageheader_post = get_post($pageheader_id);
    if ($pageheader_post) {
        // Выводим кастомный заголовок
    }
}
```

### Заголовок для архивных страниц

**Опция:** `archive_page_header_select_{post_type}`

**Тип:** Select (выпадающий список)

**Описание:** Выбор заголовка страницы для архивных страниц CPT.

**Доступные опции:** Аналогично single страницам

**Использование в коде:**
```php
global $opt_name;
$post_type = 'staff';
$pageheader_id = Redux::get_option($opt_name, 'archive_page_header_select_' . $post_type);
```

## 📊 Управление сайдбарами

### Позиция сайдбара для Single страниц

**Опция:** `sidebar_position_single_{post_type}`

**Тип:** Button Set (кнопки)

**Описание:** Позиция сайдбара на single страницах CPT.

**Доступные опции:**
- `left` - Слева
- `right` - Справа (по умолчанию)
- `none` - Отключен

**Пример:**
- `sidebar_position_single_staff` - для Staff
- `sidebar_position_single_vacancies` - для Vacancies

**Использование в коде:**
```php
global $opt_name;
$post_type = 'staff';
$sidebar_position = Redux::get_option($opt_name, 'sidebar_position_single_' . $post_type);

// Определяем класс контента
$content_class = ($sidebar_position === 'none') ? 'col-12' : 'col-md-8';
```

### Позиция сайдбара для архивных страниц

**Опция:** `sidebar_position_archive_{post_type}`

**Тип:** Button Set (кнопки)

**Описание:** Позиция сайдбара на архивных страницах CPT.

**Доступные опции:**
- `left` - Слева
- `right` - Справа (по умолчанию)
- `none` - Отключен

**Использование в коде:**
```php
global $opt_name;
$post_type = 'staff';
$sidebar_position = Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);

// Определяем класс контента
$content_class = ($sidebar_position === 'none') ? 'col-12 py-14' : 'col-xl-9 pt-14';
```

**Универсальная функция:**
```php
function get_sidebar_position($opt_name)
{
    $post_type = universal_get_post_type();
    
    // Для архивов
    if (!is_singular($post_type)) {
        return Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);
    }
    
    // Для single страниц
    return Redux::get_option($opt_name, 'sidebar_position_single_' . $post_type);
}
```

## 🎨 Кастомные заголовки и подзаголовки

### Кастомный заголовок

**Опция:** `custom_title_{post_type}`

**Тип:** Text (текстовое поле)

**Описание:** Пользовательский заголовок для архивной страницы CPT.

**Пример:**
- `custom_title_staff` - для Staff
- `custom_title_vacancies` - для Vacancies

**Использование в коде:**
```php
global $opt_name;
$post_type = 'staff';
$custom_title = Redux::get_option($opt_name, 'custom_title_' . $post_type);

if (!empty($custom_title)) {
    echo esc_html($custom_title);
} else {
    // Используем стандартный заголовок
    echo post_type_archive_title('', false);
}
```

### Кастомный подзаголовок

**Опция:** `custom_subtitle_{post_type}`

**Тип:** Textarea (многострочное текстовое поле)

**Описание:** Пользовательский подзаголовок для архивной страницы CPT.

**Использование в коде:**
```php
global $opt_name;
$post_type = 'staff';
$custom_subtitle = Redux::get_option($opt_name, 'custom_subtitle_' . $post_type);

if (!empty($custom_subtitle)) {
    echo '<p class="lead">' . esc_html($custom_subtitle) . '</p>';
}
```

## 🎯 Управление Header и Footer

### Header для Single страниц

**Опция:** `single_header_select_{post_type}`

**Тип:** Select (выпадающий список)

**Описание:** Выбор кастомного header для single страниц CPT.

**Доступные опции:**
- Записи типа `header` (CPT)

**Использование в коде:**
```php
global $opt_name;
$post_type = 'staff';
$header_id = Redux::get_option($opt_name, 'single_header_select_' . $post_type);

if (!empty($header_id)) {
    $header_post = get_post($header_id);
    if ($header_post && $header_post->post_type === 'header') {
        // Выводим кастомный header
        echo apply_filters('the_content', $header_post->post_content);
    }
}
```

### Header для архивных страниц

**Опция:** `archive_header_select_{post_type}`

**Тип:** Select (выпадающий список)

**Описание:** Выбор кастомного header для архивных страниц CPT.

### Footer для Single страниц

**Опция:** `single_footer_select_{post_type}`

**Тип:** Select (выпадающий список)

**Описание:** Выбор кастомного footer для single страниц CPT.

### Footer для архивных страниц

**Опция:** `archive_footer_select_{post_type}`

**Тип:** Select (выпадающий список)

**Описание:** Выбор кастомного footer для архивных страниц CPT.

## 📋 Полный список опций для CPT

Для каждого CPT доступны следующие опции:

### Основные настройки:
1. ✅ `cpt_switch_{post_type}` - Включение/выключение CPT
2. 📄 `archive_template_select_{post_type}` - Шаблон архива
3. 📄 `single_template_select_{post_type}` - Шаблон single
4. 📝 `custom_title_{post_type}` - Кастомный заголовок
5. 📝 `custom_subtitle_{post_type}` - Кастомный подзаголовок

### Сайдбары:
6. 📊 `sidebar_position_single_{post_type}` - Позиция сайдбара (single)
7. 📊 `sidebar_position_archive_{post_type}` - Позиция сайдбара (archive)

### Заголовки страниц:
8. 📑 `single_page_header_select_{post_type}` - Page Header (single)
9. 📑 `archive_page_header_select_{post_type}` - Page Header (archive)

### Header и Footer:
10. 🎯 `single_header_select_{post_type}` - Header (single)
11. 🎯 `archive_header_select_{post_type}` - Header (archive)
12. 🎯 `single_footer_select_{post_type}` - Footer (single)
13. 🎯 `archive_footer_select_{post_type}` - Footer (archive)

## 🔍 Пример: Получение всех настроек для Staff

```php
global $opt_name;
$post_type = 'staff';

// Основные настройки
$is_enabled = Redux::get_option($opt_name, 'cpt_switch_staff');
$archive_template = Redux::get_option($opt_name, 'archive_template_select_staff');
$single_template = Redux::get_option($opt_name, 'single_template_select_staff');
$custom_title = Redux::get_option($opt_name, 'custom_title_staff');
$custom_subtitle = Redux::get_option($opt_name, 'custom_subtitle_staff');

// Сайдбары
$sidebar_single = Redux::get_option($opt_name, 'sidebar_position_single_staff');
$sidebar_archive = Redux::get_option($opt_name, 'sidebar_position_archive_staff');

// Заголовки страниц
$pageheader_single = Redux::get_option($opt_name, 'single_page_header_select_staff');
$pageheader_archive = Redux::get_option($opt_name, 'archive_page_header_select_staff');

// Header и Footer
$header_single = Redux::get_option($opt_name, 'single_header_select_staff');
$header_archive = Redux::get_option($opt_name, 'archive_header_select_staff');
$footer_single = Redux::get_option($opt_name, 'single_footer_select_staff');
$footer_archive = Redux::get_option($opt_name, 'archive_footer_select_staff');
```

## 🎨 Структура настроек в Redux

Настройки организованы в виде табов для удобства:

```
Redux Framework
└── Custom Post Types
    ├── [Переключатели CPT] ← Основная секция
    └── {Название CPT} ← Секция для каждого CPT
        ├── Archive Template
        ├── Single Template
        ├── Custom Title
        ├── Custom Subtitle
        ├── Sidebar Settings (табы: Single, Archive)
        ├── Header Settings (табы: Single, Archive)
        ├── Footer Settings (табы: Single, Archive)
        └── Page Header Settings (табы: Single, Archive)
```

## ⚙️ Автоматическое обнаружение шаблонов

Redux автоматически сканирует папки с шаблонами и добавляет их в выпадающие списки:

**Для архивов:**
- Сканирует: `templates/archives/{post_type}/`
- Добавляет все `.php` файлы как опции

**Для single:**
- Сканирует: `templates/singles/{post_type}/`
- Добавляет все `.php` файлы как опции

**Важно:** 
- Файл `default.php` всегда доступен как опция "Default Template"
- Имена файлов становятся опциями в выпадающем списке

## 🔧 Программное изменение настроек

Если нужно программно изменить настройки:

```php
global $opt_name;

// Включить CPT
Redux::set_option($opt_name, 'cpt_switch_staff', true);

// Установить шаблон
Redux::set_option($opt_name, 'archive_template_select_staff', 'staff_2');

// Установить позицию сайдбара
Redux::set_option($opt_name, 'sidebar_position_single_staff', 'left');
```

## ✅ Проверка настроек

### Проверка включен ли CPT:
```php
global $opt_name;
$is_enabled = Redux::get_option($opt_name, 'cpt_switch_staff');
if (!$is_enabled) {
    // CPT отключен
    return;
}
```

### Проверка существования опции:
```php
global $opt_name;
$template = Redux::get_option($opt_name, 'archive_template_select_staff');
if (empty($template)) {
    // Опция не установлена, используем значение по умолчанию
    $template = 'staff_1';
}
```

## 🎯 Рекомендации

1. **Всегда проверяйте включение CPT** перед использованием его настроек
2. **Используйте fallback значения** если опция не установлена
3. **Проверяйте существование шаблонов** перед их загрузкой
4. **Используйте универсальные функции** типа `get_sidebar_position()` для упрощения кода
5. **Документируйте кастомные опции** если добавляете свои

## 🔗 Связанные документы

- [CPT_CREATION.md](CPT_CREATION.md) - Создание новых CPT
- [ARCHIVE_TEMPLATES.md](ARCHIVE_TEMPLATES.md) - Создание архивных шаблонов
- [SINGLE_TEMPLATES.md](SINGLE_TEMPLATES.md) - Создание single шаблонов
- [SIDEBARS.md](SIDEBARS.md) - Добавление сайдбаров

---

**Последнее обновление:** 2024-12-13




