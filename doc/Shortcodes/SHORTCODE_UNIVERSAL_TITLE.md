# SHORTCODE_UNIVERSAL_TITLE

## Общее описание

Шорткод `[universal_title]` выводит универсальный заголовок текущей страницы с автоматическим определением типа контента.

Является обёрткой над функцией [`universal_title()`](functions/global.php:634).

---

## Исходный код

**Файл:** [`functions/global.php`](functions/global.php:742)

```php
function universal_title_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'tag' => 'h1',
        'theme' => 'theme'
    ), $atts);

    return universal_title($atts['tag'], $atts['theme']);
}
add_shortcode('universal_title', 'universal_title_shortcode');
```

---

## Параметры шорткода

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `tag` | string | `h1` | HTML-тег для обёртки (h1, h2, h3, div, span и т.д.) |
| `theme` | string | `theme` | CSS класс или `theme` для получения из Redux |

---

## Использование

```php
[universal_title]
[universal_title tag="h2"]
[universal_title tag="div"]
[universal_title tag="h1" theme="custom-class"]
```

---

## Автоопределение типа страницы

Функция автоматически определяет тип страницы и возвращает соответствующий заголовок:

| Тип страницы | Источник заголовка |
|--------------|-------------------|
| Одиночная запись (post) | Заголовок записи |
| Страница (page) | Заголовок страницы |
| Товар (product) | Заголовок товара |
| Архив категории | Название категории |
| Архив тега | Название тега |
| Архив автора | Имя автора |
| Архив даты | Дата |
| Таксономия | Название термина |
| Архив CPT | Название архива |
| Страница поиска | Поисковый запрос |
| 404 | "Page Not Found" |
| Главная страница | Название сайта |
| Страница блога | Заголовок страницы блога |

---

## Примеры

### Базовый вывод (h1 с классом из Redux)
```php
[universal_title]
// Вывод: <h1 class="from-redux">Заголовок страницы</h1>
```

### С указанием тега
```php
[universal_title tag="h2"]
// Вывод: <h2 class="from-redux">Заголовок страницы</h2>
```

### С кастомным классом
```php
[universal_title tag="div" theme="my-custom-title"]
// Вывод: <div class="my-custom-title">Заголовок страницы</div>
```

### Без обёртки (только текст)
```php
universal_title(false, false)
// Возвращает только текст заголовка
```

---

## Функция universal_title()

**Файл:** [`functions/global.php`](functions/global.php:634)

```php
function universal_title($tag = false, $theme = false)
```

### Параметры функции

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `$tag` | string\|false | `false` | HTML-тег обёртки или false для текста |
| `$theme` | string\|false | `false` | CSS класс или 'theme' для Redux |

### Логика работы

1. Определяет тип страницы через WordPress условные теги
2. Получает заголовок соответствующего типа
3. Если указан тег — оборачивает в него с классом
4. Экранирует вывод через `esc_html()`

---

## Использование в PHP

```php
// Простой вывод заголовка
echo universal_title();

// С обёрткой в h1
echo universal_title('h1');

// С обёрткой в h2 и классом из Redux
echo universal_title('h2', 'theme');

// С кастомным классом
echo universal_title('h1', 'my-custom-class');

// Без обёртки (только текст)
$title = universal_title(false);
```

---

## HTML структура

```html
<!-- С тегом и классом из Redux -->
<h1 class="from-redux-settings">Заголовок страницы</h1>

<!-- С кастомным тегом и классом -->
<div class="my-custom-class">Заголовок страницы</div>

<!-- Без обёртки -->
Заголовок страницы
```

---

## Интеграция с Redux

При `theme='theme'` класс получается из настройки Redux:
```php
$title_class = Redux::get_option($opt_name, 'opt-select-title-size');
```

---

## Связанные шорткоды

- [`[site_domain]`](SHORTCODE_SITE_DOMAIN.md) — домен сайта
- [`[address]`](SHORTCODE_ADDRESS.md) — адрес из Redux
- [`[social_links]`](SHORTCODE_SOCIAL_LINKS.md) — социальные иконки

---

## Связанные функции

| Функция | Файл | Описание |
|---------|------|----------|
| `universal_title()` | functions/global.php:634 | Основная функция |
| `universal_get_post_type()` | functions/global.php:962 | Определение post_type |
