<?php
/**
 * Body Background — управление фоном страницы.
 *
 * Приоритет применения:
 *   1. Per-post мета `_cw_body_bg`
 *   2. Redux `body_bg_single_{post_type}` / `body_bg_archive_{post_type}`
 *   3. Default (прозрачный, без класса)
 *
 * Подход: добавляет класс `cw-page-bg-{value}` на <body>.
 * SCSS (.cw-page-bg-* .content-wrapper) применяет фон.
 * Секции со своим `bg-*` остаются нетронутыми.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Допустимые значения фона.
 */
function cw_body_bg_options(): array {
	return [
		'default'          => __( 'Default (transparent)', 'codeweber' ),
		'bg-light'         => __( 'Light', 'codeweber' ),
		'bg-gray'          => __( 'Gray', 'codeweber' ),
		'bg-soft-primary'  => __( 'Soft Primary', 'codeweber' ),
		'bg-soft-secondary' => __( 'Soft Secondary', 'codeweber' ),
		'bg-soft-leaf'     => __( 'Soft Leaf', 'codeweber' ),
		'bg-dark'          => __( 'Dark', 'codeweber' ),
	];
}

/**
 * Определяет нужный класс фона для текущей страницы.
 * Возвращает строку (без 'default') или '' если фон не задан.
 */
function cw_get_body_bg(): string {
	$bg = '';

	if ( is_singular() ) {
		$post_id  = get_queried_object_id();
		$post_type = get_post_type( $post_id );

		// 1. Per-post мета
		$meta = get_post_meta( $post_id, '_cw_body_bg', true );
		if ( $meta && $meta !== 'default' ) {
			return sanitize_key( $meta );
		}

		// 2. Redux global for this post type
		$redux_key = 'body_bg_single_' . sanitize_key( $post_type );
		$redux_val = Codeweber_Options::get( $redux_key );
		if ( $redux_val && $redux_val !== 'default' ) {
			return sanitize_key( $redux_val );
		}
	} elseif ( is_post_type_archive() || is_tax() ) {
		$post_type = is_tax()
			? get_queried_object()->taxonomy // use taxonomy name for tax pages
			: get_query_var( 'post_type' );

		// For taxonomy pages, try to get associated post type
		if ( is_tax() ) {
			$tax_obj = get_queried_object();
			if ( $tax_obj ) {
				$tax_info = get_taxonomy( $tax_obj->taxonomy );
				if ( $tax_info && ! empty( $tax_info->object_type ) ) {
					$post_type = $tax_info->object_type[0];
				}
			}
		}

		$redux_key = 'body_bg_archive_' . sanitize_key( $post_type );
		$redux_val = Codeweber_Options::get( $redux_key );
		if ( $redux_val && $redux_val !== 'default' ) {
			return sanitize_key( $redux_val );
		}
	} elseif ( is_home() || is_archive() ) {
		$redux_val = Codeweber_Options::get( 'body_bg_archive_post' );
		if ( $redux_val && $redux_val !== 'default' ) {
			return sanitize_key( $redux_val );
		}
	} elseif ( is_page() ) {
		$post_id = get_queried_object_id();

		// 1. Per-post мета
		$meta = get_post_meta( $post_id, '_cw_body_bg', true );
		if ( $meta && $meta !== 'default' ) {
			return sanitize_key( $meta );
		}

		// 2. Redux global for pages
		$redux_val = Codeweber_Options::get( 'body_bg_single_page' );
		if ( $redux_val && $redux_val !== 'default' ) {
			return sanitize_key( $redux_val );
		}
	}

	return $bg;
}

/**
 * Добавляет класс cw-page-bg-{value} на <body>.
 */
add_filter( 'body_class', function ( array $classes ): array {
	$bg = cw_get_body_bg();
	if ( $bg ) {
		$classes[] = 'cw-page-bg-' . $bg;
	}
	return $classes;
} );

// ── Метабокс ──────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', function () {
	$post_types = array_merge(
		[ 'page', 'post', 'product' ],
		get_post_types( [ '_builtin' => false, 'public' => true ], 'names' )
	);
	// Исключаем служебные CPT
	$exclude = [ 'header', 'footer', 'page-header', 'cw_modal', 'html-block', 'notification' ];
	$post_types = array_diff( $post_types, $exclude );

	add_meta_box(
		'cw_body_bg',
		__( 'Page Background', 'codeweber' ),
		'cw_body_bg_render_metabox',
		array_unique( $post_types ),
		'side',
		'default'
	);
} );

function cw_body_bg_render_metabox( WP_Post $post ): void {
	wp_nonce_field( 'cw_body_bg_save', 'cw_body_bg_nonce' );
	$current = get_post_meta( $post->ID, '_cw_body_bg', true ) ?: 'default';
	$options = cw_body_bg_options();
	?>
	<select name="cw_body_bg" id="cw_body_bg" style="width:100%">
		<?php foreach ( $options as $val => $label ) : ?>
			<option value="<?= esc_attr( $val ) ?>"<?= selected( $current, $val, false ) ?>>
				<?= esc_html( $label ) ?>
			</option>
		<?php endforeach; ?>
	</select>
	<p class="description" style="margin-top:6px">
		<?= esc_html__( 'Override global Redux setting for this page only.', 'codeweber' ) ?>
	</p>
	<?php
}

add_action( 'save_post', function ( int $post_id ): void {
	if (
		! isset( $_POST['cw_body_bg_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cw_body_bg_nonce'] ) ), 'cw_body_bg_save' )
	) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['cw_body_bg'] ) ) {
		return;
	}

	$value = sanitize_key( $_POST['cw_body_bg'] );
	$allowed = array_keys( cw_body_bg_options() );
	if ( ! in_array( $value, $allowed, true ) ) {
		return;
	}

	update_post_meta( $post_id, '_cw_body_bg', $value );
} );
