# WooCommerce Cart Page

Страница корзины с Bootstrap 2-колоночным макетом и AJAX-удалением товаров без перезагрузки.

**Шаблон корзины:** `woocommerce/cart/cart.php`
**Шаблон итогов:** `woocommerce/cart/cart-totals.php`
**Кнопка оформления:** `woocommerce/cart/proceed-to-checkout-button.php`
**Пустая корзина:** `woocommerce/cart/cart-empty.php`
**JS (AJAX удаление):** `src/assets/js/woo-cart-offcanvas.js`
**SCSS:** `src/assets/scss/theme/_woo-cart.scss`

---

## Макет

```
.row.gx-md-8.gx-xl-12.gy-12
├── .col-lg-8   ← таблица товаров + форма промокода
└── .col-lg-4   ← woocommerce_cart_totals()
```

### Таблица товаров `table.woocommerce-cart-form__contents`

| Колонка | Содержимое |
|---------|-----------|
| `product-name` | `figure.rounded.w-17` (фото) + название + `dl.variation` |
| `product-price` | Цена в `p.price` |
| `product-quantity` | `woocommerce_quantity_input()` |
| `product-subtotal` | Подытог в `p.price` |
| `pe-0` | Иконка удаления `uil-trash-alt` с `data-product_id` |

Кнопка «Update cart» скрыта (`d-none`) — количество обновляется автоматически при изменении `qty` через AJAX WooCommerce.

### Промокод

Реализован как Bootstrap `form-floating.input-group` — поле ввода + кнопка «Применить» в одной строке.

---

## Order Summary (`cart-totals.php`)

- Заголовок: «Ваш заказ» (переведено в домене `codeweber`)
- Итого: «Итого» (переведено в домене `codeweber`)
- Таблица `.table.table-order` — строки с разделителями через CSS
- Кнопка «Перейти к оформлению» в обёртке `<div class="mt-4">` (без класса `wc-proceed-to-checkout`)

### Кнопка оформления (`proceed-to-checkout-button.php`)

```php
<a class="checkout-button btn btn-primary{$btn_shape} w-100 wc-forward has-ripple">
    Перейти к оформлению
</a>
```

Форма кнопки берётся из `Codeweber_Options::style('button')`.

---

## AJAX удаление на странице корзины

**Без перезагрузки страницы** — использует тот же `cw_remove_from_cart` AJAX-экшен что и offcanvas.

### JS-логика (в `woo-cart-offcanvas.js`)

```js
$(document).on('click', '.woocommerce-cart-form .pe-0 [data-product_id]', function(e) { ... })
```

1. Извлекает `remove_item` ключ из `href` через regex: `/[?&]remove_item=([a-f0-9]+)/`
2. Устанавливает строке `opacity: 0.4`
3. POST на `cw_remove_from_cart` с `cart_item_key`
4. При успехе:
   - Если `cart_count === 0` → `window.location.reload()` (показывает empty state)
   - Иначе: удаляет `<tr>`, обновляет badge, обновляет offcanvas HTML, заменяет `.cart_totals` через `replaceWith()`

### PHP: `cw_ajax_remove_from_cart()` возвращает

```json
{
  "cart_html": "...",
  "cart_count": 2,
  "cart_totals_html": "<div class=\"cart_totals\">...</div>"
}
```

`cart_totals_html` генерируется через `ob_start()` + `woocommerce_cart_totals()`.

---

## CSS (`_woo-cart.scss`)

### Атрибуты вариации

```scss
dl.variation {
  @extend .small;   // font-size 0.875em, line-height 1.5
  margin-bottom: 0;
  dt, dd { display: inline; }
  dd {
    margin-left: .25rem;
    &::after { content: '\A'; white-space: pre; }  // перенос между атрибутами
  }
}
```

### Таблица корзины

```scss
table.woocommerce-cart-form__contents {
  td { vertical-align: middle; padding: 1.2rem 0; }
  .product-price, .product-subtotal, .price,
  .woocommerce-Price-amount { white-space: nowrap; }
}
```

### Order Summary (`.cart_totals`)

```scss
.cart_totals .table-order td {
  border-bottom: 1px solid rgba(0,0,0,.09);
  padding: 0.85rem 0;
}
.cart_totals .table-order tr:last-child td { border-bottom: 0; }
```
