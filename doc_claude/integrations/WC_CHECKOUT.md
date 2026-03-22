# WooCommerce Checkout Page

Страница оформления заказа с Bootstrap `form-floating` для полей формы и стилизацией select2 для полей страны/региона.

**PHP-фильтры:** `functions/woocommerce-checkout.php`
**Шаблоны:** `woocommerce/checkout/`
**SCSS:** `src/assets/scss/theme/_woo-cart.scss`

---

## Шаблоны

| Файл | Переопределяет | Что изменено |
|------|---------------|-------------|
| `woocommerce/checkout/form-checkout.php` | WC default | Заголовок «Ваш заказ» |
| `woocommerce/checkout/review-order.php` | WC default | Bootstrap карточки товаров, `table.table-order`, «Итого» |
| `woocommerce/checkout/payment.php` | WC default | Кнопка «Оформить заказ» → `btn btn-primary w-100 has-ripple` |

### `review-order.php` — сводка заказа

Товары в виде карточек `.shopping-cart-item` (аналогично offcanvas):
```html
<div class="shopping-cart mb-7">
  <div class="shopping-cart-item d-flex justify-content-between mb-4">
    <figure class="rounded w-17"><!-- фото --></figure>
    <div class="w-100 ms-4"><!-- название, кол-во, вариации --></div>
    <p class="price fs-sm mb-0"><!-- цена --></p>
  </div>
</div>
<table class="table table-order"><!-- subtotal, купоны, доставка, итого --></table>
```

---

## PHP-фильтры полей формы

**Файл:** `functions/woocommerce-checkout.php`
Подключается в `functions.php` только если `class_exists('WooCommerce')`.

### Текстовые поля (`text`, `email`, `tel`, `number`, `password`)

Фильтр: `woocommerce_form_field_{type}` → `cw_checkout_field_text()`

Генерируемая структура:
```html
<p class="form-row {class}" id="{id}_field" data-priority="{priority}">
  <span class="woocommerce-input-wrapper">
    <div class="form-floating">
      <input type="{type}" class="input-text form-control" name="{key}" id="{id}"
             placeholder="{placeholder|label}" value="{value}" autocomplete="...">
      <label for="{id}">{label} <abbr class="required">*</abbr></label>
    </div>
  </span>
</p>
```

**Важно:** `<input>` идёт **перед** `<label>` — Bootstrap `form-floating` требует именно такой порядок. WooCommerce по умолчанию генерирует label перед input.

Placeholder = `$args['placeholder']` или `wp_strip_all_tags($args['label'])` если placeholder пустой.

### Select-поля

Фильтр: `woocommerce_form_field_select` → `cw_checkout_field_select()`

```html
<p class="form-row {class}" ...>
  <label for="{id}">{label}</label>
  <span class="woocommerce-input-wrapper">
    <div class="form-select-wrapper">
      <select name="{key}" id="{id}" class="form-select">
        <option value="...">...</option>
      </select>
    </div>
  </span>
</p>
```

### Textarea

Фильтр: `woocommerce_form_field_textarea` → `cw_checkout_field_textarea()`

Структура аналогична текстовым полям (`form-floating`), `rows` берётся из `custom_attributes['rows']` (default: 4).

### Поля Страна / Регион

Фильтр: `woocommerce_form_field_args` → `cw_checkout_country_state_args()`

```php
if ( in_array( $args['type'], ['country', 'state'] ) ) {
    $args['input_class'][] = 'form-select';
}
```

WooCommerce рендерит эти поля сам и заменяет `<select>` на select2. Класс `form-select` добавляется на скрытый нативный элемент — визуальная стилизация осуществляется через CSS на `.select2-container`.

---

## CSS — стилизация полей (`_woo-cart.scss`)

### Fallback-стили (для нестандартных полей без form-floating)

```scss
.woocommerce-checkout .form-row {
  margin-bottom: 1rem;

  // Лейблы для select-полей
  > .woocommerce-input-wrapper > label,
  > label {
    display: block;
    margin-bottom: 0.35rem;
    font-size: .875rem;
  }

  // Сброс для form-floating — Bootstrap управляет лейблом сам
  .form-floating > label {
    display: revert;
    margin-bottom: 0;
    font-size: 1rem;
  }
}
```

### Тонкое focus-кольцо

```scss
.woocommerce-checkout {
  .form-control:focus,
  .form-select:focus {
    box-shadow: 0 0 0 1px rgba(var(--bs-primary-rgb), 0.4);
  }
}
```

### Select2 (поля Страна / Регион)

```scss
.woocommerce-checkout {
  // Предотвращение FOUC — скрываем нативный select до инициализации select2
  select.country_select,
  select.country_to_state { display: none; }

  .select2-container--default {
    width: 100% !important;

    .select2-selection--single {
      padding: .6rem 2rem .6rem 1rem;
      font-size: .75rem;
      line-height: 1.5;
      color: var(--bs-body-color);
      background-color: var(--bs-body-bg);
      background-image: url("...svg chevron...");
      background-repeat: no-repeat;
      background-position: right .75rem center;
      background-size: 16px 12px;
      border: var(--bs-border-width) solid var(--bs-border-color);
      border-radius: var(--bs-border-radius);

      .select2-selection__rendered { padding: 0; line-height: 1.5; }
      .select2-selection__arrow { display: none; }
    }

    &.select2-container--open .select2-selection--single,
    &.select2-container--focus .select2-selection--single {
      border-color: #86b7fe;
      box-shadow: 0 0 0 1px rgba(var(--bs-primary-rgb), 0.4);
    }
  }
}
```

**Почему `display: none` на select.country_select:**
WooCommerce на checkout **всегда** инициализирует select2 для этих полей. Без скрытия нативный `<select>` виден в DOM пока не загрузится JS (FOUC).

---

## Двухколоночный макет

WooCommerce стандартно рендерит `#customer_details` (левая колонка) и `#order_review` (правая). Bootstrap-сетка настраивается через CSS или переопределение `form-checkout.php`.

---

## Платёжные методы

```scss
.woocommerce-checkout .wc_payment_methods {
  list-style: none; padding: 0;

  .wc_payment_method {
    padding: 0.85rem 0;
    border-bottom: 1px solid rgba(0,0,0,.09);
    > label { font-weight: 500; cursor: pointer; }
    .payment_box {
      margin-top: 0.75rem; padding: 1rem;
      background: rgba(0,0,0,.03);
      border-radius: 0.5rem; font-size: .875rem;
    }
  }
}
```
