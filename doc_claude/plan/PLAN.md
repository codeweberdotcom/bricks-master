# План разработки темы CodeWeber

Актуально на: 2026-03-15

---

## 1. WooCommerce — шаблоны

Создать полный набор шаблонов WooCommerce под тему CodeWeber (Bootstrap 5).

- [ ] Страница магазина (`shop.php` / `archive-product.php`)
- [ ] Страница товара (`single-product.php`)
- [ ] Корзина (`cart.php`)
- [ ] Оформление заказа (`checkout.php`)
- [ ] Личный кабинет (`my-account.php`)
- [ ] Страница спасибо (`order-received.php`)
- [ ] Страница категории товаров (`taxonomy-product_cat.php`)
- [ ] Поиск по товарам
- [ ] Виджеты (корзина в шапке, фильтры)

---

## 2. WooCommerce — карточки товаров

Создать систему карточек по аналогии с `templates/post-cards/`.

- [ ] Карточка по умолчанию (grid)
- [ ] Карточка горизонтальная (list)
- [ ] Карточка минималистичная
- [ ] Карточка с hover-эффектом (zoom / overlay)
- [ ] Карточка для каталога (крупная, с характеристиками)

---

## 3. Правила для child темы ✅

Документация полностью готова:
- `doc_claude/architecture/CHILD_THEME_GUIDE.md` — настройка с нуля, Gulp, assets, deploy
- `doc_claude/architecture/CHILD_THEME_AI_RULES.md` — CPT, шаблоны, WooCommerce, блоки, sidebar

### CPT (Custom Post Types)
- [x] Как добавить новый CPT в child теме (без изменения parent)
- [x] Шаблон `archive-{cpt}.php` в child
- [x] Шаблон `single-{cpt}.php` в child (два варианта: делегация + самостоятельный)
- [x] Карточки для нового CPT в `templates/post-cards/`

### Sidebar
- [x] Как зарегистрировать новый sidebar в child теме
- [x] Как переопределить шаблон с sidebar
- [x] Sidebar-виджеты: добавить/удалить/заменить через хуки

### Новый функционал
- [x] Паттерн подключения новых PHP-модулей через `functions.php` child темы
- [x] Переопределение хуков и фильтров parent темы из child

### JS-библиотеки
- [x] Как правильно подключить новую JS-библиотеку через `functions.php` child темы
- [x] Как добавить свой SCSS/JS в сборку (Gulp setup для child)
- [x] Паттерн инициализации JS-плагинов в child теме

### Дополнительно (покрыто)
- [x] Кастомные Gutenberg-блоки в child (namespace, block.json, register)
- [x] WooCommerce шаблоны и карточки товаров в child
- [x] Чеклист перед коммитом

---

## 4. WooCommerce — слайдер галереи single product (Redux)

**Статус:** ✅ реализован (commit 1b3e55c)  
**Актуально на:** 2026-04-07

### Анализ

- **Шаблон:** `woocommerce/single-product.php:91` — swiper-container галереи товара захардкожен, нет `data-thumbs-direction` и `data-thumbs-items`
- **JS:** `src/assets/js/theme.js:526-544` — уже читает `data-thumbs-direction` (horizontal/vertical) и `data-thumbs-items` (число), добавляет класс `swiper-thumbs-v` при vertical. **Новый JS не нужен.**
- **Redux:** нет ни одной настройки для галереи single product

### Что добавить

**Шаг 1 — Redux** (`redux-framework/sample/sections/codeweber/woocommerce.php`):
Подсекция "Галерея товара":

- `woo_gallery_thumbs_direction` — `button_set`: `horizontal` / `vertical` (default: `horizontal`)
- `woo_gallery_thumbs_items` — `slider` или `text`: 3–6 (default: `5`)

**Шаг 2 — Шаблон** (`woocommerce/single-product.php`):

```php
$thumbs_dir   = Codeweber_Options::get('woo_gallery_thumbs_direction') ?: 'horizontal';
$thumbs_items = Codeweber_Options::get('woo_gallery_thumbs_items') ?: '5';
// Добавить в swiper-container:
// data-thumbs-direction="<?= esc_attr($thumbs_dir) ?>"
// data-thumbs-items="<?= esc_attr($thumbs_items) ?>"
```

**Итого:** 2 файла, ~20 строк. Никакого нового JS/CSS.

---

## Приоритет

| Задача | Приоритет |
|--------|-----------|
| WooCommerce галерея: Redux thumbs direction/items | 🔴 Высокий |
| Правила child темы (документация) | ✅ Готово |
| WooCommerce карточки товаров | 🟡 Средний |
| WooCommerce шаблоны (полный набор) | 🟡 Средний |
