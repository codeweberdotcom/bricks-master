# Media Tools — инструменты работы с изображениями

## Обзор

Тема управляет регистрацией размеров изображений и предоставляет UI для регенерации миниатюр и очистки потерянных вложений прямо из настроек Redux.

---

## Файлы

| Файл | Назначение |
|------|-----------|
| `functions/images.php` | Регистрация размеров, фильтрация по CPT, безопасное удаление |
| `functions/admin/media-regenerate.php` | AJAX-обработчики регенерации и удаления потерянных вложений |
| `redux-framework/sample/sections/codeweber/media.php` | Redux-секция "Медиа" с UI: прогресс-бар, таблица потерянных файлов |

---

## Размеры изображений

Регистрируются в `codeweber_image_settings()` через `add_image_size()`.

### CPT-специфичные размеры

| CPT | Размеры |
|-----|---------|
| `events` | `codeweber_event_1070-668`, `codeweber_event_400-267`, `codeweber_event_140-88`, `codeweber_event_383-250`, `codeweber_event_600-600`, `codeweber_avatar`, `thumbnail` |
| `vacancies` | `codeweber_vacancy_1070-668`, `codeweber_vacancy_383-250`, `codeweber_vacancy_400-267`, `codeweber_vacancy_600-600`, `codeweber_avatar`, `thumbnail` |
| `staff` | `codeweber_staff`, `thumbnail` |
| `projects` | `codeweber_project_900-900`, `codeweber_project_900-718`, `codeweber_project_900-800`, `codeweber_extralarge`, `thumbnail` |
| `clients` | `codeweber_clients_115-60`, `codeweber_clients_200-60`, `codeweber_clients_300-200`, `codeweber_clients_400-267`, `thumbnail` |
| `post` | `codeweber_post_960-600`, `codeweber_post_600-600`, `codeweber_post_560-350`, `codeweber_post_100-100`, `codeweber_extralarge`, `thumbnail` |

> **Важно:** `thumbnail` (150×150) присутствует во всех CPT-массивах — без него WP Admin показывает прозрачную PNG-сетку вместо превью загруженного изображения.

### Фильтрация при загрузке

`codeweber_filter_attachment_sizes_by_post_type()` (хук `wp_generate_attachment_metadata`) удаляет физические файлы размеров, не входящих в список разрешённых для данного CPT. Логика:

1. Определяет `post_parent` вложения
2. Получает `post_type` родителя
3. Вызывает `codeweber_get_allowed_image_sizes($post_type)`
4. Строит список `$protected_files` — имена файлов, используемых разрешёнными размерами
5. Удаляет лишние файлы через `codeweber_safe_file_delete()` — **только если файл не в `$protected_files`**

Если родитель не определён — фильтрация не применяется (все размеры сохраняются).

**Защита от коллизий имён файлов:** разные CPT могут регистрировать размеры с одинаковыми пикселями (например `codeweber_event_600-600` и `codeweber_post_600-600` оба генерируют `*-600x600.jpg`). Без `$protected_files` удаление «лишнего» размера уничтожало бы общий физический файл, которым пользуется разрешённый размер. Это приводило к 404 на изображениях и прозрачной PNG-сетке в WP Admin.

Полные пары коллизий в теме:

| Файл | Размеры |
| --- | --- |
| `*-600x600.jpg` | `codeweber_event_600-600` ↔ `codeweber_post_600-600` |
| `*-1070x668.jpg` | `codeweber_event_1070-668` ↔ `codeweber_vacancy_1070-668` |
| `*-383x250.jpg` | `codeweber_event_383-250` ↔ `codeweber_vacancy_383-250` |
| `*-400x267.jpg` | `codeweber_event_400-267` ↔ `codeweber_vacancy_400-267` |

---

## Инструмент регенерации миниатюр

### Расположение в UI

**Настройки темы (Redux) → Медиа → Регенерация миниатюр**

### Когда использовать

- После изменения списка `$default_sizes` в `codeweber_get_allowed_image_sizes()`
- После добавления нового размера через `add_image_size()`
- При появлении прозрачной PNG-сетки в WP Admin для существующих изображений

### AJAX-действия

#### `cw_media_regen_count`

Возвращает общее количество изображений в медиатеке.

**POST-параметры:** `nonce`

**Ответ:**
```json
{ "success": true, "data": { "total": 210 } }
```

#### `cw_media_regen_batch`

Регенерирует пакет изображений с учётом CPT-специфичных размеров. При обнаружении файлов отсутствующих на диске возвращает их в поле `lost`.

**Логика фильтрации размеров по CPT:**

Для каждого вложения перед вызовом `wp_generate_attachment_metadata()`:

1. Определяется `post_parent` и его `post_type`
2. Вызывается `codeweber_get_allowed_image_sizes($post_type)` — та же функция, что используется при загрузке
3. Если список не пустой — добавляется временный фильтр `intermediate_image_sizes_advanced`
4. После генерации фильтр снимается

Вложения без родителя (`post_parent = 0`, тип `default`) генерируют все размеры без ограничений.

**POST-параметры:**

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|-------------|----------|
| `nonce` | string | — | Nonce `cw_media_regen` |
| `offset` | int | 0 | Смещение от начала списка |
| `limit` | int | 10 | Размер пакета |
| `total` | int | 0 | Общее кол-во (для определения `done`) |

**Ответ:**
```json
{
  "success": true,
  "data": {
    "next_offset": 90,
    "done": false,
    "errors": ["File not found for attachment #1919"],
    "lost": [
      {
        "attachment_id": 1919,
        "filename": "sh4.jpg",
        "parent_id": 0,
        "parent_title": "(no parent)",
        "parent_url": "",
        "edit_url": ""
      }
    ]
  }
}
```

### JS-логика

```
Нажать кнопку
  → cw_media_regen_count  → получить total
  → runBatch(0)
      → cw_media_regen_batch(offset, limit, total)
      → обновить прогресс-бар
      → накопить r.data.lost в allLost[]
      → если !done → runBatch(next_offset)
      → если done  → showStatus("Готово") + renderLostReport()
```

Таймаут AJAX: 180 секунд на пакет. Переменная `allLost` накапливается между батчами внутри IIFE.

### Таблица потерянных файлов

После завершения регенерации, если были обнаружены вложения без файлов на диске, под прогресс-баром отображается таблица:

| # | Файл       | Запись          | Действия  |
|---|------------|-----------------|-----------|
| 1 | `sh4.jpg`  | (нет родителя)  | [Удалить] |

**Потерянное вложение** — запись в БД (`wp_posts`, `post_type=attachment`), у которой:

- Файл по пути из `_wp_attached_file` не существует на диске
- `post_parent = 0` — типично для вложений, загруженных во время демо-импорта, когда механизм привязки к посту не сработал

---

## Инструмент удаления потерянных вложений

### AJAX-действие `cw_media_delete_lost`

Удаляет записи вложений из БД через `wp_delete_attachment($id, true)`.

- `true` — принудительное удаление (без корзины)
- Если файл отсутствует на диске — WordPress просто удаляет запись и все связанные мета, без ошибок
- Логирует каждое удаление в `debug.log`

**POST-параметры:**

| Параметр | Тип    | Описание                                                |
|----------|--------|---------------------------------------------------------|
| `nonce`  | string | Nonce `cw_media_regen` (единый для всех медиа-действий) |
| `ids`    | int[]  | Массив ID вложений для удаления                         |

**Ответ:**

```json
{ "success": true, "data": { "deleted": 9, "errors": [] } }
```

### UI-поведение

- **Кнопка "Удалить"** в строке — удаляет одно вложение, строка исчезает из таблицы
- **Кнопка "Удалить все потерянные из БД"** — отправляет все ID из `allLost` одним запросом
- Счётчик в заголовке таблицы обновляется после каждого удаления
- Таблица скрывается полностью когда все строки удалены

---

## Логирование

Все события пишутся через `error_log()` в `wp-content/debug.log` (требует `WP_DEBUG_LOG = true`).

```
[CW Media Regen] File not found for attachment #1919 | file: /path/to/sh4.jpg
[CW Media Regen] Error for #57: <WP_Error message> | file: /path/to/file.jpg
[CW Media Regen] Deleted orphaned attachment #1919
[CW Media Regen] Failed to delete attachment #1920
```

---

## Безопасность

- Все действия: проверка `current_user_can('manage_options')`
- Единый nonce `cw_media_regen` для всех трёх AJAX-действий
- IDs в `cw_media_delete_lost` прогоняются через `array_map('absint', ...)`
- Только `wp_ajax_*` хуки — недоступно для неавторизованных пользователей
