# Руководство по использованию Nav Walker в теме Codeweber

В теме подключены два класса Walker для вывода меню через `wp_nav_menu()`: **WP_Bootstrap_Navwalker** (горизонтальное меню, dropdown, Mega Menu) и **CodeWeber_Menu_Collapse_Walker** (вертикальное меню с Bootstrap Collapse). Оба подключаются в `functions.php`.

---

## 1. Обзор

| Walker | Назначение | Где используется |
|--------|------------|-------------------|
| **WP_Bootstrap_Navwalker** | Горизонтальное меню в шапке/футере: пункты, dropdown’ы, Mega Menu | Шаблоны header-*, footer-*.php |
| **CodeWeber_Menu_Collapse_Walker** | Вертикальное меню-аккордеон (ссылка + кнопка раскрытия, `div.collapse`) | Шорткод `[menu_collapse]` |

Оба расширяют `Walker_Nav_Menu` и передаются в `wp_nav_menu()` в аргументе `walker`.

---

## 2. WP_Bootstrap_Navwalker

### Когда использовать

- Главное меню в навбаре (горизонтальное).
- Меню в футере (обычно один уровень).
- Offcanvas-меню на мобильных (если нужна та же разметка).
- Нужны dropdown’ы или Mega Menu (контент из Redux HTML-блоков).

### Стандартные аргументы wp_nav_menu()

Используются обычные ключи `wp_nav_menu()`:

- **theme_location** — слот меню (например `primary`, `footer`, `offcanvas`).
- **menu** — ID или slug меню, если не используете `theme_location`.
- **depth** — глубина вложенности: `1` только верхний уровень, `0` или `4` — с подменю.
- **container** — обёртка корневого `<ul>` (например `''` или `'nav'`).
- **container_class**, **container_id** — класс и ID обёртки.
- **menu_class** — класс корневого `<ul>` (например `navbar-nav`, `list-unstyled`).
- **fallback_cb** — callback, если меню не назначено; в теме часто `'WP_Bootstrap_Navwalker::fallback'`.
- **walker** — экземпляр `new WP_Bootstrap_Navwalker()`.

### Пример: главное меню в шапке

```php
wp_nav_menu(
    array(
        'theme_location'  => $config['mainMenuName'],  // например 'primary'
        'depth'           => 4,
        'container'       => '',
        'container_class' => '',
        'container_id'    => '',
        'menu_class'      => $config['mainMenuClass'],  // например 'navbar-nav'
        'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
        'walker'          => new WP_Bootstrap_Navwalker(),
    )
);
```

### Пример: меню в футере (один уровень)

```php
wp_nav_menu(
    array(
        'theme_location'  => 'footer',
        'depth'           => 1,
        'container'       => 'nav',
        'container_class' => '...',
        'menu_class'      => 'list-unstyled ...',
        'walker'          => new WP_Bootstrap_Navwalker(),
    )
);
```

### Mega Menu

- В админке WordPress (Внешний вид → Меню) у пунктов верхнего уровня есть чекбокс «Mega Menu» (логика в `functions/menu.php` и `functions/admin/admin_menu.php`).
- Если у пункта включён Mega Menu, Walker выводит подменю в разметке Mega Menu (dropdown-mega, контент из html_blocks и т.д.).
- Дополнительные классы для dropdown (например скругление) могут браться из Redux (`getThemeCardImageRadius`).

---

## 3. CodeWeber_Menu_Collapse_Walker

### Когда использовать

- Вертикальное меню с раскрывающимися подуровнями (Bootstrap Collapse / accordion).
- Разметка: у пунктов с детьми — ссылка + кнопка `.btn-collapse` с `data-bs-toggle="collapse"`, подменю в `div.collapse` с `data-bs-parent`.
- В теме используется в шорткоде `[menu_collapse]`; тот же Walker можно вызвать напрямую через `wp_nav_menu()` в своих шаблонах.

### Обязательные кастомные аргументы

Эти ключи передаются в массиве аргументов `wp_nav_menu()` и считываются Walker’ом:

| Аргумент | Тип | Описание |
|----------|-----|----------|
| **wrapper_id** | string | ID контейнера `<nav>` (или обёртки корневого уровня). Используется как `data-bs-parent` для подменю верхнего уровня. Должен совпадать с `container_id`. |
| **instance_suffix** | string | Суффикс для уникальных `id` collapse-блоков на странице (если меню выводится несколько раз). |

### Опциональные кастомные аргументы

| Аргумент | Тип | Описание |
|----------|-----|----------|
| **depth** | int | Глубина меню (0 = без ограничения). Передаётся как обычный аргумент `depth` в `wp_nav_menu()`. |
| **theme_class** | string | Класс для ссылок (тема/цвет), например `text-dark`, `text-white`. |
| **item_class** | string | Дополнительные классы для `<li>`. |
| **link_class** | string | Дополнительные классы для `<a>`. |
| **top_level_class** | string | Классы для всех пунктов верхнего уровня (depth 0). |
| **top_level_class_start** | string | Классы только для первого пункта верхнего уровня. |
| **top_level_class_end** | string | Классы только для последнего пункта верхнего уровня. |
| **top_level_count** | int | Количество пунктов верхнего уровня (нужно для корректной выдачи start/end классов). |

### Стандартные аргументы wp_nav_menu()

- **menu** — ID или slug меню.
- **container** — например `'nav'`.
- **container_class** — например `navbar-vertical menu-collapse-nav`.
- **container_id** — тот же ID, что и в `wrapper_id`.
- **menu_class** — класс корневого `<ul>` и вложенных списков.
- **echo** — `false`, если нужна строка вывода.
- **fallback_cb** — например `false`.

### Пример вызова (как в шорткоде [menu_collapse])

```php
$wrapper_id    = 'menu-collapse-nav-' . ( $suffix ?: '1' );
$nav_args = array(
    'menu'                    => $menu_id,
    'depth'                   => 4,
    'container'                => 'nav',
    'container_class'          => 'navbar-vertical menu-collapse-nav',
    'container_id'             => $wrapper_id,
    'menu_class'               => 'navbar-nav list-unstyled',
    'menu_id'                  => '',
    'fallback_cb'              => false,
    'echo'                     => false,
    'items_wrap'               => '<ul id="%1$s" class="%2$s">%3$s</ul>',
    'item_spacing'             => 'discard',
    'walker'                   => new CodeWeber_Menu_Collapse_Walker(),
    'wrapper_id'               => $wrapper_id,
    'instance_suffix'          => $suffix,
    'theme_class'              => 'text-dark',
    'top_level_class'          => '',
    'top_level_class_start'    => '',
    'top_level_class_end'      => '',
    'top_level_count'          => $top_level_count,
    'item_class'               => '',
    'link_class'               => 'nav-link',
);

$output = wp_nav_menu( $nav_args );
```

### Несколько меню на странице

Если выводите несколько вертикальных collapse-меню на одной странице, задайте разный **container_id** и **wrapper_id** и передайте уникальный **instance_suffix**, чтобы у каждого блока collapse были свои `id` и не конфликтовал `data-bs-parent`.

---

## 4. Подключение классов

Оба Walker’а подключаются в `functions.php`:

```php
require_once get_template_directory() . '/functions/lib/class-wp-bootstrap-navwalker.php';
require_once get_template_directory() . '/functions/lib/class-codeweber-menu-collapse-walker.php';
```

Дополнительно подключать ничего не нужно.

---

## 5. Связанные документы

- **[SHORTCODE_MENU_COLLAPSE](Shortcodes/SHORTCODE_MENU_COLLAPSE.md)** — шорткод `[menu_collapse]`, внутри которого используется `CodeWeber_Menu_Collapse_Walker`.
- **Файлы Walker’ов:**  
  - `functions/lib/class-wp-bootstrap-navwalker.php`  
  - `functions/lib/class-codeweber-menu-collapse-walker.php`
