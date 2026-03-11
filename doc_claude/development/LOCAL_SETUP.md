# Локальная разработка — Laragon + WP-CLI

## Требования

| Инструмент | Версия | Где взять |
|-----------|--------|----------|
| Laragon | 6.x | laragon.io |
| PHP | 8.x | входит в Laragon |
| MySQL | 8.x | входит в Laragon |
| Node.js | 18+ | nodejs.org |
| WP-CLI | 2.x | wp-cli.org |
| Git | любая | git-scm.com |

---

## Первый запуск

### 1. Laragon

1. Установить Laragon в `C:\laragon\`
2. Запустить → кнопка **Start All**
3. Убедиться, что Apache и MySQL зелёные

### 2. Создать WordPress

```bash
# В директории Laragon WWW
cd C:\laragon\www
mkdir codeweber2026
cd codeweber2026

# Скачать WordPress через WP-CLI
wp core download --locale=ru_RU

# Создать БД
wp db create  # или через phpMyAdmin

# Установить WordPress
wp core install \
  --url="http://codeweber2026.test" \
  --title="CodeWeber" \
  --admin_user="admin" \
  --admin_password="admin" \
  --admin_email="admin@example.com"
```

### 3. Установить тему

```bash
# Скопировать тему в wp-content/themes/
# ИЛИ клонировать репозиторий напрямую в themes/

cd wp-content/themes/codeweber
npm install
npm run build   # первая сборка dist/
```

### 4. Активировать тему

```bash
wp theme activate codeweber
```

---

## wp-config.php: ключевые настройки

Laragon автоматически создаёт `wp-config.php`. Убедиться в наличии:

```php
// Отладочный режим
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);    // Логи в wp-content/debug.log
define('WP_DEBUG_DISPLAY', false); // Не показывать ошибки на странице

// БД
define('DB_NAME', 'codeweber2026');
define('DB_USER', 'root');
define('DB_PASSWORD', '');       // Laragon — без пароля
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');

// URL — Laragon устанавливает динамически через условие
if (!defined('WP_CLI')) {
    define('WP_SITEURL', 'http://' . $_SERVER['HTTP_HOST']);
    define('WP_HOME', 'http://' . $_SERVER['HTTP_HOST']);
}
```

> Условие `if (!defined('WP_CLI'))` гарантирует правильную работу WP-CLI.

---

## WP-CLI — частые команды

```bash
# Проверить статус WordPress
wp core version
wp db check

# Управление плагинами
wp plugin list
wp plugin install contact-form-7 --activate
wp plugin activate redux-framework

# Управление темами
wp theme list
wp theme activate codeweber

# Flush rewrite rules (после добавления CPT)
wp rewrite flush --hard

# Очистить кеш
wp cache flush

# Экспорт/импорт БД
wp db export backup.sql
wp db import backup.sql

# Поиск в базе
wp search-replace 'http://old-url.test' 'http://codeweber2026.test'

# Создать тестовых пользователей
wp user create testuser test@example.com --role=editor

# Выполнить произвольный PHP (для отладки)
wp eval 'var_dump(get_option("redux_demo"));'
```

---

## Установка необходимых плагинов

Тема рекомендует плагины через TGM Plugin Activation (автоматически предлагает при активации темы). Для ручной установки:

```bash
wp plugin install redux-framework --activate
wp plugin install contact-form-7 --activate
wp plugin install flamingo --activate         # Для CF7 GDPR данных
wp plugin install woocommerce --activate      # Если нужен WooCommerce
```

---

## Режим разработки (Gulp)

```bash
cd wp-content/themes/codeweber

# Первая установка
npm install

# Разработка: компилировать при изменениях
npm start
# → gulp serve: компилирует dist/, запускает BrowserSync

# Продакшен-сборка
npm run build
# → gulp build:dist: полная сборка с минификацией
```

После `npm start` при изменении SCSS/JS файлов в `src/` — dist/ обновляется автоматически. WordPress подхватывает изменения при следующем запросе (версия ассета меняется через `filemtime` в WP_DEBUG режиме).

---

## Laragon: виртуальные хосты

Laragon автоматически создаёт виртуальный хост для каждой папки в `www/`:

- Папка `www/codeweber2026` → `http://codeweber2026.test`

Если хост не создался: Laragon → Menu → Apache → Add Virtual Host.

---

## Отладка PHP

```php
// В wp-config.php уже должно быть:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Логи смотреть в:
// wp-content/debug.log
```

Для временной отладки в шаблонах:
```php
if (current_user_can('manage_options') && defined('WP_DEBUG') && WP_DEBUG) {
    error_log(print_r($variable, true));
    // или
    var_dump($variable); exit;
}
```

---

## Отладка JavaScript

В браузере (DevTools → Console) доступны глобальные переменные:

```javascript
// REST API
wpApiSettings.root     // URL REST API
wpApiSettings.nonce    // Nonce для запросов

// AJAX (fetch-система)
fetch_vars.ajaxurl
fetch_vars.nonce

// CodeWeber Forms
codeweberForms.restUrl
codeweberForms.restNonce

// DaData (если активен WooCommerce + DaData)
codeweberDadata.ajaxUrl
codeweberDadata.dadataToken
```

---

## Типичные проблемы

| Проблема | Решение |
|---------|---------|
| `gulp` не запускается | Убедиться, что запускается из `themes/codeweber/`, PHP доступен в PATH |
| Стили не обновляются | Запустить `npm run build`, проверить наличие `dist/assets/css/style.css` |
| `Redux::get_option()` возвращает null | Redux ещё не инициализирован — вызов должен быть в хуке `init` или позже |
| CPT не отображаются | `wp rewrite flush --hard` |
| AJAX возвращает 0 | Проверить nonce, убедиться что action зарегистрирован через `wp_ajax_` |
| Ошибка `strip_tags(null)` | Уже исправлено в `functions.php` хуком `current_screen` |
| DaData не работает | Проверить `dadata_enabled` в Redux-настройках темы |
