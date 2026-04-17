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
    ];

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

    if (!isset($registry[$post_type]) || empty($registry[$post_type]['templates'])) {
        return [];
    }

    $result = [];
    foreach ($registry[$post_type]['templates'] as $slug => $meta) {
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
