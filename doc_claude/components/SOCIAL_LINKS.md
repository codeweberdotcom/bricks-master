# Social Links

Система отображения ссылок на соцсети: 5 PHP-функций, шорткод `[social_links]`, 9 визуальных типов, два формата данных.

---

## Файлы системы

| Файл | Назначение |
|------|-----------|
| `functions/global.php` | Все 5 функций: `social_links()`, `codeweber_single_social_links()`, `codeweber_global_social_style()`, `staff_social_links()`, `vacancy_social_links()` |
| `functions/shortcodes.php` | Шорткод `[social_links]` |
| `templates/components/socialicons.php` | **Устаревший** ACF-шаблон — не используется в текущей системе |

---

## Источник данных

Глобальные ссылки хранятся в опции `socials_urls` (wp_options):

```php
$socials = get_option('socials_urls');
```

Управляется через Redux → Theme Options → Social. Ключи опции — названия соцсетей (например, `vk`, `telegram`, `instagram`).

---

## Два формата данных

### Формат 1: простой (глобальные соцсети)

```php
[ 'vk' => 'https://vk.com/...', 'telegram' => 'https://t.me/...' ]
```

Ключ (`vk`, `telegram` и т.д.) определяет иконку через встроенный маппинг.

### Формат 2: расширенный (кастомные соцсети)

```php
[
    'linkedin' => [
        'url'          => 'https://linkedin.com/...',
        'icon'         => 'linkedin',          // uil-{icon}
        'label'        => 'LinkedIn',
        'social_name'  => 'linkedin',          // CSS-класс: btn-{social_name}
        'target_blank' => true,                // false → без target="_blank"
    ],
]
```

Используется в `staff_social_links()` и `vacancy_social_links()`.

---

## Основная функция `social_links()`

```php
social_links(
    string $class,           // Доп. CSS-классы для <nav>
    string $type,            // Тип отображения: type1–type9
    string $size = 'md',     // 'lg' | 'md' | 'sm'
    string $button_color = 'primary',  // Для type8 (any Bootstrap color)
    string $buttonstyle = 'solid',     // Для type8: 'solid' | 'outline'
    string $button_form = 'circle',    // 'circle' | 'block'
    array|null $custom_socials = null  // null → читать get_option('socials_urls')
): string
```

Возвращает HTML-строку `<nav>...</nav>`. Пустая строка если нет данных.

---

## 9 типов отображения

| Тип | Описание | Обёртка | Кнопка |
|-----|----------|---------|--------|
| `type1` | Цветные круглые кнопки, иконка | `nav social gap-2` | `btn btn-circle btn-{social_name}` |
| `type2` | Приглушённые иконки, без кнопок | `nav social social-muted gap-2` | `<a class="lh-1">` |
| `type3` | Цветные иконки, без кнопок | `nav social gap-2` | `<a class="lh-1">` |
| `type4` | Белые иконки (для тёмного фона) | `nav social social-white gap-2` | `<a class="lh-1">` |
| `type5` | Тёмные круглые кнопки | `nav social gap-2` | `btn btn-circle btn-dark` |
| `type6` | Широкие белые кнопки с иконкой и текстом | `nav social gap-2` | `btn btn-icon btn-white w-100 btn-icon-start border` |
| `type7` | Широкие цветные кнопки с иконкой и текстом | `nav gap-2 social-white` | `btn btn-icon btn-{social_name} w-100 btn-icon-start` |
| `type8` | Единый цвет — управляется через `$button_color`/`$buttonstyle` | `nav gap-2` | `btn btn-{form} btn-{color}` или `btn-outline-{color}` |
| `type9` | Outline-primary кнопки | `nav gap-2` | `btn btn-{form} btn-outline-primary` |

**Размеры:**

| `$size` | Иконка | Кнопка |
|---------|--------|--------|
| `lg` | `fs-60` | `btn-lg` |
| `md` | `fs-45` | `btn-md` |
| `sm` | — | `btn-sm` |

**Форма кнопки (`$button_form`):**

- `circle` → `btn-circle` (фиксированный круг)
- `block` → `btn-block` + `Codeweber_Options::style('button')` (скругление из Redux)

---

## Маппинг иконок (простой формат)

Ключ опции → иконка Unicons (`uil-{icon}`):

| Ключ | Иконка |
|------|--------|
| `telegram` | `uil-telegram-alt` |
| `rutube` | `uil-rutube-1` |
| `github` | `uil-github-alt` |
| `ok` | `uil-ok-1` |
| `vkmusic` | `uil-vk-music` |
| `tik-tok` | `uil-tiktok` |
| `googledrive` | `uil-google-drive` |
| `googleplay` | `uil-google-play` |
| `odnoklassniki` | `uil-square-odnoklassniki` |
| остальные | `uil-{key}` (напрямую) |

---

## Обёртки для single-страниц: `codeweber_single_social_links()`

```php
echo codeweber_single_social_links([
    'class'        => 'mb-3',
    'type'         => '',       // пусто → из Redux
    'size'         => '',       // пусто → из Redux
    'button_form'  => '',       // пусто → из Redux
    'button_color' => 'primary',
    'buttonstyle'  => 'solid',
]);
```

Читает Redux-настройки если параметр не передан или пустой:

| Redux ключ | Назначение | Умолчание |
|-----------|-----------|----------|
| `global-social-icon-type` | Тип (число 1–9) | `1` |
| `global-social-button-size` | Размер | `'md'` |
| `global-social-button-style` | Форма (`circle`/`block`) | `'circle'` |

Фолбэк: если `global-social-icon-type` не задан, пробует `social-icon-type`.

---

## Стиль из Redux: `codeweber_global_social_style()`

Возвращает массив параметров (для использования в шаблонах карточек):

```php
$style = codeweber_global_social_style();
// [ 'type' => 'type3', 'size' => 'md', 'button_form' => 'circle' ]

echo staff_social_links($post_id, '', $style['type'], $style['size'], 'primary', 'solid', $style['button_form']);
```

---

## Staff: `staff_social_links()`

```php
echo staff_social_links(
    int $post_id,
    string $class = '',
    string $type = 'type1',
    string $size = 'sm',
    string $button_color = 'primary',
    string $buttonstyle = 'solid',
    string $button_form = 'circle'
): string
```

Читает метаполя `_staff_{key}` для текущей записи. Доступные ключи:

| Метаполе | Иконка | social_name |
|----------|--------|-------------|
| `_staff_facebook` | `facebook-f` | `facebook` |
| `_staff_twitter` | `twitter` | `twitter` |
| `_staff_linkedin` | `linkedin` | `linkedin` |
| `_staff_instagram` | `instagram` | `instagram` |
| `_staff_telegram` | `telegram-alt` | `telegram` |
| `_staff_vk` | `vk` | `primary` |
| `_staff_whatsapp` | `whatsapp` | `whatsapp` |
| `_staff_skype` | `skype` | `skype` |
| `_staff_website` | `globe` | `primary` |

---

## Vacancy: `vacancy_social_links()`

```php
echo vacancy_social_links(
    int $post_id,
    string $class = '',
    string $type = 'type1',
    string $size = 'sm',
    ...
): string
```

Читает метаполя `_vacancy_{key}`:

| Метаполе | Иконка | social_name | Особенность |
|----------|--------|-------------|-------------|
| `_vacancy_email` | `envelope` | `primary` | `mailto:` добавляется автоматически; `target_blank = false` |
| `_vacancy_linkedin_url` | `linkedin` | `linkedin` | `target_blank = true` |
| `_vacancy_apply_url` | `link` | `primary` | `target_blank = true` |

---

## Шорткод `[social_links]`

Зарегистрирован в `functions/shortcodes.php`. Параметры:

| Атрибут | По умолчанию | Описание |
|---------|-------------|---------|
| `type` | `type1` | Тип отображения |
| `size` | `md` | `sm`, `md`, `lg` |
| `class` | — | Доп. CSS-классы для `<nav>` |
| `button-color` | `primary` | Только для type8 |
| `buttonstyle` | `solid` | `solid` / `outline` — только для type8 |

Примеры:

```
[social_links]
[social_links type="type3" size="sm"]
[social_links type="type8" button-color="red" buttonstyle="outline" size="lg"]
[social_links class="justify-content-center mb-4"]
```

> **Примечание:** шорткод не поддерживает `button_form` — всегда `circle`.

---

## Особенности реализации

**Переопределение gap через `$class`:**
Если в `$class` передан `gap-*`, он заменяет дефолтный `gap-2` в обёртке.

**type3 и type6 убирают `social-white`:**
Если `$class` содержит `social-white`, он удаляется для этих типов (иконки должны быть цветными, не белыми).

**type8 и type9 — отдельная обёртка:**
Используют `<nav class="nav gap-2">` без класса `social`, чтобы не подхватывать стили цветных соцсетей темы.

---

## Устаревший шаблон

`templates/components/socialicons.php` — использует ACF `get_field('social_*', 'option')`. Это legacy-шаблон, не связан с `social_links()`. Не использовать в новых шаблонах.

---

## Связанные документы

- [REDUX_OPTIONS.md](../settings/REDUX_OPTIONS.md) — ключи `global-social-*`
- [CPT_CATALOG.md](../cpt/CPT_CATALOG.md) — Staff, Vacancy CPT
