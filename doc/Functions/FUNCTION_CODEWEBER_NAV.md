# Функция `codeweber_nav()`

Универсальный вывод навигации из таксономии или типа записи (CPT) в виде Bootstrap Collapse. Разметка совпадает с шорткодом `[menu_collapse]` и Walker'ом `CodeWeber_Menu_Collapse_Walker`. Не требует создания меню в админке WordPress.

**Файл:** `functions/codeweber-nav.php`

---

## Сигнатура

```php
codeweber_nav( string $source, string $name, array $args = [] ) : string
```

| Параметр | Тип   | Описание |
|----------|--------|----------|
| **$source** | string | Источник: `'tax'` (таксономия) или `'cpt'` (тип записи) |
| **$name**   | string | Slug таксономии (например `category`, `product_cat`) или slug типа записи (`post`, `product`, `page`) |
| **$args**   | array  | Необязательный массив аргументов. Если не передан или пустой — выводится стандартная collapse-вёрстка со всеми уровнями |

**Возвращает:** HTML-строка `<nav>` с меню или пустая строка при ошибке/пустом дереве.

---

## Аргументы по умолчанию

Если `$args` не задан или задан частично, используются значения по умолчанию (стандартная collapse со всеми уровнями):

| Ключ | По умолчанию | Описание |
|------|--------------|----------|
| **depth** | `0` | Глубина меню: `0` — все уровни, `1` — только верхний, `2` — верхний + один подуровень и т.д. |
| **theme** | `'default'` | Тема: `'default'`, `'dark'` (navbar-vertical-dark, text-white), `'light'` (navbar-vertical-light, text-dark) |
| **list_type** | `'1'` | Класс списка: `menu-collapse-1`, `menu-collapse-2`, `menu-collapse-3` |
| **container_class** | `''` | Дополнительные классы для `<nav>` |
| **item_class** | `''` | Дополнительные классы для `<li>` |
| **link_class** | `''` | Дополнительные классы для ссылок |
| **top_level_class** | `''` | Классы для пунктов верхнего уровня (кроме первого/последнего) |
| **top_level_class_start** | `''` | Классы только для первого пункта верхнего уровня |
| **top_level_class_end** | `''` | Классы только для последнего пункта верхнего уровня |
| **hide_empty** | `false` | Только для таксономий: скрывать ли пустые термины |
| **wrapper_id** | авто | Уникальный `id` для `<nav>` (генерируется, если не задан) |
| **menu_class** | по list_type | Класс для `<ul>` (по умолчанию `navbar-nav list-unstyled menu-collapse-{list_type}`) |

---

## Примеры использования

### Таксономия, стандартная вёрстка (все уровни)

```php
// Рубрики (Categories)
echo codeweber_nav( 'tax', 'category' );

// Категории WooCommerce
echo codeweber_nav( 'tax', 'product_cat' );

// С пустым массивом — то же самое
echo codeweber_nav( 'tax', 'product_cat', [] );
```

### CPT, стандартная вёрстка

```php
// Страницы (иерархия по post_parent)
echo codeweber_nav( 'cpt', 'page' );

// Записи (один уровень)
echo codeweber_nav( 'cpt', 'post' );

// Товары WooCommerce (один уровень, если product не иерархический)
echo codeweber_nav( 'cpt', 'product' );
```

### С ограничением глубины и темой

```php
echo codeweber_nav( 'tax', 'category', [
	'depth'  => 2,
	'theme'  => 'dark',
] );
```

### С дополнительными классами

```php
echo codeweber_nav( 'tax', 'product_cat', [
	'container_class' => 'my-sidebar-nav',
	'item_class'      => 'mb-1',
	'link_class'      => 'text-reset',
] );
```

### Скрытие пустых терминов (только для таксономий)

```php
echo codeweber_nav( 'tax', 'category', [
	'hide_empty' => true,
] );
```

---

## Разметка

- Контейнер: `<nav id="..." class="navbar-vertical menu-collapse-nav [navbar-vertical-dark|navbar-vertical-light]">`
- Список: `<ul class="navbar-nav list-unstyled menu-collapse-1|2|3">`
- Пункты: `<li class="nav-item parent-collapse-item parent-item [collapse-has-children] [current-menu-item]">`
- Пункты с детьми: ссылка + кнопка `data-bs-toggle="collapse"` + `<div class="collapse">` с вложенным `<ul>`
- Текущая страница/термин: класс `current-menu-item` и `aria-current="page"` на ссылке
- Ветка, содержащая текущий элемент, раскрыта по умолчанию (`collapse show`)

---

## Вспомогательные функции (внутренние)

В том же файле объявлены функции для построения дерева и рендера (при необходимости расширения темы):

- `codeweber_nav_default_args()` — возвращает массив аргументов по умолчанию
- `codeweber_nav_build_tree_tax( $name, $args )` — дерево из терминов таксономии
- `codeweber_nav_build_tree_cpt( $name, $args )` — дерево из записей CPT (иерархия для hierarchical post types)
- `codeweber_nav_has_current_in_subtree( $by_parent, $parent_id )` — есть ли текущая страница в поддереве
- `codeweber_nav_render_collapse( ... )` — рекурсивный вывод HTML уровня меню

---

## Отличие от меню WordPress и шорткода [menu_collapse]

| | codeweber_nav() | wp_nav_menu() / [menu_collapse] |
|--|-----------------|----------------------------------|
| Источник данных | Таксономия или CPT (get_terms / get_posts) | Сохранённое меню в админке (Appearance → Menus) |
| Настройка пунктов | Не нужна: пункты формируются автоматически | Нужно создать меню и добавить пункты вручную |
| Вёрстка | Та же collapse (navbar-vertical, menu-collapse-*) | Та же collapse при использовании Walker / шорткода |

Используйте `codeweber_nav()`, когда нужно вывести навигацию по рубрикам, категориям товаров или списку записей CPT без создания меню в WordPress.
