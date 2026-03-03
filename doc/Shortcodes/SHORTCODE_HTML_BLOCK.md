# SHORTCODE_HTML_BLOCK

## Общее описание

Шорткод `[html_block]` выводит контент из CPT HTML Blocks по ID. Позволяет создавать переиспользуемые блоки контента.

---

## Исходный код

**Файл:** [`functions/cpt/cpt-html_blocks.php`](functions/cpt/cpt-html_blocks.php:88)

```php
add_shortcode('html_block', 'codeweber_html_block_shortcode');

function codeweber_html_block_shortcode($atts)
{
    $atts = shortcode_atts(['id' => ''], $atts, 'html_block');
    if (empty($atts['id'])) {
        return '';
    }
    $post = get_post((int) $atts['id']);
    if (!$post || $post->post_type !== 'html_blocks' || $post->post_status !== 'publish') {
        return '';
    }
    return do_shortcode(apply_filters('the_content', $post->post_content));
}
```

---

## Параметры шорткода

| Параметр | Тип | Описание |
|----------|-----|----------|
| `id` | int | ID поста HTML Block (обязательный) |

---

## Использование

### Базовый вывод
```php
[html_block id="123"]
```

---

## CPT HTML Blocks

**Post Type:** `html_blocks`

### Создание блока

1. Перейдите в **HTML Blocks → Add New**
2. Заполните заголовок
3. Добавьте HTML/контент в редакторе
4. Опубликуйте
5. Скопируйте шорткод из колонки Shortcode

---

## Особенности

- Обрабатывает шорткоды внутри блока через `do_shortcode()`
- Применяет фильтры контента `apply_filters('the_content')`
- Возвращает пустую строку если блок не найден или не опубликован
- Single и Archive страницы заблокированы (возвращают 404)

---

## Админка

В списке HTML Blocks добавлена колонка **Shortcode** с кнопкой копирования:

```php
[html_block id="123"]
```

---

## HTML структура

```html
<!-- Контент HTML Block -->
<div class="html-block">
    <!-- Контент из редактора -->
</div>
```

---

## Применение

### Футер
```php
[html_block id="15"]
```

### Сайдбар
```php
[html_block id="20"]
```

### Между секциями
```php
[html_block id="25"]
```

---

## Связанные файлы

| Файл | Описание |
|------|----------|
| `cpt-html_blocks.php` | Регистрация CPT и шорткода |

---

## Связанные шорткоды

- [`[redux_option]`](SHORTCODE_REDUX_OPTION.md) — вывод Redux настроек
- [`[codeweber_form]`](SHORTCODE_CODEWEBER_FORM.md) — формы
