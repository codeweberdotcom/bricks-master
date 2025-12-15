# CodeWeber Forms Module - Полная документация

## Содержание

1. [Введение](#введение)
2. [Установка и настройка](#установка-и-настройка)
3. [Создание форм](#создание-форм)
4. [Типы полей](#типы-полей)
5. [API документация](#api-документация)
6. [Классы и методы](#классы-и-методы)
7. [REST API Endpoints](#rest-api-endpoints)
8. [Хуки (Hooks)](#хуки-hooks)
9. [Шаблоны писем](#шаблоны-писем)
10. [Настройки](#настройки)
11. [База данных](#база-данных)
12. [Примеры использования](#примеры-использования)
13. [Структура файлов](#структура-файлов)

---

## Введение

CodeWeber Forms Module - это полнофункциональный модуль для создания и управления формами в WordPress, аналогичный Contact Form 7. Модуль интегрирован в тему CodeWeber и предоставляет:

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
- ✅ PHP и JavaScript хуки для расширения

---

## Установка и настройка

Модуль уже интегрирован в тему CodeWeber и находится в:
```
wp-content/themes/codeweber/functions/integrations/codeweber-forms/
```

### Активация

Модуль активируется автоматически при загрузке темы. Никаких дополнительных действий не требуется.

### Первоначальная настройка

1. Перейдите в **Form Submissions → Settings**
2. Настройте параметры по умолчанию:
   - Email получателя
   - Email отправителя
   - Имя отправителя
   - Rate limiting
   - Сообщения по умолчанию

3. Перейдите в **Form Submissions → Email Templates**
4. Настройте шаблоны писем:
   - Уведомление администратора
   - Автоответ пользователю
   - Ответ на отзыв
   - Ответ на резюме

---

## Создание форм

### Способ 1: Через Gutenberg (рекомендуется)

1. Перейдите в **Forms → Add New Form**
2. Добавьте блок **Form** из категории "Codeweber Gutenberg Blocks"
3. Внутри блока формы добавьте блоки **Form Field**
4. Настройте поля в Inspector Controls:
   - Тип поля
   - Label и Placeholder
   - Обязательность
   - Ширина (Bootstrap grid: col-12, col-md-6, col-lg-4, etc.)
   - Валидация
5. Настройте форму в Inspector Controls:
   - Email получателя
   - Email отправителя
   - Тема письма
   - Сообщения об успехе/ошибке
   - Капча и Rate limiting

### Способ 2: Через шорткод

Используйте шорткод на любой странице:

```php
[codeweber_form id="123"]
```

или

```php
[codeweber_form name="Contact Form"]
```

**Параметры шорткода:**
- `id` - ID формы (CPT post ID)
- `name` - Название формы (post title)

---

## Типы полей

### Поддерживаемые типы

| Тип | Описание | HTML элемент |
|-----|----------|--------------|
| `text` | Текстовое поле | `<input type="text">` |
| `email` | Email поле | `<input type="email">` |
| `tel` | Телефон | `<input type="tel">` |
| `url` | URL | `<input type="url">` |
| `textarea` | Многострочный текст | `<textarea>` |
| `select` | Выпадающий список | `<select>` |
| `radio` | Радио кнопки | `<input type="radio">` |
| `checkbox` | Чекбоксы | `<input type="checkbox">` |
| `file` | Загрузка файлов | `<input type="file">` |
| `date` | Дата | `<input type="date">` |
| `time` | Время | `<input type="time">` |
| `number` | Число | `<input type="number">` |
| `hidden` | Скрытое поле | `<input type="hidden">` |

### Настройки полей

Каждое поле может иметь следующие настройки:

- **Label** - Подпись поля
- **Placeholder** - Подсказка в поле
- **Required** - Обязательное поле (добавляет `*` к label)
- **Width** - Ширина в Bootstrap grid (col-12, col-md-6, col-lg-4, etc.)
- **Validation** - Тип валидации (email, url, tel, number, etc.)
- **Options** - Опции для select, radio, checkbox (каждая опция с новой строки)

---

## API документация

### REST API Endpoints

#### POST `/wp-json/codeweber-forms/v1/submit`

Отправка формы.

**Параметры запроса:**
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

**Ответ при успехе:**
```json
{
  "success": true,
  "message": "Thank you! Your message has been sent.",
  "submission_id": 456
}
```

**Ответ при ошибке:**
```json
{
  "code": "validation_error",
  "message": "Email field is required.",
  "data": {
    "status": 400
  }
}
```

#### POST `/wp-json/codeweber-forms/v1/form-opened`

Отслеживание открытия формы на фронтенде.

**Параметры запроса:**
```json
{
  "form_id": "123"
}
```

**Ответ:**
```json
{
  "success": true
}
```

---

## Классы и методы

### CodeweberFormsCore

Основной класс модуля.

**Методы:**

#### `enqueue_assets()`
Подключает скрипты и стили на фронтенде.

#### `render_form($form_id, $form_config)`
Рендерит форму.

**Параметры:**
- `$form_id` (int|string) - ID формы
- `$form_config` (array|WP_Post) - Конфигурация формы или объект поста

**Возвращает:** (string) HTML формы

---

### CodeweberFormsAPI

Класс для обработки REST API запросов.

**Методы:**

#### `register_routes()`
Регистрирует REST API маршруты.

#### `submit_form($request)`
Обрабатывает отправку формы.

**Параметры:**
- `$request` (WP_REST_Request) - Объект запроса

**Возвращает:** (WP_REST_Response|WP_Error)

**Процесс обработки:**
1. Проверка nonce
2. Проверка honeypot
3. Проверка rate limit
4. Получение настроек формы
5. Валидация полей
6. Санитизация данных
7. Обработка файлов
8. Сохранение в БД
9. Отправка email администратору
10. Отправка автоответа пользователю

#### `get_form_settings($form_id)`
Получает настройки формы.

**Параметры:**
- `$form_id` (int) - ID формы

**Возвращает:** (array|false) Настройки формы или false

#### `validate_fields($fields, $form_settings)`
Валидирует поля формы.

**Параметры:**
- `$fields` (array) - Данные полей
- `$form_settings` (array) - Настройки формы

**Возвращает:** (array) Массив ошибок валидации

#### `sanitize_fields($fields)`
Санитизирует данные полей.

**Параметры:**
- `$fields` (array) - Данные полей

**Возвращает:** (array) Санитизированные данные

#### `handle_file_uploads($request)`
Обрабатывает загрузку файлов.

**Параметры:**
- `$request` (WP_REST_Request) - Объект запроса

**Возвращает:** (array) Массив с информацией о загруженных файлах

#### `send_email($form_id, $form_settings, $fields, $files_data, $submission_id, $type)`
Отправляет email.

**Параметры:**
- `$form_id` (int) - ID формы
- `$form_settings` (array) - Настройки формы
- `$fields` (array) - Данные полей
- `$files_data` (array) - Данные файлов
- `$submission_id` (int) - ID отправки
- `$type` (string) - Тип письма ('admin' или 'auto-reply')

**Возвращает:** (array) `['success' => bool, 'error' => string|null]`

#### `send_auto_reply($form_id, $form_settings, $fields, $user_email, $form_type)`
Отправляет автоответ пользователю.

**Параметры:**
- `$form_id` (int) - ID формы
- `$form_settings` (array) - Настройки формы
- `$fields` (array) - Данные полей
- `$user_email` (string) - Email пользователя
- `$form_type` (string) - Тип формы ('testimonial', 'resume', 'default')

**Возвращает:** (bool) Успех отправки

#### `get_email_template($form_id, $type)`
Получает шаблон письма.

**Параметры:**
- `$form_id` (int) - ID формы
- `$type` (string) - Тип шаблона ('admin', 'auto-reply', 'testimonial-reply', 'resume-reply')

**Возвращает:** (array) `['subject' => string, 'template' => string]`

#### `detect_form_type($form_id, $form_settings)`
Определяет тип формы.

**Параметры:**
- `$form_id` (int) - ID формы
- `$form_settings` (array) - Настройки формы

**Возвращает:** (string) Тип формы ('testimonial', 'resume', 'default')

#### `form_opened($request)`
Обрабатывает событие открытия формы.

**Параметры:**
- `$request` (WP_REST_Request) - Объект запроса

**Возвращает:** (WP_REST_Response)

#### `get_client_ip()`
Получает IP адрес клиента.

**Возвращает:** (string) IP адрес

---

### CodeweberFormsDatabase

Класс для работы с базой данных.

**Методы:**

#### `create_table()`
Создает таблицу для отправок.

**Структура таблицы:**
- `id` (bigint) - ID записи
- `form_id` (bigint) - ID формы
- `form_name` (varchar) - Название формы
- `submission_data` (longtext) - JSON с данными полей
- `files_data` (longtext) - JSON с данными файлов
- `ip_address` (varchar) - IP адрес
- `user_agent` (text) - User Agent
- `user_id` (bigint) - ID пользователя (если авторизован)
- `status` (varchar) - Статус (new, read, archived, deleted)
- `email_sent` (tinyint) - Статус отправки email
- `email_error` (text) - Ошибка отправки email
- `created_at` (datetime) - Дата создания
- `updated_at` (datetime) - Дата обновления

#### `save_submission($data)`
Сохраняет отправку в БД.

**Параметры:**
- `$data` (array) - Данные отправки

**Возвращает:** (int|false) ID записи или false

#### `get_submission($id)`
Получает отправку по ID.

**Параметры:**
- `$id` (int) - ID отправки

**Возвращает:** (object|null) Объект отправки или null

#### `get_submissions($args = [])`
Получает список отправок.

**Параметры:**
- `$args` (array) - Аргументы запроса:
  - `form_id` (int) - Фильтр по ID формы
  - `status` (string) - Фильтр по статусу
  - `limit` (int) - Лимит записей
  - `offset` (int) - Смещение
  - `orderby` (string) - Поле сортировки
  - `order` (string) - Направление сортировки (ASC/DESC)

**Возвращает:** (array) Массив объектов отправок

#### `count_submissions($args = [])`
Подсчитывает количество отправок.

**Параметры:**
- `$args` (array) - Аргументы фильтрации

**Возвращает:** (int) Количество записей

#### `update_submission_status($id, $status)`
Обновляет статус отправки.

**Параметры:**
- `$id` (int) - ID отправки
- `$status` (string) - Новый статус

**Возвращает:** (bool) Успех обновления

#### `update_submission($id, $data)`
Обновляет данные отправки.

**Параметры:**
- `$id` (int) - ID отправки
- `$data` (array) - Данные для обновления

**Возвращает:** (bool) Успех обновления

#### `delete_submission($id)`
Удаляет отправку (помечает как deleted).

**Параметры:**
- `$id` (int) - ID отправки

**Возвращает:** (bool) Успех удаления

#### `permanently_delete_submission($id)`
Полностью удаляет отправку из БД.

**Параметры:**
- `$id` (int) - ID отправки

**Возвращает:** (bool) Успех удаления

#### `bulk_delete_submissions($ids)`
Массовое удаление отправок.

**Параметры:**
- `$ids` (array) - Массив ID отправок

**Возвращает:** (int) Количество удаленных записей

---

### CodeweberFormsMailer

Класс для отправки email.

**Методы:**

#### `send($to, $subject, $message, $headers = [])`
Отправляет email через `wp_mail`.

**Параметры:**
- `$to` (string|array) - Получатель(и)
- `$subject` (string) - Тема письма
- `$message` (string) - Текст письма
- `$headers` (array) - Заголовки письма

**Возвращает:** (bool) Успех отправки

#### `send_via_smtp($to, $subject, $message, $headers = [])`
Отправляет email через SMTP (Redux настройки).

**Параметры:**
- `$to` (string|array) - Получатель(и)
- `$subject` (string) - Тема письма
- `$message` (string) - Текст письма
- `$headers` (array) - Заголовки письма

**Возвращает:** (bool) Успех отправки

#### `process_template($template, $variables)`
Обрабатывает шаблон письма, заменяя переменные.

**Параметры:**
- `$template` (string) - Шаблон письма
- `$variables` (array) - Массив переменных для замены

**Возвращает:** (string) Обработанный шаблон

**Доступные переменные:**
- `{form_name}` - Название формы
- `{user_name}` - Имя пользователя
- `{user_email}` - Email пользователя
- `{submission_date}` - Дата отправки
- `{submission_time}` - Время отправки (24-часовой формат)
- `{form_fields}` - HTML таблица с полями формы
- `{user_ip}` - IP адрес пользователя
- `{user_agent}` - User Agent
- `{site_name}` - Название сайта
- `{site_url}` - URL сайта

#### `format_form_fields($fields)`
Форматирует поля формы в HTML таблицу.

**Параметры:**
- `$fields` (array) - Данные полей

**Возвращает:** (string) HTML таблица

---

### CodeweberFormsRateLimit

Класс для ограничения частоты отправок.

**Методы:**

#### `check($form_id, $ip_address, $user_id = 0)`
Проверяет, не превышен ли лимит отправок.

**Параметры:**
- `$form_id` (int) - ID формы
- `$ip_address` (string) - IP адрес
- `$user_id` (int) - ID пользователя (опционально)

**Возвращает:** (bool) true если лимит не превышен, false если превышен

**Логика:**
- Проверяет настройки rate limit из опций
- По умолчанию: 5 отправок в час с одного IP
- Настройки можно изменить в админке: **Form Submissions → Settings**

---

### CodeweberFormsHooks

Класс для работы с хуками.

**Методы:**

#### `before_send($form_id, $form_data, $fields)`
Вызывает хук перед отправкой формы.

**Параметры:**
- `$form_id` (int) - ID формы
- `$form_data` (array) - Настройки формы
- `$fields` (array) - Данные полей

**Хук:** `codeweber_form_before_send`

#### `after_send($form_id, $form_data, $submission_id)`
Вызывает хук после отправки формы.

**Параметры:**
- `$form_id` (int) - ID формы
- `$form_data` (array) - Настройки формы
- `$submission_id` (int) - ID отправки

**Хук:** `codeweber_form_after_send`

#### `after_saved($submission_id, $form_id, $form_data)`
Вызывает хук после сохранения отправки в БД.

**Параметры:**
- `$submission_id` (int) - ID отправки
- `$form_id` (int) - ID формы
- `$form_data` (array) - Данные полей

**Хук:** `codeweber_form_after_saved`

#### `send_error($form_id, $form_data, $error)`
Вызывает хук при ошибке отправки.

**Параметры:**
- `$form_id` (int) - ID формы
- `$form_data` (array) - Настройки формы или пустой массив
- `$error` (string) - Сообщение об ошибке

**Хук:** `codeweber_form_send_error`

#### `form_opened($form_id)`
Вызывает хук при открытии формы на фронтенде.

**Параметры:**
- `$form_id` (int) - ID формы

**Хук:** `codeweber_form_opened`

---

### CodeweberFormsRenderer

Класс для рендеринга форм на фронтенде.

**Методы:**

#### `render($form_id, $form_config)`
Рендерит форму.

**Параметры:**
- `$form_id` (int|string) - ID формы
- `$form_config` (array|WP_Post) - Конфигурация формы

**Возвращает:** (string) HTML формы

#### `render_from_cpt($post)`
Рендерит форму из CPT поста.

**Параметры:**
- `$post` (WP_Post) - Объект поста

**Возвращает:** (string) HTML формы

#### `render_from_config($form_id, $config)`
Рендерит форму из массива конфигурации.

**Параметры:**
- `$form_id` (int|string) - ID формы
- `$config` (array) - Конфигурация формы

**Возвращает:** (string) HTML формы

#### `render_field($field)`
Рендерит одно поле формы.

**Параметры:**
- `$field` (array) - Конфигурация поля

**Возвращает:** (string) HTML поля

---

### CodeweberFormsShortcode

Класс для работы с шорткодом.

**Методы:**

#### `render_shortcode($atts)`
Обрабатывает шорткод `[codeweber_form]`.

**Параметры:**
- `$atts` (array) - Атрибуты шорткода:
  - `id` (string) - ID формы
  - `name` (string) - Название формы

**Возвращает:** (string) HTML формы

---

## REST API Endpoints

### POST `/wp-json/codeweber-forms/v1/submit`

Отправка формы.

**Требования:**
- Nonce в заголовке `X-WP-Nonce`
- Content-Type: `application/json`

**Тело запроса:**
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

**Ответы:**

**200 OK (успех):**
```json
{
  "success": true,
  "message": "Thank you! Your message has been sent.",
  "submission_id": 456
}
```

**400 Bad Request (ошибка валидации):**
```json
{
  "code": "validation_error",
  "message": "Email field is required.",
  "data": {
    "status": 400
  }
}
```

**403 Forbidden (спам):**
```json
{
  "code": "spam_detected",
  "message": "Spam detected.",
  "data": {
    "status": 403
  }
}
```

**429 Too Many Requests (rate limit):**
```json
{
  "code": "rate_limit_exceeded",
  "message": "Too many submissions. Please try again later.",
  "data": {
    "status": 429
  }
}
```

### POST `/wp-json/codeweber-forms/v1/form-opened`

Отслеживание открытия формы.

**Тело запроса:**
```json
{
  "form_id": "123"
}
```

**Ответ:**
```json
{
  "success": true
}
```

---

## Хуки (Hooks)

### PHP хуки (серверная сторона)

#### `codeweber_form_before_send`

Вызывается перед отправкой формы.

**Параметры:**
- `$form_id` (int) - ID формы
- `$form_settings` (array) - Настройки формы
- `$fields` (array) - Данные полей

**Пример:**
```php
add_action('codeweber_form_before_send', function($form_id, $form_settings, $fields) {
    // Логирование
    error_log("Form $form_id is being submitted");
    
    // Модификация данных
    $fields['custom_field'] = 'custom_value';
}, 10, 3);
```

#### `codeweber_form_after_saved`

Вызывается после сохранения отправки в БД.

**Параметры:**
- `$submission_id` (int) - ID отправки
- `$form_id` (int) - ID формы
- `$form_data` (array) - Данные полей

**Пример:**
```php
add_action('codeweber_form_after_saved', function($submission_id, $form_id, $form_data) {
    // Интеграция с CRM
    // Отправка в другую систему
}, 10, 3);
```

#### `codeweber_form_after_send`

Вызывается после успешной отправки формы.

**Параметры:**
- `$form_id` (int) - ID формы
- `$form_settings` (array) - Настройки формы
- `$submission_id` (int) - ID отправки

**Пример:**
```php
add_action('codeweber_form_after_send', function($form_id, $form_settings, $submission_id) {
    // Уведомление в Slack, Telegram и т.д.
}, 10, 3);
```

#### `codeweber_form_send_error`

Вызывается при ошибке отправки.

**Параметры:**
- `$form_id` (int) - ID формы
- `$form_data` (array) - Настройки формы или пустой массив
- `$error` (string) - Сообщение об ошибке

**Пример:**
```php
add_action('codeweber_form_send_error', function($form_id, $form_data, $error) {
    error_log("Form error: $error");
    // Отправка уведомления администратору
}, 10, 3);
```

#### `codeweber_form_opened`

Вызывается при открытии формы на фронтенде.

**Параметры:**
- `$form_id` (int) - ID формы

**Пример:**
```php
add_action('codeweber_form_opened', function($form_id) {
    // Логирование для аналитики
    // Отслеживание просмотров форм
}, 10, 1);
```

### JavaScript хуки (клиентская сторона)

#### `codeweberFormOpened`

Событие срабатывает при загрузке формы на странице.

**Event Detail:**
```javascript
{
  formId: "123",
  form: HTMLElement // DOM элемент формы
}
```

**Пример:**
```javascript
document.addEventListener('codeweberFormOpened', function(event) {
    const formId = event.detail.formId;
    const form = event.detail.form;
    
    // Google Analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', 'form_view', {
            'form_id': formId
        });
    }
});
```

#### `codeweberFormSubmitting`

Событие срабатывает перед отправкой формы. Можно отменить через `preventDefault()`.

**Event Detail:**
```javascript
{
  formId: "123",
  form: HTMLElement,
  formData: FormData
}
```

**Пример:**
```javascript
document.addEventListener('codeweberFormSubmitting', function(event) {
    const form = event.detail.form;
    
    // Дополнительная валидация
    if (!confirm('Вы уверены?')) {
        event.preventDefault(); // Отменить отправку
    }
});
```

#### `codeweberFormInvalid`

Событие срабатывает при ошибке валидации формы.

**Event Detail:**
```javascript
{
  formId: "123",
  form: HTMLElement,
  message: "Form validation failed"
}
```

**Пример:**
```javascript
document.addEventListener('codeweberFormInvalid', function(event) {
    console.log('Validation failed:', event.detail.message);
    // Показать дополнительное уведомление
});
```

#### `codeweberFormSubmitted`

Событие срабатывает при успешной отправке формы.

**Event Detail:**
```javascript
{
  formId: "123",
  form: HTMLElement,
  submissionId: 456,
  message: "Thank you! Your message has been sent.",
  apiResponse: Object // Полный ответ API
}
```

**Пример:**
```javascript
document.addEventListener('codeweberFormSubmitted', function(event) {
    const form = event.detail.form;
    const submissionId = event.detail.submissionId;
    
    // Закрыть модальное окно
    const modal = form.closest('.modal');
    if (modal && typeof bootstrap !== 'undefined') {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) {
            setTimeout(() => bsModal.hide(), 2000);
        }
    }
    
    // Google Analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', 'form_submit', {
            'form_id': event.detail.formId,
            'submission_id': submissionId
        });
    }
});
```

#### `codeweberFormError`

Событие срабатывает при ошибке отправки (сеть или сервер).

**Event Detail:**
```javascript
{
  formId: "123",
  form: HTMLElement,
  message: "An error occurred",
  error: Error // Объект ошибки (если есть)
}
```

**Пример:**
```javascript
document.addEventListener('codeweberFormError', function(event) {
    console.error('Form error:', event.detail.message);
    // Показать дополнительное уведомление об ошибке
});
```

---

## Шаблоны писем

### Настройка шаблонов

Шаблоны писем настраиваются в админке: **Form Submissions → Email Templates**

### Доступные шаблоны

1. **Уведомление администратора** - отправляется администратору при получении отправки
2. **Автоответ пользователю** - отправляется пользователю после отправки формы
3. **Ответ на отзыв** - специальный шаблон для формы отзывов
4. **Ответ на резюме** - специальный шаблон для формы резюме

### Переменные в шаблонах

Доступные переменные для замены в шаблонах:

| Переменная | Описание |
|------------|----------|
| `{form_name}` | Название формы |
| `{user_name}` | Имя пользователя (из поля name или email) |
| `{user_email}` | Email пользователя |
| `{submission_date}` | Дата отправки |
| `{submission_time}` | Время отправки (24-часовой формат, например: 14:30) |
| `{form_fields}` | HTML таблица со всеми полями формы |
| `{user_ip}` | IP адрес пользователя |
| `{user_agent}` | User Agent браузера |
| `{site_name}` | Название сайта |
| `{site_url}` | URL сайта |

### Пример шаблона

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0073aa; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Спасибо за обращение!</h2>
        </div>
        <div class="content">
            Здравствуйте, {user_name}!
            
            Мы получили ваше сообщение и свяжемся с вами в ближайшее время.
            
            <p><strong>Дата:</strong> {submission_date} {submission_time}</p>
            
            С уважением,<br>
            {site_name}
        </div>
    </div>
</body>
</html>
```

---

## Настройки

### Глобальные настройки

**Form Submissions → Settings**

#### Email Settings

- **Default Recipient Email** - Email получателя по умолчанию
- **Default Sender Email** - Email отправителя по умолчанию
- **Default Sender Name** - Имя отправителя по умолчанию
- **Default Subject** - Тема письма по умолчанию

#### Rate Limiting

- **Enable Rate Limiting** - Включить ограничение частоты отправок
- **Max Submissions Per Hour** - Максимальное количество отправок в час
- **Max Submissions Per Day** - Максимальное количество отправок в день

#### Default Messages

- **Success Message** - Сообщение об успешной отправке
- **Error Message** - Сообщение об ошибке

### Настройки шаблонов писем

**Form Submissions → Email Templates**

Для каждого шаблона можно настроить:

- **Enable** - Включить/выключить шаблон
- **Subject** - Тема письма
- **Template** - HTML шаблон письма

---

## База данных

### Таблица `wp_codeweber_forms_submissions`

Структура таблицы для хранения отправок:

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | bigint(20) | ID записи (автоинкремент) |
| `form_id` | bigint(20) | ID формы (CPT post ID) |
| `form_name` | varchar(255) | Название формы |
| `submission_data` | longtext | JSON с данными полей |
| `files_data` | longtext | JSON с информацией о файлах |
| `ip_address` | varchar(45) | IP адрес пользователя |
| `user_agent` | text | User Agent браузера |
| `user_id` | bigint(20) | ID пользователя (если авторизован) |
| `status` | varchar(20) | Статус (new, read, archived, deleted) |
| `email_sent` | tinyint(1) | Статус отправки email (0/1) |
| `email_error` | text | Ошибка отправки email (если есть) |
| `created_at` | datetime | Дата создания |
| `updated_at` | datetime | Дата обновления |

### Статусы отправок

- `new` - Новая отправка (по умолчанию)
- `read` - Прочитана администратором
- `archived` - Архивирована
- `deleted` - Удалена (мягкое удаление)

---

## Примеры использования

### Пример 1: Интеграция с CRM

```php
// functions.php или в отдельном файле плагина

add_action('codeweber_form_after_saved', function($submission_id, $form_id, $form_data) {
    $db = new CodeweberFormsDatabase();
    $submission = $db->get_submission($submission_id);
    
    // Отправка в CRM
    $crm_data = [
        'name' => $form_data['name'] ?? '',
        'email' => $form_data['email'] ?? '',
        'phone' => $form_data['phone'] ?? '',
        'message' => $form_data['message'] ?? '',
    ];
    
    // API запрос к CRM
    wp_remote_post('https://crm.example.com/api/leads', [
        'body' => json_encode($crm_data),
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer YOUR_API_KEY'
        ]
    ]);
}, 10, 3);
```

### Пример 2: Уведомление в Telegram

```php
add_action('codeweber_form_after_send', function($form_id, $form_settings, $submission_id) {
    $db = new CodeweberFormsDatabase();
    $submission = $db->get_submission($submission_id);
    $data = json_decode($submission->submission_data, true);
    
    $message = "Новая отправка формы!\n";
    $message .= "Форма: {$submission->form_name}\n";
    $message .= "От: {$data['name']} ({$data['email']})\n";
    $message .= "Сообщение: {$data['message']}";
    
    // Отправка в Telegram
    wp_remote_post('https://api.telegram.org/botYOUR_BOT_TOKEN/sendMessage', [
        'body' => [
            'chat_id' => 'YOUR_CHAT_ID',
            'text' => $message
        ]
    ]);
}, 10, 3);
```

### Пример 3: Закрытие модального окна после отправки

```javascript
// В вашем JavaScript файле

document.addEventListener('codeweberFormSubmitted', function(event) {
    const form = event.detail.form;
    const modal = form.closest('.modal');
    
    if (modal) {
        // Bootstrap 5
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                setTimeout(() => bsModal.hide(), 2000);
            }
        }
        // Bootstrap 4 / jQuery
        else if (typeof jQuery !== 'undefined' && jQuery(modal).modal) {
            setTimeout(() => {
                jQuery(modal).modal('hide');
            }, 2000);
        }
    }
});
```

### Пример 4: Google Analytics отслеживание

```javascript
// Отслеживание просмотра формы
document.addEventListener('codeweberFormOpened', function(event) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'form_view', {
            'form_id': event.detail.formId,
            'event_category': 'Forms',
            'event_label': 'Form Opened'
        });
    }
});

// Отслеживание отправки формы
document.addEventListener('codeweberFormSubmitted', function(event) {
    if (typeof gtag !== 'undefined') {
        gtag('event', 'form_submit', {
            'form_id': event.detail.formId,
            'submission_id': event.detail.submissionId,
            'event_category': 'Forms',
            'event_label': 'Form Submitted'
        });
    }
});
```

### Пример 5: Кастомная валидация через JavaScript

```javascript
document.addEventListener('codeweberFormSubmitting', function(event) {
    const form = event.detail.form;
    const phoneField = form.querySelector('input[name="phone"]');
    
    if (phoneField) {
        const phone = phoneField.value;
        // Проверка формата телефона
        const phoneRegex = /^\+?[1-9]\d{1,14}$/;
        
        if (!phoneRegex.test(phone)) {
            alert('Пожалуйста, введите корректный номер телефона');
            event.preventDefault(); // Отменить отправку
        }
    }
});
```

---

## Структура файлов

```
codeweber-forms/
├── admin/
│   ├── codeweber-forms-admin.php          # Главная админ-панель
│   ├── codeweber-forms-settings.php       # Настройки модуля
│   ├── codeweber-forms-email-templates.php # Управление шаблонами писем
│   └── codeweber-forms-submissions.php     # Просмотр отправок
├── assets/
│   ├── css/
│   │   └── forms.css                       # Стили форм
│   └── js/
│       └── form-submit.js                  # JavaScript для отправки
├── languages/
│   ├── codeweber-forms-ru_RU.po            # Переводы (PO файл)
│   ├── codeweber-forms-ru_RU.mo            # Переводы (скомпилированный)
│   └── README.md                           # Инструкции по переводам
├── codeweber-forms-init.php                # Инициализация модуля
├── codeweber-forms-cpt.php                 # Регистрация CPT для форм
├── codeweber-forms-database.php            # Работа с БД
├── codeweber-forms-core.php                # Основной класс
├── codeweber-forms-api.php                 # REST API
├── codeweber-forms-validator.php           # Валидация полей
├── codeweber-forms-sanitizer.php           # Санитизация данных
├── codeweber-forms-mailer.php              # Отправка email
├── codeweber-forms-rate-limit.php          # Rate limiting
├── codeweber-forms-hooks.php               # Хуки
├── codeweber-forms-renderer.php            # Рендеринг форм
├── codeweber-forms-shortcode.php           # Шорткод
├── README.md                               # Краткая документация
└── DOCUMENTATION.md                        # Полная документация (этот файл)
```

---

## Константы

Модуль определяет следующие константы:

- `CODEWEBER_FORMS_PATH` - Путь к директории модуля
- `CODEWEBER_FORMS_URL` - URL директории модуля
- `CODEWEBER_FORMS_VERSION` - Версия модуля
- `CODEWEBER_FORMS_LANGUAGES` - Путь к директории с переводами

---

## Версия

Текущая версия модуля: **1.0.0**

---

## Поддержка

Для вопросов и предложений обращайтесь к разработчикам темы CodeWeber.

---

## Лицензия

Модуль является частью темы CodeWeber и следует лицензии темы.


