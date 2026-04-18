<?php
/**
 * Post Card Templates Registry
 *
 * Единый источник правды о card-шаблонах для каждого CPT.
 * Используется блоком Post Grid через REST API
 * и темой (через фильтры) для рендеринга карточек.
 *
 * Для добавления нового CPT или шаблона:
 *  — добавить запись в массив ниже (или через фильтр `codeweber_post_card_templates_registry`)
 *  — положить соответствующий `.php` в `templates/post-cards/<dir>/<template_file>.php`
 *
 * @package CodeWeber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Реестр card-шаблонов.
 *
 * Ключи массива `templates` — имена, сохраняемые в атрибуте `template`
 * блока Post Grid. Для CPT с префиксом (client-, document-, etc.)
 * тема сама стрипает префикс и ищет файл в соответствующей папке.
 *
 * @return array
 */
function codeweber_get_post_card_templates_registry() {
    $registry = [
        'post' => [
            'dir' => 'post',
            'templates' => [
                'default' => [
                    'label' => __('Default', 'codeweber'),
                    'description' => __('Simple layout with figure overlay and post header/footer', 'codeweber'),
                    'supports' => ['title', 'date', 'category', 'comments', 'image'],
                ],
                'card' => [
                    'label' => __('Card', 'codeweber'),
                    'description' => __('Card layout with shadow and card body', 'codeweber'),
                    'supports' => ['title', 'date', 'category', 'comments', 'image'],
                ],
                'card-content' => [
                    'label' => __('Card Content', 'codeweber'),
                    'description' => __('Card with excerpt and footer', 'codeweber'),
                    'supports' => ['title', 'date', 'category', 'comments', 'excerpt', 'image'],
                ],
                'slider' => [
                    'label' => __('Slider', 'codeweber'),
                    'description' => __('Slider layout with category on image and excerpt', 'codeweber'),
                    'supports' => ['title', 'date', 'category', 'excerpt', 'image'],
                ],
                'default-clickable' => [
                    'label' => __('Default Clickable', 'codeweber'),
                    'description' => __('Fully clickable card with lift effect', 'codeweber'),
                    'supports' => ['title', 'date', 'category', 'comments', 'image'],
                ],
                'overlay-5' => [
                    'label' => __('Overlay 5', 'codeweber'),
                    'description' => __('Overlay effect with 90% opacity and bottom overlay for date', 'codeweber'),
                    'supports' => ['title', 'date', 'excerpt', 'image'],
                ],
                'overlay-5-primary' => [
                    'label' => __('Overlay 5 Primary', 'codeweber'),
                    'description' => __('Primary-colored overlay on hover; title and excerpt slide in from the left', 'codeweber'),
                    'supports' => ['title', 'excerpt', 'image'],
                ],
            ],
        ],

        'clients' => [
            'dir' => 'clients',
            'templates' => [
                'client-simple' => [
                    'label' => __('Client Simple', 'codeweber'),
                    'description' => __('Simple logo for Swiper slider', 'codeweber'),
                    'supports' => ['image'],
                ],
                'client-grid' => [
                    'label' => __('Client Grid', 'codeweber'),
                    'description' => __('Logo in figure with adaptive padding for Grid layout', 'codeweber'),
                    'supports' => ['image'],
                ],
                'client-card' => [
                    'label' => __('Client Card', 'codeweber'),
                    'description' => __('Logo in card with shadow for Grid with cards', 'codeweber'),
                    'supports' => ['image'],
                ],
            ],
        ],

        'testimonials' => [
            'dir' => 'testimonials',
            'templates' => [
                'default' => [
                    'label' => __('Default', 'codeweber'),
                    'description' => __('Basic testimonial card with rating, text, avatar and author', 'codeweber'),
                    'supports' => ['title', 'excerpt', 'image', 'rating'],
                ],
                'card' => [
                    'label' => __('Card', 'codeweber'),
                    'description' => __('Card with colored backgrounds (Sandbox style)', 'codeweber'),
                    'supports' => ['title', 'excerpt', 'image', 'rating'],
                ],
                'blockquote' => [
                    'label' => __('Blockquote', 'codeweber'),
                    'description' => __('Block with quote and icon', 'codeweber'),
                    'supports' => ['title', 'excerpt', 'image'],
                ],
                'icon' => [
                    'label' => __('Icon', 'codeweber'),
                    'description' => __('Simple blockquote with icon, without rating', 'codeweber'),
                    'supports' => ['title', 'excerpt'],
                ],
                'horizontal' => [
                    'label' => __('Horizontal', 'codeweber'),
                    'description' => __('Horizontal testimonial layout', 'codeweber'),
                    'supports' => ['title', 'excerpt', 'image', 'rating'],
                ],
            ],
        ],

        'documents' => [
            'dir' => 'documents',
            'templates' => [
                'document-card' => [
                    'label' => __('Document Card', 'codeweber'),
                    'description' => __('Card layout with email button for documents', 'codeweber'),
                    'supports' => ['title', 'button'],
                ],
                'document-card-download' => [
                    'label' => __('Document Card Download', 'codeweber'),
                    'description' => __('Card layout with AJAX download button for documents', 'codeweber'),
                    'supports' => ['title', 'button'],
                ],
            ],
        ],

        'events' => [
            'dir' => 'events',
            'templates' => [
                'default' => [
                    'label' => __('Default', 'codeweber'),
                    'description' => __('Basic event card', 'codeweber'),
                    'supports' => ['title', 'date', 'image', 'category'],
                ],
                'card-events' => [
                    'label' => __('Events Card', 'codeweber'),
                    'description' => __('Event card with date badge', 'codeweber'),
                    'supports' => ['title', 'date', 'image', 'category'],
                ],
            ],
        ],

        'faq' => [
            'dir' => 'faq',
            'templates' => [
                'default' => [
                    'label' => __('Default', 'codeweber'),
                    'description' => __('FAQ card with icon, question and answer', 'codeweber'),
                    'supports' => ['title', 'excerpt'],
                ],
            ],
        ],

        'offices' => [
            'dir' => 'offices',
            'templates' => [
                'card' => [
                    'label' => __('Office Card', 'codeweber'),
                    'description' => __('Office card with address and contacts', 'codeweber'),
                    'supports' => ['title', 'image'],
                ],
            ],
        ],

        'staff' => [
            'dir' => 'staff',
            'templates' => [
                'default' => [
                    'label' => __('Default', 'codeweber'),
                    'description' => __('Basic staff card with image, name and position', 'codeweber'),
                    'supports' => ['title', 'image', 'social'],
                ],
                'card' => [
                    'label' => __('Card', 'codeweber'),
                    'description' => __('Card with colored backgrounds (Sandbox style)', 'codeweber'),
                    'supports' => ['title', 'image', 'social'],
                ],
                'circle' => [
                    'label' => __('Circle', 'codeweber'),
                    'description' => __('Circular avatar with social links', 'codeweber'),
                    'supports' => ['title', 'image', 'social'],
                ],
                'circle_center' => [
                    'label' => __('Circle Center', 'codeweber'),
                    'description' => __('Circular avatar centered with social links', 'codeweber'),
                    'supports' => ['title', 'image', 'social'],
                ],
                'circle_center_alt' => [
                    'label' => __('Circle Center Alt', 'codeweber'),
                    'description' => __('Circular avatar centered with link on image and social links', 'codeweber'),
                    'supports' => ['title', 'image', 'social'],
                ],
                'horizontal' => [
                    'label' => __('Horizontal', 'codeweber'),
                    'description' => __('Horizontal staff layout', 'codeweber'),
                    'supports' => ['title', 'image', 'social'],
                ],
            ],
        ],

        'services' => [
            'dir' => 'services',
            'templates' => [
                'overlay-5' => [
                    'label' => __('Overlay 5', 'codeweber'),
                    'description' => __('Service card: title bottom, category badge top, excerpt on hover', 'codeweber'),
                    'supports' => ['title', 'category', 'excerpt', 'image'],
                ],
                'overlay-5-primary' => [
                    'label' => __('Overlay 5 Primary', 'codeweber'),
                    'description' => __('Service card with primary-colored overlay on hover; title and excerpt slide in from the left', 'codeweber'),
                    'supports' => ['title', 'excerpt', 'image'],
                ],
            ],
        ],
    ];

    // WooCommerce product — шаблоны лежат в templates/woocommerce/cards/
    // (не копируем сюда, подключаем через фильтр codeweber_post_card_template_path ниже)
    if (class_exists('WooCommerce')) {
        $registry['product'] = [
            // 'dir' намеренно не задан — путь перехватывается фильтром ниже
            'templates' => [
                'shop-card' => [
                    'label' => __('Shop Card', 'codeweber'),
                    'description' => __('Bootstrap card with Add to cart, Sale/New badges, hover swap', 'codeweber'),
                    'supports' => ['title', 'image', 'price', 'button', 'badges'],
                ],
                'shop-compact' => [
                    'label' => __('Shop Compact', 'codeweber'),
                    'description' => __('Minimalistic card for dense 4-6 column grids', 'codeweber'),
                    'supports' => ['title', 'image', 'price', 'button'],
                ],
                'shop-list' => [
                    'label' => __('Shop List', 'codeweber'),
                    'description' => __('Horizontal: image 1/3, content 2/3', 'codeweber'),
                    'supports' => ['title', 'image', 'price', 'excerpt', 'button'],
                ],
                'shop2' => [
                    'label' => __('Shop 2', 'codeweber'),
                    'description' => __('Isotope grid style, figure with overlays', 'codeweber'),
                    'supports' => ['title', 'image', 'price', 'badges'],
                ],
            ],
        ];
    }

    return apply_filters('codeweber_post_card_templates_registry', $registry);
}

/**
 * Получить шаблоны для конкретного post_type (для REST endpoint).
 *
 * @param string $post_type
 * @return array Список шаблонов с value/label/description/supports
 */
function codeweber_get_post_card_templates_for($post_type) {
    $registry = codeweber_get_post_card_templates_registry();

    // Специфичные шаблоны для CPT — если зарегистрированы
    if (isset($registry[$post_type]) && !empty($registry[$post_type]['templates'])) {
        $source = $registry[$post_type]['templates'];
    } elseif (!empty($registry['post']['templates'])) {
        // Fallback на post-шаблоны — соответствует поведению cw_render_post_card,
        // которое для нестандартных CPT использует папку `post/`.
        $source = $registry['post']['templates'];
    } else {
        return [];
    }

    $result = [];
    foreach ($source as $slug => $meta) {
        $result[] = [
            'value' => $slug,
            'label' => isset($meta['label']) ? $meta['label'] : $slug,
            'description' => isset($meta['description']) ? $meta['description'] : '',
            'supports' => isset($meta['supports']) ? $meta['supports'] : [],
        ];
    }

    return $result;
}

/**
 * Автоматически дополняем `codeweber_post_type_template_map`
 * записями из реестра — чтобы cw_render_post_card находил папку для CPT,
 * которых нет в базовой карте.
 */
add_filter('codeweber_post_type_template_map', function ($map) {
    $registry = codeweber_get_post_card_templates_registry();
    foreach ($registry as $post_type => $config) {
        if (!isset($map[$post_type]) && isset($config['dir'])) {
            $map[$post_type] = $config['dir'];
        }
    }
    return $map;
});

/**
 * Перехват пути шаблона для WooCommerce product:
 * используем существующие карты из templates/woocommerce/cards/, не копируя их
 * в templates/post-cards/. Также проставляем global $product — WC-карты
 * рассчитывают на него.
 */
add_filter('codeweber_post_card_template_path', function ($path, $template_name, $post_type, $post_data) {
    if ($post_type !== 'product' || !class_exists('WooCommerce')) {
        return $path;
    }

    $wc_path = get_theme_file_path(
        'templates/woocommerce/cards/' . sanitize_file_name($template_name) . '.php'
    );

    if ($wc_path && file_exists($wc_path)) {
        global $product;
        if (!$product && !empty($post_data['post']->ID)) {
            $product = wc_get_product($post_data['post']->ID);
        }
        return $wc_path;
    }

    return $path;
}, 10, 4);
