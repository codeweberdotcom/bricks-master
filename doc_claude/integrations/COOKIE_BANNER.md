# Cookie Banner — Куки-баннер

Система информирования посетителей об использовании cookies. Поддерживает два режима соответствия законодательству: РФ (152-ФЗ) и GDPR (EU).

---

## Архитектура

```
redux_cookie.php          — PHP: рендер HTML баннера в wp_footer
redux_tracking_output.php — PHP: вывод трекинг-кодов в wp_head
legal.php (cookie_data)   — Redux: настройки баннера в админке
theme.js (bsModal)        — JS: инициализация Bootstrap Modal
```

### Принцип работы

1. `wp_head` (priority 5) — `redux_tracking_output.php` проверяет режим и куку → либо выводит трекинг-коды, либо нет
2. Страница загружается, пользователь видит её
3. `wp_footer` — `redux_cookie.php` проверяет условия → рендерит HTML модала (или нет)
4. JS (`bsModal`) находит `.modal-popup`, показывает через 200мс
5. Пользователь нажимает Accept → JS записывает куку в браузер
6. На следующей странице: PHP проверяет `$_COOKIE[$cookie_name]` → баннер не рендерится, трекинг работает

**Ключевое отличие от Woodmart:** проверка куки происходит на PHP-стороне. Если кука есть — HTML баннера вообще не попадает в DOM.

---

## Файлы

### `functions/integrations/redux_framework/redux_cookie.php`

Хук `wp_footer`. Рендерит Bootstrap Modal с баннером.

**Условия показа (все должны выполняться):**
- `enable_cookie_banner` = true в Redux
- Пользователь не является ботом (проверка User-Agent)
- Кука согласия отсутствует в `$_COOKIE`
- Текущий URL ≠ URL страницы политики cookies

**Имя куки:**
```php
$cookie_name = 'user_cookie_consent_' . md5($host) . '_v' . $cookie_version;
// Пример: user_cookie_consent_a1b2c3d4e5f6_v1
```

- `md5($host)` — уникально на домен, нет конфликтов между сайтами
- `_v{N}` — версия; при увеличении старая кука не совпадает → баннер показывается снова

**Значение куки** (записывается JS при клике Accept):
```
fd=2026-03-21 14:30:00|||ep=https://site.ru/page/|||rf=https://google.ru/
```
- `fd` — дата и время согласия
- `ep` — страница, на которой было дано согласие
- `rf` — реферер (откуда пришёл пользователь)

**HTML-структура:**
```html
<div class="modal fade modal-popup modal-bottom-center" id="cookieModal"
     data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xl">
    <div class="modal-content [card-radius]">
      <div class="modal-body p-6">
        <div class="row">
          <div class="col-md-12 col-lg-10">
            <!-- текст из Redux (supports shortcodes) -->
          </div>
          <div class="col-md-5 col-lg-2">
            <a id="acceptCookie" class="btn btn-primary">Accept</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
```

`data-bs-backdrop="static"` — клик вне модала не закрывает.
`data-bs-keyboard="false"` — Escape не закрывает.

### `functions/integrations/redux_framework/redux_tracking_output.php`

Хук `wp_head` (priority 5). Выводит трекинг-коды аналитики.

**GDPR-блокировка:**
```php
if ( $opts['cookie_compliance_mode'] === 'gdpr' ) {
    if ( empty( $_COOKIE[$cookie_name] ) ) {
        return; // не выводить трекинг
    }
}
```

В РФ-режиме блокировки нет — коды выводятся всегда.

**Поддерживаемые трекеры** (каждый включается отдельным switch в Redux):

| Ключ включения | Ключ кода | Трекер |
|----------------|-----------|--------|
| `yandex-on` | `yandex-metrics` | Яндекс.Метрика |
| `google-analytics-on` | `google-analytics` | Google Analytics |
| `google-tag-manager-on` | `google-tag-manager` | Google Tag Manager |
| `facebook-pixel-on` | `facebook-pixel` | Facebook Pixel |
| `hotjar-on` | `hotjar` | Hotjar |
| `other-analytics-on` | `other-analytics-code` | Произвольный код |

---

## Redux-настройки

**Путь в админке:** `Внешний вид → Theme Options → Personal Data & Privacy → Cookie Data`

**Section ID:** `cookie_data` (subsection раздела `cf7_consent`)

| Ключ опции | Тип | По умолчанию | Описание |
|------------|-----|-------------|---------|
| `enable_cookie_banner` | switch | `true` | Включить/выключить баннер |
| `cookie_compliance_mode` | select | `'ru'` | Режим: `'ru'` (152-ФЗ) или `'gdpr'` (EU) |
| `cookie_version` | number | `'1'` | Версия политики. Увеличение сбрасывает согласие у всех |
| `cookie_expiration_date` | text | `'365'` | Срок хранения куки в днях |
| `welcome_text_cookie_banneer` | editor | (текст по умолчанию) | Текст баннера. Поддерживает шорткоды |

> Обратите внимание: в ключе `welcome_text_cookie_banneer` опечатка (двойное `e`) — это историческое имя, менять нельзя.

**Доступ из кода:**
```php
$is_enabled    = Redux::get_option($opt_name, 'enable_cookie_banner');
$mode          = Redux::get_option($opt_name, 'cookie_compliance_mode'); // 'ru' | 'gdpr'
$version       = (int) Redux::get_option($opt_name, 'cookie_version') ?: 1;
$days          = (int) Redux::get_option($opt_name, 'cookie_expiration_date');
$text          = Redux::get_option($opt_name, 'welcome_text_cookie_banneer');
```

---

## JS-инициализация

**Файл:** `src/assets/js/theme.js`, функция `bsModal`

Модал инициализируется **не через data-атрибуты**, а через JS по CSS-классу `.modal-popup`:

```js
var modalPopupElements = document.querySelectorAll(".modal-popup:not(#notification-modal)");
modalPopupElements.forEach(function(el) {
    var myModalPopup = new bootstrap.Modal(el);
    var waitTime = el.getAttribute('data-wait') || 200;
    setTimeout(function() { myModalPopup.show(); }, waitTime);
});
```

**`data-wait`** — единственный data-атрибут, влияющий на поведение: задержка показа в мс (по умолчанию 200).

### Приоритет модалов

Cookie-модал имеет наивысший приоритет. При его открытии:
1. Закрываются все другие открытые модалы (notification, CF7 success и др.)
2. `bootstrap.Modal.prototype.show` monkey-patched — блокирует открытие любых других модалов пока `#cookieModal` открыт
3. При закрытии (#cookieModal `hidden.bs.modal`) — оригинальный метод восстанавливается, система триггеров (notification-triggers.js) повторно инициализируется

### JS-запись куки при Accept

```js
document.getElementById('acceptCookie')?.addEventListener('click', function() {
    const days = <?= $cookie_days ?>;
    const fd   = new Date().toISOString().replace('T', ' ').substring(0, 19);
    const ep   = location.href;
    const rf   = document.referrer;
    const value = `fd=${fd}|||ep=${ep}|||rf=${rf}`;
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = "<?= $cookie_name ?>=" + encodeURIComponent(value)
                    + "; expires=" + expires + "; path=/";
});
```

---

## Режимы соответствия

### РФ-режим (`cookie_compliance_mode = 'ru'`)

- Трекинг (Метрика, GA, GTM, Pixel и др.) загружается **сразу** при открытии страницы
- Баннер показывается если кука отсутствует — информирует пользователя
- Соответствует **152-ФЗ** (требует уведомления, не требует блокировки трекинга)
- Согласие хранится **только в браузерной куке**, на сервере не фиксируется

### GDPR-режим (`cookie_compliance_mode = 'gdpr'`)

- Трекинг **не загружается** пока нет куки согласия
- После Accept → кука устанавливается → со следующей страницы трекинг работает
- Соответствует базовым требованиям **GDPR (EU)**
- **Важно:** Matomo-плагин работает независимо. Его нужно настраивать отдельно через Settings → Matomo → "Require consent before tracking"

### Сравнение с Woodmart

| Параметр | Woodmart | CodeWeber |
|----------|----------|-----------|
| Проверка куки | только JS | PHP (HTML не рендерится) |
| Бот-фильтрация | нет | есть |
| Метаданные в куке | нет | дата, страница, реферер |
| Версионирование куки | есть | есть (добавлено) |
| Блокировка трекинга | нет | есть (GDPR-режим) |
| Исключение страницы политики | нет | есть |
| Приоритет над другими модалами | нет | есть |

---

## Обновление Cookie Policy (сброс согласия)

При изменении политики cookies нужно показать баннер повторно всем посетителям:

1. Открыть `Theme Options → Personal Data & Privacy → Cookie Data`
2. Увеличить **Cookie Version** (например, с `1` на `2`)
3. Сохранить

Старая кука `user_cookie_consent_*_v1` не совпадает с новой `user_cookie_consent_*_v2` → PHP-проверка выдаёт `false` → баннер снова показывается всем.

---

## Безопасность

- Имя куки не содержит пользовательских данных (только md5 домена + версия)
- Значение куки проходит `encodeURIComponent` в JS
- `$cookie_name` в инлайновом JS выводится напрямую из PHP — значение генерируется на сервере, не из пользовательского ввода, XSS невозможен
- Баннер не показывается ботам (Googlebot, YandexBot и др.) — нет лишней нагрузки на краулеры
- Флаг `Secure` не установлен — при необходимости добавить `; secure` в JS (актуально для HTTPS-продакшена)

---

## Что не делает этот баннер

- **Не фиксирует согласие на сервере** — нет записи в БД, нет лога с IP
- **Не блокирует Matomo-плагин** — плагин трекает независимо от баннера в обоих режимах
- **Не является полноценным CMP** — для аудит-трейла согласия нужны Cookiebot / OneTrust / Usercentrics
