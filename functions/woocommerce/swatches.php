<?php
/**
 * WooCommerce Variation Swatches
 *
 * Registers custom attribute types (button, color, image), provides admin
 * term-meta fields and saves them, and replaces WooCommerce dropdown selects
 * with visual swatch HTML on the single product page.
 *
 * Meta keys (compatible with woo-variation-swatches plugin data in DB):
 *   product_attribute_color  — primary hex color (#rrggbb)
 *   secondary_color          — secondary hex for dual-color gradient
 *   is_dual_color            — 'yes' | 'no'
 *   dual_color_angle         — int degrees 0-360 (default 45)
 *   product_attribute_image  — attachment ID
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'WC' ) ) {
	return;
}

// =============================================================================
// ATTRIBUTE TYPE REGISTRATION
// =============================================================================

/**
 * Add button / color / image to the WooCommerce attribute type selector.
 */
add_filter( 'product_attributes_type_selector', function ( $types ) {
	$types['button'] = __( 'Button', 'codeweber' );
	$types['color']  = __( 'Color swatch', 'codeweber' );
	$types['image']  = __( 'Image swatch', 'codeweber' );
	return $types;
} );

// =============================================================================
// ADMIN — ENQUEUE ASSETS
// =============================================================================

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, [ 'edit-tags.php', 'term.php' ], true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || strpos( $screen->taxonomy, 'pa_' ) !== 0 ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_media();
	wp_enqueue_script( 'wp-color-picker' );
} );

/**
 * Inline admin JS for color picker init and media upload.
 * Runs only on PA_* taxonomy term pages.
 */
add_action( 'admin_footer', function () {
	if ( ! is_admin() ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || strpos( $screen->taxonomy, 'pa_' ) !== 0 ) {
		return;
	}
	?>
	<script>
	jQuery( function ( $ ) {

		// ── Color pickers ──────────────────────────────────────────────────────
		$( '.cw-swatch-color-picker' ).wpColorPicker();

		// ── Dual-color toggle ──────────────────────────────────────────────────
		$( document ).on( 'change', '.cw-dual-color-toggle', function () {
			$( this ).closest( '.cw-swatch-color-fields' )
				.find( '.cw-secondary-color-row' )
				.toggle( this.checked );
		} );

		// Init on load (edit form)
		$( '.cw-dual-color-toggle' ).trigger( 'change' );

		// ── Image upload ───────────────────────────────────────────────────────
		$( document ).on( 'click', '.cw-swatch-upload-btn', function ( e ) {
			e.preventDefault();
			var $wrap    = $( this ).closest( '.cw-swatch-image-field' );
			var $input   = $wrap.find( '.cw-swatch-image-id' );
			var $preview = $wrap.find( '.cw-swatch-image-preview' );
			var $remove  = $wrap.find( '.cw-swatch-remove-btn' );

			var frame = wp.media( {
				title   : '<?php echo esc_js( __( 'Select swatch image', 'codeweber' ) ); ?>',
				button  : { text: '<?php echo esc_js( __( 'Use this image', 'codeweber' ) ); ?>' },
				multiple: false,
			} );

			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();
				$input.val( att.id );
				$preview.html( '<img src="' + att.url + '" style="max-width:50px;max-height:50px;border-radius:4px">' );
				$remove.show();
			} );

			frame.open();
		} );

		$( document ).on( 'click', '.cw-swatch-remove-btn', function ( e ) {
			e.preventDefault();
			var $wrap = $( this ).closest( '.cw-swatch-image-field' );
			$wrap.find( '.cw-swatch-image-id' ).val( '' );
			$wrap.find( '.cw-swatch-image-preview' ).html( '' );
			$( this ).hide();
		} );

	} );
	</script>
	<?php
} );

// =============================================================================
// ADMIN — PRODUCT ATTRIBUTE VALUES SELECTOR
// =============================================================================

/**
 * Render the Select2 terms dropdown for custom swatch types on the product
 * edit page. WooCommerce only renders this for 'select' type by default.
 *
 * @param stdClass             $attribute_taxonomy  Attribute taxonomy object.
 * @param int                  $i                   Loop index.
 * @param WC_Product_Attribute $attribute           Current attribute.
 */
add_action( 'woocommerce_product_option_terms', 'cw_swatches_product_option_terms', 10, 3 );

function cw_swatches_product_option_terms( $attribute_taxonomy, $i, $attribute ) {
	if ( ! in_array( $attribute_taxonomy->attribute_type, [ 'button', 'color', 'image' ], true ) ) {
		return;
	}

	$taxonomy  = wc_attribute_taxonomy_name( $attribute_taxonomy->attribute_name );
	$all_terms = get_terms( [
		'taxonomy'   => $taxonomy,
		'orderby'    => 'name',
		'hide_empty' => 0,
	] );
	?>
	<select multiple="multiple"
		data-placeholder="<?php esc_attr_e( 'Select terms', 'woocommerce' ); ?>"
		class="multiselect attribute_values wc-enhanced-select"
		name="attribute_values[<?php echo esc_attr( $i ); ?>][]">
		<?php if ( ! is_wp_error( $all_terms ) ) : ?>
			<?php foreach ( $all_terms as $term ) : ?>
				<option value="<?php echo esc_attr( $term->term_id ); ?>"
					<?php echo wc_selected( $term->term_id, $attribute->get_options() ); ?>>
					<?php echo esc_html( $term->name ); ?>
				</option>
			<?php endforeach; ?>
		<?php endif; ?>
	</select>
	<button class="button plus select_all_attributes">
		<?php esc_html_e( 'Select all', 'woocommerce' ); ?>
	</button>
	<button class="button minus select_none_attributes">
		<?php esc_html_e( 'Select none', 'woocommerce' ); ?>
	</button>
	<button class="button fr plus add_new_attribute">
		<?php esc_html_e( 'Add new', 'woocommerce' ); ?>
	</button>
	<?php
}

// =============================================================================
// ADMIN — TERM META FIELDS
// =============================================================================

/**
 * Register add/edit/save hooks for every PA_* taxonomy.
 * Runs on admin_init so all attribute taxonomies are already registered.
 */
add_action( 'admin_init', 'cw_swatches_register_term_hooks' );

function cw_swatches_register_term_hooks() {
	$attribute_taxonomies = wc_get_attribute_taxonomies();
	if ( empty( $attribute_taxonomies ) ) {
		return;
	}
	foreach ( $attribute_taxonomies as $tax ) {
		$taxonomy = wc_attribute_taxonomy_name( $tax->attribute_name );
		add_action( "{$taxonomy}_add_form_fields",  'cw_swatches_add_term_fields' );
		add_action( "{$taxonomy}_edit_form_fields", 'cw_swatches_edit_term_fields', 10, 2 );
		add_action( "created_{$taxonomy}",          'cw_swatches_save_term_meta' );
		add_action( "edited_{$taxonomy}",           'cw_swatches_save_term_meta' );
	}
}

/**
 * Render shared swatch fields (color picker or image uploader).
 *
 * @param int    $term_id  0 for "add new" form.
 * @param string $type     'color' | 'image' | 'button'.
 */
function cw_swatches_render_term_fields( $term_id, $type ) {
	$color      = $term_id ? (string) get_term_meta( $term_id, 'product_attribute_color', true ) : '';
	$sec_color  = $term_id ? (string) get_term_meta( $term_id, 'secondary_color', true ) : '';
	$is_dual    = $term_id && ( 'yes' === get_term_meta( $term_id, 'is_dual_color', true ) );
	$dual_angle = $term_id ? (int) get_term_meta( $term_id, 'dual_color_angle', true ) : 45;
	$dual_angle = $dual_angle ?: 45;
	$image_id   = $term_id ? (int) get_term_meta( $term_id, 'product_attribute_image', true ) : 0;
	$image_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_gallery_thumbnail' ) : '';

	if ( 'color' === $type ) {
		?>
		<div class="cw-swatch-color-fields">
			<p>
				<input type="text"
					name="product_attribute_color"
					class="cw-swatch-color-picker"
					value="<?php echo esc_attr( $color ); ?>"
					data-default-color="#ffffff">
			</p>
			<p style="margin-top:8px">
				<label style="font-weight:normal">
					<input type="checkbox"
						name="is_dual_color"
						class="cw-dual-color-toggle"
						value="yes"
						<?php checked( $is_dual ); ?>>
					<?php esc_html_e( 'Dual color (gradient)', 'codeweber' ); ?>
				</label>
			</p>
			<div class="cw-secondary-color-row" style="<?php echo $is_dual ? '' : 'display:none;'; ?>padding-left:4px;border-left:3px solid #ddd;margin-top:4px">
				<p>
					<label style="font-weight:normal;display:block;margin-bottom:4px"><?php esc_html_e( 'Second color', 'codeweber' ); ?></label>
					<input type="text"
						name="secondary_color"
						class="cw-swatch-color-picker"
						value="<?php echo esc_attr( $sec_color ); ?>"
						data-default-color="#000000">
				</p>
				<p>
					<label style="font-weight:normal;display:block;margin-bottom:4px"><?php esc_html_e( 'Gradient angle', 'codeweber' ); ?></label>
					<input type="number"
						name="dual_color_angle"
						value="<?php echo esc_attr( $dual_angle ); ?>"
						min="0" max="360"
						style="width:80px">
					<?php esc_html_e( '°', 'codeweber' ); ?>
				</p>
			</div>
		</div>
		<?php
	} elseif ( 'image' === $type ) {
		?>
		<div class="cw-swatch-image-field">
			<div class="cw-swatch-image-preview" style="margin-bottom:8px">
				<?php if ( $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" style="max-width:50px;max-height:50px;border-radius:4px">
				<?php endif; ?>
			</div>
			<input type="hidden"
				name="product_attribute_image"
				class="cw-swatch-image-id"
				value="<?php echo esc_attr( $image_id ); ?>">
			<button type="button" class="button cw-swatch-upload-btn">
				<?php esc_html_e( 'Upload image', 'codeweber' ); ?>
			</button>
			<button type="button"
				class="button cw-swatch-remove-btn"
				<?php echo $image_id ? '' : 'style="display:none"'; ?>>
				<?php esc_html_e( 'Remove', 'codeweber' ); ?>
			</button>
		</div>
		<?php
	}
	// 'button' type — no extra meta, term name is used as label
}

/**
 * "Add new term" form fields.
 *
 * @param string $taxonomy
 */
function cw_swatches_add_term_fields( $taxonomy ) {
	$type = cw_get_swatch_type( $taxonomy );
	if ( 'select' === $type || 'button' === $type ) {
		return;
	}
	?>
	<div class="form-field">
		<label><?php esc_html_e( 'Swatch', 'codeweber' ); ?></label>
		<?php cw_swatches_render_term_fields( 0, $type ); ?>
	</div>
	<?php
}

/**
 * "Edit term" form fields.
 *
 * @param WP_Term $term
 * @param string  $taxonomy
 */
function cw_swatches_edit_term_fields( $term, $taxonomy ) {
	$type = cw_get_swatch_type( $taxonomy );
	if ( 'select' === $type || 'button' === $type ) {
		return;
	}
	?>
	<tr class="form-field">
		<th scope="row">
			<label><?php esc_html_e( 'Swatch', 'codeweber' ); ?></label>
		</th>
		<td>
			<?php cw_swatches_render_term_fields( $term->term_id, $type ); ?>
		</td>
	</tr>
	<?php
}

/**
 * Save term meta on create/update.
 *
 * @param int $term_id
 */
function cw_swatches_save_term_meta( $term_id ) {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	// Color
	if ( isset( $_POST['product_attribute_color'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$color = sanitize_hex_color( wp_unslash( $_POST['product_attribute_color'] ) ); // phpcs:ignore
		if ( $color ) {
			update_term_meta( $term_id, 'product_attribute_color', $color );
		} else {
			delete_term_meta( $term_id, 'product_attribute_color' );
		}
	}

	// Dual color flag
	if ( array_key_exists( 'is_dual_color', $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		update_term_meta( $term_id, 'is_dual_color', 'yes' );
	} else {
		update_term_meta( $term_id, 'is_dual_color', 'no' );
	}

	// Secondary color
	if ( isset( $_POST['secondary_color'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$sec = sanitize_hex_color( wp_unslash( $_POST['secondary_color'] ) ); // phpcs:ignore
		update_term_meta( $term_id, 'secondary_color', $sec ?: '' );
	}

	// Gradient angle
	if ( isset( $_POST['dual_color_angle'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$angle = min( 360, max( 0, (int) $_POST['dual_color_angle'] ) ); // phpcs:ignore
		update_term_meta( $term_id, 'dual_color_angle', $angle );
	}

	// Image
	if ( isset( $_POST['product_attribute_image'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$image_id = absint( $_POST['product_attribute_image'] ); // phpcs:ignore
		if ( $image_id ) {
			update_term_meta( $term_id, 'product_attribute_image', $image_id );
		} else {
			delete_term_meta( $term_id, 'product_attribute_image' );
		}
	}
}

// =============================================================================
// HELPERS
// =============================================================================

/**
 * Get swatch type for a WooCommerce attribute taxonomy.
 *
 * @param  string $taxonomy  Full taxonomy name, e.g. 'pa_color'.
 * @return string            'button' | 'color' | 'image' | 'select'
 */
function cw_get_swatch_type( $taxonomy ) {
	foreach ( wc_get_attribute_taxonomies() as $tax ) {
		if ( wc_attribute_taxonomy_name( $tax->attribute_name ) === $taxonomy ) {
			return in_array( $tax->attribute_type, [ 'button', 'color', 'image' ], true )
				? $tax->attribute_type
				: 'select';
		}
	}
	return 'select';
}

/**
 * Return swatch visual data for a term.
 *
 * @param  int   $term_id
 * @return array { color, secondary, is_dual, dual_angle, image_id }
 */
function cw_get_term_swatch_data( $term_id ) {
	return [
		'color'      => (string) get_term_meta( $term_id, 'product_attribute_color', true ),
		'secondary'  => (string) get_term_meta( $term_id, 'secondary_color', true ),
		'is_dual'    => 'yes' === get_term_meta( $term_id, 'is_dual_color', true ),
		'dual_angle' => (int) get_term_meta( $term_id, 'dual_color_angle', true ) ?: 45,
		'image_id'   => (int) get_term_meta( $term_id, 'product_attribute_image', true ),
	];
}

/**
 * OOS swatch behavior from Redux option, or fallback.
 *
 * @return string 'blur' | 'cross' | 'hide'
 */
function cw_swatches_get_oos_behavior() {
	global $opt_name;
	if ( class_exists( 'Redux' ) && ! empty( $opt_name ) ) {
		$val = Redux::get_option( $opt_name, 'woo_swatch_oos_behavior', 'cross' );
		if ( in_array( $val, [ 'blur', 'cross', 'hide' ], true ) ) {
			return $val;
		}
	}
	return 'cross';
}

// =============================================================================
// FRONTEND — SWATCH RENDER
// =============================================================================

add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'cw_swatches_render_dropdown', 20, 2 );

/**
 * Replace WooCommerce <select> with swatch HTML.
 * The original <select> is kept hidden for WooCommerce JS compatibility.
 *
 * @param  string $html  Original select HTML from WooCommerce.
 * @param  array  $args  { attribute, product, selected, name, id, class, options, ... }
 * @return string
 */
function cw_swatches_render_dropdown( $html, $args ) {
	$taxonomy = $args['attribute']; // e.g. 'pa_color'
	$type     = cw_get_swatch_type( $taxonomy );

	// Leave standard selects and non-taxonomy attributes untouched
	if ( 'select' === $type || ! taxonomy_exists( $taxonomy ) ) {
		return $html;
	}

	$options  = $args['options'] ?? [];
	$product  = $args['product'] ?? null;
	$selected = $args['selected'] ?? '';
	// WooCommerce computes $name locally and does NOT store it back in $args,
	// so $args['name'] may be empty — mirror WooCommerce's own fallback (line 3541).
	$name     = ! empty( $args['name'] ) ? $args['name'] : 'attribute_' . sanitize_title( $taxonomy );

	if ( empty( $options ) || ! $product ) {
		return $html;
	}

	// Build term index by slug for fast lookup
	$terms         = wc_get_product_terms( $product->get_id(), $taxonomy, [ 'fields' => 'all' ] );
	$terms_by_slug = [];
	foreach ( $terms as $term ) {
		$terms_by_slug[ $term->slug ] = $term;
	}

	$oos_behavior = cw_swatches_get_oos_behavior();

	ob_start();
	?>
	<div class="cw-swatches cw-swatches--<?php echo esc_attr( $type ); ?> d-flex flex-wrap gap-2 align-items-center"
		data-attribute_name="<?php echo esc_attr( $name ); ?>"
		data-attribute_taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
		role="group"
		aria-label="<?php echo esc_attr( wc_attribute_label( $taxonomy, $product ) ); ?>">

		<?php foreach ( $options as $slug ) :
			$term      = $terms_by_slug[ $slug ] ?? null;
			$label     = $term ? $term->name : $slug;
			$term_id   = $term ? $term->term_id : 0;
			$data      = $term_id ? cw_get_term_swatch_data( $term_id ) : [];

			// is_disabled is determined by JS reactively after WooCommerce loads variation data.
			// We mark all as enabled initially; JS adds/removes .disabled on variation change.
			$is_selected = ( (string) $selected === (string) $slug );

			// Button swatches — Bootstrap .btn classes, no custom CSS needed.
			// Color/image swatches — theme .avatar + .w-8 .h-8 for size/shape,
			// only inline-style (background-color / background-image) added per-item.
			if ( 'button' === $type ) {
				$classes = [ 'cw-swatch', 'cw-swatch--button', 'btn', 'btn-sm', 'btn-outline-primary' ];
				if ( $is_selected ) {
					$classes[] = 'selected';
					$classes[] = 'active';
				}
			} else {
				$classes = [ 'cw-swatch', 'cw-swatch--' . $type, 'avatar', 'w-8', 'h-8' ];
				if ( $is_selected ) {
					$classes[] = 'selected';
				}
			}

			// Build inline style safely
			$inline_style = '';
			if ( 'color' === $type && ! empty( $data['color'] ) ) {
				$c = sanitize_hex_color( $data['color'] );
				if ( $c ) {
					if ( $data['is_dual'] && ! empty( $data['secondary'] ) ) {
						$c2    = sanitize_hex_color( $data['secondary'] ) ?: '#000000';
						$angle = absint( $data['dual_angle'] );
						$inline_style = sprintf(
							'background:linear-gradient(%ddeg,%s 50%%,%s 50%%)',
							$angle, $c, $c2
						);
					} else {
						$inline_style = 'background-color:' . $c;
					}
				}
			} elseif ( 'image' === $type && ! empty( $data['image_id'] ) ) {
				$img_url = wp_get_attachment_image_url( (int) $data['image_id'], 'woocommerce_gallery_thumbnail' );
				if ( $img_url ) {
					$inline_style = 'background-image:url(' . esc_url( $img_url ) . ');background-size:cover;background-position:center';
				}
			}
			?>
			<span class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
				data-value="<?php echo esc_attr( $slug ); ?>"
				title="<?php echo esc_attr( $label ); ?>"
				aria-label="<?php echo esc_attr( $label ); ?>"
				aria-pressed="<?php echo $is_selected ? 'true' : 'false'; ?>"
				role="button"
				tabindex="0"
				<?php if ( $inline_style ) : ?>style="<?php echo $inline_style; // Safe: built from sanitize_hex_color + esc_url + sprintf integers ?>"<?php endif; ?>>
				<?php
				// Button type: show label text
				// Color/image with fallback: show label if no visual data
				if ( 'button' === $type ) {
					echo esc_html( $label );
				} elseif ( 'image' === $type && empty( $data['image_id'] ) ) {
					echo esc_html( $label );
				} elseif ( 'color' === $type && empty( $data['color'] ) ) {
					echo esc_html( $label );
				}
				?>
			</span>
		<?php endforeach; ?>

	</div>
	<?php

	// Keep original <select> hidden — WooCommerce variation JS relies on it.
	// Our JS reads this select's value and updates it on swatch click.
	return ob_get_clean() . '<span class="cw-swatches-select-hidden">' . $html . '</span>';
}
