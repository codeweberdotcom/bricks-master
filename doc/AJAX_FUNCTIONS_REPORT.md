# Отчет: Универсальные AJAX функции в теме

## Общая информация

**Дата анализа:** 2025-01-06  
**Тема:** Codeweber  
**Статус:** В теме есть универсальная система AJAX

---

## Универсальная система Fetch

### Расположение

**Основные файлы:**
- `functions/fetch/fetch-handler.php` - главный обработчик AJAX запросов
- `functions/fetch/Fetch.php` - класс для HTTP запросов
- `functions/fetch/assets/js/fetch-handler.js` - JavaScript клиент
- `functions/fetch/exampleFunction.php` - пример функции
- `functions/fetch/getPosts.php` - пример загрузки постов

**Подключение:**
- Подключается в `functions.php` (строка 36)
- JavaScript подключается в `functions/enqueues.php` (строки 103-124)

---

## Как работает система Fetch

### 1. Backend (PHP)

**Главный обработчик:** `handle_fetch_action()`

```php
// Регистрация обработчика
add_action('wp_ajax_fetch_action', 'Codeweber\\Functions\\Fetch\\handle_fetch_action');
add_action('wp_ajax_nopriv_fetch_action', 'Codeweber\\Functions\\Fetch\\handle_fetch_action');

function handle_fetch_action() {
    $actionType = $_POST['actionType'] ?? null;
    $params = json_decode(stripslashes($_POST['params'] ?? '[]'), true);
    
    // Маршрутизация по типу действия
    if ($actionType === 'exampleFunction') {
        $response = exampleFunction($params);
        wp_send_json($response);
    }
    
    if ($actionType === 'getPosts') {
        $response = getPosts($params);
        wp_send_json($response);
    }
    
    wp_send_json([
        'status' => 'error',
        'message' => 'Неизвестное действие.',
    ]);
}
```

**Структура ответа:**
```php
// Успешный ответ
return [
    'status' => 'success',
    'data' => $html, // или любые данные
];

// Ошибка
return [
    'status' => 'error',
    'message' => 'Сообщение об ошибке',
];
```

### 2. Frontend (JavaScript)

**Автоматическая обработка элементов с атрибутом `data-fetch`:**

```html
<button 
    data-fetch="getPosts" 
    data-params='{"type":"post","perpage":5}'
    data-wrapper="#posts-container">
    Загрузить посты
</button>

<div id="posts-container">
    <!-- Сюда будет загружен контент -->
</div>
```

**JavaScript автоматически:**
1. Находит все элементы с `data-fetch`
2. Добавляет обработчики кликов
3. Показывает лоадер
4. Отправляет AJAX запрос
5. Вставляет результат в `data-wrapper` элемент

**Доступные переменные:**
- `fetch_vars.ajaxurl` - URL для AJAX запросов (`admin-ajax.php`)

---

## Примеры использования

### Пример 1: Простая функция

**Backend (`exampleFunction.php`):**
```php
namespace Codeweber\Functions\Fetch;

function exampleFunction($params) {
    $name = $params['name'] ?? 'Гость';
    $age = $params['age'] ?? 0;
    
    return [
        'status' => 'success',
        'data' => "Привет, $name! Тебе $age лет.",
    ];
}
```

**Frontend:**
```html
<button 
    data-fetch="exampleFunction" 
    data-params='{"name":"Иван","age":25}'
    data-wrapper="#result">
    Показать приветствие
</button>
<div id="result"></div>
```

### Пример 2: Загрузка постов

**Backend (`getPosts.php`):**
```php
namespace Codeweber\Functions\Fetch;

function getPosts($params) {
    $type = $params['type'] ?? 'post';
    $perpage = $params['perpage'] ?? 5;
    
    $query = new \WP_Query([
        'post_type' => $type,
        'posts_per_page' => $perpage,
    ]);
    
    if ($query->have_posts()) {
        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('templates/content/single');
        }
        wp_reset_postdata();
        $html = ob_get_clean();
        
        return [
            'status' => 'success',
            'data' => $html,
        ];
    }
    
    return [
        'status' => 'error',
        'message' => 'Посты не найдены.',
    ];
}
```

**Frontend:**
```html
<button 
    data-fetch="getPosts" 
    data-params='{"type":"post","perpage":5}'
    data-wrapper="#posts-list">
    Загрузить посты
</button>
<div id="posts-list"></div>
```

### Пример 3: Добавление новой функции

**Шаг 1:** Создайте файл функции в `functions/fetch/`

```php
<?php
namespace Codeweber\Functions\Fetch;

function myCustomFunction($params) {
    // Ваша логика
    $result = do_something($params);
    
    return [
        'status' => 'success',
        'data' => $result,
    ];
}
```

**Шаг 2:** Добавьте обработку в `fetch-handler.php`

```php
if ($actionType === 'myCustomFunction') {
    $response = myCustomFunction($params);
    wp_send_json($response);
}
```

**Шаг 3:** Используйте в HTML

```html
<button 
    data-fetch="myCustomFunction" 
    data-params='{"param1":"value1"}'
    data-wrapper="#result">
    Выполнить действие
</button>
```

---

## Класс Fetch для HTTP запросов

**Расположение:** `functions/fetch/Fetch.php`

**Использование:**
```php
use Codeweber\Functions\Fetch\Fetch;

// GET запрос
$response = Fetch::request('https://api.example.com/data', 'GET');

// POST запрос
$response = Fetch::request('https://api.example.com/submit', 'POST', [
    'key' => 'value'
]);
```

**Возвращает:**
- Массив с данными при успехе
- `['status' => 'error', 'message' => '...']` при ошибке

---

## Локализованные переменные для AJAX

### 1. theme_scripts_ajax

**Подключение:** `functions/enqueues.php` (строки 47-53)

**Доступные переменные:**
```javascript
theme_scripts_ajax.ajax_url      // URL для admin-ajax.php
theme_scripts_ajax.nonce         // Nonce для безопасности
theme_scripts_ajax.translations  // Переводы
```

**Использование:**
```javascript
fetch(theme_scripts_ajax.ajax_url, {
    method: 'POST',
    body: new FormData(form),
    headers: {
        'X-WP-Nonce': theme_scripts_ajax.nonce
    }
})
```

### 2. wpApiSettings

**Подключение:** `functions/enqueues.php` (строки 91-96)

**Доступные переменные:**
```javascript
wpApiSettings.root           // REST API root URL
wpApiSettings.nonce          // REST API nonce
wpApiSettings.currentUserId  // ID текущего пользователя
wpApiSettings.isLoggedIn     // Авторизован ли пользователь
```

**Использование:**
```javascript
fetch(`${wpApiSettings.root}wp/v2/posts`, {
    headers: {
        'X-WP-Nonce': wpApiSettings.nonce
    }
})
```

### 3. fetch_vars

**Подключение:** `functions/enqueues.php` (строки 119-121)

**Доступные переменные:**
```javascript
fetch_vars.ajaxurl  // URL для admin-ajax.php
```

**Использование:**
```javascript
fetch(fetch_vars.ajaxurl, {
    method: 'POST',
    body: formData
})
```

---

## Специфичные AJAX обработчики

### 1. Demo AJAX (`functions/demo/demo-ajax.php`)

**Обработчики:**
- `wp_ajax_cw_demo_create_clients` - создание demo клиентов
- `wp_ajax_cw_demo_delete_clients` - удаление demo клиентов
- `wp_ajax_cw_demo_create_faq` - создание demo FAQ
- `wp_ajax_cw_demo_delete_faq` - удаление demo FAQ
- `wp_ajax_cw_demo_create_testimonials` - создание demo отзывов
- `wp_ajax_cw_demo_delete_testimonials` - удаление demo отзывов

**Паттерн:**
```php
function cw_demo_ajax_create_clients() {
    // Проверка прав
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '...']);
    }
    
    // Проверка nonce
    if (!wp_verify_nonce($_POST['nonce'], 'cw_demo_create_clients')) {
        wp_send_json_error(['message' => '...']);
    }
    
    // Выполнение действия
    $result = cw_demo_create_clients();
    
    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
```

### 2. Newsletter AJAX (`functions/integrations/newsletter-subscription/frontend/newsletter-ajax.php`)

**Класс:** `NewsletterSubscriptionAjax`

**Обработчики:**
- `wp_ajax_newsletter_unsubscribe` - отписка от рассылки
- `wp_ajax_nopriv_newsletter_unsubscribe` - отписка (публичный)

### 3. AJAX Search (`functions/integrations/ajax-search-module/`)

**Модуль поиска с AJAX функциональностью:**
- `ajax-search.php` - основной файл
- `ajax-search.js` - JavaScript клиент
- `search-statistics.php` - статистика поиска

### 4. REST API для модальных окон

**Файл:** `src/assets/js/restapi.js`

**Использование:**
- Загрузка контента модальных окон через REST API
- Использует `wpApiSettings` для настройки

---

## Сравнение подходов

### WordPress Admin AJAX (admin-ajax.php)

**Используется в:**
- Система Fetch
- Demo AJAX
- Newsletter AJAX

**Преимущества:**
- Стандартный WordPress подход
- Простая регистрация обработчиков
- Поддержка nonce для безопасности

**Недостатки:**
- Менее RESTful
- Менее гибкий для сложных API

### WordPress REST API

**Используется в:**
- Модальные окна (restapi.js)
- Форма отзывов (testimonial-form-api.php)
- Плагин Load More (codeweber-gutenberg-blocks)

**Преимущества:**
- RESTful подход
- Более гибкий
- Лучше для публичных API
- Поддержка версионирования

**Недостатки:**
- Более сложная настройка
- Требует регистрации маршрутов

---

## Рекомендации по использованию

### Когда использовать систему Fetch:

1. **Простые AJAX действия** - загрузка контента, обновление данных
2. **Быстрое прототипирование** - не нужно писать много кода
3. **Внутренние действия** - действия внутри темы/сайта

**Примеры:**
- Загрузка постов
- Обновление счетчиков
- Простые формы

### Когда использовать REST API:

1. **Публичные API** - доступ извне
2. **Сложная логика** - много параметров, версионирование
3. **Интеграции** - интеграция с другими системами

**Примеры:**
- Модальные окна
- Формы с валидацией
- Интеграции с плагинами

### Когда использовать специфичные обработчики:

1. **Специфичная логика** - уникальная для конкретного модуля
2. **Безопасность** - требуется особая проверка прав
3. **Производительность** - оптимизированная логика

**Примеры:**
- Demo данные
- Newsletter подписки
- AJAX поиск

---

## Безопасность

### Проверка прав доступа

```php
if (!current_user_can('manage_options')) {
    wp_send_json_error(['message' => 'Недостаточно прав']);
}
```

### Проверка nonce

```php
// WordPress Admin AJAX
if (!wp_verify_nonce($_POST['nonce'], 'action_name')) {
    wp_send_json_error(['message' => 'Ошибка безопасности']);
}

// REST API
check_ajax_referer('wp_rest', 'nonce', true);
```

### Sanitization данных

```php
$email = sanitize_email($_POST['email'] ?? '');
$text = sanitize_text_field($_POST['text'] ?? '');
$params = json_decode(stripslashes($_POST['params'] ?? '[]'), true);
```

---

## Заключение

В теме **Codeweber** есть **универсальная система AJAX** через модуль Fetch, которая позволяет:

1. **Быстро создавать AJAX обработчики** - просто добавьте функцию в `functions/fetch/`
2. **Использовать декларативный подход** - HTML атрибуты `data-fetch`
3. **Автоматическую обработку** - JavaScript автоматически обрабатывает запросы

**Дополнительно доступны:**
- Локализованные переменные для AJAX (`theme_scripts_ajax`, `wpApiSettings`, `fetch_vars`)
- Специфичные AJAX обработчики для конкретных модулей
- REST API endpoints для более сложных случаев

**Система Fetch подключена и готова к использованию**, но требует добавления новых функций в `fetch-handler.php` для каждого нового типа действия.

