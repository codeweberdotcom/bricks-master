# CodeWeber Forms Module

Модуль форм для темы CodeWeber - аналог Contact Form 7.

## Документация

- 📖 **[Полная документация](./DOCUMENTATION.md)** - Детальная информация о всех классах, методах, API и примерах использования
- 🔌 **[API Reference](./API-REFERENCE.md)** - Краткая справка по REST API, хукам и событиям

## Возможности

- ✅ Создание форм через Gutenberg блоки
- ✅ Все типы полей (text, email, textarea, select, radio, checkbox, file, etc.)
- ✅ Сохранение всех отправок в БД
- ✅ Отправка email через SMTP (интеграция с Redux)
- ✅ Rate limiting для защиты от спама
- ✅ Honeypot капча
- ✅ Валидация и санитизация данных
- ✅ Админ-панель для просмотра отправок
- ✅ Шаблоны писем (настройки в админке)
- ✅ Шорткод для использования форм

## Создание формы

### Способ 1: Через Gutenberg (рекомендуется)

1. Перейдите в **Forms → Add New Form**
2. Добавьте блок **Form** из категории "Codeweber Gutenberg Blocks"
3. Внутри блока формы добавьте блоки **Form Field**
4. Настройте поля в Inspector Controls:
   - Тип поля
   - Label и Placeholder
   - Обязательность
   - Ширина (Bootstrap grid)
   - Валидация
5. Настройте форму в Inspector Controls:
   - Email получателя
   - Email отправителя
   - Тема письма
   - Сообщения об успехе/ошибке
   - Капча и Rate limiting

### Способ 2: Через шорткод

Используйте шорткод на любой странице:
```
[codeweber_form id="123"]
```
или
```
[codeweber_form name="Contact Form"]
```

## Типы полей

- `text` - Текстовое поле
- `email` - Email поле
- `tel` - Телефон
- `url` - URL
- `textarea` - Многострочный текст
- `select` - Выпадающий список
- `radio` - Радио кнопки
- `checkbox` - Чекбоксы
- `file` - Загрузка файлов (одиночное/множественное)
- `date` - Дата
- `time` - Время
- `number` - Число
- `hidden` - Скрытое поле

## Просмотр отправок

Все отправки сохраняются в БД и доступны в админке:
- **Form Submissions → All Submissions** - список всех отправок
- Фильтрация по форме, статусу, дате
- Просмотр детальной информации об отправке
- Экспорт в CSV

## Настройки

- **Form Submissions → Settings** - настройки модуля по умолчанию
- **Form Submissions → Email Templates** - шаблоны писем

## Хуки

Модуль предоставляет PHP и JavaScript хуки для расширения функциональности (аналогично Contact Form 7).

### PHP хуки (серверная сторона)

```php
// Перед отправкой формы
add_action('codeweber_form_before_send', function($form_id, $form_settings, $fields) {
    // Ваш код
}, 10, 3);

// После сохранения в БД
add_action('codeweber_form_after_saved', function($submission_id, $form_id, $form_data) {
    // Ваш код
}, 10, 3);

// После успешной отправки
add_action('codeweber_form_after_send', function($form_id, $form_settings, $submission_id) {
    // Ваш код
}, 10, 3);

// При ошибке отправки
add_action('codeweber_form_send_error', function($form_id, $form_data, $error) {
    error_log("Form error: $error");
}, 10, 3);

// При открытии формы на фронтенде
add_action('codeweber_form_opened', function($form_id) {
    // Логирование или аналитика
}, 10, 1);
```

### JavaScript хуки (клиентская сторона)

```javascript
// Форма открыта (загружена на странице)
document.addEventListener('codeweberFormOpened', function(event) {
    const formId = event.detail.formId;
    const form = event.detail.form;
    console.log('Form opened:', formId);
});

// Форма отправляется (можно отменить через preventDefault)
document.addEventListener('codeweberFormSubmitting', function(event) {
    const formId = event.detail.formId;
    const form = event.detail.form;
    const formData = event.detail.formData;
    
    // Можно отменить отправку
    // event.preventDefault();
});

// Ошибка валидации
document.addEventListener('codeweberFormInvalid', function(event) {
    const formId = event.detail.formId;
    const form = event.detail.form;
    console.log('Validation failed:', event.detail.message);
});

// Успешная отправка
document.addEventListener('codeweberFormSubmitted', function(event) {
    const formId = event.detail.formId;
    const submissionId = event.detail.submissionId;
    const message = event.detail.message;
    
    // Закрыть модальное окно, показать уведомление и т.д.
    console.log('Form submitted successfully:', submissionId);
});

// Ошибка отправки (сеть или сервер)
document.addEventListener('codeweberFormError', function(event) {
    const formId = event.detail.formId;
    const message = event.detail.message;
    console.error('Form error:', message);
});
```

### Пример использования JavaScript хуков

```javascript
// Закрыть модальное окно после успешной отправки (как в CF7)
document.addEventListener('codeweberFormSubmitted', function(event) {
    const form = event.detail.form;
    const modal = form.closest('.modal');
    
    if (modal && typeof bootstrap !== 'undefined') {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            setTimeout(() => bsModal.hide(), 2000);
        }
    }
});

// Отслеживание аналитики
document.addEventListener('codeweberFormOpened', function(event) {
    // Google Analytics, Matomo и т.д.
    if (typeof gtag !== 'undefined') {
        gtag('event', 'form_view', {
            'form_id': event.detail.formId
        });
    }
});

document.addEventListener('codeweberFormSubmitted', function(event) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'form_submit', {
            'form_id': event.detail.formId,
            'submission_id': event.detail.submissionId
        });
    }
});
```

## Структура БД

Отправки сохраняются в таблице `wp_codeweber_forms_submissions`:
- `form_id` - ID формы (CPT)
- `submission_data` - JSON с данными полей
- `files_data` - JSON с информацией о файлах
- `status` - new, read, archived, deleted
- `email_sent` - статус отправки email

