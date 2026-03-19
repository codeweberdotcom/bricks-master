<?php
/**
 * Product Card: shop2
 *
 * Стиль из shop2.html — isotope grid, figure с оверлеями.
 * Используется через content-product.php (WooCommerce loop dispatcher).
 *
 * Доступно: global $product (WC_Product), стандартный WP loop уже вызван.
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product || ! $product->is_visible() ) {
	return;
}

$product_id  = $product->get_id();
$product_url = get_permalink( $product_id );

// Изображение
$image_html = $product->get_image( 'woocommerce_thumbnail', [ 'class' => '' ] );

// Второе фото из галереи (для hover-свопа)
$hover_img_html  = '';
$gallery_ids     = $product->get_gallery_image_ids();
if ( ! empty( $gallery_ids ) ) {
	$hover_img_html = wp_get_attachment_image(
		$gallery_ids[0],
		'woocommerce_thumbnail',
		false,
		[ 'class' => 'product-hover-img', 'alt' => '' ]
	);
}

// Категория (верхнеуровневая)
$categories    = get_the_terms( $product_id, 'product_cat' );
$category_name = '';
if ( $categories && ! is_wp_error( $categories ) ) {
	$top   = array_filter( $categories, fn( $t ) => 0 === $t->parent );
	$first = $top ? array_values( $top )[0] : $categories[0];
	$category_name = $first->name;
}

// Рейтинг → CSS-класс (one / two / ... / five)
$rating_words = [ '', 'one', 'two', 'three', 'four', 'five' ];
$rating_index = min( 5, max( 0, round( (float) $product->get_average_rating() ) ) );
$rating_word  = $rating_words[ $rating_index ];

// Корзина
$add_to_cart_url  = $product->add_to_cart_url();
$add_to_cart_text = $product->add_to_cart_text();
$is_simple        = $product->is_type( 'simple' );

// Значок Sale / New
$badge = '';
if ( $product->is_on_sale() ) {
	$badge = '<span class="avatar bg-pink text-white w-10 h-10 position-absolute text-uppercase fs-13" style="top:1rem;left:1rem;"><span>'
		. esc_html__( 'Sale!', 'woocommerce' )
		. '</span></span>';
} elseif ( $product->is_featured() ) {
	$badge = '<span class="avatar bg-aqua text-white w-10 h-10 position-absolute text-uppercase fs-13" style="top:1rem;left:1rem;"><span>'
		. esc_html__( 'New!', 'codeweber' )
		. '</span></span>';
}
?>

<div id="product-<?php echo esc_attr( $product_id ); ?>" class="project item col">

	<figure class="rounded mb-6">

		<a href="<?php echo esc_url( $product_url ); ?>"><?php echo $image_html; ?></a>

		<?php if ( $hover_img_html ) : ?>
			<?php echo $hover_img_html; ?>
		<?php endif; ?>

		<?php
		$cw_in_wishlist = function_exists( 'cw_get_wishlist_url' ) && class_exists( 'CW_Wishlist_Item' );
		$cw_active      = false;
		if ( $cw_in_wishlist ) {
			global $cw_wishlist_instance;
			if ( $cw_wishlist_instance instanceof CW_Wishlist_Item ) {
				$cw_active = $cw_wishlist_instance->is_in_wishlist( $product_id );
			}
		}
		$cw_wl_href  = $cw_active ? esc_url( cw_get_wishlist_url() ) : '#';
		$cw_wl_class = 'item-like cw-wishlist-btn' . ( $cw_active ? ' cw-wishlist-btn--active' : '' );
		$cw_wl_title = $cw_active ? __( 'В избранном', 'codeweber' ) : __( 'В избранное', 'codeweber' );
		?>
		<a class="<?php echo esc_attr( $cw_wl_class ); ?>"
		   href="<?php echo $cw_wl_href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
		   data-product-id="<?php echo esc_attr( $product_id ); ?>"
		   data-bs-toggle="white-tooltip"
		   title="<?php echo esc_attr( $cw_wl_title ); ?>"
		   aria-label="<?php echo esc_attr( $cw_wl_title ); ?>">
			<i class="uil uil-heart"></i>
		</a>

		<a class="item-view" href="<?php echo esc_url( $product_url ); ?>"
		   data-product-id="<?php echo esc_attr( $product_id ); ?>"
		   data-bs-toggle="white-tooltip"
		   title="<?php esc_attr_e( 'Quick view', 'codeweber' ); ?>">
			<i class="uil uil-eye"></i>
		</a>

		<?php if ( $is_simple ) : ?>
			<a href="<?php echo esc_url( $add_to_cart_url ); ?>"
			   class="item-cart ajax_add_to_cart"
			   data-product_id="<?php echo esc_attr( $product_id ); ?>"
			   data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
			   rel="nofollow">
				<i class="uil uil-shopping-bag"></i>
				<?php echo esc_html( $add_to_cart_text ); ?>
			</a>
		<?php else : ?>
			<a href="<?php echo esc_url( $product_url ); ?>" class="item-cart">
				<i class="uil uil-shopping-bag"></i>
				<?php echo esc_html( $add_to_cart_text ); ?>
			</a>
		<?php endif; ?>

		<?php echo $badge; ?>

	</figure>

	<div class="post-header">

		<div class="d-flex flex-row align-items-center justify-content-between mb-2">
			<div class="post-category text-ash mb-0"><?php echo esc_html( $category_name ); ?></div>
			<?php if ( $rating_word ) : ?>
				<span class="ratings <?php echo esc_attr( $rating_word ); ?>"></span>
			<?php endif; ?>
		</div>

		<h2 class="post-title h3 fs-22">
			<a href="<?php echo esc_url( $product_url ); ?>" class="link-dark">
				<?php echo esc_html( $product->get_name() ); ?>
			</a>
		</h2>

		<p class="price"><?php echo $product->get_price_html(); ?></p>

	</div>
	<!-- /.post-header -->

</div>
<!-- /.item -->
