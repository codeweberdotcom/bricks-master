# Child Theme — Правила для AI

Правила принятия решений при работе с кодом **в дочерней теме**. Читать перед любыми задачами в дочерней теме.

> Общая структура и настройка дочерней темы — в [CHILD_THEME_GUIDE.md](CHILD_THEME_GUIDE.md).

---

## Главное правило: что куда добавлять

| Задача | Где делать |
|--------|-----------|
| Новый CPT только для этого сайта | Дочерняя тема (`functions/cpt/cpt-*.php`) |
| Изменить поведение существующего CPT из родителя | Фильтр `register_post_type_args` в дочерней теме |
| Шаблон archive/single для нового CPT | Корень дочерней темы (`archive-*.php`, `single-*.php`) |
| Переопределить шаблон из родительской темы | Дублировать путь в дочерней (см. ниже) |
| Переопределить WooCommerce-шаблон | `woocommerce/` в дочерней теме |
| PHP-утилиты, хелперы, метабоксы | `includes/` в дочерней теме |
| Кастомный Gutenberg-блок для этого сайта | `blocks/<name>/` в дочерней теме |
| Изменить настройку в родителе | Фильтр в `functions.php` дочерней, **не трогать файлы родителя** |

**Никогда не изменять файлы родительской темы (`codeweber/`)** ради задачи дочерней темы.

---

## Пути и функции

В дочерней теме **всегда** использовать:

```php
get_stylesheet_directory()      // путь к файлам дочерней темы
get_stylesheet_directory_uri()  // URL к файлам дочерней темы
```

`get_template_directory()` — это путь к **родительской** теме. Использовать только когда нужно явно обратиться к файлу родителя.

---

## CPT в дочерней теме

### Структура файлов

```
my-child-theme/
├── functions/
│   └── cpt/
│       ├── cpt-partners.php    ← регистрация CPT
│       └── cpt-awards.php
├── archive-partners.php        ← шаблон архива
├── single-partners.php         ← шаблон одиночной записи
└── functions.php               ← подключает cpt-*.php
```

### Регистрация CPT (`functions/cpt/cpt-partners.php`)

Паттерн полностью идентичен [CPT_HOW_TO_ADD.md](../cpt/CPT_HOW_TO_ADD.md), кроме одной детали: функции именовать с префиксом дочерней темы.

```php
<?php

function horizons_register_cpt_partners() {
    $labels = [
        'name'          => esc_html__( 'Partners', 'horizons' ),
        'singular_name' => esc_html__( 'Partner', 'horizons' ),
        // ...
    ];

    $args = [
        'label'             => esc_html__( 'Partners', 'horizons' ),
        'labels'            => $labels,
        'public'            => true,
        'publicly_queryable'=> true,
        'has_archive'       => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'rewrite'           => [ 'slug' => 'partners', 'with_front' => true ],
        'supports'          => [ 'title', 'editor', 'thumbnail', 'revisions' ],
        'capability_type'   => 'post',
        'map_meta_cap'      => true,
        'hierarchical'      => false,
        'can_export'        => true,
        'show_in_graphql'   => false,
    ];

    register_post_type( 'partners', $args );
}
add_action( 'init', 'horizons_register_cpt_partners' );
```

### Подключение в `functions.php` дочерней темы

```php
// В functions.php дочерней темы — используем get_stylesheet_directory()
$child_includes = [
    '/functions/cpt/cpt-partners.php',
    '/functions/cpt/cpt-awards.php',
    '/includes/metaboxes.php',
    '/includes/add_image_sizes.php',
];

foreach ( $child_includes as $file ) {
    $path = get_stylesheet_directory() . $file;
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}
```

**Никогда не добавлять `require_once` в `functions.php` родительской темы** — это сломает обновления.

### Изображения для нового CPT

Создать файл `includes/add_image_sizes.php` в дочерней теме:

```php
<?php
add_action( 'after_setup_theme', function () {
    add_image_size( 'horizons_partners_card', 400, 400, true );
    add_image_size( 'horizons_awards_thumb', 600, 400, true );
} );
```

---

## Изменить поведение существующего CPT из родителя

**Не копировать и не редактировать** файлы `codeweber/functions/cpt/`. Использовать фильтры:

```php
// В functions.php дочерней темы

// Отключить архив для CPT из родителя
add_filter( 'register_post_type_args', function ( $args, $post_type ) {
    if ( $post_type === 'faq' ) {
        $args['has_archive']       = false;
        $args['publicly_queryable'] = false;
    }
    return $args;
}, 10, 2 );

// Изменить поведение таксономии
add_filter( 'register_taxonomy_args', function ( $args, $taxonomy ) {
    if ( $taxonomy === 'document_category' ) {
        $args['public']            = false;
        $args['publicly_queryable'] = false;
        $args['rewrite']           = false;
    }
    return $args;
}, 10, 2 );
```

---

## Шаблоны archive и single в дочерней теме

### Как WordPress ищет шаблон

```
Запрос к /partners/         → дочерняя/archive-partners.php → родительская/archive-partners.php
Запрос к /partners/john/    → дочерняя/single-partners.php  → родительская/single-partners.php → single.php
```

Дочерняя тема проверяется **всегда первой**.

---

### Новый archive для CPT дочерней темы

Создать `archive-{post_type}.php` в **корне дочерней темы**. Прямой WP_Query, без Redux-селектора шаблонов:

```php
<?php get_header(); ?>
<?php get_pageheader(); ?>

<section class="wrapper bg-white">
    <div class="container py-8 py-md-12">
        <?php
        $args = [
            'post_type'      => 'partners',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'ASC',
        ];
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) :
            echo '<div class="row g-4 isotope">';
            while ( $query->have_posts() ) : $query->the_post();
                // вёрстка карточки прямо здесь
                ?>
                <div class="col-md-6 col-xl-4">
                    <a href="<?php the_permalink(); ?>" class="d-block">
                        <?php the_post_thumbnail( 'codeweber_staff_800', [ 'class' => 'img-fluid w-100' ] ); ?>
                        <h3 class="mt-2"><?php the_title(); ?></h3>
                    </a>
                </div>
                <?php
            endwhile;
            echo '</div>';
            wp_reset_postdata();
        else :
            echo '<p>' . esc_html__( 'Nothing found.', 'codeweber' ) . '</p>';
        endif;
        ?>
        <?php codeweber_posts_pagination(); ?>
    </div>
</section>

<?php get_footer(); ?>
```

**Ключевые функции из родительской темы:**
- `get_pageheader()` — блок PageHeader (баннер + заголовок страницы)
- `codeweber_posts_pagination()` — пагинация с Bootstrap-стилями

---

### Новый single для CPT дочерней темы

**Вариант A — делегировать родительскому `single.php`** (рекомендуется):

Создать `single-{post_type}.php` в корне дочерней темы:

```php
<?php
// Делегируем universal single.php родителя.
// Он найдёт templates/singles/{post_type}/default.php — сначала в дочерней, потом в родительской.
require_once get_template_directory() . '/single.php';
```

Затем создать sub-шаблон контента `templates/singles/partners/default.php` в дочерней теме:

```php
<?php
// Sub-шаблон: только контент (без header/footer — они в single.php)
$content = get_the_content();
?>
<?php if ( $content ) : ?>
    <div class="mb-5"><?php echo $content; ?></div>
<?php endif; ?>

<?php
// Мета-поля:
$position = get_post_meta( get_the_ID(), '_partners_position', true );
if ( $position ) : ?>
    <p class="text-muted"><?php echo esc_html( $position ); ?></p>
<?php endif; ?>
```

**Вариант B — полностью самостоятельный шаблон** (когда нужен нестандартный layout):

```php
<?php get_header(); ?>
<?php get_pageheader(); ?>

<section class="wrapper bg-white">
    <div class="container py-10">
        <?php while ( have_posts() ) : the_post(); ?>
            <h1><?php the_title(); ?></h1>
            <?php the_content(); ?>
        <?php endwhile; ?>
    </div>
</section>

<?php get_footer(); ?>
```

---

### Переопределить существующий архив/single родителя

Правило: **путь в дочерней = путь в родительской**. WordPress берёт файл из дочерней первым.

```
Родитель: codeweber/archive-projects.php
Дочерняя: my-child/archive-projects.php   ← создать с таким же именем
```

---

### Создать новый вариант шаблона для Redux-селектора

Redux-селектор использует `get_template_part()` — он проверяет дочернюю тему первой. Чтобы добавить новый вариант шаблона:

```
Существующий: codeweber/templates/archives/staff/staff_1.php
Новый вариант: my-child/templates/archives/staff/staff_custom.php  ← создать здесь
```

После этого добавить опцию в Redux-селектор через фильтр в `functions.php` дочерней темы. Подробнее — [REDUX_OPTIONS.md](../settings/REDUX_OPTIONS.md).

---

### Переопределение через `get_template_part()`

Если родитель вызывает `get_template_part('templates/components/breadcrumb')`, создать `templates/components/breadcrumb.php` в дочерней теме — WordPress подхватит его автоматически.

---

## Переопределение WooCommerce шаблонов

### Приоритет поиска шаблонов

WooCommerce ищет шаблоны в таком порядке:

1. `my-child-theme/woocommerce/` ← **дочерняя тема**
2. `codeweber/woocommerce/`
3. `woocommerce/templates/` (плагин)

### Правило переопределения

Скопировать файл из `codeweber/woocommerce/` в `my-child/woocommerce/` с **тем же путём**.

```
Оригинал в родителе:     codeweber/woocommerce/single-product/price.php
Переопределение в child: my-child/woocommerce/single-product/price.php
```

Если родительская тема уже переопределила шаблон — брать за основу **её версию**, не оригинал плагина.

### Все переопределения в codeweber/woocommerce/

```
woocommerce/
├── archive-product.php              ← страница магазина (обёртка + сайдбар)
├── content-product.php              ← ДИСПЕТЧЕР карточки товара (!)
├── content-quick-view.php           ← quick view модальное окно
├── single-product.php               ← страница товара (галерея, цена, кнопка)
├── single-product-reviews.php       ← форма и список отзывов
├── single-product/
│   ├── price.php                    ← цена на странице товара
│   ├── rating.php                   ← рейтинг звёздами
│   ├── meta.php                     ← SKU, категории, теги
│   ├── short-description.php        ← короткое описание
│   ├── review.php                   ← одиночный отзыв
│   └── add-to-cart/
│       ├── simple.php               ← кнопка для простого товара
│       ├── variable.php             ← форма вариаций
│       └── variation-add-to-cart-button.php
├── loop/
│   ├── orderby.php                  ← сортировка в шапке магазина
│   ├── pagination.php               ← пагинация магазина
│   └── result-count.php             ← "Showing N of N results"
├── global/
│   └── quantity-input.php           ← поле количества товара
├── notices/
│   ├── error.php
│   ├── success.php
│   └── notice.php
├── myaccount/
│   ├── dashboard.php
│   ├── navigation.php
│   ├── orders.php, my-orders.php, view-order.php
│   ├── downloads.php
│   ├── my-address.php, payment-methods.php
│   └── form-*.php (login, edit-account, edit-address, lost-password, reset-password)
├── order/
│   ├── order-details.php
│   └── order-details-customer.php
└── emails/
    ├── email-header.php, email-footer.php, email-styles.php
    ├── admin-new-order.php, admin-cancelled-order.php, admin-failed-order.php
    ├── customer-*.php (processing, completed, cancelled, failed, on-hold, invoice, note, refunded)
    ├── plain/*.php                  ← текстовые версии писем
    └── block/*.php                  ← блочные версии писем
```

---

## Карточки товаров WooCommerce

### Как работает система карточек

```
Запрос страницы магазина
    → WooCommerce вызывает woocommerce/content-product.php (диспетчер)
        → Читает Redux: archive_template_select_product = "shop2"
            → get_template_part('templates/woocommerce/cards/shop2')
                → Дочерняя тема проверяется первой
```

### Расположение карточек

```
templates/woocommerce/
├── cards/
│   └── shop2.php    ← единственная карточка (используется по умолчанию)
└── filters/
    └── ...
```

### Переопределить существующую карточку в дочерней теме

```
Родитель: codeweber/templates/woocommerce/cards/shop2.php
Дочерняя: my-child/templates/woocommerce/cards/shop2.php   ← тот же путь
```

### Добавить новую карточку в дочерней теме

**Шаг 1.** Создать файл `templates/woocommerce/cards/mycard.php` в дочерней теме:

```php
<?php
defined( 'ABSPATH' ) || exit;
global $product;
if ( ! $product || ! $product->is_visible() ) return;

$product_id  = $product->get_id();
$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';
?>
<div id="product-<?php echo esc_attr( $product_id ); ?>" class="project item col">
    <figure class="<?php echo esc_attr( $card_radius ); ?> mb-4">
        <a href="<?php the_permalink(); ?>">
            <?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
        </a>
    </figure>
    <h2 class="h5">
        <a href="<?php the_permalink(); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
    </h2>
    <p><?php echo $product->get_price_html(); ?></p>
</div>
```

**Доступно в карточке:** `global $product` (WC_Product), стандартный WP loop уже запущен.

**Шаг 2а.** Добавить в Redux-селектор через фильтр в `functions.php` дочерней:

```php
add_filter( 'redux/options/redux_demo/field/archive_template_select_product/options', function ( $options ) {
    $options['mycard'] = 'My Card';
    return $options;
} );
```

**Шаг 2б.** ИЛИ переопределить диспетчер `woocommerce/content-product.php` в дочерней теме и жёстко прописать имя карточки:

```php
<?php
defined( 'ABSPATH' ) || exit;
global $product;
if ( ! $product || ! $product->is_visible() ) return;

get_template_part( 'templates/woocommerce/cards/mycard' );
```

### Что использовать из shop2.php как образец

В `shop2.php` есть готовые паттерны — брать оттуда:
- Значки Sale / New из Redux (`woo_badge_*` опции)
- Hover-изображение из галереи товара
- Wishlist-кнопка (`cw-wishlist-btn`)
- Quick view кнопка (`item-view`)
- AJAX добавление в корзину (`ajax_add_to_cart`)
- `$card_radius` из Redux
- Режим вишлиста (`$GLOBALS['cw_wishlist_render']`)

---

## Кастомные Gutenberg-блоки в дочерней теме

Блоки, специфичные для сайта, создаются в `blocks/` дочерней темы и регистрируются через `register_block_type()`.

### Структура

```
my-child-theme/
└── blocks/
    └── partners-grid/
        ├── block.json          ← метаданные блока
        ├── index.js            ← скомпилированный JS (из src)
        ├── index.asset.php     ← зависимости (генерирует @wordpress/scripts)
        ├── render.php          ← серверный рендер (для динамических блоков)
        └── style-index.css     ← стили фронта
```

### Регистрация в `functions.php`

```php
add_action( 'init', function () {
    $block_path = get_stylesheet_directory() . '/blocks/partners-grid';

    if ( ! file_exists( $block_path . '/block.json' ) ) {
        return;
    }
    if ( ! file_exists( $block_path . '/index.asset.php' ) ) {
        return;
    }

    register_block_type( $block_path );
}, 20 ); // priority 20 — после init родителя
```

### Namespace блока

Блоки дочерней темы должны иметь собственный namespace (не `codeweber-blocks/`):

```json
{
  "name": "horizons/partners-grid",
  "title": "Partners Grid",
  "category": "horizons-blocks"
}
```

### Категория блоков

Зарегистрировать отдельную категорию в `functions.php`:

```php
add_filter( 'block_categories_all', function ( $categories ) {
    $custom = [
        'slug'  => 'my-child-blocks',
        'title' => __( 'My Child Blocks', 'my-child' ),
    ];
    return array_merge( [ $custom ], $categories );
}, 10, 2 );
```

---

## Sidebar-виджеты по умолчанию для CPT

Родительская тема регистрирует **программные виджеты** для сайдбаров CPT через хук `codeweber_before_sidebar`. Они показываются автоматически на single-страницах, но скрываются, если пользователь добавил свои виджеты в область виджетов.

Подробнее — [SIDEBAR_WIDGETS.md](../components/SIDEBAR_WIDGETS.md).

### Отключить дефолтный виджет из дочерней темы

```php
// В functions.php дочерней темы
remove_action( 'codeweber_before_sidebar', 'codeweber_sidebar_widget_events' );
```

### Заменить виджет своим

```php
// Отключить родительский
remove_action( 'codeweber_before_sidebar', 'codeweber_sidebar_widget_events' );

// Добавить свой
add_action( 'codeweber_before_sidebar', 'horizons_sidebar_widget_events' );
function horizons_sidebar_widget_events( $sidebar_id ) {
    if ( $sidebar_id !== 'events' ) return;
    if ( is_active_sidebar( 'events' ) ) return;
    if ( ! is_singular( 'events' ) ) return;
    // ... свой рендер
}
```

### Добавить виджет для нового CPT дочерней темы

```php
add_action( 'codeweber_before_sidebar', 'horizons_sidebar_widget_partners' );
function horizons_sidebar_widget_partners( $sidebar_id ) {
    if ( $sidebar_id !== 'partners' ) return;
    if ( is_active_sidebar( 'partners' ) ) return;
    if ( ! is_singular( 'partners' ) ) return;
    // ... рендер виджета
}
```

---

## PHP-хелперы и утилиты

Все PHP-модули дочерней темы (метабоксы, хелперы, Ajax-обработчики) хранить в `includes/` и подключать циклом `foreach` из `functions.php`.

```
my-child-theme/
└── includes/
    ├── metaboxes.php           ← ACF / кастомные метаполя
    ├── add_image_sizes.php     ← add_image_size() для CPT дочерней
    ├── shortcodes.php          ← кастомные шорткоды
    └── ajax-handlers.php       ← wp_ajax_ обработчики
```

---

## Чеклист перед коммитом при работе в дочерней теме

- [ ] Все новые файлы — в директории дочерней темы, **не в `codeweber/`**
- [ ] Все `require_once` — в `functions.php` дочерней, через `get_stylesheet_directory()`
- [ ] Шаблоны переопределяют оригинал по **точному** совпадению пути
- [ ] Именование функций/хуков — с префиксом дочерней темы (e.g. `horizons_`)
- [ ] WooCommerce-шаблоны — в `woocommerce/` дочерней темы
- [ ] Блоки — в `blocks/` дочерней, namespace отличается от `codeweber-blocks/`
- [ ] Запустить сборку (`npm run build`) если изменялись ассеты

---

## Связанная документация

- [CHILD_THEME_GUIDE.md](CHILD_THEME_GUIDE.md) — настройка дочерней темы с нуля
- [CPT_HOW_TO_ADD.md](../cpt/CPT_HOW_TO_ADD.md) — полный цикл создания CPT
- [ARCHIVE_SINGLE_PATTERNS.md](../templates/ARCHIVE_SINGLE_PATTERNS.md) — паттерны archive/single шаблонов
- [POST_CARDS_SYSTEM.md](../templates/POST_CARDS_SYSTEM.md) — система карточек постов
- [HOOKS_REFERENCE.md](../api/HOOKS_REFERENCE.md) — доступные фильтры родительской темы
