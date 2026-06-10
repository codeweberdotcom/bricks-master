# Stock Photos — поиск и импорт бесплатных изображений и видео

Модуль интеграции с фотостоками **Unsplash**, **Pexels**, **Pixabay**, агрегатором **Openverse** и **Freepik**. Позволяет искать бесплатные фото **и видео** прямо в админке и импортировать их в медиатеку (sideload) с сохранением атрибуции автора.

**Видео** доступно только у **Pexels** (`api.pexels.com/videos/search`) и **Pixabay** (`pixabay.com/api/videos/`) — тем же API-ключом, что и фото. У Unsplash и Openverse видео-API нет. Переключатель «Photos / Videos» в UI появляется, если в Redux включены оба типа медиа.

**Openverse** — без API-ключа (rate-limit), CC/Public Domain контент, превью отдаются через собственный хост `api.openverse.org` (надёжнее для РФ, чем чужие CDN). Активируется одной галочкой в `stock_photos_providers`, поле ключа не требуется.

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
| `stock_media_types` | checkbox | Типы медиа: `photo` / `video` (видео — только Pexels/Pixabay). По умолчанию оба |
| `stock_photos_providers` | checkbox | Какие провайдеры показывать (`unsplash`/`pexels`/`pixabay`/`openverse`/`freepik`) |
| `unsplash_access_key` | password | Access Key приложения Unsplash |
| `pexels_api_key` | password | API-ключ Pexels |
| `pixabay_api_key` | password | API-ключ Pixabay |
| `freepik_api_key` | password | API-ключ Freepik |

Кнопки «Тест» для каждого ключа обрабатываются в `functions/admin/api-test.php`
(`codeweber_api_test_unsplash` / `_pexels` / `_pixabay` / `_freepik`).

**Провайдер активен, если он отмечен в `stock_photos_providers` И (его ключ заполнен ИЛИ он keyless)** — см. `cw_stock_photos_providers()`. Openverse — `keyless`, ключ не нужен.

### Freepik: особенности

- Endpoint: `https://api.freepik.com/v1/resources` (Resources API v1).
- Auth: заголовок `x-freepik-api-key: {key}`.
- Только фото (`media: ['photo']`); видео-поиска нет.
- Ориентация через `filters[orientation][landscape|portrait|square]=1` — передаётся напрямую в `cw_stock_photos_fetch_freepik()`, в `cw_stock_orientation_value()` не добавлялась.
- `full` = URL из `image.source.url` (превью-размер); оригинал высокого разрешения требует платного плана.
- CDN-хосты в allowlist: `img.freepik.com`, `cdn.freepik.com`.
- Бесплатный план: ~100 запросов/день, обязательна атрибуция.
- Лицензия: `"Free to use with attribution. Link to Freepik and the author in your work."`

### Openverse: особенности

- Эндпоинт поиска: `https://api.openverse.org/v1/images/` (без ключа, rate-limit).
- `thumb` = `thumbnail` (хост `api.openverse.org` → в allowlist прокси превью).
- `full`/`source_url` указывают на **первоисточник** (Flickr и др., произвольный хост).
- Импорт Openverse валидируется через **`wp_http_validate_url()`** (WP-щит от SSRF: блок localhost/приватных IP) вместо хост-allowlist.
- В meta дополнительно полезен `license` (CC-код), приходит из API.

---

## Фильтр ориентации + мета-инфо превью

**Мета-строка превью** (в оверлее при наведении, фото и видео): `1920×1080 · Horizontal · 12.4 MB`. Ориентация вычисляется в JS из `width`/`height` (`itemMeta()`), размер — через `fmtSize()` только если `size > 0`.

**Фильтр ориентации** (кнопки All / Horizontal / Vertical над поиском):

| Провайдер | Серверный фильтр | Параметр API |
|-----------|:----------------:|--------------|
| Unsplash | ✅ | `orientation=landscape/portrait/squarish` |
| Pexels (фото и видео) | ✅ | `orientation=landscape/portrait/square` |
| Openverse | ✅ | `aspect_ratio=wide/tall/square` |
| **Pixabay** | ❌ | — (нет в API) |

- Маппинг generic→provider в `cw_stock_orientation_value()` (`proxy.php`). Generic-значения: `horizontal` / `vertical` / `square`.
- Для **Pixabay фильтр скрыт** в UI (флаг `orientation => false` в реестре провайдеров → `_renderFilters()` не рисует контрол). Провайдеры выбираются по одному через табы, поэтому контрол зависит от активного провайдера.
- `orientation` передаётся в `cw_stock_photos_ajax_search()` и далее в фетчеры поддерживающих провайдеров.

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

При импорте также автоматически создаётся запись CPT `media_license` (модуль Image Licenses):

| Поле лицензии | Значение |
|---------------|----------|
| `post_title` | `"{Provider} — {alt text}"` |
| `_license_type` | Строка лицензии провайдера |
| `_item_url` | `source_url` (страница фото у провайдера) |
| `_download_date` | Дата импорта (`Y-m-d`) |
| `licensor_author` | Таксономия-термин с именем автора |

Вложение связывается с лицензией через `_media_license_id`. Если CPT `media_license` не зарегистрирован — запись не создаётся (функция `cw_stock_photos_create_license()` в `import.php`).

| Meta | Значение |
|------|----------|
| `_cw_stock_provider` | `unsplash` / `pexels` / `pixabay` / `freepik` |
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
  provider, media_type, id, thumb, preview, full,
  width, height, size, alt,
  author, author_url, source_url,
  duration,           // только видео (секунды)
  download_location   // только Unsplash
}
```

**`size` (размер файла, байты) — не у всех:**

| Провайдер | Размер | Поле API | Примечание |
|-----------|:------:|----------|-----------|
| Unsplash | ❌ | — | API не отдаёт → `0` |
| Pexels (фото и видео) | ❌ | — | API не отдаёт → `0` |
| Pixabay фото | ⚠️ | `imageSize` | размер **оригинала**, а импортируется `largeImageURL` (≤1280) — приблизительно |
| Pixabay видео | ✅ | `videos.<size>.size` | точный размер выбранного варианта |
| Openverse | ⚠️ | `filesize` | часто `null` → `0` |

В UI размер показывается только при `size > 0`. `width`/`height` есть у всех (ориентация в превью выводится из них).

`thumb` — превью для сетки (для видео это постер-картинка), `full` — URL для импорта (для видео — mp4-файл). `media_type` = `photo` | `video`.

**Выбор качества видео:**
- **Pexels** — из `video_files` выбирается mp4 с шириной ≤ 1280 (наибольшая); если все больше — наименьшая (`cw_stock_pexels_pick_video_file()`).
- **Pixabay** — берётся `videos.medium.url` (fallback: small → large → tiny). Постер — `videos.*.thumbnail`, fallback на `i.vimeocdn.com/video/{picture_id}_295x166.jpg`.

---

## Gotchas

- **Pixabay** при ошибке ключа отдаёт **plain text** (`[ERROR 400] ...`), а не JSON — учтено в тесте и прокси.
- **Pixabay** `full` = `largeImageURL` (макс 1280px); `fullHDURL`/`imageURL` требуют отдельного разрешения и не используются.
- **Pexels** авторизация — ключ в заголовке `Authorization` **без** префикса; **Unsplash** — `Client-ID <key>`.
- Лимиты: Unsplash demo 50 req/h (5000 после approve), Pexels 200/h · 20k/мес, Pixabay ~100/min.
- В блочном редакторе вкладка работает через стандартный `wp.media` фрейм, который использует и Gutenberg image-блок.

### Видео: gotchas

- **Allowlist хостов** (`cw_stock_photos_allowed_hosts()`): для Pexels добавлен `videos.pexels.com` (mp4-файлы), для Pixabay — `i.vimeocdn.com` (постеры видео; сами mp4 на `cdn.pixabay.com`, уже был в списке).
- **MIME/расширение при импорте:** видео получает `mp4` (`import.php` ветвится по `media_type`); `media_handle_sideload` грузит `video/mp4` (стандартно разрешён для админов/редакторов).
- **Timeout скачивания** для видео поднят до 120 с (видео тяжелее картинок) — может упереться в `upload_max_filesize`/`max_execution_time` PHP при больших файлах.
- **Атрибуция видео:** в meta `_cw_stock_media_type = video`; поле атрибуции в Edit Media показывает «Photo by …» (строка общая, не критично).
- Превью видео — постер + бейдж ▶ + длительность (`m:ss`); самого видео в сетке не воспроизводится.
