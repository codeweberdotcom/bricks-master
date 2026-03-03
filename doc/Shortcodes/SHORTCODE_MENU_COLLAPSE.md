# Шорткод `[menu_collapse]`

Выводит выбранное **WP-меню** в виде **вертикального списка с Bootstrap Collapse (accordion)** — пункты с подменю раскрываются по клику. Подходит для сайдбара. Цвет текста задаётся параметром `theme` (как в блоке Menu).

**Подробно:** код и комментарии в `functions/shortcodes.php`, Walker в `functions/lib/class-codeweber-menu-collapse-walker.php`.

---

## Тема (цвет)

| Параметр | Значение | Назначение |
|----------|----------|------------|
| **theme** | `default` (по умолчанию) | Без дополнительного класса цвета |
| **theme** | `dark` | Светлый текст (text-white), для тёмного фона |
| **theme** | `light` | Тёмный текст (text-dark), для светлого фона |

---

## Все параметры

| Параметр | По умолчанию | Назначение | Варианты |
|----------|--------------|------------|----------|
| **menu** | — (обязательный при demo=false) | ID или slug меню WordPress | `4`, `"main-menu"` |
| **demo** | `false` | `true` — вывести демо-меню (menu не нужен), все атрибуты применяются к нему | `true` / `false` |
| **depth** | `0` | Глубина вложенности. `0` = без ограничения | 0, 1, 2, 3… |
| **theme** | `default` | Цвет текста (как в блоке Menu) | `default` / `dark` / `light` |
| **list_type** | `1` | Тип оформления списка: на `<ul>` добавляется класс `menu-collapse-1`, `menu-collapse-2` или `menu-collapse-3`. Стили и переменные в `_nav.scss` и `_variables.scss` (NAVBAR VERTICAL). | `1` (компактный) / `2` (с рамками, карточки) / `3` (контейнер с рамкой, разделители пунктов) |
| **container_class** | `""` | Доп. CSS-классы для контейнера `<nav>`. Контейнер всегда получает классы `navbar-vertical menu-collapse-nav`. | любые классы |
| **top_level_class** | `""` | Доп. CSS-классы только для пунктов верхнего уровня (depth 0). Не применяется к первому/последнему, если заданы start/end | любые классы |
| **top_level_class_start** | `""` | Класс только для первого пункта верхнего уровня; если задан, для него не применяется top_level_class | любые классы |
| **top_level_class_end** | `""` | Класс только для последнего пункта верхнего уровня; если задан, для него не применяется top_level_class | любые классы |
| **item_class** | `""` | Доп. CSS-классы для всех пунктов `<li>` | любые классы |
| **link_class** | `""` | Доп. CSS-классы для ссылок `<a>` | любые классы |

---

## 3 примера со всеми атрибутами (demo)

Во всех трёх используется `demo="true"` — меню создавать не нужно, подойдёт для проверки стилей и вставки в демо-страницы.

### 1. Тёмный фон

**Shortcode:**
```
[menu_collapse demo="true" depth="3" theme="dark" container_class="mb-4 p-3 rounded" top_level_class="border-bottom border-secondary mb-2 pb-2" item_class="py-1" link_class="fw-bold"]
```

**PHP (do_shortcode):**
```php
<?php
echo do_shortcode( '[menu_collapse demo="true" depth="3" theme="dark" container_class="mb-4 p-3 rounded" top_level_class="border-bottom border-secondary mb-2 pb-2" item_class="py-1" link_class="fw-bold"]' );
?>
```

**PHP (wp_nav_menu):**
```php
<?php
wp_nav_menu( array(
	'menu'                 => 999999,
	'depth'                => 3,
	'container'            => 'nav',
	'container_class'      => 'menu-collapse-nav mb-4 p-3 rounded',
	'container_id'         => 'menu-collapse-demo-1',
	'walker'               => new CodeWeber_Menu_Collapse_Walker(),
	'theme_class'          => 'text-white',
	'top_level_class'      => 'border-bottom border-secondary mb-2 pb-2',
	'top_level_class_start'=> '',
	'top_level_class_end'   => '',
	'item_class'           => 'py-1',
	'link_class'           => 'fw-bold',
	'wrapper_id'           => 'menu-collapse-demo-1',
	'instance_suffix'      => '1',
	'demo'                 => true,
) );
?>
```

### 2. Светлый фон, с рамкой

**Shortcode:**
```
[menu_collapse demo="true" depth="3" theme="light" container_class="mb-4 p-3 border rounded" top_level_class="border-bottom mb-2 pb-2" item_class="py-1" link_class="fw-bold"]
```

**PHP (do_shortcode):**
```php
<?php
echo do_shortcode( '[menu_collapse demo="true" depth="3" theme="light" container_class="mb-4 p-3 border rounded" top_level_class="border-bottom mb-2 pb-2" item_class="py-1" link_class="fw-bold"]' );
?>
```

**PHP (wp_nav_menu):**
```php
<?php
wp_nav_menu( array(
	'menu'                 => 999999,
	'depth'                => 3,
	'container'            => 'nav',
	'container_class'      => 'menu-collapse-nav mb-4 p-3 border rounded',
	'container_id'         => 'menu-collapse-demo-2',
	'walker'               => new CodeWeber_Menu_Collapse_Walker(),
	'theme_class'          => 'text-dark',
	'top_level_class'      => 'border-bottom mb-2 pb-2',
	'top_level_class_start'=> '',
	'top_level_class_end'   => '',
	'item_class'           => 'py-1',
	'link_class'           => 'fw-bold',
	'wrapper_id'           => 'menu-collapse-demo-2',
	'instance_suffix'      => '2',
	'demo'                 => true,
) );
?>
```

### 3. Светлый фон, компактный

**Shortcode:**
```
[menu_collapse demo="true" depth="3" theme="light" container_class="mb-4" top_level_class="border-bottom border-dark mb-1" item_class="py-1" link_class="text-decoration-none"]
```

**PHP (do_shortcode):**
```php
<?php
echo do_shortcode( '[menu_collapse demo="true" depth="3" theme="light" container_class="mb-4" top_level_class="border-bottom border-dark mb-1" item_class="py-1" link_class="text-decoration-none"]' );
?>
```

**PHP (wp_nav_menu):**
```php
<?php
wp_nav_menu( array(
	'menu'                 => 999999,
	'depth'                => 3,
	'container'            => 'nav',
	'container_class'      => 'menu-collapse-nav mb-4',
	'container_id'         => 'menu-collapse-demo-3',
	'walker'               => new CodeWeber_Menu_Collapse_Walker(),
	'theme_class'          => 'text-dark',
	'top_level_class'      => 'border-bottom border-dark mb-1',
	'top_level_class_start'=> '',
	'top_level_class_end'   => '',
	'item_class'           => 'py-1',
	'link_class'           => 'text-decoration-none',
	'wrapper_id'           => 'menu-collapse-demo-3',
	'instance_suffix'      => '3',
	'demo'                 => true,
) );
?>
```

---

### 4. С top_level_class_start / top_level_class_end (первый и последний пункт верхнего уровня)

**Shortcode:**
```
[menu_collapse demo="true" depth="4" theme="dark" container_class="border border-secondary px-3 rounded" top_level_class="border-bottom border-secondary py-3" item_class="pt-3" link_class="fw-bold" top_level_class_start="" top_level_class_end="py-3"]
```

**PHP (wp_nav_menu):**
```php
<?php
wp_nav_menu( array(
	'menu'                 => 999999,
	'depth'                => 4,
	'container'            => 'nav',
	'container_class'      => 'menu-collapse-nav border border-primary p-3 rounded',
	'container_id'         => 'menu-collapse-demo-1',
	'walker'               => new CodeWeber_Menu_Collapse_Walker(),
	'theme_class'          => 'text-white',
	'top_level_class'      => 'border-bottom border-secondary py-3',
	'top_level_class_start'=> '',
	'top_level_class_end'   => 'py-3',
	'item_class'           => 'pt-3',
	'link_class'           => 'fw-bold',
	'wrapper_id'           => 'menu-collapse-demo-1',
	'instance_suffix'      => '1',
	'demo'                 => true,
) );
?>
```

---

## Примеры использования (все с demo="true")

**Минимум:**
```
[menu_collapse demo="true"]
```

**Тёмный сайдбар (глубина 3):**
```
[menu_collapse demo="true" depth="3" theme="dark"]
```

**Светлая тема, жирные ссылки:**
```
[menu_collapse demo="true" theme="light" link_class="fw-bold"]
```

**Свои классы для контейнера и верхнего уровня:**
```
[menu_collapse demo="true" container_class="mb-4" top_level_class="border-bottom mb-2"]
```

**Пункты и ссылки:**
```
[menu_collapse demo="true" theme="dark" item_class="py-1" link_class="py-2"]
```

**PHP (do_shortcode):**
```php
<?php
echo do_shortcode( '[menu_collapse demo="true" depth="2" theme="dark"]' );
?>
```

**PHP (wp_nav_menu):**
```php
<?php
wp_nav_menu( array(
	'menu'                 => 999999,
	'depth'                => 2,
	'container'            => 'nav',
	'container_class'      => 'menu-collapse-nav',
	'container_id'         => 'menu-collapse-demo-sidebar',
	'walker'               => new CodeWeber_Menu_Collapse_Walker(),
	'theme_class'          => 'text-white',
	'top_level_class_start'=> '',
	'top_level_class_end'   => '',
	'wrapper_id'           => 'menu-collapse-demo-sidebar',
	'instance_suffix'      => '1',
	'demo'                 => true,
) );
?>
```
