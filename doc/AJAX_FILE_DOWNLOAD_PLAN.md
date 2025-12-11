# План реализации AJAX загрузки файлов

## Анализ текущей ситуации

### ✅ Что уже есть:
1. **REST API для documents**: Метаполе `_document_file` уже зарегистрировано в REST API (`register_document_file_rest_field`)
2. **Универсальная система Fetch**: Есть готовая система для AJAX запросов
3. **REST API система**: Есть `restapi.js` для работы с REST API
4. **Примеры AJAX**: Есть примеры в `testimonial-form-api.php`

### ❌ Что отсутствует:
1. **REST API endpoint** для получения URL файла по ID поста
2. **JavaScript функция** для AJAX загрузки файлов
3. **Обновление шаблонов** для использования AJAX вместо прямых ссылок

---

## План реализации

### Вариант 1: Получение URL через REST API и программная загрузка (РЕКОМЕНДУЕТСЯ)

**Преимущества:**
- ✅ Простая реализация
- ✅ Не требует изменений в серверной логике
- ✅ Работает с любыми файлами
- ✅ Можно добавить аналитику/логирование загрузок

**Недостатки:**
- ⚠️ Требует два запроса (получение URL + загрузка файла)

**Реализация:**
1. Создать REST API endpoint: `/wp/v2/documents/{id}/download-url`
2. Создать JavaScript функцию для AJAX загрузки
3. Обновить шаблоны для использования AJAX

---

### Вариант 2: Прямая загрузка через AJAX с Blob

**Преимущества:**
- ✅ Один запрос
- ✅ Полный контроль над процессом

**Недостатки:**
- ⚠️ Сложнее реализация
- ⚠️ Проблемы с большими файлами
- ⚠️ Требует обработку CORS

---

## Рекомендуемая реализация (Вариант 1)

### Шаг 1: REST API Endpoint для получения URL файла

**Файл:** `wp-content/themes/codeweber/functions/cpt/cpt-documents.php`

Добавить endpoint:
```php
/**
 * REST API endpoint для получения URL файла документа
 */
function register_document_download_endpoint() {
    register_rest_route('codeweber/v1', '/documents/(?P<id>\d+)/download-url', [
        'methods' => 'GET',
        'callback' => 'get_document_download_url',
        'permission_callback' => '__return_true',
        'args' => [
            'id' => [
                'required' => true,
                'type' => 'integer',
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ]
        ]
    ]);
}
add_action('rest_api_init', 'register_document_download_endpoint');

function get_document_download_url($request) {
    $post_id = $request->get_param('id');
    
    // Проверяем, что пост существует и это documents
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'documents') {
        return new WP_Error(
            'invalid_post',
            __('Document not found', 'codeweber'),
            ['status' => 404]
        );
    }
    
    // Получаем URL файла
    $file_url = get_post_meta($post_id, '_document_file', true);
    
    if (empty($file_url)) {
        return new WP_Error(
            'no_file',
            __('File not found for this document', 'codeweber'),
            ['status' => 404]
        );
    }
    
    // Опционально: логирование загрузки
    // do_action('document_downloaded', $post_id);
    
    return new WP_REST_Response([
        'success' => true,
        'file_url' => esc_url_raw($file_url),
        'file_name' => basename($file_url),
        'post_id' => $post_id
    ], 200);
}
```

---

### Шаг 2: JavaScript функция для AJAX загрузки

**Файл:** `wp-content/themes/codeweber/src/assets/js/ajax-download.js`

```javascript
/**
 * AJAX загрузка файлов
 * 
 * @param {number} postId - ID поста документа
 * @param {string} fileName - Имя файла (опционально)
 * @param {function} onSuccess - Callback при успехе
 * @param {function} onError - Callback при ошибке
 */
function ajaxDownloadFile(postId, fileName, onSuccess, onError) {
    // Показываем индикатор загрузки
    const button = event?.target?.closest('.download-btn');
    if (button) {
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="uil uil-spinner-alt"></i> ' + (button.dataset.loadingText || 'Загрузка...');
    }
    
    // Получаем URL файла через REST API
    fetch(`${wpApiSettings.root}codeweber/v1/documents/${postId}/download-url`, {
        method: 'GET',
        headers: {
            'X-WP-Nonce': wpApiSettings.nonce
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to get file URL');
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.file_url) {
            // Программно инициируем загрузку
            const link = document.createElement('a');
            link.href = data.file_url;
            link.download = fileName || data.file_name || 'download';
            link.target = '_blank';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Восстанавливаем кнопку
            if (button) {
                button.disabled = false;
                button.innerHTML = originalText;
            }
            
            // Вызываем callback успеха
            if (onSuccess) {
                onSuccess(data);
            }
        } else {
            throw new Error('Invalid response');
        }
    })
    .catch(error => {
        console.error('Download error:', error);
        
        // Восстанавливаем кнопку
        if (button) {
            button.disabled = false;
            button.innerHTML = originalText;
        }
        
        // Вызываем callback ошибки
        if (onError) {
            onError(error);
        } else {
            alert('Ошибка при загрузке файла. Попробуйте еще раз.');
        }
    });
}

// Инициализация обработчиков для всех кнопок загрузки
document.addEventListener('DOMContentLoaded', function() {
    // Обработчик для кнопок с классом .ajax-download
    document.addEventListener('click', function(e) {
        const downloadBtn = e.target.closest('.ajax-download');
        if (downloadBtn) {
            e.preventDefault();
            const postId = downloadBtn.dataset.postId || downloadBtn.dataset.documentId;
            const fileName = downloadBtn.dataset.fileName;
            
            if (postId) {
                ajaxDownloadFile(
                    parseInt(postId),
                    fileName,
                    function(data) {
                        // Опционально: показать уведомление об успехе
                        console.log('File downloaded:', data.file_name);
                    },
                    function(error) {
                        console.error('Download failed:', error);
                    }
                );
            }
        }
    });
});
```

---

### Шаг 3: Обновление шаблонов

**Файл:** `wp-content/themes/codeweber/templates/post-cards/documents/card.php`

Заменить обычную ссылку на AJAX версию:

```php
<?php if ($document_file_url) : ?>
    <a href="javascript:void(0)" 
       class="btn btn-primary btn-icon btn-icon-start btn-sm d-flex ajax-download<?php echo getThemeButton(); ?>"
       data-post-id="<?php echo esc_attr($post_data['id']); ?>"
       data-file-name="<?php echo esc_attr($document_file_name); ?>"
       data-loading-text="<?php esc_attr_e('Loading...', 'codeweber'); ?>">
        <i class="uil uil-import fs-15"></i>
        <span><?php esc_html_e('Download', 'codeweber'); ?></span>
    </a>
<?php endif; ?>
```

---

### Шаг 4: Подключение JavaScript

**Файл:** `wp-content/themes/codeweber/functions/enqueues.php`

Добавить подключение скрипта:

```php
/**
 * Enqueue AJAX download script
 */
function codeweber_enqueue_ajax_download() {
    // Проверяем, нужен ли скрипт на текущей странице
    if (is_post_type_archive('documents') || is_singular('documents') || 
        is_page_template('archive-documents.php') || 
        has_block('codeweber/post-grid')) {
        
        $script_path = get_template_directory() . '/src/assets/js/ajax-download.js';
        $script_url = get_template_directory_uri() . '/src/assets/js/ajax-download.js';
        
        if (file_exists($script_path)) {
            wp_enqueue_script(
                'ajax-download',
                $script_url,
                [], // Dependencies
                filemtime($script_path),
                true // Load in footer
            );
            
            // Localize script (используем существующие настройки REST API)
            // wpApiSettings уже подключен в enqueue_my_custom_script()
        }
    }
}
add_action('wp_enqueue_scripts', 'codeweber_enqueue_ajax_download', 25);
```

---

## Дополнительные возможности

### 1. Логирование загрузок

```php
/**
 * Логирование загрузок документов
 */
function log_document_download($post_id) {
    $downloads = get_post_meta($post_id, '_document_downloads', true);
    $downloads = $downloads ? intval($downloads) : 0;
    update_post_meta($post_id, '_document_downloads', $downloads + 1);
    
    // Сохраняем историю загрузок
    $history = get_post_meta($post_id, '_document_download_history', true);
    if (!is_array($history)) {
        $history = [];
    }
    
    $history[] = [
        'date' => current_time('mysql'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_id' => get_current_user_id()
    ];
    
    // Храним только последние 100 записей
    if (count($history) > 100) {
        $history = array_slice($history, -100);
    }
    
    update_post_meta($post_id, '_document_download_history', $history);
}
add_action('document_downloaded', 'log_document_download');
```

### 2. Ограничение доступа

```php
function get_document_download_url($request) {
    $post_id = $request->get_param('id');
    $post = get_post($post_id);
    
    // Проверка прав доступа
    if (!current_user_can('read')) {
        // Требуем авторизацию
        return new WP_Error(
            'unauthorized',
            __('You must be logged in to download this file', 'codeweber'),
            ['status' => 401]
        );
    }
    
    // Дополнительные проверки...
    
    // ... остальной код
}
```

### 3. Аналитика загрузок

Можно интегрировать с Google Analytics или другими системами аналитики:

```javascript
// В ajax-download.js после успешной загрузки
if (typeof gtag !== 'undefined') {
    gtag('event', 'file_download', {
        'file_name': data.file_name,
        'post_id': postId
    });
}
```

---

## Применение для testimonials

Если нужно добавить файлы в testimonials:

1. Добавить метаполе `_testimonial_file` в админке
2. Зарегистрировать в REST API
3. Создать endpoint `/codeweber/v1/testimonials/{id}/download-url`
4. Использовать тот же JavaScript код

---

## Преимущества AJAX загрузки

1. ✅ **Без перезагрузки страницы** - лучший UX
2. ✅ **Аналитика** - можно отслеживать загрузки
3. ✅ **Безопасность** - можно добавить проверки доступа
4. ✅ **Логирование** - учет количества загрузок
5. ✅ **Гибкость** - можно добавить уведомления, прогресс-бары и т.д.

---

## Вопросы и ответы

**Q: Можно ли получить ссылку на файл по AJAX?**  
A: ✅ Да, абсолютно! REST API endpoint вернет URL файла, который можно использовать для загрузки.

**Q: Нужна ли перезагрузка страницы?**  
A: ❌ Нет! JavaScript программно инициирует загрузку через создание временного `<a>` элемента.

**Q: Работает ли это с большими файлами?**  
A: ✅ Да, так как файл загружается напрямую с сервера, а не через AJAX запрос.

**Q: Можно ли использовать для любых CPT?**  
A: ✅ Да, достаточно создать аналогичный endpoint для нужного типа постов.

