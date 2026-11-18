<?php
/**
 * Order rules: admin UI (product data tab, product category fields) and saving.
 *
 * @package codeweber
 */

defined( 'ABSPATH' ) || exit;

// ─── Product data tab ──────────────────────────────────────────────

add_filter( 'woocommerce_product_data_tabs', 'codeweber_order_rules_product_tab' );
function codeweber_order_rules_product_tab( array $tabs ): array {
	$tabs['codeweber_order_rules'] = [
		'label'    => esc_html__( 'Order rules', 'codeweber' ),
		'target'   => 'codeweber_order_rules_data',
		'class'    => [ 'show_if_simple', 'show_if_variable' ],
		'priority' => 75,
	];
	return $tabs;
}

add_action( 'woocommerce_product_data_panels', 'codeweber_order_rules_product_panel' );
function codeweber_order_rules_product_panel(): void {
	global $post;

	$post_id = $post ? (int) $post->ID : 0;
	$mode    = get_post_meta( $post_id, CODEWEBER_ORDER_RULES_KEY_MODE, true );
	if ( ! in_array( $mode, codeweber_order_rules_modes(), true ) ) {
		$mode = 'inherit';
	}
	?>
	<div id="codeweber_order_rules_data" class="panel woocommerce_options_panel hidden">
		<?php wp_nonce_field( 'codeweber_order_rules_product', 'codeweber_order_rules_nonce' ); ?>

		<div class="options_group">
			<?php
			woocommerce_wp_select(
				[
					'id'          => CODEWEBER_ORDER_RULES_KEY_MODE,
					'label'       => esc_html__( 'Rule for this product', 'codeweber' ),
					'value'       => $mode,
					'options'     => [
						'inherit' => esc_html__( 'Inherit (category, then global settings)', 'codeweber' ),
						'custom'  => esc_html__( 'Custom rule for this product', 'codeweber' ),
						'none'    => esc_html__( 'No restrictions', 'codeweber' ),
					],
					'desc_tip'    => true,
					'description' => esc_html__( 'Inherit takes the rule of the product category (the deepest one that has a rule) or the global settings.', 'codeweber' ),
				]
			);
			?>
		</div>

		<div class="options_group codeweber-order-rules-custom">
			<?php
			codeweber_order_rules_number_field( CODEWEBER_ORDER_RULES_KEY_MIN, esc_html__( 'Minimum quantity', 'codeweber' ), esc_html__( '0 means no minimum.', 'codeweber' ) );
			codeweber_order_rules_number_field( CODEWEBER_ORDER_RULES_KEY_STEP, esc_html__( 'Quantity step', 'codeweber' ), esc_html__( 'Sold in multiples of this number, for example 5. 0 or 1 means any quantity.', 'codeweber' ) );
			codeweber_order_rules_number_field( CODEWEBER_ORDER_RULES_KEY_MAX, esc_html__( 'Maximum quantity', 'codeweber' ), esc_html__( '0 means no maximum.', 'codeweber' ) );
			?>
		</div>

		<div class="options_group">
			<p class="form-field">
				<label><?php esc_html_e( 'Effective rule (saved)', 'codeweber' ); ?></label>
				<span class="description"><?php echo esc_html( codeweber_order_rules_describe( codeweber_order_rules_get( $post_id ) ) ); ?></span>
			</p>
		</div>
	</div>
	<?php
}

/**
 * Number input of the product panel (value read from the product meta).
 *
 * @param string $key         Meta key.
 * @param string $label       Label.
 * @param string $description Tooltip text.
 */
function codeweber_order_rules_number_field( string $key, string $label, string $description ): void {
	woocommerce_wp_text_input(
		[
			'id'                => $key,
			'label'             => $label,
			'type'              => 'number',
			'desc_tip'          => true,
			'description'       => $description,
			'custom_attributes' => [
				'min'  => '0',
				'step' => '1',
			],
		]
	);
}

/**
 * Human readable description of an effective rule.
 *
 * @param array $rules Result of codeweber_order_rules_get().
 * @return string
 */
function codeweber_order_rules_describe( array $rules ): string {
	if ( empty( $rules['active'] ) ) {
		return __( 'No restrictions apply.', 'codeweber' );
	}

	$parts = [];
	/* translators: %d: quantity */
	$parts[] = sprintf( __( 'minimum %d', 'codeweber' ), $rules['min'] );
	if ( $rules['step'] > 1 ) {
		/* translators: %d: quantity step */
		$parts[] = sprintf( __( 'step %d', 'codeweber' ), $rules['step'] );
	}
	if ( $rules['max'] > 0 ) {
		/* translators: %d: quantity */
		$parts[] = sprintf( __( 'maximum %d', 'codeweber' ), $rules['max'] );
	}

	switch ( $rules['source'] ) {
		case 'product':
			$source = __( 'this product', 'codeweber' );
			break;
		case 'category':
			$term   = get_term( (int) $rules['source_id'], 'product_cat' );
			$source = ( $term && ! is_wp_error( $term ) )
				/* translators: %s: category name */
				? sprintf( __( 'category “%s”', 'codeweber' ), $term->name )
				: __( 'category', 'codeweber' );
			break;
		case 'global':
			$source = __( 'global settings', 'codeweber' );
			break;
		default:
			$source = __( 'filter', 'codeweber' );
	}

	/* translators: 1: rule, 2: where the rule comes from */
	return sprintf( __( '%1$s (from: %2$s)', 'codeweber' ), implode( ', ', $parts ), $source );
}

add_action( 'woocommerce_process_product_meta', 'codeweber_order_rules_save_product', 20 );
function codeweber_order_rules_save_product( $post_id ): void {
	$post_id = (int) $post_id;

	$nonce = isset( $_POST['codeweber_order_rules_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['codeweber_order_rules_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'codeweber_order_rules_product' ) ) {
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

	$mode = isset( $_POST[ CODEWEBER_ORDER_RULES_KEY_MODE ] ) ? sanitize_key( wp_unslash( $_POST[ CODEWEBER_ORDER_RULES_KEY_MODE ] ) ) : 'inherit';
	if ( ! in_array( $mode, codeweber_order_rules_modes(), true ) ) {
		$mode = 'inherit';
	}
	update_post_meta( $post_id, CODEWEBER_ORDER_RULES_KEY_MODE, $mode );

	foreach ( [ CODEWEBER_ORDER_RULES_KEY_MIN, CODEWEBER_ORDER_RULES_KEY_STEP, CODEWEBER_ORDER_RULES_KEY_MAX ] as $key ) {
		$value = isset( $_POST[ $key ] ) ? absint( wp_unslash( $_POST[ $key ] ) ) : 0;
		update_post_meta( $post_id, $key, $value );
	}
}

// ─── Product category fields ───────────────────────────────────────

add_action( 'product_cat_add_form_fields', 'codeweber_order_rules_term_add_fields' );
function codeweber_order_rules_term_add_fields(): void {
	wp_nonce_field( 'codeweber_order_rules_term', 'codeweber_order_rules_term_nonce' );
	?>
	<div class="form-field">
		<h3><?php esc_html_e( 'Order rules', 'codeweber' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Quantity rule for all products of this category (subcategories inherit it unless they have their own). 0 means not set.', 'codeweber' ); ?></p>
	</div>
	<?php
	foreach ( codeweber_order_rules_term_fields() as $key => $label ) :
		?>
		<div class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
			<input type="number" min="0" step="1" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="0" />
		</div>
		<?php
	endforeach;
}

add_action( 'product_cat_edit_form_fields', 'codeweber_order_rules_term_edit_fields' );
function codeweber_order_rules_term_edit_fields( $term ): void {
	$rule = codeweber_order_rules_read( (int) $term->term_id, 'term' );
	$map  = [
		CODEWEBER_ORDER_RULES_KEY_MIN  => $rule['min'],
		CODEWEBER_ORDER_RULES_KEY_STEP => $rule['step'],
		CODEWEBER_ORDER_RULES_KEY_MAX  => $rule['max'],
	];
	wp_nonce_field( 'codeweber_order_rules_term', 'codeweber_order_rules_term_nonce' );
	?>
	<tr class="form-field">
		<th scope="row"><h3><?php esc_html_e( 'Order rules', 'codeweber' ); ?></h3></th>
		<td><p class="description"><?php esc_html_e( 'Quantity rule for all products of this category (subcategories inherit it unless they have their own). 0 means not set.', 'codeweber' ); ?></p></td>
	</tr>
	<?php
	foreach ( codeweber_order_rules_term_fields() as $key => $label ) :
		?>
		<tr class="form-field">
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td><input type="number" min="0" step="1" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( (string) $map[ $key ] ); ?>" /></td>
		</tr>
		<?php
	endforeach;
}

/**
 * Labels of the category rule fields.
 *
 * @return array<string,string> Meta key => label.
 */
function codeweber_order_rules_term_fields(): array {
	return [
		CODEWEBER_ORDER_RULES_KEY_MIN  => esc_html__( 'Minimum quantity', 'codeweber' ),
		CODEWEBER_ORDER_RULES_KEY_STEP => esc_html__( 'Quantity step', 'codeweber' ),
		CODEWEBER_ORDER_RULES_KEY_MAX  => esc_html__( 'Maximum quantity', 'codeweber' ),
	];
}

add_action( 'created_product_cat', 'codeweber_order_rules_save_term' );
add_action( 'edited_product_cat', 'codeweber_order_rules_save_term' );
function codeweber_order_rules_save_term( $term_id ): void {
	$term_id = (int) $term_id;

	$nonce = isset( $_POST['codeweber_order_rules_term_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['codeweber_order_rules_term_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'codeweber_order_rules_term' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_term', $term_id ) ) {
		return;
	}

	foreach ( array_keys( codeweber_order_rules_term_fields() ) as $key ) {
		$value = isset( $_POST[ $key ] ) ? absint( wp_unslash( $_POST[ $key ] ) ) : 0;
		if ( $value > 0 ) {
			update_term_meta( $term_id, $key, $value );
		} else {
			delete_term_meta( $term_id, $key );
		}
	}
}

// ─── Small admin script: show custom fields only for the "custom" mode ───

add_action( 'admin_enqueue_scripts', 'codeweber_order_rules_admin_assets' );
function codeweber_order_rules_admin_assets( $hook ): void {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'product' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_script(
		'codeweber-order-rules-admin',
		get_template_directory_uri() . '/functions/woocommerce/order-rules/order-rules-admin.js',
		[ 'jquery' ],
		wp_get_theme()->get( 'Version' ),
		true
	);
}
