# SHORTCODE_GETTHEMEBUTTON

## Общее описание

Шорткод `[getthemebutton]` выводит CSS-класс для стилизации кнопок, который настраивается в Redux Framework. Используется для поддержания консистентности стиля кнопок в формах Contact Form 7.

---

## Исходный код

**Файл:** [`functions/integrations/redux_framework/redux_cf7.php`](functions/integrations/redux_framework/redux_cf7.php:4)

```php
add_shortcode('getthemebutton', function ($atts) {
    $atts = shortcode_atts([
        'default' => ' rounded-pill',
    ], $atts);

    return Codeweber_Options::style('button', $atts['default']);
});
```

---

## Параметры шорткода

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `default` | string | ` rounded-pill` | Значение по умолчанию |

---

## Использование

```php
[getthemebutton]
[getthemebutton default=" rounded-pill"]
```

---

## API: `Codeweber_Options::style('button')`

Метод `Codeweber_Options::style('button')` возвращает CSS-класс для кнопок из настроек темы.

**Вызов:**
```php
$btn_class = Codeweber_Options::style('button');           // По умолчанию ' rounded-pill'
$btn_class = Codeweber_Options::style('button', '');       // Пустая строка как fallback
$btn_class = Codeweber_Options::style('button', ' rounded mt-2'); // Кастомный fallback
```

Все опции загружаются за один `get_option('redux_demo')` и кэшируются на весь запрос.

---

## Применение в CF7

Шорткод используется в формах Contact Form 7 для добавления стиля кнопки отправки:

```php
[submit class:btn class:getthemebutton class:btn-primary "Отправить"]
```

Результат:
```html
<input type="submit" class="btn rounded-pill btn-primary" value="Отправить">
```

---

## Связанные шорткоды

- [`[getthemeform]`](SHORTCODE_GETTHEMEFORM.md) — стиль для полей формы
- [`[codeweber_form]`](SHORTCODE_CODEWEBER_FORM.md) — формы CodeWeber
