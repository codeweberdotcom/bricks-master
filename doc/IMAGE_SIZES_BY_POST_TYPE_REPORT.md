# Отчет: Система размеров изображений для типов записей

## Обзор системы

Тема Codeweber использует **динамическую систему управления размерами изображений**, которая автоматически создает и удаляет размеры изображений в зависимости от типа записи (post type), к которому привязано изображение.

---

## 1. Регистрация размеров изображений

### Файл: `functions/images.php`

**Функция:** `codeweber_image_settings()`  
**Хук:** `after_setup_theme`

### Зарегистрированные размеры:

```php
// Для типа записи: projects
add_image_size('codeweber_project_900-900', 900, 900, true);  // Квадрат
add_image_size('codeweber_project_900-718', 900, 718, true); // Портрет
add_image_size('codeweber_project_900-800', 900, 800, true); // Альбом

// Для типа записи: staff
add_image_size('codeweber_staff', 400, 400, true); // Квадрат
```

### Удаленные стандартные размеры WordPress:

```php
remove_image_size('large');
remove_image_size('thumbnail');
remove_image_size('medium');
remove_image_size('medium_large');
remove_image_size('1536x1536');
remove_image_size('2048x2048');
```

**Примечание:** Стандартные размеры WordPress удаляются для экономии места на сервере.

---

## 2. Система фильтрации размеров по типу записи

### Функция: `codeweber_get_allowed_image_sizes($post_type, $post_id)`

**Назначение:** Определяет, какие размеры изображений разрешены для каждого типа записи.

**Расположение:** `functions/images.php` (строки 55-85)

### Базовые настройки:

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
    'default' => [] // Пустой массив - не удаляем никакие размеры
];
```

### Фильтры для кастомизации:

1. **`codeweber_allowed_image_sizes`** - Общий фильтр для всех типов записей
   ```php
   apply_filters('codeweber_allowed_image_sizes', $default_sizes, $post_type, $post_id);
   ```

2. **`codeweber_allowed_image_sizes_{$post_type}`** - Фильтр для конкретного типа записи
   ```php
   apply_filters("codeweber_allowed_image_sizes_{$post_type}", $sizes[$post_type], $post_id);
   ```

3. **`codeweber_allowed_image_sizes_default`** - Фильтр для типов записей по умолчанию
   ```php
   apply_filters("codeweber_allowed_image_sizes_default", $sizes['default'], $post_id);
   ```

### Пример использования фильтра:

**Файл:** `functions/user.php` (строки 225-231)

```php
// Добавляем фильтр для пользовательских аватаров
add_filter('codeweber_allowed_image_sizes', 'user_avatar_allowed_sizes');

function user_avatar_allowed_sizes($sizes)
{
   $sizes['user'] = ['thumbnail'];
   return $sizes;
}
```

---

## 3. Автоматическая фильтрация при загрузке

### Функция: `codeweber_filter_attachment_sizes_by_post_type($metadata, $attachment_id)`

**Назначение:** Автоматически удаляет неразрешенные размеры изображений при загрузке.

**Хук:** `wp_generate_attachment_metadata` (приоритет: 10)

**Расположение:** `functions/images.php` (строки 165-214)

### Логика работы:

1. **Определение родительского поста:**
   - Использует `codeweber_get_attachment_parent_id()` для определения родителя
   - Поддерживает загрузки через Redux Framework
   - Поддерживает загрузки через стандартный механизм WordPress

2. **Определение типа записи:**
   - Получает тип записи родительского поста
   - Специальная обработка для WooCommerce (`product`)
   - Fallback на `default` если тип не определен

3. **Фильтрация размеров:**
   - Получает разрешенные размеры через `codeweber_get_allowed_image_sizes()`
   - Удаляет файлы неразрешенных размеров
   - Удаляет записи о размерах из метаданных

### Безопасность:

- Проверка прав доступа (`current_user_can('upload_files')`)
- Проверка что файл находится в `uploads` директории
- Безопасное удаление файлов через `codeweber_safe_file_delete()`

---

## 4. Определение родительского поста

### Функция: `codeweber_get_attachment_parent_id($attachment_id)`

**Расположение:** `functions/images.php` (строки 122-155)

### Методы определения родителя:

1. **Стандартный метод:**
   ```php
   $parent_id = get_post_field('post_parent', $attachment_id);
   ```

2. **Временное хранилище (Redux):**
   ```php
   $temp_parent_id = get_transient('codeweber_current_upload_parent');
   ```

3. **Резервный метод (из запроса):**
   ```php
   if (!empty($_REQUEST['post_id']) && is_numeric($_REQUEST['post_id'])) {
       $request_parent_id = intval($_REQUEST['post_id']);
   }
   ```

### Хуки для установки родителя:

1. **`redux/metaboxes/upload/prefilter`** - Для загрузок через Redux
   ```php
   add_filter('redux/metaboxes/upload/prefilter', 'codeweber_set_parent_for_redux_uploads', 10, 3);
   ```

2. **`add_attachment`** - Для стандартных загрузок
   ```php
   add_action('add_attachment', 'codeweber_set_attachment_parent_on_upload');
   ```

3. **`wp_insert_attachment_data`** - Резервный метод
   ```php
   add_filter('wp_insert_attachment_data', 'codeweber_force_attachment_parent_before_upload', 10, 2);
   ```

---

## 5. Использование размеров в шаблонах

### Тип записи: `projects`

**Размеры:**
- `codeweber_project_900-900` (900x900px, crop: true)
- `codeweber_project_900-718` (900x718px, crop: true)
- `codeweber_project_900-800` (900x800px, crop: true)

**Использование:**
- В шаблонах проектов через `wp_get_attachment_image()` или `get_the_post_thumbnail()`
- В Post Grid блоке через параметр `image_size`

### Тип записи: `staff`

**Размеры:**
- `codeweber_staff` (400x400px, crop: true)

**Использование:**
- В шаблонах сотрудников
- В аватарах пользователей (через фильтр `user_avatar_allowed_sizes`)

**Пример из `functions/user.php`:**
```php
$staff_url = wp_get_attachment_image_url($avatar_id, 'thumbnail');
```

### Тип записи: `post` (блог)

**Размеры:**
- Используются стандартные размеры WordPress (если не удалены)
- `medium_large` - часто используется в Post Grid блоке
- `full` - используется по умолчанию в новой системе шаблонов

**Использование в Post Card Templates:**
```php
// functions/post-card-templates.php
$post_data = cw_get_post_card_data($post, $template_args['image_size'] ?? 'full');
```

**Примеры из шаблонов:**
```php
// templates/content/single.php
$large_image_url = wp_get_attachment_image_src($thumbnail_id, 'codeweber_extralarge');
the_post_thumbnail('codeweber_extralarge', array('class' => 'img-fluid'));
```

---

## 6. Схема работы системы

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Загрузка изображения через медиабиблиотеку или Redux     │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. Определение родительского поста                          │
│    - codeweber_get_attachment_parent_id()                   │
│    - Использование transient для Redux                      │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. Определение типа записи родителя                         │
│    - get_post_type($parent_id)                             │
│    - Специальная обработка для WooCommerce                  │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. Получение разрешенных размеров                           │
│    - codeweber_get_allowed_image_sizes($post_type)          │
│    - Применение фильтров                                    │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. Генерация всех размеров WordPress                        │
│    - wp_generate_attachment_metadata()                      │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. Фильтрация размеров                                      │
│    - codeweber_filter_attachment_sizes_by_post_type()       │
│    - Удаление неразрешенных размеров                        │
│    - Удаление файлов с диска                                │
└─────────────────────────────────────────────────────────────┘
```

---

## 7. Добавление нового размера для типа записи

### Шаг 1: Зарегистрировать размер

В `functions/images.php`, функция `codeweber_image_settings()`:

```php
add_image_size('codeweber_new_type_600-400', 600, 400, true);
```

### Шаг 2: Добавить в разрешенные размеры

В `functions/images.php`, функция `codeweber_get_allowed_image_sizes()`:

```php
$default_sizes = [
    'new_type' => [
        'codeweber_new_type_600-400',
        'woocommerce_gallery_thumbnail'
    ],
    // ... остальные типы
];
```

### Шаг 3: Использовать в шаблонах

```php
<?php 
$image_id = get_post_thumbnail_id();
echo wp_get_attachment_image($image_id, 'codeweber_new_type_600-400', false, [
    'class' => 'img-fluid',
    'alt' => get_the_title()
]);
?>
```

### Альтернатива: Использовать фильтр

В `functions/user.php` или в файле функций темы:

```php
add_filter('codeweber_allowed_image_sizes', 'add_custom_image_sizes');

function add_custom_image_sizes($sizes) {
    $sizes['new_type'] = [
        'codeweber_new_type_600-400',
        'woocommerce_gallery_thumbnail'
    ];
    return $sizes;
}
```

---

## 8. Текущие размеры по типам записей

| Тип записи | Размеры изображений | Параметры | Использование |
|------------|---------------------|-----------|---------------|
| **projects** | `codeweber_project_900-900` | 900x900px, crop: true | Квадратные изображения проектов |
| | `codeweber_project_900-718` | 900x718px, crop: true | Портретные изображения проектов |
| | `codeweber_project_900-800` | 900x800px, crop: true | Альбомные изображения проектов |
| | `woocommerce_gallery_thumbnail` | - | Миниатюры галереи |
| **staff** | `codeweber_staff` | 400x400px, crop: true | Фото сотрудников |
| | `woocommerce_gallery_thumbnail` | - | Миниатюры галереи |
| **user** | `thumbnail` | - | Аватары пользователей |
| **default** | Все стандартные размеры WordPress | - | Для всех остальных типов записей |

---

## 9. Особенности и ограничения

### Особенности:

1. **Кэширование:** Функция `codeweber_get_allowed_image_sizes()` использует статическое кэширование для оптимизации
2. **Безопасность:** Все операции с файлами проверяют права доступа и пути
3. **Гибкость:** Система фильтров позволяет легко расширять функциональность
4. **Автоматизация:** Размеры автоматически фильтруются при загрузке

### Ограничения:

1. **Только при загрузке:** Фильтрация происходит только при загрузке новых изображений
2. **Требуется родитель:** Система работает только если изображение привязано к посту
3. **Удаление стандартных размеров:** Стандартные размеры WordPress удаляются глобально

---

## 10. Рекомендации

### Для разработчиков:

1. **Используйте фильтры** вместо прямого редактирования `codeweber_get_allowed_image_sizes()`
2. **Добавляйте размеры** в `codeweber_image_settings()` при регистрации
3. **Тестируйте** на существующих изображениях после изменений
4. **Документируйте** новые размеры в комментариях

### Для оптимизации:

1. **Используйте crop: false** для логотипов и изображений с разными пропорциями
2. **Создавайте размеры** под конкретные области отображения
3. **Удаляйте неиспользуемые** размеры для экономии места
4. **Используйте Retina** - создавайте размеры с учетом 2x масштабирования

---

## 11. Примеры использования

### Пример 1: Получение изображения проекта

```php
<?php
$project_id = get_the_ID();
$thumbnail_id = get_post_thumbnail_id($project_id);

// Использование размера 900x900
echo wp_get_attachment_image($thumbnail_id, 'codeweber_project_900-900', false, [
    'class' => 'img-fluid',
    'alt' => get_the_title($project_id)
]);
?>
```

### Пример 2: Получение фото сотрудника

```php
<?php
$staff_id = get_the_ID();
$avatar_id = get_post_meta($staff_id, 'staff_avatar', true);

if ($avatar_id) {
    echo wp_get_attachment_image($avatar_id, 'codeweber_staff', false, [
        'class' => 'rounded-circle',
        'alt' => get_the_title($staff_id)
    ]);
}
?>
```

### Пример 3: Использование в Post Grid блоке

```php
// В render.php блока Post Grid
$image_size = isset($attributes['imageSize']) ? $attributes['imageSize'] : 'full';
$image_url = wp_get_attachment_image_src($thumbnail_id, $image_size)[0];
```

---

## Заключение

Система управления размерами изображений в теме Codeweber обеспечивает:

✅ **Автоматическую оптимизацию** - создаются только нужные размеры  
✅ **Гибкость** - легко расширяется через фильтры  
✅ **Безопасность** - проверка прав доступа и путей  
✅ **Экономию места** - удаление неиспользуемых размеров  
✅ **Удобство** - автоматическая работа без ручного вмешательства

**Файлы системы:**
- `functions/images.php` - основная логика
- `functions/user.php` - примеры использования фильтров
- `templates/post-cards/helpers.php` - использование в шаблонах


