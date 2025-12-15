# Добавление метаполей к single записям

Это руководство описывает процесс добавления метаполей (custom fields) к записям Custom Post Types.

## 📁 Где добавлять метаполя

Метаполя добавляются в файле CPT, который находится в `functions/cpt/cpt-{post_type}.php`.

**Пример:** Для Staff метаполя находятся в `functions/cpt/cpt-staff.php`

## 🔧 Создание метаполей

### Шаг 1: Добавление метабокса

Используйте хук `add_meta_boxes` для добавления метабокса:

```php
/**
 * Add metabox with additional fields for CPT
 */
function codeweber_add_custom_meta_boxes()
{
    add_meta_box(
        'custom_details',                           // ID метабокса
        esc_html__('Custom Information', 'codeweber'), // Заголовок
        'codeweber_custom_meta_box_callback',      // Callback функция
        'your_post_type',                           // Тип записи
        'normal',                                   // Контекст (normal, side, advanced)
        'high'                                      // Приоритет (high, core, default, low)
    );
}
add_action('add_meta_boxes', 'codeweber_add_custom_meta_boxes');
```

### Шаг 2: Создание callback функции

Callback функция отображает форму с полями:

```php
/**
 * Callback function for displaying the metabox
 */
function codeweber_custom_meta_box_callback($post)
{
    // Add nonce for security
    wp_nonce_field('custom_meta_box', 'custom_meta_box_nonce');

    // Get existing field values
    $field1 = get_post_meta($post->ID, '_custom_field1', true);
    $field2 = get_post_meta($post->ID, '_custom_field2', true);
    $field3 = get_post_meta($post->ID, '_custom_field3', true);
    ?>
    
    <div style="display: grid; grid-template-columns: 150px 1fr; gap: 12px; align-items: center;">
        <label for="custom_field1">
            <strong><?php echo esc_html__('Field 1', 'codeweber'); ?>:</strong>
        </label>
        <input type="text" 
               id="custom_field1" 
               name="custom_field1" 
               value="<?php echo esc_attr($field1); ?>" 
               style="width: 100%; padding: 8px;">
        
        <label for="custom_field2">
            <strong><?php echo esc_html__('Field 2', 'codeweber'); ?>:</strong>
        </label>
        <input type="email" 
               id="custom_field2" 
               name="custom_field2" 
               value="<?php echo esc_attr($field2); ?>" 
               style="width: 100%; padding: 8px;">
        
        <label for="custom_field3">
            <strong><?php echo esc_html__('Field 3', 'codeweber'); ?>:</strong>
        </label>
        <textarea id="custom_field3" 
                  name="custom_field3" 
                  rows="4" 
                  style="width: 100%; padding: 8px;"><?php echo esc_textarea($field3); ?></textarea>
    </div>
    
    <?php
}
```

### Шаг 3: Сохранение метаполей

Используйте хук `save_post_{post_type}` для сохранения данных:

```php
/**
 * Save metadata fields
 */
function codeweber_save_custom_meta($post_id)
{
    // Check nonce
    if (!isset($_POST['custom_meta_box_nonce']) || 
        !wp_verify_nonce($_POST['custom_meta_box_nonce'], 'custom_meta_box')) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Save fields
    $fields = [
        'custom_field1',
        'custom_field2',
        'custom_field3'
    ];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            // Sanitize and save
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        } else {
            // Clear field if not set
            delete_post_meta($post_id, '_' . $field);
        }
    }
}
add_action('save_post_your_post_type', 'codeweber_save_custom_meta');
```

## 📋 Типы полей

### Текстовое поле

```php
<input type="text" 
       id="field_name" 
       name="field_name" 
       value="<?php echo esc_attr($value); ?>" 
       style="width: 100%; padding: 8px;">
```

### Email поле

```php
<input type="email" 
       id="field_email" 
       name="field_email" 
       value="<?php echo esc_attr($value); ?>" 
       style="width: 100%; padding: 8px;">
```

### Телефон

```php
<input type="tel" 
       id="field_phone" 
       name="field_phone" 
       value="<?php echo esc_attr($value); ?>" 
       style="width: 100%; padding: 8px;">
```

### URL поле

```php
<input type="url" 
       id="field_url" 
       name="field_url" 
       value="<?php echo esc_attr($value); ?>" 
       placeholder="https://..." 
       style="width: 100%; padding: 8px;">
```

### Textarea

```php
<textarea id="field_description" 
          name="field_description" 
          rows="4" 
          style="width: 100%; padding: 8px;"><?php echo esc_textarea($value); ?></textarea>
```

### Выпадающий список (Select)

```php
<select id="field_select" name="field_select" style="width: 100%; padding: 8px;">
    <option value=""><?php echo esc_html__('Select Option', 'codeweber'); ?></option>
    <option value="option1" <?php selected($value, 'option1'); ?>>
        <?php echo esc_html__('Option 1', 'codeweber'); ?>
    </option>
    <option value="option2" <?php selected($value, 'option2'); ?>>
        <?php echo esc_html__('Option 2', 'codeweber'); ?>
    </option>
</select>
```

### Выпадающий список с таксономией

```php
<?php
$terms = get_terms([
    'taxonomy' => 'your_taxonomy',
    'hide_empty' => false,
]);
$selected_term = get_post_meta($post->ID, '_field_taxonomy', true);
?>
<select id="field_taxonomy" name="field_taxonomy" style="width: 100%; padding: 8px;">
    <option value=""><?php echo esc_html__('Select Term', 'codeweber'); ?></option>
    <?php if (!empty($terms) && !is_wp_error($terms)) : ?>
        <?php foreach ($terms as $term) : ?>
            <option value="<?php echo esc_attr($term->term_id); ?>" 
                    <?php selected($selected_term, $term->term_id); ?>>
                <?php echo esc_html($term->name); ?>
            </option>
        <?php endforeach; ?>
    <?php endif; ?>
</select>
```

### Media Upload (Изображение)

```php
<?php
$image_id = get_post_meta($post->ID, '_field_image', true);
$image_url = '';
if ($image_id) {
    $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
}
?>
<div>
    <input type="hidden" id="field_image" name="field_image" value="<?php echo esc_attr($image_id); ?>">
    <div id="field_image_preview" style="margin-bottom: 10px;">
        <?php if ($image_url) : ?>
            <img src="<?php echo esc_url($image_url); ?>" 
                 alt="Preview" 
                 style="max-width: 200px; height: auto;">
        <?php endif; ?>
    </div>
    <button type="button" 
            class="button" 
            id="field_image_upload_btn">
        <?php echo esc_html__('Select Image', 'codeweber'); ?>
    </button>
    <button type="button" 
            class="button" 
            id="field_image_remove_btn" 
            style="display: <?php echo $image_id ? 'inline-block' : 'none'; ?>;">
        <?php echo esc_html__('Remove Image', 'codeweber'); ?>
    </button>
</div>
```

## 🔒 Безопасность

### 1. Nonce проверка

Всегда используйте nonce для защиты от CSRF атак:

```php
// В callback функции
wp_nonce_field('meta_box_name', 'meta_box_nonce');

// В функции сохранения
if (!isset($_POST['meta_box_nonce']) || 
    !wp_verify_nonce($_POST['meta_box_nonce'], 'meta_box_name')) {
    return;
}
```

### 2. Проверка прав доступа

```php
if (!current_user_can('edit_post', $post_id)) {
    return;
}
```

### 3. Санитизация данных

Используйте соответствующие функции санитизации:

```php
// Текст
sanitize_text_field($_POST['field'])

// Email
sanitize_email($_POST['field'])

// URL
esc_url_raw($_POST['field'])

// Textarea
sanitize_textarea_field($_POST['field'])

// Число
intval($_POST['field'])

// HTML (с ограничениями)
wp_kses_post($_POST['field'])
```

## 📖 Пример: Полный код для Staff

См. файл `functions/cpt/cpt-staff.php` для полного примера с:

- Множественными полями
- Социальными сетями
- QR кодом
- Валидацией
- Сохранением данных

## 🎨 Использование метаполей в шаблонах

### Получение значения метаполя

```php
<?php
// Получить значение метаполя
$value = get_post_meta(get_the_ID(), '_field_name', true);

// Вывести значение
if (!empty($value)) {
    echo esc_html($value);
}
?>
```

### Пример использования в single шаблоне

```php
<?php
$position = get_post_meta(get_the_ID(), '_staff_position', true);
$email = get_post_meta(get_the_ID(), '_staff_email', true);
$phone = get_post_meta(get_the_ID(), '_staff_phone', true);
?>

<?php if (!empty($position)) : ?>
    <p class="text-muted"><?php echo esc_html($position); ?></p>
<?php endif; ?>

<?php if (!empty($email)) : ?>
    <a href="mailto:<?php echo esc_attr($email); ?>">
        <?php echo esc_html($email); ?>
    </a>
<?php endif; ?>
```

## 🔍 Добавление колонок в админке

### Добавление колонок

```php
function codeweber_add_custom_admin_columns($columns)
{
    $new_columns = [
        'cb' => $columns['cb'],
        'title' => $columns['title'],
        'custom_field1' => esc_html__('Field 1', 'codeweber'),
        'custom_field2' => esc_html__('Field 2', 'codeweber'),
        'date' => $columns['date']
    ];
    return $new_columns;
}
add_filter('manage_your_post_type_posts_columns', 'codeweber_add_custom_admin_columns');
```

### Заполнение колонок данными

```php
function codeweber_fill_custom_admin_columns($column, $post_id)
{
    switch ($column) {
        case 'custom_field1':
            echo esc_html(get_post_meta($post_id, '_custom_field1', true));
            break;
        case 'custom_field2':
            echo esc_html(get_post_meta($post_id, '_custom_field2', true));
            break;
    }
}
add_action('manage_your_post_type_posts_custom_column', 'codeweber_fill_custom_admin_columns', 10, 2);
```

### Сортировка колонок

```php
function codeweber_make_custom_columns_sortable($columns)
{
    $columns['custom_field1'] = 'custom_field1';
    return $columns;
}
add_filter('manage_edit-your_post_type_sortable_columns', 'codeweber_make_custom_columns_sortable');
```

## 🎯 Рекомендации

1. **Используйте префикс `_`** - Метаполя с префиксом `_` не отображаются в кастомных полях WordPress
2. **Группируйте поля** - Используйте несколько метабоксов для логической группировки
3. **Валидация** - Всегда валидируйте и санитизируйте входящие данные
4. **Документируйте** - Комментируйте назначение каждого поля
5. **Используйте переводы** - Все строки должны быть обернуты в функции перевода

## ✅ Проверка работы

1. Откройте запись вашего CPT в админке
2. Проверьте наличие метабокса с полями
3. Заполните поля и сохраните
4. Проверьте, что данные сохранились
5. Проверьте отображение в шаблоне

---

**Последнее обновление:** 2024-12-13




