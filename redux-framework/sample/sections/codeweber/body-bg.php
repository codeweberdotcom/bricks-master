<?php
/**
 * Redux — глобальная секция Body Background.
 * Применяется ко всем страницам если не переопределена per-CPT или per-post.
 */

defined( 'ABSPATH' ) || exit;

$_body_bg_color_options = array(
	'default'           => esc_html__( 'Default (transparent)', 'codeweber' ),
	'bg-light'          => esc_html__( 'Light', 'codeweber' ),
	'bg-gray'           => esc_html__( 'Gray', 'codeweber' ),
	'bg-soft-primary'   => esc_html__( 'Soft Primary', 'codeweber' ),
	'bg-soft-secondary' => esc_html__( 'Soft Secondary', 'codeweber' ),
	'bg-soft-leaf'      => esc_html__( 'Soft Leaf', 'codeweber' ),
	'bg-dark'           => esc_html__( 'Dark', 'codeweber' ),
);

Redux::set_section(
	$opt_name,
	array(
		'title'      => esc_html__( 'Body Background', 'codeweber' ),
		'id'         => 'body-bg-global-section',
		'desc'       => esc_html__( 'Global page background. Overridden by per-CPT and per-post settings.', 'codeweber' ),
		'subsection' => true,
		'parent'     => 'themestyle',
		'fields'     => array(

			// ── Цвет ───────────────────────────────────────────────────────────
			array(
				'id'      => 'body_bg_global_color',
				'type'    => 'select',
				'title'   => esc_html__( 'Background Color', 'codeweber' ),
				'desc'    => esc_html__( 'Fallback color when no image is set.', 'codeweber' ),
				'options' => $_body_bg_color_options,
				'default' => 'default',
			),

			// ── Изображение / паттерн ───────────────────────────────────────────
			array(
				'id'    => 'body_bg_global_image',
				'type'  => 'media',
				'title' => esc_html__( 'Background Image / Pattern', 'codeweber' ),
				'desc'  => esc_html__( 'Upload an image or a repeating pattern tile.', 'codeweber' ),
				'url'   => true,
			),

			array(
				'id'      => 'body_bg_global_mode',
				'type'    => 'button_set',
				'title'   => esc_html__( 'Mode', 'codeweber' ),
				'options' => array(
					'image'   => esc_html__( 'Image', 'codeweber' ),
					'pattern' => esc_html__( 'Pattern', 'codeweber' ),
				),
				'default' => 'image',
			),

			array(
				'id'       => 'body_bg_global_size',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Image Size', 'codeweber' ),
				'options'  => array(
					'cover' => esc_html__( 'Cover', 'codeweber' ),
					'auto'  => esc_html__( 'Auto', 'codeweber' ),
					'full'  => esc_html__( 'Full Width', 'codeweber' ),
				),
				'default'  => 'cover',
				'required' => array( 'body_bg_global_mode', '=', 'image' ),
			),

			array(
				'id'       => 'body_bg_global_repeat',
				'type'     => 'button_set',
				'title'    => esc_html__( 'Pattern Repeat', 'codeweber' ),
				'options'  => array(
					'repeat'    => esc_html__( 'All', 'codeweber' ),
					'repeat-x'  => esc_html__( 'Horizontal', 'codeweber' ),
					'repeat-y'  => esc_html__( 'Vertical', 'codeweber' ),
					'no-repeat' => esc_html__( 'None', 'codeweber' ),
				),
				'default'  => 'repeat',
				'required' => array( 'body_bg_global_mode', '=', 'pattern' ),
			),

			array(
				'id'      => 'body_bg_global_pattern_preload_color',
				'type'    => 'select',
				'title'   => esc_html__( 'Pattern Preload Color', 'codeweber' ),
				'desc'    => esc_html__( 'Background color shown while the pattern image is loading.', 'codeweber' ),
				'options' => call_user_func( function () {
					$opts = array( '' => esc_html__( 'None', 'codeweber' ) );
					$file = get_template_directory() . '/components/colors.json';
					if ( file_exists( $file ) ) {
						$data = json_decode( file_get_contents( $file ), true );
						if ( is_array( $data ) ) {
							foreach ( $data as $c ) {
								$opts[ $c['value'] ] = esc_html__( $c['label'], 'codeweber' );
							}
						}
					}
					return $opts;
				} ),
				'default' => '',
				'required' => array( 'body_bg_global_mode', '=', 'pattern' ),
			),

			// ── Цвет текста ────────────────────────────────────────────────────
			array(
				'id'      => 'body_bg_global_text',
				'type'    => 'button_set',
				'title'   => esc_html__( 'Text Color', 'codeweber' ),
				'options' => array(
					'auto'    => esc_html__( 'Auto', 'codeweber' ),
					'inverse' => esc_html__( 'Light (inverse)', 'codeweber' ),
				),
				'default' => 'auto',
			),
		),
	)
);
