# SHORTCODE_SOCIAL_LINKS

## Общее описание

Шорткод `[social_links]` выводит ссылки на социальные сети из настроек темы (Redux Framework). Является обёрткой над функцией [`social_links()`](functions/global.php:179).

---

## Исходный код

**Файл:** [`functions/shortcodes.php`](functions/shortcodes.php:65)

```php
add_shortcode('social_links', function ($atts) {
    if (!function_exists('social_links')) {
        return '<!-- Функция social_links не найдена -->';
    }
    
    $atts = shortcode_atts(array(
        'type' => 'type1',
        'size' => 'md',
        'class' => '',
        'button-color' => 'primary',
        'buttonstyle' => 'solid'
    ), $atts, 'social_links');
    
    return social_links($atts['class'], $atts['type'], $atts['size'], $atts['button-color'], $atts['buttonstyle']);
});
```

---

## Параметры шорткода

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `type` | string | `type1` | Тип отображения (type1-type9) |
| `size` | string | `md` | Размер кнопок/иконок (lg, md, sm) |
| `class` | string | `''` | Дополнительные CSS-классы |
| `button-color` | string | `primary` | Цвет кнопки для type8 |
| `buttonstyle` | string | `solid` | Стиль кнопки для type8 (solid/outline) |

---

## Типы отображения (type)

### type1 — Круглые кнопки с фоном
Каждая соцсеть отображается собственный цвет:
- `btn-facebook` — синий
- `btn-twitter` — голубой  
- `btn-linkedin` — тёмно-синий
- `btn-instagram` — градиент
- `btn-telegram` — голубой
- `btn-vk` — синий
- и т.д.

```php
[social_links type="type1"]
```

---

### type2 — Иконки в muted-стиле
Серые иконки без фона.

```php
[social_links type="type2"]
```

---

### type3 — Цветные иконки
Цветные иконки без кнопок.

```php
[social_links type="type3"]
```

---

### type4 — Белые иконки
Белые иконки для тёмного фона.

```php
[social_links type="type4"]
```

---

### type5 — Тёмные круглые кнопки
Все кнопки тёмного цвета (`btn-dark`).

```php
[social_links type="type5"]
```

---

### type6 — Широкие кнопки с названиями
Кнопки с иконками и названиями соцсетей.

```php
[social_links type="type6"]
```

---

### type7 — Кнопки с кастомным фоном
Использует классы `btn-telegram`, `btn-whatsapp` и т.д.

```php
[social_links type="type7"]
```

---

### type8 — Настраиваемые кнопки
Позволяет задать произвольный цвет и стиль.

**Дополнительные параметры:**
- `button-color` — primary, red, blue, green, purple, orange, yellow, navy, ash
- `buttonstyle` — solid (сплошная) или outline (с обводкой)

```php
[social_links type="type8" button-color="primary" buttonstyle="solid"]
[social_links type="type8" button-color="red" buttonstyle="outline"]
```

---

### type9 — Primary outline
Фиксированные параметры: `btn-outline-primary`.

```php
[social_links type="type9"]
```

---

## Размеры (size)

| Размер | CSS классы | Описание |
|--------|------------|----------|
| `lg` | `fs-60`, `btn-lg` | Большие кнопки |
| `md` | `fs-45`, `btn-md` | Средние (по умолчанию) |
| `sm` | `''`, `btn-sm` | Маленькие |

---

## Примеры использования

### Базовый вывод
```php
[social_links]
```

### С указанием типа и размера
```php
[social_links type="type3" size="sm"]
```

### С дополнительными классами
```php
[social_links type="type2" size="lg" class="my-custom-class gap-3"]
```

### Кастомные кнопки
```php
[social_links type="type8" button-color="primary" buttonstyle="solid" size="lg"]
[social_links type="type8" button-color="red" buttonstyle="outline"]
```

---

## Источник данных

Данные берутся из опции `socials_urls` в Redux Framework:
```php
get_option('socials_urls')
```

Для заполнения перейдите в **Redux → Theme Options → Social Links**.

---

## HTML структура

### type1-type7
```html
<nav class="nav social gap-2 [дополнительные классы]">
    <a href="..." class="btn btn-circle lh-1 has-ripple btn-md btn-[соцсеть]" target="_blank" rel="noopener">
        <i class="uil uil-[иконка]"></i>
    </a>
    ...
</nav>
```

### type8-type9
```html
<nav class="nav gap-2 [дополнительные классы]">
    <a href="..." class="btn btn-circle lh-1 has-ripple btn-[primary/outline]-[цвет] btn-md" target="_blank" rel="noopener">
        <i class="uil uil-[иконка]"></i>
    </a>
    ...
</nav>
```

---

## Поддерживаемые социальные сети

Функция автоматически преобразует ключи в иконки:

| Ключ | Иконка | Класс кнопки |
|------|--------|--------------|
| `telegram` | `uil-telegram-alt` | `btn-telegram` |
| `vk` | `uil-vk` | `btn-vk` |
| `facebook` | `uil-facebook-f` | `btn-facebook` |
| `instagram` | `uil-instagram` | `btn-instagram` |
| `linkedin` | `uil-linkedin` | `btn-linkedin` |
| `twitter` | `uil-twitter` | `btn-twitter` |
| `youtube` | `uil-youtube` | `btn-youtube` |
| `ok` | `uil-ok-1` | `btn-ok` |
| `rutube` | `uil-rutube-1` | `btn-rutube` |
| `tik-tok` | `uil-tiktok` | `btn-tiktok` |
| `github` | `uil-github-alt` | `btn-github` |

---

## Связанные функции

| Функция | Файл | Описание |
|---------|------|----------|
| [`social_links()`](functions/global.php:179) | functions/global.php | Основная функция вывода соц. иконок |
| [`codeweber_single_social_links()`](functions/global.php:354) | functions/global.php | Обёртка для single-страниц |
| [`staff_social_links()`](functions/global.php:416) | functions/global.php | Соц. иконки для сотрудников (CPT staff) |
| [`vacancy_social_links()`](functions/global.php:557) | functions/global.php | Соц. иконки для вакансий (CPT vacancies) |

---

## Родственные шорткоды

- [`[site_domain]`](functions/shortcodes.php:10) — выводит домен сайта
- [`[site_domain_link]`](functions/shortcodes.php:26) — ссылка на главную страницу
- [`[address]`](functions/shortcodes.php:101) — выводит адрес из Redux
