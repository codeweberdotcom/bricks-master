# Стандарты кода — тема CodeWeber

## Префиксы функций и классов

Все новые функции, классы и хуки должны использовать префикс `codeweber_`. Другие префиксы в кодовой базе — исторические.

| Префикс | Статус | Примеры |
|---------|--------|--------|
| `codeweber_` | ✅ Актуальный | `codeweber_asset_version()`, `codeweber_enqueue_restapi_script()` |
| `codeweber_` | ⚠ Устаревший | `codeweber_styles_scripts()`, `codeweber_get_dist_file_url()` |
| `cw_` | ⚠ Устаревший | `cw_render_post_card()` |
| `codeweber_` | ⚠ Устаревший | `codeweber_get_dist_file_url()` |
| без префикса | ❌ Недопустимо для новых | `social_links()`, `enqueue_my_custom_script()` (переименована) |

> **Правило:** Любая новая функция, не являющаяся методом класса, обязана иметь префикс `codeweber_`.

---

## PHP: стандарты именования

```php
// Функции — snake_case с префиксом
function codeweber_get_post_template($post_type) { ... }

// Классы — PascalCase
class NewsletterSubscriptionDatabase { ... }
class Codeweber_Yandex_Maps { ... }

// Константы — UPPER_SNAKE_CASE с префиксом
define('NEWSLETTER_SUBSCRIPTION_PATH', __DIR__);
define('CODEWEBER_FORMS_MATOMO_SITE_ID', 1);

// Хуки — snake_case с префиксом
do_action('codeweber_form_after_send', $form_id, $fields);
apply_filters('codeweber_header_post_id', $post_id);
```

---

## Безопасность: обязательные проверки

### AJAX-обработчик (минимальный шаблон)

```php
add_action('wp_ajax_codeweber_my_action', 'codeweber_my_action_handler');
add_action('wp_ajax_nopriv_codeweber_my_action', 'codeweber_my_action_handler');

function codeweber_my_action_handler() {
    // 1. Nonce
    if (!check_ajax_referer('codeweber_my_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Security check failed.'], 403);
    }

    // 2. Санитизация
    $type = sanitize_key(wp_unslash($_POST['type'] ?? ''));
    $text = sanitize_text_field(wp_unslash($_POST['text'] ?? ''));
    $id   = absint($_POST['id'] ?? 0);

    // 3. Whitelist (если это тип записи)
    $allowed = get_post_types(['public' => true]);
    if (!in_array($type, $allowed, true)) {
        $type = 'post';
    }

    // 4. Обработка и ответ
    wp_send_json_success(['result' => $data]);
}
```

### REST endpoint (минимальный шаблон)

```php
add_action('rest_api_init', function() {
    register_rest_route('codeweber/v1', '/my-endpoint', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'codeweber_my_rest_callback',
        'permission_callback' => '__return_true', // Только для публичных!
        'args' => [
            'id' => [
                'required'          => true,
                'sanitize_callback' => 'absint',
                'validate_callback' => fn($val) => $val > 0,
            ],
        ],
    ]);
});
```

> Для мутирующих операций (POST/PUT/DELETE) `permission_callback` **никогда** не `__return_true`.

### SQL

```php
global $wpdb;

// ✅ Всегда через prepare()
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}my_table WHERE email = %s AND status = %s",
        $email,
        $status
    )
);

// ✅ Вставка через wpdb->insert()
$wpdb->insert(
    $wpdb->prefix . 'my_table',
    ['email' => sanitize_email($email), 'name' => sanitize_text_field($name)],
    ['%s', '%s']
);
```

### Вывод данных

```php
echo esc_html($title);           // Текст
echo esc_attr($attribute);       // HTML-атрибут
echo esc_url($link);             // URL
echo wp_kses_post($content);     // HTML с разрешёнными тегами
echo esc_js($js_string);         // Inline JS
```

---

## Хуки: правила добавления

### Нейминг хуков

```php
// Actions
do_action('codeweber_{module}_{event}', $arg1, $arg2);
// Примеры:
do_action('codeweber_form_after_send', $form_id, $fields);
do_action('codeweber_yandex_maps_init', $maps_instance);

// Filters
apply_filters('codeweber_{что_фильтруется}', $value, $context);
// Примеры:
apply_filters('codeweber_header_post_id', $post_id);
apply_filters('codeweber_post_card_template_dir', $dir, $post_type);
```

### Документировать хуки при создании

```php
/**
 * Fires after a form submission is saved.
 *
 * @param int    $form_id  Form ID.
 * @param array  $fields   Submitted field values.
 * @param string $status   Submission status: 'sent', 'error'.
 */
do_action('codeweber_form_after_send', $form_id, $fields, $status);
```

---

## Работа с Redux Options

```php
// ❌ НЕ читать напрямую из get_option()
$options = get_option('redux_demo');
$value = $options['my_key'];

// ✅ Через Codeweber_Options::get()
$value = Codeweber_Options::get('my_key');

// Или через Redux (если нужно с дефолтом)
global $opt_name;
$value = Redux::get_option($opt_name, 'my_key', 'default_value');
```

> `Codeweber_Options::get()` читает напрямую из `get_option('redux_demo')`. Оба варианта эквивалентны, но первый предпочтительнее как более явная обёртка.

> Redux доступен только после хука `after_setup_theme` с приоритетом ≥ 30. Не вызывать `Redux::get_option()` в глобальном контексте PHP.

---

## Глобальные переменные: правила

В кодовой базе используются `global $opt_name` и `$GLOBALS[]` — это устаревший паттерн. В новом коде:

```php
// ❌ Устаревший паттерн
global $opt_name;
Redux::get_option($opt_name, 'key');

// ✅ Предпочтительно
Codeweber_Options::get('key');

// ❌ Не создавать новых глобальных переменных
global $my_data;
$my_data = [...];

// ✅ Использовать хуки или singleton
```

---

## Карточки (cards): правила вёрстки

### Никогда не использовать `stretched-link`

Bootstrap `.stretched-link` запрещён в этом проекте.

**Почему:** `stretched-link` конфликтует с интерактивными элементами внутри карточки (соцсети, кнопки, ссылки) — они перекрываются псевдоэлементом `::after` и становятся некликабельными.

**Паттерны для кликабельной карточки:**

```html
<!-- ✅ Вся карточка — ссылка (только если внутри НЕТ других <a>) -->
<a href="..." class="card lift overflow-hidden text-inherit text-decoration-none">
    <figure>...</figure>
    <div class="card-body">...</div>
</a>

<!-- ✅ Карточка с интерактивными элементами внутри — ссылки на фото и заголовок отдельно -->
<div class="card lift overflow-hidden">
    <div class="row g-0 h-100">
        <div class="col-3">
            <figure class="mb-0 h-100">
                <a href="..." class="d-block h-100">
                    <img ... class="w-100 h-100 object-fit-cover">
                </a>
            </figure>
        </div>
        <div class="col-9">
            <div class="card-body">
                <h4><a href="..." class="link-dark text-decoration-none">Имя</a></h4>
                <!-- соцсети, кнопки — кликабельны без конфликтов -->
            </div>
        </div>
    </div>
</div>

<!-- ❌ Никогда -->
<a class="stretched-link">...</a>
```

**Важно:** `<a>` как внешний контейнер карточки нельзя использовать, если внутри есть другие `<a>` (соцсети, кнопки-ссылки) — браузер сломает разметку из-за запрета вложенных `<a>`.

---

## Структура нового модуля

```
functions/integrations/my-module/
├── my-module-init.php       # Точка входа; require_once из functions.php
├── class-my-module.php      # Основной класс (если есть)
├── my-module-ajax.php       # AJAX-обработчики (если есть)
└── assets/
    └── js/
        └── my-module.js
```

### `my-module-init.php` — шаблон

```php
<?php

if (!defined('ABSPATH')) {
    exit;
}

define('MY_MODULE_PATH', __DIR__);

require_once MY_MODULE_PATH . '/class-my-module.php';

add_action('init', function() {
    new My_Module();
}, 20);
```

### Подключение в `functions.php`

```php
// Добавить в нужном месте (после зависимостей):
require_once get_template_directory() . '/functions/integrations/my-module/my-module-init.php';
```

---

## Enqueue: регистрация скриптов нового модуля

```php
function codeweber_enqueue_my_module() {
    $dist_path = codeweber_get_dist_file_path('dist/assets/js/my-module.js');
    $dist_url  = codeweber_get_dist_file_url('dist/assets/js/my-module.js');

    if (!$dist_path || !$dist_url) {
        return;
    }

    wp_enqueue_script(
        'my-module',
        $dist_url,
        ['plugins-scripts'],               // Bootstrap как зависимость
        codeweber_asset_version($dist_path),
        true                               // В футере
    );

    wp_localize_script('my-module', 'myModuleVars', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('my_module_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'codeweber_enqueue_my_module', 20);
```

---

## i18n: переводы

```php
// Вывод переведённого текста
echo esc_html__('My text', 'codeweber');
esc_html_e('My text', 'codeweber');

// С экранированием
echo esc_attr__('Attribute text', 'codeweber');

// С переменными
echo esc_html(sprintf(__('Hello, %s!', 'codeweber'), $name));
```

**Text domain:** всегда `'codeweber'`.

---

## Запрещённые практики

| Запрещено | Правильно |
|----------|---------|
| `echo $variable;` | `echo esc_html($variable);` |
| `$wpdb->query("... '$var'")` | `$wpdb->prepare("... %s", $var)` |
| `unserialize($_POST['data'])` | `json_decode(wp_unslash($_POST['data']))` |
| AJAX без nonce | `check_ajax_referer('action', 'nonce')` |
| `permission_callback => '__return_true'` для POST/PUT/DELETE | Проверить `current_user_can()` |
| `error_log()` в production-коде | Только под `if (WP_DEBUG)` |
| `file_put_contents()` в шаблонах | Никогда в шаблонах |
| Функции без префикса | `codeweber_` префикс обязателен |
