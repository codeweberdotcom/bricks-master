# Newsletter — модуль email-подписки

## Что делает этот модуль

Собственная система управления email-подписками. Хранит подписчиков в отдельной таблице БД, поддерживает отправку подтверждения, отписку по ссылке и GDPR-экспорт/удаление данных через Personal Data V2.

**Подача форм подписки** — через универсальный `CodeWeberForms` (старый `newsletter.js` удалён).

---

## Файлы модуля

| Файл | Назначение |
|------|-----------|
| `newsletter-init.php` | Инициализация, подключение всех классов |
| `newsletter-core.php` | Класс `NewsletterSubscription` (минимальная оболочка) |
| `newsletter-database.php` | Класс `NewsletterSubscriptionDatabase` — CRUD для таблицы подписок |
| `frontend/newsletter-frontend.php` | Класс `NewsletterSubscriptionFrontend` — обработка отписки по URL |
| `admin/newsletter-admin.php` | Класс `NewsletterSubscriptionAdmin` — список подписчиков в админке |
| `admin/newsletter-settings.php` | Класс `NewsletterSubscriptionSettings` — настройки модуля |
| `admin/newsletter-import-export.php` | Импорт/экспорт подписчиков в CSV |

---

## Структура БД

Таблица: `wp_newsletter_subscriptions`

| Колонка | Тип | Описание |
|---------|-----|---------|
| `id` | BIGINT UNSIGNED PK | Автоинкремент |
| `email` | VARCHAR(100) UNIQUE | Email подписчика |
| `first_name` | VARCHAR(100) | Имя |
| `last_name` | VARCHAR(100) | Фамилия |
| `phone` | VARCHAR(20) | Телефон |
| `ip_address` | VARCHAR(45) | IP при подписке |
| `user_agent` | TEXT | User-Agent браузера |
| `form_id` | VARCHAR(50) | ID формы CodeWeberForms, через которую подписался |
| `user_id` | BIGINT UNSIGNED | ID WordPress-пользователя (0 если незарегистрирован) |
| `status` | ENUM | `pending`, `confirmed`, `unsubscribed`, `trash` |
| `created_at` | DATETIME | Дата подписки |
| `confirmed_at` | DATETIME | Дата подтверждения |
| `unsubscribed_at` | DATETIME | Дата отписки |
| `updated_at` | DATETIME | Дата изменения |
| `unsubscribe_token` | VARCHAR(100) | Токен для ссылки отписки |
| `events_history` | LONGTEXT | JSON-история событий (subscribe, unsubscribe и т.д.) |

Версия схемы: `1.0.5` (хранится в `newsletter_subscription_version` в `wp_options`). Миграции выполняются автоматически через `dbDelta()`.

---

## Классы и их методы

### `NewsletterSubscriptionDatabase`

```php
$db = new NewsletterSubscriptionDatabase();
```

| Метод | Описание |
|-------|---------|
| `get_subscription($email)` | Получить запись подписчика по email |
| `add_subscription($data)` | Добавить нового подписчика |
| `update_subscription($email, $data)` | Обновить данные подписчика |
| `delete_subscription($email)` | Удалить запись |
| `get_subscriptions($where, $limit, $offset)` | Список с фильтрацией (raw SQL where-clause) |
| `count_subscriptions($where)` | Количество записей |

---

### `NewsletterSubscriptionFrontend`

Отвечает за:

1. **Обработку ссылки отписки** — перехватывает URL с `?action=newsletter_unsubscribe&email=...&token=...`:
   - Проверяет токен в БД
   - Меняет статус на `unsubscribed`
   - Записывает событие в `events_history`
   - Отзывает согласие через `codeweber_forms_revoke_user_consent()` (если настроен `codeweber_legal_email_consent`)
   - Редиректит на `/?unsubscribe=success` или `/?unsubscribe=error`

2. **Показ уведомления об отписке** — в `wp_footer`:
   - Читает `?unsubscribe=success/error` из URL
   - Открывает Bootstrap-модал `#modal` с текстом результата
   - После закрытия очищает параметр из URL через `history.replaceState`

### Формирование ссылки отписки

```php
$unsubscribe_url = add_query_arg([
    'action' => 'newsletter_unsubscribe',
    'email'  => urlencode($email),
    'token'  => urlencode($unsubscribe_token),
], home_url('/'));
```

---

### `NewsletterSubscriptionFrontend::send_confirmation_email()`

Отправляет email с подтверждением и ссылкой отписки.

Параметры письма управляются через настройки модуля (`wp_options` ключ `newsletter_subscription_settings`):

| Ключ настройки | По умолчанию |
|---------------|-------------|
| `email_subject` | `'Confirming your subscription to the newsletter'` |
| `from_email` | `get_option('admin_email')` |
| `from_name` | `get_bloginfo('name')` |
| `email_template` | HTML-шаблон по умолчанию |
| `unsubscribe_success` | `'You have successfully unsubscribed...'` |
| `unsubscribe_message` | `'We will no longer send you email notifications.'` |
| `unsubscribe_error` | `'Unsubscribe Error'` |
| `unsubscribe_error_message` | `'Failed to unsubscribe... The link may have expired.'` |

**Плейсхолдеры в `email_template`:**

| Плейсхолдер | Заменяется на |
|-------------|-------------|
| `{email_subject}` | Тема письма |
| `{first_name}` | Имя подписчика |
| `{last_name}` | Фамилия |
| `{email}` | Email подписчика |
| `{unsubscribe_url}` | Ссылка отписки |
| `{site_name}` | Название сайта |

---

## Процесс подписки (сквозной)

```
Пользователь заполняет форму подписки (создана в CodeWeberForms)
    ↓
form-submit-universal.js отправляет данные на REST API codeweber-forms/v1/submit
    ↓
PHP обрабатывает отправку, сохраняет в wp_codeweber_forms_submissions
    ↓
Хук codeweber_form_after_send срабатывает
    ↓ (если в теме настроен обработчик)
Данные записываются в wp_newsletter_subscriptions
    ↓
send_confirmation_email() отправляет письмо с ссылкой отписки
    ↓
Подписчик кликает ссылку в письме
    ↓
handle_unsubscribe_redirect() обрабатывает, статус → unsubscribed
    ↓
Согласие отзывается через Personal Data V2 (Consent_Data_Provider)
```

> Нет встроенного AJAX-хендлера для добавления подписчиков — форма подписки должна быть создана через **CodeWeberForms**, а в хуке `codeweber_form_after_send` нужно самостоятельно вызывать `$db->add_subscription()` или `$db->update_subscription()`.

---

## GDPR-интеграция

Провайдер `Newsletter_Data_Provider` автоматически регистрируется в `Personal_Data_Manager` при инициализации модуля:

```php
add_action('personal_data_v2_ready', function($manager) {
    $provider = new Newsletter_Data_Provider();
    $manager->register_provider($provider);
}, 10);
```

**ID провайдера:** `newsletter-subscription-v2`

Данные для экспорта: email, first_name, last_name, phone, ip_address, user_agent, status, created_at, confirmed_at, unsubscribed_at.

При удалении: запись анонимизируется или удаляется.

---

## Страницы в админке

| Страница | Класс | Функции |
|---------|-------|---------|
| Список подписчиков | `NewsletterSubscriptionAdmin` | Просмотр, поиск, фильтр по статусу |
| Настройки | `NewsletterSubscriptionSettings` | Тема письма, шаблон, тексты отписки |
| Импорт/экспорт | `NewsletterSubscriptionImportExport` | CSV импорт/экспорт списка |

---

## Связь с другими модулями

| Модуль | Связь |
|--------|-------|
| **CodeWeberForms** | Форма подписки создаётся в CF; обработка через хуки `codeweber_form_*` |
| **Personal Data V2** | Провайдер `Newsletter_Data_Provider` экспортирует/удаляет данные для GDPR |
| **Consent** (consent_subscriber) | При отписке вызывается `codeweber_forms_revoke_user_consent()` |
| **Matomo** | Нет прямой интеграции; события подписки можно добавить через `codeweber_form_after_send` |
