# План: Создание меню Docs в админке WordPress

## Задача

Создать в админке WordPress меню "Docs" как подменю, которое будет:
1. Выводить список всех MD файлов из папки `doc/`
2. При клике открывать содержимое MD файла

## Структура документации

Текущая структура файлов в папке `doc/`:

```
doc/
├── README.md
├── ARCHIVE_AND_CARD_TEMPLATES.md
├── ajax/
│   ├── AJAX_DOWNLOAD_IMPLEMENTATION.md
│   └── AJAX_FILTER.md
├── Analysis/
├── Functions/
│   ├── FUNCTION_CODEWEBER_SINGLE_SOCIAL_LINKS.md
│   ├── FUNCTION_SOCIAL_LINKS.md
│   ├── FUNCTION_STAFF_SOCIAL_LINKS.md
│   └── FUNCTION_VACANCY_SOCIAL_LINKS.md
├── images/
│   └── IMAGE_CREATION_GUIDE.md
├── install/
│   └── GHOSTSCRIPT_INSTALL.md
├── Integrations/
│   ├── dadata-integration-plan.md
│   ├── dadata-integration.md
│   └── woocommerce-template-migration.md
├── modules/
│   ├── ARCHIVE_TEMPLATES.md
│   ├── CPT_CREATION.md
│   ├── METAFIELDS.md
│   ├── REDUX_CPT_OPTIONS.md
│   ├── SIDEBARS.md
│   ├── SINGLE_TEMPLATES.md
│   └── YANDEX_MAPS.md
├── Other/
│   └── GULP.md
├── Shortcodes/
│   ├── SHORTCODE_ADDRESS.md
│   ├── SHORTCODE_AJAX_SEARCH_FORM.md
│   └── ... (много других shortcodes)
└── Tasks/
```

---

## Технический план реализации

### Шаг 1: Создание PHP файла для админ-страницы

Создать файл `functions/admin/admin_docs.php` со следующим функционалом:

```php
// Регистрация меню
add_action('admin_menu', 'codeweber_docs_menu');

function codeweber_docs_menu() {
    add_menu_page(
        'Docs',                    // Заголовок страницы
        'Docs',                    // Название в меню
        'manage_options',          // Права доступа
        'codeweber-docs',         // Slug меню
        'codeweber_docs_page',    // Callback функция
        'dashicons-book',         // Иконка
        3                          // Позиция в меню
    );
}
```

### Шаг 2: Создание функции сканирования MD файлов

```php
function codeweber_get_doc_files($dir = null) {
    $base_dir = get_template_directory() . '/doc';
    $files = [];
    
    // Рекурсивное сканирование
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base_dir)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'md') {
            $relative_path = str_replace($base_dir . '/', '', $file->getPathname());
            $files[] = $relative_path;
        }
    }
    
    return $files;
}
```

### Шаг 3: Создание страницы вывода списка файлов

```php
function codeweber_docs_page() {
    $doc_files = codeweber_get_doc_files();
    
    // Группировка по папкам
    $grouped = [];
    foreach ($doc_files as $file) {
        $parts = explode('/', $file);
        $folder = $parts[0] ?? 'Root';
        $grouped[$folder][] = $file;
    }
    
    // Вывод HTML
    echo '<div class="wrap">';
    echo '<h1>CodeWeber Documentation</h1>';
    
    foreach ($grouped as $folder => $files) {
        echo '<h2>' . esc_html(ucfirst($folder)) . '</h2>';
        echo '<ul>';
        foreach ($files as $file) {
            $url = add_query_arg(['page' => 'codeweber-docs', 'file' => $file]);
            echo '<li><a href="' . esc_url($url) . '">' . esc_html($file) . '</a></li>';
        }
        echo '</ul>';
    }
    
    echo '</div>';
}
```

### Шаг 4: Добавление функционала просмотра MD файла

При клике на файл - отобразить его содержимое с форматированием:

```php
function codeweber_docs_view_file($file) {
    $file_path = get_template_directory() . '/doc/' . sanitize_file_name($file);
    
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        
        // Конвертация Markdown в HTML (использоватьParsedown или similar)
        require_once get_template_directory() . '/vendor/autoload.php';
        $parser = new Parsedown();
        echo $parser->text($content);
    } else {
        echo '<p>File not found</p>';
    }
}
```

### Шаг 5: Подключение Parsedown для рендеринга MD

Добавить библиотеку для парсинга Markdown (через Composer):

```json
// composer.json
{
    "require": {
        "erusev/parsedown": "^1.7"
    }
}
```

---

## Структура меню

```
Консоль
├── Docs (новое меню)
│   ├── All Docs (список всех файлов)
│   ├── Shortcodes
│   │   ├── SHORTCODE_SOCIAL_LINKS.md
│   │   ├── SHORTCODE_ADDRESS.md
│   │   └── ...
│   ├── Functions
│   │   ├── FUNCTION_SOCIAL_LINKS.md
│   │   └── ...
│   ├── Modules
│   │   └── ...
│   └── ...
```

---

## План реализации (через switch_mode в code)

1. **Создать файл** `functions/admin/admin_docs.php`
2. **Добавить регистрацию меню** через `add_action('admin_menu')`
3. **Создать функцию** сканирования MD файлов из папки `doc/`
4. **Создать функцию** рендеринга списка файлов с группировкой по папкам
5. **Создать функцию** просмотра содержимого MD файла
6. **Подключить библиотеку** Parsedown для конвертации Markdown в HTML
7. **Добавить стили** для админ-страницы (CSS)
8. **Подключить файл** в `functions.php` темы

---

## Ожидаемый результат

В админке WordPress появится новое меню "Docs" с иконкой книги. При переходе в меню отобразится список всех MD файлов, сгруппированных по папкам. При клике на файл - откроется его содержимое с форматированием Markdown.

---

## Нужно продолжить?

Подтвердите план, и я переключусь в режим code для реализации.
