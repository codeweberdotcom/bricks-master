# S3 Storage Module

Модуль темы, переносящий медиафайлы WordPress на **кастомный S3-совместимый сервер** (MinIO, Ceph, Garage, SeaweedFS, Beget Cloud Storage и т.д.). Не использует сторонние облака типа AWS S3 / DigitalOcean Spaces.

**Расположение:** `wp-content/themes/codeweber/functions/integrations/s3-storage/`

**Namespace:** `Codeweber\S3Storage\`
**CSS prefix:** `cws3-`
**AJAX prefix:** `cws3_`
**DB prefix:** `{wpdb_prefix}cws3_`

---

## Архитектура

### Это модуль темы, не плагин

Код изначально был standalone-плагином, потом перенесён в тему — потому что медиаоффлоад это фундаментальная часть проекта Codeweber, которую нельзя деактивировать без потери URL rewriting. Модуль живёт вместе с темой, ставится через `git pull`.

### Точка входа

`functions/integrations/s3-storage/s3-storage.php` подключается из `functions.php` темы (рядом с `modal/init.php`). Внутри — ранний гейт:

```php
add_action( 'after_setup_theme', function () {
    $enabled = defined('CWS3_FORCE_ENABLE') && CWS3_FORCE_ENABLE;
    if ( ! $enabled ) {
        $options = get_option( 'redux_demo', [] );
        $enabled = ! empty( $options['s3_storage_enabled'] );
    }
    if ( ! apply_filters( 'cws3_enabled', $enabled ) ) return;
    // ... autoload + Module::boot()
}, 20 );
```

Модуль **не грузится вообще** (классы, хуки, таблицы), если Redux-флаг `s3_storage_enabled` = OFF. Аварийный оверрайд — `define('CWS3_FORCE_ENABLE', true)` в `wp-config.php`.

### Redux toggle

**Theme Options → S3 Storage → Enable S3 Storage integration** (`redux_demo.s3_storage_enabled`, default `0`).

Секция зарегистрирована в `redux-framework/sample/sections/codeweber/s3-storage.php` + `theme-config.php`.

### AWS SDK

`aws/aws-sdk-php 3.x` установлен через `composer install` внутри модуля. **Папка `vendor/` закоммичена в git** потому что на проде composer отсутствует.

Несмотря на название «AWS SDK» — библиотека универсальная, работает с **любым S3-совместимым сервером**. Endpoint, region, credentials — всё указываются в Settings, к amazonaws.com запросы не идут.

---

## Конфигурация

### Settings → S3 Storage

| Поле | Описание |
|------|----------|
| Endpoint URL | Адрес S3-сервера (например `https://s3.ru1.storage.beget.cloud`) |
| Region | Регион (`us-east-1` дефолт для MinIO) |
| Access Key / Secret Key | Credentials |
| Bucket | Имя бакета |
| Path-style URLs | Для MinIO/Ceph обычно ON |
| Verify SSL | OFF для self-signed dev-серверов |
| Public / CDN URL | Опционально — если отдача через CDN |
| Key prefix | `{year}/{month}/` дефолт, placeholders `{year}`, `{month}`, `{day}` |
| Cache-Control max-age | Дефолт `31536000` (1 год). 0 отключает заголовок |

### Константы в `wp-config.php` (приоритет над Redux UI)

```php
define( 'CWS3_ENDPOINT',   'https://s3.example.com' );
define( 'CWS3_KEY',        '...' );
define( 'CWS3_SECRET',     '...' );
define( 'CWS3_BUCKET',     'my-bucket' );
define( 'CWS3_REGION',     'us-east-1' );
define( 'CWS3_PATH_STYLE', true );
define( 'CWS3_VERIFY_SSL', true );
define( 'CWS3_PUBLIC_URL', 'https://cdn.example.com' );
```

Если константа определена — поле в Settings UI показывается readonly.

### Test connection

Кнопка на Settings UI → headBucket + putObject + deleteObject тестового ключа. Результат в баннере под формой.

---

## Storage modes

| Режим | На фронте | Локально | S3 | Комментарий |
|-------|-----------|----------|-----|-------------|
| **Local** | локаль | есть | нет | S3 выключен для новых, но плагин активен |
| **S3** | S3 URL | удаляется | есть | Экономит диск сервера |
| **Mirror** | S3 URL | есть | есть | Страховка: если S3 упал — переключил в Local |

URL rewriting работает одинаково для S3 и Mirror — всегда S3 URL если файл offloaded. Для локального URL нужен режим Local + удаление записи из `wp_cws3_items`.

---

## Admin UX

### Меню

- **Settings → S3 Storage** — страница настроек + журнал ошибок (БД)
- **Tools → S3 Storage** — массовые операции с прогресс-баром
- **Tools → S3 Storage Logs** — viewer файловых логов

### Медиабиблиотека

**Колонка «Storage»** в list view с бейджами:
- `Local` (серый) — только локально
- `S3` (синий) — только в S3
- `Mirror` (зелёный) — локально + S3
- `Mirror (partial)` — частично в обоих

Колонка сортируемая (prefetch через `pre_get_posts` чтобы не бить БД N+1).

**Grid view** — поля `S3 URL`, `Bucket`, `Key` в модалке вложения.

**Edit Media sidebar metabox «S3 Storage»**:
- Кнопки: Offload now / Restore to local / Delete local copy / Re-sync / Verify / **Re-apply metadata**
- Показывает bucket, key, публичный URL, счётчик размеров `X / Y offloaded`

### Bulk actions в медиабиблиотеке

Dropdown в Media Library:
- Offload to S3
- Restore to local
- Delete local copies
- **Re-apply S3 metadata** — обновляет Content-Type + Cache-Control через CopyObject
- Verify in bucket

Row action «Offload» появляется на hover строки.

---

## Bulk operations (Tools → S3 Storage)

Все работают через единый `Queue\BatchRunner` (порциями по 20 через `wp_schedule_single_event`). Pause / Resume / Cancel / Dry-run для каждой.

| Операция | Что делает |
|----------|-----------|
| Offload | Заливает локальные attachments в S3, которых ещё нет в `wp_cws3_items` |
| Restore | Скачивает offloaded объекты обратно в `wp-content/uploads/` |
| Delete local | Удаляет локальные копии у файлов которые уже в S3 |
| Sync | Двусторонняя сверка — находит локальные attachments без записи в S3 и заливает |
| **Re-apply cache headers** | Обновляет `Content-Type` + `Cache-Control` на всех offloaded объектах через `CopyObject` (тело файла НЕ перезаливается) |
| Prepare for uninstall | Скачивает всё обратно в локаль и ставит флаг `cws3_safe_to_uninstall` |
| **Wipe S3** | Удаляет все offloaded объекты из бакета (red danger button, с confirm) |

### Background queue

Work выполняется в отдельных WP-Cron процессах, **не** в AJAX-хендлерах админа:

1. `ajax_start` → создаёт job в `wp_cws3_jobs` → `wp_schedule_single_event(time()+1, 'cws3_run_batch', [$job_id])` → `spawn_cron()` → возвращается мгновенно
2. WP-Cron запускает `BatchRunner::run($job_id)` в отдельном PHP-процессе с `ignore_user_abort(true)` и `session_write_close()`
3. После batch — `schedule_next()` + `spawn_cron()` → следующая порция в новом процессе
4. JS в админке только поллит `cws3_job_status` каждые 2 сек — read-only, не блокирует

**Caveat:** `spawn_cron()` делает self-HTTP на сам сайт. Если фаервол/CDN режет — нужен реальный cron:
```
*/1 * * * * wget -q -O - https://site.ru/wp-cron.php?doing_wp_cron >/dev/null 2>&1
```
+ `define('DISABLE_WP_CRON', true)` в wp-config.

---

## Services (reusable)

Слой бизнес-логики для одиночных attachments (используется из Bulk tools, AttachmentController, Metabox):

| Класс | Назначение |
|-------|-----------|
| `Services\RestoreService::restore_attachment($id)` | Скачать все offloaded файлы одного attachment'а |
| `Services\DeleteLocalService::delete_local_for_attachment($id)` | Удалить локальные у одного |
| `Services\VerifyService::verify_attachment($id)` | headObject по всем ключам, возвращает `[ok, missing, error]` |
| `Services\MetadataService::reapply_for_attachment($id)` | CopyObject с MetadataDirective=REPLACE, обновляет Content-Type + Cache-Control + ACL |
| `Services\MetadataService::resolve_mime_for_attachment($id, $key)` | Резолвит MIME: `post_mime_type` → `wp_check_filetype($ext)` → `application/octet-stream` |
| `Services\MetadataService::build_cache_control($settings)` | Возвращает `public, max-age=N, immutable` или `null` |

Основная упаковка: `Uploader::offload_attachment($id, $metadata)` — заливка одного attachment'а (используется и из `wp_generate_attachment_metadata` hook, и из bulk Offload).

### Отложенная выгрузка при регенерации миниатюр

`Uploader` слушает два хука:
- `wp_generate_attachment_metadata` → **синхронная** выгрузка (первичная загрузка файла, пользователь ждёт)
- `wp_update_attachment_metadata` → **отложенная** (через WP-Cron) если attachment уже offloaded

При regenerate thumbnails (плагин «Regenerate Thumbnails» или AJAX из медиатеки) `wp_update_attachment_metadata` срабатывает на уже offloaded вложении. Вместо синхронной блокировки планируется `wp_schedule_single_event(time()+5, 'cws3_deferred_offload', [$attachment_id])` + `spawn_cron()`. Дедупликация через `wp_next_scheduled` — если событие уже стоит в очереди, повторно не планируется.

Это устраняет двойную выгрузку при первой загрузке (раньше оба хука вызывали `offload_attachment`) и делает регенерацию миниатюр быстрой.

---

## DB tables

### `{prefix}_cws3_items`

Запись на каждый offloaded файл + его размеры.

| Колонка | Тип |
|---------|-----|
| `attachment_id` | BIGINT — FK на wp_posts |
| `source_type` | `original` / `size` / `original_image` |
| `source_id` | имя размера (`thumbnail`, `medium`, …) или имя файла для `original_image` |
| `bucket`, `object_key`, `region`, `provider` | параметры хранения |
| `file_size` | байты |
| `is_offloaded` | 1 если объект в S3 |
| `is_local` | 1 если локальный файл существует |
| `created_at`, `updated_at` | таймстемпы |

Ключ `(attachment_id, source_type, source_id)` — idempotent upsert.

### `{prefix}_cws3_errors`

Лог ошибок (дублируется в файловых логах).

### `{prefix}_cws3_jobs`

Очередь bulk-задач. Каждая запись — один job (status, total, processed, failed, batch_size, dry_run, cursor_id).

**Миграция:** `DB\Installer::maybe_install()` вызывается на `admin_init`, сравнивает `get_option('cws3_db_version')` с `Installer::DB_VERSION`, прогоняет `dbDelta` при несовпадении. Миграция v2→v3 автоматически ставит `rewrite_content=1` для существующих установок.

---

## Logging

Два канала:
1. **БД** — таблица `wp_cws3_errors` (только error/warning)
2. **Файлы** — дневная ротация в `wp-content/uploads/cws3-logs/cws3-YYYY-MM-DD.log`

Директория создаётся автоматически при первой записи через `wp_mkdir_p`. Выбрана `uploads/` (а не папка модуля) потому что веб-сервер на проде не имеет прав на запись внутрь темы — `uploads/` всегда writable.

**Уровни** (Settings → Logging):
- `off` — не пишем
- `error` — только ошибки (default)
- `info` — + успешные операции, duration
- `debug` — + все S3-запросы

**Retention:** дефолт 14 дней (настраивается 1–90).

**Маскировка секретов:** ключи `secret`, `secret_key`, `access_key`, `password`, `Authorization` в context автоматически заменяются на `***`.

**Viewer:** Tools → S3 Storage Logs → фильтр по уровню + кнопка очистки.

---

## URL rewriting

Включен только при `s3_storage_enabled=1` (Redux gate).

### Динамические API (всегда)

- `wp_get_attachment_url` → S3 URL если есть запись в `wp_cws3_items` с `is_offloaded=1`
- `wp_get_attachment_image_src` → S3 URL для нужного размера
- `wp_calculate_image_srcset` → перезаписывает все size URLs

Использует in-memory cache (runtime) чтобы не хитить БД многократно на одной странице.

### Контент (при `rewrite_content=1`, default с v0.2+)

- `the_content` — посты, страницы, блоки Gutenberg
- `widget_text_content` — классические текстовые виджеты
- `widget_block_content` — блок-виджеты
- `render_block` — блоки FSE-темы

Простая `str_replace($base_url, $target_url, $content)` покрывает:
- `<img src="">` в блоках
- `background-image: url()` в Cover / Media Text
- `<a href="">` ссылки на PDF/документы
- `data-*` атрибуты (Swiper, модалки)
- `<video>`, `<audio>` sources

Не покрывает (не критично): REST API ответы для блок-редактора, featured-image-серверный-рендер-в-эмбеде.

---

## JS локализация (i18n)

Все строки в `assets/admin.js` передаются через `wp_localize_script('cws3-admin', 'cws3', [...])` в `Settings::enqueue_admin_assets()`. Ключи объекта `cws3.i18n`:

| Ключ | Назначение |
|------|-----------|
| `testing`, `test_ok`, `test_fail` | Test connection кнопка |
| `confirm_clear` | Подтверждение очистки лога |
| `running`, `idle`, `completed`, `cancelled`, `failed`, `paused` | Статусы job'а в progress bar |
| `working`, `done`, `error` | Row/metabox actions |
| `failed_to_start` | Если `cws3_start_job` вернул ошибку |
| `confirm_wipe` | Confirm перед Wipe S3 |
| `confirm_cancel` | Confirm перед Cancel job |
| `failed_count` | «N failed» в progress text |
| `dry_run_label` | «[dry-run]» в progress text |

Для перевода — синхронизировать через Loco Translate (Theme: codeweber → ru_RU → Sync). Домен `codeweber`.

---

## Настройки бакета (Beget Cloud Storage)

**Бакет должен быть публичным** — иначе все объекты отдают 403 Forbidden, даже если при загрузке передавался `ACL: public-read`.

Beget управляет доступом на уровне бакета, не объекта:
- **Beget панель** → Cloud Storage → выбрать бакет → «Настройки доступа» → включить **«Сделать все материалы публичными»**

`ACL: public-read` в коде (Uploader, ReapplyCacheHeaders) оставляем — это не вредит и является правильной практикой для S3-совместимых серверов.

**Приватные документы** в том же бакете станут публично доступны по прямой ссылке. Для приватных файлов — отдельный закрытый бакет + PHP-прокси или presigned URLs (expires 1–604800 сек).

---

## Content-Type handling

**Важный gotcha** — S3-серверы часто **не автодетектят** Content-Type по расширению, ставят дефолт `binary/octet-stream`. Браузеры отказываются рендерить SVG с таким типом.

**Решение:**
- `Uploader::offload_attachment` — всегда передаёт `ContentType` (через `MetadataService::resolve_mime_for_attachment`)
- `Tools\ReapplyCacheHeaders` — передаёт ContentType при каждом `CopyObject` (иначе `MetadataDirective=REPLACE` сносит MIME)
- `Services\MetadataService::resolve_mime_for_attachment` — приоритеты: `post_mime_type` → `wp_check_filetype` по ext → `application/octet-stream`

**Если что-то сломалось:** Tools → S3 Storage → **Re-apply cache headers** → метаданные обновятся через CopyObject (без перезаливки тел файлов, быстро).

---

## Cache-Control

Заголовок `public, max-age=31536000, immutable` (1 год) ставится на каждый offloaded объект. Настраивается в Settings (0 — отключает). `immutable` безопасен потому что WP включает год/месяц + хэш в имя файла для субразмеров.

**Важно:** если делаешь реплей — используй **Re-apply cache headers** (через CopyObject), а не Wipe+Offload. CopyObject не перекачивает тело файла, обновляет только метаданные.

---

## Deployment workflow

Прод-сервер без composer/SSH-доступа к папке темы:

1. Локально — работа в модуле, `git commit` в submodule темы, `git push origin main`
2. Обновление submodule-указателя в parent-repo (локальный коммит, push не требуется — у parent нет origin)
3. На проде:
```bash
cd /var/www/vhosts/.../wp-content/themes/codeweber
git pull origin main
```
4. `admin_init` на следующем запросе в админку — миграция БД если DB_VERSION изменился

---

## Troubleshooting

| Симптом | Причина | Фикс |
|---------|---------|------|
| «AWS SDK is missing» в Settings | vendor/ не попал в репо | composer install локально → commit vendor → push |
| Картинки в блоках — локальные URL | `rewrite_content=0` | Settings → S3 Storage → ✓ Rewrite post content |
| SVG не рендерятся после Re-apply | Content-Type сброшен на binary/octet-stream | Re-apply cache headers (после фикса v0.3+ сохраняет MIME) |
| Картинки отдают 403 Forbidden | Бакет приватный | Beget/MinIO панель → Настройки доступа → «Сделать все материалы публичными» |
| Регенерация миниатюр очень медленная | До фикса — синхронная выгрузка на S3 при каждом thumb | После фикса — выгрузка идёт в фоне через WP-Cron |
| Логи не появляются в Tools → S3 Logs | `logs/` внутри темы не writable веб-сервером | Исправлено: логи перенесены в `uploads/cws3-logs/` |
| Админка виснет при Offload | Inline AJAX runner (до v0.2.1) | Обновить — работа делается в WP-Cron в фоне |
| WP-Cron не стартует | Фаервол режет self-HTTP от spawn_cron | Реальный cron на сервере + `DISABLE_WP_CRON=true` |
| Menu S3 Storage не появляется | Redux флаг OFF | Theme Options → S3 Storage → Enable → Save |

---

## Файловая структура

```
functions/integrations/s3-storage/
├── s3-storage.php                      Точка входа, Redux gate
├── composer.json                       AWS SDK зависимость
├── vendor/                             Закоммичен в git
│                                       (logs/ перенесены → wp-content/uploads/cws3-logs/)
├── assets/
│   ├── admin.css                       Бейджи, progress bar, danger section
│   └── admin.js                        Test connection, job polling, row actions
└── inc/
    ├── Module.php                      Singleton, регистрация всех хуков
    ├── Settings.php                    Страница Settings + AJAX test_connection/clear_errors
    ├── Client.php                      Фабрика AWS S3Client
    ├── StorageMode.php                 Local/S3/Mirror helpers
    ├── Uploader.php                    on_generate_metadata (sync) + on_update_metadata (deferred cron) + offload_attachment
    ├── UrlRewriter.php                 Все filters для URL rewriting
    ├── Deleter.php                     delete_attachment → S3 deleteObjects
    ├── Logger.php                      БД + файлы + маскировка
    ├── DB/
    │   ├── ItemsTable.php              Schema + queries; is_offloaded($id) → bool
    │   ├── ErrorsTable.php             wp_cws3_errors
    │   ├── JobsTable.php               wp_cws3_jobs
    │   └── Installer.php               Runtime DB migration
    ├── Queue/
    │   └── BatchRunner.php             Cron hook + AJAX start/status/control
    ├── Services/
    │   ├── RestoreService.php          Per-attachment restore
    │   ├── DeleteLocalService.php      Per-attachment delete local
    │   ├── VerifyService.php           headObject проверка
    │   └── MetadataService.php         MIME resolve + reapply_for_attachment (CopyObject)
    ├── Tools/
    │   ├── ToolsPage.php               Tools → S3 Storage страница
    │   ├── Offload.php                 Bulk runner: локаль → S3
    │   ├── Restore.php                 Bulk runner: S3 → локаль
    │   ├── DeleteLocal.php             Bulk runner: удалить локальные
    │   ├── Sync.php                    Bulk runner: двусторонняя сверка
    │   ├── Uninstaller.php             Prepare for uninstall
    │   ├── Wipe.php                    Удалить всё из бакета (danger)
    │   └── ReapplyCacheHeaders.php     CopyObject с ContentType + CacheControl
    ├── Admin/
    │   ├── MediaLibrary.php            Колонка Storage, grid view fields, prefetch
    │   ├── AttachmentMetabox.php       Metabox на Edit Media
    │   ├── BulkActions.php             Dropdown bulk + row actions
    │   ├── AttachmentController.php    AJAX cws3_attachment_action (per-attachment)
    │   └── LogViewer.php               Tools → S3 Storage Logs
    └── views/
        ├── settings.php                Settings page template
        ├── tools.php                   Tools page template (6 секций)
        ├── log-viewer.php              Log viewer template
        └── attachment-metabox.php      Metabox template
```
