# WooCommerce Compare — Документация

Модуль сравнения товаров. Cookie-хранение, AJAX, нижняя панель, таблица атрибутов. Не зависит от сторонних плагинов.

---

## Файловая структура

```
functions/integrations/compare/
├── class-cw-compare.php          # Главный класс: AJAX-хуки, инициализация
├── class-cw-compare-storage.php  # Cookie CRUD (без БД)
├── class-cw-compare-table.php    # Генератор таблицы сравнения
├── class-cw-compare-ui.php       # UI: кнопки, бар, шорткод, enqueue
└── functions.php                 # Хелперы + AJAX создания страницы

woocommerce/content-compare-bar.php    # Шаблон нижней панели (inner, заменяется AJAX)
woocommerce/content-compare-table.php  # Шаблон таблицы сравнения

src/assets/js/woo-compare.js           # Исходник JS
dist/assets/js/woo-compare.js          # Скомпилированный JS
src/assets/scss/theme/_woo-compare.scss # Стили
```

---

## Включение

Redux → **WooCommerce → Compare → Включить модуль** (`compare_enable`).

Требования: WooCommerce активен, Redux Framework подключён.

Подключается в `functions.php`:
```php
if ( class_exists('WooCommerce') ) {
    require_once get_template_directory() . '/functions/integrations/compare/functions.php';
    require_once get_template_directory() . '/functions/integrations/compare/class-cw-compare-storage.php';
    require_once get_template_directory() . '/functions/integrations/compare/class-cw-compare-table.php';
    require_once get_template_directory() . '/functions/integrations/compare/class-cw-compare-ui.php';
    require_once get_template_directory() . '/functions/integrations/compare/class-cw-compare.php';
    add_action( 'after_setup_theme', function () { new CW_Compare(); }, 40 );
}
```

---

## Redux-настройки

| Ключ Redux | Тип | По умолчанию | Описание |
|-----------|-----|-------------|----------|
| `compare_enable` | switch | false | Включить модуль |
| `compare_page` | select (pages) | — | Страница с `[cw_compare]` |
| `compare_limit` | slider | 4 | Макс. товаров для сравнения (2–6) |
| `compare_btn_loop` | switch | true | Кнопка на карточках каталога |
| `compare_btn_single` | switch | true | Кнопка на странице товара |
| `compare_show_rating` | switch | true | Строка «Рейтинг» в таблице |
| `compare_show_stock` | switch | true | Строка «Наличие» в таблице |
| `compare_show_sku` | switch | true | Строка «SKU» в таблице |

### Создание страницы сравнения

В Redux рядом с полем `compare_page` — кнопка **«Create Compare Page»** (JS в `admin_footer`). Создаёт страницу «Compare Products» с шорткодом `[cw_compare]` внутри `codeweber-blocks/section`, автоматически выбирает её в select.

---

## Хранилище: `CW_Compare_Storage`

Cookie `cw_compare` — JSON-массив product/variation IDs. Живёт 30 дней. Без БД.

```php
CW_Compare_Storage::get_ids(): int[]          // читает cookie → array int
CW_Compare_Storage::add( $id, $limit ): bool  // добавляет; false если лимит
CW_Compare_Storage::remove( $id ): bool       // удаляет из cookie
CW_Compare_Storage::clear(): void             // очищает cookie
CW_Compare_Storage::has( $id ): bool          // есть ли ID в списке
CW_Compare_Storage::count(): int              // кол-во
CW_Compare_Storage::set_ids( $ids ): void     // перезаписывает список (синхронизация с клиентом)
```

`save()` вызывает `setcookie()` + обновляет `$_COOKIE` для текущего запроса.

**Отличие от Wishlist:** нет интерфейса/БД. Один класс вместо трёх. Работает для всех пользователей одинаково.

---

## Главный класс: `CW_Compare`

Регистрирует AJAX-хуки, создаёт `CW_Compare_UI` через `init` на приоритете 1.

### AJAX-endpoints

| Action | Доступ | Параметры | Описание |
|--------|--------|-----------|----------|
| `cw_compare_toggle` | nopriv | `product_id`, `nonce`, `current_ids` | Добавить/удалить товар |
| `cw_compare_clear` | nopriv | `nonce` | Очистить весь список |

### Ответ `cw_compare_toggle` (success)

```json
{
  "success": true,
  "data": {
    "added": true,
    "ids": [12, 45, 78],
    "count": 3,
    "limit_reached": false,
    "bar_html": "<div class=\"cw-compare-bar-inner\">...</div>"
  }
}
```

`bar_html` — перерендеренный `content-compare-bar.php` (только inner-контент). JS заменяет `#cw-compare-bar innerHTML`, не весь wrapper — чтобы не прерывать CSS transition.

### Ответ при лимите (error)

```json
{
  "success": false,
  "data": { "message": "Достигнут лимит...", "limit_reached": true }
}
```

### Параметр `current_ids` (защита от race condition)

JS передаёт текущий `state.ids` как строку через запятую (`current_ids`). PHP синхронизирует куку с клиентским состоянием перед toggle. Это исключает race condition при быстрых кликах на несколько товаров подряд.

```php
// В ajax_toggle():
if ( isset( $_POST['current_ids'] ) && '' !== $_POST['current_ids'] ) {
    $client_ids = array_map( 'absint', explode( ',', ... ) );
    CW_Compare_Storage::set_ids( $client_ids );
}
```

---

## UI: `CW_Compare_UI`

### Кнопка на карточке каталога

Рендерится напрямую из `shop2.php` (статический метод):

```php
CW_Compare_UI::render_loop_button( $product_id );
```

HTML:
```html
<a href="{compareUrl}" class="item-compare cw-compare-btn [cw-compare-btn--active]"
   data-product-id="{id}" data-bs-toggle="white-tooltip" title="..." aria-label="...">
    <i class="uil uil-exchange"></i>
</a>
```

Позиционирование — через `_projects.scss` (в одной группе с `item-like`, `item-view`): `top: 6.4rem; right: 0; opacity: 0` → `:hover` → `right: 1rem; opacity: 1`.

Условие показа в `shop2.php`:
```php
$cw_compare_on = class_exists( 'CW_Compare_Storage' )
    && class_exists( 'Redux' )
    && (bool) Redux::get_option( 'redux_demo', 'compare_enable', true )
    && (bool) Redux::get_option( 'redux_demo', 'compare_btn_loop', true );
```

### Кнопка на странице товара

Хук: `woocommerce_after_add_to_cart_button`, priority 25.

HTML:
```html
<a href="{compareUrl}"
   class="cw-compare-btn cw-compare-btn--single btn btn-outline-secondary btn-icon has-ripple px-3 h-100 [{btn_style}] [cw-compare-btn--active]"
   data-product-id="{id}" aria-label="..." title="...">
    <i class="uil uil-exchange"></i>
</a>
```

Для вариативных товаров: JS обновляет `data-product-id` при выборе вариации (слушает `found_variation` / `reset_data` WC jQuery events).

### Нижний бар

Хук: `wp_footer`. Рендерится на WC-страницах, странице сравнения и странице вишлиста.

```html
<div id="cw-compare-bar"
     class="cw-compare-bar bg-white border-top shadow-lg py-3 px-4 [is-visible]"
     style="z-index:1040; [display:none;]">
    <!-- inner: content-compare-bar.php -->
</div>
```

- `is-visible` + нет `display:none` → есть товары (PHP, при загрузке)
- `display:none` без `is-visible` → список пуст
- JS управляет через `showBar()` / `hideBar()` после AJAX

### Шорткод `[cw_compare]`

Три состояния:
- **Пусто** (`ids = []`) — иконка + «Добавьте товары» + кнопка «В каталог»
- **1 товар** (`count < 2`) — иконка + «Добавьте ещё хотя бы один» + кнопка «В каталог»
- **2+ товаров** — переключатель «Только различия» + кнопка «Очистить всё» + таблица

Все кнопки принимают стиль из `Codeweber_Options::style('button')`.

### Enqueue

Handle: `cw-compare`. Грузится на WC-страницах, странице сравнения и вишлиста.

---

## Таблица: `CW_Compare_Table`

### Строки таблицы (сверху вниз)

| Строка | Источник | Redux-флаг |
|--------|----------|-----------|
| Изображение + название + удалить | `wp_get_attachment_image()`, `get_name()` | — |
| Цена | `$product->get_price_html()` | — |
| Рейтинг | `wc_get_rating_html()` | `compare_show_rating` |
| Наличие | `$product->get_availability()` | `compare_show_stock` |
| SKU | `$product->get_sku()` | `compare_show_sku` |
| В корзину | `woocommerce_loop_add_to_cart_link` filter | — |
| Атрибуты WC | `$product->get_attributes()` (union всех товаров) | — |

### Алгоритм атрибутов

1. Для каждого товара `$product->get_attributes()` → собирает все ключи (union)
2. Для каждой строки атрибута и каждого товара: выводит значение или «—»
3. Если все значения в строке одинаковые → `cw-compare-row--same` (JS скрывает при «только различия»)

### Вариативные товары

`wc_get_product($variation_id)` возвращает `WC_Product_Variation`. Название берётся от родителя. URL — от родителя. Изображение — сначала вариации, потом родителя.

### Методы

```php
$table = new CW_Compare_Table( $ids );
$table->get_products(): WC_Product[]
$table->collect_attributes(): array         // ['key' => 'Label', ...]
$table->get_image( $product, $size, $extra_class ): string
$table->get_url( $product ): string
$table->get_name( $product ): string
$table->all_same( $key ): bool              // все товары имеют одно значение атрибута
$table->show_sku(): bool
$table->show_rating(): bool
$table->show_stock(): bool
$table->render(): string                    // ob_start + include content-compare-table.php
```

---

## Шаблоны

### `content-compare-bar.php`

Принимает `$compare_ids` (int[]) и `$limit` (int) через `get_template_part()` args.

Структура:
- `.cw-compare-slots` — заполненные слоты (картинка 56×56, крестик удаления) + пустые (пунктирная рамка)
- `.cw-compare-actions` — кнопка «Сравнить» (иконка + текст скрыт на xs) + «Очистить» (всегда с текстом)

Карточки и кнопки принимают `$card_radius` и `$btn_style` из `Codeweber_Options`.

### `content-compare-table.php`

Переменные: `$this` (CW_Compare_Table), `$products`, `$attrs`, `$btn_style`, `$card_radius`.

Bootstrap `table-responsive` → горизонтальный скролл на мобильном. `min-width: 560px` на таблице.

---

## JavaScript: `woo-compare.js`

### Конфигурация `cwCompare`

```js
cwCompare = {
    ajaxUrl,     // admin-ajax.php
    nonce,       // cw_compare_nonce
    compareUrl,  // URL страницы сравнения
    limit,       // макс. товаров из Redux
    ids,         // текущие IDs из cookie (при загрузке)
    i18n: { limitReached, add, added, removed, compare, clear, emptySlot }
}
```

### Архитектура

**Очередь запросов** — `enqueue(fn)` / `runQueue()`: каждый следующий AJAX запускается только после завершения предыдущего. Совместно с `current_ids` исключает race condition.

**Локальный state**: `state = { ids: cfg.ids, limit: cfg.limit }` — обновляется после каждого успешного ответа.

### Ключевые функции

| Функция | Описание |
|---------|----------|
| `init()` | `syncButtons` + `bindEvents` + `initDiffOnly` + `initVariationSync` |
| `toggleCompare(btn)` | Берёт id из `data-product-id`, ставит в очередь POST с `current_ids` |
| `removeFromCompare(id)` | POST toggle → убирает колонку из таблицы, редирект если список пуст |
| `clearCompare()` | POST clear → скрывает бар, редирект со страницы сравнения |
| `updateBar(barHtml, count)` | Заменяет `bar.innerHTML`, show/hideBar |
| `showBar()` | `display:''` + rAF → `is-visible` |
| `hideBar()` | Убирает `is-visible`, `display:none` по `transitionend` |
| `syncButtons(ids)` | Обновляет все `.cw-compare-btn` по `data-product-id` |
| `setLoading(btn, bool)` | Добавляет/убирает `cw-compare-btn--loading` |
| `initVariationSync()` | jQuery `found_variation` / `reset_data` → обновляет `data-product-id` на single-кнопке |
| `initDiffOnly()` | Чекбокс `#cw-compare-diff-only` → toggle `display:none` на `.cw-compare-row--same` |
| `showNotice(msg)` | `CWNotify.show(msg, { type: 'warning', event: 'compare' })` или `alert()` |

### Loading-состояния

| Элемент | Класс | Эффект |
|---------|-------|--------|
| `.cw-compare-btn` (карточка/single) | `cw-compare-btn--loading` | Иконка прозрачная, border-spinner через `::after` |
| `.cw-compare-slot` (бар) | `cw-compare-slot--loading` | Картинка прозрачная, spinner через `::after`, крестик скрыт |
| `.cw-compare-col--removing` (таблица) | — | Колонка `opacity: .4`, `pointer-events: none` |
| `.cw-compare-clear` (кнопка) | `cw-compare-clear--loading` | `opacity: .65`, spinner через `::after` |

---

## SCSS: `_woo-compare.scss`

| Блок | Описание |
|------|----------|
| `.cw-compare-bar` | `position: fixed; bottom: 0; width: 100%; overflow: hidden; transform: translateY(100%)` → `.is-visible` translateY(0) |
| `.cw-compare-slot` | 40px mobile / 56px sm+ через media query |
| `.cw-compare-slot--empty` | `border-style: dashed; opacity: .4` |
| `.cw-compare-slot--loading` | Spinner через `::after`, картинка `opacity: .3` |
| `.cw-compare-btn--active` | `color: var(--bs-primary)` |
| `.cw-compare-btn--loading` | Иконка прозрачная + spinner `::after` |
| `.cw-compare-btn--single.--active` | `background-color: primary; color: #fff` |
| `.cw-compare-col--removing` | `opacity: .4; transition: .25s` |
| `.cw-compare-clear--loading` | `opacity: .65` + spinner `::after` |
| `.cw-compare-table` | `min-width: 560px` |
| `@keyframes cw-compare-spin` | Анимация 0.7s для всех спиннеров |

---

## Helper-функции

```php
cw_get_compare_url(): string      // URL страницы сравнения из Redux или /compare/
cw_get_compare_ids(): int[]       // Текущие IDs из cookie
cw_compare_has( $id ): bool       // Есть ли ID в списке
cw_get_compare_limit(): int       // Лимит из Redux (default 4)
cw_is_compare_page(): bool        // Текущая страница — страница сравнения
```

---

## Адаптивность бара

| Breakpoint | Слоты | Кнопки |
|-----------|-------|--------|
| `< 576px` (xs) | 40×40px, `gap-2` | Иконки (текст «Сравнить» скрыт `d-none d-sm-inline`), «Очистить» с текстом |
| `≥ 576px` (sm+) | 56×56px, `gap-3` | Полные кнопки с текстом |

Бар: `flex-wrap: wrap` — кнопки переносятся на следующую строку если не хватает места.

---

## Страницы где работает модуль

- Каталог WooCommerce (`is_shop`, `is_product_category`, `is_product_tag`)
- Страница товара (`is_product`)
- Страница сравнения (`cw_is_compare_page`)
- Страница вишлиста (`cw_is_wishlist_page`) — бар и JS

На всех остальных страницах JS и бар не грузятся.
