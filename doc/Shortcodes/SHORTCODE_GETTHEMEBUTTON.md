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

    return getThemeButton($atts['default']);
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

## Функция getThemeButton()

Функция `getThemeButton()` возвращает CSS-класс для кнопок из настроек темы.

**Определение:**
```php
function getThemeButton($default = '') {
    // Логика получения класса из Redux
}
```

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
