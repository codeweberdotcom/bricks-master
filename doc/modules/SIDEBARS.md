# Добавление новых сайдбаров

Это руководство описывает процесс добавления новых сайдбаров в тему Codeweber.

## 📁 Структура файлов

Сайдбары регистрируются в двух основных файлах:

- `functions/sidebars.php` - Основные сайдбары темы
- `functions/sidebars-redux.php` - Сайдбары для CPT, управляемые через Redux

## 🔧 Способы добавления сайдбаров

### Способ 1: Статический сайдбар (для общих целей)

Используйте этот способ для сайдбаров, которые не привязаны к конкретному CPT.

#### 1. Откройте файл `functions/sidebars.php`

#### 2. Добавьте функцию регистрации сайдбара

```php
function codeweber_register_custom_sidebar()
{
    codeweber_sidebars(
        __('Custom Sidebar', 'codeweber'),        // Название
        'sidebar-custom',                         // ID сайдбара
        __('Description of custom sidebar', 'codeweber'), // Описание
        'h3',                                     // Тег заголовка
        'custom-title-class'                      // CSS класс заголовка
    );
}
add_action('widgets_init', 'codeweber_register_custom_sidebar');
```

#### 3. Использование функции `codeweber_sidebars()`

```php
codeweber_sidebars(
    $sidebar_name,      // Название сайдбара (для админки)
    $sidebar_id,        // Уникальный ID сайдбара
    $sidebar_description, // Описание
    $title_tag,         // HTML тег для заголовков виджетов (по умолчанию 'h3')
    $title_class        // CSS класс для заголовков (по умолчанию 'mb-4')
);
```

### Способ 2: Сайдбар для CPT через Redux

Сайдбары для CPT автоматически регистрируются через Redux Framework, если CPT включен в настройках.

#### Как это работает:

1. Функция `codeweber_register_cpt_redux_sidebars()` в `functions/sidebars-redux.php` автоматически сканирует все файлы CPT
2. Для каждого включенного CPT создается сайдбар с ID равным названию CPT
3. ID сайдбара формируется из имени файла CPT (без префикса `cpt-` и расширения `.php`)

**Пример:**
- Файл: `cpt-staff.php` → ID сайдбара: `staff`
- Файл: `cpt-vacancies.php` → ID сайдбара: `vacancies`

#### Настройка через Redux:

1. Перейдите в **Redux Framework → Custom Post Types**
2. Включите нужный CPT (переключатель `cpt_switch_{название}`)
3. Сайдбар автоматически зарегистрируется

### Способ 3: Прямая регистрация через `register_sidebar()`

Для полного контроля используйте стандартную функцию WordPress:

```php
function register_my_custom_sidebar()
{
    register_sidebar([
        'name'          => __('My Custom Sidebar', 'codeweber'),
        'id'            => 'my-custom-sidebar',
        'description'   => __('Widget area for custom content', 'codeweber'),
        'before_widget' => '<div class="widget mb-4 %2$s clearfix">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="mb-4">',
        'after_title'   => '</h3>',
    ]);
}
add_action('widgets_init', 'register_my_custom_sidebar');
```

## 📍 Использование сайдбаров в шаблонах

### В архивных шаблонах

```php
<?php
// Получаем позицию сайдбара из Redux
$post_type = 'staff';
global $opt_name;
$sidebar_position = Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);

// Определяем класс контента
$content_class = ($sidebar_position === 'none') ? 'col-12 py-14' : 'col-xl-9 pt-14';
?>

<div class="row">
    <?php get_sidebar('left'); ?>  <!-- Левый сайдбар -->
    
    <div class="<?php echo esc_attr($content_class); ?>">
        <!-- Контент -->
    </div>
    
    <?php get_sidebar('right'); ?>  <!-- Правый сайдбар -->
</div>
```

### В single шаблонах

```php
<?php
// Получаем позицию сайдбара
$sidebar_position = get_sidebar_position($opt_name);
$content_class = ($sidebar_position === 'none') ? 'col-12' : 'col-md-8';
?>

<div class="row gx-lg-8 gx-xl-12">
    <?php get_sidebar('left'); ?>
    
    <div class="<?php echo esc_attr($content_class); ?>">
        <!-- Контент -->
    </div>
    
    <?php get_sidebar('right'); ?>
</div>
```

### Вывод конкретного сайдбара

```php
<?php
// Вывод сайдбара по ID
if (is_active_sidebar('staff')) {
    dynamic_sidebar('staff');
}
?>
```

## 🎨 Добавление кастомных виджетов к CPT

### Обзор

В теме Codeweber есть два хука для добавления кастомного контента (виджетов) в сайдбары CPT:

1. **`codeweber_after_widget`** - Срабатывает всегда, даже если в сайдбаре нет активных виджетов
2. **`codeweber_after_sidebar`** - Срабатывает только когда в сайдбаре есть активные виджеты

### Где добавлять код

Добавляйте код в файл `functions/sidebars.php` в конец файла (после функции `get_sidebar_position()`).

### Хук 1: codeweber_after_widget

Этот хук срабатывает **всегда**, независимо от наличия активных виджетов в сайдбаре.

**Используйте для:**
- Навигационных меню (как для legal)
- Контента, который должен отображаться всегда
- Списков записей

**Пример: Навигация для Legal CPT**

```php
add_action('codeweber_after_widget', function ($sidebar_id) {
    if ($sidebar_id === 'legal') {
        // Проверяем, существует ли тип записи
        if (!post_type_exists('legal')) {
            return;
        }

        // Получаем все записи legal
        $legal_posts = get_posts([
            'post_type'      => 'legal',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ]);

        if ($legal_posts) {
            echo '<div class="widget">
                    <nav id="sidebar-nav">
                        <ul class="list-unstyled text-reset">';

            $index = 1;
            $current_id = get_queried_object_id();

            foreach ($legal_posts as $post) {
                // Проверяем мета _hide_from_archive
                $hide = get_post_meta($post->ID, '_hide_from_archive', true);
                if ($hide === '1') {
                    continue; // пропускаем скрытую запись
                }

                $permalink = get_permalink($post);
                $active_class = ($current_id === $post->ID) ? ' active' : '';
                echo '<li><a class="nav-link' . $active_class . '" href="' . esc_url($permalink) . '">' . $index . '. ' . esc_html(get_the_title($post)) . '</a></li>';
                $index++;
            }

            echo '</ul>
                 </nav>
              </div>';
        }
    }
});
```

### Хук 2: codeweber_after_sidebar

Этот хук срабатывает **только когда** в сайдбаре есть активные виджеты.

**Используйте для:**
- Дополнительной информации о текущей записи
- Связанного контента
- Действий (кнопки, формы)

**Пример: Детали вакансии**

```php
add_action('codeweber_after_sidebar', function ($sidebar_id) {
    if ($sidebar_id === 'vacancies') {
        // Проверяем, существует ли тип записи
        if (!post_type_exists('vacancies')) {
            return;
        }

        // Проверяем, что мы на single странице вакансии
        if (!is_singular('vacancies')) {
            return;
        }

        // Получаем данные вакансии
        $vacancy_data = get_vacancy_data_array();
        
        // Получаем стили из Redux
        $button_style = function_exists('getThemeButton') ? getThemeButton('') : '';
        $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : '';
        ?>
        <div class="widget">
            <div class="card<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <?php 
                $thumbnail_id = get_post_thumbnail_id();
                $image_url = '';
                if ($thumbnail_id) {
                    $image_url = wp_get_attachment_image_url($thumbnail_id, 'codeweber_vacancy');
                }
                
                if (empty($image_url)) {
                    $image_url = get_template_directory_uri() . '/dist/assets/img/photos/about6.jpg';
                }
                ?>
                <figure<?php echo $card_radius ? ' class="' . esc_attr($card_radius) . '"' : ''; ?>>
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="img-fluid">
                </figure>

                <div class="card-body">
                    <div class="mb-6">
                        <h3 class="mb-4"><?php _e('Details', 'codeweber'); ?></h3>

                        <?php if (!empty($vacancy_data['location'])) : ?>
                            <p class="mb-1 d-flex align-items-center">
                                <i class="uil uil-map-marker-alt text-primary me-2"></i>
                                <span><?php echo esc_html($vacancy_data['location']); ?></span>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($vacancy_data['employment_type'])) : ?>
                            <p class="mb-1 d-flex align-items-center">
                                <i class="uil uil-calendar-alt text-primary me-2"></i>
                                <span><?php echo esc_html($vacancy_data['employment_type']); ?></span>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($vacancy_data['salary'])) : ?>
                            <p class="mb-1 d-flex align-items-center">
                                <i class="uil uil-money-stack text-primary me-2"></i>
                                <span><?php echo esc_html($vacancy_data['salary']); ?></span>
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($vacancy_data['pdf_url'])) : ?>
                        <a href="javascript:void(0)" class="btn btn-primary btn-icon btn-icon-start w-100 mb-2<?php echo esc_attr($button_style); ?>" data-bs-toggle="download" data-value="vac-<?php echo esc_attr(get_the_ID()); ?>">
                            <i class="uil uil-file-download"></i>
                            <?php _e('Download document', 'codeweber'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
});
```

### Пример: Виджет для Staff CPT

```php
add_action('codeweber_after_sidebar', function ($sidebar_id) {
    if ($sidebar_id === 'staff' && is_singular('staff')) {
        // Получаем метаполя сотрудника
        $position = get_post_meta(get_the_ID(), '_staff_position', true);
        $email = get_post_meta(get_the_ID(), '_staff_email', true);
        $phone = get_post_meta(get_the_ID(), '_staff_phone', true);
        
        $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : '';
        ?>
        <div class="widget">
            <div class="card<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <div class="card-body">
                    <h3 class="mb-4"><?php _e('Quick Contact', 'codeweber'); ?></h3>
                    
                    <?php if (!empty($position)) : ?>
                        <p class="text-muted mb-3"><?php echo esc_html($position); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($email)) : ?>
                        <a href="mailto:<?php echo esc_attr($email); ?>" class="btn btn-primary w-100 mb-2">
                            <i class="uil uil-envelope"></i> <?php _e('Send Email', 'codeweber'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($phone)) : ?>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>" class="btn btn-outline-primary w-100">
                            <i class="uil uil-phone"></i> <?php _e('Call', 'codeweber'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
});
```

### Пример: Навигация по категориям для FAQ

```php
add_action('codeweber_after_sidebar', function ($sidebar_id) {
    if ($sidebar_id === 'faq') {
        if (!post_type_exists('faq')) {
            return;
        }

        // Получаем текущий якорь из URL
        $current_anchor = '';
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '#') !== false) {
            $current_anchor = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '#') + 1);
        }

        // Получаем все категории FAQ
        $faq_categories = get_terms([
            'taxonomy'   => 'faq_categories',
            'hide_empty' => true,
        ]);

        if (!empty($faq_categories) && !is_wp_error($faq_categories)) {
            echo '<div class="widget">
                    <nav id="sidebar-nav">
                        <ul class="list-unstyled text-reset">';

            $index = 1;
            
            foreach ($faq_categories as $category) {
                $category_anchor = sanitize_title($category->name);
                $anchor_url = '#' . $category_anchor;
                $active_class = ($current_anchor === $category_anchor) ? ' active' : '';
                
                echo '<li><a class="nav-link scroll' . $active_class . '" href="' . esc_attr($anchor_url) . '">' . $index . '. ' . esc_html($category->name) . '</a></li>';
                $index++;
            }

            echo '</ul>
                 </nav>
              </div>';
        }
    }
});
```

## 📋 Когда использовать какой хук?

### Используйте `codeweber_after_widget`, если:

- ✅ Контент должен отображаться всегда (навигация, меню)
- ✅ Нужен список записей или категорий
- ✅ Контент не зависит от наличия виджетов

### Используйте `codeweber_after_sidebar`, если:

- ✅ Контент связан с текущей записью
- ✅ Нужно отображать только когда есть активные виджеты
- ✅ Дополнительная информация о записи
- ✅ Действия (кнопки, формы)

## 🎯 Рекомендации

1. **Проверяйте существование CPT** - Всегда проверяйте `post_type_exists()` перед использованием
2. **Проверяйте контекст** - Используйте `is_singular()`, `is_archive()` для проверки типа страницы
3. **Используйте правильные функции темы** - `getThemeCardImageRadius()`, `getThemeButton()` и т.д.
4. **Экранирование данных** - Всегда используйте `esc_html()`, `esc_attr()`, `esc_url()`
5. **Структура виджета** - Оберните контент в `<div class="widget">` для единообразия
6. **Проверка данных** - Проверяйте наличие данных перед выводом

## 🔍 Отладка

Если виджет не отображается:

1. Проверьте, что хук правильно добавлен в `functions/sidebars.php`
2. Убедитесь, что `$sidebar_id` соответствует ID сайдбара CPT
3. Проверьте условия (`is_singular()`, `post_type_exists()`)
4. Добавьте временный `error_log()` для отладки:

```php
add_action('codeweber_after_sidebar', function ($sidebar_id) {
    error_log('Sidebar ID: ' . $sidebar_id);
    error_log('Is singular: ' . (is_singular('your_post_type') ? 'yes' : 'no'));
    // ... ваш код
});
```

## 🔍 Определение позиции сайдбара

Функция `get_sidebar_position()` определяет позицию сайдбара для текущей страницы:

```php
/**
 * Получает позицию сайдбара для текущей страницы/записи
 * 
 * @param string $opt_name Имя опции Redux
 * @return string Позиция сайдбара (left|right|none)
 */
function get_sidebar_position($opt_name)
{
    $post_type = universal_get_post_type();
    
    // Для архивов
    if (!is_singular($post_type)) {
        return Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);
    }
    
    // Для single страниц
    $post_id = get_the_ID();
    $custom_sidebar_enabled = Redux::get_post_meta($opt_name, $post_id, 'custom-page-sidebar-type') === '2';
    
    if ($custom_sidebar_enabled) {
        $custom_position = Redux::get_post_meta($opt_name, $post_id, 'custom-page-sidebar-position');
        if (!empty($custom_position)) {
            return $custom_position;
        }
    }
    
    return Redux::get_option($opt_name, 'sidebar_position_single_' . $post_type);
}
```

## 📋 Существующие сайдбары в теме

### Основные сайдбары:

- **`sidebar-main`** - Основной сайдбар
- **`sidebar-woo`** - Сайдбар для WooCommerce (если плагин активен)

### Сайдбары для CPT (автоматические):

- **`staff`** - Для типа записи Staff
- **`vacancies`** - Для типа записи Vacancies
- **`testimonials`** - Для типа записи Testimonials
- И другие, в зависимости от включенных CPT

### Специальные сайдбары:

- **`header-right`** - Правая часть хедера
- **`header-right-1`** - Дополнительная область хедера
- **`mobile-menu-footer`** - Футер мобильного меню
- **`header-widget-1`**, **`header-widget-2`**, **`header-widget-3`** - Виджеты хедера

## ✅ Проверка работы

1. Перейдите в **Внешний вид → Виджеты**
2. Найдите ваш сайдбар в списке
3. Добавьте тестовый виджет
4. Проверьте отображение на фронтенде

## 🎯 Рекомендации

1. **Используйте уникальные ID** - ID сайдбара должен быть уникальным
2. **Следуйте соглашениям** - Для CPT используйте автоматическую регистрацию через Redux
3. **Проверяйте активность** - Используйте `is_active_sidebar()` перед выводом
4. **Используйте хуки** - Для кастомного контента используйте `codeweber_after_widget` или `codeweber_after_sidebar`

---

**Последнее обновление:** 2024-12-13

