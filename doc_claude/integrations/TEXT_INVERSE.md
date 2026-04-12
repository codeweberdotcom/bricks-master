# Text Inverse — инверсия цвета текста

Механизм для светлого текста поверх тёмного фона страницы. Класс `text-inverse` применяется к `.content-wrapper` (`<main>`), заголовок вынесен за пределы `<main>` и не затрагивается.

---

## Файлы

| Файл | Назначение |
|------|-----------|
| `functions/body-bg.php` | `cw_content_wrapper_bg_attrs()` — вычисляет класс и data-атрибуты |
| `redux-framework/sample/metaboxes.php` | Метабокс `page-body-bg-text` на уровне поста |
| `header.php` | `<main class="content-wrapper ...">` открывается **после** шапки |

---

## Как работает

`cw_content_wrapper_bg_attrs()` возвращает `['class' => '...', 'data' => '...']`.

Если text = `'inverse'` — в `class` добавляется `text-inverse`.

### Приоритет источников (от высшего к низшему)

1. **Per-post метабокс** — поле `page-body-bg-text` (`auto` / `inverse`)
2. **Per-CPT Redux** — ключ `body_bg_text_{prefix}` (например `body_bg_text_services`)
3. **Глобальный Redux** — ключ `body_bg_global_text`

---

## Метабокс

**Файл:** `redux-framework/sample/metaboxes.php`

```php
array(
    'id'      => 'page-body-bg-text',
    'type'    => 'button_set',
    'title'   => 'Text Color',
    'options' => array(
        'auto'    => 'Auto',
        'inverse' => 'Light (inverse)',
    ),
    'default' => 'auto',
),
```

---

## CSS

Класс `text-inverse` определён в теме — делает все тексты белыми через CSS custom properties Bootstrap:

```scss
.text-inverse {
    --bs-body-color: var(--bs-white);
    --bs-body-color-rgb: var(--bs-white-rgb);
    // heading color наследуется через cascade (не переопределяется явно)
}
```

### Заголовки h1–h6

Чтобы каскад работал на заголовки, в дочерней теме **не должно** быть `$headings-color` = конкретный цвет. Нужно `null`:

```scss
// hoger/_user-variables.scss
$headings-color: null; // null → CSS cascade; позволяет text-inverse перекрашивать h1-h6
```

Bootstrap при `null` не эмитирует `--bs-heading-color`, заголовки наследуют `color` из родителя.

### Звёзды WooCommerce

Пустые звёзды рейтинга используют `rgba(var(--bs-body-color-rgb), .2)` вместо `rgba(#000, .15)` — следуют цвету текста автоматически. При `text-inverse` добавляется override:

```scss
.text-inverse .comment-form-rating p.stars {
    // пустые звёзды — светлее на тёмном фоне
    color: rgba(var(--bs-white-rgb), .3);
}
```

---

## Шапка вне main

`<main class="content-wrapper text-inverse">` открывается **после** рендера всех шаблонов шапки в `header.php`. Это гарантирует, что `text-inverse` не затрагивает навигацию и header.

```php
// header.php — упрощённо:
$_cw_bg = cw_content_wrapper_bg_attrs();
// ... рендер header-шаблонов ...
?>
<main class="content-wrapper<?php echo $_cw_bg['class'] ? ' ' . $_cw_bg['class'] : ''; ?>"<?php echo $_cw_bg['data']; ?>>
```
