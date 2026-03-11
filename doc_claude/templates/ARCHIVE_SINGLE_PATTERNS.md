# Паттерны архивных и single-шаблонов

## Как WordPress выбирает шаблон

```
Запрос к /staff/          → archive-staff.php
Запрос к /staff/john-doe/ → single-staff.php → single.php
Запрос к /page-slug/      → page.php
```

`single-{post_type}.php` в теме просто делает `require_once single.php` — весь рендеринг централизован в `single.php`.

---

## Архивный шаблон: структура

### Стандартный паттерн (`archive-staff.php` как образец)

```php
<?php
get_header();
get_pageheader();       // Блок PageHeader (баннер + заголовок)
?>

<?php if (have_posts()) : ?>
<section id="content-wrapper" class="wrapper bg-light">
  <div class="container">
    <?php
    $post_type = 'staff';   // <-- имя CPT
    global $opt_name;

    // 1. Получить выбранный шаблон из Redux
    $templateloop = Redux::get_option($opt_name, 'archive_template_select_' . $post_type);
    if (empty($templateloop)) {
        $templateloop = 'staff_1';   // дефолт
    }
    $template_file = "templates/archives/{$post_type}/{$templateloop}.php";

    // 2. Позиция сайдбара из Redux
    $sidebar_position = Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);
    $content_class = ($sidebar_position === 'none') ? 'col-12 py-14' : 'col-xl-9 pt-14';
    ?>

    <div class="row">
        <?php get_sidebar('left'); ?>

        <div class="<?php echo esc_attr($content_class); ?>">
            <div class="grid mb-5">
                <div class="row isotope g-3">
                    <?php while (have_posts()) : the_post();
                        // 3. Подключить шаблон карточки
                        if (!empty($templateloop) && locate_template($template_file)) {
                            get_template_part("templates/archives/{$post_type}/{$templateloop}");
                        } else {
                            get_template_part("templates/archives/{$post_type}/{$post_type}_1"); // fallback
                        }
                    endwhile; ?>
                </div>
            </div>

            <?php codeweber_posts_pagination(); ?>
        </div>

        <?php get_sidebar('right'); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
```

### Ключевые вызовы

| Функция | Откуда | Что делает |
|---------|--------|-----------|
| `get_header()` | WordPress | Шапка (выбирается через Redux) |
| `get_pageheader()` | `functions/global.php` | Блок PageHeader CPT |
| `Redux::get_option($opt_name, 'archive_template_select_' . $post_type)` | Redux | Выбранный вариант шаблона |
| `Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type)` | Redux | Позиция сайдбара |
| `get_sidebar('left')` / `get_sidebar('right')` | WordPress | Боковые сайдбары |
| `codeweber_posts_pagination()` | `functions/global.php` | Пагинация |
| `get_footer()` | WordPress | Подвал |

### Когда использовать `row-cols` вместо `isotope`

Для компактных сеток без фильтрации (staff_3, staff_4) используется `row-cols` Bootstrap:

```php
// Вместо isotope grid:
<div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-3 mb-5">
    <?php while (have_posts()) : the_post();
        get_template_part("templates/archives/{$post_type}/{$templateloop}");
    endwhile; ?>
</div>
```

### Empty state

```php
<?php if (have_posts()) : ?>
    <!-- ...контент... -->
<?php else : ?>
<section class="wrapper bg-light">
  <div class="container py-14">
    <p><?php esc_html_e('Nothing found.', 'codeweber'); ?></p>
  </div>
</section>
<?php endif; ?>
```

---

## Single шаблон: структура

`single.php` — единый universal шаблон для всех CPT.

### Поток выполнения

```php
get_header();

while (have_posts()) : the_post();
    get_pageheader();

    $post_type    = universal_get_post_type();   // CPT slug
    $post_type_lc = strtolower($post_type);

    // 1. Позиция сайдбара
    $sidebar_position = get_sidebar_position($opt_name);
    $content_class = ($sidebar_position === 'none') ? 'col-12' : 'col-md-8';

    // 2. Показывать ли h1 заголовок (из PageHeader или из Redux)
    $show_universal_title = ($pageheader_name === '1' && $single_pageheader_id !== 'disabled');

    // 3. Выбор sub-шаблона
    $templatesingle = Redux::get_option($opt_name, 'single_template_select_' . $post_type);
    $template_file  = "templates/singles/{$post_type_lc}/{$templatesingle}.php";

    // Приоритет поиска шаблона:
    // 1) templates/singles/{post_type}/{redux_selected_template}.php
    // 2) templates/singles/{post_type}/default.php
    // 3) templates/content/single.php (универсальный fallback)

endwhile;
get_footer();
```

### Redux-ключи для single

| Ключ | Значение |
|------|---------|
| `single_template_select_{post_type}` | Slug sub-шаблона (напр. `staff_1`) |
| `single_page_header_select_{post_type}` | `'disabled'` — скрыть PageHeader |
| `global_page_header_model` | `'1'` — показывать универсальный заголовок |
| `sidebar_position_single_{post_type}` | `'none'`, `'left'`, `'right'` |

---

## Структура директорий шаблонов

```
templates/
├── archives/
│   ├── staff/
│   │   ├── staff_1.php    # Isotope/grid карточки
│   │   ├── staff_2.php
│   │   ├── staff_3.php    # row-cols сетка
│   │   ├── staff_4.php
│   │   └── staff_5.php
│   ├── vacancies/
│   │   ├── vacancies_1.php  # С AJAX-фильтрами
│   │   ├── vacancies_2.php
│   │   └── ...
│   └── {post_type}/
│       └── {post_type}_{N}.php
│
├── singles/
│   ├── staff/
│   │   ├── default.php
│   │   └── staff_1.php
│   └── {post_type}/
│       ├── default.php
│       └── {post_type}_{N}.php
│
├── post-cards/           # Карточки для cw_render_post_card()
│   └── {post_type}/
│       └── card-{variant}.php
│
├── content/
│   └── single.php        # Универсальный fallback для single
│
├── header/               # Варианты шапки
├── footer/               # Варианты подвала
└── components/           # Переиспользуемые части
```

---

## Добавление нового варианта архива

### Шаг 1: Создать шаблон

```php
<?php
// templates/archives/staff/staff_6.php
// В $post доступны все поля, have_posts() уже выполнен выше
?>
<div class="col">
    <div class="card h-100">
        <?php if (has_post_thumbnail()) : ?>
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail('medium', ['class' => 'card-img-top']); ?>
            </a>
        <?php endif; ?>
        <div class="card-body">
            <h5 class="card-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h5>
            <?php
            $position = get_post_meta(get_the_ID(), 'staff_position', true);
            if ($position) {
                echo '<p class="card-text">' . esc_html($position) . '</p>';
            }
            ?>
        </div>
    </div>
</div>
```

### Шаг 2: Зарегистрировать вариант в Redux

В `redux-framework/theme-settings/theme-settings.php` найти раздел с `archive_template_select_staff` и добавить опцию:

```php
'options' => [
    'staff_1' => __('Style 1', 'codeweber'),
    'staff_2' => __('Style 2', 'codeweber'),
    // ...
    'staff_6' => __('Style 6 (My New)', 'codeweber'),
],
```

### Шаг 3: Добавить обработку в `archive-staff.php`

Если новый вариант требует другой структуры контейнера (не isotope), добавить условие по аналогии с `$use_row_cols`.

---

## Добавление нового варианта single

### Шаг 1: Создать шаблон

```php
<?php
// templates/singles/staff/staff_2.php
// $post доступен, the_post() уже вызван
?>
<div class="staff-detail">
    <div class="row">
        <div class="col-md-4">
            <?php the_post_thumbnail('medium'); ?>
        </div>
        <div class="col-md-8">
            <?php the_content(); ?>
        </div>
    </div>
</div>
```

### Шаг 2: Зарегистрировать в Redux

В `redux-framework/theme-settings/theme-settings.php` в раздел `single_template_select_staff`:

```php
'options' => [
    'staff_1' => __('Style 1', 'codeweber'),
    'staff_2' => __('Style 2 (My New)', 'codeweber'),
],
```

---

## Хуки в single.php

```php
// До контента записи
do_action('before_single_content', $post_type);

// После контента записи
do_action('after_single_content', $post_type);

// После всей секции
do_action('after_single_post', $post_type);
```

Пример использования:

```php
// Показать похожие записи только для staff
add_action('after_single_content', function($post_type) {
    if ($post_type !== 'staff') return;
    get_template_part('templates/components/related-staff');
});
```

---

## Полезные функции в шаблонах

```php
universal_get_post_type()         // Текущий тип записи (string)
universal_title()                  // Заголовок поста (безопасный вывод)
get_sidebar_position($opt_name)    // 'none' | 'left' | 'right'
codeweber_posts_pagination()       // Пагинация с настройками Redux
codeweber_posts_nav()              // Предыдущая/следующая запись (в single)
get_pageheader()                   // Блок PageHeader CPT
```
