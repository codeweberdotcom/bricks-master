<?php
/**
 * Yandex Maps v3 style presets registry.
 *
 * Large customization arrays live as JSON files in ./presets/. Only the
 * selected preset is ever sent to the frontend — it is resolved in
 * Codeweber_Yandex_Maps::render_map() into the colorSchemeCustom payload,
 * reusing the existing "custom" code path in yandex-maps-v3.js.
 *
 * Single source of truth for both the Redux global setting and the
 * Gutenberg block (yandex-map-v3) preset buttons.
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registry of available map style presets.
 *
 * The slug must match a JSON file in ./presets/<slug>.json. The swatch is a
 * representative colour used for preview chips in the editor / settings.
 *
 * @return array<string,array{label:string,swatch:string}>
 */
function codeweber_yandex_map_style_presets(): array {
    return array(
        'gray'  => array( 'label' => __( 'Silver', 'codeweber' ),     'swatch' => '#c7cfd6' ),
        'slate' => array( 'label' => __( 'Dark Slate', 'codeweber' ), 'swatch' => '#40474f' ),
        'sky'   => array( 'label' => __( 'Sky', 'codeweber' ),        'swatch' => '#a3c6fa' ),
        'navy'  => array( 'label' => __( 'Navy', 'codeweber' ),       'swatch' => '#2d4a76' ),
    );
}

/**
 * Check whether a slug refers to a registered style preset.
 *
 * @param string $slug Preset slug.
 * @return bool
 */
function codeweber_yandex_map_is_preset( string $slug ): bool {
    return isset( codeweber_yandex_map_style_presets()[ $slug ] );
}

/**
 * Load a preset's customization array by slug.
 *
 * Results are cached per request. Unknown slugs and unreadable / invalid
 * files resolve to an empty array (caller should fall back gracefully).
 *
 * @param string $slug Preset slug.
 * @return array Decoded customization array, or empty array.
 */
function codeweber_yandex_map_load_preset( string $slug ): array {
    static $cache = array();

    $slug = sanitize_key( $slug );
    if ( '' === $slug ) {
        return array();
    }
    if ( array_key_exists( $slug, $cache ) ) {
        return $cache[ $slug ];
    }

    if ( ! codeweber_yandex_map_is_preset( $slug ) ) {
        $cache[ $slug ] = array();
        return array();
    }

    $file = __DIR__ . '/presets/' . $slug . '.json';
    if ( ! is_readable( $file ) ) {
        $cache[ $slug ] = array();
        return array();
    }

    $data           = json_decode( (string) file_get_contents( $file ), true );
    $cache[ $slug ] = is_array( $data ) ? $data : array();

    return $cache[ $slug ];
}

/**
 * Build a Redux/select-ready options map of preset slug => label.
 *
 * @return array<string,string>
 */
function codeweber_yandex_map_preset_options(): array {
    $options = array();
    foreach ( codeweber_yandex_map_style_presets() as $slug => $preset ) {
        $options[ $slug ] = $preset['label'];
    }
    return $options;
}
