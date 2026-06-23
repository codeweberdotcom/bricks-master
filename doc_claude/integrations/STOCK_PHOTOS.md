# Stock Photos — поиск и импорт бесплатных изображений и видео

Модуль интеграции с фотостоками **Unsplash**, **Pexels**, **Pixabay** и агрегатором **Openverse**. Позволяет искать бесплатные фото **и видео** прямо в админке и импортировать их в медиатеку (sideload) с сохранением атрибуции автора.

**Видео** доступно у **Pexels** (`api.pexels.com/videos/search`), **Pixabay** (`pixabay.com/api/videos/`) и **Vecteezy** — тем же ключом, что и фото. У Unsplash и Openverse видео-API нет. Переключатель «Photos / Videos» в UI появляется, если в Redux включены оба типа медиа.

**Vecteezy** — отдельный провайдер с двухшаговым импортом и метрируемой квотой (500 скачиваний/мес). Подробности — в разделе [«Vecteezy»](#vecteezy) ниже.

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
| `stock_photos_providers` | checkbox | Какие провайдеры показывать (`unsplash`/`pexels`/`pixabay`/`openverse`) |
| `unsplash_access_key` | password | Access Key приложения Unsplash |
| `pexels_api_key` | password | API-ключ Pexels |
| `pixabay_api_key` | password | API-ключ Pixabay |
| `vecteezy_account_id` | text | Числовой Account ID Vecteezy |
| `vecteezy_secret_key` | password | Secret Key (Bearer) Vecteezy — активен только вместе с Account ID |

Кнопки «Тест» для каждого ключа обрабатываются в `functions/admin/api-test.php`
(`codeweber_api_test_unsplash` / `_pexels` / `_pixabay`).

**Провайдер активен, если он отмечен в `stock_photos_providers` И (его ключ заполнен ИЛИ он keyless)** — см. `cw_stock_photos_providers()`. Openverse — `keyless`, ключ не нужен.

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
| `_cw_stock_provider` | `unsplash` / `pexels` / `pixabay` / `openverse` |
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

### Vecteezy

Провайдер с другой архитектурой, чем Pexels/Pixabay.

- **API V2**, base `https://api.vecteezy.com`. Аккаунту выдают доступ только к V2 (V1 запрещён).
- **Авторизация:** `Authorization: Bearer <secret_key>`. Путь включает **account_id**: `GET /v2/{account_id}/resources?term=&content_type=photo|video&page=&per_page=&orientation=`.
- **`content_type` обязателен** — модуль шлёт `photo`/`video` по media_type.
- **`orientation`** нативный, значения совпадают с generic (`horizontal|vertical|square`) — маппинг не нужен.
- **Превью сетки** — `thumbnail_url` (чистый, публичный, host `api.vecteezy.com`). `preview_url` — с вотермаркой, **не используется**.
- **Размеры/размер файла:** `dimensions`=null; берём из `available_download_sizes` (id `original`) и `available_file_types[].size_in_bytes`.
- **Двухшаговый импорт:** поиск НЕ даёт прямой URL файла. При импорте `import.php` вызывает `GET /v2/{account_id}/resources/{id}/download` (Bearer) → `url` (подписанный, host `files.vecteezy.com`) + `required_attribution_url`. JS шлёт `id`, URL резолвится на сервере.
- **Квота:** каждый `/download` = 1 из 500/мес. Поиск и превью — бесплатны. В UI у Vecteezy показывается жёлтая подсказка (`quotaNote`).
- **Free-аккаунт:** ресайз запрещён (`file_size` не передаём) — качается оригинал. ⚠️ **Видео в оригинале бывает очень большим** (4K, сотни МБ) → импорт может упереться в `upload_max_filesize`/`max_execution_time`. Фото (~10 МБ) — норм.
- **Атрибуция обязательна** (`requires_attribution: true`). `required_attribution_url` приходит на хост `api.vecteezy.com` — при импорте нормализуется в `www.vecteezy.com` и идёт в `source_url` → `_item_url` лицензии.
- **Media License:** Vecteezy проходит через общий `cw_stock_photos_create_license()`. Автор не приходит из API → подставляется `author = 'Vecteezy'`, поэтому терм `licensor_author` = «Vecteezy». Лицензия привязывается к вложению через `_media_license_id`.
- **Доп. поля лицензии** (модуль `image-licenses`): `_license_provider`, `_license_resource_id` заполняются для всех провайдеров; для Vecteezy дополнительно — `_license_guid` и `_license_ai_generated`, получаемые **доп. бесплатным** вызовом `/v2/{account}/account/licenses` (поиск записи по `resource.id`). Текст лицензии/PDF Vecteezy не отдаёт — поле PDF остаётся пустым (это норма).
- **Остаток квоты — только показ, не в БД.** AJAX `cw_stock_vecteezy_quota` → `/v2/{account}/account/info` → `download.call_count`/`call_limit`. В UI у активного Vecteezy подсказка: «Import uses 1 download · Remaining: X/500». Обновляется после каждого импорта.
- **Allowlist хостов:** `api.vecteezy.com` (превью) + `files.vecteezy.com` (файл).
- **Тест ключа** (`codeweber_api_test_vecteezy`) шлёт `key`=secret + `account_id`; идёт через прокси.

**Прочие эндпоинты V2** (на будущее, на тех же Bearer-кредах): `/resources/{id}` (детальная карточка, есть `license_type`), `/resources/{id}/similar_images`, `/resources/{id}/download_info`, `/resource_feed`.

**Поведение медиа-модала:** после импорта вкладка «Free Photos» **остаётся открытой** (раньше переключалась на «Медиатеку») — можно импортировать несколько подряд; файл добавляется в библиотеку и выбор, Insert/Select работает после переключения на «Медиатеку».

### Видео: gotchas

- **Allowlist хостов** (`cw_stock_photos_allowed_hosts()`): для Pexels добавлен `videos.pexels.com` (mp4-файлы), для Pixabay — `i.vimeocdn.com` (постеры видео; сами mp4 на `cdn.pixabay.com`, уже был в списке).
- **MIME/расширение при импорте:** видео получает `mp4` (`import.php` ветвится по `media_type`); `media_handle_sideload` грузит `video/mp4` (стандартно разрешён для админов/редакторов).
- **Timeout скачивания** для видео поднят до 120 с (видео тяжелее картинок) — может упереться в `upload_max_filesize`/`max_execution_time` PHP при больших файлах.
- **Атрибуция видео:** в meta `_cw_stock_media_type = video`; поле атрибуции в Edit Media показывает «Photo by …» (строка общая, не критично).
- Превью видео — постер + бейдж ▶ + длительность (`m:ss`); самого видео в сетке не воспроизводится.
