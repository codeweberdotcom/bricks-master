# Отчет: Система шаблонов карточек постов

## Обзор

В теме Codeweber реализована **централизованная система шаблонов карточек постов**, которая позволяет унифицированно отображать записи различных типов (post, projects, staff и т.д.) с гибкой настройкой элементов отображения.

---

## 1. Архитектура системы

### 1.1. Структура файлов

```
wp-content/themes/codeweber/
├── functions/
│   └── post-card-templates.php      # Основная функция рендеринга и шорткод
├── templates/
│   └── post-cards/
│       ├── helpers.php              # Вспомогательные функции (данные, настройки)
│       ├── default.php              # Шаблон по умолчанию
│       ├── card.php                 # Шаблон карточки с тенью
│       ├── card-content.php        # Шаблон карточки с контентом
│       ├── slider.php               # Шаблон для слайдера
│       ├── default-clickable.php    # Кликабельная карточка
│       └── overlay-5.php            # Шаблон с overlay-5 эффектом
```

### 1.2. Принципы работы

1. **Централизованный рендеринг** - одна функция `cw_render_post_card()` для всех шаблонов
2. **Универсальные данные** - функция `cw_get_post_card_data()` нормализует данные для любого типа записи
3. **Гибкие настройки** - `cw_get_post_card_display_settings()` управляет отображением элементов
4. **Изоляция** - все функции префиксированы `cw_`, не конфликтуют с существующим кодом

---

## 2. Основные функции

### 2.1. `cw_render_post_card()`

**Расположение:** `functions/post-card-templates.php` (строки 24-47)

**Назначение:** Главная функция для рендеринга карточки поста

**Параметры:**
- `$post` (WP_Post|int) - Объект поста или ID
- `$template_name` (string) - Имя шаблона (default, card, card-content, slider, default-clickable, overlay-5)
- `$display_settings` (array) - Настройки отображения элементов
- `$template_args` (array) - Дополнительные аргументы шаблона

**Возвращает:** HTML строку карточки

**Логика работы:**
1. Загружает helpers.php
2. Получает данные поста через `cw_get_post_card_data()`
3. Загружает шаблон из `templates/post-cards/{template_name}.php`
4. Использует fallback на `default.php` если шаблон не найден
5. Возвращает HTML через output buffering

### 2.2. `cw_get_post_card_data()`

**Расположение:** `templates/post-cards/helpers.php` (строки 52-131)

**Назначение:** Получает и нормализует данные поста для любого типа записи

**Параметры:**
- `$post` (WP_Post|int) - Объект поста или ID
- `$image_size` (string) - Размер изображения (по умолчанию 'full')

**Возвращает:** Массив данных поста или null

**Структура возвращаемых данных:**
```php
[
    'id' => int,                    // ID поста
    'title' => string,              // Заголовок
    'excerpt' => string,            // Краткое описание
    'link' => string,               // URL поста
    'date' => string,               // Дата в формате 'd M Y'
    'date_format' => string,        // Дата в формате настроек WordPress
    'comments_count' => int,        // Количество комментариев
    'category' => WP_Term|null,     // Объект категории/термина
    'category_link' => string,     // URL категории
    'image_url' => string,          // URL изображения
    'image_alt' => string,          // Alt текст изображения
    'post_type' => string,          // Тип записи
]
```

**Особенности:**
- Автоматически определяет категорию для стандартных постов (`post`)
- Для кастомных типов записей ищет первую иерархическую таксономию
- Если иерархической нет, берет первую доступную таксономию
- Поддерживает любой тип записи (post, projects, staff, clients и т.д.)

### 2.3. `cw_get_post_card_display_settings()`

**Расположение:** `templates/post-cards/helpers.php` (строки 31-43)

**Назначение:** Управляет настройками отображения элементов карточки

**Параметры:**
- `$args` (array) - Массив настроек

**Структура настроек:**
```php
[
    'show_title' => bool,           // Показывать заголовок (по умолчанию true)
    'show_date' => bool,            // Показывать дату (по умолчанию true)
    'show_category' => bool,        // Показывать категорию (по умолчанию true)
    'show_comments' => bool,        // Показывать комментарии (по умолчанию true)
    'title_length' => int,          // Максимальная длина заголовка (0 = без ограничения)
    'excerpt_length' => int,        // Длина описания (0 = не показывать)
    'title_tag' => string,          // HTML тег для заголовка (h1-h6, p, div, span, по умолчанию h2)
    'title_class' => string,        // Дополнительный CSS класс для заголовка
]
```

**Возвращает:** Массив настроек с примененными значениями по умолчанию

---

## 3. Шаблоны карточек

### 3.1. `default.php` - Шаблон по умолчанию

**Особенности:**
- Изображение с overlay эффектом (`overlay overlay-1`)
- Опциональный `hover-scale` эффект
- Категория, заголовок, дата, комментарии
- Figcaption с "Read More"

**Структура:**
```html
<article>
    <figure class="overlay overlay-1 rounded mb-5">
        <a href="..."><img src="..." alt="..." /></a>
        <figcaption><h5>Read More</h5></figcaption>
    </figure>
    <div class="post-header">
        <div class="post-category">...</div>
        <h2 class="post-title">...</h2>
    </div>
    <div class="post-footer">
        <ul class="post-meta">...</ul>
    </div>
</article>
```

**Параметры `$template_args`:**
- `hover_classes` - CSS классы для hover эффекта
- `border_radius` - Класс скругления (rounded)
- `show_figcaption` - Показывать figcaption
- `enable_hover_scale` - Включить hover-scale эффект

### 3.2. `card.php` - Шаблон карточки

**Особенности:**
- Обертка в `<div class="card shadow-lg">`
- Изображение с классом `card-img-top`
- Контент в `card-body`
- Footer с `mt-auto` для выравнивания внизу
- Класс `h-100` для растягивания на всю высоту

**Структура:**
```html
<article class="h-100 mb-6">
    <div class="card shadow-lg d-flex flex-column h-100">
        <figure class="overlay overlay-1 rounded card-img-top">...</figure>
        <div class="card-body p-6">
            <div class="post-header">...</div>
            <div class="post-footer mt-auto">...</div>
        </div>
    </div>
</article>
```

### 3.3. `card-content.php` - Шаблон карточки с контентом

**Особенности:**
- Аналогичен `card.php`
- Добавлен excerpt (описание поста)
- Ограничение excerpt до 116 символов
- Класс `mb-0` для параграфа с excerpt

### 3.4. `slider.php` - Шаблон для слайдера

**Особенности:**
- Категория отображается на изображении (в `caption-wrapper`)
- Включает excerpt и ссылку "Read more"
- Класс `hover-scale` для hover эффекта
- `justify-content-between` для post-meta

### 3.5. `default-clickable.php` - Кликабельная карточка

**Особенности:**
- Вся карточка обернута в один `<a>` тег
- Без overlay на изображении
- Опциональный `lift` эффект для hover
- Заголовок всегда `h2` с классом `h3`
- Padding `p-4` для header и footer

**Структура:**
```html
<article class="h-100">
    <a href="..." class="card-link d-block text-decoration-none d-flex flex-column h-100 lift">
        <figure class="rounded mb-5">...</figure>
        <div class="post-header p-4">...</div>
        <div class="post-footer p-4 mt-auto">...</div>
    </a>
</article>
```

**Параметры `$template_args`:**
- `enable_lift` - Включить lift эффект (по умолчанию true для этого шаблона)

### 3.6. `overlay-5.php` - Шаблон с overlay-5

**Особенности:**
- Использует `overlay overlay-5` класс
- Темный overlay с `opacity: 0.9`
- Класс `bottom-overlay` для даты
- Дата и заголовок отображаются на изображении

---

## 4. Шорткод `[cw_blog_posts_slider]`

**Расположение:** `functions/post-card-templates.php` (строки 56-237)

**Назначение:** Создает слайдер постов с использованием шаблонов карточек

### 4.1. Параметры шорткода

```php
[
    'posts_per_page' => 4,              // Количество постов
    'category' => '',                    // Фильтр по категориям (slug через запятую)
    'tag' => '',                         // Фильтр по меткам (slug через запятую)
    'post_type' => 'post',               // Тип записей
    'orderby' => 'date',                 // Сортировка
    'order' => 'DESC',                   // Порядок
    'image_size' => 'codeweber_single', // Размер изображения
    'excerpt_length' => 20,              // Длина описания
    'title_length' => 0,                 // Длина заголовка (0 = без ограничения)
    'template' => 'default',             // Шаблон карточки
    'enable_hover_scale' => 'false',     // Hover-scale для default
    'show_title' => 'true',              // Показывать заголовок
    'show_date' => 'true',               // Показывать дату
    'show_category' => 'true',           // Показывать категорию
    'show_comments' => 'true',           // Показывать комментарии
    'title_tag' => 'h2',                 // Тег заголовка
    'title_class' => '',                 // Класс заголовка
    'enable_lift' => 'false',            // Lift эффект
    // Swiper настройки
    'items_xl' => '3',
    'items_lg' => '3',
    'items_md' => '2',
    'items_sm' => '2',
    'items_xs' => '1',
    'items_xxs' => '1',
    'margin' => '30',
    'dots' => 'true',
    'nav' => 'false',
    'autoplay' => 'false',
    'loop' => 'false'
]
```

### 4.2. Примеры использования

```php
// Базовый пример
[cw_blog_posts_slider posts_per_page="6" template="default"]

// С настройками
[cw_blog_posts_slider 
    posts_per_page="4" 
    template="card" 
    image_size="medium_large"
    show_category="true"
    title_length="50"
]

// Для слайдера
[cw_blog_posts_slider 
    posts_per_page="6" 
    template="slider" 
    items_xl="2" 
    items_md="1"
    dots="true"
]
```

### 4.3. Особенности реализации

- Генерирует уникальный ID для каждого слайдера
- Добавляет inline стили для выравнивания высоты слайдов
- Использует data-атрибуты для Swiper конфигурации
- Автоматически обрабатывает WP_Query и wp_reset_postdata()

---

## 5. Использование в коде

### 5.1. Прямой вызов функции

```php
// Базовый пример
echo cw_render_post_card($post, 'default');

// С настройками
$display_settings = [
    'show_title' => true,
    'show_date' => true,
    'title_length' => 50,
    'title_tag' => 'h3',
];

$template_args = [
    'image_size' => 'medium_large',
    'hover_classes' => 'overlay overlay-1 hover-scale',
    'enable_hover_scale' => true,
];

echo cw_render_post_card($post, 'default', $display_settings, $template_args);
```

### 5.2. В шаблонах темы

**Пример:** `templates/components/lastpostslider-blog.php`

```php
$display_settings = [
    'show_title' => true,
    'show_date' => true,
    'show_category' => true,
    'show_comments' => true,
    'title_length' => 0,
    'excerpt_length' => 0,
    'title_tag' => 'h2',
];

$template_args = [
    'image_size' => 'codeweber_single',
    'hover_classes' => 'overlay overlay-1 hover-scale',
    'enable_hover_scale' => true,
];

echo cw_render_post_card(get_post(), 'default', $display_settings, $template_args);
```

### 5.3. В Gutenberg блоке Post Grid

**Расположение:** `wp-content/plugins/codeweber-gutenberg-blocks/src/blocks/post-grid/render.php`

```php
$display_settings = [
    'show_title' => true,
    'show_date' => true,
    'show_category' => true,
    'show_comments' => true,
    'title_length' => 56,
    'excerpt_length' => 0,
    'title_tag' => 'h2',
];

$template_args = [
    'image_size' => $image_size,
    'hover_classes' => $hover_classes,
    'border_radius' => 'rounded',
    'show_figcaption' => true,
    'enable_hover_scale' => false,
];

echo cw_render_post_card($post, $template, $display_settings, $template_args);
```

---

## 6. Поддержка кастомных типов записей

### 6.1. Автоматическое определение категорий

Система автоматически определяет категорию для любого типа записи:

1. **Для стандартных постов (`post`):**
   - Использует `get_the_category()`

2. **Для кастомных типов записей:**
   - Получает все таксономии через `get_object_taxonomies()`
   - Ищет первую иерархическую таксономию (категории)
   - Если не найдена, берет первую доступную таксономию
   - Получает первый термин через `get_the_terms()`

### 6.2. Примеры поддерживаемых типов

- `post` - стандартные посты WordPress
- `projects` - проекты
- `staff` - сотрудники
- `clients` - клиенты
- Любые другие кастомные типы записей

---

## 7. Ограничение длины текста

### 7.1. Заголовок

```php
if ($display['title_length'] > 0 && mb_strlen($title) > $display['title_length']) {
    $title = mb_substr($title, 0, $display['title_length']) . '...';
}
```

- Использует `mb_substr()` для корректной работы с UTF-8
- Добавляет `...` при обрезке

### 7.2. Excerpt

- Обрабатывается в шаблонах `card-content` и `slider`
- Ограничение до 116 символов (настраивается через `excerpt_length`)

---

## 8. Управление HTML тегами заголовка

### 8.1. Поддерживаемые теги

- `h1`, `h2`, `h3`, `h4`, `h5`, `h6` - стандартные заголовки
- `p`, `div`, `span` - альтернативные теги

### 8.2. Автоматические классы

```php
$title_class = 'post-title';
if (in_array($title_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
    $title_class .= ' ' . $title_tag; // Добавляет h2, h3 и т.д.
} else {
    $title_class .= ' h3'; // Fallback для p, div, span
}
$title_class .= ' mt-1 mb-3';
```

### 8.3. Кастомные классы

Можно добавить дополнительные классы через `title_class`:

```php
$display_settings = [
    'title_tag' => 'h2',
    'title_class' => 'custom-class another-class',
];
```

---

## 9. Система размеров изображений

### 9.1. Параметр `image_size`

Функция `cw_get_post_card_data()` принимает параметр `image_size`:

```php
$post_data = cw_get_post_card_data($post, 'medium_large');
```

### 9.2. Использование в шаблонах

Размер изображения передается через `$template_args`:

```php
$template_args = [
    'image_size' => 'codeweber_project_900-800',
];
```

### 9.3. Автоматическая генерация размеров

При установке featured image система темы автоматически генерирует размеры:
- Для `clients`: `codeweber_clients_200-60`, `codeweber_clients_300-200`, `codeweber_clients_400-267`
- Для `projects`: `codeweber_project_900-900`, `codeweber_project_900-718`, `codeweber_project_900-800`
- И т.д.

---

## 10. Интеграция с Swiper

### 10.1. Автоматическое выравнивание высоты

Шорткод `[cw_blog_posts_slider]` добавляет inline стили для выравнивания высоты слайдов:

```css
.swiper-wrapper {
    align-items: stretch !important;
}
.swiper-slide {
    height: auto !important;
    display: flex !important;
}
```

### 10.2. Data-атрибуты

Все настройки Swiper передаются через data-атрибуты:
- `data-margin`
- `data-dots`
- `data-nav`
- `data-autoplay`
- `data-loop`
- `data-items-xl`, `data-items-lg`, и т.д.

---

## 11. Безопасность

### 11.1. Экранирование данных

Все данные экранируются перед выводом:
- `esc_html()` - для текста
- `esc_url()` - для URL
- `esc_attr()` - для атрибутов
- `sanitize_file_name()` - для имен файлов шаблонов
- `sanitize_html_class()` - для HTML классов

### 11.2. Валидация

- Проверка существования файла шаблона
- Fallback на `default.php` если шаблон не найден
- Проверка существования поста перед обработкой
- Валидация параметров через `wp_parse_args()`

---

## 12. Преимущества системы

### 12.1. Универсальность

- Работает с любыми типами записей
- Автоматическое определение категорий/таксономий
- Единый интерфейс для всех шаблонов

### 12.2. Гибкость

- Настройка отображения элементов
- Выбор HTML тегов
- Управление длиной текста
- Различные визуальные стили

### 12.3. Изоляция

- Префикс `cw_` для всех функций
- Не конфликтует с существующим кодом
- Легко расширяется

### 12.4. Переиспользование

- Один шаблон для разных контекстов
- Шорткод для быстрого использования
- Прямой вызов функции в коде

---

## 13. Расширение системы

### 13.1. Добавление нового шаблона

1. Создать файл `templates/post-cards/new-template.php`
2. Использовать переменные `$post_data`, `$display_settings`, `$template_args`
3. Вызвать `cw_render_post_card($post, 'new-template')`

### 13.2. Добавление новых параметров

1. Расширить `$template_args` в функции `cw_render_post_card()`
2. Использовать в шаблоне через `$template_args['new_param']`

### 13.3. Кастомизация данных

Использовать фильтры WordPress для изменения данных:
- `the_title` - для заголовка
- `get_the_excerpt` - для описания
- `post_thumbnail_html` - для изображения

---

## 14. Примеры использования

### 14.1. В цикле постов

```php
$query = new WP_Query(['post_type' => 'post', 'posts_per_page' => 6]);

while ($query->have_posts()) {
    $query->the_post();
    echo cw_render_post_card(get_post(), 'card');
}
wp_reset_postdata();
```

### 14.2. Для кастомного типа записей

```php
$projects = get_posts(['post_type' => 'projects', 'posts_per_page' => 4]);

foreach ($projects as $project) {
    echo cw_render_post_card($project, 'default', [
        'title_length' => 50,
        'show_category' => true,
    ], [
        'image_size' => 'codeweber_project_900-800',
    ]);
}
```

### 14.3. В шаблоне страницы

```php
// В page.php или single.php
echo do_shortcode('[cw_blog_posts_slider posts_per_page="6" template="default" enable_hover_scale="true"]');
```

---

## 15. Заключение

Система шаблонов карточек постов представляет собой **универсальное, гибкое и расширяемое решение** для отображения записей различных типов. Она обеспечивает:

✅ **Единообразие** - один интерфейс для всех типов записей  
✅ **Гибкость** - настройка отображения элементов  
✅ **Безопасность** - экранирование всех данных  
✅ **Производительность** - оптимизированный код  
✅ **Расширяемость** - легко добавлять новые шаблоны  
✅ **Совместимость** - работает с любыми типами записей

**Файлы системы:**
- `functions/post-card-templates.php` - основная логика
- `templates/post-cards/helpers.php` - вспомогательные функции
- `templates/post-cards/*.php` - шаблоны карточек

