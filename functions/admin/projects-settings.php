<?php
/**
 * Projects Settings Page
 *
 * Страница настроек «Проекты → Настройки».
 * Опция хранится в: codeweber_projects_settings
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Menu
// ---------------------------------------------------------------------------

function codeweber_projects_settings_register_page(): void {
	add_submenu_page(
		'edit.php?post_type=projects',
		__( 'Projects Settings', 'codeweber' ),
		__( 'Settings', 'codeweber' ),
		'manage_options',
		'codeweber-projects-settings',
		'codeweber_projects_settings_render_page'
	);
}
add_action( 'admin_menu', 'codeweber_projects_settings_register_page' );

// ---------------------------------------------------------------------------
// Settings registration
// ---------------------------------------------------------------------------

function codeweber_projects_settings_register(): void {
	register_setting(
		'codeweber_projects_settings_group',
		'codeweber_projects_settings',
		[
			'sanitize_callback' => 'codeweber_projects_settings_sanitize',
			'default'           => [],
		]
	);

	// ── Секция: Карта ─────────────────────────────────────────────────────────
	add_settings_section(
		'codeweber_projects_map',
		__( 'Map', 'codeweber' ),
		null,
		'codeweber-projects-settings'
	);

	add_settings_field(
		'show_map',
		__( 'Show map of projects', 'codeweber' ),
		'codeweber_projects_field_show_map',
		'codeweber-projects-settings',
		'codeweber_projects_map'
	);

	// ── Секция: Плавающая кнопка карты (мобильные) ───────────────────────────
	add_settings_section(
		'codeweber_projects_map_float',
		__( 'Floating map button (mobile)', 'codeweber' ),
		function () {
			echo '<p class="description">' . esc_html__( 'Shown only on mobile devices (hidden on md breakpoint and above). Requires «Show map of projects» to be enabled.', 'codeweber' ) . '</p>';
		},
		'codeweber-projects-settings'
	);

	add_settings_field(
		'map_float_enabled',
		__( 'Enable floating button', 'codeweber' ),
		'codeweber_projects_field_map_float_enabled',
		'codeweber-projects-settings',
		'codeweber_projects_map_float'
	);

	add_settings_field(
		'map_float_type',
		__( 'Button type', 'codeweber' ),
		'codeweber_projects_field_map_float_type',
		'codeweber-projects-settings',
		'codeweber_projects_map_float'
	);

	add_settings_field(
		'map_float_icon',
		__( 'Icon', 'codeweber' ),
		'codeweber_projects_field_map_float_icon',
		'codeweber-projects-settings',
		'codeweber_projects_map_float'
	);

	add_settings_field(
		'map_float_text',
		__( 'Button text', 'codeweber' ),
		'codeweber_projects_field_map_float_text',
		'codeweber-projects-settings',
		'codeweber_projects_map_float'
	);

	add_settings_field(
		'map_float_color',
		__( 'Color', 'codeweber' ),
		'codeweber_projects_field_map_float_color',
		'codeweber-projects-settings',
		'codeweber_projects_map_float'
	);

	add_settings_field(
		'map_float_shape',
		__( 'Shape', 'codeweber' ),
		'codeweber_projects_field_map_float_shape',
		'codeweber-projects-settings',
		'codeweber_projects_map_float'
	);

	add_settings_field(
		'map_float_zindex',
		__( 'z-index', 'codeweber' ),
		'codeweber_projects_field_map_float_zindex',
		'codeweber-projects-settings',
		'codeweber_projects_map_float'
	);

	add_settings_field(
		'map_float_offset_bottom',
		__( 'Offset from bottom', 'codeweber' ),
		'codeweber_projects_field_map_float_offset_bottom',
		'codeweber-projects-settings',
		'codeweber_projects_map_float'
	);

	add_settings_field(
		'map_float_offset_left',
		__( 'Offset from left', 'codeweber' ),
		'codeweber_projects_field_map_float_offset_left',
		'codeweber-projects-settings',
		'codeweber_projects_map_float'
	);
}
add_action( 'admin_init', 'codeweber_projects_settings_register' );

// ---------------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------------

function codeweber_projects_settings_get( string $key, $default = '' ) {
	$options = get_option( 'codeweber_projects_settings', [] );
	return $options[ $key ] ?? $default;
}

// ---------------------------------------------------------------------------
// Field renderers
// ---------------------------------------------------------------------------

function codeweber_projects_field_show_map(): void {
	$val = codeweber_projects_settings_get( 'show_map', '1' );
	echo '<label><input type="checkbox" name="codeweber_projects_settings[show_map]" value="1" ' . checked( $val, '1', false ) . '> ';
	esc_html_e( 'Show «Show on map» button and map offcanvas on single project pages', 'codeweber' );
	echo '</label>';
	echo '<p class="description">' . esc_html__( 'Requires latitude and longitude to be filled in project settings.', 'codeweber' ) . '</p>';
}

function codeweber_projects_field_map_float_enabled(): void {
	$val = codeweber_projects_settings_get( 'map_float_enabled', '0' );
	echo '<label><input type="checkbox" name="codeweber_projects_settings[map_float_enabled]" value="1" ' . checked( $val, '1', false ) . '> ';
	esc_html_e( 'Show floating map button on mobile devices', 'codeweber' );
	echo '</label>';
}

function codeweber_projects_field_map_float_type(): void {
	$val     = codeweber_projects_settings_get( 'map_float_type', 'icon_text' );
	$options = [
		'icon'      => __( 'Icon only', 'codeweber' ),
		'text'      => __( 'Text only', 'codeweber' ),
		'icon_text' => __( 'Icon + text', 'codeweber' ),
	];
	echo '<select name="codeweber_projects_settings[map_float_type]">';
	foreach ( $options as $k => $label ) {
		echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select>';
}

function codeweber_projects_field_map_float_icon(): void {
	$val = codeweber_projects_settings_get( 'map_float_icon', 'map-marker' );
	echo '<input type="text" name="codeweber_projects_settings[map_float_icon]" value="' . esc_attr( $val ) . '" style="width:200px;" placeholder="map-marker">';
	echo '<p class="description">' . esc_html__( 'Unicons icon name without «uil-» prefix. Used for «Icon only» and «Icon + text» types.', 'codeweber' ) . ' <a href="https://iconscout.com/unicons/explore/line" target="_blank">Browse icons</a></p>';
}

function codeweber_projects_field_map_float_text(): void {
	$val = codeweber_projects_settings_get( 'map_float_text', __( 'Map', 'codeweber' ) );
	echo '<input type="text" name="codeweber_projects_settings[map_float_text]" value="' . esc_attr( $val ) . '" style="width:200px;" placeholder="Map">';
	echo '<p class="description">' . esc_html__( 'Used for «Text only» and «Icon + text» types.', 'codeweber' ) . '</p>';
}

function codeweber_projects_field_map_float_color(): void {
	$val     = codeweber_projects_settings_get( 'map_float_color', 'primary' );
	$options = [
		'primary'        => 'Primary',
		'soft-primary'   => 'Soft Primary',
		'secondary'      => 'Secondary',
		'soft-secondary' => 'Soft Secondary',
		'dark'           => 'Dark',
		'white'          => 'White',
	];
	echo '<select name="codeweber_projects_settings[map_float_color]">';
	foreach ( $options as $k => $label ) {
		echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $label ) . '</option>';
	}
	echo '</select>';
	echo '<p class="description">' . esc_html__( 'Generates class btn-{color}, e.g. btn-primary, btn-soft-primary.', 'codeweber' ) . '</p>';
}

function codeweber_projects_field_map_float_shape(): void {
	$val     = codeweber_projects_settings_get( 'map_float_shape', 'rounded-pill' );
	$options = [
		'rounded-pill' => __( 'Pill', 'codeweber' ),
		'rounded'      => __( 'Rounded', 'codeweber' ),
		'rounded-0'    => __( 'Square', 'codeweber' ),
	];
	foreach ( $options as $k => $label ) {
		echo '<label style="margin-right:16px;"><input type="radio" name="codeweber_projects_settings[map_float_shape]" value="' . esc_attr( $k ) . '" ' . checked( $val, $k, false ) . '> ' . esc_html( $label ) . '</label>';
	}
	echo '<p class="description">' . esc_html__( 'Not applied to «Icon only» type (always circle).', 'codeweber' ) . '</p>';
}

function codeweber_projects_field_map_float_zindex(): void {
	$val = (int) codeweber_projects_settings_get( 'map_float_zindex', 1040 );
	echo '<input type="number" name="codeweber_projects_settings[map_float_zindex]" value="' . esc_attr( $val ) . '" style="width:100px;" min="1" max="9999">';
	echo '<p class="description">' . esc_html__( 'Default: 1040 (above most UI, below modals/offcanvas at 1045).', 'codeweber' ) . '</p>';
}

function codeweber_projects_field_map_float_offset_bottom(): void {
	$val = (int) codeweber_projects_settings_get( 'map_float_offset_bottom', 24 );
	echo '<input type="number" name="codeweber_projects_settings[map_float_offset_bottom]" value="' . esc_attr( $val ) . '" style="width:100px;" min="0" max="500"> <span>px</span>';
}

function codeweber_projects_field_map_float_offset_left(): void {
	$val = (int) codeweber_projects_settings_get( 'map_float_offset_left', 16 );
	echo '<input type="number" name="codeweber_projects_settings[map_float_offset_left]" value="' . esc_attr( $val ) . '" style="width:100px;" min="0" max="500"> <span>px</span>';
}

// ---------------------------------------------------------------------------
// Sanitize
// ---------------------------------------------------------------------------

function codeweber_projects_settings_sanitize( $input ): array {
	if ( ! is_array( $input ) ) {
		$input = [];
	}

	$allowed_types  = [ 'icon', 'text', 'icon_text' ];
	$allowed_colors = [ 'primary', 'soft-primary', 'secondary', 'soft-secondary', 'dark', 'white' ];
	$allowed_shapes = [ 'rounded-pill', 'rounded', 'rounded-0' ];

	return [
		'show_map'          => isset( $input['show_map'] ) ? '1' : '0',
		'map_float_enabled' => isset( $input['map_float_enabled'] ) ? '1' : '0',
		'map_float_type'    => in_array( $input['map_float_type'] ?? '', $allowed_types, true )
			? $input['map_float_type']
			: 'icon_text',
		'map_float_icon'    => sanitize_key( $input['map_float_icon'] ?? 'map-marker' ) ?: 'map-marker',
		'map_float_text'    => sanitize_text_field( $input['map_float_text'] ?? __( 'Map', 'codeweber' ) ),
		'map_float_color'   => in_array( $input['map_float_color'] ?? '', $allowed_colors, true )
			? $input['map_float_color']
			: 'primary',
		'map_float_shape'   => in_array( $input['map_float_shape'] ?? '', $allowed_shapes, true )
			? $input['map_float_shape']
			: 'rounded-pill',
		'map_float_zindex'         => min( 9999, max( 1, (int) ( $input['map_float_zindex'] ?? 1040 ) ) ),
		'map_float_offset_bottom'  => min( 500, max( 0, (int) ( $input['map_float_offset_bottom'] ?? 24 ) ) ),
		'map_float_offset_left'    => min( 500, max( 0, (int) ( $input['map_float_offset_left'] ?? 16 ) ) ),
	];
}

// ---------------------------------------------------------------------------
// Floating map button (front-end render)
// ---------------------------------------------------------------------------

/**
 * Рендерит плавающую кнопку карты проектов для мобильных устройств.
 * Вызывается в шаблонах архива и single после codeweber_projects_map_modal().
 *
 * — На архиве: показывается всегда (при включённой настройке).
 * — На single:  показывается только если у проекта заполнены координаты.
 */
function codeweber_projects_map_float_button(): void {
	if ( ! function_exists( 'codeweber_projects_settings_get' ) ) {
		return;
	}
	if ( codeweber_projects_settings_get( 'show_map', '1' ) !== '1' ) {
		return;
	}
	if ( ! class_exists( 'Codeweber_Yandex_Maps' ) ) {
		return;
	}
	if ( codeweber_projects_settings_get( 'map_float_enabled', '0' ) !== '1' ) {
		return;
	}

	// На single — только если у проекта есть координаты
	if ( is_singular( 'projects' ) ) {
		$lat = get_post_meta( get_the_ID(), 'main_information_latitude', true );
		$lng = get_post_meta( get_the_ID(), 'main_information_longitude', true );
		if ( ! $lat || ! $lng ) {
			return;
		}
	}

	$type    = codeweber_projects_settings_get( 'map_float_type', 'icon_text' );
	$icon    = codeweber_projects_settings_get( 'map_float_icon', 'map-marker' );
	$text    = codeweber_projects_settings_get( 'map_float_text', __( 'Map', 'codeweber' ) );
	$color   = codeweber_projects_settings_get( 'map_float_color', 'primary' );
	$shape   = codeweber_projects_settings_get( 'map_float_shape', 'rounded-pill' );
	$zindex  = (int) codeweber_projects_settings_get( 'map_float_zindex', 1040 );
	$bottom  = (int) codeweber_projects_settings_get( 'map_float_offset_bottom', 24 );
	$left    = (int) codeweber_projects_settings_get( 'map_float_offset_left', 16 );

	// Классы кнопки
	$btn_classes = 'btn btn-' . $color;
	if ( $type === 'icon' ) {
		$btn_classes .= ' btn-circle';
	} else {
		$btn_classes .= ' ' . $shape;
		if ( $type === 'icon_text' ) {
			$btn_classes .= ' btn-icon btn-icon-start';
		}
	}
	$btn_classes .= ' has-ripple mb-0';

	// Содержимое кнопки
	$inner = '';
	if ( $type === 'icon' || $type === 'icon_text' ) {
		$inner .= '<i class="uil uil-' . esc_attr( $icon ) . '"></i>';
	}
	if ( $type === 'text' || $type === 'icon_text' ) {
		$inner .= ' ' . esc_html( $text );
	}
	?>
	<div class="codeweber-projects-map-float d-md-none position-fixed"
	     style="bottom:<?php echo (int) $bottom; ?>px;left:<?php echo (int) $left; ?>px;z-index:<?php echo (int) $zindex; ?>;">
		<a href="#" data-project-map class="<?php echo esc_attr( $btn_classes ); ?>">
			<?php echo $inner; // Escaped above ?>
		</a>
	</div>
	<?php
}

// ---------------------------------------------------------------------------
// Page render
// ---------------------------------------------------------------------------

function codeweber_projects_settings_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Projects Settings', 'codeweber' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'codeweber_projects_settings_group' );
			do_settings_sections( 'codeweber-projects-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
