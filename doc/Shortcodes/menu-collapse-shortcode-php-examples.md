# Три типа меню [menu_collapse] — шорткод и PHP (wp_nav_menu)

Все примеры с **demo="true"** (демо-меню подставляется фильтром). Для реального меню замените на `menu="4"` (ID или slug) и `demo="false"`.

---

## Тип 1 — компактный (menu-collapse-1)

**Шорткод со всеми атрибутами:**
```
[menu_collapse demo="true" depth="0" theme="dark" list_type="1" container_class="" top_level_class="" top_level_class_start="" top_level_class_end="" item_class="" link_class=""]
```

**PHP (wp_nav_menu):**
```php
<?php
wp_nav_menu( array(
	'menu'                    => 999999,
	'depth'                   => 0,
	'container'               => 'nav',
	'container_class'         => 'navbar-vertical menu-collapse-nav navbar-vertical-dark',
	'container_id'            => 'menu-collapse-walker-demo-1',
	'menu_class'              => 'navbar-nav menu-collapse-1',
	'menu_id'                 => '',
	'fallback_cb'             => false,
	'items_wrap'              => '<ul id="%1$s" class="%2$s">%3$s</ul>',
	'item_spacing'            => 'discard',
	'walker'                  => new CodeWeber_Menu_Collapse_Walker(),
	'wrapper_id'              => 'menu-collapse-walker-demo-1',
	'instance_suffix'         => '1',
	'theme_class'             => 'text-white',
	'top_level_class'         => '',
	'top_level_class_start'   => '',
	'top_level_class_end'     => '',
	'top_level_count'         => 0,
	'item_class'              => '',
	'link_class'              => '',
	'demo'                    => true,
) );
?>
```

---

## Тип 2 — с рамками, карточки (menu-collapse-2)

**Шорткод со всеми атрибутами:**
```
[menu_collapse demo="true" depth="0" theme="dark" list_type="2" container_class="" top_level_class="" top_level_class_start="" top_level_class_end="" item_class="" link_class=""]
```

**PHP (wp_nav_menu):**
```php
<?php
wp_nav_menu( array(
	'menu'                    => 999999,
	'depth'                   => 0,
	'container'               => 'nav',
	'container_class'         => 'navbar-vertical menu-collapse-nav navbar-vertical-dark',
	'container_id'            => 'menu-collapse-walker-demo-2',
	'menu_class'              => 'navbar-nav menu-collapse-2',
	'menu_id'                 => '',
	'fallback_cb'             => false,
	'items_wrap'              => '<ul id="%1$s" class="%2$s">%3$s</ul>',
	'item_spacing'            => 'discard',
	'walker'                  => new CodeWeber_Menu_Collapse_Walker(),
	'wrapper_id'              => 'menu-collapse-walker-demo-2',
	'instance_suffix'         => '2',
	'theme_class'             => 'text-white',
	'top_level_class'         => '',
	'top_level_class_start'   => '',
	'top_level_class_end'     => '',
	'top_level_count'         => 0,
	'item_class'              => '',
	'link_class'              => '',
	'demo'                    => true,
) );
?>
```

---

## Тип 3 — контейнер с рамкой и разделителями (menu-collapse-3)

**Шорткод со всеми атрибутами:**
```
[menu_collapse demo="true" depth="0" theme="dark" list_type="3" container_class="" top_level_class="" top_level_class_start="" top_level_class_end="" item_class="" link_class=""]
```

**PHP (wp_nav_menu):**
```php
<?php
wp_nav_menu( array(
	'menu'                    => 999999,
	'depth'                   => 0,
	'container'               => 'nav',
	'container_class'         => 'navbar-vertical menu-collapse-nav navbar-vertical-dark',
	'container_id'            => 'menu-collapse-walker-demo-3',
	'menu_class'              => 'navbar-nav menu-collapse-3',
	'menu_id'                 => '',
	'fallback_cb'             => false,
	'items_wrap'              => '<ul id="%1$s" class="%2$s">%3$s</ul>',
	'item_spacing'            => 'discard',
	'walker'                  => new CodeWeber_Menu_Collapse_Walker(),
	'wrapper_id'              => 'menu-collapse-walker-demo-3',
	'instance_suffix'         => '3',
	'theme_class'             => 'text-white',
	'top_level_class'         => '',
	'top_level_class_start'   => '',
	'top_level_class_end'     => '',
	'top_level_count'         => 0,
	'item_class'              => '',
	'link_class'              => '',
	'demo'                    => true,
) );
?>
```

---

## Сводка отличий по типам

| Параметр           | Тип 1 | Тип 2 | Тип 3 |
|--------------------|-------|-------|-------|
| **list_type**      | `1`   | `2`   | `3`   |
| **menu_class**     | `navbar-nav menu-collapse-1` | `navbar-nav menu-collapse-2` | `navbar-nav menu-collapse-3` |

Остальные аргументы совпадают; стили задаются в `_nav.scss` по классу на `<ul>`.

Для **theme="light"** в PHP: `container_class` → `'navbar-vertical menu-collapse-nav navbar-vertical-light'`, `theme_class` → `'text-dark'`.  
Для **theme="default"**: без `navbar-vertical-dark`/`navbar-vertical-light`, `theme_class` → `''`.

Доп. атрибуты шорткода в PHP:
- **container_class** — добавить строку к `container_class`.
- **top_level_class**, **top_level_class_start**, **top_level_class_end** — передать в аргументы с теми же именами.
- **item_class**, **link_class** — передать в аргументы с теми же именами.
- **depth** — число в аргумент `depth`.
