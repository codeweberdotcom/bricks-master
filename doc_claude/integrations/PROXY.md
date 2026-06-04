# Outbound Proxy — прокси для серверных запросов

Общий модуль темы: направляет **исходящие server-side HTTP-запросы** отдельных модулей через внешний прокси (например, VPS). Нужен, когда веб-сервер не может достучаться до хоста напрямую (блокировки в РФ: Unsplash CDN, `api.telegram.org` и т.п.), а через прокси — может.

**Расположение:** `functions/integrations/proxy/proxy.php`
**Подключение:** `functions.php` → `require_once .../proxy/proxy.php` (до Telegram и Stock Photos).

---

## Принцип

```
WP-сервер → CURLOPT_PROXY (VPS) → внешний хост (Unsplash / Telegram / …)
```

Прокси применяется **только к запросам, помеченным флагом** `cw_use_proxy` в args. Остальной трафик WordPress (обновления, прочие плагины) не затрагивается. Требуется cURL-транспорт (на практике есть всегда).

---

## Настройки (Redux → секция «Proxy»)

Отдельная вкладка `proxy` (ключ опции `redux_demo`):

| ID | Тип | Назначение |
|----|-----|-----------|
| `proxy_enabled` | switch | Общий гейт прокси |
| `proxy_host` | text | IP/хост прокси |
| `proxy_port` | text | Порт (напр. 8888) |
| `proxy_type` | select | `http` / `socks5` |
| `proxy_user` | text | Логин (пусто = без авторизации) |
| `proxy_pass` | password | Пароль |
| `proxy_scope` | checkbox | **Какие модули** идут через прокси: `stock_photos`, `telegram` |
| `proxy_test_btn` | raw | Тест: пинг `api.ipify.org` через прокси → показывает внешний IP |

Тест-кнопка → AJAX `codeweber_api_test_proxy` (в `functions/admin/api-test.php`). Возвращает egress-IP — удобно убедиться, что трафик реально выходит через VPS.

---

## API модуля

| Функция | Назначение |
|---------|-----------|
| `cw_proxy_config()` | Массив `{host,port,type,auth}` или `null`, если выключено/не заполнено |
| `cw_proxy_enabled_for($module)` | Включён ли прокси И отмечен ли модуль в `proxy_scope` |
| `cw_proxy_request_args($module, $extra)` | args для `wp_remote_*` с флагом `cw_use_proxy`, если модуль в scope |
| `cw_proxy_apply_curl()` | Хук `http_api_curl`: ставит `CURLOPT_PROXY*` на помеченные запросы |

### Подключить новый модуль к прокси

1. Добавить слаг модуля в `proxy_scope` (options) в `redux-framework/sample/sections/codeweber/proxy.php`.
2. В запросах модуля обернуть args:
   ```php
   $args = cw_proxy_request_args( 'my_module', $args );
   $resp = wp_remote_get( $url, $args );
   ```
   Для `download_url()` так не получится (не принимает кастомные args) — использовать `wp_remote_get` со `'stream' => true, 'filename' => $tmp`.

---

## Потребители

| Модуль | Что идёт через прокси | Scope-слаг |
|--------|----------------------|-----------|
| **Stock Photos** | поиск, прокси превью, импорт (sideload) | `stock_photos` |
| **Telegram Bot** | `sendMessage` (отправка + тест) | `telegram` |

---

## Gotchas

- Работает **только при cURL-транспорте** (`http_api_curl`). Если на сервере cURL отключён — прокси не применится.
- `download_url()` не несёт кастомных args → Stock Photos импорт переведён на потоковый `wp_remote_get('stream')`.
- Для SOCKS5 нужен cURL с поддержкой socks5 (обычно есть).
- Прокси на VPS обязательно закрывать ACL по IP + авторизацией, иначе это открытый прокси.
- Egress-тест бьёт в `api.ipify.org` принудительно (`cw_use_proxy => true`), игнорируя scope — проверяет именно канал.
