# Desktop Mode Widgets — Recent Form Submissions

Интеграция темы CodeWeber с плагином [WordPress/desktop-mode](https://github.com/WordPress/desktop-mode) (v0.8.9+). Добавляет виджет рабочего стола **«Recent Forms»**, который выводит последние отправки CodeWeber Forms в правой колонке desktop-shell админки.

Модуль — **no-op**, если плагин desktop-mode не активен.

---

## Файлы

| Файл | Назначение |
|------|-----------|
| `functions/integrations/desktop-mode-widgets/desktop-mode-widgets.php` | Регистрация виджета, скрипта, REST-эндпоинта |
| `functions/integrations/desktop-mode-widgets/recent-forms-widget.js` | mount-колбэк виджета (рендер списка) |

Подключение — `require_once` в `functions.php` (после `telegram-init.php`).

---

## Контракт desktop-mode v0.8.9

> ⚠️ В версии **0.8.9** API отличается от документации `trunk`. Здесь зафиксирован реальный контракт установленной версии.

**PHP** — `desktop_mode_register_widget( string $id, array $args )`, аргументы в **snake_case**:

| Аргумент | Тип | Примечание |
|----------|-----|-----------|
| `label` | string | Обязательно. Подпись в пикере |
| `description` | string | Подзаголовок |
| `icon` | string | Dashicons-класс |
| `script` | string | Хэндл зарегистрированного скрипта с mount-колбэком |
| `movable` / `resizable` | bool | Перетаскивание / ресайз |
| `min_width` / `min_height` / `max_width` / `max_height` | int | Ограничения размера |
| `default_width` / `default_height` | int | Стартовый размер |
| `capabilities` | string[] | ВСЕ cap должны быть у пользователя, иначе `WP_Error` |

Возвращает `true` или `WP_Error`. После успеха срабатывает action `desktop_mode_widget_registered`.

**JS** — mount-колбэк регистрируется как глобал (не `wp.desktop.registerWidget()`):

```js
window.desktopModeWidgets = window.desktopModeWidgets || {};
window.desktopModeWidgets['codeweber/recent-forms'] = function (container, ctx) {
    // ... рендер в container ...
    return function teardown() { /* очистка */ };
};
```

`ctx = { id, pluginUrl, storage }`, где `storage` — `{ get, set, remove, clear }`.

**Enqueue:** плагин сам делает `wp_enqueue_script( handle )` на `admin_enqueue_scripts` @20 — теме достаточно `wp_register_script()` с приоритетом < 20.

---

## Поток данных

1. `init` → `codeweber_dm_register_recent_forms_widget()` регистрирует виджет (только `is_admin()`, cap `manage_options`).
2. `admin_enqueue_scripts` @10 → `wp_register_script('codeweber-dm-widgets')` + `wp_localize_script('codeweberDmRecentForms', {...})` с `root`, `nonce`, `adminUrl`, `i18n`.
3. `rest_api_init` → эндпоинт `GET /wp-json/codeweber/v1/recent-form-submissions` (`permission_callback` = `manage_options`).
4. Плагин enqueue'ит скрипт @20 → JS определяет глобал → desktop-shell вызывает `mount()`.
5. `mount()` делает `fetch(root, { 'X-WP-Nonce': nonce })`, рендерит `list-group` на Bootstrap-классах.

---

## REST-эндпоинт

`GET /wp-json/codeweber/v1/recent-form-submissions?limit=7`

- `permission_callback`: `current_user_can('manage_options')`
- `limit`: 1–20 (по умолчанию 7)
- Источник: `CodeweberFormsDatabase::get_submissions(['orderby'=>'created_at','order'=>'DESC','exclude_status'=>'trash'])`

Ответ — массив объектов:

```json
{
  "id": 50,
  "formName": "Document Email",
  "formType": "document-email",
  "status": "new",
  "date": "03.05.2026 21:29",
  "preview": "user@example.com · 4142",
  "viewUrl": ".../edit.php?post_type=codeweber_form&page=codeweber&action=view&id=50"
}
```

`preview` — первые 2 непустых значения полей из `submission_data`, обрезка 80 символов.

---

## Gotchas

- **`is_admin()`-гейт.** Виджет регистрируется только в админке — в WP-CLI / на фронте `desktop_mode_desktop_widget_registry()` его не покажет. Это норма.
- **Версия API.** Код написан под v0.8.9. При обновлении плагина сверять контракт (`includes/registries/widgets.php` + глобал `window.desktopModeWidgets` в `assets/js/desktop.js`).
- **Цвета бейджей** типов форм в JS (`TYPE_COLORS`) дублируют логику типов из `CODEWEBER_FORMS.md` — при добавлении нового `form_type` обновить и здесь.

---

## Связанное

- [CODEWEBER_FORMS.md](../forms/CODEWEBER_FORMS.md) — система форм, типы, таблица submissions
- [REST_API_REFERENCE.md](../api/REST_API_REFERENCE.md) — REST-эндпоинты темы
