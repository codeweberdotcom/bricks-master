# SHORTCODE_MENU_LIST

## Общее описание

Шорткод `[menu_list]` выводит список всех зарегистрированных меню сайта с их ID, slug, названием и привязкой к областям темы.

---

## Исходный код

**Файл:** [`functions/shortcodes.php`](functions/shortcodes.php:129)

```php
add_shortcode('menu_list', function ($atts) {
    $atts = shortcode_atts([
        'format' => 'list',
    ], $atts, 'menu_list');

    $menus = wp_get_nav_menus();
    if (empty($menus)) {
        return '<p>' . esc_html__('No menus created yet. Create menus in Appearance → Menus.', 'codeweber') . '</p>';
    }

    $locations = get_registered_nav_menus();
    $assigned  = get_nav_menu_locations();

    $out = '';
    if ($atts['format'] === 'table') {
        // Table format output
        // ...
    } else {
        // List format output
        // ...
    }

    $out .= '<p><strong>Registered theme locations:</strong> header, header_1, offcanvas, footer, footer_1, footer_2, footer_3, footer_4</p>';
    return $out;
});
```

---

## Параметры шорткода

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `format` | string | `list` | Формат вывода: `list` (маркированный список) или `table` (таблица) |

---

## Использование

```php
[menu_list]
[menu_list format="list"]
[menu_list format="table"]
```

---

## Форматы вывода

### format="list" (по умолчанию)
Выводит маркированный список меню:
```html
<ul class="menu-list">
    <li>ID: 4, slug: main-menu, name: Main Menu → location: header, footer</li>
    <li>ID: 5, slug: top-menu, name: Top Menu → location: header_1</li>
</ul>
```

### format="table"
Выводит таблицу меню:
```html
<table class="menu-list-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Slug</th>
            <th>Name</th>
            <th>Theme location</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>4</td>
            <td><code>main-menu</code></td>
            <td>Main Menu</td>
            <td>header (Header Menu), footer (Footer Menu)</td>
        </tr>
    </tbody>
</table>
```

---

## Зарегистрированные области темы

Тема CodeWeber поддерживает следующие области меню:

| Location | Описание |
|----------|----------|
| `header` | Основное меню в хедере |
| `header_1` | Дополнительное меню в хедере |
| `offcanvas` | Меню в offcanvas-панели |
| `footer` | Основное меню в футере |
| `footer_1` | Меню футера 1 |
| `footer_2` | Меню футера 2 |
| `footer_3` | Меню футера 3 |
| `footer_4` | Меню футера 4 |

---

## Примеры использования

### Простой вывод списка
```php
[menu_list]
```

### Вывод в таблице
```php
[menu_list format="table"]
```

---

## Использование в коде

### Получение меню по slug
```php
wp_nav_menu([
    'menu' => 'main-menu',
    'theme_location' => 'header'
]);
```

### Получение меню по ID
```php
wp_nav_menu([
    'menu' => 4
]);
```

---

## Связанные шорткоды

- [`[menu_collapse]`](SHORTCODE_MENU_COLLAPSE.md) — вертикальное меню с аккордеоном
- [`[site_domain]`](SHORTCODE_SITE_DOMAIN.md) — домен сайта

---

## Связанные функции

| Функция | Файл | Описание |
|---------|------|----------|
| `wp_get_nav_menus()` | WordPress Core | Получение всех меню |
| `get_registered_nav_menus()` | WordPress Core | Получение зарегистрированных областей |
| `get_nav_menu_locations()` | WordPress Core | Получение привязок меню к областям |

---

## WordPress хуки

### wp_nav_menu_objects
Фильтр `wp_nav_menu_objects` используется для демо-меню в шорткоде `menu_collapse`:
```php
add_filter('wp_nav_menu_objects', 'codeweber_menu_collapse_demo_items', 10, 2);
```
