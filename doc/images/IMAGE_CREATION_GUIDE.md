# Руководство по созданию изображений для записей

Это подробное руководство описывает, как в теме Codeweber организована система создания и использования изображений для различных типов записей.

## 📋 Содержание

1. [Обзор системы](#обзор-системы)
2. [Регистрация размеров изображений](#регистрация-размеров-изображений)
3. [Автоматическая фильтрация размеров](#автоматическая-фильтрация-размеров)
4. [Использование изображений в шаблонах](#использование-изображений-в-шаблонах)
5. [Добавление новых размеров](#добавление-новых-размеров)
6. [Примеры использования](#примеры-использования)
7. [Оптимизация и производительность](#оптимизация-и-производительность)

## 🎯 Обзор системы

Тема Codeweber использует **динамическую систему управления размерами изображений**, которая:

- ✅ Автоматически создает нужные размеры при загрузке
- ✅ Удаляет неиспользуемые размеры для экономии места
- ✅ Адаптируется под тип записи (CPT)
- ✅ Оптимизирует качество изображений (JPEG quality: 80)

### Основные файлы:

- **`functions/images.php`** - Основная логика системы изображений
- **`functions/user.php`** - Примеры использования фильтров

## 📐 Регистрация размеров изображений

### Где регистрируются размеры

Все размеры регистрируются в функции `codeweber_image_settings()` в файле `functions/images.php`.

**Хук:** `after_setup_theme`

### Текущие размеры по типам записей

#### Projects (Проекты)

```php
add_image_size('codeweber_project_900-900', 900, 900, true);  // Квадрат
add_image_size('codeweber_project_900-718', 900, 718, true);  // Портрет
add_image_size('codeweber_project_900-800', 900, 800, true);  // Альбом
```

**Параметры:**
- `900-900`: 900x900px, обрезка включена (crop: true)
- `900-718`: 900x718px, обрезка включена
- `900-800`: 900x800px, обрезка включена

#### Staff (Сотрудники)

```php
add_image_size('codeweber_staff', 400, 400, true);  // Квадрат 400x400
```

**Параметры:**
- `400x400px`, обрезка включена
- Используется для фото сотрудников

#### Vacancies (Вакансии)

```php
add_image_size('codeweber_vacancy', 600, 400, true);  // 600x400
```

**Параметры:**
- `600x400px`, обрезка включена
- Пропорционально увеличенный размер 382x255

#### Clients (Клиенты)

```php
add_image_size('codeweber_clients_115-60', 115, 60, false);   // Логотип маленький
add_image_size('codeweber_clients_200-60', 200, 60, false); // Логотип средний
add_image_size('codeweber_clients_300-200', 300, 200, false); // Карточка
add_image_size('codeweber_clients_400-267', 400, 267, false); // Карточка большая
```

**Параметры:**
- Обрезка отключена (crop: false) для сохранения пропорций логотипов

### Удаленные стандартные размеры

```php
remove_image_size('large');
remove_image_size('thumbnail');
remove_image_size('medium');
remove_image_size('medium_large');
remove_image_size('1536x1536');
remove_image_size('2048x2048');
```

**Причина:** Экономия места на сервере, используются только кастомные размеры.

### Качество изображений

```php
function codeweber_image_quality() {
    return 80; // JPEG quality (0-100)
}
add_filter('jpeg_quality', 'codeweber_image_quality');
```

**Рекомендация:** 80 - оптимальный баланс между качеством и размером файла.

## 🔄 Автоматическая фильтрация размеров

### Как это работает

При загрузке изображения система:

1. Определяет родительский пост (к какой записи привязано изображение)
2. Определяет тип записи (post type)
3. Получает список разрешенных размеров для этого типа
4. Генерирует все размеры WordPress
5. Удаляет неразрешенные размеры и их файлы

### Функция фильтрации

**Функция:** `codeweber_filter_attachment_sizes_by_post_type()`

**Хук:** `wp_generate_attachment_metadata` (приоритет: 10)

**Логика:**

```php
function codeweber_filter_attachment_sizes_by_post_type($metadata, $attachment_id)
{
    // 1. Получаем родительский пост
    $parent_id = codeweber_get_attachment_parent_id($attachment_id);
    
    // 2. Определяем тип записи
    $parent_type = get_post_type($parent_id);
    
    // 3. Получаем разрешенные размеры
    $allowed_sizes = codeweber_get_allowed_image_sizes($parent_type, $parent_id);
    
    // 4. Удаляем неразрешенные размеры
    foreach ($metadata['sizes'] as $size_name => $size_info) {
        if (!in_array($size_name, $allowed_sizes, true)) {
            // Удаляем файл и запись из метаданных
            codeweber_safe_file_delete($file_path);
            unset($metadata['sizes'][$size_name]);
        }
    }
    
    return $metadata;
}
```

### Разрешенные размеры по типам записей

**Функция:** `codeweber_get_allowed_image_sizes($post_type, $post_id)`

**Базовые настройки:**

```php
$default_sizes = [
    'projects' => [
        'codeweber_project_900-900',
        'codeweber_project_900-718',
        'codeweber_project_900-800',
        'woocommerce_gallery_thumbnail'
    ],
    'staff' => [
        'codeweber_staff',
        'woocommerce_gallery_thumbnail'
    ],
    'vacancies' => [
        'codeweber_vacancy',
        'woocommerce_gallery_thumbnail'
    ],
    'clients' => [
        'codeweber_clients_115-60',
        'codeweber_clients_200-60',
        'codeweber_clients_300-200',
        'codeweber_clients_400-267',
        'woocommerce_gallery_thumbnail'
    ],
    'default' => [] // Пустой массив - не удаляем никакие размеры
];
```

### Фильтры для кастомизации

#### 1. Общий фильтр

```php
add_filter('codeweber_allowed_image_sizes', 'my_custom_image_sizes', 10, 3);

function my_custom_image_sizes($sizes, $post_type, $post_id) {
    // Изменяем размеры для всех типов
    $sizes['my_cpt'] = ['my_size_1', 'my_size_2'];
    return $sizes;
}
```

#### 2. Фильтр для конкретного типа записи

```php
add_filter('codeweber_allowed_image_sizes_staff', 'staff_custom_sizes', 10, 2);

function staff_custom_sizes($sizes, $post_id) {
    // Добавляем дополнительные размеры для staff
    $sizes[] = 'thumbnail';
    return $sizes;
}
```

#### 3. Фильтр для типов по умолчанию

```php
add_filter('codeweber_allowed_image_sizes_default', 'default_custom_sizes', 10, 2);

function default_custom_sizes($sizes, $post_id) {
    // Размеры для типов записей по умолчанию
    return ['medium', 'large'];
}
```

### Определение родительского поста

Система использует несколько методов для определения родителя:

1. **Стандартный метод:**
   ```php
   $parent_id = get_post_field('post_parent', $attachment_id);
   ```

2. **Временное хранилище (Redux):**
   ```php
   $temp_parent_id = get_transient('codeweber_current_upload_parent');
   ```

3. **Из запроса:**
   ```php
   if (!empty($_REQUEST['post_id'])) {
       $parent_id = intval($_REQUEST['post_id']);
   }
   ```

**Хуки для установки родителя:**

- `redux/metaboxes/upload/prefilter` - Для загрузок через Redux
- `add_attachment` - Для стандартных загрузок
- `wp_insert_attachment_data` - Резервный метод

## 🖼️ Использование изображений в шаблонах

### Основные функции WordPress

#### 1. Получение миниатюры записи

```php
<?php
// Получить ID миниатюры
$thumbnail_id = get_post_thumbnail_id();

// Вывести миниатюру с размером
the_post_thumbnail('codeweber_staff', array('class' => 'img-fluid'));

// Получить URL изображения
$image_url = get_the_post_thumbnail_url(get_the_ID(), 'codeweber_staff');
?>
```

#### 2. Получение изображения вложения

```php
<?php
$image_id = get_post_thumbnail_id();

// Получить HTML изображения
echo wp_get_attachment_image($image_id, 'codeweber_staff', false, array(
    'class' => 'img-fluid rounded',
    'alt' => get_the_title()
));

// Получить URL изображения
$image_url = wp_get_attachment_image_url($image_id, 'codeweber_staff');

// Получить массив данных изображения
$image_data = wp_get_attachment_image_src($image_id, 'codeweber_staff');
// Возвращает: [0] => URL, [1] => ширина, [2] => высота, [3] => is_intermediate
?>
```

### Примеры для разных типов записей

#### Staff (Сотрудники)

```php
<?php
$thumbnail_id = get_post_thumbnail_id();

if ($thumbnail_id) {
    // Вариант 1: Простой вывод
    the_post_thumbnail('codeweber_staff', array('class' => 'img-fluid rounded-circle'));
    
    // Вариант 2: С ссылкой на большое изображение
    $large_image_url = wp_get_attachment_image_src($thumbnail_id, 'full');
    ?>
    <a href="<?php echo esc_url($large_image_url[0]); ?>" data-glightbox>
        <?php the_post_thumbnail('codeweber_staff', array('class' => 'img-fluid')); ?>
    </a>
    <?php
}
?>
```

#### Projects (Проекты)

```php
<?php
$thumbnail_id = get_post_thumbnail_id();

if ($thumbnail_id) {
    // Выбор размера в зависимости от типа проекта
    $project_type = get_post_meta(get_the_ID(), '_project_type', true);
    
    $image_size = 'codeweber_project_900-900'; // По умолчанию квадрат
    
    if ($project_type === 'portrait') {
        $image_size = 'codeweber_project_900-718';
    } elseif ($project_type === 'landscape') {
        $image_size = 'codeweber_project_900-800';
    }
    
    the_post_thumbnail($image_size, array('class' => 'img-fluid'));
}
?>
```

#### Vacancies (Вакансии)

```php
<?php
$thumbnail_id = get_post_thumbnail_id();
$image_url = '';

if ($thumbnail_id) {
    $image_url = wp_get_attachment_image_url($thumbnail_id, 'codeweber_vacancy');
}

// Fallback изображение
if (empty($image_url)) {
    $image_url = get_template_directory_uri() . '/dist/assets/img/photos/about6.jpg';
}
?>

<figure>
    <img src="<?php echo esc_url($image_url); ?>" 
         alt="<?php echo esc_attr(get_the_title()); ?>" 
         class="img-fluid">
</figure>
```

#### Clients (Клиенты)

```php
<?php
$logo_id = get_post_thumbnail_id();

if ($logo_id) {
    // Для списка клиентов - маленький размер
    echo wp_get_attachment_image($logo_id, 'codeweber_clients_115-60', false, array(
        'class' => 'img-fluid',
        'alt' => get_the_title()
    ));
    
    // Для карточки клиента - большой размер
    echo wp_get_attachment_image($logo_id, 'codeweber_clients_400-267', false, array(
        'class' => 'img-fluid',
        'alt' => get_the_title()
    ));
}
?>
```

### Использование в архивных шаблонах

```php
<?php
// В цикле архива
while (have_posts()) : the_post();
    $thumbnail_id = get_post_thumbnail_id();
    $post_type = get_post_type();
    
    // Определяем размер в зависимости от типа записи
    $image_size = 'full'; // По умолчанию
    
    switch ($post_type) {
        case 'staff':
            $image_size = 'codeweber_staff';
            break;
        case 'projects':
            $image_size = 'codeweber_project_900-900';
            break;
        case 'vacancies':
            $image_size = 'codeweber_vacancy';
            break;
    }
    
    if ($thumbnail_id) {
        ?>
        <div class="card">
            <figure>
                <a href="<?php the_permalink(); ?>">
                    <?php the_post_thumbnail($image_size, array('class' => 'img-fluid')); ?>
                </a>
            </figure>
            <div class="card-body">
                <h3><?php the_title(); ?></h3>
            </div>
        </div>
        <?php
    }
endwhile;
?>
```

### Использование в single шаблонах

```php
<?php
$thumbnail_id = get_post_thumbnail_id();
$card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : '';

if ($thumbnail_id) {
    ?>
    <div class="card<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
        <figure class="card-img-top<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
            <?php 
            // Получаем URL большого изображения для lightbox
            $large_image_url = wp_get_attachment_image_src($thumbnail_id, 'full');
            if ($large_image_url) :
            ?>
                <a href="<?php echo esc_url($large_image_url[0]); ?>" data-glightbox data-gallery="g1">
                    <?php the_post_thumbnail('codeweber_extralarge', array('class' => 'img-fluid')); ?>
                </a>
            <?php else : ?>
                <?php the_post_thumbnail('codeweber_extralarge', array('class' => 'img-fluid')); ?>
            <?php endif; ?>
        </figure>
    </div>
    <?php
}
?>
```

## ➕ Добавление новых размеров

### Шаг 1: Зарегистрировать размер

В файле `functions/images.php`, функция `codeweber_image_settings()`:

```php
function codeweber_image_settings()
{
    // Добавляем новый размер для вашего CPT
    add_image_size('codeweber_my_cpt_600-400', 600, 400, true);
    
    // Или без обрезки (для сохранения пропорций)
    add_image_size('codeweber_my_cpt_600-400', 600, 400, false);
    
    // Или с указанием позиции обрезки
    add_image_size('codeweber_my_cpt_600-400', 600, 400, array('center', 'center'));
}
```

**Параметры `add_image_size()`:**
- `$name` - Имя размера (уникальное)
- `$width` - Ширина в пикселях
- `$height` - Высота в пикселях
- `$crop` - Обрезка:
  - `true` - обрезка по центру
  - `false` - без обрезки (пропорциональное масштабирование)
  - `array('left', 'top')` - позиция обрезки

### Шаг 2: Добавить в разрешенные размеры

В функции `codeweber_get_allowed_image_sizes()`:

```php
$default_sizes = [
    'my_cpt' => [
        'codeweber_my_cpt_600-400',
        'woocommerce_gallery_thumbnail' // Если нужны миниатюры
    ],
    // ... остальные типы
];
```

### Шаг 3: Использовать в шаблонах

```php
<?php
$thumbnail_id = get_post_thumbnail_id();

if ($thumbnail_id) {
    echo wp_get_attachment_image($thumbnail_id, 'codeweber_my_cpt_600-400', false, array(
        'class' => 'img-fluid',
        'alt' => get_the_title()
    ));
}
?>
```

### Альтернатива: Использовать фильтр

Если не хотите редактировать основной файл:

```php
// В functions.php или в файле вашего CPT
add_filter('codeweber_allowed_image_sizes', 'add_my_cpt_image_sizes');

function add_my_cpt_image_sizes($sizes) {
    $sizes['my_cpt'] = [
        'codeweber_my_cpt_600-400',
        'woocommerce_gallery_thumbnail'
    ];
    return $sizes;
}
```

## 📊 Примеры использования

### Пример 1: Карточка записи с изображением

```php
<?php
$thumbnail_id = get_post_thumbnail_id();
$post_type = get_post_type();

// Определяем размер изображения
$image_size_map = [
    'staff' => 'codeweber_staff',
    'projects' => 'codeweber_project_900-900',
    'vacancies' => 'codeweber_vacancy',
    'clients' => 'codeweber_clients_300-200'
];

$image_size = $image_size_map[$post_type] ?? 'full';
?>

<article class="card">
    <?php if ($thumbnail_id) : ?>
        <figure class="card-img-top">
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail($image_size, array('class' => 'img-fluid')); ?>
            </a>
        </figure>
    <?php endif; ?>
    
    <div class="card-body">
        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <?php the_excerpt(); ?>
    </div>
</article>
```

### Пример 2: Галерея изображений

```php
<?php
$gallery_ids = get_post_meta(get_the_ID(), '_gallery_images', true);

if ($gallery_ids) {
    $gallery_ids = explode(',', $gallery_ids);
    ?>
    <div class="row g-3">
        <?php foreach ($gallery_ids as $image_id) : 
            $image_id = intval($image_id);
            $full_image = wp_get_attachment_image_src($image_id, 'full');
            ?>
            <div class="col-md-4">
                <a href="<?php echo esc_url($full_image[0]); ?>" data-glightbox="gallery">
                    <?php echo wp_get_attachment_image($image_id, 'codeweber_project_900-900', false, array(
                        'class' => 'img-fluid'
                    )); ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}
?>
```

### Пример 3: Адаптивное изображение с srcset

```php
<?php
$thumbnail_id = get_post_thumbnail_id();

if ($thumbnail_id) {
    // WordPress автоматически создает srcset для адаптивности
    the_post_thumbnail('codeweber_staff', array(
        'class' => 'img-fluid',
        'alt' => get_the_title(),
        'loading' => 'lazy' // Ленивая загрузка
    ));
}
?>
```

### Пример 4: Изображение с fallback

```php
<?php
$thumbnail_id = get_post_thumbnail_id();
$image_url = '';

if ($thumbnail_id) {
    $image_data = wp_get_attachment_image_src($thumbnail_id, 'codeweber_staff');
    $image_url = $image_data ? $image_data[0] : '';
}

// Fallback изображение
if (empty($image_url)) {
    $image_url = get_template_directory_uri() . '/dist/assets/img/photos/default-staff.jpg';
}
?>

<img src="<?php echo esc_url($image_url); ?>" 
     alt="<?php echo esc_attr(get_the_title()); ?>" 
     class="img-fluid">
```

## ⚡ Оптимизация и производительность

### Рекомендации

1. **Используйте правильные размеры**
   - Не используйте `full` для миниатюр
   - Создавайте размеры под конкретные области отображения

2. **Ленивая загрузка**
   ```php
   the_post_thumbnail('codeweber_staff', array(
       'loading' => 'lazy',
       'class' => 'img-fluid'
   ));
   ```

3. **Кэширование**
   - Функция `codeweber_get_allowed_image_sizes()` использует статическое кэширование
   - Не вызывайте функции получения изображений в циклах без необходимости

4. **Оптимизация качества**
   - Текущее качество: 80 (оптимально)
   - Можно изменить через фильтр `jpeg_quality`

5. **Удаление неиспользуемых размеров**
   - Система автоматически удаляет неиспользуемые размеры
   - Это экономит место на сервере

### Проверка созданных размеров

```php
<?php
$thumbnail_id = get_post_thumbnail_id();

if ($thumbnail_id) {
    $metadata = wp_get_attachment_metadata($thumbnail_id);
    
    if (isset($metadata['sizes'])) {
        echo '<pre>';
        print_r(array_keys($metadata['sizes']));
        echo '</pre>';
    }
}
?>
```

### Регенерация размеров для существующих изображений

Если вы добавили новый размер, нужно регенерировать изображения:

**Плагины:**
- Regenerate Thumbnails
- Force Regenerate Thumbnails

**Или через WP-CLI:**
```bash
wp media regenerate --yes
```

## 🔍 Отладка

### Проверка работы системы

```php
<?php
// В functions.php временно добавьте:
add_action('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
    error_log('Attachment ID: ' . $attachment_id);
    error_log('Parent ID: ' . codeweber_get_attachment_parent_id($attachment_id));
    error_log('Allowed sizes: ' . print_r(codeweber_get_allowed_image_sizes('staff'), true));
    error_log('Created sizes: ' . print_r(array_keys($metadata['sizes'] ?? []), true));
    return $metadata;
}, 10, 2);
?>
```

### Проверка существования размера

```php
<?php
$thumbnail_id = get_post_thumbnail_id();
$image_size = 'codeweber_staff';

$image_url = wp_get_attachment_image_url($thumbnail_id, $image_size);

if ($image_url) {
    echo 'Размер существует: ' . $image_url;
} else {
    echo 'Размер не найден. Нужна регенерация.';
}
?>
```

## 📋 Таблица размеров по типам записей

| Тип записи | Размер изображения | Параметры | Использование |
|------------|---------------------|-----------|---------------|
| **projects** | `codeweber_project_900-900` | 900x900px, crop: true | Квадратные изображения |
| | `codeweber_project_900-718` | 900x718px, crop: true | Портретные изображения |
| | `codeweber_project_900-800` | 900x800px, crop: true | Альбомные изображения |
| **staff** | `codeweber_staff` | 400x400px, crop: true | Фото сотрудников |
| **vacancies** | `codeweber_vacancy` | 600x400px, crop: true | Изображения вакансий |
| **clients** | `codeweber_clients_115-60` | 115x60px, crop: false | Маленькие логотипы |
| | `codeweber_clients_200-60` | 200x60px, crop: false | Средние логотипы |
| | `codeweber_clients_300-200` | 300x200px, crop: false | Карточки клиентов |
| | `codeweber_clients_400-267` | 400x267px, crop: false | Большие карточки |

## 🎯 Рекомендации

1. **Именование размеров:** Используйте префикс `codeweber_` для всех кастомных размеров
2. **Пропорции:** Учитывайте пропорции изображений при создании размеров
3. **Crop vs No Crop:** 
   - Используйте `crop: true` для фотографий и изображений с фиксированными пропорциями
   - Используйте `crop: false` для логотипов и изображений с разными пропорциями
4. **Retina:** Создавайте размеры с учетом 2x масштабирования для Retina дисплеев
5. **Документирование:** Комментируйте назначение каждого размера

## 🔗 Связанные документы

- [CPT_CREATION.md](../modules/CPT_CREATION.md) - Создание новых CPT
- [ARCHIVE_TEMPLATES.md](../modules/ARCHIVE_TEMPLATES.md) - Использование в архивных шаблонах
- [SINGLE_TEMPLATES.md](../modules/SINGLE_TEMPLATES.md) - Использование в single шаблонах

---

**Последнее обновление:** 2024-12-13




