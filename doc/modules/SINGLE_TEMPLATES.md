# Создание шаблонов Single записей

Это руководство описывает процесс создания шаблонов для отображения отдельных записей Custom Post Types на примере Staff.

## 📁 Структура файлов

Single шаблоны организованы следующим образом:

```
wp-content/themes/codeweber/
├── single.php                    # Главный single шаблон (универсальный)
├── single-{post_type}.php       # Специфичный шаблон для CPT (опционально)
└── templates/
    └── singles/
        └── {post_type}/
            ├── default.php      # Шаблон по умолчанию
            ├── {template_name}_1.php
            ├── {template_name}_2.php
            └── ...
```

**Пример для Staff:**
- `single.php` - Универсальный шаблон (используется для всех CPT)
- `single-staff.php` - Опциональный специфичный шаблон
- `templates/singles/staff/staff_1.php` - Шаблон отображения
- `templates/singles/staff/staff_2.php` - Альтернативный шаблон

## 🔧 Как работает система шаблонов

### Иерархия загрузки шаблонов

WordPress ищет шаблоны в следующем порядке:

1. `single-{post_type}-{slug}.php` - Для конкретной записи
2. `single-{post_type}.php` - Для типа записи
3. `single.php` - Универсальный шаблон (используется в теме)
4. `singular.php` - Общий шаблон для всех типов записей
5. `index.php` - Последний fallback

### В теме Codeweber

Главный файл `single.php` определяет, какой шаблон использовать:

```php
<?php
$post_type = universal_get_post_type();
$post_type_lc = strtolower($post_type);
global $opt_name;

// Получаем выбранный шаблон из Redux
$templatesingle = Redux::get_option($opt_name, 'single_template_select_' . $post_type);
$template_file = "templates/singles/{$post_type_lc}/{$templatesingle}.php";

// 1. Пытаемся загрузить выбранный шаблон
if (!empty($templatesingle) && locate_template($template_file)) {
    get_template_part("templates/singles/{$post_type_lc}/{$templatesingle}");
}
// 2. Fallback на default.php
elseif (locate_template("templates/singles/{$post_type_lc}/default.php")) {
    get_template_part("templates/singles/{$post_type_lc}/default");
}
// 3. Последний fallback - общий шаблон
else {
    get_template_part("templates/content/single", '');
}
?>
```

## 🎨 Создание single шаблона

### Шаг 1: Создание папки для шаблонов

Создайте папку `templates/singles/{post_type}/`

**Пример:** `templates/singles/staff/`

### Шаг 2: Создание шаблона по умолчанию

Создайте файл `default.php` в папке вашего CPT:

```php
<?php
/**
 * Template: Single {Post Type} Default
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

// Получаем метаполя
$custom_field = get_post_meta(get_the_ID(), '_custom_field', true);
$thumbnail_id = get_post_thumbnail_id();
?>

<section id="post-<?php the_ID(); ?>" <?php post_class('single-post'); ?>>
    <div class="row g-3">
        <!-- Левая колонка - Изображение -->
        <?php if ($thumbnail_id) : ?>
        <div class="col-lg-4 mb-10 mb-lg-0">
            <?php $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : ''; ?>
            <div class="card h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <figure class="card-img-top<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                    <?php the_post_thumbnail('codeweber_extralarge', array('class' => 'img-fluid')); ?>
                </figure>
            </div>
        </div>
        <?php endif; ?>

        <!-- Правая колонка - Контент -->
        <div class="col-lg-8">
            <?php $card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : ''; ?>
            <div class="card h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <div class="card-body px-6 py-5">
                    <h1 class="mb-4"><?php the_title(); ?></h1>
                    
                    <?php if (!empty($custom_field)) : ?>
                        <p class="text-muted mb-4"><?php echo esc_html($custom_field); ?></p>
                    <?php endif; ?>
                    
                    <hr class="my-6">
                    
                    <!-- Контент записи -->
                    <?php if (get_the_content()) : ?>
                        <div class="post-content">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
```

### Шаг 3: Создание альтернативных шаблонов

Создайте дополнительные шаблоны с разными вариантами отображения:

**Пример:** `templates/singles/staff/staff_1.php`

```php
<?php
/**
 * Template: Single Staff - Template 1
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

// Получаем метаполя
$position = get_post_meta(get_the_ID(), '_staff_position', true);
$name = get_post_meta(get_the_ID(), '_staff_name', true);
$surname = get_post_meta(get_the_ID(), '_staff_surname', true);
$email = get_post_meta(get_the_ID(), '_staff_email', true);
$phone = get_post_meta(get_the_ID(), '_staff_phone', true);
$company = get_post_meta(get_the_ID(), '_staff_company', true);
$job_phone = get_post_meta(get_the_ID(), '_staff_job_phone', true);

// Получаем отдел из таксономии
$departments = get_the_terms(get_the_ID(), 'departments');
$department_name = '';
if ($departments && !is_wp_error($departments) && !empty($departments)) {
    $department_name = $departments[0]->name;
}

$thumbnail_id = get_post_thumbnail_id();
$card_radius = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius() : '';
?>

<section id="post-<?php the_ID(); ?>" <?php post_class('staff single'); ?>>
    <div class="row g-3">
        <!-- Левая колонка - Изображение -->
        <div class="col-lg-4 mb-10 mb-lg-0">
            <div class="card h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <?php if ($thumbnail_id) : ?>
                    <figure class="card-img-top<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                        <?php 
                        $large_image_url = wp_get_attachment_image_src($thumbnail_id, 'codeweber_extralarge');
                        if ($large_image_url) :
                        ?>
                            <a href="<?php echo esc_url($large_image_url[0]); ?>" data-glightbox data-gallery="g1">
                                <?php the_post_thumbnail('codeweber_extralarge', array('class' => 'img-fluid')); ?>
                            </a>
                        <?php else : ?>
                            <?php the_post_thumbnail('codeweber_extralarge', array('class' => 'img-fluid')); ?>
                        <?php endif; ?>
                    </figure>
                <?php endif; ?>
            </div>
        </div>

        <!-- Правая колонка - Информация -->
        <div class="col-lg-8">
            <div class="card h-100<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <div class="card-body px-6 py-5">
                    <!-- Имя и должность -->
                    <?php if (!empty($name) || !empty($surname)) : ?>
                        <h2 class="mb-1">
                            <?php 
                            $full_name = trim($name . ' ' . $surname);
                            echo esc_html(!empty($full_name) ? $full_name : get_the_title());
                            ?>
                        </h2>
                    <?php else : ?>
                        <h2 class="mb-1"><?php the_title(); ?></h2>
                    <?php endif; ?>

                    <?php 
                    $position_with_company = $position;
                    if (!empty($company)) {
                        $position_with_company = trim($position . ' ' . $company);
                    }
                    if (!empty($position_with_company)) : 
                    ?>
                        <p class="text-muted mb-4"><?php echo esc_html($position_with_company); ?></p>
                    <?php endif; ?>

                    <hr class="my-6">

                    <!-- Контент записи -->
                    <?php if (get_the_content()) : ?>
                        <div class="post-content mb-6">
                            <?php the_content(); ?>
                        </div>
                        <hr class="my-6">
                    <?php endif; ?>

                    <!-- Контактная информация -->
                    <?php if (!empty($email) || !empty($phone) || !empty($job_phone)) : ?>
                        <h3 class="mb-4"><?php echo esc_html__('Contact Information', 'codeweber'); ?></h3>
                        <div class="row g-4 mb-6">
                            <?php if (!empty($email)) : ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="uil uil-envelope fs-20 text-primary me-3"></i>
                                        <div>
                                            <strong><?php echo esc_html__('E-Mail', 'codeweber'); ?>:</strong><br>
                                            <a href="mailto:<?php echo esc_attr($email); ?>" class="link-body">
                                                <?php echo esc_html($email); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($phone)) : ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="uil uil-phone fs-20 text-primary me-3"></i>
                                        <div>
                                            <strong><?php echo esc_html__('Phone', 'codeweber'); ?>:</strong><br>
                                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>" class="link-body">
                                                <?php echo esc_html($phone); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($job_phone)) : ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="uil uil-phone-alt fs-20 text-primary me-3"></i>
                                        <div>
                                            <strong><?php echo esc_html__('Job Phone', 'codeweber'); ?>:</strong><br>
                                            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $job_phone)); ?>" class="link-body">
                                                <?php echo esc_html($job_phone); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
```

## ⚙️ Интеграция с Redux Framework

### Настройка шаблона single

В Redux Framework доступны следующие настройки:

1. **Выбор шаблона:** `single_template_select_{post_type}`
   - Значения: `staff_1`, `staff_2`, `staff_3`, и т.д.

2. **Позиция сайдбара:** `sidebar_position_single_{post_type}`
   - Значения: `left`, `right`, `none`

3. **Заголовок страницы:** `single_page_header_select_{post_type}`
   - Может быть отключен значением `disabled`

### Пример получения настроек:

```php
global $opt_name;
$templatesingle = Redux::get_option($opt_name, 'single_template_select_staff');
$sidebar_position = get_sidebar_position($opt_name);
$show_title = Redux::get_option($opt_name, 'single_page_header_select_staff') !== 'disabled';
```

## 🎨 Структура single.php

Главный файл `single.php` уже содержит всю необходимую логику:

```php
<?php get_header();

while (have_posts()) :
    the_post();
    get_pageheader();

    $post_type = universal_get_post_type();
    global $opt_name;
    $sidebar_position = get_sidebar_position($opt_name);
    $content_class = ($sidebar_position === 'none') ? 'col-12' : 'col-md-8';
    ?>

    <section class="wrapper">
        <div class="container">
            <div class="row gx-lg-8 gx-xl-12">
                <?php get_sidebar('left'); ?>
                
                <div id="article-wrapper" class="<?php echo $content_class; ?> py-14">
                    <?php if ($show_universal_title) { ?>
                        <h1 class="display-4 mb-10"><?php echo universal_title(); ?></h1>
                    <?php } ?>
                    
                    <?php
                    // Загрузка шаблона
                    $templatesingle = Redux::get_option($opt_name, 'single_template_select_' . $post_type);
                    // ... логика загрузки шаблона ...
                    ?>
                    
                    <!-- Навигация между записями -->
                    <nav class="nav mt-8">
                        <?php
                        $previous_post = get_adjacent_post(false, '', true);
                        if ($previous_post) {
                            printf('<a href="%s" class="hover-5 left">%s</a>', 
                                get_permalink($previous_post->ID),
                                get_the_title($previous_post->ID)
                            );
                        }
                        ?>
                    </nav>
                </div>
                
                <?php get_sidebar('right'); ?>
            </div>
        </div>
    </section>
    
<?php
endwhile;
get_footer();
?>
```

## 📝 Примеры существующих шаблонов

### Staff Single Templates

- **`staff_1.php`** - Базовый шаблон с изображением и контактами
- **`staff_2.php`** - Альтернативный вариант отображения
- **`staff_3.php`** - Еще один вариант
- **`staff_4.php`** - Расширенный шаблон
- **`staff_5.php`** - Полный шаблон с дополнительной информацией
- **`default.php`** - Шаблон по умолчанию

## 🎯 Рекомендации

1. **Используйте префиксы** - Называйте шаблоны как `{post_type}_1`, `{post_type}_2`, и т.д.
2. **Создавайте default.php** - Всегда создавайте шаблон по умолчанию
3. **Экранирование данных** - Используйте `esc_html()`, `esc_attr()`, `esc_url()` для всех выводимых данных
4. **Проверка существования** - Проверяйте наличие метаполей перед выводом
5. **Используйте функции темы** - Используйте `getThemeCardImageRadius()` и другие функции темы
6. **Поддержка изображений** - Всегда проверяйте наличие миниатюры перед выводом
7. **Адаптивность** - Используйте Bootstrap классы для адаптивности

## 🔍 Работа с метаполями

### Получение метаполей

```php
<?php
// Одно значение
$value = get_post_meta(get_the_ID(), '_meta_key', true);

// Множественные значения
$values = get_post_meta(get_the_ID(), '_meta_key', false);
?>
```

### Работа с таксономиями

```php
<?php
// Получить термины таксономии
$terms = get_the_terms(get_the_ID(), 'taxonomy_name');

if ($terms && !is_wp_error($terms)) {
    foreach ($terms as $term) {
        echo esc_html($term->name);
    }
}
?>
```

### Работа с изображениями

```php
<?php
$thumbnail_id = get_post_thumbnail_id();

if ($thumbnail_id) {
    // Получить URL изображения определенного размера
    $image_url = wp_get_attachment_image_src($thumbnail_id, 'codeweber_extralarge');
    
    // Вывести изображение
    the_post_thumbnail('codeweber_extralarge', array('class' => 'img-fluid'));
}
?>
```

## ✅ Проверка работы

1. Создайте запись вашего CPT
2. Перейдите на single страницу: `yoursite.com/{post_type}/test-post/`
3. Проверьте отображение контента
4. Проверьте работу сайдбаров (если включены)
5. Проверьте выбор разных шаблонов через Redux
6. Проверьте навигацию между записями

## 🔗 Связанные документы

- [CPT_CREATION.md](CPT_CREATION.md) - Создание новых CPT
- [METAFIELDS.md](METAFIELDS.md) - Добавление метаполей
- [SIDEBARS.md](SIDEBARS.md) - Добавление сайдбаров
- [ARCHIVE_TEMPLATES.md](ARCHIVE_TEMPLATES.md) - Создание архивных шаблонов

---

**Последнее обновление:** 2024-12-13




