# Duplicate Post — спецификация

Встроенная в тему функция «Дублировать запись» — адаптация ключевой идеи плагина
[Duplicate Post (Yoast) 4.7](https://wordpress.org/plugins/duplicate-post/), урезанная
до одного сценария (клонировать как черновик) и перенесённая в `functions/`
темы CodeWeber, без зависимости от стороннего плагина.

Референс изучен в `C:\Users\gigam\Downloads\duplicate-post.4.7\duplicate-post\`
(рабочая функция копирования — `admin-functions.php:604`,
`duplicate_post_create_duplicate()`).

**v2 этого документа** — переписана после code-review первой версии: исправлена
гарантированная бесконечная рекурсия при форке форм, добавлены откат при
ошибке, корректная capability-проверка на создание записей, реально
применяемый taxonomy-фильтр, копирование терминов по ID вместо slug,
осторожная (не всегда) пересериализация контента и уточнённая обработка
шорткода `codeweber_form_steps`. Список изменений — см. §12.

---

## 1. Цель и границы

**Делаем:** ссылка «Дублировать» в списке записей admin-панели для каждого
разрешённого типа записи. Клик создаёт черновик-копию текущей записи со всеми
метаполями и таксономиями, редиректит обратно в список с уведомлением. Если в
контенте есть ссылки на формы CodeWeber Forms — формы форкаются вместе с
записью (§6.2).

**Не делаем** (сознательно, чтобы не тащить инфраструктуру оригинального плагина):

| Фича плагина | Причина исключения |
|---|---|
| Rewrite & Republish | Отдельный сложный workflow: свой `post_status`, cron-хук `future_to_publish`, REST-хуки жизненного цикла, перезапись оригинала при публикации копии. Избыточно для простого клонирования. |
| REST API поддержка | Нет сценария дублирования через REST в этом проекте. |
| Кнопка в admin bar | Row action в списке записей достаточно. |
| Bulk actions (массовое дублирование) | Не запрошено; можно добавить позже как отдельный тикет. |
| Колонка «Original item» / чекбокс в Quick Edit | Не запрошено. |
| Метабокс на странице редактирования (ссылка на оригинал) | Не запрошено. |
| Копирование вложений (файл), комментариев, дочерних записей | Не запрошено; точка расширения — `codeweber_duplicate_post_after_duplicate`. |
| Страница настроек (что копировать, чёрные списки через UI) | Всё зафиксировано в коде констант; расширяется PHP-фильтрами, не UI. |
| Кастомная capability `copy_posts` | Используется штатные `edit_post`/`{post_type}->cap->create_posts`. |

---

## 2. Область действия (какие типы записей получают кнопку)

**Решение по итогам ревью:** динамический охват по `show_ui => true`, но с
явным denylist для типов, чьё «сырое» postmeta-копирование небезопасно или
бессмысленно. На сегодня в denylist только `attachment` и `product`
(WooCommerce — см. обоснование ниже). Фильтром можно как расширить, так и
сузить список, в т.ч. вернуть `product` — но тогда его нужно дублировать не
через общий `copy_meta`, а через WooCommerce API (`wc_get_product` + clone),
это уже отдельная задача, не покрытая этим документом.

```php
if ( ! function_exists( 'codeweber_duplicate_post_get_post_types' ) ) {
    function codeweber_duplicate_post_get_post_types(): array {
        $types = get_post_types( [ 'show_ui' => true, '_builtin' => false ], 'names' );
        $types[] = 'post';
        $types[] = 'page';

        $denylist = apply_filters( 'codeweber_duplicate_post_denylist', [
            'attachment', // медиафайл дублировать через эту ссылку не нужно
            'product',    // WooCommerce — сырое copy_meta небезопасно (SKU, lookup-таблицы, вариации); дублировать через WC API отдельно, если понадобится
        ] );
        $types = array_diff( $types, $denylist );

        return apply_filters( 'codeweber_duplicate_post_post_types', array_values( array_unique( $types ) ) );
    }
}
```

Почему `product` исключён по умолчанию: цена/SKU/остатки — это тоже postmeta,
поэтому формально скопируются, НО WooCommerce требует уникальности SKU,
поддерживает отдельные lookup-таблицы (`wp_wc_product_meta_lookup` и т.п.),
вариации со своими постами-детьми, привязанные к атрибутам — «плоское»
копирование postmeta этого не воспроизводит корректно и может испортить
консистентность каталога. Возврат через фильтр — осознанный шаг разработчика,
не default-поведение.

Почему **не** `public => true`: осознанное решение — включены и непубличные
служебные CPT (`clients`, `modal`, `html_blocks`, `notifications`,
`price_lists`, а также `mkd_document`, `cw_module` из плагинов) — у них нет
собственной публичной страницы, но редакторский смысл в клонировании
конфигурации/шаблона есть.

Список НЕ хардкодится — берётся динамически, поэтому новые CPT автоматически
получают кнопку без правок этого файла (механизм — §5).

---

## 3. Расположение и подключение

- Основной файл: `wp-content/themes/codeweber/functions/admin/duplicate-post.php`
- Подключение: безусловный `require_once` в `functions.php`, сразу после
  `functions/admin/documents-settings.php` — без обёртки в `is_admin()`: это
  фактическая конвенция файлов `functions/admin/*.php` в этой теме (проверено
  на соседних модулях при реализации) — все их хуки (`admin_action_*`,
  `admin_notices`, `post_row_actions`) физически не срабатывают вне
  wp-admin, так что обёртка избыточна.
- Второй, отдельный файл, требующий правки — **уже существующий**
  `functions/integrations/codeweber-forms/codeweber-forms-shortcode.php`
  (§6.3) — добавляется проверка статуса формы перед публичным рендером.
  Это единственная правка существующего кода за пределами нового файла.
- Namespace/префикс функций: `codeweber_duplicate_post_*` (процедурный стиль,
  как и остальной код `functions/admin/` в этой теме — без ООП/классов).
- Каждая функция обёрнута в `if ( ! function_exists( '...' ) ) { ... }` —
  защита от повторного объявления при возможном двойном подключении файла
  (например дочерней темой).
- Возвращаемые типы `int|WP_Error` не объявляются как union-тип в сигнатуре
  (минимальная заявленная версия PHP в теме — 7.4, судя по бандлу Redux
  Framework, `readme.txt: Requires PHP: 7.4`) — только через PHPDoc
  `@return int|WP_Error`. Nullable-параметры — через явный `?array`, не через
  неявный nullable по умолчанию (на PHP 8.4+ он считается deprecated).

---

## 4. Алгоритм копирования

```php
/**
 * @param WP_Post    $post
 * @param array|null $ctx  { fork_map: array<int,int>, created_ids: int[] } — общий
 *                          контекст одной операции дублирования, передаётся по
 *                          ссылке через рекурсивные вызовы (форк форм, §6.2).
 *                          При вызове "снаружи" (из обработчика, §6) не передаётся —
 *                          функция сама инициализирует пустой контекст.
 * @return int|WP_Error
 */
function codeweber_duplicate_post_create_duplicate( WP_Post $post, ?array &$ctx = null ) {
    $is_top_level = ( null === $ctx ); // зафиксировать ДО нормализации ниже — см. пункт 3
    if ( $is_top_level ) {
        $ctx = [ 'fork_map' => [], 'created_ids' => [] ];
    }
    // ...
}
```

**Порядок действий** (два прохода — это принципиально, см. §6.2 про рекурсию):

1. `apply_filters( 'codeweber_duplicate_post_allow', true, $post )` — если `false`,
   вернуть `new WP_Error( 'codeweber_duplicate_post_denied', __( 'Duplication of this item is not allowed.', 'codeweber' ) )`.
2. **Проверка права на создание** (не только на редактирование оригинала —
   см. §7): `get_post_type_object( $post->post_type )->cap->create_posts`. Нет
   объекта типа или права — `WP_Error( 'codeweber_duplicate_post_forbidden', ... )`.
3. Собрать массив для `wp_insert_post()` через
   `apply_filters( 'codeweber_duplicate_post_new_post_args', [...], $post )`:
   - `post_title` — **не копия как есть**, а
     `codeweber_duplicate_post_unique_title( $post->post_title, $post->post_type )`
     (§4.0) — гарантирует уникальность в пределах типа записи.
   - `post_content` (**сырой, ещё НЕ переписанный** — фаза 1),
     `post_content_filtered`, `post_excerpt` — как в оригинале
   - `post_status` — `'draft'` для обычной записи. **Исключение: если
     `$post->post_type === 'codeweber_form'` И это НЕ верхнеуровневый вызов**
     (`! $is_top_level` — т.е. вызов пришёл рекурсивно из
     `codeweber_duplicate_post_get_or_fork_form()`, а не напрямую из row
     action) — статус наследуется от оригинала формы (см. §6.2, обычно
     `publish`, чтобы форкнутая форма сразу работала на копии страницы).
     **Важно:** прямое дублирование самой записи `codeweber_form` из её
     собственного списка — это ВСЕГДА верхнеуровневый вызов и ВСЕГДА
     `draft`, наравне с любой другой записью — статус оригинала наследует
     ТОЛЬКО форма, форкнутая как побочный эффект дублирования чего-то
     другого. Флаг `$is_top_level` фиксируется до присвоения `$ctx`
     значения по умолчанию (шаг выше) — проверка `null !== $ctx` ПОСЛЕ
     нормализации была бы всегда истинной и не различала бы эти два случая
     (баг первой реализации, найден на дополнительном ревью).
   - `post_type`, `post_parent`, `post_password`, `comment_status`,
     `ping_status`, `menu_order` — как в оригинале
   - `post_author` — `get_current_user_id()`
   - Дата **не копируется** — WP проставит текущую
   - `post_name` (slug) **не копируется** — WP сгенерирует новый на основе
     уже уникализированного заголовка
4. `wp_insert_post( wp_slash( $new_post ), true )`. `WP_Error` — вернуть как
   есть (в контексте уже ничего не создано, откатывать нечего).
5. `$ctx['created_ids'][] = $new_id` — регистрируем созданную запись **сразу**,
   до любых дальнейших шагов, которые могут провалиться — чтобы вызывающий
   код (§6) знал, что удалять при откате.
6. **Фаза 2 — регистрация self-mapping ДО переписывания контента.** Если
   `$post->post_type === 'codeweber_form'`: `$ctx['fork_map'][$post->ID] = $new_id`
   немедленно, ещё до вызова `rewrite_form_refs`. Это и есть исправление
   бесконечной рекурсии (см. подробный разбор в §6.2) — форма, ссылающаяся
   сама на себя в своём же `post_content` (а `codeweber-forms-cpt.php:154`
   именно так и создаёт форму по умолчанию), найдёт готовое отображение в
   карте вместо повторного вызова этой же функции.
7. `codeweber_duplicate_post_rewrite_form_refs( $post->post_content, $ctx )`
   (§6.2). Если результат — `WP_Error`, вернуть его как есть (созданные к
   этому моменту записи уже в `$ctx['created_ids']`, откат — на вызывающем
   уровне). Если контент изменился — `wp_update_post( [ 'ID' => $new_id,
   'post_content' => $rewritten ] )`.
8. `codeweber_duplicate_post_copy_meta( $new_id, $post->ID, $post->post_type )` (§4.1).
9. `codeweber_duplicate_post_copy_taxonomies( $new_id, $post )` (§4.2).
10. `add_post_meta( $new_id, '_cw_duplicate_of', $post->ID )`.
11. `do_action( 'codeweber_duplicate_post_after_duplicate', $new_id, $post )`.
12. Вернуть `$new_id`.

**Точка входа для верхнеуровневого вызова.** `create_duplicate()` сама по
себе НЕ вызывается напрямую ни обработчиком (§6), ни тестами — у обеих
сторон есть общая обёртка:

```php
if ( ! function_exists( 'codeweber_duplicate_post_duplicate_top_level' ) ) {
    /**
     * @return array{0: int|WP_Error, 1: array} [ $new_id_or_error, $ctx ]
     */
    function codeweber_duplicate_post_duplicate_top_level( WP_Post $post ): array {
        $ctx    = null;
        $new_id = codeweber_duplicate_post_create_duplicate( $post, $ctx );
        return [ $new_id, $ctx ];
    }
}
```

Причина существования этой обёртки — исторический баг (§13, п. 1 и п. 4):
контракт «$ctx стартует как `null`» ранее был продублирован вручную и в
обработчике, и в тестах, и эти два места разошлись — обработчик заранее
заполнял `$ctx` непустым массивом, из-за чего `$is_top_level` внутри
`create_duplicate()` в реальности никогда не срабатывал, хотя тест (вызывавший
`create_duplicate()` напрямую с `$ctx = null`) исправно проходил. Теперь
контракт зафиксирован ровно в одном месте — `create_duplicate()` (и любая её
рекурсия, включая форк форм) вызывается напрямую ТОЛЬКО из
`codeweber_duplicate_post_get_or_fork_form()` (§6.2), а любой сторонний код —
включая тесты — обязан идти через `codeweber_duplicate_post_duplicate_top_level()`.

### 4.0 Уникальность заголовка

Заголовок копии не должен буквально совпадать с уже существующей записью того
же типа — иначе список записей после нескольких дублирований выглядит как
неразличимый набор одинаковых строк. `"Title"` → первое дублирование →
`"Title (1)"` → второе дублирование (оригинала ИЛИ уже созданной копии) →
`"Title (2)"`, и т.д.

```php
if ( ! function_exists( 'codeweber_duplicate_post_title_exists' ) ) {
    /**
     * @param int $exclude_id ID записи, которую нужно исключить из проверки.
     *                        В самом дублировании НЕ используется (у новой
     *                        записи ещё нет ID на момент генерации заголовка —
     *                        исключать нечего), параметр — задел на повторное
     *                        использование этой функции там, где такое
     *                        исключение действительно нужно (переименование
     *                        существующей записи и т.п.), по аналогии с тем,
     *                        как `wp_unique_post_slug()` в ядре WP принимает
     *                        `$post_ID` для той же цели.
     */
    function codeweber_duplicate_post_title_exists( string $title, string $post_type, int $exclude_id = 0 ): bool {
        $args = [
            'post_type'        => $post_type,
            'post_status'      => get_post_stati(), // ВСЕ зарегистрированные статусы — draft/private/trash тоже
            'title'            => $title,           // точное совпадение ($wpdb post_title = %s), а не нечёткий поиск через `s`
            'posts_per_page'   => 1,
            'fields'           => 'ids',
            'no_found_rows'    => true,
            'suppress_filters' => true,
        ];
        if ( $exclude_id > 0 ) {
            $args['post__not_in'] = [ $exclude_id ];
        }
        return (bool) get_posts( $args );
    }
}

if ( ! function_exists( 'codeweber_duplicate_post_unique_title' ) ) {
    function codeweber_duplicate_post_unique_title( string $title, string $post_type, int $exclude_id = 0 ): string {
        if ( '' === trim( $title ) ) {
            return $title;
        }

        $base = preg_replace( '/\s\(\d+\)$/', '', $title ); // срезаем только КОНЕЧНЫЙ суффикс " (N)"

        $candidate = $base;
        $suffix    = 1;
        while ( codeweber_duplicate_post_title_exists( $candidate, $post_type, $exclude_id ) ) {
            $candidate = $base . ' (' . $suffix . ')';
            $suffix++;
        }

        return $candidate;
    }
}
```

**Почему source-запись НЕ исключается из проверки занятости** (было
специально проверено при ревью — исключение source по умолчанию сломало бы
базовый сценарий): дублируемая запись `$post` уже занимает свой собственный
заголовок в БД. Если бы мы её исключали, первое дублирование `"Title"`
находило бы базовый заголовок свободным (сам оригинал не в счёт) и создавало
бы копию, буквально дублирующую оригинал по имени — ровно то, что нужно
избежать. Оригинал (и любая другая уже существующая запись, включая ту, из
которой сейчас делается копия) должны считаться "занимающими" свой заголовок
— так и первое дублирование корректно получает `"(1)"`, а не пустой суффикс.

**Разбор конечного среза суффикса** (`/\s\(\d+\)$/`): если дублируется уже
пронумерованная копия (например `"Title (2)"` напрямую, без промежуточного
дублирования оригинала) — базой всё равно становится `"Title"`, а не
`"Title (2)"`. Дальше перебор идёт по всем занятым слотам подряд (`"Title"`,
`"(1)"`, `"(2)"` — включая саму запись `"Title (2)"`, которая сейчас
дублируется, и которая тоже "занимает" этот слот) до первого свободного —
результат не зависит от того, какая именно из существующих копий была
источником дублирования, номер всегда продолжает общую последовательность.

**Область проверки** — строго в пределах `$post_type` (`get_posts(['post_type' => $post_type, ...])`).
Запись `"Same Name"` типа `post` никак не мешает записи `"Same Name"` типа
`page` — WP в принципе не требует уникальности заголовка между разными
типами записей, и эта функция такое ограничение тоже не вводит.

### 4.1 Копирование метаполей

```php
if ( ! function_exists( 'codeweber_duplicate_post_copy_meta' ) ) {
    function codeweber_duplicate_post_copy_meta( int $new_id, int $original_id, string $post_type ): void {
        $exclude = apply_filters( 'codeweber_duplicate_post_meta_exclude', [
            '_edit_lock',
            '_edit_last',
            '_cw_duplicate_of', // не наследуем цепочку, если оригинал сам был копией
        ], $post_type );

        $all_meta = get_post_meta( $original_id );
        $all_meta = apply_filters( 'codeweber_duplicate_post_meta', $all_meta, $original_id, $post_type );

        foreach ( $all_meta as $key => $values ) {
            if ( in_array( $key, $exclude, true ) ) {
                continue;
            }
            foreach ( $values as $value ) {
                $value = apply_filters(
                    'codeweber_duplicate_post_meta_value',
                    maybe_unserialize( $value ),
                    $key,
                    $post_type
                );
                add_post_meta( $new_id, $key, $value );
            }
        }
    }
}
```

Три уровня расширения (не только один блэклист, как в первой версии):
`codeweber_duplicate_post_meta` — весь массив целиком (можно вычистить/
преобразовать группу ключей для конкретного типа), `codeweber_duplicate_post_meta_exclude`
— блэклист ключей (теперь получает `$post_type` вторым аргументом — можно
различать по типу записи), `codeweber_duplicate_post_meta_value` — значение
одного ключа перед записью.

`_thumbnail_id` **не исключается** — копируется как обычное метаполе, файл
физически не дублируется (ссылка на тот же attachment).

### 4.2 Копирование таксономий

```php
if ( ! function_exists( 'codeweber_duplicate_post_copy_taxonomies' ) ) {
    function codeweber_duplicate_post_copy_taxonomies( int $new_id, WP_Post $post ): void {
        $taxonomies = get_object_taxonomies( $post->post_type );
        $taxonomies = apply_filters( 'codeweber_duplicate_post_taxonomies', $taxonomies, $post );

        foreach ( $taxonomies as $taxonomy ) {
            $term_ids = wp_get_object_terms( $post->ID, $taxonomy, [ 'fields' => 'ids' ] );
            if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
                continue;
            }
            wp_set_object_terms( $new_id, array_map( 'intval', $term_ids ), $taxonomy );
        }
    }
}
```

Два исправления относительно первой версии: (а) термины передаются в
`wp_set_object_terms()` по **числовому ID** (`fields => 'ids'`), а не по
slug — `slug` при коллизии/неоднозначности может создать новый термин вместо
использования существующего; (б) фильтр `codeweber_duplicate_post_taxonomies`
теперь реально применяется в коде, а не только упоминается в тексте.

---

## 5. UI: ссылка «Дублировать»

```php
if ( ! function_exists( 'codeweber_duplicate_post_add_row_action' ) ) {
    add_filter( 'post_row_actions', 'codeweber_duplicate_post_add_row_action', 10, 2 );
    add_filter( 'page_row_actions', 'codeweber_duplicate_post_add_row_action', 10, 2 );

    function codeweber_duplicate_post_add_row_action( array $actions, WP_Post $post ): array {
        if ( ! in_array( $post->post_type, codeweber_duplicate_post_get_post_types(), true ) ) {
            return $actions;
        }
        if ( ! current_user_can( 'edit_post', $post->ID ) ) {
            return $actions;
        }

        $post_type_object = get_post_type_object( $post->post_type );
        if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->create_posts ) ) {
            return $actions; // право читать/редактировать оригинал не равно праву создавать новые записи этого типа
        }

        $url = wp_nonce_url(
            add_query_arg( [
                'action' => 'codeweber_duplicate_post',
                'post'   => $post->ID,
            ], admin_url( 'admin.php' ) ),
            'codeweber_duplicate_post_' . $post->ID
        );

        $actions['codeweber_duplicate'] = sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            esc_url( $url ),
            esc_attr( sprintf(
                /* translators: %s: post title */
                __( 'Duplicate "%s"', 'codeweber' ),
                $post->post_title
            ) ),
            esc_html__( 'Duplicate', 'codeweber' )
        );

        return $actions;
    }
}
```

Дополнительной регистрации под каждый CPT не требуется: `post_row_actions` и
`page_row_actions` — это два универсальных фильтра ядра WP, которые
`WP_Posts_List_Table` применяет **для всех типов записей без исключения** —
`page_row_actions`, если тип иерархический (`hierarchical => true`), иначе
`post_row_actions`. Динамического имени фильтра на конкретный `post_type` в
ядре не существует — этих двух хуков достаточно, чтобы покрыть любое
количество текущих и будущих CPT. Это и есть механизм автоматического
подхвата новых CPT (§2 берёт список динамически на каждый запрос).

---

## 6. Обработчик

```php
if ( ! function_exists( 'codeweber_duplicate_post_handle_action' ) ) {
    add_action( 'admin_action_codeweber_duplicate_post', 'codeweber_duplicate_post_handle_action' );

    function codeweber_duplicate_post_handle_action(): void {
        $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
        check_admin_referer( 'codeweber_duplicate_post_' . $post_id );

        $post = get_post( $post_id );
        if ( ! $post || ! current_user_can( 'edit_post', $post_id ) ) {
            wp_die(
                esc_html__( 'You are not allowed to duplicate this item.', 'codeweber' ),
                esc_html__( 'Access denied', 'codeweber' ),
                [ 'response' => 403 ]
            );
        }

        // Единая точка входа для верхнеуровневого вызова — см. §4, конец.
        // Обработчик и тесты используют ТОЛЬКО эту функцию, чтобы контракт
        // "$ctx стартует как null" не мог разойтись между двумя местами
        // (как это уже случилось один раз — см. §13, п. 1 и п. 4).
        [ $new_id, $ctx ] = codeweber_duplicate_post_duplicate_top_level( $post );

        if ( is_wp_error( $new_id ) ) {
            // Откат: удаляем всё, что успели создать за эту операцию (сама
            // запись и любые форкнутые формы), не оставляем "сирот".
            foreach ( $ctx['created_ids'] as $created_id ) {
                wp_delete_post( $created_id, true );
            }
            wp_die(
                esc_html( $new_id->get_error_message() ),
                esc_html__( 'Duplication failed', 'codeweber' ),
                [ 'response' => 500, 'back_link' => true ]
            );
        }

        $redirect = add_query_arg(
            [
                'post_type'     => 'post' === $post->post_type ? false : $post->post_type,
                'cw_duplicated' => $new_id,
            ],
            admin_url( 'edit.php' )
        );
        wp_safe_redirect( $redirect );
        exit;
    }
}
```

Исправлено относительно первой версии: `wp_die( $message, 403 )` было неверно
— второй аргумент `wp_die()` это **заголовок страницы**, а не HTTP-код; код
передаётся третьим аргументом `[ 'response' => 403 ]`. Также добавлен полный
откат при ошибке (раньше ошибка форка формы просто "проглатывалась" и
основная запись всё равно считалась успешно продублированной — теперь любая
ошибка на любом уровне вложенности превращает всю операцию в неудачную, без
полу-созданных записей).

### 6.1 Admin-notice

```php
if ( ! function_exists( 'codeweber_duplicate_post_admin_notice' ) ) {
    add_action( 'admin_notices', 'codeweber_duplicate_post_admin_notice' );

    function codeweber_duplicate_post_admin_notice(): void {
        $new_id = isset( $_GET['cw_duplicated'] ) ? absint( $_GET['cw_duplicated'] ) : 0;
        if ( ! $new_id ) {
            return;
        }

        $new_post = get_post( $new_id );
        if ( ! $new_post || ! current_user_can( 'edit_post', $new_id ) ) {
            return; // не подтверждаем существование/детали чужой записи тому, у кого нет прав её видеть
        }

        printf(
            '<div class="notice notice-success is-dismissible"><p>%s %s</p></div>',
            esc_html__( 'Item duplicated. The copy was saved as a draft.', 'codeweber' ),
            sprintf(
                '<a href="%s">%s</a>',
                esc_url( get_edit_post_link( $new_id, 'raw' ) ),
                esc_html__( 'Edit the duplicate', 'codeweber' )
            )
        );

        // "is-dismissible" (крестик) только визуально прячет DOM-узел на текущем
        // просмотре — URL не меняет. Пока ?cw_duplicated=N сидит в адресной
        // строке, ссылки списка записей (row actions, bulk-action редиректы),
        // построенные на основе ТЕКУЩЕГО URL, продолжают тащить этот параметр
        // дальше на другие действия (включая удаление) — уведомление
        // всплывает снова и снова, "даже после клика на крестик". Стираем
        // параметр из адресной строки сразу после показа.
        ?>
        <script>
        ( function () {
            if ( ! window.history || ! window.history.replaceState ) {
                return;
            }
            var url = new URL( window.location.href );
            url.searchParams.delete( 'cw_duplicated' );
            window.history.replaceState( {}, document.title, url.toString() );
        }() );
        </script>
        <?php
    }
}
```

Раньше уведомление показывалось по голому флагу `?cw_duplicated=1`, который
можно было открыть вручную и получить ложное сообщение об успехе. Теперь в
параметре передаётся **ID копии**, и перед показом уведомления проверяется,
что запись существует и текущий пользователь имеет право её редактировать —
заодно это даёт прямую ссылку «Редактировать копию».

**Баг "уведомление висит даже после крестика"** (найден пользователем при
использовании): дело не в самой кнопке дизмисса (она работает штатно — это
код ядра WP, `is-dismissible` только прячет DOM-узел на клиенте), а в том,
что PHP-условие показа уведомления основано **только** на query-параметре
`?cw_duplicated=N` в URL. Пока страница с этим параметром висит в адресной
строке — а типичные ссылки списка записей в wp-admin (действия строки,
редиректы после bulk-действий) строятся через `add_query_arg()` поверх
ТЕКУЩЕГО URL — параметр реплицируется на другие действия (в т.ч. на
удаление продублированной записи), и уведомление появляется заново на
каждой такой странице, независимо от того, что пользователь его только что
"закрыл" где-то ещё. Исправление — `history.replaceState()` сразу после
рендера, чтобы параметр не попадал в URL, из которого строятся дальнейшие
ссылки.

### 6.2 Форк встроенных форм (`codeweber-blocks/form`)

Если дублируемая запись ссылается на форму CodeWeber Forms (CPT
`codeweber_form`), копия должна ссылаться на **свою собственную независимую
копию формы** — иначе оригинал и дубликат делят одну форму и её настройки.

**Где хранятся заявки:** в отдельной SQL-таблице
`wp_codeweber_forms_submissions` (`form_id` — строковая ссылка на ID поста
формы), НЕ в postmeta формы. Форк формы через общий `copy_meta` не тащит
чужую историю заявок — таблица заявок при форке не трогается вообще, у новой
формы заявок просто нет.

**Статус форкнутой формы:** наследует статус оригинальной формы (не
принудительный `draft`, как для «обычной» дублируемой записи, — см. п. 3
алгоритма в §4). Публикация записи-контейнера не должна сопровождаться
дополнительным ручным действием «опубликуй ещё и форму» — если оригинал был
`publish`, копия формы тоже `publish` сразу.

**Три места в контенте, ссылающиеся на форму, которые нужно переписать:**

1. Блок `codeweber-blocks/form` — атрибут `formId` (числовой post ID)
2. Блок `codeweber-blocks/form-selector` — тоже `formId`
3. Шорткод `[codeweber_form id="N"]` — числовой post ID (**не** путать с
   `codeweber_form_steps`, там другая семантика, см. ниже)
4. Шорткод `[codeweber_form_steps id="form-N"]` — **только** для
   автосгенерированного fast-path паттерна `id="form-{cptId}"`
   (`codeweber-forms-shortcode.php:48`, `render_steps_shortcode()`). Если
   `id` — произвольная кастомная строка блока (не `form-\d+`), она **не
   переписывается** — это осознанное ограничение (см. §11), т.к. надёжно
   сопоставить произвольный blockId с post ID без полного скана всех форм
   на сайте нельзя, а угадывать рискованно.

**Разбор бага бесконечной рекурсии первой версии документа** (найден на
ревью, подтверждён кодом): по умолчанию при создании новая запись
`codeweber_form` получает в свой `post_content` блок `codeweber-blocks/form`
с `formId`, равным **её же собственному** ID (`codeweber-forms-cpt.php:154`,
`'formId' => (string) $post_id`). Значит, форма **всегда** ссылается сама на
себя. Первая версия документа регистрировала `$fork_map[$old_id] = $new_id`
только **после** возврата из `create_duplicate()` — то есть на момент, когда
`rewrite_form_refs()` пытался обработать это самоссылающееся `formId`, записи
в карте ещё не было → повторный вызов `create_duplicate()` для той же формы →
бесконечная рекурсия. **Исправление** — регистрация в `$ctx['fork_map']`
происходит сразу после `wp_insert_post()`, до вызова `rewrite_form_refs()`
(шаг 6 в §4) — самоссылка находит готовое отображение вместо повторного
форка.

**Алгоритм:**

```php
if ( ! function_exists( 'codeweber_duplicate_post_rewrite_form_refs' ) ) {
    /**
     * @param string $content
     * @param array  $ctx
     * @return string|WP_Error
     */
    function codeweber_duplicate_post_rewrite_form_refs( string $content, array &$ctx ) {
        if ( ! has_block( 'codeweber-blocks/form', $content )
            && ! has_block( 'codeweber-blocks/form-selector', $content )
            && ! has_shortcode( $content, 'codeweber_form' )
            && ! has_shortcode( $content, 'codeweber_form_steps' )
        ) {
            return $content; // ничего форм-специфичного — контент не трогаем и не пересериализуем
        }

        $error  = null;
        $blocks = codeweber_duplicate_post_walk_blocks(
            parse_blocks( $content ),
            function ( array $block ) use ( &$ctx, &$error ) {
                if ( $error ) {
                    return $block;
                }
                if ( in_array( $block['blockName'], [ 'codeweber-blocks/form', 'codeweber-blocks/form-selector' ], true )
                    && ! empty( $block['attrs']['formId'] )
                ) {
                    $result = codeweber_duplicate_post_get_or_fork_form( (int) $block['attrs']['formId'], $ctx );
                    if ( is_wp_error( $result ) ) {
                        $error = $result;
                        return $block;
                    }
                    $block['attrs']['formId'] = (string) $result;
                }
                return $block;
            }
        );
        if ( $error ) {
            return $error;
        }
        $content = serialize_blocks( $blocks );

        // [codeweber_form id="N"] — числовой post ID
        $content = preg_replace_callback(
            '/\[codeweber_form\s+([^\]]*\bid=["\'])(\d+)(["\'][^\]]*)\]/',
            function ( array $m ) use ( &$ctx, &$error ) {
                if ( $error ) {
                    return $m[0];
                }
                $result = codeweber_duplicate_post_get_or_fork_form( (int) $m[2], $ctx );
                if ( is_wp_error( $result ) ) {
                    $error = $result;
                    return $m[0];
                }
                return '[codeweber_form ' . $m[1] . $result . $m[3] . ']';
            },
            $content
        );
        if ( $error ) {
            return $error;
        }

        // [codeweber_form_steps id="form-N"] — только fast-path паттерн
        $content = preg_replace_callback(
            '/\[codeweber_form_steps\s+([^\]]*\bid=["\'])form-(\d+)(["\'][^\]]*)\]/',
            function ( array $m ) use ( &$ctx, &$error ) {
                if ( $error ) {
                    return $m[0];
                }
                $result = codeweber_duplicate_post_get_or_fork_form( (int) $m[2], $ctx );
                if ( is_wp_error( $result ) ) {
                    $error = $result;
                    return $m[0];
                }
                return '[codeweber_form_steps ' . $m[1] . 'form-' . $result . $m[3] . ']';
            },
            $content
        );

        return $error ?: $content;
    }
}

if ( ! function_exists( 'codeweber_duplicate_post_get_or_fork_form' ) ) {
    /**
     * @return int|WP_Error
     */
    function codeweber_duplicate_post_get_or_fork_form( int $old_form_id, array &$ctx ) {
        if ( isset( $ctx['fork_map'][ $old_form_id ] ) ) {
            return $ctx['fork_map'][ $old_form_id ]; // уже форкнута (или это self-mapping) — переиспользуем
        }

        $form_post = get_post( $old_form_id );
        if ( ! $form_post || 'codeweber_form' !== $form_post->post_type ) {
            return $old_form_id; // не форма/не найдена — не трогаем, это не ошибка
        }

        $new_id = codeweber_duplicate_post_create_duplicate( $form_post, $ctx );
        if ( is_wp_error( $new_id ) ) {
            return $new_id; // пробрасываем — вызывающий код (§6) откатит всё созданное в $ctx['created_ids']
        }

        $ctx['fork_map'][ $old_form_id ] = $new_id;
        return $new_id;
    }
}
```

`codeweber_duplicate_post_walk_blocks( array $blocks, callable $callback ): array`
— рекурсивный обходчик дерева блоков: применяет `$callback` к каждому блоку,
затем рекурсивно к его `innerBlocks`, возвращает изменённое дерево. Не
пересериализует ничего сам — просто трансформация массива.

**Эффективность:** `has_block()`/`has_shortcode()` — быстрая проверка перед
тем, как вообще запускать `parse_blocks()`/`serialize_blocks()`. Если в
контенте нет ни одного упоминания форм — функция возвращает исходную строку
без изменений, **не пересериализуя** контент (в первой версии документа была
грубая проверка `strpos($content, 'codeweber')`, которая и матчила лишнее по
факту наличия любого другого блока `codeweber-blocks/*` в контенте, и всё
равно гоняла контент через `parse_blocks`/`serialize_blocks`, что могло бы
незаметно нормализовать разметку остальных, не связанных с формами блоков).

**Дедупликация:** если одна и та же форма встречается в контенте дважды
(например вверху и внизу страницы) — форкается один раз, оба вхождения
указывают на одну и ту же новую копию (за счёт `$ctx['fork_map']`).

**Судьба уже отправленных заявок:** остаются у оригинальной формы, к копии не
привязываются — таблица `wp_codeweber_forms_submissions` при форке не
затрагивается вообще.

### 6.3 Правка существующего файла: `codeweber-forms-shortcode.php`

**Это правка уже существующего файла темы, а не только нового кода** —
фиксирую отдельно, т.к. это выходит за рамки "просто новый файл".

Сейчас: (а) шорткод-рендерер `[codeweber_form id="N"]` не проверяет
`post_status` формы вообще — `get_form_config()`
(`codeweber-forms-shortcode.php:216`) при поиске по числовому ID проверяет
только `post_type === 'codeweber_form'`, статус не участвует. Это значит, что
`draft`-форма фактически уже сейчас рендерится и работает публично, если на
неё есть прямая ссылка. Раз это решение теперь принимается сознательно
(форкнутая форма может остаться `draft`, если её оригинал был `draft`),
нужно закрыть эту дыру: draft-форма должна быть видна только пользователю с
правом её редактировать, не анонимному посетителю.

**Правка (добавить приватный метод + два места вызова в
`CodeweberFormsShortcode`):**

```php
private function form_is_viewable( \WP_Post $form_post ): bool {
    if ( 'publish' === $form_post->post_status ) {
        return true;
    }
    return current_user_can( 'edit_post', $form_post->ID );
}
```

- `get_form_config( $id )` (строка ~216-227) — добавить `&& $this->form_is_viewable( $form_post )`
  в условие перед `return $this->parse_form_config( $form_post );`.
- `render_steps_shortcode()`, fast-path ветка (строка ~48-53, `preg_match('/^form-(\d+)$/', ...)`)
  — добавить ту же проверку перед `$this->find_form_block_by_id(...)`.
- Ветка "последний вариант" (полный скан всех `codeweber_form`, строка ~64-70)
  **уже безопасна** — в `get_posts()` там жёстко стоит `'post_status' => 'publish'`,
  правка не нужна.

**Явно вне скоупа этой задачи:** файл `render.php` блока `codeweber-blocks/form`
в плагине `codeweber-gutenberg-blocks` — это отдельный плагин/репозиторий, там
своя, независимая от шорткод-класса логика получения формы по ID. Он **не
правится** в рамках одобренного сейчас скоупа (только правка одного файла
темы). Если тот же самый пробел (рендер draft-формы без проверки прав) нужно
закрыть и там — это отдельная задача с отдельным согласованием, за пределами
темы CodeWeber.

---

## 7. Безопасность

- **Capability на редактирование оригинала**: `current_user_can( 'edit_post', $post->ID )`
  — и на этапе показа ссылки, и на этапе обработки.
- **Capability на создание записи нового типа**: `current_user_can( $post_type_object->cap->create_posts )`
  — отдельная проверка (§4 шаг 2, §5) — право читать/редактировать чужую
  запись не равно праву создавать новые записи этого типа.
- **Nonce**: `wp_nonce_url()` / `check_admin_referer()`, привязан к
  конкретному `$post_id`.
- **Откат при ошибке**: любая неудача на любом уровне вложенности (основная
  запись, форк формы) приводит к удалению всех уже созданных в рамках этой
  операции записей — не оставляем частично выполненное дублирование.
- **Раскрытие информации**: admin-notice после дублирования проверяет право
  пользователя на просмотр созданной записи, прежде чем показать ссылку на
  неё (§6.1).
- **Экранирование**: `esc_url()`, `esc_html()`, `esc_attr()` на всех
  выводимых значениях.
- Все строки — английский текст в `esc_html__()`/`__()` с text domain
  `codeweber`.

---

## 8. Точки расширения (фильтры/экшены)

| Хук | Тип | Назначение |
|---|---|---|
| `codeweber_duplicate_post_post_types` | filter | Финальный список типов записей с кнопкой «Дублировать» |
| `codeweber_duplicate_post_denylist` | filter | Список типов, исключённых из динамического `show_ui`-охвата (по умолчанию `attachment`, `product`) |
| `codeweber_duplicate_post_allow` | filter | `bool` — запретить дублирование конкретной записи |
| `codeweber_duplicate_post_new_post_args` | filter | Изменить массив аргументов перед `wp_insert_post()` |
| `codeweber_duplicate_post_meta` | filter | Весь массив метаполей оригинала перед копированием |
| `codeweber_duplicate_post_meta_exclude` | filter | Чёрный список ключей meta (получает `$post_type`) |
| `codeweber_duplicate_post_meta_value` | filter | Значение одного metaключа перед записью в копию |
| `codeweber_duplicate_post_taxonomies` | filter | Список таксономий для копирования конкретной записи |
| `codeweber_duplicate_post_after_duplicate` | action | `($new_id, $original_post)` — после успешного дублирования |

---

## 9. Именование — во избежание конфликта с плагином Duplicate Post

| Оригинальный плагин (`duplicate-post`) | Реализация темы (`codeweber`) |
|---|---|
| `duplicate_post_create_duplicate()` | `codeweber_duplicate_post_create_duplicate()` |
| `duplicate_post_copy_post_meta_info()` | `codeweber_duplicate_post_copy_meta()` |
| `duplicate_post_copy_post_taxonomies()` | `codeweber_duplicate_post_copy_taxonomies()` |
| `admin_action_duplicate_post_clone` (хук) | `admin_action_codeweber_duplicate_post` (хук) |
| meta `_dp_original` | meta `_cw_duplicate_of` |
| nonce `duplicate_post_clone_{id}` | nonce `codeweber_duplicate_post_{id}` |
| capability `copy_posts` | не заводится — используются штатные `edit_post`/`create_posts` |

Функции механизма форка форм (§6.2) — своих аналогов в оригинальном плагине
не имеют: `codeweber_duplicate_post_rewrite_form_refs()`,
`codeweber_duplicate_post_get_or_fork_form()`,
`codeweber_duplicate_post_walk_blocks()`. Ни одно имя функции/хука/meta-ключа
не пересекается с оригинальным плагином.

---

## 10. Известные ограничения / сознательно не обрабатываемые случаи

- **Иерархические типы с детьми**: дочерние записи НЕ дублируются рекурсивно.
  Копируется только сама запись.
- **Вложения**: не дублируются физически — только ссылка `_thumbnail_id` на
  тот же файл.
- **Комментарии**: не копируются.
- **Ссылки на другие записи внутри контента — общее правило "не трогаем",
  кроме форм** (§6.2 — единственное осознанное исключение). Любые другие
  блоки с id-подобными атрибутами (галерея на attachment ID, «похожие
  проекты» на конкретные post ID) при дублировании останутся указывать на
  исходные записи.
- **`codeweber_form_steps` с кастомным (не `form-{id}`) Block ID**: не
  переписывается при форке — см. §6.2, п. 4. Такие шорткоды после
  дублирования продолжат искать степ-навигацию у формы **оригинала**, а не
  форкнутой копии. Явное, осознанное ограничение.
- **`product` (WooCommerce)**: исключён из дефолтного охвата (§2). Возврат
  через фильтр `codeweber_duplicate_post_denylist` возможен, но тогда нужна
  отдельная реализация дублирования через WooCommerce API — простое
  postmeta-копирование не покрывает SKU-уникальность, lookup-таблицы,
  вариации.
- **Мультисайт**: не тестировалось, специальной обработки нет.
- **`render.php` блока `codeweber-blocks/form` в плагине `codeweber-gutenberg-blocks`**:
  не проверяет статус формы (в отличие от исправленного в этой задаче
  шорткод-рендерера, §6.3) — вне скоупа этой задачи.

---

## 11. План тестирования (вручную, после реализации)

1. `post`/`page` — дублировать, проверить статус `draft`, автор, метаполя, таксономии.
2. Один CPT из темы (например `events`) — проверить, что все специфичные
   метаполя скопированы.
3. `mkd_object` (плагин) — проверить, что все `_mkd_*` метаполя и таксономия
   `mkd_object_status` скопированы.
4. Непубличный тип (`clients` или `modal`) — убедиться, что ссылка «Дублировать»
   тоже появляется и работает.
5. `product` (WooCommerce) — убедиться, что ссылки «Дублировать» **нет**
   (denylist по умолчанию).
6. Попытка дублировать без прав редактирования — `wp_die` с реальным HTTP 403
   (проверить фактический код ответа, не только текст).
7. Пользователь с `edit_post` для конкретной записи, но без
   `create_{post_type}` — ссылка не должна показываться, прямой URL должен
   быть отклонён.
8. Nonce-защита: URL дублирования одной записи с чужим nonce — отказ.
9. **Форма, ссылающаяся сама на себя** (обычный случай — любая форма из
   `codeweber_form`) — продублировать страницу, содержащую её: убедиться, что
   операция вообще завершается (не виснет / не падает по memory limit из-за
   рекурсии) и что итоговый `formId` внутри копии формы указывает на её же
   новый ID, а не на старый.
10. Страница/запись с блоком `codeweber-blocks/form {"formId": N}` —
    продублировать, проверить: (а) `formId` копии — новый, (б) новая запись
    `codeweber_form` создана с теми же полями, (в) статус форкнутой формы
    совпадает со статусом оригинала (опубликуйте оригинал-форму и продублируйте
    — копия формы тоже `publish`), (г) форма реально отображается на копии
    страницы и принимает заявки, (д) в `wp_codeweber_forms_submissions` у
    новой формы 0 заявок.
11. Страница с ДВУМЯ вхождениями одного `formId` — оба должны указывать на
    один и тот же новый form ID после дублирования.
12. `[codeweber_form id="N"]`, вписанный вручную — тоже переписывается.
13. `[codeweber_form_steps id="form-N"]` (fast-path) — переписывается на
    `form-{новый_id}`; с кастомным нечисловым blockId — сознательно
    остаётся без изменений (см. §10).
14. **Откат при ошибке**: искусственно спровоцировать ошибку форка формы
    (например через `codeweber_duplicate_post_allow` вернуть `false` для
    формы) — убедиться, что после неудачи в списке записей нет ни основной
    полу-созданной копии, ни "осиротевшей" формы, показана ошибка, а не
    success-уведомление.
15. Draft-форма — открыть её `[codeweber_form id="N"]` на фронтенде под
    анонимным пользователем — должно быть скрыто/не рендериться (проверка
    правки §6.3); под пользователем с `edit_post` на эту форму — должна
    отображаться (превью для редактора).
16. **Прямое дублирование ОПУБЛИКОВАННОЙ формы** из её собственного списка
    записей (не через страницу) — новая форма должна быть `draft`, как любая
    другая запись. **Тест обязан вызывать
    `codeweber_duplicate_post_duplicate_top_level( $form_post )`, а НЕ
    `create_duplicate()` напрямую с самостоятельно выставленным `$ctx = null`**
    — второе не гарантирует, что тест реально проходит тем же путём, что и
    обработчик (см. §13, п. 1 и п. 4, где именно такое расхождение скрыло
    баг при первом заходе).
17. Форк опубликованной формы через страницу (как в п. 9-10) — форма
    остаётся `publish`, а сама продублированная СТРАНИЦА — `draft` (два
    разных объекта в одной операции с разными правилами статуса).
18. Заголовок `"Title"` → продублировать дважды подряд (оригинал) →
    `"Title (1)"`, затем `"Title (2)"`.
19. Продублировать саму запись `"Title (2)"` (когда `"Title"`, `"(1)"` и
    `"(2)"` уже заняты) → результат `"Title (3)"`, а не ошибка и не
    `"Title (2) (1)"`.
20. Создать запись `"Same Name"` типа `post` и запись `"Same Name"` типа
    `page` одновременно — продублировать каждую независимо: обе копии
    становятся `"Same Name (1)"` каждая в своём типе, без взаимной коллизии.
21. Уведомление после дублирования: кликнуть по крестику ИЛИ перейти по
    ссылке «Edit the duplicate», затем вернуться в список записей (в т.ч.
    кнопкой «Назад» браузера) — уведомление не должно появляться повторно;
    URL в адресной строке после показа не должен содержать `cw_duplicated`.

---

## 12. Список исправлений после ревью (v1 → v2)

1. Двухфазное создание + раннее заполнение `$fork_map` — устраняет
   гарантированную бесконечную рекурсию при форке самоссылающейся формы (§4, §6.2).
2. Форкнутая форма наследует статус оригинала, а не всегда `draft` (§4, §6.2);
   дополнена правка `codeweber-forms-shortcode.php`, чтобы non-publish формы
   не были публично доступны (§6.3, новый раздел).
3. Исправлен `wp_die( $message, 403 )` → `wp_die( $message, $title, [ 'response' => 403 ] )` (§6).
4. Ошибка форка формы больше не проглатывается молча — пробрасывается вверх,
   вся операция откатывается (§4, §6, §6.2).
5. Добавлена проверка `{post_type}->cap->create_posts`, отдельно от `edit_post` (§4, §5, §7).
6. Область действия сужена до `show_ui=true` минус denylist (`attachment`,
   `product` по умолчанию, с фильтром) вместо безусловного охвата всех типов (§2).
7. Добавлен откат созданных записей (`$ctx['created_ids']`) при любой ошибке (§4, §6).
8. Таксономии копируются по term ID, а не slug; фильтр `codeweber_duplicate_post_taxonomies`
   реально применяется в коде (§4.2).
9. Добавлены дополнительные фильтры на копирование meta: весь массив,
   значение по ключу, exclude с учётом типа записи (§4.1, §8).
10. Пересериализация `post_content` теперь происходит только если в контенте
    реально есть формы (`has_block()`/`has_shortcode()` вместо `strpos`) и
    только для контента, который действительно изменился (§6.2).
11. Регулярки для шорткодов разделены на `codeweber_form` (числовой post ID)
    и `codeweber_form_steps` (только fast-path `form-{id}`, с явно
    документированным ограничением для кастомных blockId) (§6.2, §10).
12. Все функции обёрнуты в `function_exists()` — защита от повторного
    подключения (§3).
13. Union-тип `int|WP_Error` заменён на PHPDoc — совместимость с PHP 7.4;
    nullable-параметр контекста — явный `?array` (§3, §4).
14. Admin-notice передаёт и проверяет ID копии вместо голого флага,
    добавлена прямая ссылка «Редактировать копию» (§6.1).

---

## 13. Список изменений v2 → v3

1. **Исправлен баг статуса при прямом дублировании формы.** `null !== $ctx`
   как признак «это форк» всегда был истинным (к моменту проверки `$ctx` уже
   нормализован выше по коду) — любое прямое дублирование записи
   `codeweber_form` из её собственного списка ошибочно наследовало статус
   оригинала вместо `draft`. Заменено на явный флаг `$is_top_level`,
   зафиксированный ДО нормализации `$ctx` (§4).
   **Уточнение по факту повторного ревью:** первая правка этого пункта была
   неполной — сам обработчик (§6) заранее инициализировал `$ctx` непустым
   массивом (`[ 'fork_map' => [], 'created_ids' => [] ]`) ДО вызова
   `create_duplicate()`, поэтому `$is_top_level` в реальном клике по кнопке
   всегда оказывался `false`, несмотря на исправленную проверку внутри самой
   функции. Баг не был пойман тестами первого захода, потому что тест вызывал
   `create_duplicate()` напрямую с `$ctx = null`, что НЕ совпадало с тем, как
   его реально вызывает обработчик — два места независимо описывали один и
   тот же контракт и разошлись. Структурное исправление — см. п. 4 ниже.
2. **Уникальность заголовка** — новый §4.0,
   `codeweber_duplicate_post_unique_title()`/`codeweber_duplicate_post_title_exists()`:
   `"Title"` → `"Title (1)"` → `"Title (2)"`, проверка строго в пределах
   `post_type`, по всем статусам (включая trash), точное совпадение
   заголовка (не через `s`-поиск), срез только конечного суффикса `(N)`.
   Применяется в `post_title` до первого `wp_insert_post()` (§4, шаг 3).
3. **Admin-notice больше не «залипает» после дизмисса** — крестик
   (`is-dismissible`) прятал уведомление только визуально, а
   `?cw_duplicated=N` оставался в URL и реплицировался на другие ссылки
   списка записей (row actions, bulk-редиректы), из-за чего уведомление
   появлялось заново на следующих действиях (в т.ч. после удаления
   продублированной записи). Добавлен `history.replaceState()` сразу после
   рендера, стирающий параметр из адресной строки (§6.1).
4. **Единая точка входа для верхнеуровневого вызова** —
   `codeweber_duplicate_post_duplicate_top_level( WP_Post $post ): array`
   (новая функция, между `create_duplicate()` и `copy_meta()` в файле).
   Структурная причина появления: до этой правки «правильный» вызов
   (`$ctx` стартует как `null`) существовал в виде повторённого вручную кода
   в ДВУХ независимых местах — в обработчике (§6) и в тестах — и эти два
   места разошлись (обработчик заранее заполнял `$ctx`, тест — нет), баг
   от этого не проявлялся в тестах, но проявлялся в реальном клике. Теперь
   и обработчик, и тесты вызывают ИСКЛЮЧИТЕЛЬНО эту функцию для
   верхнеуровневого дублирования — она сама инициализирует `$ctx = null`
   и возвращает `[ $new_id_or_error, $ctx ]`; переписывать этот вызов
   где-либо ещё вручную не нужно и не следует.
