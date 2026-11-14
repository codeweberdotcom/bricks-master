<?php
/**
 * Product Card: shop-list
 *
 * Горизонтальная карточка: фото слева, контент справа.
 * Контент: категория / рейтинг, название, описание (краткое, иначе начало полного),
 * ряд миниатюр галереи, до 3 характеристик (видимые атрибуты WC), цена.
 * Кнопка: корзина (если включена в Redux) либо «Подробнее».
 * Стиль карточки: Redux woo_list_card_style (light | dark).
 * На мобильном стакается вертикально через .card-horizontal CSS.
 * Иконки wishlist / quick view / compare появляются при hover на фото.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

require __DIR__ . '/_common.php';

if ( ! isset( $product_id ) ) {
	return;
}

$figure_radius = $card_radius && $card_radius !== 'rounded-0' ? ' rounded-start' : ( $card_radius ? ' ' . trim( $card_radius ) : '' );

// Стиль карточки (Redux woo_list_card_style): light | dark.
$cw_list_dark        = isset( $cw_opts['woo_list_card_style'] ) && 'dark' === $cw_opts['woo_list_card_style'];
$cw_list_card_class  = 'card card-horizontal cw-list-card' . ( $card_radius ? ' ' . $card_radius : '' ) . ( $cw_list_dark ? ' cw-list-card--dark' : '' );
$cw_list_title_class = $cw_list_dark ? 'text-white' : 'link-dark text-dark';
// Явный цвет текста: на странице с text-inverse (светлый текст для тёмного фона) всё наследуемое было бы светлым на белой карточке.
$cw_list_fg_class    = $cw_list_dark ? 'text-white' : 'text-dark';

// Описание: краткое, а если его нет — начало полного.
$cw_list_text = $product->get_short_description();
if ( '' === trim( wp_strip_all_tags( $cw_list_text ) ) ) {
	$cw_list_text = $product->get_description();
}
$cw_list_text = $cw_list_text ? wp_trim_words( wp_strip_all_tags( strip_shortcodes( $cw_list_text ) ), 20 ) : '';

// Миниатюры галереи (до 4) и счётчик остальных.
$cw_list_gallery    = array_map( 'absint', (array) $gallery_ids );
$cw_list_thumb_ids  = array_slice( $cw_list_gallery, 0, 4 );
$cw_list_thumb_more = max( 0, count( $cw_list_gallery ) - count( $cw_list_thumb_ids ) );

// Характеристики: до 3 видимых атрибутов. Блок не выводится, если атрибутов нет.
$cw_list_specs = [];
foreach ( $product->get_attributes() as $cw_attr ) {
	if ( ! $cw_attr instanceof WC_Product_Attribute || ! $cw_attr->get_visible() ) {
		continue;
	}
	$cw_values = $cw_attr->is_taxonomy()
		? wc_get_product_terms( $product_id, $cw_attr->get_name(), [ 'fields' => 'names' ] )
		: $cw_attr->get_options();
	$cw_value  = implode( ', ', array_filter( array_map( 'wp_strip_all_tags', (array) $cw_values ) ) );
	if ( '' === $cw_value ) {
		continue;
	}
	$cw_list_specs[] = [ wc_attribute_label( $cw_attr->get_name(), $product ), $cw_value ];
	if ( count( $cw_list_specs ) >= 3 ) {
		break;
	}
}

$cw_list_price = $show_price ? $product->get_price_html() : '';
?>
<div id="product-<?php echo esc_attr( $product_id ); ?>" class="project item <?php echo esc_attr( $cw_col ); ?>"<?php echo $cw_wl_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="<?php echo esc_attr( $cw_list_card_class ); ?>">

		<figure class="card-img position-relative text-reset<?php echo $figure_radius ? ' ' . esc_attr( trim( $figure_radius ) ) : ''; ?>">
			<a href="<?php echo esc_url( $product_url ); ?>">
				<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>

			<?php if ( $hover_img_html ) : ?>
				<?php echo $hover_img_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>

			<?php echo $badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

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

			<?php if ( $cw_compare_on ) : ?>
				<?php CW_Compare_UI::render_loop_button( $product_id ); ?>
			<?php endif; ?>
		</figure>

		<div class="card-body cw-list-body p-5">

			<div class="cw-list-main">

				<?php if ( $show_category || $show_rating ) : ?>
				<div class="d-flex align-items-center justify-content-between mb-2">
					<?php if ( $show_category && $category_name ) : ?>
						<span class="post-category text-ash mb-0"><?php echo esc_html( $category_name ); ?></span>
					<?php endif; ?>
					<?php if ( $show_rating && $rating_word ) : ?>
						<span class="ratings <?php echo esc_attr( $rating_word ); ?>"></span>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<h2 class="post-title h4 mb-2">
					<a href="<?php echo esc_url( $product_url ); ?>" class="<?php echo esc_attr( $cw_list_title_class ); ?>">
						<?php echo esc_html( $product->get_name() ); ?>
					</a>
				</h2>

				<?php if ( $cw_list_text ) : ?>
					<p class="cw-list-text fs-md line-clamp-2 mb-3"><?php echo esc_html( $cw_list_text ); ?></p>
				<?php endif; ?>

				<?php if ( $cw_list_thumb_ids ) : ?>
					<div class="cw-list-thumbs mb-3" aria-hidden="true">
						<?php foreach ( $cw_list_thumb_ids as $cw_thumb_id ) : ?>
							<a href="<?php echo esc_url( $product_url ); ?>" class="cw-list-thumb" tabindex="-1">
								<?php echo wp_get_attachment_image( $cw_thumb_id, 'woocommerce_gallery_thumbnail', false, [ 'alt' => '', 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php endforeach; ?>
						<?php if ( $cw_list_thumb_more > 0 ) : ?>
							<span class="cw-list-thumb cw-list-thumb--more">+<?php echo (int) $cw_list_thumb_more; ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $cw_list_specs ) : ?>
					<dl class="cw-list-specs mb-3">
						<?php foreach ( $cw_list_specs as $cw_spec ) : ?>
							<dt><?php echo esc_html( $cw_spec[0] ); ?></dt>
							<dd class="<?php echo esc_attr( $cw_list_fg_class ); ?>"><?php echo esc_html( $cw_spec[1] ); ?></dd>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>

				<?php if ( $cw_list_price ) : ?>
					<p class="price mb-0 <?php echo esc_attr( $cw_list_fg_class ); ?>"><?php echo $cw_list_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<?php endif; ?>

			</div>
			<!-- /.cw-list-main -->

			<div class="cw-list-cta">
				<?php if ( $show_cart ) : ?>
					<?php if ( $is_simple ) : ?>
						<a href="<?php echo esc_url( $add_to_cart_url ); ?>"
						   class="btn btn-primary btn-sm btn-icon btn-icon-start has-ripple ajax_add_to_cart<?php echo esc_attr( $btn_style ); ?>"
						   data-product_id="<?php echo esc_attr( $product_id ); ?>"
						   data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
						   data-quantity="1"
						   rel="nofollow">
							<i class="uil uil-shopping-bag"></i>
							<?php echo esc_html( $add_to_cart_text ); ?>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( $product_url ); ?>"
						   class="btn btn-primary btn-sm btn-icon btn-icon-start has-ripple<?php echo esc_attr( $btn_style ); ?>">
							<i class="uil uil-arrow-right"></i>
							<?php echo esc_html( $add_to_cart_text ); ?>
						</a>
					<?php endif; ?>
				<?php else : ?>
					<?php // Без корзины (каталог без цен): кнопка перехода на страницу товара. ?>
					<a href="<?php echo esc_url( $product_url ); ?>"
					   class="btn btn-primary btn-sm has-ripple<?php echo esc_attr( $btn_style ); ?>">
						<?php esc_html_e( 'Read more', 'codeweber' ); ?>
					</a>
				<?php endif; ?>
			</div>
			<!-- /.cw-list-cta -->

		</div>
		<!-- /.card-body -->

	</div>
	<!-- /.card -->
</div>
<!-- /.item -->
