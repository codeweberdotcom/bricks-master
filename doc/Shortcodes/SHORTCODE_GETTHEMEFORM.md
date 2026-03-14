# SHORTCODE_GETTHEMEFORM

## Общее описание

Шорткод `[getthemeform]` выводит CSS-класс для скругления полей формы, который настраивается в Redux Framework. Используется для поддержания консистентности стиля полей в формах Contact Form 7.

---

## Исходный код

**Файл:** [`functions/integrations/redux_framework/redux_cf7.php`](functions/integrations/redux_framework/redux_cf7.php:13)

```php
add_shortcode('getthemeform', function ($atts) {
    $atts = shortcode_atts([
        'default' => ' rounded',
    ], $atts);

    return Codeweber_Options::style('form-radius', $atts['default']);
});
```

---

## Параметры шорткода

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `default` | string | ` rounded` | Значение по умолчанию |

---

## Использование

```php
[getthemeform]
[getthemeform default=" rounded"]
```

---

## API: `Codeweber_Options::style('form-radius')`

Метод `Codeweber_Options::style('form-radius')` возвращает CSS-класс для скругления полей формы из настроек темы.

**Вызов:**
```php
$form_class = Codeweber_Options::style('form-radius');        // По умолчанию ' rounded'
$form_class = Codeweber_Options::style('form-radius', '');    // Пустая строка как fallback
```

Все опции загружаются за один `get_option('redux_demo')` и кэшируются на весь запрос.

---

## Применение в CF7

Шорткод используется в формах Contact Form 7 для добавления стиля полей ввода:

```php
[text* your-name class:form-control class:getthemeform placeholder "Ваше имя"]
[email* your-email class:form-control class:getthemeform placeholder "Ваш email"]
[textarea* your-message class:form-control class:getthemeform placeholder "Сообщение"]
```

---

## Связанные шорткоды

- [`[getthemebutton]`](SHORTCODE_GETTHEMEBUTTON.md) — стиль для кнопки
- [`[codeweber_form]`](SHORTCODE_CODEWEBER_FORM.md) — формы CodeWeber
