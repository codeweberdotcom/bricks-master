---
name: cpt
description: Создать новый Custom Post Type в дочерней теме CodeWeber — по всем паттернам темы
argument-hint: "название CPT, например: Portfolio или portfolio"
---

Создай новый Custom Post Type в этой дочерней теме CodeWeber: `$ARGUMENTS`

> **Правило языка:** весь код, labels, slug-и, meta keys — только на **английском**.
> Русский — только в `languages/ru_RU.po`.

Прочитай `.claude/RULES.md` и `../PARENT_SLUG/doc_claude/development/CODING_STANDARDS.md`.

> Где `PARENT_SLUG` — значение строки `Template:` из `./style.css`.

---

## Шаг 1: Коммит текущего состояния

Запусти `git status`. Если есть незакоммиченные изменения — коммит перед началом.

---

## Шаг 2: Анализ

1. Прочитай `./style.css` — извлеки `PARENT_SLUG` (строка `Template:`), `TEXT_DOMAIN` (строка `Text Domain:`), `CHILD_SLUG` (строка `Theme URI:` или папка темы).

2. Прочитай:
   - `../PARENT_SLUG/doc_claude/cpt/CPT_CATALOG.md` — проверь на дубли
   - `../PARENT_SLUG/doc_claude/cpt/CPT_HOW_TO_ADD.md`

3. Проверь `./functions/cpt/` — нет ли похожего CPT в дочерней.
   Проверь `../PARENT_SLUG/functions/cpt/` — нет ли похожего в родительской.

4. Задай пользователю два блока уточняющих вопросов:

### Блок А — Базовые характеристики записи

| Вопрос | Влияет на |
|--------|-----------|
| Изображение (featured image) | `supports: thumbnail` |
| Заголовок | `supports: title` |
| Краткое описание (excerpt) | `supports: excerpt` |
| Полный контент (editor) | `supports: editor` |
| Автор | `supports: author` |
| Сортировка / иерархия | `supports: page-attributes`, `hierarchical: true` |
| История версий | `supports: revisions` |
| Публичный или структурный (только в админке) | `public`, `publicly_queryable` |
| Исключить из поиска сайта | `exclude_from_search: true` |
| Gutenberg или классический редактор | `use_block_editor_for_post_type` filter |
| REST API | `show_in_rest: true/false` |

### Блок Б — Single-страница (если нужен single)

| Вопрос | Влияет на |
|--------|-----------|
| Блок автора (аватар, имя, bio) | `get_the_author_meta()` + аватар |
| Комментарии | `comments_template()` + `supports: comments` |
| Поделиться (VK, Telegram, WhatsApp, копировать) | share-блок |
| Рекомендуемые записи после поста | `WP_Query` по таксономии или rand |

5. Уточни:
   - Нужен ли **archive** (`/{slug}/`)
   - Нужен ли **single** (`/{slug}/{post-name}/`)
   - Нужны ли **meta boxes** и какие поля
   - Нужна ли **таксономия** (см. шаг 3б)
   - Нужны ли **JS-библиотеки** (flatpickr, select2, sortable, gallery — см. шаг 3в)

---

## Шаг 3: Выбор схемы шаблонов

### Схема 1 — Классическая (стандартный loop + делегирование)

**Когда:** CPT показывает записи в обычной сетке без кастомной логики.

| Файл | Путь | Назначение |
|------|------|------------|
| `archive-{slug}.php` | `./archive-{slug}.php` | Redux + loop + `get_template_part` карточек |
| `templates/archives/{slug}/{slug}_1.php` | `./templates/archives/{slug}/{slug}_1.php` | Обёртка одной карточки |
| `single-{slug}.php` | `./single-{slug}.php` | `require_once get_template_directory() . '/single.php'` |
| `templates/singles/{slug}/default.php` | `./templates/singles/{slug}/default.php` | Реальный контент single |

Примеры: `staff`, `vacancies`, `testimonials`.

---

### Схема 2 — Прямая (специфичный контент, кастомный запрос)

**Когда:** accordion, карта, фильтры, группировка по таксономии — данные не через стандартный loop.

| Файл | Путь | Назначение |
|------|------|------------|
| `archive-{slug}.php` | `./archive-{slug}.php` | Полная разметка: `get_header()` → `get_pageheader()` → `WP_Query` → `get_footer()` |
| `single-{slug}.php` | `./single-{slug}.php` | Полная разметка с `get_post_meta` |

Примеры: `faq` (accordion), `offices` (Yandex Maps), `legal`.

---

## Шаг 3б: Таксономии

Предложи на выбор если не указано явно:

| Вариант | Slug | Тип |
|---------|------|-----|
| Категории | `{slug}_category` | `hierarchical: true` |
| Теги | `{slug}_tag` | `hierarchical: false` |
| Обе | — | — |
| Без таксономии | — | — |

Дождись ответа перед планом.

---

## Шаг 3в: JS-библиотеки

Спроси явно: нужны ли flatpickr, select2, sortable, gallery, другое?

Если да — enqueue только на страницах этого CPT через `admin_enqueue_scripts` + проверку `$screen->post_type`.

> Подключать из локальных файлов `../PARENT_SLUG/dist/vendor/`, не CDN.

---

## Шаг 4: План

**CPT:** `{name}` (`{slug}`)
**Схема:** Классическая / Прямая — объясни почему

Все файлы создаются в **дочерней теме** (`./`):

| Файл | Полный путь |
|------|-------------|
| `cpt-{slug}.php` | `./functions/cpt/cpt-{slug}.php` |
| `functions.php` | `./functions.php` — добавить `require_once` |
| + файлы шаблонов по схеме | `./templates/...` или `./archive-*.php` |

**Дождись подтверждения пользователя.**

---

## Шаг 5: Реализация

### 5.1 `functions/cpt/cpt-{slug}.php`

Text domain из `style.css` (`TEXT_DOMAIN`). Используй `get_stylesheet_directory()` — дочерняя тема.

**Именование:**

| Элемент | Паттерн |
|---------|---------|
| Регистрация CPT | `cptui_register_my_cpts_{slug}()` |
| Регистрация таксономии | `cptui_register_my_taxes_{slug}_category()` |
| Meta box ID | `cw_{slug}_{box_name}` |
| Callback | `cw_{slug}_{box_name}_callback()` |
| Сохранение | `cw_{slug}_save_{box_name}()` |
| Meta keys | `_{slug}_{field}` |
| Admin колонки | `cw_{slug}_add_admin_columns()` |

```php
<?php
/**
 * CPT: {Name}
 */

function cptui_register_my_cpts_{slug}() {
    $labels = [
        'name'          => esc_html__( '{Names}', 'TEXT_DOMAIN' ),
        'singular_name' => esc_html__( '{Name}', 'TEXT_DOMAIN' ),
        'add_new'       => esc_html__( 'Add New', 'TEXT_DOMAIN' ),
        'add_new_item'  => esc_html__( 'Add New {Name}', 'TEXT_DOMAIN' ),
        'edit_item'     => esc_html__( 'Edit {Name}', 'TEXT_DOMAIN' ),
        'all_items'     => esc_html__( 'All {Names}', 'TEXT_DOMAIN' ),
        'search_items'  => esc_html__( 'Search {Names}', 'TEXT_DOMAIN' ),
        'not_found'     => esc_html__( 'No {names} found.', 'TEXT_DOMAIN' ),
    ];

    $args = [
        'label'               => esc_html__( '{Name}', 'TEXT_DOMAIN' ),
        'labels'              => $labels,
        'public'              => true,           // false = структурный
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_rest'        => true,
        'has_archive'         => true,
        'rewrite'             => [ 'slug' => '{slug}', 'with_front' => true ],
        'supports'            => [ 'title', 'editor', 'thumbnail' ], // по ответам Блока А
        'menu_icon'           => 'dashicons-portfolio',
        'hierarchical'        => false,
        'exclude_from_search' => false,
    ];

    register_post_type( '{slug}', $args );
}
add_action( 'init', 'cptui_register_my_cpts_{slug}' );
```

**Если структурный:**
```php
add_action( 'template_redirect', function () {
    if ( is_singular( '{slug}' ) || is_post_type_archive( '{slug}' ) ) {
        global $wp_query;
        $wp_query->set_404();
        status_header( 404 );
    }
} );
```

**Если классический редактор:**
```php
add_filter( 'use_block_editor_for_post_type', function( $use, $post_type ) {
    if ( $post_type === '{slug}' ) { return false; }
    return $use;
}, 10, 2 );
```

**Таксономия:**
```php
function cptui_register_my_taxes_{slug}_category() {
    $labels = [
        'name'          => esc_html__( '{Name} Categories', 'TEXT_DOMAIN' ),
        'singular_name' => esc_html__( '{Name} Category', 'TEXT_DOMAIN' ),
        'all_items'     => esc_html__( 'All Categories', 'TEXT_DOMAIN' ),
        'add_new_item'  => esc_html__( 'Add New Category', 'TEXT_DOMAIN' ),
        'not_found'     => esc_html__( 'No categories found.', 'TEXT_DOMAIN' ),
    ];
    $args = [
        'labels'            => $labels,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'rewrite'           => [ 'slug' => '{slug}/category', 'with_front' => true ],
        'show_admin_column' => true,
    ];
    register_taxonomy( '{slug}_category', [ '{slug}' ], $args );
}
add_action( 'init', 'cptui_register_my_taxes_{slug}_category' );
```

**Meta boxes — 4 обязательные проверки:**
```php
function cw_{slug}_add_meta_boxes() {
    add_meta_box( 'cw_{slug}_info', __( 'Info', 'TEXT_DOMAIN' ),
        'cw_{slug}_info_callback', '{slug}', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'cw_{slug}_add_meta_boxes' );

function cw_{slug}_info_callback( $post ) {
    wp_nonce_field( 'cw_{slug}_info_save', 'cw_{slug}_info_nonce' );
    $value = get_post_meta( $post->ID, '_{slug}_field', true );
    ?>
    <table class="form-table">
        <tr>
            <th><label for="{slug}_field"><?php esc_html_e( 'Field', 'TEXT_DOMAIN' ); ?></label></th>
            <td><input type="text" id="{slug}_field" name="{slug}_field"
                       value="<?php echo esc_attr( $value ); ?>" class="regular-text" /></td>
        </tr>
    </table>
    <?php
}

function cw_{slug}_save_info( $post_id ) {
    if ( ! isset( $_POST['cw_{slug}_info_nonce'] ) ||
         ! wp_verify_nonce( $_POST['cw_{slug}_info_nonce'], 'cw_{slug}_info_save' ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
    if ( get_post_type( $post_id ) !== '{slug}' ) { return; }

    if ( isset( $_POST['{slug}_field'] ) ) {
        update_post_meta( $post_id, '_{slug}_field',
            sanitize_text_field( wp_unslash( $_POST['{slug}_field'] ) ) );
    }
}
add_action( 'save_post', 'cw_{slug}_save_info' );
```

**Admin columns:**
```php
function cw_{slug}_add_admin_columns( $columns ) {
    return [
        'cb'            => $columns['cb'],
        'title'         => $columns['title'],
        '_{slug}_field' => esc_html__( 'Field', 'TEXT_DOMAIN' ),
        'date'          => $columns['date'],
    ];
}
add_filter( 'manage_{slug}_posts_columns', 'cw_{slug}_add_admin_columns' );

function cw_{slug}_fill_admin_columns( $column, $post_id ) {
    if ( $column === '_{slug}_field' ) {
        echo esc_html( get_post_meta( $post_id, '_{slug}_field', true ) );
    }
}
add_action( 'manage_{slug}_posts_custom_column', 'cw_{slug}_fill_admin_columns', 10, 2 );
```

---

### 5.2 `require_once` в `functions.php` дочерней темы

```php
require_once get_stylesheet_directory() . '/functions/cpt/cpt-{slug}.php';
```

> `get_stylesheet_directory()` — всегда дочерняя тема.

**JS-библиотеки — только для этого CPT:**
```php
add_action( 'admin_enqueue_scripts', 'cw_{slug}_admin_enqueue' );
function cw_{slug}_admin_enqueue() {
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== '{slug}' ) { return; }

    // flatpickr
    wp_enqueue_style( 'flatpickr',
        get_template_directory_uri() . '/dist/vendor/flatpickr/flatpickr.min.css', [], '4.6.13' );
    wp_enqueue_script( 'flatpickr',
        get_template_directory_uri() . '/dist/vendor/flatpickr/flatpickr.min.js', [], '4.6.13', true );
    wp_add_inline_script( 'flatpickr', 'flatpickr(".{slug}-datepicker", { dateFormat: "Y-m-d" });' );
}
```

---

### 5.3 Шаблоны — Схема 1 (классическая)

#### `./archive-{slug}.php`

```php
<?php
get_header();
get_pageheader();

global $opt_name;
$templateloop  = Redux::get_option( $opt_name, 'archive_template_select_{slug}' ) ?: '{slug}_1';
$sidebar_pos   = Redux::get_option( $opt_name, 'sidebar_position_archive_{slug}' );
$content_class = ( $sidebar_pos === 'none' ) ? 'col-12 py-14' : 'col-xl-9 pt-14';
?>

<?php if ( have_posts() ) : ?>
<section id="content-wrapper" class="wrapper bg-light">
    <div class="container">
        <div class="row">
            <?php get_sidebar( 'left' ); ?>
            <div class="<?php echo esc_attr( $content_class ); ?>">
                <div class="grid mb-5">
                    <div class="row isotope g-3">
                        <?php while ( have_posts() ) : the_post();
                            get_template_part( "templates/archives/{slug}/{$templateloop}" );
                        endwhile; ?>
                    </div>
                </div>
                <?php codeweber_posts_pagination(); ?>
            </div>
            <?php get_sidebar( 'right' ); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
```

#### `./templates/archives/{slug}/{slug}_1.php`

```php
<?php
$post_id   = absint( get_the_ID() );
$card_html = cw_render_post_card( get_post(), 'default', [], [ 'image_size' => 'medium' ] );
if ( empty( $card_html ) ) { return; }
?>
<div id="<?php echo esc_attr( $post_id ); ?>" class="item col-md-6 col-xl-4">
    <?php echo $card_html; ?>
</div>
```

#### `./single-{slug}.php`

```php
<?php
require_once get_template_directory() . '/single.php';
```

#### `./templates/singles/{slug}/default.php`

Включай секции по ответам Блока Б:

```php
<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$field = get_post_meta( get_the_ID(), '_{slug}_field', true );
?>
<section id="post-<?php the_ID(); ?>" <?php post_class( '{slug} single' ); ?>>

    <?php if ( has_post_thumbnail() ) : // [А: изображение] ?>
        <?php the_post_thumbnail( 'large', [ 'class' => 'img-fluid rounded mb-6' ] ); ?>
    <?php endif; ?>

    <?php if ( ! empty( $field ) ) : ?>
        <p class="lead mb-4"><?php echo esc_html( $field ); ?></p>
    <?php endif; ?>

    <div class="entry-content mb-8">
        <?php the_content(); // [А: editor] ?>
    </div>

    <?php // [Б: поделиться] ?>
    <div class="share-buttons d-flex gap-2 mb-8">
        <?php $url = urlencode( get_permalink() ); $title = urlencode( get_the_title() ); ?>
        <a href="https://vk.com/share.php?url=<?php echo $url; ?>" target="_blank" rel="noopener"
           class="btn btn-sm btn-outline-primary">VK</a>
        <a href="https://t.me/share/url?url=<?php echo $url; ?>&text=<?php echo $title; ?>" target="_blank" rel="noopener"
           class="btn btn-sm btn-outline-primary">Telegram</a>
        <a href="https://wa.me/?text=<?php echo $title; ?>%20<?php echo $url; ?>" target="_blank" rel="noopener"
           class="btn btn-sm btn-outline-primary">WhatsApp</a>
        <button class="btn btn-sm btn-outline-secondary js-copy-link"
                data-url="<?php echo esc_attr( get_permalink() ); ?>">
            <?php esc_html_e( 'Copy link', 'TEXT_DOMAIN' ); ?>
        </button>
    </div>

    <?php // [Б: автор] ?>
    <div class="author-box d-flex align-items-center gap-4 p-4 bg-light rounded mb-8">
        <?php echo get_avatar( get_the_author_meta( 'ID' ), 72, '', '', [ 'class' => 'rounded-circle' ] ); ?>
        <div>
            <div class="fw-bold"><?php the_author(); ?></div>
            <div class="text-muted small"><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></div>
        </div>
    </div>

    <?php // [Б: рекомендуемые по {slug}_category] ?>
    <?php
    $terms = get_the_terms( get_the_ID(), '{slug}_category' );
    if ( $terms && ! is_wp_error( $terms ) ) :
        $related = new WP_Query( [
            'post_type'      => '{slug}',
            'posts_per_page' => 3,
            'post__not_in'   => [ get_the_ID() ],
            'post_status'    => 'publish',
            'tax_query'      => [ [ 'taxonomy' => '{slug}_category', 'field' => 'term_id',
                                    'terms' => wp_list_pluck( $terms, 'term_id' ) ] ],
        ] );
        if ( $related->have_posts() ) :
    ?>
        <h3 class="mb-4"><?php esc_html_e( 'Related', 'TEXT_DOMAIN' ); ?></h3>
        <div class="row g-4 mb-6">
            <?php while ( $related->have_posts() ) : $related->the_post(); ?>
                <div class="col-md-4"><?php echo cw_render_post_card( get_post(), 'default' ); ?></div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    <?php endif; endif; ?>

    <?php // [Б: комментарии] ?>
    <?php if ( comments_open() || get_comments_number() ) : ?>
        <?php comments_template(); ?>
    <?php endif; ?>

</section>
```

---

### 5.4 Шаблоны — Схема 2 (прямая)

#### `./archive-{slug}.php`

```php
<?php
get_header();
get_pageheader();

global $opt_name;
$sidebar_pos   = Redux::get_option( $opt_name, 'sidebar_position_archive_{slug}' );
$content_class = ( $sidebar_pos === 'none' ) ? 'col-12 py-14' : 'col-xl-9 pt-14';

$items = new WP_Query( [
    'post_type'      => '{slug}',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
] );
?>

<section id="content-wrapper" class="wrapper bg-light">
    <div class="container">
        <div class="row">
            <?php get_sidebar( 'left' ); ?>
            <div class="<?php echo esc_attr( $content_class ); ?>">
                <?php if ( $items->have_posts() ) : ?>
                    <div class="row g-4 mb-5">
                    <?php while ( $items->have_posts() ) : $items->the_post();
                        $field = get_post_meta( get_the_ID(), '_{slug}_field', true );
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'medium', [ 'class' => 'card-img-top' ] ); ?>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php the_title(); ?></h5>
                                    <?php if ( ! empty( $field ) ) : ?>
                                        <p class="card-text"><?php echo esc_html( $field ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                <?php else : ?>
                    <p><?php esc_html_e( 'No entries found.', 'TEXT_DOMAIN' ); ?></p>
                <?php endif; ?>
            </div>
            <?php get_sidebar( 'right' ); ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
```

#### `./single-{slug}.php`

```php
<?php
get_header();

while ( have_posts() ) : the_post();
    get_pageheader();

    global $opt_name;
    $sidebar_pos   = Redux::get_option( $opt_name, 'sidebar_position_single_{slug}' );
    $content_class = ( $sidebar_pos === 'none' ) ? 'col-12 py-14' : 'col-md-8 py-14';
    $field = get_post_meta( get_the_ID(), '_{slug}_field', true );
?>
<section class="wrapper">
    <div class="container">
        <div class="row gx-lg-8 gx-xl-12">
            <?php get_sidebar( 'left' ); ?>
            <div id="article-wrapper" class="<?php echo esc_attr( $content_class ); ?>">
                <h1 class="display-4 mb-6"><?php the_title(); ?></h1>
                <?php if ( has_post_thumbnail() ) : ?>
                    <?php the_post_thumbnail( 'large', [ 'class' => 'img-fluid mb-6' ] ); ?>
                <?php endif; ?>
                <?php if ( ! empty( $field ) ) : ?>
                    <p class="lead mb-4"><?php echo esc_html( $field ); ?></p>
                <?php endif; ?>
                <div class="entry-content"><?php the_content(); ?></div>
            </div>
            <?php get_sidebar( 'right' ); ?>
        </div>
    </div>
</section>
<?php endwhile; ?>
<?php get_footer(); ?>
```

---

## Шаг 6: Отчёт

- Схема и обоснование
- Список всех файлов с полными путями
- URL: `/wp-admin/edit.php?post_type={slug}`
- Что проверить вручную

---

## Шаг 7: Сброс постоянных ссылок

```bash
wp rewrite flush
```

---

## Шаг 8: Тестирование

1. `npm run build` из `../PARENT_SLUG/` (parent тема — там Gulp)
2. Проверь `cpt-{slug}.php`:
   - 4 проверки в `save_post`: nonce, `current_user_can`, `DOING_AUTOSAVE`, `get_post_type`
   - Все `$_POST` санитизированы через `sanitize_text_field( wp_unslash(...) )`
   - Все выводы эскейпированы
   - Text domain везде совпадает со значением из `style.css`
3. Схема 2: `wp_reset_postdata()` после кастомного `WP_Query`

---

## Шаг 9: Обновление переводов

Файлы: `./languages/{TEXT_DOMAIN}.pot` и `./languages/ru_RU.po`

Добавь все новые строки из созданных файлов:

```po
# В {TEXT_DOMAIN}.pot — source строки (msgstr пустой)
msgid "{Names}"
msgstr ""

# В ru_RU.po — с переводом
msgid "{Names}"
msgstr "{Названия на русском}"
```

Скомпилировать: `wp i18n make-mo languages/ru_RU.po`

---

## Шаг 10: Коммит

```
feat: add CPT {slug} ({Name})
```

Включи все файлы и языковые файлы.
