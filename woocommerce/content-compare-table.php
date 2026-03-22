<?php
/**
 * Compare Table — HTML-таблица сравнения товаров.
 * Подключается из CW_Compare_Table::render() через ob_start + include.
 *
 * Доступно: $this (CW_Compare_Table), $attrs (array label=>string), $products (WC_Product[])
 *
 * @package CodeWeber
 */

defined( 'ABSPATH' ) || exit;

/** @var CW_Compare_Table $this */
$products = $this->get_products();
$attrs    = $this->collect_attributes();

if ( empty( $products ) ) {
	return;
}
?>
<div class="table-responsive cw-compare-table-wrap">
<table class="cw-compare-table table table-bordered align-middle text-center">

	<!-- ── Шапка: изображения + названия ───────────────────────────────────── -->
	<thead>
		<tr>
			<th class="cw-compare-label-col text-start text-muted fw-normal small"></th>
			<?php foreach ( $products as $product ) : ?>
				<th class="cw-compare-product-col" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">

					<!-- Фото -->
					<a href="<?php echo esc_url( $this->get_url( $product ) ); ?>">
						<?php echo $this->get_image( $product, 100 ); // phpcs:ignore ?>
					</a>

					<!-- Название -->
					<div class="mt-2 mb-1">
						<a href="<?php echo esc_url( $this->get_url( $product ) ); ?>"
							class="fw-semibold text-dark text-decoration-none fs-15 link-hover-primary">
							<?php echo esc_html( $this->get_name( $product ) ); ?>
						</a>
					</div>

					<!-- Удалить из сравнения -->
					<button
						type="button"
						class="cw-compare-remove-product btn btn-link btn-sm text-muted p-0 text-decoration-none"
						data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
						aria-label="<?php esc_attr_e( 'Убрать из сравнения', 'codeweber' ); ?>">
						<i class="uil uil-times me-1" aria-hidden="true"></i>
						<span class="small"><?php esc_html_e( 'Убрать', 'codeweber' ); ?></span>
					</button>

				</th>
			<?php endforeach; ?>
		</tr>
	</thead>

	<tbody>

		<!-- ── Цена ─────────────────────────────────────────────────────────── -->
		<tr class="cw-compare-row" data-row="price">
			<td class="cw-compare-label-col text-start text-muted small"><?php esc_html_e( 'Цена', 'codeweber' ); ?></td>
			<?php foreach ( $products as $product ) : ?>
				<td><?php echo $product->get_price_html(); // phpcs:ignore ?></td>
			<?php endforeach; ?>
		</tr>

		<!-- ── Рейтинг ──────────────────────────────────────────────────────── -->
		<?php if ( $this->show_rating() ) : ?>
		<tr class="cw-compare-row" data-row="rating">
			<td class="cw-compare-label-col text-start text-muted small"><?php esc_html_e( 'Рейтинг', 'codeweber' ); ?></td>
			<?php foreach ( $products as $product ) :
				$parent_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
				$rating    = get_post_meta( $parent_id, '_wc_average_rating', true );
				$count     = get_post_meta( $parent_id, '_wc_review_count', true );
				?>
				<td>
					<?php if ( $rating > 0 ) : ?>
						<?php echo wc_get_rating_html( $rating, $count ); // phpcs:ignore ?>
					<?php else : ?>
						<span class="text-muted">—</span>
					<?php endif; ?>
				</td>
			<?php endforeach; ?>
		</tr>
		<?php endif; ?>

		<!-- ── Наличие ──────────────────────────────────────────────────────── -->
		<?php if ( $this->show_stock() ) : ?>
		<tr class="cw-compare-row" data-row="stock">
			<td class="cw-compare-label-col text-start text-muted small"><?php esc_html_e( 'Наличие', 'codeweber' ); ?></td>
			<?php foreach ( $products as $product ) :
				$avail = $product->get_availability();
				$class = ! empty( $avail['class'] ) ? ' text-' . esc_attr( $avail['class'] ) : '';
				?>
				<td>
					<?php if ( ! empty( $avail['availability'] ) ) : ?>
						<span class="small<?php echo $class; // phpcs:ignore ?>"><?php echo esc_html( $avail['availability'] ); ?></span>
					<?php else : ?>
						<span class="text-success small"><?php esc_html_e( 'В наличии', 'codeweber' ); ?></span>
					<?php endif; ?>
				</td>
			<?php endforeach; ?>
		</tr>
		<?php endif; ?>

		<!-- ── SKU ──────────────────────────────────────────────────────────── -->
		<?php if ( $this->show_sku() ) : ?>
		<tr class="cw-compare-row" data-row="sku">
			<td class="cw-compare-label-col text-start text-muted small">SKU</td>
			<?php foreach ( $products as $product ) : ?>
				<td class="small text-muted"><?php echo $product->get_sku() ? esc_html( $product->get_sku() ) : '—'; ?></td>
			<?php endforeach; ?>
		</tr>
		<?php endif; ?>

		<!-- ── В корзину ────────────────────────────────────────────────────── -->
		<tr class="cw-compare-row" data-row="add_to_cart">
			<td class="cw-compare-label-col text-start text-muted small"></td>
			<?php foreach ( $products as $product ) :
				// Для вариации ссылаемся на родительский товар
				$link_product = $product->is_type( 'variation' )
					? wc_get_product( $product->get_parent_id() )
					: $product;
				if ( ! $link_product ) {
					echo '<td></td>';
					continue;
				}
				?>
				<td>
					<?php
					$link_args = array(
						'quantity'   => 1,
						'class'      => implode( ' ', array_filter( array(
							'btn btn-sm btn-primary has-ripple',
							$link_product->is_type( 'simple' ) ? 'ajax_add_to_cart' : '',
						) ) ),
						'attributes' => array(
							'data-product_id'  => $link_product->get_id(),
							'data-product_sku' => $link_product->get_sku(),
							'aria-label'       => $link_product->add_to_cart_description(),
							'rel'              => 'nofollow',
						),
					);
					echo apply_filters( // phpcs:ignore
						'woocommerce_loop_add_to_cart_link',
						sprintf(
							'<a href="%s" class="%s" %s>%s</a>',
							esc_url( $link_product->add_to_cart_url() ),
							esc_attr( $link_args['class'] ),
							wc_implode_html_attributes( $link_args['attributes'] ),
							esc_html( $link_product->add_to_cart_text() )
						),
						$link_product,
						$link_args
					);
					?>
				</td>
			<?php endforeach; ?>
		</tr>

		<!-- ── Атрибуты ─────────────────────────────────────────────────────── -->
		<?php if ( ! empty( $attrs ) ) : ?>
		<tr>
			<td colspan="<?php echo count( $products ) + 1; ?>"
				class="text-start text-muted small bg-light fw-semibold py-2 px-3">
				<?php esc_html_e( 'Характеристики', 'codeweber' ); ?>
			</td>
		</tr>

		<?php foreach ( $attrs as $attr_key => $attr_label ) :
			// Все одинаковые → класс для JS-скрытия
			$same       = $this->all_same( $attr_key );
			$row_class  = 'cw-compare-row' . ( $same ? ' cw-compare-row--same' : '' );
			?>
			<tr class="<?php echo esc_attr( $row_class ); ?>" data-row="attr-<?php echo esc_attr( $attr_key ); ?>">
				<td class="cw-compare-label-col text-start text-muted small"><?php echo esc_html( $attr_label ); ?></td>
				<?php foreach ( $products as $product ) :
					$value = $this->get_attribute_value( $product, $attr_key );
					?>
					<td class="small"><?php echo $value !== '' ? esc_html( $value ) : '<span class="text-muted">—</span>'; ?></td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
		<?php endif; ?>

	</tbody>
</table>
</div>
<!-- /.table-responsive -->
