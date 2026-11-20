<?php
/**
 * Product page templates: choose a template for a single product.
 *
 * A product can use an alternative page layout instead of the default
 * woocommerce/single-product.php. The choice is stored in the product meta
 * `_cw_product_template` (a slug) and edited in the "Product template" box of
 * the product edit screen.
 *
 * - Registry: codeweber_product_templates() (slug => label), filter 'codeweber_product_templates'.
 * - Files: woocommerce/single-product-templates/{slug}.php (child theme first, then parent).
 * - Empty, unknown or missing-file value => the default single-product.php is used.
 * - Public API: codeweber_get_product_template( $product_id ) returns the active slug or ''.
 *
 * @package codeweber
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'CODEWEBER_PRODUCT_TEMPLATE_KEY' ) ) {
	define( 'CODEWEBER_PRODUCT_TEMPLATE_KEY', '_cw_product_template' );
}

/**
 * Registered product page templates.
 *
 * @return array<string,string> Slug => label.
 */
function codeweber_product_templates(): array {
	// No built-in templates: themes register theirs with this filter, for example
	// $templates['my-layout'] = __( 'My layout', 'my-theme-domain' );
	// and put the file in woocommerce/single-product-templates/my-layout.php.
	$templates = apply_filters( 'codeweber_product_templates', [] );

	$clean = [];
	foreach ( (array) $templates as $slug => $label ) {
		$slug = sanitize_key( (string) $slug );
		if ( '' !== $slug && is_string( $label ) && '' !== $label ) {
			$clean[ $slug ] = $label;
		}
	}

	return $clean;
}

/**
 * Absolute path of a template file (child theme overrides parent), or '' when missing.
 *
 * @param string $slug Template slug.
 * @return string
 */
function codeweber_product_template_file( string $slug ): string {
	if ( '' === $slug ) {
		return '';
	}

	return (string) locate_template( 'woocommerce/single-product-templates/' . $slug . '.php' );
}

/**
 * Template slug that is active for a product, or '' for the default layout.
 *
 * @param int $product_id Product ID (0 = current post).
 * @return string
 */
function codeweber_get_product_template( $product_id = 0 ): string {
	$product_id = $product_id ? (int) $product_id : (int) get_the_ID();
	if ( $product_id <= 0 ) {
		return '';
	}

	$slug = sanitize_key( (string) get_post_meta( $product_id, CODEWEBER_PRODUCT_TEMPLATE_KEY, true ) );
	if ( '' === $slug || ! isset( codeweber_product_templates()[ $slug ] ) ) {
		return '';
	}

	return '' !== codeweber_product_template_file( $slug ) ? $slug : '';
}

// ─── Frontend: load the chosen template ────────────────────────────

add_filter( 'woocommerce_template_loader_files', 'codeweber_product_template_loader_files', 10, 2 );
/**
 * Put the chosen template first in the files WooCommerce looks for.
 *
 * @param array  $templates    Candidate files.
 * @param string $default_file WooCommerce default file for the request.
 * @return array
 */
function codeweber_product_template_loader_files( $templates, $default_file ) {
	if ( 'single-product.php' !== $default_file || ! is_singular( 'product' ) ) {
		return $templates;
	}

	$slug = codeweber_get_product_template( get_queried_object_id() );
	if ( '' !== $slug ) {
		array_unshift( $templates, 'woocommerce/single-product-templates/' . $slug . '.php' );
	}

	return $templates;
}

add_filter( 'body_class', 'codeweber_product_template_body_class' );
/**
 * Body class cw-product-template-{slug} for styling.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function codeweber_product_template_body_class( $classes ) {
	if ( is_singular( 'product' ) ) {
		$slug = codeweber_get_product_template( get_queried_object_id() );
		if ( '' !== $slug ) {
			$classes[] = 'cw-product-template-' . $slug;
		}
	}

	return $classes;
}

// ─── Admin: "Product template" box ─────────────────────────────────

add_action( 'add_meta_boxes_product', 'codeweber_product_template_add_metabox' );
function codeweber_product_template_add_metabox(): void {
	if ( empty( codeweber_product_templates() ) ) {
		return;
	}

	add_meta_box(
		'codeweber_product_template',
		esc_html__( 'Product template', 'codeweber' ),
		'codeweber_product_template_metabox',
		'product',
		'side',
		'default'
	);
}

/**
 * @param WP_Post $post Product being edited.
 */
function codeweber_product_template_metabox( $post ): void {
	$current = codeweber_get_product_template( (int) $post->ID );

	wp_nonce_field( 'codeweber_product_template', 'codeweber_product_template_nonce' );
	?>
	<p>
		<label class="screen-reader-text" for="codeweber_product_template"><?php esc_html_e( 'Product template', 'codeweber' ); ?></label>
		<select name="<?php echo esc_attr( CODEWEBER_PRODUCT_TEMPLATE_KEY ); ?>" id="codeweber_product_template" class="widefat">
			<option value=""><?php esc_html_e( 'Default Template', 'codeweber' ); ?></option>
			<?php foreach ( codeweber_product_templates() as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>"<?php selected( $current, $slug ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="description"><?php esc_html_e( 'Page layout of this product. The default template is the standard product page.', 'codeweber' ); ?></p>
	<?php
}

add_action( 'save_post_product', 'codeweber_product_template_save' );
/**
 * @param int $post_id Product ID.
 */
function codeweber_product_template_save( $post_id ): void {
	$post_id = (int) $post_id;

	$nonce = isset( $_POST['codeweber_product_template_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['codeweber_product_template_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'codeweber_product_template' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'product' !== get_post_type( $post_id ) ) {
		return;
	}

	$slug = isset( $_POST[ CODEWEBER_PRODUCT_TEMPLATE_KEY ] ) ? sanitize_key( wp_unslash( $_POST[ CODEWEBER_PRODUCT_TEMPLATE_KEY ] ) ) : '';

	if ( '' !== $slug && isset( codeweber_product_templates()[ $slug ] ) ) {
		update_post_meta( $post_id, CODEWEBER_PRODUCT_TEMPLATE_KEY, $slug );
	} else {
		delete_post_meta( $post_id, CODEWEBER_PRODUCT_TEMPLATE_KEY );
	}
}
