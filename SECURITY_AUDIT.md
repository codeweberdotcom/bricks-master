# Аудит безопасности и план улучшений — тема Codeweber

> Дата аудита: 2026-03-11
> Статус: в работе

---

## Что уже исправлено ✅

| Файл | Проблема | Статус |
|------|----------|--------|
| `header.php` | Debug-логирование в `.cursor/debug.log` на каждый запрос | ✅ Удалено |
| `functions.php` | Debug-блок `#region agent log` в `codeweber_initialize_redux()` | ✅ Удалено |
| `functions/fetch/getHotspotContent.php` | 9 блоков `#region agent log` с `file_put_contents()` | ✅ Удалено |
| `debug-df0f0f.log` | Публичный файл лога в корне сайта | ✅ Удалён |
| `functions/fetch/fetch-handler.php` | Нет nonce-проверки в AJAX-обработчике, прямой `$_POST` | ✅ Добавлен `check_ajax_referer()`, `sanitize_text_field()`, `wp_unslash()` |
| `functions/fetch/getPosts.php` | Нет whitelist post_type, нет `post_status`, нет санитизации | ✅ Whitelist, `absint()`, `post_status=publish` |
| `functions/fetch/assets/js/fetch-handler.js` | Nonce не передавался в AJAX-запросе | ✅ Добавлен `nonce: fetch_vars.nonce` |
| `functions/enqueues.php` | `filemtime()` вызывался 9 раз на каждый запрос в production | ✅ Заменён на `codeweber_asset_version()` |
| `functions/enqueues.php` | Дублирующий `wpApiSettings` в `codeweber_enqueue_testimonial_form()` | ✅ Удалён дубль |
| `functions/enqueues.php` | Функция `enqueue_my_custom_script()` без префикса, handle `my-custom-script` | ✅ Переименовано в `codeweber_enqueue_restapi_script()` / `codeweber-restapi` |
| `functions/integrations/codeweber-forms/codeweber-forms-api.php` | `return true` при отсутствии nonce — CSRF bypass | ✅ Исправлено на `return false` |
| `functions/integrations/ajax-search-module/search-statistics.php` | SQL-запрос к `information_schema` без `$wpdb->prepare()` | ✅ Исправлено |

---

## Критические проблемы — требуют исправления 🔴

### 1. SMS.ru callback без защиты
**Файл:** `functions/integrations/smsru/callback.php`

```php
// Текущий код — УЯЗВИМ:
foreach ($_POST["data"] as $entry) {
    $lines = explode("\n", $entry);
    // Прямое использование без проверок источника
}
```

**Решение:** Проверять IP-адрес источника запроса (серверы sms.ru: диапазон `217.107.239.0/24`) или добавить секретный токен в URL callback'а через Redux-настройку. Также добавить `sanitize_text_field()` для всех данных из `$_POST`.

```php
$allowed_ips = ['217.107.239.0/24']; // Уточнить актуальный диапазон sms.ru
$client_ip = $_SERVER['REMOTE_ADDR'];
// Проверить IP перед обработкой
```

---

## Высокий приоритет 🟠

### 2. DaData: открытый AJAX для анонимов расходует платный API
**Файл:** `functions/integrations/dadata/dadata-ajax.php` — строки 53–54, 93–94

```php
// ПРОБЛЕМА: wp_ajax_nopriv_ = доступно без авторизации
add_action('wp_ajax_nopriv_dadata_clean_address', 'codeweber_dadata_ajax_clean_address');
add_action('wp_ajax_nopriv_dadata_suggest_address', 'codeweber_dadata_ajax_suggest_address');
```

**Решение:** Если DaData используется только в WooCommerce checkout/account — убрать `wp_ajax_nopriv_`. Если нужна анонимная подсказка адресов — добавить rate-limiting через transients:

```php
// Rate-limiting: не более 30 запросов в минуту с одного IP
function codeweber_dadata_rate_limit() {
    $ip  = sanitize_text_field($_SERVER['REMOTE_ADDR']);
    $key = 'dadata_rl_' . md5($ip);
    $count = (int) get_transient($key);
    if ($count >= 30) {
        wp_send_json_error(['message' => 'Too many requests'], 429);
    }
    set_transient($key, $count + 1, MINUTE_IN_SECONDS);
}
```

### 3. Поиск для анонимов без rate-limiting
**Файл:** `functions/integrations/ajax-search-module/ajax-search.php` — строки 100–101

```php
add_action('wp_ajax_nopriv_ajax_search_load_all', 'handle_ajax_search_load_all');
add_action('wp_ajax_nopriv_ajax_search', 'handle_ajax_search');
```

Публичный поиск допустим, но нет:
- Минимальной длины запроса (риск `LIKE '%a%'` по всей БД)
- Rate-limiting (риск DoS)
- Ограничения возвращаемых полей

**Решение:**
```php
function handle_ajax_search() {
    $query = sanitize_text_field(wp_unslash($_POST['query'] ?? ''));
    if (mb_strlen($query) < 3) {
        wp_send_json_error(['message' => 'Query too short'], 400);
    }
    // Rate-limiting: аналогично DaData выше
    // ...
}
```

---

## Архитектурные проблемы — средний приоритет 🟡

### 4. Redux вызывается десятки раз без обёртки
**Файлы:** `header.php` (~12 вызовов), `footer.php` (~10), `global.php` (~20+)

Повторяющийся паттерн в каждом файле:
```php
global $opt_name;
if (empty($opt_name)) { $opt_name = 'redux_demo'; }
if (!class_exists('Redux')) { ... }
$value = Redux::get_option($opt_name, 'some-option');
```

**Решение — создать файл `functions/class-codeweber-options.php`:**

```php
<?php
if (!defined('ABSPATH')) exit;

class Codeweber_Options {
    private static $opt = 'redux_demo';

    public static function get(string $key, $default = '') {
        if (!class_exists('Redux')) {
            return $default;
        }
        $value = Redux::get_option(self::$opt, $key, $default);
        return (!empty($value) || $value === 0 || $value === false) ? $value : $default;
    }
}
```

Подключить в `functions.php` и заменить все прямые вызовы:
```php
// Было:
global $opt_name;
$val = Redux::get_option($opt_name, 'global-header-type');

// Стало:
$val = Codeweber_Options::get('global-header-type');
```

### 5. Глобальные переменные в header.php передают контекст в шаблоны
**Файл:** `header.php` — строки ~70–280

```php
// Неявная зависимость через GLOBALS:
$GLOBALS['codeweber_use_this_header_settings']  = true;
$GLOBALS['codeweber_this_header_rounded']        = $rounded;
$GLOBALS['codeweber_this_header_color_text']     = $color;
```

**Решение:** Контекст-класс или массив, передаваемый явно через функцию:
```php
// functions/class-codeweber-page-context.php
class Codeweber_Page_Context {
    private static array $data = [];
    public static function set(string $key, $value): void { self::$data[$key] = $value; }
    public static function get(string $key, $default = null) { return self::$data[$key] ?? $default; }
}
```

### 6. Функция `social_links()` — 159 строк, 9 визуальных вариантов
**Файл:** `functions/global.php` — строки 179–338

Одна функция парсит форматы данных, строит HTML для 9 вариантов, преобразует названия соцсетей. Циклическая сложность >20.

**Решение:** Разбить на отдельные методы внутри класса `Codeweber_Social_Links`. Каждый тип (`type1`–`type9`) — отдельный protected-метод.

### 7. Выбор папки шаблона post-card через 15 `if/elseif`
**Файл:** `functions/post-card-templates.php` — строки 24–122

```php
// Текущий подход:
if (strpos($template_name, 'client-') === 0)       { $dir = 'clients'; }
elseif (strpos($template_name, 'testimonial-') === 0) { $dir = 'testimonials'; }
// ... ещё 7 блоков ...
```

**Решение:** Маппинг-массив + фильтр для расширяемости из дочерней темы:
```php
$template_dir_map = apply_filters('cw_post_card_template_dirs', [
    'client-'       => 'clients',
    'testimonial-'  => 'testimonials',
    'document-'     => 'documents',
    'staff-'        => 'staff',
    'vacancy-'      => 'vacancies',
    'office-'       => 'offices',
    'faq-'          => 'faq',
]);
foreach ($template_dir_map as $prefix => $dir) {
    if (strpos($template_name, $prefix) === 0) {
        $template_dir = $dir;
        break;
    }
}
```

### 8. Непоследовательное именование функций
В одном проекте используются 4 разных префикса:
- `codeweber_*` — основной (20+ функций)
- `cw_*` — сокращённый (5+ функций)
- `brk_*` — старый (enqueues)
- без префикса — `social_links()`, `staff_social_links()`, `universal_*`

**Решение:** Постепенно унифицировать к `codeweber_` (без экстренных переименований, чтобы не сломать дочерние темы). Новые функции создавать только с префиксом `codeweber_`.

### 9. Отсутствие фильтров на критических точках
Невозможно переопределить поведение из дочерней темы без редактирования файлов.

**Добавить минимально:**
```php
// header.php — выбор ID хедера
$header_post_id = apply_filters('codeweber_header_post_id', $header_post_id, $post_type, $post_id);

// post-card-templates.php — путь к шаблону карточки
$template_path = apply_filters('cw_post_card_template_path', $template_path, $template_name, $post_type);

// global.php — данные соцсетей
$links = apply_filters('codeweber_social_links_data', $links, $type);
```

### 10. Кэширование через `static` вместо transients
**Файл:** `functions/images.php` — строки 73–106

```php
function codeweber_get_allowed_image_sizes($post_type = '', $post_id = 0) {
    static $cache = []; // Не инвалидируется при изменении Redux-настроек
    // ...
}
```

**Решение:** Использовать `wp_cache_*` (object cache) или transients:
```php
$cache_key = 'cw_img_sizes_' . md5($post_type . '_' . $post_id);
$cached = wp_cache_get($cache_key, 'codeweber');
if ($cached !== false) return $cached;
// ... вычисляем $result ...
wp_cache_set($cache_key, $result, 'codeweber', HOUR_IN_SECONDS);
```

---

## Низкий приоритет / Технический долг 🔵

### 11. Устаревший комментарий в enqueues.php
**Файл:** `functions/enqueues.php` — строка 367

```php
// wpApiSettings уже подключен в enqueue_my_custom_script() для всех страниц
```

Функция переименована в `codeweber_enqueue_restapi_script()`, комментарий устарел. Обновить.

### 12. `functions/global.php` vs `functions/cleanup.php` — дублирование
Оба файла регистрируют `add_action('init', 'codeweber_disable_emojis')` и аналогичные хуки. Проверить и убрать дубли.

### 13. Использование `global $post` с мутацией
**Файл:** `header.php` (~строка 217)

```php
global $post;
$post = $header_post;   // Мутация глобального объекта — опасно
setup_postdata($post);
the_content();
wp_reset_postdata();
```

**Решение:**
```php
$header_content = do_blocks($header_post->post_content);
echo apply_filters('the_content', $header_content);
```

---

## Порядок работы на следующих сессиях

```
Сессия 1 (✅ выполнено 2026-03-11):
  [x] SMS.ru callback — добавлена проверка IP (whitelist 217.107.239.0/24) + sanitize
  [x] DaData — добавлен rate-limiting (30 req/min per IP) через transients
  [x] Ajax search — переименована функция enqueue, time() → codeweber_asset_version(), добавлен wp_unslash()

Сессия 2 (✅ выполнено 2026-03-11):
  [x] Создан functions/class-codeweber-options.php (Codeweber_Options::get/get_post_meta/is_ready)
  [x] Подключён в functions.php (перед enqueues.php)
  [x] header.php — удалены 6 debug-блоков, все Redux::get_option() заменены на Codeweber_Options
  [x] footer.php — удалены 3 debug-блока, все Redux::get_option() заменены на Codeweber_Options

Сессия 3 (✅ выполнено 2026-03-11):
  [x] post-card-templates.php — маппинг-массив + apply_filters
  [x] Добавить apply_filters в header.php (header_post_id)
  [x] Обновить устаревший комментарий в enqueues.php

Сессия 4 (✅ выполнено 2026-03-11):
  [x] images.php — заменить static cache на wp_cache_*
  [x] global.php — проверить дублирование с cleanup.php (найдено: старый Redux паттерн, обновлено на Codeweber_Options)
  [x] header.php — убрать мутацию global $post
```

---

## Справка: ключевые файлы проекта

| Файл | Роль |
|------|------|
| `functions.php` | Точка входа, подключает все модули |
| `functions/enqueues.php` | Все скрипты/стили; хелпер `codeweber_asset_version()` |
| `functions/global.php` | Хелперы: social_links, адрес, кнопки, SVG |
| `functions/fetch/fetch-handler.php` | Единый AJAX-маршрутизатор (`fetch_action`) |
| `functions/fetch/getPosts.php` | AJAX-загрузка постов |
| `functions/fetch/getHotspotContent.php` | Контент для хотспотов |
| `functions/integrations/codeweber-forms/codeweber-forms-api.php` | REST API форм |
| `functions/integrations/dadata/dadata-ajax.php` | DaData AJAX |
| `functions/integrations/ajax-search-module/ajax-search.php` | AJAX-поиск |
| `functions/integrations/ajax-search-module/search-statistics.php` | Статистика поиска |
| `functions/integrations/smsru/` | SMS.ru интеграция |
| `functions/post-card-templates.php` | Система карточек постов |
| `functions/images.php` | Размеры изображений |
| `redux-framework/` | Настройки темы (ключ: `redux_demo`) |
