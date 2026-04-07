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

## Приоритет

| Задача | Приоритет |
|--------|-----------|
| Правила child темы (документация) | 🔴 Высокий |
| WooCommerce карточки товаров | 🟡 Средний |
| WooCommerce шаблоны (полный набор) | 🟡 Средний |
