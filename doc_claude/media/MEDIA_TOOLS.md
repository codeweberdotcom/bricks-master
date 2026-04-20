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

Регистрируются в `codeweber_image_settings()` через `add_image_size()`. **Набор универсальный** — все размеры доступны любому типу записи без CPT-привязки.

### Универсальный набор (`cw_*`)

| Slug | Размер | Crop | Назначение |
|------|--------|-----|-----------|
| `cw_square_xs` | 100×100 | ✓ | мини-квадрат (blog widget) |
| `cw_square_sm` | 200×200 | ✓ | аватар (1:1) |
| `cw_square_md` | 400×400 | ✓ | карточка staff / product grid 3-col |
| `cw_square_lg` | 600×600 | ✓ | карточка 2-col grid, vacancy square |
| `cw_square_xl` | 900×900 | ✓ | projects archive |
| `cw_landscape_xs` | 140×88 | ✓ | swiper thumbs |
| `cw_landscape_sm` | 383×250 | ✓ | sidebar image |
| `cw_landscape_md` | 560×350 | ✓ | related posts |
| `cw_landscape_lg` | 960×600 | ✓ | post single main |
| `cw_landscape_xl` | 1070×668 | ✓ | event/vacancy single main |
| `cw_landscape_hd` | 1600×900 | ✓ | projects hero |
| `cw_landscape_9x7` | 900×718 | ✓ | projects near-square landscape 9:7 |
| `cw_landscape_9x8` | 900×800 | ✓ | projects near-square landscape 9:8 |
| `cw_wide_4x3_xl` | 1600×1200 | ✓ | lightbox fullscreen (4:3) |
| `cw_wide_2k` | 2560×1440 | ✓ | projects 2K hero/lightbox |
| `cw_card_3x2` | 400×267 | ✓ | archive card 3:2 |
| `cw_portrait_2x3_sm` | 400×600 | ✓ | портрет (2:3), превью карточки |
| `cw_portrait_2x3_md` | 600×900 | ✓ | портрет (2:3), grid 2-col |
| `cw_portrait_2x3_lg` | 900×1350 | ✓ | портрет (2:3), single hero / lightbox |
| `cw_client_sm` | 115×60 | ✗ | клиент-логотип (без crop) |
| `cw_client_md` | 200×60 | ✗ | клиент-логотип (без crop) |
| `cw_client_lg` | 300×200 | ✗ | клиент-логотип (без crop) |

### Legacy-алиасы для horizons

| Slug | Размер | Зачем |
|------|--------|-------|
| `codeweber_extralarge` | 1600×1200 | используется в horizons single-шаблонах (тот же файл, что и `cw_wide_4x3_xl`) |
| `codeweber_staff` | 400×400 | используется в horizons шорткодах (тот же файл, что и `cw_square_md`) |

> **Важно:** `thumbnail` (150×150) WordPress регистрирует автоматически — без него WP Admin показывает прозрачную PNG-сетку вместо превью.

### Фильтрация при загрузке

`codeweber_filter_attachment_sizes_by_post_type()` (хук `wp_generate_attachment_metadata`) оставляет для всех CPT единый универсальный набор (`default`) и отдельный расширенный для `product` (добавляет `woocommerce_*`). Привязки размеров к конкретным CPT больше нет — любой шаблон любого CPT может использовать любой `cw_*`-размер.

**Защита от коллизий имён файлов:** алиасы `codeweber_extralarge`/`codeweber_staff` генерируют те же файлы, что и `cw_wide_4x3_xl`/`cw_square_md`. Фильтр проверяет `$protected_files` перед удалением, чтобы один slug из пары не уничтожил файл второго.

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
