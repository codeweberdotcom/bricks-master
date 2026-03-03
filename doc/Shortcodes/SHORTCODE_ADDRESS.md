# SHORTCODE_ADDRESS

## Общее описание

Шорткод `[address]` выводит фактический или юридический адрес из настроек Redux Framework.

Является обёрткой над функцией [`codeweber_get_address()`](functions/global.php:1010).

---

## Исходный код

**Файл:** [`functions/shortcodes.php`](functions/shortcodes.php:101)

```php
add_shortcode('address', function ($atts) {
    if (!function_exists('codeweber_get_address')) {
        return '<!-- Функция codeweber_get_address не найдена -->';
    }
    
    $atts = shortcode_atts(array(
        'type' => 'fact',
        'separator' => ', ',
        'fallback' => 'Moonshine St. 14/05 Light City, London, United Kingdom'
    ), $atts, 'address');
    
    return codeweber_get_address($atts['type'], $atts['separator'], $atts['fallback']);
});
```

---

## Параметры шорткода

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `type` | string | `fact` | Тип адреса: `fact` (фактический) или `juri` (юридический) |
| `separator` | string | `, ` | Разделитель между частями адреса |
| `fallback` | string | `Moonshine St. 14/05 Light City, London, United Kingdom` | Текст по умолчанию, если адрес не заполнен |

---

## Использование

```php
[address]
[address type="juri"]
[address type="fact" separator="<br>"]
[address type="juri" separator=", " fallback="Адрес не указан"]
```

---

## Параметры Redux

Адрес заполняется в **Redux → Theme Options → Contacts**.

### Фактический адрес (fact)
- `fact-country` — Страна
- `fact-region` — Регион/Область
- `fact-city` — Город
- `fact-street` — Улица
- `fact-house` — Дом
- `fact-office` — Офис/Квартира
- `fact-postal` — Почтовый индекс

### Юридический адрес (juri)
- `juri-country` — Страна
- `juri-region` — Регион/Область
- `juri-city` — Город
- `juri-street` — Улица
- `juri-house` — Дом
- `juri-office` — Офис/Квартира
- `juri-postal` — Почтовый индекс

---

## Примеры

### Фактический адрес (по умолчанию)
```php
[address]
// Вывод: Russia, Moscow, Tverskaya St. 1
```

### Юридический адрес
```php
[address type="juri"]
// Вывод: Russia, Moscow, Lenina St. 10
```

### С разделителем перенос строки
```php
[address separator="<br>"]
// Вывод:
// Russia
// Moscow
// Tverskaya St. 1
```

### С кастомным fallback
```php
[address type="juri" fallback="Адрес не указан"]
```

---

## Функция codeweber_get_address()

**Файл:** [`functions/global.php`](functions/global.php:1010)

```php
function codeweber_get_address($type = 'fact', $separator = ', ', $fallback = 'Moonshine St. 14/05 Light City, London, United Kingdom')
```

### Параметры функции

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `$type` | string | `fact` | Тип адреса: `fact` или `juri` |
| `$separator` | string | `, ` | Разделитель |
| `$fallback` | string | (текст) | Значение по умолчанию |

### Логика работы

1. Проверяет наличие Redux Framework
2. Определяет префикс полей (`fact-` или `juri-`)
3. Получает все части адреса из Redux
4. Собирает адрес в порядке: индекс → страна → регион → город → улица
5. Если адрес пустой — возвращает fallback

---

## HTML структура

Простой текст без HTML-обёрток:
```html
Russia, Moscow, Tverskaya St. 1
```

При separator="<br>":
```html
Russia<br>Moscow<br>Tverskaya St. 1
```

---

## Связанные шорткоды

- [`[site_domain]`](SHORTCODE_SITE_DOMAIN.md) — домен сайта
- [`[site_domain_link]`](SHORTCODE_SITE_DOMAIN_LINK.md) — ссылка на главную
- [`[social_links]`](SHORTCODE_SOCIAL_LINKS.md) — социальные иконки
- [`[get_contact]`](SHORTCODE_GET_CONTACT.md) — контактные данные

---

## Связанные функции

| Функция | Файл | Описание |
|---------|------|----------|
| `codeweber_get_address()` | functions/global.php:1010 | Получение адреса из Redux |
