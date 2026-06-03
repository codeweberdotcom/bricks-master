# Stock Photos — поиск и импорт бесплатных изображений

Модуль интеграции с фотостоками **Unsplash**, **Pexels** и **Pixabay**. Позволяет искать бесплатные фото прямо в админке и импортировать их в медиатеку (sideload) с сохранением атрибуции автора.

**Расположение:** `functions/integrations/stock-photos/`
**Подключение:** `functions.php` → `require_once .../stock-photos/stock-photos.php`

---

## Архитектура

| Файл | Назначение |
|------|-----------|
| `stock-photos.php` | Гейт по Redux, хелперы опций/провайдеров, регистрация страницы в админ-меню, enqueue ассетов, кнопка на медиатеке |
| `inc/proxy.php` | Серверный AJAX-прокси поиска (`cw_stock_photos_search`); запросы к API провайдеров, нормализация ответа |
| `inc/import.php` | AJAX-импорт (`cw_stock_photos_import`): `media_handle_sideload` + атрибуция; вывод атрибуции в полях вложения |
| `assets/stock-photos.js` | UI поиска (`SearchUI`): вкладка медиа-модала, оверлей, инлайн на странице |
| `assets/stock-photos.css` | Стили UI и оверлея |
| `views/page.php` | Разметка отдельной страницы (Медиа → Free Photos) |

---

## Настройки (Redux → API)

Все настройки в секции `api` (`redux-framework/sample/sections/codeweber/api.php`), ключ опции `redux_demo`:

| ID | Тип | Назначение |
|----|-----|-----------|
| `stock_photos_enabled` | switch | Общий гейт модуля |
| `stock_photos_providers` | checkbox | Какие провайдеры показывать (`unsplash`/`pexels`/`pixabay`) |
| `unsplash_access_key` | password | Access Key приложения Unsplash |
| `pexels_api_key` | password | API-ключ Pexels |
| `pixabay_api_key` | password | API-ключ Pixabay |

Кнопки «Тест» для каждого ключа обрабатываются в `functions/admin/api-test.php`
(`codeweber_api_test_unsplash` / `_pexels` / `_pixabay`).

**Провайдер активен, только если он отмечен в `stock_photos_providers` И его ключ заполнен** — см. `cw_stock_photos_providers()`.

---

## Точки входа (3 surface)

1. **Вкладка «Free Photos» в медиа-модале** — `wp.media` фреймы `Post`/`Select` расширяются в JS (`registerFrameTab`). Покрывает вставку в пост, выбор миниатюры, блоки. После импорта вложение добавляется в `selection`, фрейм переключается на «Медиатеку» — далее штатная кнопка Insert/Select.
2. **Кнопка на странице «Медиатека»** (`upload.php`) — `admin_head-upload.php` печатает `<template>`, JS вставляет кнопку `.cw-stock-open` после `.page-title-action`; открывает оверлей.
3. **Отдельная страница** — `add_submenu_page('upload.php', …, 'cw-stock-photos')`, hook `media_page_cw-stock-photos`, `SearchUI` монтируется инлайн в `#cw-stock-app`.

---

## Безопасность

- **Ключи API только server-side.** В браузер уходят лишь `slug`/`label`/`license` провайдеров. Все запросы к стокам — через `wp_remote_get` в прокси.
- **Nonce** `cw_stock_photos` + проверка `current_user_can('upload_files')` на обоих AJAX-экшенах.
- **Anti-SSRF при импорте:** хост URL загрузки сверяется с белым списком CDN провайдера (`cw_stock_photos_allowed_hosts()`). Скачивание с произвольного домена запрещено.

---

## Атрибуция

При импорте в post meta вложения пишутся:

| Meta | Значение |
|------|----------|
| `_cw_stock_provider` | `unsplash` / `pexels` / `pixabay` |
| `_cw_stock_author` | Имя автора |
| `_cw_stock_author_url` | Профиль автора |
| `_cw_stock_source_url` | Страница изображения у провайдера |
| `_wp_attachment_image_alt` | Alt-текст (из описания/тегов фото) |

Атрибуция показывается в полях «Edit Media» через фильтр `attachment_fields_to_edit`.

**Unsplash:** при импорте дёргается `download_location` (неблокирующий `wp_remote_get`) — требование [API Guidelines](https://help.unsplash.com/en/articles/2511258-guideline-triggering-a-download).

---

## Нормализованный формат элемента (proxy → JS)

```
{
  provider, id, thumb, preview, full,
  width, height, alt,
  author, author_url, source_url,
  download_location   // только Unsplash
}
```

`thumb` — превью для сетки, `full` — URL для импорта.

---

## Gotchas

- **Pixabay** при ошибке ключа отдаёт **plain text** (`[ERROR 400] ...`), а не JSON — учтено в тесте и прокси.
- **Pixabay** `full` = `largeImageURL` (макс 1280px); `fullHDURL`/`imageURL` требуют отдельного разрешения и не используются.
- **Pexels** авторизация — ключ в заголовке `Authorization` **без** префикса; **Unsplash** — `Client-ID <key>`.
- Лимиты: Unsplash demo 50 req/h (5000 после approve), Pexels 200/h · 20k/мес, Pixabay ~100/min.
- В блочном редакторе вкладка работает через стандартный `wp.media` фрейм, который использует и Gutenberg image-блок.
