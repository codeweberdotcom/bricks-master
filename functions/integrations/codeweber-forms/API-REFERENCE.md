# CodeWeber Forms - API Reference

Краткая справка по API модуля форм.

## REST API Endpoints

### POST `/wp-json/codeweber-forms/v1/submit`

Отправка формы.

**Headers:**
```
Content-Type: application/json
X-WP-Nonce: {wp_rest_nonce}
```

**Request Body:**
```json
{
  "form_id": "123",
  "fields": {
    "name": "Иван Иванов",
    "email": "ivan@example.com",
    "message": "Текст сообщения"
  },
  "nonce": "wp_rest_nonce",
  "honeypot": ""
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Thank you! Your message has been sent.",
  "submission_id": 456
}
```

**Response (400 Bad Request):**
```json
{
  "code": "validation_error",
  "message": "Email field is required.",
  "data": {
    "status": 400
  }
}
```

---

### POST `/wp-json/codeweber-forms/v1/form-opened`

Отслеживание открытия формы.

**Request Body:**
```json
{
  "form_id": "123"
}
```

**Response:**
```json
{
  "success": true
}
```

---

## PHP Hooks

### `codeweber_form_before_send`
```php
add_action('codeweber_form_before_send', function($form_id, $form_settings, $fields) {
    // Код
}, 10, 3);
```

### `codeweber_form_after_saved`
```php
add_action('codeweber_form_after_saved', function($submission_id, $form_id, $form_data) {
    // Код
}, 10, 3);
```

### `codeweber_form_after_send`
```php
add_action('codeweber_form_after_send', function($form_id, $form_settings, $submission_id) {
    // Код
}, 10, 3);
```

### `codeweber_form_send_error`
```php
add_action('codeweber_form_send_error', function($form_id, $form_data, $error) {
    // Код
}, 10, 3);
```

### `codeweber_form_opened`
```php
add_action('codeweber_form_opened', function($form_id) {
    // Код
}, 10, 1);
```

---

## JavaScript Events

### `codeweberFormOpened`
```javascript
document.addEventListener('codeweberFormOpened', function(event) {
    const { formId, form } = event.detail;
});
```

### `codeweberFormSubmitting`
```javascript
document.addEventListener('codeweberFormSubmitting', function(event) {
    const { formId, form, formData } = event.detail;
    // Можно отменить: event.preventDefault();
});
```

### `codeweberFormInvalid`
```javascript
document.addEventListener('codeweberFormInvalid', function(event) {
    const { formId, form, message } = event.detail;
});
```

### `codeweberFormSubmitted`
```javascript
document.addEventListener('codeweberFormSubmitted', function(event) {
    const { formId, submissionId, message, apiResponse } = event.detail;
});
```

### `codeweberFormError`
```javascript
document.addEventListener('codeweberFormError', function(event) {
    const { formId, form, message, error } = event.detail;
});
```

---

## Шорткод

```php
[codeweber_form id="123"]
[codeweber_form name="Contact Form"]
```

---

## Переменные в шаблонах писем

- `{form_name}` - Название формы
- `{user_name}` - Имя пользователя
- `{user_email}` - Email пользователя
- `{submission_date}` - Дата отправки
- `{submission_time}` - Время отправки (24ч формат)
- `{form_fields}` - HTML таблица с полями
- `{user_ip}` - IP адрес
- `{user_agent}` - User Agent
- `{site_name}` - Название сайта
- `{site_url}` - URL сайта

---

## Основные классы

- `CodeweberFormsCore` - Основной класс
- `CodeweberFormsAPI` - REST API
- `CodeweberFormsDatabase` - Работа с БД
- `CodeweberFormsMailer` - Отправка email
- `CodeweberFormsRateLimit` - Rate limiting
- `CodeweberFormsHooks` - Хуки
- `CodeweberFormsRenderer` - Рендеринг форм
- `CodeweberFormsShortcode` - Шорткод

---

📖 **Полная документация:** [DOCUMENTATION.md](./DOCUMENTATION.md)


