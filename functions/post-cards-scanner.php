<?php
/**
 * Post Card Templates Scanner
 *
 * Автообнаружение card-шаблонов в файловой системе.
 *
 * Источники («корни») сканируются по возрастанию `priority` — последний
 * выигрывает при совпадении имени файла:
 *
 *   20 — директории, зарегистрированные плагинами
 *   80 — родительская тема (`templates/post-cards/`)
 *   90 — дочерняя тема (`templates/post-cards/`)
 *
 * То есть дочерняя тема всегда может перекрыть карточку плагина одноимённым
 * файлом, а плагин — не может перекрыть тему.
 *
 * Структура корня по умолчанию — вложенная: `<root>/<post_type>/<template>.php`.
 * Если при регистрации указан `post_type`, корень считается плоским:
 * все `*.php` внутри относятся к этому типу записи.
 *
 * Метаданные читаются из шапки файла (все поля опциональны):
 *
 *   /**
 *    * Card Template: Card 2
 *    * Description: Frosted flat card with icon and category subtitle
 *    * Post Type: cw_module
 *    * Supports: title, excerpt
 *    * Order: 20
 *    * Hidden: false
 *    *\/
 *
 * Без шапки label собирается из имени файла (`card-2` → `Card 2`).
 *
 * Как подключить свои шаблоны из плагина:
 *
 *   add_action( 'after_setup_theme', function () {
 *       if ( function_exists( 'cw_register_post_card_templates_dir' ) ) {
 *           cw_register_post_card_templates_dir( MY_PLUGIN_DIR . 'templates/post-cards/', [
 *               'text_domain' => 'my-plugin',
 *           ] );
 *       }
 *   } );
 *
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Хранилище зарегистрированных корней (по ссылке).
 *
 * @return array
 */
function &cw_post_card_template_roots_store() {
    static $roots = [];
    return $roots;
}

/**
 * Зарегистрировать директорию с card-шаблонами.
 *
 * Публичный API для плагинов и дочерних тем. Вызывать не раньше
 * `after_setup_theme` (тема к этому моменту уже загружена).
 *
 * @param string $path Абсолютный путь к директории.
 * @param array  $args {
 *     @type int    $priority    Приоритет; больше — выше в цепочке переопределения. По умолчанию 20.
 *     @type string $text_domain Текстовый домен для перевода label/description из шапки файла.
 *     @type string $post_type   Если задан — корень плоский, все шаблоны относятся к этому типу записи.
 *     @type string $dir         Имя папки шаблонов для плоского корня (по умолчанию — basename пути).
 *     @type string $label       Человекочитаемое имя источника (для отладки).
 * }
 * @return void
 */
function cw_register_post_card_templates_dir($path, $args = []) {
    if (empty($path)) {
        return;
    }

    $path  = trailingslashit(wp_normalize_path($path));
    $store = &cw_post_card_template_roots_store();

    $store[$path] = wp_parse_args($args, [
        'priority'    => 20,
        'text_domain' => '',
        'post_type'   => '',
        'dir'         => '',
        'label'       => '',
    ]);

    $store[$path]['path'] = $path;
}

/**
 * Все корни сканирования, отсортированные по возрастанию приоритета.
 *
 * @return array
 */
function cw_get_post_card_template_roots() {
    $roots = [];

    $parent = trailingslashit(wp_normalize_path(get_template_directory())) . 'templates/post-cards/';
    $roots[$parent] = [
        'path'        => $parent,
        'priority'    => 80,
        'text_domain' => 'codeweber',
        'post_type'   => '',
        'dir'         => '',
        'label'       => 'parent-theme',
    ];

    if (get_stylesheet_directory() !== get_template_directory()) {
        $child = trailingslashit(wp_normalize_path(get_stylesheet_directory())) . 'templates/post-cards/';
        $roots[$child] = [
            'path'        => $child,
            'priority'    => 90,
            'text_domain' => get_stylesheet(),
            'post_type'   => '',
            'dir'         => '',
            'label'       => 'child-theme',
        ];
    }

    foreach (cw_post_card_template_roots_store() as $path => $root) {
        $roots[$path] = $root;
    }

    $roots = apply_filters('codeweber_post_card_template_roots', $roots);

    $roots = array_filter($roots, function ($root) {
        return !empty($root['path']) && is_dir($root['path']);
    });

    uasort($roots, function ($a, $b) {
        $pa = isset($a['priority']) ? (int) $a['priority'] : 20;
        $pb = isset($b['priority']) ? (int) $b['priority'] : 20;
        return $pa <=> $pb;
    });

    return $roots;
}

/**
 * Папки, «занятые» другим типом записи.
 *
 * Если `codeweber_post_type_template_map` отправляет CPT в чужую папку
 * (`vacancies` → `post`), одноимённая папка не должна становиться
 * самостоятельным источником — иначе значения в дропдауне разойдутся с тем,
 * что реально резолвит cw_render_post_card().
 *
 * ВАЖНО: здесь нельзя обращаться к codeweber_get_post_card_templates_registry() —
 * реестр сам вызывает сканер, получится рекурсия. Фильтр применяется к базовой
 * карте, а его обработчик в реестре читает только «сырой» массив.
 *
 * @return array post_type => dir
 */
function cw_post_card_dir_claims() {
    return apply_filters('codeweber_post_type_template_map', [
        'clients'      => 'clients',
        'testimonials' => 'testimonials',
        'documents'    => 'documents',
        'faq'          => 'faq',
        'staff'        => 'staff',
        'offices'      => 'offices',
        'vacancies'    => 'post',
    ]);
}

/**
 * Собрать label из слага файла: `card-3b` → `Card 3b`.
 *
 * @param string $slug
 * @return string
 */
function cw_humanize_post_card_slug($slug) {
    return ucwords(str_replace(['-', '_'], ' ', $slug));
}

/**
 * Просканировать одну папку с шаблонами.
 *
 * @param string $dir_path          Абсолютный путь к папке (со слешем на конце).
 * @param string $default_post_type Тип записи по умолчанию для найденных файлов.
 * @param string $dir_name          Имя папки шаблонов.
 * @param array  $root              Конфигурация корня.
 * @return array Список записей.
 */
function cw_scan_post_card_dir($dir_path, $default_post_type, $dir_name, $root) {
    $entries = [];
    $files   = glob($dir_path . '*.php');

    if (empty($files)) {
        return $entries;
    }

    $domain  = !empty($root['text_domain']) ? $root['text_domain'] : 'codeweber';
    $skip    = ['helpers', 'index', 'functions'];

    foreach ($files as $file) {
        $slug = basename($file, '.php');

        if (in_array($slug, $skip, true) || strpos($slug, '_') === 0) {
            continue;
        }

        $data = get_file_data($file, [
            'name'        => 'Card Template',
            'description' => 'Description',
            'post_type'   => 'Post Type',
            'supports'    => 'Supports',
            'order'       => 'Order',
            'hidden'      => 'Hidden',
        ]);

        if ($data['hidden'] !== '' && in_array(strtolower($data['hidden']), ['1', 'true', 'yes'], true)) {
            continue;
        }

        $supports = [];
        if ($data['supports'] !== '') {
            $supports = array_values(array_filter(array_map('trim', explode(',', $data['supports']))));
        }

        $entries[] = [
            'post_type'   => $data['post_type'] !== '' ? sanitize_key($data['post_type']) : $default_post_type,
            'value'       => $slug,
            'file'        => wp_normalize_path($file),
            'dir'         => $dir_name,
            // translate() вместо __() — строка из шапки файла, не литерал;
            // для перевода добавляйте её в .po своего домена вручную.
            'label'       => $data['name'] !== '' ? translate($data['name'], $domain) : cw_humanize_post_card_slug($slug),
            'description' => $data['description'] !== '' ? translate($data['description'], $domain) : '',
            'supports'    => $supports,
            'order'       => $data['order'] !== '' ? (int) $data['order'] : 50,
            'source'      => isset($root['label']) ? $root['label'] : '',
        ];
    }

    return $entries;
}

/**
 * Просканировать один корень.
 *
 * @param array $root
 * @return array Список записей.
 */
function cw_scan_post_card_root($root) {
    $path = $root['path'];

    // Плоский корень — тип записи задан при регистрации.
    if (!empty($root['post_type'])) {
        $dir = !empty($root['dir']) ? $root['dir'] : basename(untrailingslashit($path));
        return cw_scan_post_card_dir($path, $root['post_type'], $dir, $root);
    }

    $subdirs = glob($path . '*', GLOB_ONLYDIR);
    if (empty($subdirs)) {
        return [];
    }

    $claims  = cw_post_card_dir_claims();
    $entries = [];

    foreach ($subdirs as $subdir) {
        $dir = basename($subdir);

        if (isset($claims[$dir]) && $claims[$dir] !== $dir) {
            continue;
        }

        $entries = array_merge(
            $entries,
            cw_scan_post_card_dir(trailingslashit($subdir), $dir, $dir, $root)
        );
    }

    return $entries;
}

/**
 * Версия кеша скана. Инкрементируется при смене темы/плагинов.
 *
 * @return int
 */
function cw_post_card_scan_cache_version() {
    return (int) get_option('cw_post_cards_scan_version', 1);
}

/**
 * Сбросить кеш скана.
 *
 * @return void
 */
function cw_flush_post_card_templates_cache() {
    update_option('cw_post_cards_scan_version', cw_post_card_scan_cache_version() + 1, false);
}

add_action('switch_theme', 'cw_flush_post_card_templates_cache');
add_action('activated_plugin', 'cw_flush_post_card_templates_cache');
add_action('deactivated_plugin', 'cw_flush_post_card_templates_cache');
add_action('upgrader_process_complete', 'cw_flush_post_card_templates_cache');

/**
 * Полный скан всех корней.
 *
 * @return array post_type => [ template_value => meta ]
 */
function cw_scan_post_card_templates() {
    static $cache = null;

    if (null !== $cache) {
        return $cache;
    }

    $roots = cw_get_post_card_template_roots();

    $is_debug = defined('WP_DEBUG') && WP_DEBUG;

    // Локаль в ключе: label/description переведены на момент скана.
    $cache_key = 'cw_post_cards_scan_' . md5(
        wp_json_encode(array_keys($roots))
        . '|' . cw_post_card_scan_cache_version()
        . '|' . (function_exists('determine_locale') ? determine_locale() : get_locale())
    );

    if (!$is_debug) {
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            $cache = $cached;
            return $cache;
        }
    }

    $result = [];

    foreach ($roots as $root) {
        foreach (cw_scan_post_card_root($root) as $entry) {
            // Корни идут по возрастанию приоритета — последний перекрывает.
            $result[$entry['post_type']][$entry['value']] = $entry;
        }
    }

    foreach ($result as $post_type => $templates) {
        uasort($templates, function ($a, $b) {
            if ($a['order'] === $b['order']) {
                return strcasecmp($a['label'], $b['label']);
            }
            return $a['order'] <=> $b['order'];
        });
        $result[$post_type] = $templates;
    }

    if (!$is_debug) {
        set_transient($cache_key, $result, DAY_IN_SECONDS);
    }

    $cache = $result;
    return $cache;
}

/**
 * Найти файл шаблона по типу записи и имени шаблона.
 *
 * Сначала карта сканера (учитывает плагины и приоритеты корней),
 * затем историческая логика папок темы (префиксы + post_type map).
 *
 * @param string $template_name
 * @param string $post_type
 * @return string Абсолютный путь или пустая строка.
 */
function cw_locate_post_card_template($template_name, $post_type) {
    $scanned = cw_scan_post_card_templates();

    if (isset($scanned[$post_type][$template_name]['file'])) {
        return $scanned[$post_type][$template_name]['file'];
    }

    // Историческая логика: префикс шаблона → папка, иначе тип записи → папка.
    $prefix_to_dir = apply_filters('codeweber_template_prefix_map', [
        'client-'      => 'clients',
        'testimonial-' => 'testimonials',
        'document-'    => 'documents',
        'faq-'         => 'faq',
        'staff-'       => 'staff',
        'office-'      => 'offices',
        'vacancy-'     => 'post',
    ]);

    $template_dir  = 'post';
    $template_file = sanitize_file_name($template_name);
    $matched       = false;

    foreach ($prefix_to_dir as $prefix => $dir) {
        if (strpos($template_name, $prefix) === 0) {
            $template_dir  = $dir;
            $template_file = str_replace($prefix, '', $template_file);
            $matched       = true;
            break;
        }
    }

    if (!$matched) {
        $post_type_to_dir = cw_post_card_dir_claims();
        if (isset($post_type_to_dir[$post_type])) {
            $template_dir = $post_type_to_dir[$post_type];
        }
    }

    $path = get_theme_file_path('templates/post-cards/' . $template_dir . '/' . $template_file . '.php');

    return ($path && file_exists($path)) ? wp_normalize_path($path) : '';
}
