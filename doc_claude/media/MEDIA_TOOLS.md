# Media Tools — инструменты работы с изображениями

## Обзор

Тема управляет регистрацией размеров изображений и предоставляет UI для регенерации миниатюр и очистки потерянных вложений прямо из настроек Redux.

---

## Файлы

| Файл | Назначение |
|------|-----------|
| `functions/images.php` | Регистрация размеров, фильтрация по CPT, безопасное удаление |
| `functions/admin/media-regenerate.php` | AJAX-обработчики регенерации, удаления потерянных, получения лога |
| `redux-framework/sample/sections/codeweber/media.php` | Redux-секция "Медиа" с полным UI |

---

## Размеры изображений

Регистрируются в `codeweber_image_settings()` через `add_image_size()`.

### Глобальные размеры (Universal)

| Slug | Размер | Назначение |
|------|--------|-----------|
| `codeweber_extralarge` | 1600×1200 | Полноэкранные изображения |
| `codeweber_avatar` | 200×200 | Аватары, организаторы (1:1) |
| `codeweber_avatar_100-100` | 100×100 | Свотчи WooCommerce, виджеты (1:1) |

### CPT-специфичные размеры

| CPT | Размеры |
|-----|---------|
| `events` | `codeweber_event_1070-668`, `codeweber_event_400-267`, `codeweber_event_140-88`, `codeweber_event_383-250`, `codeweber_event_600-600`, `codeweber_avatar`, `thumbnail` |
| `vacancies` | `codeweber_vacancy_1070-668`, `codeweber_vacancy_383-250`, `codeweber_vacancy_400-267`, `codeweber_vacancy_600-600`, `codeweber_avatar`, `thumbnail` |
| `staff` | `codeweber_staff`, `thumbnail` |
| `projects` | `codeweber_project_900-900`, `codeweber_project_900-718`, `codeweber_project_900-800`, `codeweber_extralarge`, `thumbnail` |
| `clients` | `codeweber_clients_115-60`, `codeweber_clients_200-60`, `codeweber_clients_300-200`, `codeweber_clients_400-267`, `thumbnail` |
| `post` | `codeweber_post_960-600`, `codeweber_post_600-600`, `codeweber_post_560-350`, `codeweber_extralarge`, `thumbnail` |

> **Важно:** `thumbnail` (150×150) присутствует во всех CPT-массивах — без него WP Admin показывает прозрачную PNG-сетку вместо превью загруженного изображения.

### Фильтрация при загрузке

`codeweber_filter_attachment_sizes_by_post_type()` (хук `wp_generate_attachment_metadata`) удаляет физические файлы размеров, не входящих в список разрешённых для данного CPT.

**Защита от коллизий имён файлов:**

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

### Кнопки управления

| Кнопка | Поведение |
|--------|-----------|
| **Регенерировать миниатюры** | Старт с нуля — очищает лог и sessionStorage |
| **Продолжить** | Показывается при прерванном процессе — продолжает с сохранённого offset |
| **Начать сначала** | Сбрасывает sessionStorage, лог-файл и UI, стартует заново |

### Устойчивость к прерываниям (sessionStorage)

Состояние процесса сохраняется в `sessionStorage` под ключом `cw_media_regen_state`:

```json
{
  "offset": 42,
  "total": 210,
  "allLost": [...],
  "done": false
}
```

При перезагрузке страницы:
- Если `done: true` → показывает итоговую статистику и таблицу потерянных
- Если `done: false` и `offset > 0` → показывает кнопку «Продолжить» с прогрессом
- Если нет записи → чистый старт

### AJAX-действия

#### `cw_media_regen_count`

Возвращает общее количество изображений в медиатеке.

**POST:** `nonce`  
**Ответ:** `{ "success": true, "data": { "total": 210 } }`

#### `cw_media_regen_batch`

Регенерирует пакет из 3 изображений за запрос.

**POST-параметры:**

| Параметр | Тип | Описание |
|----------|-----|----------|
| `nonce` | string | Nonce `cw_media_regen` |
| `offset` | int | Смещение от начала списка |
| `limit` | int | Размер пакета (default: 3) |
| `total` | int | Общее кол-во (для определения `done`) |

**Особенности:**
- `set_time_limit(300)` — 5 минут на батч
- `wp_raise_memory_limit('image')` — поднимает лимит памяти
- `gc_collect_cycles()` после каждого изображения
- При `offset === 0` — удаляет лог-файл (сброс)
- Записывает лог в `{active_theme}/cw-regen-log.json`

**Ответ:**
```json
{
  "success": true,
  "data": {
    "next_offset": 3,
    "done": false,
    "errors": [],
    "lost": [],
    "log": [
      {
        "id": 123,
        "filename": "photo.jpg",
        "ext": "jpg",
        "parent_type": "product",
        "sizes": ["thumbnail", "woocommerce_single", "woocommerce_thumbnail"],
        "ok": true,
        "error": ""
      }
    ]
  }
}
```

#### `cw_media_regen_get_log`

Возвращает сохранённый лог из `{active_theme}/cw-regen-log.json`.

**POST:** `nonce`  
**Ответ:** `{ "success": true, "data": { "log": [...] } }`

Используется при загрузке страницы для восстановления журнала обработки.

### Журнал обработки

Файл `cw-regen-log.json` хранится в корне **активной дочерней темы** — при переключении темы каждая видит свой лог. Файл:
- Создаётся при старте регенерации
- Дополняется каждым батчем
- Удаляется при нажатии «Начать сначала» (offset=0 в новом батче)

**UI журнала** — аккордеон с inline-стилями (Bootstrap недоступен в WP Admin):
- Свёрнутая строка: иконка статуса (dashicons) + `[ext]` + имя файла + тип записи CPT + `N размеров`
- Развёрнутая: таблица всех сгенерированных размеров
- Поиск по имени файла фильтрует записи в реальном времени

### JS-логика

```
Старт
  → cw_media_regen_count → total
  → runBatch(0)
      → cw_media_regen_batch(offset=0) → очищает лог-файл на сервере
      → appendLog(r.data.log) → добавляет строки в UI
      → saveState(next_offset) → sessionStorage
      → если !done → runBatch(next)
      → если done  → onFinished() → saveState(total, done=true)

При ошибке
  → saveState(offset) → показать кнопки «Продолжить» и «Начать сначала»

При загрузке страницы
  → fetchStoredLog() → AJAX → рендер журнала
  → loadState() → восстановить UI прогресса / кнопок
```

---

## Инструмент удаления потерянных вложений

### AJAX-действие `cw_media_delete_lost`

Удаляет записи вложений из БД через `wp_delete_attachment($id, true)`.

**POST-параметры:**

| Параметр | Тип    | Описание |
|----------|--------|----------|
| `nonce`  | string | Nonce `cw_media_regen` |
| `ids`    | int[]  | Массив ID вложений для удаления |

**Ответ:** `{ "success": true, "data": { "deleted": 9, "errors": [] } }`

### UI-поведение

- **«Удалить»** в строке — удаляет одно вложение, строка исчезает
- **«Удалить все потерянные из БД»** — отправляет все ID одним запросом
- Таблица скрывается полностью когда все строки удалены

---

## Логирование

```
[CW Media Regen] File not found for attachment #1919 | file: /path/to/sh4.jpg
[CW Media Regen] Error for #57: <WP_Error message> | file: /path/to/file.jpg
[CW Media Regen] Deleted orphaned attachment #1919
[CW Media Regen] Failed to delete attachment #1920
```

---

## Безопасность

- Все действия: проверка `current_user_can('manage_options')`
- Единый nonce `cw_media_regen` для всех AJAX-действий
- IDs в `cw_media_delete_lost` прогоняются через `array_map('absint', ...)`
- Только `wp_ajax_*` хуки
