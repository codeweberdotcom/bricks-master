# SHORTCODE_AJAX_SEARCH_FORM

## Общее описание

Шорткод `[ajax_search_form]` выводит форму AJAX-поиска с автодополнением. Поддерживает поиск по различным типам записей, таксономиям и контенту.

---

## Исходный код

**Файл:** [`functions/integrations/ajax-search-module/ajax-search.php`](functions/integrations/ajax-search-module/ajax-search.php:570)

```php
add_shortcode('ajax_search_form', 'ajax_search_form_shortcode');
function ajax_search_form_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'placeholder' => __('Search...', 'codeweber'),
        'posts_per_page' => '10',
        'post_types' => '',
        'search_content' => 'false',
        'taxonomy' => '',
        'term' => '',
        'include_taxonomies' => 'false',
        'show_excerpt' => 'true',
        'class' => '',
        'id' => ''
    ), $atts);
    // ...
}
```

---

## Параметры шорткода

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `placeholder` | string | `Search...` | Текст плейсхолдера |
| `posts_per_page` | int | `10` | Количество результатов |
| `post_types` | string | `''` | Типы записей через запятую |
| `search_content` | bool | `false` | Поиск в контенте |
| `taxonomy` | string | `''` | Таксономия для фильтрации |
| `term` | string | `''` | Термин таксономии |
| `include_taxonomies` | bool | `false` | Включить таксономии в результаты |
| `show_excerpt` | bool | `true` | Показывать отрывки |
| `class` | string | `''` | CSS классы |
| `id` | string | auto | Уникальный ID формы |

---

## Использование

### Базовый вывод
```php
[ajax_search_form]
```

### Поиск товаров
```php
[ajax_search_form placeholder="Поиск товаров..." posts_per_page="8" post_types="product"]
```

### Поиск с отрывками
```php
[ajax_search_form search_content="true" show_excerpt="true"]
```

### Фильтрация по категории
```php
[ajax_search_form taxonomy="category" term="news" include_taxonomies="true"]
```

### С кастомным ID
```php
[ajax_search_form id="my-search-form" class="custom-class"]
```

---

## Типы записей

| Тип | Описание |
|------|----------|
| `post` | Записи блога |
| `page` | Страницы |
| `product` | Товары WooCommerce |
| `staff` | Сотрудники |
| `vacancies` | Вакансии |
| `faq` | FAQ |

---

## Исключённые типы записей

Из поиска автоматически исключаются:
- `header`
- `footer`
- `media_license`
- `page-header`
- `modal`
- `html_blocks`

---

## AJAX обработка

Поиск обрабатывается через AJAX:
- `wp_ajax_ajax_search` — обычный поиск
- `wp_ajax_nopriv_ajax_search` — для неавторизованных
- `wp_ajax_ajax_search_load_all` — загрузка всех результатов

---

## HTML структура

```html
<div class="position-relative">
    <form class="search-form" id="search-form-abc123">
        <input type="text" id="search-form-abc123-input" 
               class="search-form form-control rounded"
               placeholder="Поиск..."
               data-posts-per-page="10"
               data-post-types="">
    </form>
</div>
```

---

## JavaScript

Файл: `assets/js/ajax-search.js`

Обрабатывает:
- Ввод пользователя
- AJAX запросы
- Вывод результатов
- Подсветку совпадений

---

## Переводы

```php
$i18n = array(
    'searching' => __('Searching...', 'codeweber'),
    'no_results' => __('No results found', 'codeweber'),
    'total_found' => __('Total found', 'codeweber'),
    // ...
);
```

---

## Связанные файлы

| Файл | Описание |
|------|----------|
| `ajax-search.php` | Основной файл |
| `ajax-search.js` | JavaScript обработка |
| `search-statistics.php` | Статистика поиска |

---

## Связанные шорткоды

- [`[codeweber_form]`](SHORTCODE_CODEWEBER_FORM.md) — формы
- [`[breadcrumbs]`](SHORTCODE_BREADCRUMBS.md) — хлебные крошки
