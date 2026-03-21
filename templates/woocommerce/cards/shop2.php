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

// Значок Sale / New — настройки из Redux
$cw_opts         = get_option( 'redux_demo', array() );
$badge_shape_map = array(
	'1' => 'rounded-pill',
	'2' => 'rounded',
	'3' => 'rounded-3',
	'4' => 'rounded-0',
);
$use_theme_shape = ! isset( $cw_opts['woo_badge_shape_use_theme'] ) || (bool) $cw_opts['woo_badge_shape_use_theme'];
$shape_key       = $use_theme_shape
	? ( $cw_opts['opt_button_select_style'] ?? '1' )
	: ( $cw_opts['woo_badge_shape'] ?? '1' );
$badge_shape     = $badge_shape_map[ $shape_key ] ?? 'rounded-pill';
$badge_position = ( isset( $cw_opts['woo_badge_position'] ) && $cw_opts['woo_badge_position'] === 'top-right' )
	? 'top:1rem;right:1rem;'
	: 'top:1rem;left:1rem;';

$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';

$badge           = '';
$sale_badge_on   = ! isset( $cw_opts['woo_badge_sale_enable'] ) || (bool) $cw_opts['woo_badge_sale_enable'];
$new_badge_on    = ! isset( $cw_opts['woo_badge_new_enable'] ) || (bool) $cw_opts['woo_badge_new_enable'];

if ( $sale_badge_on && $product->is_on_sale() ) {
	$bg         = ! empty( $cw_opts['woo_badge_sale_bg'] ) ? $cw_opts['woo_badge_sale_bg'] : '#d16b86';
	$color      = ! empty( $cw_opts['woo_badge_sale_color'] ) ? $cw_opts['woo_badge_sale_color'] : '#ffffff';
	$sale_type  = $cw_opts['woo_badge_sale_type'] ?? 'text';

	if ( 'percent' === $sale_type ) {
		$percent = 0;
		if ( $product->is_type( 'variable' ) ) {
			$regular = (float) $product->get_variation_regular_price( 'max' );
			$sale    = (float) $product->get_variation_sale_price( 'min' );
			if ( $regular > 0 ) {
				$percent = round( ( $regular - $sale ) / $regular * 100 );
			}
		} else {
			$regular = (float) $product->get_regular_price();
			$sale    = (float) $product->get_sale_price();
			if ( $regular > 0 ) {
				$percent = round( ( $regular - $sale ) / $regular * 100 );
			}
		}
		$text = $percent > 0 ? '−' . $percent . '%' : ( ! empty( $cw_opts['woo_badge_sale_text'] ) ? $cw_opts['woo_badge_sale_text'] : __( 'Распродажа!', 'codeweber' ) );
	} else {
		$text = ! empty( $cw_opts['woo_badge_sale_text'] ) ? $cw_opts['woo_badge_sale_text'] : __( 'Распродажа!', 'codeweber' );
	}

	$badge = '<span class="' . esc_attr( $badge_shape ) . ' w-10 h-10 position-absolute text-uppercase fs-13 d-flex align-items-center justify-content-center text-center lh-sm" style="' . esc_attr( $badge_position ) . 'background-color:' . esc_attr( $bg ) . ';color:' . esc_attr( $color ) . ';"><span>' . esc_html( $text ) . '</span></span>';
} elseif ( $new_badge_on && $product->is_featured() ) {
	$bg    = ! empty( $cw_opts['woo_badge_new_bg'] ) ? $cw_opts['woo_badge_new_bg'] : '#54a8c7';
	$color = ! empty( $cw_opts['woo_badge_new_color'] ) ? $cw_opts['woo_badge_new_color'] : '#ffffff';
	$text  = ! empty( $cw_opts['woo_badge_new_text'] ) ? $cw_opts['woo_badge_new_text'] : __( 'Новинка!', 'codeweber' );
	$badge = '<span class="' . esc_attr( $badge_shape ) . ' w-10 h-10 position-absolute text-uppercase fs-13 d-flex align-items-center justify-content-center text-center lh-sm" style="' . esc_attr( $badge_position ) . 'background-color:' . esc_attr( $bg ) . ';color:' . esc_attr( $color ) . ';"><span>' . esc_html( $text ) . '</span></span>';
}
?>


<?php
// Wishlist page mode: adds cw-wishlist-card class, data-product-id, fixed col sizes.
$cw_wl_mode  = ! empty( $GLOBALS['cw_wishlist_render'] );
$cw_col      = $cw_wl_mode ? 'col-6 col-md-4 col-xl-3 cw-wishlist-card' : 'col';
$cw_wl_attr  = $cw_wl_mode ? ' data-product-id="' . esc_attr( $product_id ) . '"' : '';
?>
<div id="product-<?php echo esc_attr( $product_id ); ?>" class="project item <?php echo esc_attr( $cw_col ); ?>"<?php echo $cw_wl_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

<?php if ( $cw_wl_mode ) : ?>
<div class="card <?php echo esc_attr( $card_radius ); ?> p-3">
	<a href="#"
	   class="cw-wishlist-remove ms-auto mb-2 lh-1 text-ash"
	   data-product-id="<?php echo esc_attr( $product_id ); ?>"
	   data-bs-toggle="tooltip"
	   data-bs-placement="left"
	   title="<?php esc_attr_e( 'Удалить', 'codeweber' ); ?>"
	   aria-label="<?php esc_attr_e( 'Удалить', 'codeweber' ); ?>"
	><i class="uil uil-times"></i></a>
<?php endif; ?>

	<figure class="<?php echo esc_attr( $card_radius ); ?> mb-6">

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
		$cw_wl_title = $cw_active ? __( 'In Wishlist', 'codeweber' ) : __( 'Add to Wishlist', 'codeweber' );
		?>
		<a class="<?php echo esc_attr( $cw_wl_class ); ?>"
		   href="<?php echo $cw_wl_href; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
		   data-product-id="<?php echo esc_attr( $product_id ); ?>"
		   data-bs-toggle="white-tooltip"
		   title="<?php echo esc_attr( $cw_wl_title ); ?>"
		   aria-label="<?php echo esc_attr( $cw_wl_title ); ?>">
			<span class="cw-wishlist-icon"><i class="uil uil-heart"></i></span>
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

<?php if ( $cw_wl_mode ) : ?></div><!-- /.card --><?php endif; ?>

</div>
<!-- /.item -->
