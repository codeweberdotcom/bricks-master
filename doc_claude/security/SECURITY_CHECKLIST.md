# Чеклист безопасности темы CodeWeber

> Источник: `SECURITY_AUDIT.md` (аудит от 2026-03-11).
> Этот файл — краткая справка для разработчиков. Подробное обоснование — в `SECURITY_AUDIT.md`.

---

## Уже исправлено ✅

| Проблема | Файл | Статус |
|---------|------|--------|
| Debug-логирование в файлы на каждый запрос | `header.php`, `functions.php`, `getHotspotContent.php`, `cf7.php` | ✅ Удалено |
| Публичный лог-файл в корне сайта | `debug-df0f0f.log` | ✅ Удалён |
| Нет nonce в AJAX-обработчике fetch | `fetch/fetch-handler.php` | ✅ `check_ajax_referer()` добавлен |
| Нет nonce в JS для AJAX-запроса | `fetch/assets/js/fetch-handler.js` | ✅ `nonce: fetch_vars.nonce` |
| Нет whitelist post_type, нет post_status=publish | `fetch/getPosts.php` | ✅ Whitelist + `absint()` + publish |
| `filemtime()` на каждый запрос в production | `enqueues.php` | ✅ Заменён на `codeweber_asset_version()` |
| Дублирующий `wpApiSettings` | `enqueues.php` | ✅ Дубль удалён |
| Функция без префикса `enqueue_my_custom_script()` | `enqueues.php` | ✅ → `codeweber_enqueue_restapi_script()` |
| CSRF bypass: `return true` при отсутствии nonce | `codeweber-forms/codeweber-forms-api.php` | ✅ → `return false` |
| SQL к `information_schema` без `prepare()` | `ajax-search-module/search-statistics.php` | ✅ Исправлено |
| SMS.ru callback без проверки IP | `smsru/callback.php` | ✅ IP whitelist `217.107.239.0/24` |
| DaData без rate-limiting для анонимов | `dadata/dadata-ajax.php` | ✅ Rate limit 30 req/min через transient |

---

## AJAX-обработчики: чеклист

При добавлении нового AJAX-обработчика проверить:

```php
// ✅ 1. Nonce-верификация
check_ajax_referer('my_nonce_action', 'nonce');
// или:
if (!wp_verify_nonce($_POST['nonce'] ?? '', 'my_nonce_action')) {
    wp_send_json_error(['message' => 'Security check failed.'], 403);
}

// ✅ 2. Санитизация всех входных данных
$type   = sanitize_key(wp_unslash($_POST['type'] ?? ''));
$text   = sanitize_text_field(wp_unslash($_POST['text'] ?? ''));
$number = absint($_POST['count'] ?? 0);
$email  = sanitize_email(wp_unslash($_POST['email'] ?? ''));

// ✅ 3. Whitelist допустимых значений (для post_type, action и т.п.)
$allowed_types = get_post_types(['public' => true]);
if (!in_array($type, $allowed_types, true)) {
    $type = 'post';
}

// ✅ 4. Ограничение диапазона числовых значений
$per_page = max(1, min(100, absint($per_page)));

// ✅ 5. Только publish-посты (если это WP_Query)
$query = new WP_Query([
    'post_status' => 'publish',
    // ...
]);

// ✅ 6. Правила доступа: является ли пользователь залогиненным?
// wp_ajax_ + wp_ajax_nopriv_ — для всех
// wp_ajax_ — только для залогиненных
```

**Nonce в JS:**
```javascript
// При локализации скрипта (PHP):
wp_localize_script('my-script', 'myVars', [
    'nonce' => wp_create_nonce('my_nonce_action'),
]);

// В JS-запросе:
body: new URLSearchParams({
    action: 'my_ajax_action',
    nonce:  myVars.nonce,
    // остальные данные
})
```

---

## REST API: чеклист

При добавлении нового REST-endpoint проверить:

```php
register_rest_route('codeweber/v1', '/my-endpoint', [
    'methods'             => 'POST',
    'callback'            => 'my_callback',

    // ✅ permission_callback — никогда не оставлять __return_true для мутирующих операций
    'permission_callback' => function() {
        return current_user_can('manage_options');  // или нужная capability
    },

    // ✅ args с validation/sanitize_callback
    'args' => [
        'email' => [
            'required'          => true,
            'validate_callback' => 'is_email',
            'sanitize_callback' => 'sanitize_email',
        ],
        'count' => [
            'default'           => 10,
            'sanitize_callback' => 'absint',
        ],
    ],
]);

// ✅ Nonce для аутентифицированных запросов
// Передаётся как заголовок X-WP-Nonce: <wp_create_nonce('wp_rest')>
// WordPress проверяет автоматически при наличии заголовка
```

**Публичные endpoints (для форм, поиска):**
```php
'permission_callback' => '__return_true',
// Но обязательно:
// - Валидировать все входные данные через args
// - Добавить rate-limiting (transient по IP)
// - Не возвращать чувствительные данные
```

---

## SQL-запросы: чеклист

```php
// ✅ ВСЕГДА использовать $wpdb->prepare() с переменными
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}my_table WHERE email = %s AND status = %s",
        $email,
        $status
    )
);

// ✅ Whitelist для динамических имён таблиц/полей (нельзя использовать prepare())
$allowed_columns = ['id', 'email', 'status', 'created_at'];
if (!in_array($order_by, $allowed_columns, true)) {
    $order_by = 'id';
}
$query = "SELECT * FROM {$wpdb->prefix}my_table ORDER BY {$order_by} DESC";

// ✅ Использовать $wpdb->insert() / $wpdb->update() / $wpdb->delete() для записи
$wpdb->insert($wpdb->prefix . 'my_table', [
    'email'      => sanitize_email($email),
    'created_at' => current_time('mysql'),
], ['%s', '%s']);

// ❌ НЕ делать:
$wpdb->query("SELECT * FROM {$wpdb->prefix}my_table WHERE email = '$email'");  // SQL injection!
```

---

## Загрузка файлов: чеклист

```php
// Если форма принимает файлы (тип поля 'file' в CodeWeberForms):

// ✅ Проверка MIME-типа через wp_check_filetype_and_ext()
$check = wp_check_filetype_and_ext($tmp_path, $filename);
if (!$check['ext'] || !$check['type']) {
    // Отклонить файл
}

// ✅ Проверка допустимых расширений (whitelist)
$allowed_types = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
if (!in_array(strtolower($extension), $allowed_types, true)) {
    // Отклонить файл
}

// ✅ Ограничение размера
$max_size = 5 * 1024 * 1024;  // 5MB
if ($_FILES['file']['size'] > $max_size) {
    // Отклонить файл
}

// ✅ Сохранять файлы через wp_handle_upload() или в uploads/
// ❌ НЕ сохранять в публично доступные директории без проверки типа
// ❌ НЕ хранить в /tmp напрямую в production
```

---

## Вывод данных: чеклист

```php
// В шаблонах и хуках:

// ✅ Экранирование при выводе
echo esc_html($title);           // Для текста
echo esc_attr($attribute);       // Для HTML-атрибутов
echo esc_url($link);             // Для URL
echo wp_kses_post($content);     // Для HTML с разрешёнными тегами
echo esc_js($js_string);         // Для inline-JS

// ✅ i18n с экранированием
esc_html_e('Text', 'codeweber');
esc_attr_e('Attribute text', 'codeweber');

// ❌ НЕ делать:
echo $title;                     // XSS!
echo '<a href="' . $url . '">';  // XSS через URL!
```

---

## Права доступа: чеклист

```php
// ✅ Проверка capability перед чувствительными операциями
if (!current_user_can('manage_options')) {
    wp_die(__('Access denied', 'codeweber'));
}

// ✅ Для пользовательских данных — проверять владение записью
$post = get_post($post_id);
if (!$post || $post->post_author != get_current_user_id()) {
    wp_send_json_error('Access denied', 403);
}

// ✅ Проверка nonce в admin-action формах
if (!wp_verify_nonce($_POST['_wpnonce'], 'my_admin_action')) {
    wp_die('Security check failed');
}
check_admin_referer('my_admin_action');  // Альтернативный вариант
```

---

## Специфичные риски темы

### Fetch-система (AJAX)

| Проверка | Статус |
|---------|--------|
| `check_ajax_referer('fetch_action_nonce', 'nonce')` в `handle_fetch_action()` | ✅ |
| Nonce передаётся через `fetch_vars.nonce` в JS | ✅ |
| `actionType` санитизируется через `sanitize_text_field()` | ✅ |
| `params` декодируется через `json_decode()` (не `unserialize`) | ✅ |
| Каждый `getPosts.php` использует whitelist типов и `post_status=publish` | ✅ |

### CodeWeberForms (REST API)

| Проверка | Статус |
|---------|--------|
| Nonce проверяется в `codeweber-forms/v1/submit` | ✅ (исправлено: был return true) |
| Rate limiting для отправки форм | ✅ |
| Honeypot против ботов | ✅ |
| Файлы проверяются по MIME + расширению | Проверить в `codeweber-forms-core.php` |

### DaData (AJAX)

| Проверка | Статус |
|---------|--------|
| Nonce `codeweber_dadata_clean` в обоих хендлерах | ✅ |
| Rate limiting 30 req/min по IP | ✅ |
| `dadata_secret` не передаётся в браузер | ✅ |
| `dadata_enabled` проверяется перед вызовом API | ✅ |

### SMS.ru (callback)

| Проверка | Статус |
|---------|--------|
| IP whitelist `217.107.239.0/24` | ✅ |
| `$_POST['data']` санитизируется через `preg_replace` | ✅ |
| Ответ `100` при успехе | ✅ |

---

## Известные архитектурные риски (не критичные)

| Риск | Описание |
|-----|---------|
| `global $opt_name` в десятках файлов | Устаревший паттерн. Использовать `Codeweber_Options::get()` |
| `$GLOBALS['codeweber_use_this_header_settings']` | Неявные зависимости через глобальные переменные |
| Несогласованные префиксы функций (`codeweber_`, `cw_`, `codeweber_`, без префикса) | Риск конфликта с плагинами; новые функции — только `codeweber_` |
| `social_links()` без префикса | При установке плагина с аналогичной функцией — фатальная ошибка |

---

## Быстрая проверка нового кода

Перед коммитом проверить grep:

```bash
# Потенциальные SQL-инъекции
grep -r "\$wpdb->query.*\$_" functions/

# Прямой вывод POST/GET без экранирования
grep -r "echo.*\$_" templates/ --include="*.php"

# Несанитизированные данные в SQL
grep -rn "\$_POST\|\$_GET" functions/ | grep -v "sanitize\|wp_verify_nonce\|absint"

# Функции без wp_ajax_ nonce
grep -B10 "wp_ajax_nopriv_" functions/ | grep -v "check_ajax_referer\|wp_verify_nonce"
```
