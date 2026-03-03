# SHORTCODE_BREADCRUMBS

## Общее описание

Шорткод `[breadcrumbs]` выводит навигационную цепочку (хлебные крошки). Поддерживает интеграцию с популярными SEO-плагинами: Rank Math, Yoast SEO, SEOPress, All in One SEO. При отсутствии плагинов используется встроенная реализация.

---

## Исходный код

**Файл:** [`functions/breadcrumbs.php`](functions/breadcrumbs.php:311)

```php
add_shortcode('breadcrumbs', 'breadcrumbs_shortcode');

function breadcrumbs_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'align' => 'left',
        'color' => '',
        'class' => ''
    ), $atts, 'breadcrumbs');

    ob_start();
    get_breadcrumbs($atts['align'], $atts['color'], $atts['class']);
    return ob_get_clean();
}
```

---

## Параметры шорткода

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `align` | string | `left` | Выравнивание: `left`, `center`, `right` |
| `color` | string | `''` | Цвет текста: `white`, `dark` |
| `class` | string | `''` | Дополнительные CSS классы |

---

## Использование

### Базовый вывод
```php
[breadcrumbs]
```

### По центру
```php
[breadcrumbs align="center"]
```

### Белый текст справа
```php
[breadcrumbs align="right" color="white"]
```

### С кастомным классом
```php
[breadcrumbs class="mb-4 custom-class"]
```

---

## Поддерживаемые плагины

| Плагин | Функция |
|--------|---------|
| Rank Math SEO | `rank_math_the_breadcrumbs()` |
| Yoast SEO | `yoast_breadcrumb()` |
| SEOPress | `seopress_display_breadcrumbs()` |
| All in One SEO | `aioseo_breadcrumbs()` |

---

## Функция get_breadcrumbs()

**Файл:** [`functions/breadcrumbs.php`](functions/breadcrumbs.php:22)

```php
function get_breadcrumbs($align = null, $color = null, $class = null)
```

### Параметры

| Параметр | Тип | Описание |
|----------|-----|----------|
| `$align` | string | Выравнивание: left, center, right |
| `$color` | string | Цвет: white, dark |
| `$class` | string | Дополнительные классы |

---

## HTML структура

```html
<nav class="d-inline-block w-100" aria-label="breadcrumb">
    <ol class="breadcrumb justify-content-start">
        <li class="breadcrumb-item"><a href="/">Главная</a></li>
        <li class="breadcrumb-item"><a href="/category/">Категория</a></li>
        <li class="breadcrumb-item active" aria-current="page">Текущая страница</li>
    </ol>
</nav>
```

---

## CSS классы

| Класс | Описание |
|-------|----------|
| `.breadcrumb` | Основной класс списка |
| `.breadcrumb-item` | Элемент крошки |
| `.justify-content-start` | Выравнивание слева |
| `.justify-content-center` | Выравнивание по центру |
| `.justify-content-end` | Выравнивание справа |
| `.text-white` | Белый текст |
| `.text-dark` | Тёмный текст |

---

## Связанные шорткоды

- [`[universal_title]`](SHORTCODE_UNIVERSAL_TITLE.md) — универсальный заголовок
- [`[address]`](SHORTCODE_ADDRESS.md) — адрес
- [`[social_links]`](SHORTCODE_SOCIAL_LINKS.md) — социальные иконки
