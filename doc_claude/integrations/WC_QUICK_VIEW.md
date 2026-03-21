# WooCommerce Quick View

Система быстрого просмотра товара: Bootstrap Modal с галереей Swiper, summary WooCommerce, поддержкой вариаций и свотчей.

---

## Файлы системы

| Файл | Назначение |
|------|-----------|
| `functions/woocommerce-quick-view.php` | AJAX-обработчик, modal container, enqueue JS |
| `woocommerce/content-quick-view.php` | Шаблон содержимого модала |
| `src/assets/js/woo-quick-view.js` | JS: клик, AJAX-загрузка, Swiper init, WC вариации |
| `src/assets/scss/theme/_woo-quick-view.scss` | Стили: skeleton, скролл правой колонки |

---

## Архитектура

```
Клик на .item-view[data-product-id]
  └─ woo-quick-view.js: loadProduct(id)
       ├─ Показывает skeleton-loader в modal → modal.show()
       └─ fetch GET admin-ajax.php?action=cw_quick_view&product_id=N
            └─ cw_quick_view_handler() (PHP)
                 ├─ setup_postdata($post)
                 ├─ get_template_part('woocommerce/content-quick-view')
                 └─ wp_send_json_success($html)
                      └─ JS: body.innerHTML = html
                           ├─ initSwiper(body)
                           └─ initVariationForm(body)
```

---

## PHP: `functions/woocommerce-quick-view.php`

### AJAX-обработчик

```php
add_action('wp_ajax_cw_quick_view',        'cw_quick_view_handler');
add_action('wp_ajax_nopriv_cw_quick_view', 'cw_quick_view_handler');
```

**GET-параметры:**
- `product_id` — ID товара (`absint`)

**Логика:**
1. Устанавливает глобальные `$post` и `$product`
2. Временно убирает хуки `wc_print_notices` и `single_sharing` (лишние для quick view)
3. Рендерит `woocommerce/content-quick-view.php` через `ob_start`
4. `wp_send_json_success($html)` — возвращает HTML-фрагмент
5. Восстанавливает хуки

### Modal container

Добавляется в `wp_footer` — только на WooCommerce-страницах и странице вишлиста:

```php
add_action('wp_footer', 'cw_quick_view_modal_container');
```

**Условие показа:**
```php
is_woocommerce() || is_shop() || is_product_category() || is_product_tag() || cw_is_wishlist_page()
```

**HTML-структура:**
```html
<div class="modal fade" id="cw-quick-view-modal" ...>
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content position-relative overflow-hidden">
      <button class="btn-close position-absolute top-0 end-0 m-3" style="z-index:5" data-bs-dismiss="modal">
      <div class="modal-body p-0" id="cw-quick-view-body">
        <!-- Заменяется JS-ом: skeleton → реальный контент -->
      </div>
    </div>
  </div>
</div>
```

### Enqueue JS

```php
add_action('wp_enqueue_scripts', 'cw_quick_view_enqueue', 35);
```

- **Handle:** `cw-quick-view`
- **Файл:** `dist/assets/js/woo-quick-view.js`
- **Зависимости:** `['jquery', 'wc-add-to-cart-variation']`
- **Версия:** `codeweber_asset_version($path)` (filemtime при WP_DEBUG)

**JS-объект локализации `cwQuickView`:**

| Ключ | Значение |
|------|---------|
| `ajaxUrl` | `admin_url('admin-ajax.php')` |
| `action` | `'cw_quick_view'` |
| `i18n.loading` | «Loading...» |
| `i18n.error` | «Failed to load product...» |

### Helper `cw_is_wishlist_page()`

```php
function cw_is_wishlist_page() {
    $opts    = get_option('redux_demo', []);
    $page_id = (int)($opts['wishlist_page'] ?? 0);
    return $page_id && is_page($page_id);
}
```

---

## Шаблон: `woocommerce/content-quick-view.php`

Использует глобальные `$post`, `$product` (установлены в обработчике).

**Структура:**

```html
<div class="row g-0">

  <!-- Левая колонка: Swiper-галерея -->
  <div class="col-md-6 bg-light">
    <div class="swiper-container dots-over"
         data-margin="0"
         data-dots="true|false"
         data-nav="true|false">
      <div class="swiper">
        <div class="swiper-wrapper">
          <div class="swiper-slide">
            <figure class="rounded m-0">
              <img ...>
            </figure>
          </div>
          <!-- ...остальные слайды -->
        </div>
      </div>
    </div>
  </div>

  <!-- Правая колонка: WooCommerce summary -->
  <div class="col-md-6 p-4 p-lg-5">
    <?php do_action('woocommerce_single_product_summary'); ?>
    <a href="..." class="btn btn-soft-primary btn-sm mt-3">View full details</a>
  </div>

</div>
```

**Поведение галереи:**
- `data-dots` и `data-nav` = `"true"` только если у товара есть галерея (> 1 изображения)
- Класс `dots-over` — точки пагинации накладываются поверх изображения (не под ним)
- Изображения: `woocommerce_single` размер, без `<a>`-обёрток

**Хуки WooCommerce в summary (порядок по умолчанию):**

| Приоритет | Шаблон |
|-----------|--------|
| 5 | Название (`woocommerce_template_single_title`) |
| 10 | Рейтинг (`woocommerce_template_single_rating`) |
| 10 | Цена (`woocommerce_template_single_price`) |
| 20 | Описание (`woocommerce_template_single_excerpt`) |
| 30 | Форма «В корзину» (`woocommerce_template_single_add_to_cart`) |
| 40 | Мета (категории, теги) (`woocommerce_template_single_meta`) |

Убраны из quick view: `wc_print_notices` (before_single_product) и `single_sharing` (50).

---

## JS: `src/assets/js/woo-quick-view.js`

Vanilla JS. jQuery используется только там, где требует WooCommerce API.

### Функции

#### `getModal()`

```js
function getModal() {
    var el = document.getElementById('cw-quick-view-modal');
    if (!el) return null;
    return bootstrap.Modal.getOrCreateInstance(el);
}
```

#### `initSwiper(container)`

Инициализирует Swiper для AJAX-загруженного контента. Повторяет логику `theme.swiperSlider()`, но только для переданного контейнера (чтобы не переинициализировать все слайдеры на странице).

Создаёт DOM `.swiper-controls > .swiper-navigation + .swiper-pagination` и передаёт их Swiper:

```js
new Swiper(swiperEl, {
    loop: false,
    slidesPerView: 1,
    grabCursor: true,
    navigation: { prevEl: prev, nextEl: next },
    pagination: { el: pagi, clickable: true },
    on: {
        beforeInit: function() {
            // Убирает nav/pagi если data-nav/data-dots !== 'true'
        }
    }
});
```

#### `initVariationForm(container)`

Инициализирует WooCommerce-вариации и кастомные свотчи для AJAX-загруженной формы:

```js
function initVariationForm(container) {
    var $form = jQuery(container).find('.variations_form');
    if (!$form.length) return;
    $form.wc_variation_form();                                  // WC API
    jQuery(document.body).trigger('cw_init_swatches', [$form]); // Свотчи
    $form.find('.variations select:eq(0)').trigger('change');   // Sync
}
```

#### `loadProduct(productId, triggerBtn)`

1. Рендерит детальный skeleton-loader в `#cw-quick-view-body`
2. Открывает модал (`modal.show()`)
3. Отправляет `fetch GET` к `admin-ajax.php`
4. При успехе: `body.innerHTML = html` → `initSwiper()` → `initVariationForm()`
5. Триггерит `wc_fragment_refresh` для обновления мини-корзины

#### Click delegation

```js
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.item-view[data-product-id]');
    if (!btn) return;
    e.preventDefault();
    loadProduct(btn.dataset.productId, btn);
});
```

### Skeleton-loader

При загрузке в `#cw-quick-view-body` вставляется детальный skeleton, имитирующий реальную структуру quick view:
- Левая колонка: `cw-skeleton-block` с `aspect-ratio:1/1`
- Правая колонка: блоки заголовка, рейтинга, цены, атрибутов, кнопки корзины, метаданных

---

## SCSS: `src/assets/scss/theme/_woo-quick-view.scss`

```scss
// Loading state
.item-view.cw-qv-loading {
    pointer-events: none;
    opacity: .5;
}

// Правая колонка скроллится независимо
#cw-quick-view-modal {
    .modal-body { overflow: hidden; }
    .row.g-0 {
        height: 100%;
        .col-md-6:last-child { overflow-y: auto; }
    }
}
```

**Паттерн независимого скролла правой колонки:**
- `modal-dialog-scrollable` делает весь `modal-body` скроллируемым — переопределяем `overflow: hidden`
- `row` растягивается на всю высоту тела модала (`height: 100%`)
- Только правая `.col-md-6:last-child` получает `overflow-y: auto`

---

## Свотчи в Quick View (`woo-swatches.js`)

Свотчи инициализируются через кастомное jQuery-событие `cw_init_swatches`:

```js
// В woo-swatches.js:
$(document.body).on('cw_init_swatches', function(e, $form) {
    initSwatchForm($form);
});
```

**Почему нужно событие:** `initSwatchForm()` — не публичная функция, вызвать её напрямую из другого скрипта нельзя. Событие на `document.body` — это публичный API для динамической инициализации свотчей (quick view, other AJAX widgets).

**Почему `woo-swatches.js` грузится на страницах каталога:**
До этой доработки скрипт грузился только на `is_product()`. Quick View открывается с каталога, поэтому условие расширено:

```php
// functions/woocommerce.php
if (!is_product() && !is_shop() && !is_product_category() && !is_product_tag() && !cw_is_wishlist_page()) {
    return;
}
```

---

## Глобальный фикс Swiper: click-through на disabled кнопку

**Проблема:** когда Swiper достигает последнего слайда, кнопка «вперёд» получает класс `swiper-button-disabled`. Swiper устанавливает на неё `pointer-events: none`, из-за чего клик проходит сквозь кнопку на изображение под ней.

**Решение** (в `src/assets/scss/theme/_carousel.scss`):

```scss
&.swiper-button-disabled {
    background: $swiper-arrow-bg-disabled;
    pointer-events: auto;   // Перехватывает клик
    cursor: default;         // Показывает что кнопка неактивна
}
```

Фикс глобальный — применяется ко всем Swiper-слайдерам в теме.

---

## Триггер кнопки Quick View

В карточке товара `templates/post-cards/product/shop2.php`:

```html
<a href="<?php the_permalink(); ?>"
   class="item-view"
   data-product-id="<?php echo esc_attr($product->get_id()); ?>">
  <!-- иконка -->
</a>
```

JS перехватывает клики по `.item-view[data-product-id]` через event delegation на `document`.

---

## Зависимости

| Зависимость | Откуда |
|------------|--------|
| `bootstrap.Modal` | `plugins.js` (тема) |
| `Swiper` | `plugins.js` (тема) |
| `jQuery` | WordPress core |
| `wc-add-to-cart-variation` | WooCommerce |
| `cw-woo-swatches` | `woo-swatches.js` (тема) |

---

## Связанные документы

- [WC_FILTERS.md](WC_FILTERS.md) — фильтры каталога
- [AJAX_FETCH_SYSTEM.md](../api/AJAX_FETCH_SYSTEM.md) — общая AJAX-архитектура темы
- [BUILD_SYSTEM.md](../development/BUILD_SYSTEM.md) — Gulp задачи для woo-quick-view.js
