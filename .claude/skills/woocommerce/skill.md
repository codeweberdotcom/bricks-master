---
name: woocommerce
description: WooCommerce задачи в теме CodeWeber — переопределение шаблонов, карточки товаров, страница товара, магазин
argument-hint: "что нужно сделать, например: override single-product/price.php | new product card | customize shop page"
---

Выполни WooCommerce задачу в теме CodeWeber: `$ARGUMENTS`

---

## Шаг 1: Коммит текущего состояния

Запусти `git status`.

- Незакоммиченные изменения → создай коммит перед началом.
- Чистое дерево → продолжай.

---

## Шаг 2: Определи контекст — parent или child тема

Проверь наличие `./style.css`:

- Если есть строка `Template:` — это **дочерняя тема**. Извлеки `PARENT_SLUG` и `TEXT_DOMAIN`.
- Если нет — это **родительская тема** (`codeweber`). `PARENT_SLUG = codeweber`.

> Всё новое — только в **дочерней теме**. Родительскую тему менять только если задача явно про parent.

---

## Шаг 3: Прочитай документацию

Всегда читай:

- `../PARENT_SLUG/doc_claude/architecture/CHILD_THEME_AI_RULES.md` — разделы "Переопределение WooCommerce шаблонов" и "Карточки товаров WooCommerce"

По типу задачи дополнительно:

| Задача | Документ |
|--------|----------|
| Фильтры, свотчи, PJAX | `../PARENT_SLUG/doc_claude/integrations/WC_FILTERS.md` |
| Quick View, модальное окно | `../PARENT_SLUG/doc_claude/integrations/WC_QUICK_VIEW.md` |
| Redux-настройки | `../PARENT_SLUG/doc_claude/settings/REDUX_OPTIONS.md` |

---

## Шаг 4: Анализ задачи

Определи тип задачи из `$ARGUMENTS`:

### Тип A — Переопределить WC-шаблон

Найти нужный файл в `../PARENT_SLUG/woocommerce/` и создать его копию в `./woocommerce/` с тем же путём.

**Приоритет поиска шаблонов:**
```
1. my-child/woocommerce/          ← создавать здесь
2. codeweber/woocommerce/         ← брать за основу ЭТОТ файл
3. woocommerce/templates/ (плагин)
```

**Все переопределения родительской темы:**
```
woocommerce/
├── archive-product.php              ← магазин (обёртка + сайдбар)
├── content-product.php              ← ДИСПЕТЧЕР карточки товара (!)
├── content-quick-view.php           ← quick view модальное окно
├── single-product.php               ← страница товара
├── single-product-reviews.php       ← форма и список отзывов
├── single-product/
│   ├── price.php, rating.php, meta.php, short-description.php, review.php
│   └── add-to-cart/ (simple.php, variable.php, variation-add-to-cart-button.php)
├── loop/ (orderby.php, pagination.php, result-count.php)
├── global/quantity-input.php
├── notices/ (error.php, success.php, notice.php)
├── myaccount/ (dashboard, navigation, orders, form-*.php ...)
├── order/ (order-details.php, order-details-customer.php)
└── emails/ (email-header, email-footer, customer-*.php, plain/, block/)
```

### Тип B — Карточка товара (новая или переопределение)

**Как работает система карточек:**
```
WooCommerce → content-product.php (диспетчер)
    → Redux: archive_template_select_product = "shop2"
        → get_template_part('templates/woocommerce/cards/{template}')
            → Дочерняя тема проверяется первой
```

Расположение карточек: `templates/woocommerce/cards/`

- **Переопределить** `shop2.php`: создать `./templates/woocommerce/cards/shop2.php` в дочерней
- **Новая карточка**: создать `./templates/woocommerce/cards/{name}.php` + подключить через Redux или переопределить `content-product.php`

### Тип C — Страница товара (single-product)

Переопределить `single-product.php` или части из `single-product/`. Читать текущую версию из родителя перед изменением.

### Тип D — Страница магазина (archive-product)

Переопределить `archive-product.php`. Поддерживает Redux: sidebar_position, шаблон карточек.

---

## Шаг 5: Проверка существующих файлов

Перед созданием проверить, нет ли уже переопределения в дочерней теме по нужному пути.

---

## Шаг 6: План

Покажи таблицу:

| Действие | Файл | Откуда взять основу |
|----------|------|---------------------|
| Создать | `./woocommerce/single-product/price.php` | `../codeweber/woocommerce/single-product/price.php` |

**Дождись подтверждения пользователя.**

---

## Шаг 7: Реализация

### Правила для WC-шаблонов

- Всегда начинать с `defined( 'ABSPATH' ) || exit;`
- Версия шаблона в комментарии: `@version X.X.X` (из оригинала)
- Bootstrap-классы для стилей, не inline CSS
- `Codeweber_Options::style('card-radius')` — скругление из Redux
- `Codeweber_Options::style('button')` — стиль кнопок из Redux

### Переменные доступные в карточке товара

```php
global $product;   // WC_Product — уже установлен, loop запущен

$product_id      = $product->get_id();
$product_url     = get_permalink( $product_id );
$image_html      = $product->get_image( 'woocommerce_thumbnail' );
$gallery_ids     = $product->get_gallery_image_ids();  // для hover-swap
$add_to_cart_url = $product->add_to_cart_url();
$price_html      = $product->get_price_html();
$is_simple       = $product->is_type( 'simple' );
$card_radius     = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';
```

**Паттерны из `shop2.php` — использовать как образец:**
- Sale / New значки с настройками из Redux (`woo_badge_*`)
- Wishlist-кнопка (`cw-wishlist-btn`, режим `cw_wishlist_render`)
- Quick view кнопка (`item-view`, `data-product-id`)
- AJAX добавление в корзину (`ajax_add_to_cart`)

### Добавить новую карточку в Redux-селектор

```php
// В functions.php дочерней темы:
add_filter( 'redux/options/redux_demo/field/archive_template_select_product/options', function ( $options ) {
    $options['mycard'] = __( 'My Card', 'TEXT_DOMAIN' );
    return $options;
} );
```

### Безопасность

- `$_POST`/`$_GET` — только через `sanitize_*( wp_unslash(...) )`
- Весь вывод — через `esc_html()`, `esc_url()`, `esc_attr()`
- Строки — через `esc_html__( '', 'TEXT_DOMAIN' )`

---

## Шаг 8: Сборка

```bash
# Из директории родительской темы:
npm run build
```

Если сборка упала — остановись и сообщи об ошибке.

---

## Шаг 9: Отчёт

- Что сделано
- Полные пути всех файлов
- Что проверить в браузере (страница товара / магазина / отзывы)

---

## Шаг 10: Переводы

Если добавлены новые строки в `__()` / `_e()` — добавить в языковые файлы дочерней темы.

```bash
wp i18n make-mo languages/ru_RU.po
```

> Пропустить если новых переводимых строк нет.

---

## Шаг 11: Коммит

Тип сообщения:

| Задача | Тип |
|--------|-----|
| Новый файл/функционал | `feat` |
| Изменение существующего | `fix` или `refactor` |
| Только стили | `style` |

Формат: `feat(woo): краткое описание`
