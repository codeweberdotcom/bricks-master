# SHORTCODE_SITE_DOMAIN_LINK

## Общее описание

Шорткод `[site_domain_link]` выводит ссылку на главную страницу сайта в виде HTML-элемента `<a>`.

---

## Исходный код

**Файл:** [`functions/shortcodes.php`](functions/shortcodes.php:26)

```php
add_shortcode('site_domain_link', function () {
    $url = home_url();
    return '<a href="' . esc_url($url) . '">' . esc_html($url) . '</a>';
});
```

---

## Использование

```php
[site_domain_link]
```

**Вывод:** `<a href="https://example.com">https://example.com</a>`

---

## Описание работы

1. Получает URL главной страницы через `home_url()`
2. Создаёт HTML-элемент `<a>` с ссылкой и текстом
3. Экранирует URL через `esc_url()`
4. Экранирует текст через `esc_html()`

---

## Примеры

```php
// На сайте https://example.com
[site_domain_link]

// Вывод: <a href="https://example.com">https://example.com</a>

// На сайте https://example.com/subfolder/
[site_domain_link]

// Вывод: <a href="https://example.com/subfolder/">https://example.com/subfolder/</a>
```

---

## Отличие от site_domain

| Шорткод | Вывод |
|----------|-------|
| `[site_domain]` | `example.com` (просто текст) |
| `[site_domain_link]` | `<a href="https://example.com">https://example.com</a>` (ссылка) |

---

## Связанные шорткоды

- [`[site_domain]`](SHORTCODE_SITE_DOMAIN.md) — только домен без ссылки
- [`[social_links]`](SHORTCODE_SOCIAL_LINKS.md) — социальные иконки
- [`[address]`](SHORTCODE_ADDRESS.md) — адрес из Redux
