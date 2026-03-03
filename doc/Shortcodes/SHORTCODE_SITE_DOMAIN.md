# SHORTCODE_SITE_DOMAIN

## Общее описание

Шорткод `[site_domain]` выводит домен сайта без протокола (http/https) и префикса www.

---

## Исходный код

**Файл:** [`functions/shortcodes.php`](functions/shortcodes.php:10)

```php
add_shortcode('site_domain', function () {
    $host = parse_url(home_url(), PHP_URL_HOST);
    $host = preg_replace('/^www\./', '', $host); // убираем www
    return esc_html($host);
});
```

---

## Использование

```php
[site_domain]
```

**Вывод:** `example.com`

---

## Описание работы

1. Получает URL главной страницы через `home_url()`
2. Извлекает хост с помощью `parse_url()`
3. Удаляет префикс `www.` если он есть
4. Экранирует вывод через `esc_html()`

---

## Примеры

```php
// На главной странице https://www.example.com
[site_domain]

// Вывод: example.com

// На странице https://subdomain.example.com/page/
[site_domain]

// Вывод: subdomain.example.com
```

---

## Связанные шорткоды

- [`[site_domain_link]`](SHORTCODE_SITE_DOMAIN_LINK.md) — ссылка на главную страницу
- [`[social_links]`](SHORTCODE_SOCIAL_LINKS.md) — социальные иконки
- [`[address]`](SHORTCODE_ADDRESS.md) — адрес из Redux
