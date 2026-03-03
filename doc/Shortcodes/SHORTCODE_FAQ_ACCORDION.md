# SHORTCODE_FAQ_ACCORDION

## Общее описание

Шорткод `[faq_accordion]` выводит аккордеон с вопросами и ответами из CPT FAQ. Поддерживает фильтрацию по категориям и тегам, сортировку и пагинацию.

---

## Исходный код

**Файл:** [`functions/cpt/cpt-faq.php`](functions/cpt/cpt-faq.php:530)

```php
add_shortcode('faq_accordion', 'faq_accordion_shortcode');

function faq_accordion_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'category' => '',
        'tag' => '',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'ASC'
    ), $atts);
    // ...
}
```

---

## Параметры шорткода

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `category` | string | `''` | Категории (слаги через запятую) |
| `tag` | string | `''` | Теги (слаги через запятую) |
| `posts_per_page` | int | `-1` | Количество элементов (-1 = все) |
| `orderby` | string | `date` | Поле сортировки: date, title, menu_order |
| `order` | string | `ASC` | Порядок: ASC, DESC |

---

## Использование

### Все FAQ
```php
[faq_accordion]
```

### Из категории
```php
[faq_accordion category="general" posts_per_page="5"]
```

### С тегами
```php
[faq_accordion tag="important,popular" posts_per_page="10"]
```

### Сортировка по убыванию
```php
[faq_accordion order="DESC" posts_per_page="8"]
```

### Комбинированный фильтр
```php
[faq_accordion category="payment" tag="new" posts_per_page="6" order="DESC"]
```

### Несколько категорий
```php
[faq_accordion category="general,technical" posts_per_page="10"]
```

### Сортировка по названию
```php
[faq_accordion orderby="title" order="ASC"]
```

---

## CPT FAQ

**Post Type:** `faq`
**Таксономии:** `faq_categories`, `faq_tag`

### Создание FAQ

1. Перейдите в **FAQ → Add New**
2. Заполните вопрос в заголовке
3. Заполните ответ в контенте
4. Добавьте категорию и теги

---

## HTML структура

```html
<div class="accordion accordion-wrapper" id="accordionFaq">
    <div class="card plain accordion-item">
        <div class="card-header" id="headingFaq1">
            <button class="accordion-button" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#collapseFaq1">
                Вопрос
            </button>
        </div>
        <div id="collapseFaq1" class="accordion-collapse collapse show">
            <div class="card-body">
                Ответ
            </div>
        </div>
    </div>
</div>
```

---

## CSS классы

| Класс | Описание |
|-------|----------|
| `.accordion-wrapper` | Основная обёртка |
| `.accordion-item` | Элемент аккордеона |
| `.card-header` | Заголовок вопроса |
| `.accordion-button` | Кнопка вопроса |
| `.accordion-collapse` | Контейнер ответа |
| `.card-body` | Тело ответа |

---

## Интеграция с Bootstrap

Используется Bootstrap 5 collapse:
- `data-bs-toggle="collapse"`
- `data-bs-target="#collapseId"`
- `aria-expanded="true/false"`

---

## Связанные файлы

| Файл | Описание |
|------|----------|
| `cpt-faq.php` | Регистрация CPT и шорткода |

---

## Связанные шорткоды

- [`[breadcrumbs]`](SHORTCODE_BREADCRUMBS.md) — хлебные крошки
- [`[codeweber_form]`](SHORTCODE_CODEWEBER_FORM.md) — формы для вопросов
