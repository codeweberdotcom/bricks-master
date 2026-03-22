# Модуль Сравнения Товаров — Анализ Woodmart + План реализации

## Анализ Woodmart: что делает сравнение хорошим

### Ключевые UX-решения в Woodmart

| Что | Как работает | Почему хорошо |
|-----|-------------|---------------|
| **Фиксированная панель-бар** | Fixed bottom bar с миниатюрами товаров | Пользователь всегда видит что добавил — не нужно идти на страницу |
| **Кнопка на карточке** | Иконка «весы», рядом с wishlist/quick-view | Один жест — добавить к сравнению прямо из каталога |
| **Кнопка на single product** | После «В корзину» | Позволяет добавить при детальном изучении |
| **Лимит товаров** | Обычно 4 товара | Таблица остаётся читаемой на десктопе |
| **«Скрыть одинаковые»** | Чекбокс на странице сравнения | Фокус на реальных отличиях |
| **Горизонтальный скролл** | На мобиле таблица скроллится | Не ломает мобильную вёрстку |
| **Cookie-хранение** | Только cookie, без БД | Работает мгновенно, не нужна авторизация |
| **AJAX add/remove** | Без перезагрузки страницы | Плавный UX |
| **Статус кнопки** | Active/disabled state | Пользователь видит что уже добавлено |

### Чего НЕТ в Woodmart (или плохо реализовано)

- Нет «персистентности» между устройствами (cookie ≠ аккаунт)
- Сравнение атрибутов работает только для «плоских» атрибутов WooCommerce — вариативные товары сравниваются по родителю
- Таблица атрибутов пустая если у товаров нет одинаковых атрибутов
- Нет возможности скачать/поделиться сравнением

---

## Архитектура нашей реализации

### Принципы

1. **Паттерн Wishlist** — структура классов по аналогии с уже готовым wishlist-модулем
2. **Только cookie** — хранение без БД (в отличие от wishlist). Авторизованным не нужна персистентность сравнения.
3. **Bootstrap-first** — бар, таблица, кнопки — только Bootstrap-классы
4. **Vanilla JS** — jQuery только там где требует WC API
5. **Включается через Redux** — `compare_enable` switch

### Схема взаимодействия

```
Клик на .cw-compare-btn[data-product-id]
  └─ woo-compare.js: toggleCompare(id)
       ├─ fetch POST admin-ajax.php?action=cw_compare_toggle
       │    └─ CW_Compare::ajax_toggle()
       │         ├─ Обновляет cookie cw_compare (JSON array ids)
       │         └─ wp_send_json_success({ ids, count, bar_html })
       └─ JS: обновляет кнопку + заменяет #cw-compare-bar innerHTML

Переход на страницу сравнения [cw_compare]
  └─ CW_Compare_UI::render_shortcode()
       ├─ ids = cw_get_compare_ids()  ← читает cookie на сервере
       ├─ WC_Product[] = wc_get_products(ids)
       └─ Рендерит таблицу с атрибутами
```

---

## Файловая структура

```
functions/integrations/compare/
├── class-cw-compare.php         # Главный класс: AJAX, enqueue, хуки
├── class-cw-compare-storage.php # Работа с cookie (чтение/запись ids)
├── class-cw-compare-table.php   # Генерация HTML-таблицы сравнения
├── class-cw-compare-ui.php      # UI: кнопки, шорткод, бар, хуки карточек
└── functions.php                # Хелперы: cw_get_compare_url(), cw_get_compare_ids()

woocommerce/content-compare-bar.php   # Шаблон: нижняя панель (бар)
woocommerce/content-compare-table.php # Шаблон: таблица сравнения

src/assets/js/woo-compare.js          # JS: toggle, bar update, table «скрыть одинаковые»
src/assets/scss/theme/_woo-compare.scss # Стили: бар, кнопка, таблица
```

---

## Детали реализации

### 1. Хранилище: `class-cw-compare-storage.php`

Cookie `cw_compare` — JSON-массив product IDs.
Лимит берётся из Redux `compare_limit` (default 4, диапазон 2–6).

```php
class CW_Compare_Storage {
    const COOKIE_NAME = 'cw_compare';
    const COOKIE_DAYS = 30;

    public static function get_ids(): array         // читает cookie → array int
    public static function add( int $id ): bool     // добавляет, returns false если лимит
    public static function remove( int $id ): bool  // удаляет из cookie
    public static function clear(): void            // очищает cookie
    public static function has( int $id ): bool     // проверяет наличие
    public static function count(): int             // кол-во
    private static function save( array $ids ): void // setcookie()
}
```

**Отличие от Wishlist-хранилища:** нет интерфейса/БД — только cookie. Один класс вместо трёх.

---

### 2. Главный класс: `class-cw-compare.php`

```php
class CW_Compare {
    private bool $enabled;

    public function __construct() // регистрирует хуки если enabled
    public function init()        // создаёт CW_Compare_UI

    // AJAX
    public function ajax_toggle() // action: cw_compare_toggle (nopriv)
    public function ajax_clear()  // action: cw_compare_clear (nopriv)
}
```

**AJAX `cw_compare_toggle` — POST-параметры:**
- `product_id` (absint)
- `nonce` (cw_compare_nonce)

**Ответ:**
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

`bar_html` — переренденный `content-compare-bar.php` (только inner, без wrapper). JS заменяет innerHTML контейнера, не весь bar (чтобы не сломать CSS transition).

---

### 3. UI: `class-cw-compare-ui.php`

```php
class CW_Compare_UI {
    public function __construct()

    // Кнопка на карточке — через глобальный флаг или фильтр карточки
    public function render_loop_button( int $product_id ): string

    // Кнопка на single product
    public function render_single_button( WC_Product $product ): void
    // → hook: woocommerce_after_add_to_cart_button, priority 25

    // Нижний бар
    public function render_bar(): void
    // → hook: wp_footer (только на WC-страницах)

    // Шорткод [cw_compare]
    public function render_shortcode(): string

    // Enqueue scripts/styles
    public function enqueue(): void
    // → hook: wp_enqueue_scripts, priority 35
}
```

---

### 4. Таблица сравнения: `class-cw-compare-table.php`

**Строки таблицы (сверху вниз):**

| Строка | Источник данных | Примечание |
|--------|----------------|------------|
| Изображение | `get_the_post_thumbnail()` | Ссылка на товар |
| Название | `get_the_title()` | `<a>` на товар |
| Цена | `$product->get_price_html()` | С зачёркнутой старой ценой |
| Рейтинг | `wc_get_rating_html()` | Звёзды + кол-во отзывов |
| Наличие | `$product->get_availability()` | In stock / Out of stock |
| SKU | `$product->get_sku()` | Если включён в Redux |
| Кнопка «В корзину» | `woocommerce_template_loop_add_to_cart()` | Через хук WC |
| **Атрибуты WC** | `$product->get_attributes()` | Динамически, все товары |
| Кнопка удалить | JS / ссылка | Убирает из cookie |

**Алгоритм сбора атрибутов:**
1. Для каждого товара получить `$product->get_attributes()`
2. Объединить ключи всех атрибутов в единый список (union)
3. Для каждого атрибута и каждого товара: вывести значение или «—»
4. Строки где все значения одинаковые → получают класс `cw-compare-row--same` (скрываются JS при включённом "показать только различия")

---

### 5. Шаблон нижней панели: `content-compare-bar.php`

```html
<!-- Только inner-контент (заменяется AJAX) -->
<div class="cw-compare-bar-inner d-flex align-items-center gap-3">

  <!-- Слоты товаров (до limit штук) -->
  <div class="cw-compare-slots d-flex gap-2 flex-grow-1">
    <?php foreach ( $compare_ids as $id ) : ?>
      <div class="cw-compare-slot position-relative" data-id="<?= $id ?>">
        <img src="..." class="rounded" width="60" height="60" alt="...">
        <button class="cw-compare-slot-remove btn-close btn-close-sm position-absolute top-0 end-0"
                data-product-id="<?= $id ?>"></button>
      </div>
    <?php endforeach; ?>
    <!-- Пустые слоты -->
    <?php for ( $i = count($compare_ids); $i < $limit; $i++ ) : ?>
      <div class="cw-compare-slot cw-compare-slot--empty rounded border border-dashed"></div>
    <?php endfor; ?>
  </div>

  <!-- Кнопки действий -->
  <div class="cw-compare-actions d-flex gap-2">
    <a href="<?= cw_get_compare_url() ?>" class="btn btn-primary btn-sm has-ripple">
      <?= __('Сравнить', 'codeweber') ?>
      <span class="badge bg-white text-primary ms-1"><?= count($compare_ids) ?></span>
    </a>
    <button class="cw-compare-clear btn btn-outline-secondary btn-sm">
      <?= __('Очистить', 'codeweber') ?>
    </button>
  </div>

</div>
```

**Wrapper (в PHP wp_footer, не меняется AJAX):**
```html
<div id="cw-compare-bar" class="cw-compare-bar fixed-bottom bg-white shadow border-top py-3 px-4"
     style="display:none; z-index:1040">
  <!-- inner заменяется AJAX -->
</div>
```

**Показ/скрытие bar:** при count > 0 → JS добавляет `show` через `style.display = 'flex'`, иначе скрывает.

---

### 6. Страница сравнения: шорткод `[cw_compare]`

**Состояния:**
- **Пусто** — «Добавьте товары для сравнения» + ссылка в каталог
- **1 товар** — «Добавьте ещё минимум 1 товар»
- **2–N товаров** — таблица

**Горизонтальный скролл на мобиле:**
```html
<div class="table-responsive">
  <table class="cw-compare-table table table-bordered align-middle">
    <thead>
      <tr>
        <th class="cw-compare-label-col"><?= __('Параметр', 'codeweber') ?></th>
        <?php foreach ( $products as $p ) : ?>
          <th class="cw-compare-product-col" data-product-id="<?= $p->get_id() ?>">
            <!-- название + кнопка удалить -->
          </th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <!-- строки: image, price, rating, stock, sku, add_to_cart, attrs... -->
    </tbody>
  </table>
</div>

<!-- Переключатель "только различия" -->
<div class="form-check form-switch mb-4">
  <input class="form-check-input" type="checkbox" id="cw-compare-diff-only">
  <label class="form-check-label" for="cw-compare-diff-only">
    <?= __('Показать только различия', 'codeweber') ?>
  </label>
</div>
```

---

### 7. JS: `src/assets/js/woo-compare.js`

**Конфигурация `cwCompare` (wp_localize_script):**
```js
cwCompare = {
  ajaxUrl,        // admin_url('admin-ajax.php')
  nonce,          // wp_create_nonce('cw_compare_nonce')
  compareUrl,     // URL страницы сравнения
  limit,          // max products (из Redux)
  ids,            // текущие IDs (из cookie при загрузке)
  i18n: {
    limitReached,  // 'Достигнут лимит сравниваемых товаров'
    added,         // 'Добавлен в сравнение'
    removed,       // 'Удалён из сравнения'
    compare,       // 'Сравнить'
    clear,         // 'Очистить'
  }
}
```

**Методы:**

| Метод | Описание |
|-------|----------|
| `init()` | Инициализация: bindEvents, updateBar, syncButtons |
| `bindEvents()` | Delegation: `.cw-compare-btn`, `.cw-compare-slot-remove`, `.cw-compare-clear` |
| `toggle(id, btn)` | POST AJAX cw_compare_toggle → обновляет state |
| `remove(id)` | POST AJAX cw_compare_toggle (удаление если уже есть) |
| `clear()` | POST AJAX cw_compare_clear → скрывает бар |
| `updateBar(bar_html)` | Заменяет `#cw-compare-bar .cw-compare-bar-inner` |
| `showBar(count)` | Показывает бар с CSS transition если count > 0 |
| `hideBar()` | Скрывает бар |
| `syncButtons(ids)` | Проставляет `--active` на все `.cw-compare-btn[data-product-id]` |
| `markButton(btn, added)` | Меняет иконку/title/класс кнопки |
| `initDiffOnly()` | На странице сравнения: toggle `.cw-compare-row--same` |

**Transition бара:**
Бар появляется снизу — Bootstrap `fixed-bottom` + CSS `translateY(100%) → translateY(0)` с `transition: transform 0.3s ease`.

---

### 8. SCSS: `src/assets/scss/theme/_woo-compare.scss`

```scss
// Нижний бар
.cw-compare-bar {
  transform: translateY(100%);
  transition: transform .3s ease;

  &.is-visible {
    transform: translateY(0);
  }
}

// Слоты в баре
.cw-compare-slot {
  width: 60px; height: 60px;

  &--empty {
    border-style: dashed !important;
    opacity: .4;
  }

  img { width: 100%; height: 100%; object-fit: cover; }

  .btn-close {
    background-size: .5em;
    padding: .15rem;
    background-color: rgba(var(--bs-white-rgb), .9);
  }
}

// Кнопка сравнения на карточке/single
.cw-compare-btn {
  &--active { color: var(--bs-primary); }
  &--loading { pointer-events: none; opacity: .5; }
}

// Таблица сравнения
.cw-compare-table {
  min-width: 600px; // горизонтальный скролл на мобиле

  .cw-compare-label-col {
    width: 160px;
    font-weight: 600;
    white-space: nowrap;
  }

  .cw-compare-product-col { min-width: 180px; text-align: center; }

  // Строки с одинаковыми значениями — скрываются JS
  .cw-compare-row--same { display: none; }

  // Изображение товара в шапке таблицы
  .cw-compare-product-img {
    width: 100px; height: 100px;
    object-fit: contain;
    margin: 0 auto .5rem;
  }
}
```

---

### 9. Redux-настройки

Добавить секцию в **WooCommerce → Compare**:

| Redux key | Тип | Default | Описание |
|-----------|-----|---------|----------|
| `compare_enable` | switch | false | Включить модуль |
| `compare_page` | select pages | — | Страница с `[cw_compare]` |
| `compare_limit` | slider/text | 4 | Максимум товаров (2–6) |
| `compare_btn_loop` | switch | true | Кнопка на карточках в каталоге |
| `compare_btn_single` | switch | true | Кнопка на single product |
| `compare_show_sku` | switch | true | Строка SKU в таблице |
| `compare_show_rating` | switch | true | Строка Рейтинг |
| `compare_show_stock` | switch | true | Строка Наличие |

---

### 10. Кнопка на карточке товара

В `shop2.php` (основная карточка каталога) добавить рядом с `.item-view` и `.item-like`:

```html
<?php if ( cw_compare_btn_on_loop() ) : ?>
  <a href="<?= get_permalink($product->get_id()) ?>"
     class="item-compare cw-compare-btn <?= cw_compare_has($product->get_id()) ? 'cw-compare-btn--active' : '' ?>"
     data-product-id="<?= esc_attr($product->get_id()) ?>"
     data-bs-toggle="tooltip"
     data-bs-title="<?= esc_attr(__('Сравнить', 'codeweber')) ?>"
     title="<?= esc_attr(__('Сравнить', 'codeweber')) ?>">
    <i class="uil uil-balance-scale"></i>
  </a>
<?php endif; ?>
```

`href` — ссылка на товар как fallback (без JS).

---

## Что отличает нас от Woodmart

| Аспект | Woodmart | Наша реализация |
|--------|----------|-----------------|
| Хранение | Cookie | Cookie (аналогично) |
| Обновление бара | AJAX → полный HTML бара | AJAX → только inner контент (wrapper статичный) |
| Стили | Кастомные SCSS (собственная дизайн-система) | Bootstrap-first, минимум кастомного CSS |
| Кнопка «В корзину» в таблице | Переопределённая | Через WC hook `woocommerce_loop_add_to_cart_link` |
| Мобайл | Адаптивная сетка | `table-responsive` + горизонтальный скролл |
| Атрибуты | Только product attributes (WC) | WC attributes + возможность расширения через хук |
| База данных | Нет | Нет |

---

## Порядок реализации (этапы)

### Этап 1 — Storage + Core (1 сессия)
- `class-cw-compare-storage.php` — cookie CRUD
- `class-cw-compare.php` — конструктор, AJAX handlers
- `functions.php` — хелперы `cw_get_compare_ids()`, `cw_get_compare_url()`, `cw_compare_has()`
- `functions.php` темы — `require_once` нового модуля под флагом `compare_enable`

### Этап 2 — Бар + Кнопки (1 сессия)
- `content-compare-bar.php` — шаблон бара
- `class-cw-compare-ui.php` — кнопки loop/single, бар в wp_footer
- Redux-настройки (секция WooCommerce → Compare)
- Правка `shop2.php` — добавить `.cw-compare-btn`

### Этап 3 — Страница сравнения (1 сессия)
- `class-cw-compare-table.php` — генератор таблицы
- `content-compare-table.php` — HTML-шаблон
- Шорткод `[cw_compare]`

### Этап 4 — JS + SCSS (1 сессия)
- `src/assets/js/woo-compare.js` — полная логика
- `src/assets/scss/theme/_woo-compare.scss` — стили
- Подключение в Gulp (enqueue в `class-cw-compare-ui.php`)

### Этап 5 — Полировка (1 сессия)
- Тест на: каталог, single, wishlist-страница, quick view
- `i18n` строки
- Документация `doc_claude/integrations/WC_COMPARE.md`
- Build + коммит

---

## Зависимости и хуки

| Зависимость | Источник |
|------------|---------|
| `bootstrap.Tooltip` | `plugins.js` темы |
| `jQuery` | WordPress core (только для WC add-to-cart) |
| `WooCommerce` | Плагин |
| `Redux` | `redux-framework/` темы |
| `cw_is_wishlist_page()` | `functions/integrations/wishlist/functions.php` |

**Хук подключения модуля:**
В `functions/woocommerce.php` (или отдельным `require_once` в `functions.php`) — аналогично wishlist:

```php
if ( class_exists('WooCommerce') ) {
    require_once get_template_directory() . '/functions/integrations/compare/class-cw-compare-storage.php';
    require_once get_template_directory() . '/functions/integrations/compare/class-cw-compare-table.php';
    require_once get_template_directory() . '/functions/integrations/compare/class-cw-compare-ui.php';
    require_once get_template_directory() . '/functions/integrations/compare/class-cw-compare.php';
    require_once get_template_directory() . '/functions/integrations/compare/functions.php';
    new CW_Compare();
}
```

---

## Открытые вопросы для согласования

1. **Лимит по умолчанию**: 4 товара (как Woodmart) — ок?
2. **Кнопка на карточке**: использовать `<a>` с `href` как fallback или `<button>`? (Wishlist использует `<button>`)
3. **Строки таблицы**: помимо WC-атрибутов, добавлять кастомные поля (ACF, postmeta)? Нужен хук `cw_compare_table_rows`.
4. **Вариативные товары**: сравнивать parent-товар целиком или конкретный вариант? (Woodmart — parent, упрощённо)
5. **Синхронизация с wishlist-страницей**: если wishlist показывает сравнение — нужна ли кнопка «сравнить» и там?
6. **Матомо-трекинг**: добавить event «Compare → Add» аналогично wishlist?
