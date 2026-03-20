<?php
/**
 * Single Product Rating
 *
 * Переопределяет single-product/rating.php.
 * Использует классы .ratings-wrapper / .ratings темы вместо WC-звёзд.
 *
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! wc_review_ratings_enabled() ) {
	return;
}

$rating_count = $product->get_rating_count();
$review_count = $product->get_review_count();
$average      = $product->get_average_rating();

if ( $rating_count > 0 ) :

	$star_words  = [ 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five' ];
	$star_class  = $star_words[ min( 5, max( 1, (int) round( (float) $average ) ) ) ] ?? 'five';
	?>

	<a href="#reviews"
	   class="link-body ratings-wrapper d-inline-flex align-items-center gap-2 mb-2"
	   rel="nofollow">
		<span class="ratings <?php echo esc_attr( $star_class ); ?>"
		      role="img"
		      aria-label="<?php echo esc_attr( sprintf( __( 'Rated %s out of 5', 'woocommerce' ), $average ) ); ?>">
		</span>
		<?php if ( comments_open() ) : ?>
		<span>
			(<?php
			printf(
				/* translators: %s: reviews count */
				_n( '%s customer review', '%s customer reviews', $review_count, 'woocommerce' ),
				'<span class="count">' . esc_html( $review_count ) . '</span>'
			);
			?>)
		</span>
		<?php endif; ?>
	</a>

<?php endif; ?>
