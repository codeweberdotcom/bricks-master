# WooCommerce Offcanvas Cart

Выдвижная корзина (Bootstrap Offcanvas) с AJAX add-to-cart для архивов и страниц товара.

**PHP:** `functions/woocommerce-cart-offcanvas.php`
**JS:** `src/assets/js/woo-cart-offcanvas.js`
**SCSS:** `src/assets/scss/theme/_woo-cart-offcanvas.scss`
**Шаблон содержимого:** `templates/woocommerce/offcanvas-cart-items.php`
**Контейнер offcanvas:** рендерится в `wp_footer` → `cw_cart_offcanvas_container()`

---

## Архитектура

### Два источника добавления в корзину

| Контекст | Механизм |
|----------|----------|
| Архив товаров | Клик по `.ajax_add_to_cart` → наш AJAX `cw_add_to_cart` |
| Страница товара | Сабмит `form.cart` → наш AJAX `cw_add_to_cart` |

Встроенный WC AJAX (`wc-add-to-cart.js`) **отключён программно**:
```php
add_filter( 'pre_option_woocommerce_enable_ajax_add_to_cart', '__return_false' );
```

### WC cart fragments

Зарегистрированы два фрагмента (`woocommerce_add_to_cart_fragments`):

| CSS-селектор | Содержимое |
|--------------|-----------|
| `.cw-offcanvas-cart-inner` | Список товаров + кнопки оформления |
| `.badge-cart` | Счётчик товаров в шапке |

WC автоматически обновляет эти элементы в DOM при любом изменении корзины.

---

## AJAX-обработчики (PHP)

### `cw_add_to_cart` — добавление товара

```
wp_ajax_cw_add_to_cart
wp_ajax_nopriv_cw_add_to_cart
```

Читает из `$_POST`:
- `add-to-cart` или `product_id` — ID товара
- `quantity` — количество (min 1)
- `variation_id` — ID вариации (опционально)
- `attribute_*` — атрибуты вариации (опционально)

Возвращает `wp_send_json_success`:
```json
{
  "cart_html": "<div class=\"cw-offcanvas-cart-inner\">...</div>",
  "cart_count": 3,
  "cart_hash": "abc123"
}
```

### `cw_remove_from_cart` — удаление товара

```
wp_ajax_cw_remove_from_cart
wp_ajax_nopriv_cw_remove_from_cart
```

Читает `cart_item_key` из `$_POST`. Возвращает:

```json
{
  "cart_html": "...",
  "cart_count": 2,
  "cart_totals_html": "<div class=\"cart_totals\">...</div>"
}
```

`cart_totals_html` используется JS на странице корзины для замены блока `.cart_totals` без перезагрузки страницы. В offcanvas это поле игнорируется.

---

## Критическая ловушка: двойное добавление

### Проблема

`WC_Form_Handler::add_to_cart_action()` подключён к `wp_loaded` (priority 20) и обрабатывает `$_POST['add-to-cart']` на **любом** запросе, включая `admin-ajax.php`.

Когда наш JS отправляет POST:
```
action=cw_add_to_cart & add-to-cart=123 & quantity=1
```

Происходит следующая последовательность:
1. `wp_loaded` → WC добавляет товар (qty=1)
2. `wp_ajax_cw_add_to_cart` → наш код добавляет снова → WC мержит → **qty=2**

### Решение

Отцепляем WC-обработчик до его срабатывания (priority 1 < priority 20):

```php
add_action( 'wp_loaded', function() {
    if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
        return;
    }
    $our_actions = array( 'cw_add_to_cart', 'cw_remove_from_cart' );
    if ( isset( $_POST['action'] ) && in_array( $_POST['action'], $our_actions, true ) ) {
        remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );
    }
}, 1 );
```

**Это обязательно.** Без этого каждый клик добавляет товар дважды.

---

## Критическая ловушка: прелоад браузера

### Проблема

`href` кнопки «В корзину» в архиве содержал `?add-to-cart=ID`:
```php
$add_to_cart_url = $product->add_to_cart_url(); // → ?add-to-cart=2183
```

Chrome/браузер прелоадит ссылки при наведении → WooCommerce обрабатывает
`?add-to-cart=ID` как GET-запрос → товар добавляется (qty=1).
Затем JS отправляет наш AJAX → WC мержит → **qty=2**.

### Решение

В `templates/woocommerce/cards/shop2.php` используем URL товара вместо add-to-cart URL:

```php
// БЫЛО:
<a href="<?php echo esc_url( $add_to_cart_url ); ?>" class="item-cart ajax_add_to_cart" ...>

// СТАЛО:
<a href="<?php echo esc_url( $product_url ); ?>" class="item-cart ajax_add_to_cart" ...>
```

Теперь прелоад просто загружает страницу товара — без побочных эффектов.

---

## JS: логика offcanvas

**Файл:** `src/assets/js/woo-cart-offcanvas.js`

### Поведение при добавлении

1. Клик / сабмит формы
2. Немедленно: открыть offcanvas (`bootstrap.Offcanvas.show()`), показать существующие товары с оверлеем загрузки (`cw-cart-loading`)
3. AJAX в фоне
4. По ответу: `updateCartHtml()` + `updateBadge()` + `wc_fragment_refresh`

**Важно:** offcanvas открывается мгновенно, показывая актуальное содержимое корзины с индикатором загрузки — не скелетон.

### `updateCartHtml(html)`

Заменяет содержимое `.cw-offcanvas-cart-inner` через `innerHTML` (не `outerHTML`).
Причина: `outerHTML` оставлял HTML-комментарии вне элемента как соседние узлы DOM, которые накапливались при каждом обновлении.

### Архив: перехват `.ajax_add_to_cart`

Обработчик игнорирует кнопки внутри `form.cart` (они обрабатываются через сабмит формы):
```js
if ($(this).closest('form.cart').length) return;
```

Защита от двойного клика: `$btn.hasClass('loading')`.

### Single product: перехват `form.cart`

Сериализует всю форму + добавляет `action` и `nonce`. Кнопка блокируется (`disabled`) на время запроса.

---

## CSS-состояния

| Класс | Где | Эффект |
|-------|-----|--------|
| `.cw-cart-loading` | `.cw-offcanvas-cart-inner` | opacity 0.45 + спиннер по центру |
| `.loading` | `.single_add_to_cart_button` | Текст прозрачный, сохраняет primary-цвет кнопки, белый спиннер |
| `.loading` | `.item-cart` | Текст прозрачный, белый спиннер |
| `.cw-item-removing` | `.shopping-cart-item` | opacity 0.4 + спиннер, pointer-events none |

**Важно:** WooCommerce по умолчанию снижает opacity кнопки при `.loading`. Переопределяем:
```scss
.single_add_to_cart_button.loading {
  opacity: 1 !important;
  background-color: var(--bs-primary) !important;
}
```

---

## Вариации в корзине и offcanvas

Фильтр `woocommerce_get_item_data` → `cw_force_variation_data_in_cart()`.

**Проблема:** `wc_get_formatted_cart_item_data()` вызывает `wc_is_attribute_in_product_name()` и **пропускает** атрибуты вариации, если они уже включены в название товара (например «Браслет — Чёрный, Кожа»).

**Решение:** Фильтр принудительно добавляет все атрибуты в `$item_data` когда массив пустой:

```php
add_filter( 'woocommerce_get_item_data', 'cw_force_variation_data_in_cart', 10, 2 );

function cw_force_variation_data_in_cart( $item_data, $cart_item ) {
    if ( ! empty( $item_data ) ) return $item_data;
    if ( ! $cart_item['data']->is_type( 'variation' ) || empty( $cart_item['variation'] ) ) return $item_data;
    foreach ( $cart_item['variation'] as $name => $value ) {
        $taxonomy = str_replace( 'attribute_', '', sanitize_title( $name ) ); // pa_color
        if ( taxonomy_exists( $taxonomy ) ) {
            $term = get_term_by( 'slug', $value, $taxonomy );
            if ( $term && $term->name ) $value = $term->name; // slug → читаемое название
            $label = wc_attribute_label( $taxonomy );
        } else {
            $label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $cart_item['data'] );
        }
        $item_data[] = [ 'key' => $label, 'value' => $value ];
    }
    return $item_data;
}
```

Работает как в offcanvas, так и на странице корзины (`cart.php`) — `wc_get_formatted_cart_item_data()` используется в обоих местах.

---

## AJAX удаление на странице корзины

JS-обработчик в `woo-cart-offcanvas.js` (тот же файл, что и offcanvas):

```js
$(document).on('click', '.woocommerce-cart-form .pe-0 [data-product_id]', function(e) { ... })
```

- Извлекает ключ из `href` через `/[?&]remove_item=([a-f0-9]+)/`
- При `cart_count === 0` → `window.location.reload()` (показывает empty state)
- Иначе: удаляет `<tr>`, обновляет badge + offcanvas HTML + `.cart_totals`

Подробнее: [`doc_claude/integrations/WC_CART.md`](WC_CART.md).

---

## Ripple на кнопках offcanvas

Кнопкам в `templates/woocommerce/offcanvas-cart-items.php` добавлен класс `has-ripple`.

**Проблема:** WC fragment refresh заменяет HTML после инициализации `theme.js` → ripple не инициализируется на новых кнопках.

**Решение — два места:**

```js
// 1. После каждого updateCartHtml()
function updateCartHtml(html) {
    // ... обновляем DOM ...
    if (typeof custom !== 'undefined' && typeof custom.rippleEffect === 'function') {
        custom.rippleEffect();
    }
}

// 2. После WC fragment refresh (первичная загрузка и обновления)
$(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function() {
    if (typeof custom !== 'undefined' && typeof custom.rippleEffect === 'function') {
        custom.rippleEffect();
    }
});
```

---

## Swiper на странице товара («Вам может понравится»)

Карточки товаров в Swiper конфликтовали с Bootstrap col-классами. Решение — флаг `$GLOBALS['cw_swiper_loop']`:

```php
// woocommerce/single-product.php — перед циклом related:
$GLOBALS['cw_swiper_loop'] = true;
// ... цикл ...
unset( $GLOBALS['cw_swiper_loop'] );
```

В `templates/woocommerce/cards/shop2.php`:
```php
$cw_col = ! empty( $GLOBALS['cw_swiper_loop'] ) ? 'w-100' : 'col-12 col-sm-6 col-lg-4';
```
