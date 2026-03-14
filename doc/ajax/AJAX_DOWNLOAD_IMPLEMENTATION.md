# Реализация AJAX загрузки файлов для документов

## ✅ Что было реализовано

### 1. REST API Endpoint
**Файл:** `wp-content/themes/codeweber/functions/cpt/cpt-documents.php`

Добавлен endpoint: `/codeweber/v1/documents/{id}/download-url`

- Получает ID документа
- Возвращает URL файла, имя файла и ID поста
- Логирует загрузку через хук `document_downloaded`

### 2. JavaScript для AJAX загрузки
**Файл:** `wp-content/themes/codeweber/src/assets/js/ajax-download.js`

Функционал:
- Автоматически обрабатывает клики по кнопкам с классом `.ajax-download`
- Получает URL файла через REST API
- Программно инициирует загрузку без перезагрузки страницы
- Показывает индикатор загрузки на кнопке
- Обрабатывает ошибки
- Поддерживает Google Analytics (если подключен)

### 3. Обновление шаблона
**Файл:** `wp-content/themes/codeweber/templates/post-cards/documents/card.php`

Кнопка загрузки теперь:
- Использует класс `.ajax-download`
- Имеет атрибуты `data-post-id` и `data-file-name`
- Показывает индикатор загрузки при клике

### 4. Подключение скрипта
**Файл:** `wp-content/themes/codeweber/functions/enqueues.php`

Скрипт подключается на:
- Архивах документов
- Отдельных страницах документов
- Страницах с блоками post-grid
- Всех страницах и архивах (для универсальности)

## 📋 Использование

### В шаблонах

Для использования AJAX загрузки добавьте кнопку с классом `.ajax-download`:

```php
<a href="javascript:void(0)" 
   class="btn btn-primary ajax-download<?php echo Codeweber_Options::style('button'); ?>"
   data-post-id="<?php echo esc_attr($post_id); ?>"
   data-file-name="<?php echo esc_attr($file_name); ?>"
   data-loading-text="<?php esc_attr_e('Loading...', 'codeweber'); ?>">
    <i class="uil uil-import"></i>
    <span><?php esc_html_e('Download', 'codeweber'); ?></span>
</a>
```

### Атрибуты кнопки

- `data-post-id` (обязательно) - ID поста документа
- `data-file-name` (опционально) - Имя файла для загрузки
- `data-loading-text` (опционально) - Текст при загрузке (по умолчанию "Loading...")
- `class="ajax-download"` (обязательно) - Класс для инициализации

### Программное использование

```javascript
// Если нужно вызвать загрузку программно
if (typeof window.ajaxDownloadFile !== 'undefined') {
    window.ajaxDownloadFile(
        postId,        // ID документа
        fileName,      // Имя файла (опционально)
        buttonElement, // Элемент кнопки (опционально)
        onSuccess,     // Callback при успехе (опционально)
        onError        // Callback при ошибке (опционально)
    );
}
```

## 🔧 Дополнительные возможности

### Логирование загрузок

Для логирования загрузок можно использовать хук `document_downloaded`:

```php
add_action('document_downloaded', function($post_id) {
    $downloads = get_post_meta($post_id, '_document_downloads', true);
    $downloads = $downloads ? intval($downloads) + 1 : 1;
    update_post_meta($post_id, '_document_downloads', $downloads);
});
```

### Ограничение доступа

Для ограничения доступа измените `permission_callback` в endpoint:

```php
'permission_callback' => function() {
    return is_user_logged_in(); // Только для авторизованных
}
```

## 🚀 Сборка

После изменений в `src/assets/js/ajax-download.js` выполните:

```bash
npm run build
```

Или скопируйте файл вручную в `dist/assets/js/ajax-download.js`

## ✅ Преимущества

1. ✅ **Без перезагрузки страницы** - лучший UX
2. ✅ **Аналитика** - можно отслеживать загрузки
3. ✅ **Безопасность** - можно добавить проверки доступа
4. ✅ **Логирование** - учет количества загрузок
5. ✅ **Гибкость** - можно добавить уведомления, прогресс-бары и т.д.

## 🔍 Отладка

Если загрузка не работает:

1. Проверьте консоль браузера на ошибки
2. Убедитесь, что `wpApiSettings` доступен (должен быть подключен `restapi.js`)
3. Проверьте, что REST API endpoint доступен: `/wp-json/codeweber/v1/documents/{id}/download-url`
4. Убедитесь, что у документа есть файл в метаполе `_document_file`


