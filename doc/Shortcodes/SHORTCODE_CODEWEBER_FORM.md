# SHORTCODE_CODEWEBER_FORM

## Общее описание

Шорткод `[codeweber_form]` выводит форму, созданную в CodeWeber Forms (CPT) или встроенную форму. Это основной шорткод для отображения всех форм, созданных через плагин CodeWeber Forms.

---

## Исходный код

**Файл:** [`functions/integrations/codeweber-forms/codeweber-forms-shortcode.php`](functions/integrations/codeweber-forms/codeweber-forms-shortcode.php:16)

```php
class CodeweberFormsShortcode {
    public function __construct() {
        add_shortcode('codeweber_form', [$this, 'render_shortcode']);
    }
    
    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'id'    => '',
            'name'  => '',
            'title' => '',
        ], $atts, 'codeweber_form');
        // ... рендеринг формы
    }
}
```

---

## Параметры шорткода

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `id` | string\|int | (обязательный) | ID формы или ключ встроенной формы |
| `name` | string | `''` | Логическое имя формы (внутренний идентификатор) |
| `title` | string | `''` | Отображаемый заголовок формы |

---

## Использование

### Форма из CPT по ID
```php
[codeweber_form id="123"]
[codeweber_form id="6119"]
```

### Форма с кастомным заголовком
```php
[codeweber_form id="123" title="Новая форма"]
```

### Встроенные формы

```php
[codeweber_form id="newsletter"]
[codeweber_form id="testimonial"]
[codeweber_form id="resume"]
[codeweber_form id="callback"]
```

### С логическим именем
```php
[codeweber_form id="123" name="contact_form_main"]
```

---

## Встроенные формы

| ID | Описание |
|----|----------|
| `newsletter` | Форма подписки на рассылку |
| `testimonial` | Форма отзыва |
| `resume` | Форма резюме |
| `callback` | Заявка на обратный звонок |

---

## CPT CodeWeber Form

Формы создаются как Custom Post Type `codeweber_form`.

### Метаполя формы
- `_form_recipient_email` — Email получателя
- `_form_sender_email` — Email отправителя
- `_form_sender_name` — Имя отправителя
- `_form_subject` — Тема письма
- `_form_success_message` — Сообщение об успехе
- `_form_error_message` — Сообщение об ошибке
- `_form_type` — Тип формы

---

## HTML структура

```html
<form class="codeweber-form" method="post">
    <div class="form-group">
        <label for="field_1">Имя поля</label>
        <input type="text" name="field_1" id="field_1">
    </div>
    <!-- Поля формы -->
    <div class="form-submit">
        <button type="submit" class="btn btn-primary">Отправить</button>
    </div>
</form>
```

---

## Gutenberg блоки

Форма строится из Gutenberg блоков:
- `codeweber-blocks/form` — Основной блок формы
- `codeweber-blocks/form-field` — Поле формы
- `codeweber-blocks/submit-button` — Кнопка отправки

---

## JavaScript обработка

Формы обрабатываются через JavaScript:
- `form-submit-universal.js` — Универсальная обработка отправки
- Валидация полей
- AJAX отправка
- Обработка UTM-меток
- Согласия (consent)

---

## Связанные файлы

| Файл | Описание |
|------|----------|
| `codeweber-forms-shortcode.php` | Обработчик шорткода |
| `codeweber-forms-renderer.php` | Рендеринг формы |
| `codeweber-forms-api.php` | API форм |
| `codeweber-forms-init.php` | Инициализация |

---

## Связанные шорткоды

- [`[getthemebutton]`](SHORTCODE_GETTHEMEBUTTON.md) — стиль кнопки для CF7
- [`[getthemeform]`](SHORTCODE_GETTHEMEFORM.md) — стиль полей для CF7
