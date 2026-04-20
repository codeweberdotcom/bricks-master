# Media Filters — таксономия + каскадный фильтр медиатеки

Система расширяет стандартную WP Media Library тремя независимыми фильтрами и добавляет таксономию для тегирования изображений.

## Обзор

| Фильтр | Параметр в query | Независим |
|---|---|---|
| **Тег изображения** | `image_tag` (slug термина) | да |
| **Тип записи (CPT)** | `parent_post_type` (slug) | да |
| **Конкретный пост** | `parent_post_id` (int ID) | требует CPT |

Все три можно комбинировать — например «Тег `reception` ∩ Тип `projects` ∩ Пост `Офис Москва`» даст `post_parent = X AND tax_query = image_tag:reception`.

Работает во всех admin-контекстах, где открывается WP Media Library:
- `/wp-admin/upload.php?mode=grid` и `?mode=list`
- Gallery Create / Edit
- Featured Image metabox
- Любой `MediaUpload` из `@wordpress/block-editor`
- Customizer, Widgets

---

## Файлы

| Файл | Назначение |
|---|---|
| `functions/admin/image-tag-taxonomy.php` | Регистрация таксономии `image_tag` + autocomplete в Attachment Details |
| `functions/admin/media-cpt-filter.php` | PHP: списки CPT/постов/тегов, AJAX endpoint, фильтры grid/list mode |
| `functions/admin/media-cpt-filter.js` | JS: Backbone-views и monkey-patch `AttachmentsBrowser.prototype.createToolbar` |

---

## Таксономия `image_tag`

Регистрируется в `cw_register_image_tag_taxonomy()`:

```php
register_taxonomy( 'image_tag', [ 'attachment' ], [
    'public'            => true,
    'show_ui'           => true,
    'show_in_menu'      => true,
    'show_in_rest'      => true,
    'show_admin_column' => true,
    'hierarchical'      => false,   // плоские теги, не иерархия
    'rewrite'           => [ 'slug' => 'image-tag' ],
] );
```

Что появляется автоматически:
- Меню **Медиатека → Image Tags**.
- Колонка «Image Tags» в List-mode таблицы медиатеки.
- REST endpoint `/wp/v2/image_tag`.
- Архивный URL `/image-tag/<slug>/`.

### Autocomplete в Attachment Details modal

WP по умолчанию рендерит для non-hierarchical таксономий **textarea без подсказок**. Поэтому в `image-tag-taxonomy.php`:

1. Фильтр `attachment_fields_to_edit` подменяет textarea на `<input class="cw-image-tag-input">`.
2. Фильтр `attachment_fields_to_save` парсит comma-separated строку и вызывает `wp_set_object_terms()`.
3. `admin_enqueue_scripts` подключает `jquery-ui-autocomplete` + минимальный CSS (z-index 200000 — выше media modal 160000).
4. Inline JS через `$(document).on('focus.cwImageTag', '.cw-image-tag-input', init)` — делегированная инициализация, работает с динамически вставляемыми input'ами.
5. Source autocomplete → `admin-ajax.php?action=ajax-tag-search&tax=image_tag` (WP core endpoint, капабилити `assign_terms`).
6. На `select` — подстановка выбранного термина + `, ` для следующего, как у стандартного tags-box.

**Почему не `suggest`:** WP core `suggest` (jQuery plugin) помечен deprecated в WP 6+ и не всегда инициализируется. `jquery-ui-autocomplete` — надёжный, всегда в ядре.

---

## CPT whitelist

### Blacklist (служебные post_type)

`cw_media_cpt_filter_blacklist()`:

```
attachment, revision, nav_menu_item,
wp_block, wp_template, wp_template_part,
wp_navigation, wp_global_styles,
wp_font_family, wp_font_face,
header, footer, modal, html_blocks,
page-header, notifications, codeweber_form
```

### Автомат

`cw_media_cpt_filter_types()`:

1. `get_post_types( [ 'public' => true, 'show_ui' => true ], 'objects' )`.
2. Минус blacklist.
3. Сортировка по `labels->name`.
4. Кэш `wp_cache` 5 минут, ключ `cw_media_cpt_types`.

### Hooks для расширения

```php
// Добавить/убрать CPT в blacklist
add_filter( 'codeweber_media_cpt_blacklist', function ( $list ) {
    $list[] = 'my_service_cpt';
    return $list;
} );

// Полностью переопределить итоговый список
add_filter( 'codeweber_media_cpt_filter_types', function ( $types ) {
    unset( $types['legal'] );
    return $types;
} );
```

---

## Grid mode (Backbone Media Library)

### JS архитектура (media-cpt-filter.js)

**Данные из PHP:**

`wp_localize_script()` передаёт в `window.CW_MediaCptFilter`:
- `types`: `[{slug, label}, ...]`
- `tags`: `[{slug, name}, ...]`
- `ajaxUrl`, `nonce`
- `i18n`: словарь локализованных строк

**Три view:**

| View | Base | Логика |
|---|---|---|
| `CptTagFilter` | `wp.media.view.AttachmentFilters` | `image_tag` prop, populated из `cfg.tags` |
| `CptTypeFilter` | `wp.media.view.AttachmentFilters` | `parent_post_type` prop, populated из `cfg.types`. На смене сбрасывает `parent_post_id` |
| `CptPostFilter` | `wp.media.View` (собственный) | Слушает `change:parent_post_type` → AJAX `cw_media_cpt_posts` → populate. Show/hide через `setVisible`. `parent_post_id` prop |

**Monkey-patch createToolbar:**

```js
var originalCreateToolbar = wp.media.view.AttachmentsBrowser.prototype.createToolbar;
wp.media.view.AttachmentsBrowser.prototype.createToolbar = function () {
    originalCreateToolbar.apply(this, arguments);
    // ... inject trois filters into this.toolbar
};
```

**Важно:** используется `prototype`-patch, а не `OriginalBrowser.extend({...})`. Причина: подклассы (например, Gallery frame в `MediaFrame.Post`) могут держать reference на **оригинальный** базовый класс ещё до загрузки нашего скрипта. `extend` не применяется к ним. Monkey-patch прототипа действует на все существующие и будущие инстансы.

**Priority в toolbar.set():**
- `cptTagFilter`: `-77` (первый, слева)
- `cptTagFilterLabel`: `-76`
- `cptTypeFilterLabel`: `-74`
- `cptTypeFilter`: `-75`
- `cptPostFilterLabel`: `-73`
- `cptPostFilter`: `-72`

### PHP: AJAX-эндпоинт `cw_media_cpt_posts`

POST параметры: `nonce`, `post_type`.

Возвращает:
```json
{
  "items": [{ "id": 123, "title": "Проект X" }, ...],
  "truncated": true
}
```

- До 200 постов, `date DESC`, все статусы кроме `trash`.
- Whitelist: только CPT из `cw_media_cpt_filter_types()`.
- Permission: `current_user_can('upload_files')`.
- Nonce: `cw_media_cpt_posts`.
- Кэш `wp_cache` 2 минуты, ключ `cw_cpt_posts_<slug>`.

### PHP: фильтр AJAX query-attachments

`ajax_query_attachments_args` (грид-режим, AJAX):

1. Если есть `query.image_tag` — добавляет `tax_query` с `image_tag` slug.
2. Если есть `query.parent_post_id` — `$args['post_parent'] = ID` (приоритет над CPT).
3. Иначе если есть `query.parent_post_type` — `$args['post_parent__in'] = [...ids...]` (кэш 1 мин, ключ `cw_parents_<slug>`).

---

## List mode (`upload.php?mode=list`, WP_List_Table)

Backbone не работает в list-mode — свой рендер.

### PHP: `restrict_manage_posts`

Хук срабатывает в toolbar списка. Если `$post_type === 'attachment'`, рендерит три `<select>`:

| `<select name="...">` | Данные |
|---|---|
| `image_tag` | `get_terms('image_tag')`, placeholder «All image tags» |
| `parent_post_type` | `cw_media_cpt_filter_types()`, placeholder «All post types» |
| `parent_post_id` | **Только если** `parent_post_type` уже выбран в URL — `WP_Query(post_type=..., posts_per_page=200)`, placeholder «All posts» |

### PHP: `parse_query`

Срабатывает для main query на `upload.php` + `post_type=attachment`:

1. Если `$_GET['image_tag']` не пусто — добавляет `tax_query` с `image_tag` slug.
2. Если `$_GET['parent_post_id']` > 0 — `$query->set('post_parent', X)`.
3. Иначе если `$_GET['parent_post_type']` в whitelist — `$query->set('post_parent__in', [ids...])`.

### UX

Как у стандартных WP-фильтров type/month — пользователь меняет dropdown → жмёт **«Фильтровать»** → страница перезагружается с GET-параметрами.

---

## Cache bust

При `save_post` / `deleted_post`:

```php
wp_cache_delete( 'cw_parents_' . $pt, 'cw_media' );
wp_cache_delete( 'cw_cpt_posts_' . $pt, 'cw_media' );
```

Список типов (`cw_media_cpt_types`) автоматически истекает через 5 минут, bust не делается — список CPT меняется редко (только при добавлении кода).

---

## Производительность

| Место | Запрос | Кэш |
|---|---|---|
| Список CPT для фильтра | `get_post_types()` | 5 мин |
| Parent IDs для фильтра `post_parent__in` | `get_posts(post_type, fields=ids, posts_per_page=-1)` | 1 мин |
| Список постов CPT для второго dropdown | `WP_Query(posts_per_page=200)` | 2 мин |
| Список тегов | `get_terms('image_tag', hide_empty=false)` | при load script (без кэша — нерегулярно) |

**Потенциальное узкое место:** у CPT с тысячами записей `post_parent__in` может содержать тысячи ID'ов. Это ок для `WP_Query`, но медленно при большом каталоге. Оптимизация через `posts_clauses` + JOIN — возможна, не реализована, т.к. в текущем проекте CPT компактные.

---

## Проверка работы

### Grid mode
1. Медиатека (обычная или в Gallery Create).
2. В toolbar три dropdown: **«Все теги изображений»**, **«Все типы записей»**, + **«Все записи»** появляется при выборе CPT.
3. Выбор любого из трёх → список attachments фильтруется через AJAX.

### List mode
1. Медиатека → переключить в список (`?mode=list`).
2. В toolbar те же три dropdown.
3. Смена dropdown + клик **«Фильтровать»** → перезагрузка с GET-параметрами.

### Attachment Details modal
1. Кликнуть любое изображение → открывается модалка Details.
2. Поле **Image Tags** — input с autocomplete.
3. Ввод части имени → dropdown с подсказками из существующих тегов.
4. Выбор подсказки → добавляется `Имя тега, ` в поле.
5. Сохранение — через стандартный механизм WP при закрытии модалки.
