# Contact Form 7 — интеграция с темой

## Что делает интеграция

Файл `functions/integrations/cf7.php` подключается **только если CF7 активен** (`class_exists('WPCF7')`). Расширяет CF7 для совместимости с Bootstrap 5 и системой согласий темы.

---

## Подключаемые JS-файлы (при активном CF7)

Функция `codeweber_cf7_custom_scripts()` — `wp_enqueue_scripts` приоритет 20:

| Скрипт | Handle | Зависимости | Назначение |
|--------|--------|------------|-----------|
| `dist/assets/js/form-validation.js` | `codeweber-form-validation` | — | Bootstrap 5 валидация форм с классом `needs-validation` |
| `dist/assets/js/cf7-acceptance-required.js` | `codeweber-cf7-acceptance-required` | `contact-form-7`, `codeweber-form-validation` | Управление required для acceptance-полей |
| `dist/assets/js/cf7-success-message.js` | `codeweber-cf7-success-message` | `contact-form-7` | Слушает `wpcf7mailsent` → делегирует в `window.codeweberModal.showSuccess()` (restapi.js) |
| `dist/assets/js/cf7-utm-tracking.js` | `codeweber-cf7-utm-tracking` | `contact-form-7` | UTM-параметры в скрытые поля формы |

Все скрипты используют patten: dist → fallback src.

---

## HTML-фильтры форм

### Удаление wrapper-спанов (два места)

```php
// 1. Убирает <span class="wpcf7-form-control-wrap">
add_filter('wpcf7_form_elements', function($content) {
    $content = preg_replace('/<(span).*?class="...wpcf7-form-control-wrap..."...>(.*)<\/\1>/i', '\2', $content);
    $content = str_replace('<br />', '', $content);
    return $content;
});

// 2. Убирает <span class="wpcf7-list-item"> и <span class="wpcf7-form-control wpcf7-acceptance">
add_filter('wpcf7_form_elements', function($content) { ... });
```

Без этих фильтров Bootstrap-разметка ломается из-за лишних обёрток.

### Отключение wpautop

```php
add_filter('wpcf7_autop_or_not', '__return_false');
```

Предотвращает автоматические `<p>` и `<br>` в шаблоне формы.

### Bootstrap-валидация

```php
// Добавляет класс needs-validation к <form>
add_filter('wpcf7_form_class_attr', function($class) {
    $class .= ' needs-validation';
    return $class;
});
```

### Добавление HTML5 required

```php
// Преобразует aria-required="true" → добавляет required
add_filter('wpcf7_form_elements', 'dd_wpcf7_form_elements_replace', 5);

// Добавляет required ко всем чекбоксам (кроме class="optional")
add_filter('wpcf7_form_elements', function($content) { ... });
```

### Удаление required из опциональных согласий

Сложная логика: после всех фильтров (приоритет 999) читает список согласий через `CF7_Consents_Panel`, находит опциональные (не required), удаляет у них атрибуты `required` и `aria-required`.

```php
add_filter('wpcf7_form_elements', 'dd_wpcf7_remove_required_from_optional_acceptance', 999);
```

### Маска телефона

```php
// Добавляет data-mask из Redux (opt_phone_mask) к input[type=tel]
// Если маска не задана в Redux — фильтр ничего не делает
add_filter('wpcf7_form_elements', function(string $html): string { ... });
```

Маска берётся из **Настройки темы → Phone mask** (`opt_phone_mask`). Менять маску в шорткоде не нужно — она применяется ко всем CF7-формам автоматически. JS `addTelMask()` подхватывает `input[data-mask]` и инициализирует библиотеку `PhoneMask`.

---

## Панель типа формы (Form Type Panel)

Добавляет вкладку **"Form Type"** в редактор CF7 (аналог поля `form_type` в CodeWeber Forms).

### Как работает

1. Фильтр `wpcf7_editor_panels` — регистрирует панель с коллбэком `codeweber_cf7_render_form_type_panel()`
2. Action `wpcf7_save_contact_form` — сохраняет тип в `post_meta` с ключом `_cf7_form_type`
3. Фильтр `wpcf7_form_additional_atts` — добавляет `data-form-type="{type}"` к тегу `<form>`

### Доступные типы

| Значение | Описание |
|---------|---------|
| `form` | Обычная форма (дефолт) |
| `callback` | Запрос обратного звонка |
| `newsletter` | Форма подписки |
| `testimonial` | Форма отзыва |
| `resume` | Форма резюме |
| `contact` | Контактная форма |

### Чтение типа в JS

```javascript
const formType = document.querySelector('.wpcf7 form').dataset.formType;
// 'callback', 'newsletter', и т.д.
```

---

## Панель согласий (Consents Panel)

Подключается через `functions/integrations/cf7-consents-panel.php` (отдельный файл).

`CF7_Consents_Panel` добавляет вкладку **"Consents"** в редактор CF7, позволяя привязать документы согласий (CPT `legal`) к полям acceptance. Согласия сохраняются в `post_meta` формы и читаются при рендеринге для управления required.

---

## REST endpoint

```
GET /wp-json/custom/v1/cf7-title/{id}
```

Возвращает заголовок и slug CF7-формы по ID:

```json
{
  "id": 123,
  "title": "Форма обратной связи",
  "slug": "forma-obratnoy-svyazi"
}
```

`permission_callback: '__return_true'` — публичный endpoint (только GET, не мутирующий).

---

## Управление загрузкой CF7 ассетов

По умолчанию CF7 подключает скрипты и стили на всех страницах. Для отключения в `codeweber_cf7_styles_scripts()`:

```php
// Раскомментировать в cf7.php:
// wp_dequeue_script('contact-form-7');
// wp_dequeue_style('contact-form-7');
```

После этого нужно вручную подключать CF7 только на нужных страницах.

---

## Порядок фильтров `wpcf7_form_elements`

| Приоритет | Функция | Действие |
|-----------|---------|---------|
| 5 | `dd_wpcf7_form_elements_replace` | Добавляет `required` для `aria-required="true"` |
| 10 (default) | Анонимная (wrapper-спаны) | Убирает `wpcf7-list-item` и `wpcf7-acceptance` обёртки |
| 10 (default) | `wpcf7_form_elements` (control-wrap) | Убирает `wpcf7-form-control-wrap` |
| 10 (default) | Анонимная (чекбоксы) | Добавляет `required` к чекбоксам (кроме `class="optional"`) |
| 10 (default) | Анонимная (маска телефона) | Добавляет `data-mask` из `opt_phone_mask` к `input[type=tel]` |
| 999 | `dd_wpcf7_remove_required_from_optional_acceptance` | Удаляет `required` у опциональных согласий |

---

## Успешная отправка CF7

После отправки формы CF7 генерирует событие `wpcf7mailsent`. Обработчик в `cf7-success-message.js`:

```js
document.addEventListener('wpcf7mailsent', function(event) {
    // Делегируем в единый success-обработчик модальной системы
    if (window.codeweberModal && window.codeweberModal.showSuccess) {
        window.codeweberModal.showSuccess('');
    }
    // Очищаем классы валидации формы
    event.target.classList.remove('was-validated');
    event.target.querySelectorAll('.form-control, .form-check-input')
        .forEach(el => { el.classList.remove('is-valid', 'is-invalid'); });
});
```

`showSuccess('')` (пустая строка) → серверный перевод из REST endpoint `codeweber/v1/success-message-template`.

Подробнее о потоке: [MODAL_SYSTEM.md](../api/MODAL_SYSTEM.md#10-успешная-отправка-формы).

---

## Связь с другими модулями

| Модуль | Связь |
|--------|-------|
| **Personal Data V2** | `CF7_Data_Provider` экспортирует данные Flamingo для GDPR |
| **CodeWeber Forms** | Независимы; оба используют `window.codeweberModal.showSuccess()` для показа результата |
| **Matomo** | `matomo-forms-integration.php` отслеживает CF7-события через REST + JS |
| **Newsletter** | Тип формы `newsletter` — JS-хук для подписки после submit CF7 |
| **Modal System** | `cf7-success-message.js` → `window.codeweberModal.showSuccess()` → `restapi.js` |

---

## Важно: debug-логирование в cf7.php

Функция `codeweber_cf7_add_form_type_attribute()` содержит блоки `#region agent log` с вызовами `file_put_contents()` в файл `.cursor/debug.log`. Это устаревший debug-код, аналогичный тому, что был удалён из `header.php` и `functions.php` в ходе предыдущего аудита безопасности. **Подлежит удалению** из строк 582–594, 599–610, 613–625, 631–642, 657–668 файла [functions/integrations/cf7.php](../../functions/integrations/cf7.php).
