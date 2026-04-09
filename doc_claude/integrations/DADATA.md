# DaData — Стандартизация и автодополнение адресов

## Что делает этот модуль

Интегрирует API [DaData](https://dadata.ru/) для работы с российскими адресами в WooCommerce:

- **clean/address** — стандартизация произвольной строки адреса (нормализует, раскладывает по полям WooCommerce)
- **suggest/address** — автодополнение при вводе (подсказки через jQuery Suggestions)

Оба метода работают только для России. API-ключи хранятся на сервере и **никогда не передаются в браузер открыто** (только токен для suggest).

---

## Файлы модуля

| Файл | Назначение |
|------|-----------|
| `functions/integrations/dadata/class-codeweber-dadata.php` | Класс `Codeweber_Dadata` — логика вызовов API |
| `functions/integrations/dadata/dadata-ajax.php` | AJAX-обработчики (регистрация wp_ajax) |
| `functions/enqueues.php` → `codeweber_enqueue_dadata_address()` | Подключение скриптов на нужных страницах |
| `src/assets/js/dadata-address.js` | JS-логика кнопки проверки и автодополнения |
| `src/assets/js/dadata/jquery.suggestions.min.js` | Вендорный виджет jQuery Suggestions |

---

## Настройка (Redux)

Раздел **API Keys** в настройках темы:

| Redux-ключ | Тип | Описание |
|-----------|-----|---------|
| `dadata_enabled` | switch | Включить/выключить интеграцию (default: false) |
| `dadata` | password | API Token (из кабинета DaData) |
| `dadata_secret` | password | X-Secret (нужен только для clean/address) |
| `dadata_scenarios` | checkbox | Где показывать кнопку проверки |

`dadata_scenarios` содержит ключи:
- `edit_address` — страница «Редактирование адреса» в Мой аккаунт
- `checkout` — страница оформления заказа

Доступ через PHP:
```php
$enabled  = Codeweber_Options::get('dadata_enabled');
$token    = Codeweber_Options::get('dadata');
$secret   = Codeweber_Options::get('dadata_secret');
$scenarios = Codeweber_Options::get('dadata_scenarios');
```

---

## Архитектура: два режима работы

```
clean/address (стандартизация)          suggest/address (автодополнение)
─────────────────────────────────       ──────────────────────────────────
Требует: token + secret                 Требует: только token
API: cleaner.dadata.ru                  API: suggestions.dadata.ru
Вызов: через кнопку «Проверить»        Вызов: при вводе в поле адреса
Результат: раскладка по полям WC       Результат: список подсказок
```

---

## Класс `Codeweber_Dadata`

**Файл:** [functions/integrations/dadata/class-codeweber-dadata.php](../functions/integrations/dadata/class-codeweber-dadata.php)

### Константы

```php
const API_URL_CLEAN   = 'https://cleaner.dadata.ru/api/v1/clean/address';
const API_URL_SUGGEST = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address';
const MAX_INPUT_LENGTH = 500;   // Максимальная длина строки адреса для clean
const MAX_QUERY_LENGTH = 300;   // Максимальная длина запроса для suggest
```

### Методы

| Метод | Описание | Требует |
|-------|---------|---------|
| `is_available()` | Проверить, настроены ли token + secret | — |
| `is_suggest_available()` | Проверить, настроен ли хотя бы token | — |
| `clean_address($address_string)` | Стандартизировать адрес | token + secret |
| `suggest_address($query, $count = 10)` | Получить подсказки | token |

### `clean_address()` — возвращаемые данные

При успехе (`success: true`):
```php
[
    'success' => true,
    'data' => [
        'country'   => 'RU',         // ISO-код страны
        'state'     => 'MOW',        // Код региона (RU- убран из начала)
        'city'      => 'Москва',
        'address_1' => 'ул. Сухонская, 11',   // улица + дом
        'address_2' => '',
        'postcode'  => '127642',
    ],
    'code' => 200,
]
```

При ошибке (`success: false`):
```php
[
    'success' => false,
    'error'   => 'Сервис проверки адреса временно недоступен.',
    'code'    => 401,  // HTTP-код или 0 при сетевой ошибке
]
```

**Специальная обработка регионов** (маппинг в коды WooCommerce):
| Регион DaData | Код WC |
|-------------|-------|
| Крым (UA-43) | `CR` |
| Севастополь | `SEV` |
| ДНР | `DNR` |
| ЛНР | `LNR` |
| Запорожская | `ZAP` |
| Херсонская | `KHE` |
| Остальные | RU-код без префикса `RU-` |

### `suggest_address()` — возвращаемые данные

```php
[
    'success'     => true,
    'suggestions' => [
        [
            'value' => 'г Москва, ул Сухонская, д 11',  // Текст для отображения
            'data'  => [...],    // Полный объект DaData (postal_code, region, city и т.д.)
            'wc'    => [         // Уже смаппированные поля WooCommerce
                'country'   => 'RU',
                'state'     => 'MOW',
                'city'      => 'Москва',
                'address_1' => 'ул. Сухонская, 11',
                'address_2' => '',
                'postcode'  => '127642',
            ],
        ],
        // ...до count элементов
    ],
    'code' => 200,
]
```

---

## AJAX-обработчики

**Файл:** [functions/integrations/dadata/dadata-ajax.php](../functions/integrations/dadata/dadata-ajax.php)

Оба обработчика доступны для залогиненных и незалогиненных пользователей:

### `dadata_clean_address`

```javascript
// JS-запрос
jQuery.post(ajaxUrl, {
    action:  'dadata_clean_address',
    nonce:   codeweberDadata.nonce,  // wp_create_nonce('codeweber_dadata_clean')
    address: 'мск сухонска 11/-89',
}, function(response) {
    if (response.success) {
        // response.data: { country, state, city, address_1, address_2, postcode }
    }
});
```

PHP-обработчик: `codeweber_dadata_ajax_clean_address()`

### `dadata_suggest_address`

```javascript
jQuery.post(ajaxUrl, {
    action: 'dadata_suggest_address',
    nonce:  codeweberDadata.nonce,
    query:  'москва сухо',
    count:  10,  // 1..20
}, function(response) {
    if (response.success) {
        // response.suggestions: массив подсказок
    }
});
```

PHP-обработчик: `codeweber_dadata_ajax_suggest_address()`

### Безопасность AJAX

1. **Nonce-верификация:** `wp_verify_nonce($nonce, 'codeweber_dadata_clean')` — проверяется в обоих обработчиках
2. **Rate limiting:** максимум 30 запросов в минуту с одного IP (через WordPress transient)
3. **Проверка включённости:** если `dadata_enabled = false` в Redux → возвращает ошибку
4. **Санитизация входных данных:** `sanitize_text_field()` + `wp_unslash()`

---

## Безопасность — защита API-ключей

### Уровни защиты (все уже реализованы)

| Уровень | Место | Проверка |
| ------- | ----- | -------- |
| Enqueue | `enqueues.php:481` | `dadata_enabled` из Redux — если выключено, скрипты не грузятся |
| Токен в JS | `enqueues.php:560-562` | Токен передаётся в JS **только если непустой** |
| JS runtime | `dadata-address.js:73-76` | `if (!token) return` — виджет не инициализируется без токена |
| PHP методы | `class-codeweber-dadata.php` | `is_available()` и `is_suggest_available()` перед каждым запросом |
| Secret | `class-codeweber-dadata.php` | `X-Secret` — только в PHP, **никогда не передаётся в браузер** |

### Что происходит без ключа

```text
dadata_enabled = false (или не заполнен dadata-токен):
  → enqueues.php: скрипты не подключаются
  → браузер не получает токен
  → виджет автодополнения не инициализируется
  → кнопка «Проверить» не появляется

dadata_enabled = true, но токен пустой:
  → enqueues.php: скрипты грузятся (enabled=true)
  → токен НЕ передаётся в codeweberDadata (условная локализация)
  → dadata-address.js: if (!token) return → виджет не запускается
  → AJAX-обработчик: is_suggest_available() = false → ошибка 400
```

### Токен vs Secret

```
token  → нужен для suggest/address (автодополнение)
         → передаётся в JS (только если непустой)
         → используется в jQuery Suggestions и AJAX suggest

secret → нужен только для clean/address (стандартизация)
         → НИКОГДА не передаётся в браузер
         → используется только в PHP: Authorization + X-Secret headers
```

---

## Подключение скриптов

**Хук:** `wp_enqueue_scripts` (приоритет 25)
**Функция:** `codeweber_enqueue_dadata_address()` в `functions/enqueues.php`

**Условия подключения:**
- WooCommerce активен
- Redux активен
- `dadata_enabled = true` в Redux
- Текущая страница соответствует выбранным сценариям:
  - `edit_address` → `is_wc_endpoint_url('edit-address')` или `is_account_page()`
  - `checkout` → `is_checkout()`

**Подключаемые скрипты:**
1. `dadata-jquery-suggestions` — `src/assets/js/dadata/jquery.suggestions.min.js` (зависимость: jQuery)
2. `dadata-address` — `dist/assets/js/dadata-address.js` или `src/assets/js/dadata-address.js` (зависимость: jquery + suggestions)

**JS-переменные (`codeweberDadata`):**

```javascript
window.codeweberDadata = {
    ajaxUrl:       '/wp-admin/admin-ajax.php',
    nonce:         'abc123',              // wp_create_nonce('codeweber_dadata_clean')
    addressPrefix: 'billing',            // Префикс полей WC (billing/shipping)
    dadataToken:   'xxxxxx',             // Токен DaData (только если задан) — для suggest
    messages: {
        enterAddress: 'Введите адрес...',
        loading:      'Проверка…',
        error:        'Ошибка сети. Попробуйте позже.',
    },
    // В WP_DEBUG=true добавляются поля debug и debugEnqueue
};
```

> Токен (`dadataToken`) передаётся в браузер **только для suggest** (работает через клиентский виджет). **Secret-ключ (`dadata_secret`) никогда не передаётся в браузер** — он используется только в серверном запросе к clean/address.

---

## Как использовать класс напрямую (PHP)

```php
require_once get_template_directory() . '/functions/integrations/dadata/class-codeweber-dadata.php';

$dadata = new Codeweber_Dadata();

// Проверить доступность перед вызовом
if ($dadata->is_available()) {
    $result = $dadata->clean_address('москва тверская 1');
    if ($result['success']) {
        $wc_fields = $result['data'];
        // $wc_fields['city'] = 'Москва'
        // $wc_fields['address_1'] = 'ул. Тверская, 1'
        // $wc_fields['postcode'] = '125009'
    }
}

// Подсказки (только токен)
if ($dadata->is_suggest_available()) {
    $result = $dadata->suggest_address('москва тв', 5);
    if ($result['success']) {
        foreach ($result['suggestions'] as $s) {
            echo $s['value'];        // Текст подсказки
            print_r($s['wc']);       // Поля WooCommerce
        }
    }
}
```

---

## Типичный сценарий использования

```
Пользователь вводит адрес в поле "Адрес" на странице чекаута
    ↓
jQuery Suggestions (dadata-jquery-suggestions) показывает выпадающий список
    ↓ (если нет autoselect — пользователь нажимает кнопку "Проверить")
JS вызывает AJAX action 'dadata_clean_address'
    ↓
PHP проверяет nonce → rate limit → enabled → вызывает clean_address()
    ↓
clean_address() делает wp_remote_post() к DaData API (с secret-ключом)
    ↓
Возвращает нормализованные поля WooCommerce (country, state, city, address_1, postcode)
    ↓
JS заполняет поля формы WooCommerce (#billing_country, #billing_state, ...)
```

---

## Отладка

При `WP_DEBUG=true` в `codeweberDadata` добавляются поля:

```javascript
codeweberDadata.debug = true;
codeweberDadata.debugEnqueue = {
    on_edit:            true,
    on_checkout:        false,
    vendor_script:      'enqueued',  // или 'file_not_found'
    token_set:          true,
    script_source:      'dist',      // или 'src'
    dadata_address_js:  '/wp-content/themes/.../dadata-address.js',
};
```

PHP-ошибки логируются через `error_log()` с префиксом `[DaData]`:
- `[DaData] Enqueue пропущен: ...` — скрипт не подключён (WooCommerce/Redux не активны, или disabled)
- `[DaData] clean_address WP_Error: ...` — сетевая ошибка при вызове API
- `[DaData] suggest_address error, code: 401` — проблема с авторизацией

---

## Ограничения и особенности

- Работает **только с российскими адресами**
- Для clean/address нужны оба ключа (token + secret)
- Для suggest достаточно только token
- Rate limit: 30 запросов/мин на IP (через WP transient)
- Модуль требует активного WooCommerce (используется только на страницах чекаута/аккаунта)
- Маппинг регионов жёстко закодирован в `map_dadata_to_woocommerce()` — при изменении кодов WC нужно обновить метод
