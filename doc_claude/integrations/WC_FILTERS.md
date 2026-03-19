# WooCommerce Filters — Тема CodeWeber

Документация по серверной части фильтров WooCommerce: PHP-функции, шаблоны, CSS-классы и JS-взаимодействие.

**Файл с функциями:** `functions/woocommerce-filters.php`
**Шаблоны:** `templates/woocommerce/filters/`
**JS:** `src/assets/js/shop-pjax.js`
**SCSS:** `src/assets/scss/theme/_woo-filters.scss`

**Плагин-документация:** [`doc_claude/blocks/WC_FILTER_PANEL.md`](../../../../plugins/codeweber-gutenberg-blocks/doc_claude/blocks/WC_FILTER_PANEL.md) *(блок Gutenberg)*

---

## Главная функция: `cw_render_filter_items()`

```php
cw_render_filter_items( array $items, array $panel_atts = [] ): void
```

Итерирует массив `$items` (из атрибутов блока или виджета) и подключает нужный шаблон для каждого элемента.

### Параметры `$panel_atts`

| Ключ | Тип | Описание |
|------|-----|----------|
| `section_style` | string | `plain` / `accordion` |
| `sections_open` | bool | Секции открыты по умолчанию |
| `wrapper_class` | string | CSS-класс обёртки (напр. `widget`) |
| `heading_tag` | string | Тег заголовка: h2–h6, p |
| `heading_class` | string | CSS-класс заголовка |
| `checkbox_size` | string | `''` / `sm` |
| `checkbox_item_class` | string | CSS-класс `form-check` |
| `radio_size` | string | `''` / `sm` |
| `radio_item_class` | string | CSS-класс `form-check` |
| `button_size` | string | `''` / `btn-xs` / `btn-sm` / `btn-lg` |
| `button_style` | string | `solid` / `outline` / `soft` |
| `button_shape` | string | Bootstrap radius class или пустая строка |
| `button_color` | string | Bootstrap color token (напр. `secondary`) |
| `button_extra_class` | string | Доп. CSS-класс кнопок |
| `badge_size` | string | `''` / `badge-lg` |
| `badge_shape` | string | Bootstrap radius class |
| `badge_color` | string | Bootstrap color token (пустое = без `bg-*`) |
| `badge_extra_class` | string | Доп. CSS-класс меток |
| `reset_label` | string | Текст кнопки сброса |
| `slider_size` | string | `lg` / `md` / `sm` |

---

## Функции получения терминов

### `cw_get_category_filter_terms()`

```php
cw_get_category_filter_terms(
    int  $parent          = 0,
    bool $show_count      = true,
    bool $count_unfiltered = false
): array
```

Возвращает категории WooCommerce с учётом активных фильтров.

- `$count_unfiltered = true` — всегда возвращает общее кол-во товаров в категории, игнорируя активные фильтры.

### `cw_get_tag_filter_terms()`

```php
cw_get_tag_filter_terms( bool $show_count = true ): array
```

Метки WooCommerce с отфильтрованным счётчиком.

### `cw_get_attribute_filter_terms()`

```php
cw_get_attribute_filter_terms(
    string $taxonomy,
    bool   $show_count   = true,
    bool   $single_select = false
): array
```

Термины таксономии атрибута WC. При `$single_select = true` формирует URL как одиночный выбор (заменяет текущий, не добавляет).

### `cw_get_term_swatch_data()`

```php
cw_get_term_swatch_data( int $term_id ): array
```

Возвращает данные свотча для термина:

```php
[
    'color'      => '#hex',
    'is_dual'    => bool,
    'secondary'  => '#hex',
    'dual_angle' => int,   // градусы градиента
    'image_id'   => int,   // attachment ID
]
```

---

## Вспомогательные функции URL

| Функция | Описание |
|---------|----------|
| `cw_get_filter_url( $param, $value )` | URL с добавленным/убранным значением фильтра (toggle) |
| `cw_get_filter_url_single( $param, $value )` | URL с заменой значения (single-select) |
| `cw_get_price_filter_url( $min, $max )` | URL ценового фильтра |
| `cw_get_clear_filters_url()` | URL сброса всех фильтров |
| `cw_get_active_filter_params()` | Массив активных параметров фильтра |
| `cw_get_current_filter_values( $param )` | Активные значения для конкретного параметра |
| `cw_get_price_filter_range()` | Текущий диапазон цен `[min, max, current_min, current_max]` |

---

## Шаблоны фильтров

Расположены в `templates/woocommerce/filters/`.

| Файл | Вызывается для |
|------|---------------|
| `filter-panel.php` | Обёртка секции (заголовок + collapse) |
| `filter-category.php` | `filterType: categories` и `tags` |
| `filter-attribute.php` | `filterType: attributes` |
| `filter-price.php` | `filterType: price` (слайдер) |
| `filter-rating.php` | `filterType: rating` |
| `filter-stock.php` | `filterType: stock` |
| `filter-active.php` | `type: active_chips` |

### Переменные, доступные в шаблонах

Шаблон получает переменные через `extract()` из массива, переданного `cw_render_filter_items()`:

**Общие:**
- `$display_mode` — режим отображения (checkbox / radio / list / button / badge / color / image)
- `$show_count` — показывать счётчик
- `$empty_behavior` — поведение пустых терминов
- `$terms_data` — массив терминов с полями `term`, `url`, `count`, `is_active`, `is_empty`, `is_clickable`
- `$panel_atts` — весь массив настроек оформления

**Для checkbox/radio:**
- `$checkbox_size`, `$checkbox_item_class`

**Для кнопок:**
- `$button_size`, `$button_style`, `$button_shape`, `$button_color`, `$button_extra_class`

**Для меток (badge):**
- `$badge_size`, `$badge_shape`, `$badge_color`, `$badge_extra_class`
- `$badge_item_class` (per-item, из элемента повторителя)

**Для свотчей (color/image):**
- `$swatch_shape`, `$swatch_columns`, `$swatch_item_class`
- `$use_grid` — true когда `$swatch_columns > 0`

---

## Поведение недоступных терминов

Термин считается недоступным (`is_empty = true`) если `count = 0` и он не активен.

| `empty_behavior` | Действие в шаблоне |
|------------------|--------------------|
| `default` | Показывать как обычно |
| `hide` | `continue` (пропустить) |
| `disable` | Класс `disabled` / `opacity-50`, без `href` |
| `disable_clickable` | Класс `disabled`, но `href` сохранён |
| `hide_block` | Если все пусты — скрыть весь блок |

**Для `color` / `image` режимов** вместо `opacity-50` используется класс `cw-swatch--unavailable`:
```scss
.cw-swatch--unavailable {
  box-shadow: inset 0 0 0 1.5px $primary;

  &::after {
    // Красная диагональная линия (к верхнему левому углу)
    background: linear-gradient(
      to top left,
      transparent calc(50% - 1px),
      $primary calc(50% - 1px),
      $primary calc(50% + 1px),
      transparent calc(50% + 1px)
    );
  }
}
```

---

## Ограничение списка (JS)

Контейнер с терминами оборачивается в `.cw-filter-limit` при `limitType != 'none'`.

### Data-атрибуты

```html
<div class="cw-filter-limit"
     data-limit-type="count|height"
     data-limit-value="5"
     data-show-more-text="Показать ещё"
     data-show-less-text="Скрыть">
  <!-- термины -->
</div>
<a href="#" class="cw-filter-show-more">Показать ещё (N)</a>
```

### JS (shop-pjax.js)

```js
initLimitFilter()   // инициализация при загрузке и после PJAX
toggleLimitFilter(el)  // переключение при клике на ссылку
```

- **count**: скрывает элементы с `nth-child(n+{value+1})`
- **height**: устанавливает `max-height` и `overflow: hidden`

Ссылка «Показать ещё» рендерится как `<a href="#">`, не `<button>`.

---

## Виджеты (устаревшие)

В `woocommerce-filters.php` также зарегистрированы два классических виджета:
- `CW_WC_Attribute_Filter_Widget` — фильтр по атрибутам
- `CW_WC_Category_Filter_Widget` — фильтр по категориям

Оба поддерживают `show_count`. Используют те же функции `cw_get_*_filter_terms()` и шаблоны.

> **Предпочтительный способ** — блок `codeweber-blocks/wc-filter-panel`, виджеты оставлены для обратной совместимости.

---

## PJAX-интеграция

Фильтры работают через PJAX: при клике на ссылку фильтра не происходит полной перезагрузки страницы. `shop-pjax.js` перехватывает переход, загружает новую страницу, заменяет контент магазина и повторно инициализирует фильтры.

Ссылки фильтров должны иметь класс `pjax-link` (добавляется шаблонами автоматически).
