# Telegram Bot — модуль уведомлений

Модуль темы для отправки server-side уведомлений в Telegram. Подключается как канал к **CW_Notify** через хук `cw_notify_server_notification`.

**Расположение:** `functions/integrations/telegram/`

---

## Архитектура

### Файлы модуля

| Файл | Назначение |
|------|-----------|
| `class-cw-telegram-bot.php` | Класс `CW_Telegram_Bot` — HTTP-клиент Telegram Bot API |
| `telegram-init.php` | Хуки событий + форматирование сообщений |

### Точка входа

`functions.php` подключает модуль напрямую:

```php
require_once get_template_directory() . '/functions/integrations/telegram/telegram-init.php';
```

`telegram-init.php` сам подключает класс через `require_once __DIR__ . '/class-cw-telegram-bot.php'`.

### Связь с CW_Notify

Модуль не работает в изоляции — он является **каналом CW_Notify**:

```
Событие (form_saved / wc_order / newsletter)
    ↓
CW_Notify::send_server_notification('event', $text)
    ↓  do_action('cw_notify_server_notification', $event, $text)
    ↓
codeweber_telegram_channel()    ← подключён в telegram-init.php
    ↓
CW_Telegram_Bot::from_redux() → send_message($text)
    ↓
Telegram Bot API
```

**CW_Notify** — это общий диспетчер server-side каналов. Любой будущий канал (email, Slack, webhook) подключается тем же способом.

---

## Настройки Redux

**Путь:** Внешний вид → Параметры темы → API → блок «Telegram Bot»

| Ключ Redux | Тип | Описание | По умолчанию |
|-----------|-----|---------|-------------|
| `telegram_bot_enabled` | switch | Включить/выключить модуль | `false` |
| `telegram_bot_token` | password | Bot Token от @BotFather (`123456:ABC-DEF…`) | — |
| `telegram_bot_chat_id` | text | Куда отправлять (chat_id, -100…, @channel) | — |
| `telegram_bot_events` | checkbox | Какие события триггерят уведомление | `form: true`, остальные `false` |

### Поле `telegram_bot_events` — доступные события

| Ключ | Событие | PHP-хук |
|------|---------|---------|
| `form` | Заявка с формы (CodeWeber Forms) | `codeweber_form_saved` |
| `order` | Новый заказ WooCommerce | `woocommerce_checkout_order_created` |
| `newsletter` | Новая подписка на рассылку | `codeweber_newsletter_subscribed` |

### Кнопка «Test»

Отправляет реальное тестовое сообщение в настроенный chat_id. Требует token + chat_id. Реализовано через AJAX action `codeweber_api_test_telegram` в `functions/admin/api-test.php`.

---

## Класс CW_Telegram_Bot

**Файл:** `functions/integrations/telegram/class-cw-telegram-bot.php`

### Публичные методы

```php
// Конструктор — прямая инициализация с известными credentials
$bot = new CW_Telegram_Bot( $token, $chat_id );

// Фабрика из Redux — возвращает null если Telegram выключен или не настроен
$bot = CW_Telegram_Bot::from_redux();

// Отправить сообщение
$ok = $bot->send_message( string $text, string $parse_mode = 'HTML' ): bool
```

### Метод `send_message()`

Отправляет POST-запрос на `https://api.telegram.org/bot{TOKEN}/sendMessage`:

```json
{
  "chat_id": "-1001234567890",
  "text": "...",
  "parse_mode": "HTML",
  "disable_web_page_preview": true
}
```

Использует `wp_remote_post()` с таймаутом 10 секунд. Возвращает `true` если `body.ok === true`.

### Поддерживаемая HTML-разметка Telegram

В тексте сообщения допустимы теги:
- `<b>жирный</b>`
- `<i>курсив</i>`
- `<code>моноширинный</code>`
- `<a href="url">ссылка</a>`

> Всегда экранировать пользовательские данные через `esc_html()` перед вставкой в тег.

### Метод `from_redux()`

Читает настройки из Redux, проверяет включение:

```php
$bot = CW_Telegram_Bot::from_redux();
if ( $bot ) {
    $bot->send_message( '<b>Сработало событие!</b>' );
}
```

Возвращает `null` в случаях:
- `telegram_bot_enabled` = OFF
- token пустой
- chat_id пустой
- Redux не инициализирован

---

## telegram-init.php

### Хук канала (центральная точка)

```php
add_action( 'cw_notify_server_notification', 'codeweber_telegram_channel', 10, 2 );

function codeweber_telegram_channel( string $event, string $text ): void {
    if ( ! codeweber_telegram_event_enabled( $event ) ) return;
    $bot = CW_Telegram_Bot::from_redux();
    if ( $bot ) $bot->send_message( $text );
}
```

### Хуки событий

**CodeWeber Forms** (`codeweber_form_saved`):

```php
add_action( 'codeweber_form_saved', 'codeweber_telegram_on_form_saved', 20, 3 );
// $submission_id (int), $form_id (int|string), $fields (array)
```

Определяет название формы из `get_post($form_id)->post_title`. Вызывает `CW_Notify::send_server_notification('form', $text)`.

**WooCommerce** (`woocommerce_checkout_order_created`):

```php
add_action( 'woocommerce_checkout_order_created', 'codeweber_telegram_on_order', 20, 1 );
// $order (WC_Order)
```

Извлекает `order_id`, `billing_first_name`, `billing_last_name`, `billing_phone`, `billing_email`, `formatted_order_total`. Вызывает `CW_Notify::send_server_notification('order', $text)`.

**Newsletter** (`codeweber_newsletter_subscribed`):

```php
add_action( 'codeweber_newsletter_subscribed', 'codeweber_telegram_on_newsletter', 20, 1 );
// $email (string)
```

Вызывает `CW_Notify::send_server_notification('newsletter', $text)`.

---

## CW_Notify — расширение

**Файл:** `functions/lib/cw-notify/class-cw-notify.php`

Добавлен статический метод:

```php
public static function send_server_notification( string $event, string $text ): void {
    do_action( 'cw_notify_server_notification', $event, $text );
}
```

Этот метод — единственный правильный способ отправить server-side уведомление из любого места темы.

### Как добавить новый канал (Slack, webhook, email)

```php
// В своём init-файле:
add_action( 'cw_notify_server_notification', function ( string $event, string $text ) {
    if ( $event !== 'form' ) return;
    // отправить куда нужно
}, 10, 2 );
```

---

## Формат сообщений

### Форма (form)

```
📬 <b>Название сайта</b>
New form submission: <b>Название формы</b>
#42

<b>Name:</b> Иван Иванов
<b>Phone:</b> +79991234567
<b>Message:</b> Текст сообщения

🕐 30.04.2026 14:35
```

Пропускаются:
- служебные поля: `_utm_data`, `newsletter_consents`
- UUID FilePond (файлы уже отправлены как вложения в email)
- пустые значения

### Заказ WooCommerce (order)

```
🛒 <b>Название сайта</b>
New order <b>#123</b>

<b>Name:</b> Иван Иванов
<b>Phone:</b> +79991234567
<b>Email:</b> ivan@example.com
<b>Total:</b> 4 500 ₽

🕐 30.04.2026 14:35
```

### Подписка (newsletter)

```
📧 <b>Название сайта</b>
New newsletter subscription
ivan@example.com

🕐 30.04.2026 14:35
```

### Тест (кнопка Test в Admin)

```
✅ Test CodeWeber — bot connected!
```

---

## Функции-хелперы

### `codeweber_telegram_event_enabled( string $event ): bool`

Проверяет, включено ли событие в `telegram_bot_events`:

```php
codeweber_telegram_event_enabled( 'form' )       // true/false
codeweber_telegram_event_enabled( 'order' )      // true/false
codeweber_telegram_event_enabled( 'newsletter' ) // true/false
```

Возвращает `false` если Redux не загружен.

### `codeweber_telegram_format_form( int $submission_id, string $form_name, array $fields ): string`

Форматирует HTML-текст уведомления из полей формы. Используется внутри `codeweber_telegram_on_form_saved()`.

---

## Использование из кода

### Отправить произвольное уведомление

```php
// Через CW_Notify (рекомендуется) — отправит во все подключённые каналы
CW_Notify::send_server_notification( 'form', '<b>Новая заявка!</b>' );

// Напрямую через Telegram (если нужен только Telegram)
$bot = CW_Telegram_Bot::from_redux();
if ( $bot ) {
    $bot->send_message( '<b>Новая заявка!</b>' );
}
```

### Отправить из child-темы (например, при кастомном событии)

```php
// 1. Через существующий тип события
CW_Notify::send_server_notification( 'order', 'Заказ #' . $order_id );

// 2. Зарегистрировать новый тип события через хук
add_action( 'cw_notify_server_notification', function ( $event, $text ) {
    if ( $event !== 'my_event' ) return;
    // обработать
}, 10, 2 );
CW_Notify::send_server_notification( 'my_event', 'Сообщение' );
```

---

## Admin: AJAX Test

**Файл:** `functions/admin/api-test.php`

**AJAX action:** `codeweber_api_test_telegram`

**Параметры POST:**

| Поле | Тип | Описание |
|------|-----|---------|
| `nonce` | string | `codeweber_api_test` |
| `token` | string | Bot Token |
| `chat_id` | string | Chat/channel ID |

**Ответы:**

```json
// Успех
{ "success": true, "data": { "message": "Message sent successfully" } }

// Ошибка токена/чата
{ "success": false, "data": { "message": "Telegram: chat not found" } }
```

**JS:** `functions/admin/api-test.js` — кнопка с `data-field="telegram_bot_token" data-field2="telegram_bot_chat_id"`. Поддержка двух полей (`data-field2`) добавлена в рамках этого модуля.

---

## Настройка бота (пошагово)

1. Открыть Telegram, найти **@BotFather**
2. Написать `/newbot`, задать имя и username
3. Скопировать **Bot Token** (`123456789:ABCdef...`)
4. Добавить бота в чат/канал как администратора
5. Получить **Chat ID** — для канала: `@my_channel_name`, для группы: числовой ID (можно узнать через `@userinfobot` или Telegram API)
6. В Redux: вставить token и chat_id, включить нужные события, нажать «Test»

> **Важно для закрытых каналов:** бот должен быть добавлен как администратор со правом отправки сообщений.

---

## Gotchas

### Chat ID для супергрупп и каналов

- Публичный канал: `@channel_username`
- Приватный канал / супергруппа: числовой ID вида `-1001234567890` (начинается с `-100`)
- Личный чат с ботом: ваш числовой user ID (бот должен получить хотя бы одно сообщение от вас)

### HTML-экранирование

`esc_html()` экранирует `<`, `>`, `&` в `&lt;`, `&gt;`, `&amp;`. Telegram HTML-парсер их принимает корректно. **Не использовать** `wp_kses()` перед отправкой — он уберёт теги форматирования Telegram.

### Таймаут wp_remote_post

Таймаут 10 секунд. Если сервер медленно отвечает — уведомление может задержать ответ пользователю на форме, т.к. хук `codeweber_form_saved` синхронный. При необходимости перенести отправку в `wp_schedule_single_event()`.

### Событие `codeweber_newsletter_subscribed`

Хук должен быть задействован в модуле рассылки. Убедиться, что `do_action('codeweber_newsletter_subscribed', $email)` вызывается в `newsletter-init.php` при успешной подписке.
