# Shop PJAX — Документация

**Feature:** PJAX-навигация на страницах магазина (фильтры, сортировка, переключение колонок/страниц)
**Status:** Implemented
**Date:** 2026-03-17

---

## Принцип работы

```text
1. Пользователь кликает .pjax-link или меняет select.orderby
2. JS перехватывает событие, делает fetch(url, { headers: { 'X-PJAX': 'true' } })
3. PHP в archive-product.php детектирует заголовок X-PJAX
4. PHP пропускает get_header()/get_footer(), отдаёт содержимое #shop-pjax-wrapper
5. JS парсит ответ, читает data-page-title → обновляет document.title
6. JS заменяет innerHTML контейнера новым контентом
7. JS вызывает history.pushState(url)
8. JS ре-инициализирует Isotope в новом контенте
9. JS плавно скроллит к верху контейнера
```

**Вдохновлено:** WoodMart theme — они тоже заменяют большой контейнер целиком
(`.main-page-wrapper` у них, `#shop-pjax-wrapper` у нас).

---

## Зона PJAX: #shop-pjax-wrapper

Контейнер, который заменяется при каждом PJAX-переходе:

```text
#shop-pjax-wrapper  [data-page-title="..."]
├── get_pageheader()          ← page header (заголовок + крошки)
│                               рендерится или нет — решает сам через Redux
│                               при смене шаблона pageheader — работает автоматически
│
└── section.wrapper.bg-light
    └── .container
        └── .row.gy-10
            ├── .col-lg-9.order-lg-2   ← товары, переключатели, сортировка
            └── aside.col-lg-3.sidebar ← WooCommerce widgets (фильтры и т.д.)
```

`get_header()` / `get_footer()` — за пределами зоны, рендерятся один раз при полной загрузке.

**Ключевое преимущество:** если page header отключён в Redux или сменён шаблон —
PJAX продолжает работать без изменений в JS.

---

## Файлы

| Файл | Роль |
| --- | --- |
| `woocommerce/archive-product.php` | PJAX-детектор, структура `#shop-pjax-wrapper` |
| `src/assets/js/shop-pjax.js` | PJAX-логика: fetch, замена, history, title, Isotope |
| `src/assets/scss/theme/_projects.scss` | Loading-анимация (`#shop-pjax-wrapper.shop-pjax-loading`) |
| `functions/woocommerce.php` | Подключение shop-pjax.js + shortcode `[cw_shop_categories]` |

---

## archive-product.php: структура

### PJAX-детектор

```php
$is_pjax = ! empty( $_SERVER['HTTP_X_PJAX'] );
```

### Рендер

```php
if ( ! $is_pjax ) {
    get_header();      // только для полного запроса
}
?>

<div id="shop-pjax-wrapper" data-page-title="<?php echo esc_attr( wp_get_document_title() ); ?>">
    <?php get_pageheader(); ?>  <!-- всегда, внутри зоны -->

    <!-- section + row + товары + сайдбар — всегда -->

</div>

<?php if ( ! $is_pjax ) {
    get_footer();
}
```

`$is_pjax` управляет **только** `get_header()`/`get_footer()`. Весь контент всегда рендерится.

---

## shop-pjax.js: ключевые части

### Константы

```javascript
var CONTAINER_ID  = 'shop-pjax-wrapper';
var LOADING_CLASS = 'shop-pjax-loading';
var SPINNER_CLASS = 'shop-pjax-spinner';
```

### Замена контента + обновление title

```javascript
.then( function ( html ) {
    var tmp = document.createElement( 'div' );
    tmp.innerHTML = html;
    var newContainer = tmp.firstElementChild;

    container.innerHTML = newContainer.innerHTML;

    var newTitle = newContainer.getAttribute( 'data-page-title' );
    if ( newTitle ) {
        container.setAttribute( 'data-page-title', newTitle );
        document.title = newTitle;
    }

    history.pushState( { pjax: true, url: url }, document.title, url );
    initIsotope( container );
} )
```

### Перехват сортировки WooCommerce

```javascript
document.addEventListener( 'change', function ( e ) {
    var select = e.target.closest( 'select.orderby' );
    if ( ! select ) return;
    e.stopPropagation(); // не даём WC вызвать form.submit()
    var form = select.closest( 'form' );
    var url = new URL( form.action || window.location.href );
    url.search = new URLSearchParams( new FormData( form ) ).toString();
    pjaxLoad( url.toString() );
}, true ); // capture phase — раньше jQuery-обработчика WC
```

`url.search = ...` (не конкатенация!) — гарантирует отсутствие дублированных `?` в URL.

### Класс .pjax-link

Любая ссылка с классом `pjax-link` автоматически работает через PJAX:

```php
<!-- В archive-product.php -->
class="shop-per-row-btn pjax-link<?php echo $per_row === $cols ? ' active' : ''; ?>"
class="shop-per-page-btn pjax-link<?php echo $per_page === $count ? ' active' : ''; ?>"

<!-- В пагинации -->
<!-- WooCommerce pagination рендерится внутри зоны → ссылки автоматически .pjax-link? -->
<!-- Нет — пагинация WC не имеет класса. Добавить через filter если нужно. -->
```

---

## Redux-настройки магазина (woo_*)

Все настройки в разделе **Woocommerce → Archive**:

| Ключ Redux | Тип | Описание |
| --- | --- | --- |
| `archive_template_select_product` | select | Шаблон карточки товара |
| `woo_show_archive_title` | switch | Показывать h2-заголовок архива над сеткой |
| `woo_shop_load_more` | button_set | Навигация: `pagination` / `load_more` / `both` |
| `woo_show_per_page` | switch | Показывать переключатель per_page |
| `woo_per_page_values` | text | Значения per_page через запятую (напр. `12,24,48`) |
| `woo_show_per_row` | switch | Показывать переключатель колонок |
| `woo_per_row_values` | checkbox | Доступные варианты колонок (2 / 3 / 4) |
| `woo_show_ordering` | switch | Показывать сортировку |
| `woo_ordering_options` | checkbox | Доступные опции сортировки |

---

## Шорткод [cw_shop_categories]

Выводит кнопки-фильтры по категориям магазина. Ссылки имеют класс `pjax-link`.

```text
[cw_shop_categories]
[cw_shop_categories style="btn-outline-primary" active_style="btn-primary"]
[cw_shop_categories parent="5" hide_empty="0" show_all="0"]
```

Атрибуты: `hide_empty`, `parent`, `orderby`, `order`, `style`, `active_style`, `show_all`.

**Важно:** шорткод рендерится вне `#shop-pjax-wrapper` (если размещён в контенте страницы).
Активное состояние кнопок не обновляется автоматически при PJAX-переходе.
Для автообновления — разместить в сайдбаре магазина (попадёт внутрь зоны).

---

## Известные особенности

### woocommerce_catalog_orderby — двойное использование

WooCommerce использует фильтр `woocommerce_catalog_orderby` и для рендера дропдауна,
и для **валидации** параметра `orderby` в `WC_Query::get_orderby()`.

Если применить фильтр глобально (например убрать `price-desc`) — WC_Query будет
игнорировать `?orderby=price-desc` и откатываться к `menu_order`.

**Решение:** фильтр применяется локально — только вокруг `woocommerce_catalog_ordering()`,
после того как WC_Query уже выполнен:

```php
add_filter( 'woocommerce_catalog_orderby', $_cw_ordering_filter, 999 );
woocommerce_catalog_ordering();
remove_filter( 'woocommerce_catalog_orderby', $_cw_ordering_filter, 999 );
```

### wp_wc_product_meta_lookup

Сортировка по цене использует таблицу `wp_wc_product_meta_lookup`, а не `_price` мету напрямую.
Если таблица не заполнена — ценовая сортировка не работает.

```bash
wp wc tool run regenerate_product_lookup_tables --user=admin
```
