---
name: block
description: Создать theme-specific Gutenberg-блок в дочерней теме CodeWeber — привязан к CPT или фиче
argument-hint: "название блока и к чему привязан, например: catalog-filter (CPT catalog)"
---

Создай theme-specific Gutenberg-блок в этой дочерней теме CodeWeber: `$ARGUMENTS`

> **Используй этот скилл** только для блоков, жёстко привязанных к CPT или фиче этой темы.
> Для универсальных блоков → скилл `/block` в плагине `codeweber-gutenberg-blocks`.

> **Правило:** весь код — на **английском**. Русский — только в `languages/ru_RU.po`.

Прочитай `.claude/RULES.md`.

---

## Шаг 1: Определить параметры темы

Прочитай `./style.css`:
- `Template:` → `PARENT_SLUG`
- `Text Domain:` → `TEXT_DOMAIN`

Документация parent: `../PARENT_SLUG/doc_claude/`

---

## Шаг 2: Коммит текущего состояния

`git status` — если есть незакоммиченные изменения, коммит перед началом.

---

## Шаг 3: Уточняющие вопросы

| Вопрос | Влияет на |
|--------|-----------|
| К какому CPT / фиче привязан? | Путь: `./functions/<feature>/blocks/<name>/` |
| Нужен ли PHP-рендер (ServerSideRender)? | Dynamic → `render_callback` в PHP |
| Какие данные нужны в редакторе? | REST API endpoint |
| Нужен ли Inspector sidebar? | `InspectorControls` + `wp.components` |
| Атрибуты блока | Список полей с типами |
| Ограничить показ блока по post_type? | `unregisterBlockType` при условии |

---

## Шаг 4: План

**Блок:** `codeweber-blocks/<name>`
**Место:** `./functions/<feature>/blocks/<name>/` (дочерняя тема)
**Тип:** Dynamic (ServerSideRender) / Static

| Файл | Путь | Назначение |
|------|------|------------|
| `index.js` | `./functions/<feature>/blocks/<name>/index.js` | Регистрация блока (vanilla JS) |
| `render.php` | `./functions/<feature>/blocks/<name>/render.php` | PHP-рендер *(если dynamic)* |
| Регистрация | `./functions.php` или `./functions/<feature>/<feature>.php` | `register_block_type` + enqueue |
| REST | `./functions/<feature>/<feature>.php` | `register_rest_route` *(если нужен API)* |

**Дождись подтверждения пользователя.**

---

## Шаг 5: Реализация

### 5.1 `./functions/<feature>/blocks/<name>/index.js`

Vanilla JS (без JSX / без npm build). Используй `wp.*` глобальные объекты.
Text domain: всегда `TEXT_DOMAIN` (из `style.css`).

```js
( function () {
    'use strict';

    if ( typeof wp === 'undefined' || typeof wp.blocks === 'undefined' ) {
        return;
    }

    const { registerBlockType } = wp.blocks;
    const { InspectorControls, useBlockProps } = wp.blockEditor;
    const { PanelBody, SelectControl, Spinner } = wp.components;
    const { useEffect, useState } = wp.element;
    const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;
    const { __ } = wp.i18n;

    function Edit( { attributes, setAttributes } ) {
        const { selectedId } = attributes;
        const [ items, setItems ] = useState( [] );
        const [ loading, setLoading ] = useState( true );
        const blockProps = useBlockProps();

        useEffect( function () {
            const nonce = ( typeof wpApiSettings !== 'undefined' ) ? wpApiSettings.nonce : '';

            fetch( '/wp-json/<namespace>/v1/<endpoint>', {
                headers: { 'X-WP-Nonce': nonce },
            } )
                .then( ( r ) => r.json() )
                .then( ( data ) => {
                    if ( data.success ) setItems( data.items );
                } )
                .catch( console.error )
                .finally( () => setLoading( false ) );
        }, [] );

        const options = [
            { label: __( 'Select...', 'TEXT_DOMAIN' ), value: '' },
            ...items.map( ( item ) => ( { label: item.title, value: String( item.id ) } ) ),
        ];

        return wp.element.createElement(
            wp.element.Fragment,
            null,
            wp.element.createElement(
                InspectorControls,
                null,
                wp.element.createElement(
                    PanelBody,
                    { title: __( 'Settings', 'TEXT_DOMAIN' ), initialOpen: true },
                    loading
                        ? wp.element.createElement( Spinner )
                        : wp.element.createElement( SelectControl, {
                            label: __( 'Select Item', 'TEXT_DOMAIN' ),
                            value: selectedId || '',
                            options,
                            onChange: ( val ) => setAttributes( { selectedId: val } ),
                        } )
                )
            ),
            wp.element.createElement(
                'div',
                blockProps,
                selectedId
                    ? wp.element.createElement( ServerSideRender, {
                        block: 'codeweber-blocks/<name>',
                        attributes,
                        httpMethod: 'GET',
                    } )
                    : wp.element.createElement(
                        'p',
                        { style: { padding: '20px', color: '#666', textAlign: 'center' } },
                        __( 'Select an item from the sidebar', 'TEXT_DOMAIN' )
                    )
            )
        );
    }

    function Save() {
        return null;
    }

    registerBlockType( 'codeweber-blocks/<name>', {
        apiVersion: 2,
        title: __( '<Title>', 'TEXT_DOMAIN' ),
        icon: 'block-default',
        category: 'codeweber-gutenberg-blocks',
        description: __( '<Description>', 'TEXT_DOMAIN' ),
        supports: { html: false, customClassName: true, anchor: true },
        attributes: {
            selectedId: { type: 'string', default: '' },
        },
        edit: Edit,
        save: Save,
    } );
} )();
```

---

### 5.2 `./functions/<feature>/blocks/<name>/render.php`

```php
<?php
$selected_id = absint( $attributes['selectedId'] ?? 0 );
if ( ! $selected_id ) { return; }

$post = get_post( $selected_id );
if ( ! $post || $post->post_status !== 'publish' ) { return; }

$wrapper_attributes = get_block_wrapper_attributes();
?>
<div <?php echo $wrapper_attributes; ?>>
    <h3><?php echo esc_html( $post->post_title ); ?></h3>
    <?php echo wp_kses_post( apply_filters( 'the_content', $post->post_content ) ); ?>
</div>
```

---

### 5.3 Регистрация в `./functions.php` или `./functions/<feature>/<feature>.php`

```php
/**
 * Register <name> block (child theme)
 */
add_action( 'init', 'codeweber_register_<name>_block' );
function codeweber_register_<name>_block() {
    register_block_type( 'codeweber-blocks/<name>', [
        'render_callback' => 'codeweber_render_<name>_block',
        'attributes'      => [
            'selectedId' => [ 'type' => 'string', 'default' => '' ],
        ],
    ] );
}

function codeweber_render_<name>_block( $attributes ) {
    $selected_id = absint( $attributes['selectedId'] ?? 0 );
    if ( ! $selected_id ) { return ''; }

    ob_start();
    require get_stylesheet_directory() . '/functions/<feature>/blocks/<name>/render.php';
    return ob_get_clean();
}

/**
 * Enqueue block editor script
 */
add_action( 'enqueue_block_editor_assets', 'codeweber_enqueue_<name>_block_editor' );
function codeweber_enqueue_<name>_block_editor() {
    wp_enqueue_script(
        'codeweber-<name>-block',
        get_stylesheet_directory_uri() . '/functions/<feature>/blocks/<name>/index.js',
        [ 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n', 'wp-server-side-render' ],
        filemtime( get_stylesheet_directory() . '/functions/<feature>/blocks/<name>/index.js' ),
        true
    );

    wp_localize_script( 'codeweber-<name>-block', 'codeweberBlock<Name>Data', [
        'nonce'   => wp_create_nonce( 'wp_rest' ),
        'restUrl' => rest_url( '<namespace>/v1/' ),
    ] );
}
```

> `get_stylesheet_directory()` / `get_stylesheet_directory_uri()` — всегда дочерняя тема.

---

### 5.4 REST API (если блок загружает данные)

```php
add_action( 'rest_api_init', 'codeweber_register_<name>_rest_routes' );
function codeweber_register_<name>_rest_routes() {
    register_rest_route( '<namespace>/v1', '/items', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'codeweber_rest_get_<name>_items',
        'permission_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ] );
}

function codeweber_rest_get_<name>_items( WP_REST_Request $request ) {
    $posts = get_posts( [
        'post_type'      => '<cpt-slug>',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ] );

    $items = array_map( function ( $post ) {
        return [
            'id'    => $post->ID,
            'title' => esc_html( $post->post_title ),
        ];
    }, $posts );

    return rest_ensure_response( [ 'success' => true, 'items' => $items ] );
}
```

---

## Шаг 6: Проверка

- [ ] Блок появляется в инсертере
- [ ] InspectorControls загружают данные
- [ ] ServerSideRender показывает правильный PHP-рендер
- [ ] REST: `permission_callback` через `current_user_can('edit_posts')`
- [ ] Все выводы в PHP через `esc_html()` / `esc_url()` / `esc_attr()` / `wp_kses_post()`
- [ ] Text domain везде `TEXT_DOMAIN` (значение из `style.css`)
- [ ] `get_stylesheet_directory()` — не `get_template_directory()`

---

## Шаг 7: Переводы

Файлы: `./languages/{TEXT_DOMAIN}.pot` и `./languages/ru_RU.po`

```po
msgid "<Title>"
msgstr "<Название>"
```

```bash
wp i18n make-mo languages/ru_RU.po
```

---

## Шаг 8: Коммит

```
feat: add block <name> (<feature>)
```
