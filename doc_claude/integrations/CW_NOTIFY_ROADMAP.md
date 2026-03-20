# CWNotify — Роадмап интеграций

`CWNotify` — универсальный менеджер уведомлений темы CodeWeber.
Файлы: `functions/lib/cw-notify/cw-notify.js`, `functions/lib/cw-notify/class-cw-notify.php`.
Настройки Redux: **Внешний вид → Уведомления** (`notify_enabled`, `notify_position`, `notify_delay`, `notify_event_*`).

## API

```js
CWNotify.show(message, { type, event });
// type:  'success' | 'danger' | 'warning' | 'info' | 'primary'
// event: ключ из notify_event_* (проверяет Redux-флаг включения)

CWNotify.isEnabled(event); // → boolean
```

```php
// PHP-сторона
CW_Notify::is_event_enabled('wishlist'); // → bool
CW_Notify::get_config();                 // → array для wp_localize_script
```

---

## Реализовано

| Событие | Ключ | Файл | Статус |
|---------|------|------|--------|
| Добавление в избранное | `wishlist` | `wishlist/assets/wishlist.js` | **Готово** |
| Предупреждение о входе (избранное) | `wishlist` | `wishlist/assets/wishlist.js` | **Готово** |

---

## Роадмап — предстоящие интеграции

### 1. CodeWeber Forms (`codeweber-forms/`)

**Событие:** `form`
**Что сделать:**
- Найти JS-обработчик успешной/ошибочной отправки формы.
- Заменить inline-вставку ответа на `CWNotify.show(message, { type: 'success', event: 'form' })`.
- Ошибку отправки: `CWNotify.show(errorText, { type: 'danger', event: 'form' })`.

---

### 2. Newsletter Subscription (`newsletter-subscription/`)

**Событие:** `newsletter`
**Что сделать:**
- В JS-обработчике подписки добавить `CWNotify.show(successMessage, { type: 'success', event: 'newsletter' })`.
- Ошибка (email уже есть / сбой): `type: 'danger'`.

---

### 3. WooCommerce Add to Cart

**Событие:** `cart`
**Что сделать:**
- Подписаться на событие WooCommerce `added_to_cart`:
  ```js
  $(document.body).on('added_to_cart', function (e, fragments, hash, $btn) {
      if (typeof CWNotify !== 'undefined' && CWNotify.isEnabled('cart')) {
          var name = $btn.data('product_name') || '';
          CWNotify.show(cwNotifyI18n.addedToCart + (name ? ': ' + name : ''), { type: 'success', event: 'cart' });
      }
  });
  ```
- Добавить `cwNotifyI18n.addedToCart` в `wp_localize_script`.
- Можно отключить стандартный WC-фрагмент уведомления, если включён CWNotify.

---

### 4. DaData — ошибка стандартизации (`dadata/`)

**Событие:** `dadata`
**Что сделать:**
- В `dadata-ajax.php` или клиентском JS — при получении `status !== 'OK'` или сетевой ошибке:
  ```js
  CWNotify.show(cwNotifyI18n.dadataError, { type: 'warning', event: 'dadata' });
  ```

---

### 5. Image Licenses — копирование ссылки (`image-licenses/`)

**Событие:** `copy`
**Что сделать:**
- В JS обработчике кнопки «Скопировать»:
  ```js
  navigator.clipboard.writeText(url).then(function () {
      CWNotify.show(cwNotifyI18n.copied, { type: 'info', event: 'copy' });
  });
  ```

---

### 6. Personal Data V2 (`personal-data-v2/`)

**Событие:** `form` (или отдельный ключ `personal_data`)
**Что сделать:**
- После успешной отправки запроса на экспорт/удаление данных показать `CWNotify.show()`.
- Добавить `notify_event_personal_data` в Redux и `class-cw-notify.php`.

---

### 7. AJAX-фильтр (`ajax-filter.php`)

**Событие:** нет в настройках (информационное)
**Что сделать:**
- После применения фильтра: `CWNotify.show('Найдено: ' + count + ' товаров', { type: 'info' })`.
- Если count === 0: `CWNotify.show('Ничего не найдено', { type: 'warning' })`.
- Не требует отдельного Redux-переключателя — достаточно `notify_enabled`.

---

### 8. Comments Reply (`comments-reply.php`)

**Событие:** `form`
**Что сделать:**
- После AJAX-отправки комментария: `CWNotify.show(successText, { type: 'success', event: 'form' })`.

---

## Добавление нового события

1. В `redux-framework/sample/sections/codeweber/notifications.php` добавить `switch`-поле `notify_event_{key}`.
2. В `class-cw-notify.php` добавить ключ в массив `events` в `get_config()`.
3. В JS вызвать `CWNotify.show(msg, { type: '...', event: '{key}' })`.

---

## Структура файлов

```
functions/lib/cw-notify/
├── cw-notify.js          — JS singleton, alert-разметка темы
└── class-cw-notify.php   — enqueue, wp_localize_script, PHP helper

redux-framework/sample/sections/codeweber/
└── notifications.php     — Redux секция "Уведомления"

doc_claude/integrations/
└── CW_NOTIFY_ROADMAP.md  — этот файл
```
