# SMS.ru — интеграция

## Что делает этот модуль

Интеграция с API [sms.ru](https://sms.ru/) для отправки SMS-сообщений. Основное применение в теме — **верификация номера телефона пользователя** на страницах WooCommerce (аккаунт, чекаут).

---

## Файлы модуля

| Файл | Назначение |
|------|-----------|
| `functions/integrations/smsru/sms.ru.php` | Класс `SMSRU` — обёртка над API sms.ru |
| `functions/integrations/smsru/callback.php` | Обработчик callback от sms.ru (статусы доставки) |
| `functions/woocommerce.php` | AJAX-хуки верификации телефона (`send_verification_code`, `confirm_verification_code`) |

---

## Настройка (Redux)

| Redux-ключ | Тип | Описание |
|-----------|-----|---------|
| `smsruapi` | password | API-ключ из кабинета sms.ru |

Доступ:
```php
$api_key = Codeweber_Options::get('smsruapi');
```

---

## Класс `SMSRU`

**Файл:** [functions/integrations/smsru/sms.ru.php](../functions/integrations/smsru/sms.ru.php)

Сторонний класс (автор WebProgrammer, адаптирован). Использует `curl` для HTTP-запросов к API sms.ru, автоматически повторяет попытки при сетевой ошибке (до 5 раз).

```php
$sms = new SMSRU($api_key);
```

### Методы

| Метод | Описание |
|-------|---------|
| `send_one($post)` | Отправить SMS одному получателю, вернуть результат для этого номера |
| `send($post)` | Отправить SMS (один или несколько), вернуть полный ответ |
| `getStatus($id)` | Получить статус отправленного SMS по ID |
| `getCost($post)` | Узнать стоимость SMS |
| `getBalance()` | Получить баланс |
| `getLimit()` | Получить дневной лимит |
| `getSenders()` | Список имён отправителей |
| `addStopList($phone, $text)` | Добавить номер в стоплист |
| `delStopList($phone)` | Удалить номер из стоплиста |
| `getStopList()` | Получить стоплист |
| `addCallback($post)` | Добавить URL callback |
| `delCallback($post)` | Удалить URL callback |
| `getCallback()` | Список callback URL |

### Отправка SMS

```php
require_once get_template_directory() . '/functions/integrations/smsru/sms.ru.php';

$sms  = new SMSRU(Codeweber_Options::get('smsruapi'));
$post = new stdClass();
$post->to  = '+79001234567';
$post->msg = 'Ваш код подтверждения: 1234';

$result = $sms->send_one($post);

if ($result->status === 'OK') {
    // Отправлено. $result->sms_id — идентификатор сообщения
} else {
    // $result->status_code — код ошибки
    // $result->status_text — описание
}
```

### Параметры объекта `$post` для отправки

| Поле | Описание |
|------|---------|
| `to` | Номер телефона (или несколько через запятую, до 100) |
| `msg` | Текст в UTF-8 |
| `from` | Имя отправителя (должно быть согласовано с sms.ru) |
| `time` | UNIX timestamp отложенной отправки |
| `translit` | `1` — транслитерация (рус → лат) |
| `test` | `1` — тестовый режим (не списывает баланс) |
| `ip` | IP пользователя (для защиты от атак) |

---

## Верификация телефона (WooCommerce)

**Файл:** `functions/woocommerce.php`

Реализована через два AJAX-действия. Доступно **только для залогиненных пользователей** (`wp_ajax_*`, без `nopriv`).

### Шаг 1: Отправка кода

**AJAX action:** `send_verification_code`

```javascript
fetch(ajaxurl, {
    method: 'POST',
    body: new URLSearchParams({
        action: 'send_verification_code',
        phone:  '+79001234567',
    })
}).then(r => r.json()).then(data => {
    if (data.success) {
        // Показать поле ввода кода
        // data.show_code_input = true
    } else {
        // data.message — текст ошибки
        // data.retry_after — секунд до следующей попытки
    }
});
```

**PHP-логика:**
1. Читает `smsruapi` из Redux
2. Проверяет rate limit (прогрессивные задержки: 0, 30 сек, 60 сек, 15 мин)
3. Генерирует 4-значный код
4. Сохраняет код в user meta: `phone_verification_code`
5. Записывает время отправки: `phone_sms_last_sent`
6. Инкрементирует счётчик попыток: `phone_sms_attempts`
7. Отправляет SMS через `https://sms.ru/sms/send?api_id={key}&to={phone}&msg={msg}&json=1`

**User meta, используемые модулем:**

| Meta-ключ | Описание |
|-----------|---------|
| `phone_verification_code` | Код верификации (удаляется после подтверждения) |
| `phone_sms_last_sent` | UNIX timestamp последней отправки |
| `phone_sms_attempts` | Количество попыток (для расчёта задержки) |
| `phone` | Номер телефона (сохраняется при отправке кода) |
| `phone_verified` | `1` если телефон подтверждён |

### Шаг 2: Подтверждение кода

**AJAX action:** `confirm_verification_code`

```javascript
fetch(ajaxurl, {
    method: 'POST',
    body: new URLSearchParams({
        action: 'confirm_verification_code',
        code:   '1234',
    })
}).then(r => r.json()).then(data => {
    if (data.success) {
        // Номер подтверждён. data.message = 'Номер подтверждён.'
    }
});
```

**PHP-логика:**
- Сравнивает введённый код с `phone_verification_code` из user meta
- При совпадении: устанавливает `phone_verified = 1`, удаляет код

---

## Callback-обработчик (статусы доставки)

**Файл:** [functions/integrations/smsru/callback.php](../functions/integrations/smsru/callback.php)

Обработчик для уведомлений о статусе доставки SMS. Вызывается sms.ru по POST на указанный URL.

**URL для регистрации:** `https://sms.ru/?panel=api&subpanel=cb`

**Безопасность:** проверка IP-адреса отправителя по whitelist CIDR (диапазон серверов sms.ru `217.107.239.0/24`).

```php
// При получении статуса SMS:
// $sms_id     — ID сообщения
// $sms_status — новый статус
// Здесь можно добавить обработку (логирование, обновление БД и т.д.)
```

Обработчик должен вернуть `100` в теле ответа — иначе sms.ru считает доставку уведомления неуспешной.

> Если callback не нужен — файл можно не подключать (удалить из `require_once` в `functions.php`).

---

## Ограничения

- `SMSRU` использует `curl` напрямую (не `wp_remote_post`) — может требовать разрешения в некоторых окружениях
- `SSL_VERIFYPEER = FALSE` в curl — небезопасно в production (класс сторонний, не менять без нужды)
- Верификация телефона работает только для залогиненных пользователей
- API-ключ хранится в Redux как `password`-поле (шифруется в БД WordPress)
