<?php
/**
 * Variable product add to cart
 *
 * Переопределяет single-product/add-to-cart/variable.php.
 * Заменяет <table class="variations"> на <fieldset> с <legend> — как в образце.
 *
 * @version 9.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

do_action( 'woocommerce_before_add_to_cart_form' );
?>

<form class="variations_form cart"
	action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>"
	method="post"
	enctype="multipart/form-data"
	data-product_id="<?php echo absint( $product->get_id() ); ?>"
	data-product_variations="<?php echo $variations_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">

	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
	<?php else : ?>

		<div class="variations">
			<?php foreach ( $attributes as $attribute_name => $options ) : ?>
			<fieldset class="variation-field mb-4">
				<legend class="h6 fs-16 text-body mb-3">
					<?php echo wc_attribute_label( $attribute_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</legend>

				<?php
				wc_dropdown_variation_attribute_options(
					array(
						'options'   => $options,
						'attribute' => $attribute_name,
						'product'   => $product,
					)
				);

				echo end( $attribute_keys ) === $attribute_name
					? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#" aria-label="' . esc_attr__( 'Очистить опции', 'woocommerce' ) . '">' . esc_html__( 'Очистить', 'woocommerce' ) . '</a>' ) )
					: '';
				?>
			</fieldset>
			<?php endforeach; ?>
		</div>

		<div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>

		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<div class="single_variation_wrap">
			<?php
			do_action( 'woocommerce_before_single_variation' );
			do_action( 'woocommerce_single_variation' );
			do_action( 'woocommerce_after_single_variation' );
			?>
		</div>

	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php do_action( 'woocommerce_after_add_to_cart_form' );
