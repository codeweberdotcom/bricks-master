# Система модальных окон CodeWeber

Полная документация по архитектуре, использованию и расширению системы модальных окон.

---

## Содержание

1. [Обзор архитектуры](#1-обзор-архитектуры)
2. [Файлы системы](#2-файлы-системы)
3. [Универсальный модал (#modal)](#3-универсальный-модал-modal)
4. [Нотификационный модал (#notification-modal)](#4-нотификационный-модал-notification-modal)
5. [Как открыть модал — HTML-атрибуты](#5-как-открыть-модал--html-атрибуты)
6. [Жизненный цикл модала](#6-жизненный-цикл-модала)
7. [Skeleton-loader](#7-skeleton-loader)
8. [Предзагрузка контента (prefetch)](#8-предзагрузка-контента-prefetch)
9. [Кэш](#9-кэш)
10. [Успешная отправка формы](#10-успешная-отправка-формы)
11. [REST API](#11-rest-api)
12. [Публичный JS API](#12-публичный-js-api)
13. [Gutenberg-блок Button](#13-gutenberg-блок-button)
14. [CPT Modals](#14-cpt-modals)
15. [Инициализация компонентов после открытия](#15-инициализация-компонентов-после-открытия)
16. [Конфликты с другими модалами](#16-конфликты-с-другими-модалами)
17. [Частые сценарии и как их реализовать](#17-частые-сценарии-и-как-их-реализовать)

---

## 1. Обзор архитектуры

Система строится вокруг **одного динамического Bootstrap-модала** (`#modal`), который:

- **не существует в DOM при загрузке страницы** — создаётся только при клике на кнопку
- загружает контент через **REST API** (`wp/v2/modal/{id}`)
- **уничтожается после закрытия** — `dispose()` + `el.remove()`, состояние сбрасывается в `null`
- используется повторно для любого контента: CF7-формы, CodeWeber-формы, CPT-записи

Конфигурация модала (CSS-класс радиуса, текст кнопки закрытия) передаётся через `<meta id="cw-modal-config">` в футере.

```
Кнопка (data-bs-toggle="modal") → click → createModal() → skeleton → show() → fetch REST → innerHTML
                                                                                        ↓
                                                                                 hidden.bs.modal
                                                                                        ↓
                                                                            dispose() + el.remove() + null
```

---

## 2. Файлы системы

| Файл | Роль |
|------|------|
| `src/assets/js/restapi.js` | Ядро: `createModal()`, `showModalSuccess()`, `getModalSkeleton()`, click-обработчики, prefetch |
| `src/assets/js/cf7-success-message.js` | CF7: слушает `wpcf7mailsent` → вызывает `window.codeweberModal.showSuccess()` |
| `functions/integrations/codeweber-forms/assets/js/form-submit-universal.js` | CodeWeber Forms: `replaceModalContentWithEnvelope()` → вызывает `window.codeweberModal.showSuccess()` |
| `functions/integrations/modal-container.php` | PHP: выводит `<meta id="cw-modal-config">` и `#notification-modal` в футере |
| `src/assets/scss/theme/_skeleton.scss` | SCSS: анимация и стили `.cw-skeleton-block`, `.cw-skeleton-shimmer` |
| `functions/cpt/cpt-modals.php` | CPT `modals`: регистрация типа записи |
| `functions/fetch/rest-api.php` | REST endpoint `wp/v2/modal/{id}` + `codeweber/v1/success-message-template` |

---

## 3. Универсальный модал (#modal)

### Структура DOM (создаётся динамически)

```html
<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content {card-radius-class}">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div id="modal-content">
          <!-- Сюда вставляется контент через innerHTML -->
        </div>
      </div>
    </div>
  </div>
</div>
```

### Конфигурация через meta-тег (footer.php / modal-container.php)

```html
<meta id="cw-modal-config"
      data-card-radius="rounded-4"
      data-close-label="Закрыть">
```

| Атрибут | Источник | Описание |
|---------|----------|----------|
| `data-card-radius` | `Codeweber_Options::style('card-radius')` | CSS-класс скругления (например `rounded-4`) |
| `data-close-label` | `__('Close', 'codeweber')` | Aria-label для кнопки закрытия |

### Функция createModal()

```js
function createModal() {
  if (modalInstance) return modalInstance;      // Идемпотентна
  if (typeof bootstrap === 'undefined') return null;

  // Читает конфигурацию из <meta id="cw-modal-config">
  // Создаёт DOM, добавляет в body
  // Регистрирует shown.bs.modal и hidden.bs.modal
  // Возвращает bootstrap.Modal instance
}
```

**Вызывается:**
- при клике на кнопку (`data-bs-toggle="modal"`)
- при вызове `window.codeweberModal.showSuccess()`

**Гарантии:**
- При повторном вызове возвращает существующий instance (не создаёт второй)
- После `hidden.bs.modal` — всё обнуляется, следующий клик создаст свежий модал

---

## 4. Нотификационный модал (#notification-modal)

**Отдельный** Bootstrap-модал, существующий статически в DOM (выводится через `modal-container.php`). Управляется через CPT `notifications`. Имеет:

- позиционирование (corner: `modal-bottom-start`, `modal-bottom-end` и т.д.)
- триггеры: по времени (`data-wait`), по неактивности (`data-trigger-inactivity`), по viewport (`data-trigger-viewport`)
- размер (`modal-sm`, `modal-lg` и т.д.)

**Приоритет:** если `#notification-modal` открыт в момент клика на кнопку — он закрывается, открывается `#modal`.

---

## 5. Как открыть модал — HTML-атрибуты

### Минимальный пример

```html
<a href="#"
   data-bs-toggle="modal"
   data-value="modal-{post_id}">
  Открыть форму
</a>
```

### Все атрибуты

| Атрибут | Обязательный | Описание |
|---------|-------------|----------|
| `data-bs-toggle="modal"` | Да | Маркер для `restapi.js` (querySelectorAll) |
| `data-value="modal-{id}"` | Да | ID поста CPT `modals`. Префикс `modal-` обрезается JS: `dataValue.replace("modal-", "")` |
| `href` | Нет | Ignored (preventDefault), можно `#` |

### Форматы data-value

| Значение | Тип контента | Endpoint |
|----------|-------------|----------|
| `modal-123` | CPT `modals` (числовой ID) | `wp/v2/modal/123` |
| `cf7-{form_id}` | Contact Form 7 | `wp/v2/modal/cf7-{id}` |
| `cf-{form_id}` | CodeWeber Form | `wp/v2/modal/cf-{id}` |
| `add-testimonial` | Форма отзыва | `wp/v2/modal/add-testimonial` |

> Префиксы `cf7-` и `cf-` также используются для определения типа skeleton (форменный vs контентный).

---

## 6. Жизненный цикл модала

```
1. Клик на кнопку (data-bs-toggle="modal")
       │
2. createModal()  ← идемпотентно, создаёт DOM только один раз
       │
3. Закрыть notification-modal (если открыт)
       │
4. modalContent.innerHTML = getModalSkeleton(dataValue)  ← мгновенно
       │
5. modalInstance.show()  ← Bootstrap анимация fade in
       │
6. fetch(REST API)  ← параллельно с анимацией
       │
7. data.content.rendered → modalContent.innerHTML
       │
8. applyModalSize(data.modal_size)
       │
9. shown.bs.modal → инициализация CF7, rating stars, document email form
       │
...пользователь взаимодействует...
       │
10. Закрытие (btn-close / data-bs-dismiss / ESC / backdrop)
       │
11. hidden.bs.modal → dispose() + el.remove() + всё в null
```

### Сброс состояния при закрытии

```js
el.addEventListener('hidden.bs.modal', () => {
  modalInstance.dispose();
  el.remove();
  modalElement = null;
  modalContent  = null;
  modalDialog   = null;
  modalInstance = null;
});
```

Это гарантирует, что при повторном открытии другой формы не будет «флэша» старого контента.

---

## 7. Skeleton-loader

Показывается **мгновенно при клике**, до завершения fetch-запроса. Реализован в `getModalSkeleton(dataValue)`.

### Два типа skeleton

**Форменный** (для `data-value` начинающихся на `cf7-`, `cf-`, или равных `add-testimonial`):

```
┌─────────────────────────────────────────────────────┐
│  ████████████████████████  (55% — заголовок)        │
│  ████████████████████████████████████  (поле 1)     │
│  ████████████████████████████████████  (поле 2)     │
│  ████████████████████████████████████  (textarea)   │
│  ████████████  (кнопка)                             │
└─────────────────────────────────────────────────────┘
```

**Контентный** (для CPT-модалов, нотификаций и success):

```
┌─────────────────────────────────────────────────────┐
│  ██████████████████████████████  (65% — заголовок)  │
│  ████████████████████████████████████  (строки)     │
│  █████████████████████████████████                  │
│  ████████████████████████████                       │
│  ██████████████████████████████████████             │
│  ██████████████████████████████████                 │
│  ████████████████████████                           │
└─────────────────────────────────────────────────────┘
```

### SCSS-классы

```scss
// _skeleton.scss
.cw-skeleton-block {
  background: linear-gradient(90deg, #e0e0e0 25%, #f0f0f0 50%, #e0e0e0 75%);
  background-size: 200% 100%;
  animation: cw-skeleton-shimmer 1.4s infinite;
  border-radius: 4px;
}

@keyframes cw-skeleton-shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
```

### Skeleton при success

При вызове `showModalSuccess()` также показывается **контентный** skeleton (`getModalSkeleton('')`) пока идёт fetch шаблона успеха.

---

## 8. Предзагрузка контента (prefetch)

При появлении кнопки в viewport (через `IntersectionObserver`) контент модала **предзагружается в localStorage** (только если `ENABLE_CACHE = true`).

```js
const ENABLE_CACHE = false; // Кэш отключён по умолчанию
```

При клике: если кэш актуален (< 60 секунд) — контент вставляется из localStorage без fetch. При этом размер модала также кэшируется (`{dataValue}_size`).

> Кэш отключён `ENABLE_CACHE = false`. Включить — изменить константу в начале `restapi.js`.

---

## 9. Кэш

| Ключ localStorage | Содержимое |
|------------------|------------|
| `{dataValue}` | HTML контента (data.content.rendered) |
| `{dataValue}_time` | Unix timestamp загрузки |
| `{dataValue}_size` | CSS-класс размера (например `modal-lg`) |

TTL кэша — **60 секунд**. При инвалидации — просто истекает.

---

## 10. Успешная отправка формы

Обе системы форм используют единый путь через `window.codeweberModal.showSuccess()`.

### Поток CF7

```
wpcf7mailsent event
      ↓
cf7-success-message.js → handleCf7Success()
      ↓
window.codeweberModal.showSuccess('')
      ↓
showModalSuccess() в restapi.js
```

### Поток CodeWeber Forms

```
AJAX submit success
      ↓
form-submit-universal.js → replaceModalContentWithEnvelope(form, message)
      ↓
window.codeweberModal.showSuccess(message)
      ↓
showModalSuccess() в restapi.js
```

### Поток Newsletter (already_subscribed)

```
AJAX submit → ответ already_subscribed
      ↓
form-submit-universal.js → replaceModalContentWithEnvelope(form, errorMessage)
      ↓
window.codeweberModal.showSuccess(errorMessage)
      ↓
showModalSuccess() в restapi.js
```

> `form-submit-universal.js` **не создаёт модальные окна самостоятельно** — ни `#newsletter-success-modal`, ни `#codeweber-form-success-modal`. Все три сценария делегируют в `replaceModalContentWithEnvelope` → `showSuccess()`.

### Что делает showModalSuccess()

```js
function showModalSuccess(message) {
  if (typeof wpApiSettings === 'undefined') return;
  if (!createModal()) return;

  // Открываем модал, если он ещё не открыт (CF7 — форма уже в модале, он открыт)
  if (!modalElement.classList.contains('show')) {
    modalInstance.show();
  }

  // Мгновенно заменяем контент на skeleton (убираем форму с «Отправлено»)
  modalContent.innerHTML = getModalSkeleton('');

  // Запрашиваем шаблон успеха
  fetch(wpApiSettings.root + 'codeweber/v1/success-message-template?icon_type=svg' + ...)
    .then(data => {
      modalContent.innerHTML = data.html;
      setTimeout(() => modalInstance.hide(), 3000);  // Автозакрытие через 3 сек
    })
    .catch(() => {
      setTimeout(() => modalInstance.hide(), 1000);  // При ошибке — закрыть через 1 сек
    });
}
```

### REST endpoint шаблона успеха

```
GET /wp-json/codeweber/v1/success-message-template
    ?icon_type=svg
    ?message=Ваша заявка принята   (опционально)

Ответ: { success: true, html: "..." }
```

---

## 11. REST API

### Загрузка контента модала

```
GET /wp-json/wp/v2/modal/{id}
    ?user_id={id}   (опционально, для залогиненных пользователей)

Ответ:
{
  "id": 123,
  "content": { "rendered": "<div>...</div>" },
  "modal_size": "modal-lg"   // кастомное поле
}
```

`modal_size` — значение из мета-поля поста CPT `modals`. Применяется через `applyModalSize()`.

### Шаблон успеха

```
GET /wp-json/codeweber/v1/success-message-template
    ?icon_type=svg
    ?message=текст   (опционально)

Ответ:
{
  "success": true,
  "html": "<div class=\"success-message\">...</div>"
}
```

---

## 12. Публичный JS API

`window.codeweberModal` — объект доступен глобально после загрузки `restapi.js`.

```js
// Показать success-сообщение в модале (с skeleton → fetch → автозакрытие)
window.codeweberModal.showSuccess('');               // текст из серверного перевода
window.codeweberModal.showSuccess('Спасибо!');       // кастомный текст
```

**Использование в своём коде:**

```js
// После успешной отправки любой кастомной формы
if (window.codeweberModal && window.codeweberModal.showSuccess) {
  window.codeweberModal.showSuccess('');
}
```

---

## 13. Gutenberg-блок Button

Блок `codeweber-blocks/button` имеет режим `data-bs-toggle="modal"`:

```jsx
// save.js — атрибуты кнопки/ссылки при типе "modal"
data-bs-toggle="modal"
data-value={DataValue}          // например "modal-123"
data-bs-target="#modal"         // для совместимости (не используется JS-обработчиком)
```

**В редакторе:** инспектор → раздел «Ссылка» → тип «Modal» → выбор поста CPT `modals`.

**Размер модала** задаётся в настройках поста CPT `modals` (поле `modal_size`), не в блоке.

---

## 14. CPT Modals

Custom Post Type `modals` (`functions/cpt/cpt-modals.php`).

| Поле | Мета-ключ | Описание |
|------|-----------|----------|
| Размер | `_modal_size` | Класс Bootstrap: `modal-sm`, `modal-lg`, `modal-xl`, `modal-fullscreen` |

**Контент** поста — обычный редактор Gutenberg. Поддерживает любые блоки. Рендерится через `apply_filters('the_content', ...)` в REST-ответе.

**Создание кнопки для CPT-поста:**
1. Создать пост типа `modals`, запомнить его ID
2. В блоке Button выбрать тип ссылки «Modal» → указать пост
3. Или вручную: `data-bs-toggle="modal" data-value="modal-{post_id}"`

---

## 15. Инициализация компонентов после открытия

Компоненты в модале инициализируются на событии `shown.bs.modal` (регистрируется в `createModal()`):

```js
el.addEventListener('shown.bs.modal', function () {
  // CF7: переинициализация после динамической вставки контента
  const formElement = modalContent.querySelector('form.wpcf7-form');
  if (formElement && typeof wpcf7 !== 'undefined') {
    wpcf7.init(formElement);
  }

  // Звёздочки рейтинга (testimonial форма)
  initTestimonialRatingStars();

  // Форма с email для скачивания документа
  initDocumentEmailForm();
});
```

**CodeWeber Forms** инициализируются через `MutationObserver` в `form-submit-universal.js` — он следит за появлением `.codeweber-form` в DOM и не зависит от `shown.bs.modal`.

**FilePond** (загрузка файлов) инициализируется через `waitAndInitFilePond()` — с поллингом до готовности библиотеки.

---

## 16. Конфликты с другими модалами

### Cookie Modal (#cookieModal)

Если `#cookieModal` открыт в момент клика — кнопка «ждёт» через `setInterval(100ms)`. После закрытия cookie-модала автоматически открывается `#modal`.

### Notification Modal (#notification-modal)

При клике на кнопку — `#notification-modal` принудительно закрывается перед показом `#modal`.

### Bootstrap-конфликт

Bootstrap не позволяет открыть два `modal` одновременно (блокировка backdrop). Поэтому все сторонние модалы закрываются перед показом `#modal`.

---

## 17. Частые сценарии и как их реализовать

### Открыть модал программно

```js
// Если кнопка есть в DOM — имитировать клик
document.querySelector('[data-bs-toggle="modal"][data-value="modal-123"]').click();

// Или напрямую через API (только success-шаблон)
window.codeweberModal.showSuccess('');
```

> Прямой `createModal().show()` без контента не предусмотрен публичным API. Используйте клик по кнопке.

### Добавить новый тип skeleton

В `getModalSkeleton(dataValue)` в `restapi.js`:

```js
const isForm = dataValue && (
  dataValue.startsWith('cf7-') ||
  dataValue.startsWith('cf-') ||
  dataValue === 'add-testimonial' ||
  dataValue === 'my-new-form'    // ← добавить
);
```

### Вызвать своё действие после открытия модала

```js
// Подписаться на событие ДО вызова createModal (т.е. до клика)
// Или через делегирование на document:
document.addEventListener('shown.bs.modal', function(e) {
  if (e.target.id === 'modal') {
    // Ваш код
  }
});
```

### Изменить время автозакрытия success-модала

В `restapi.js`, функция `showModalSuccess()`:

```js
setTimeout(() => { if (modalInstance) modalInstance.hide(); }, 3000); // ← 3000ms
```

### Отключить автозакрытие после успеха

Удалить `setTimeout(...hide...)` из `showModalSuccess()`. Пользователь закроет вручную через `btn-close`.

### Открыть форму не в модале, а на странице

`replaceModalContentWithEnvelope` / `cf7-success-message.js` проверяют `window.codeweberModal` перед вызовом — если форма не в модале, `showSuccess` всё равно создаст/откроет модал. Для inline-форм (не в модале) такое поведение может быть нежелательным — в этом случае обработчик успеха нужно переопределить на уровне конкретной формы.
