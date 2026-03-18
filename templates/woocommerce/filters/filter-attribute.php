<?php
/**
 * Attribute / tag filter — Bootstrap form-check style (shop2.html).
 *
 * Expected variables:
 *   $terms_data      array  — from cw_get_attribute_filter_terms() or cw_get_tag_filter_terms()
 *   $display_mode    string — 'checkbox' | 'radio' | 'list' | 'button'
 *   $show_count      bool
 *   $radio_name      string — name attribute for radio inputs (default: taxonomy slug)
 *   $empty_behavior  string — 'default' | 'hide' | 'disable' | 'disable_clickable'
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $terms_data ) ) {
	return;
}

$display_mode        = $display_mode ?? 'checkbox';
$show_count          = $show_count ?? true;
$button_class        = $button_class ?? 'btn-outline-secondary';
$button_active_class = $button_active_class ?? 'btn-secondary';
$checkbox_size_class = $checkbox_size_class ?? '';
$checkbox_item_class = $checkbox_item_class ?? '';
$checkbox_columns    = $checkbox_columns ?? 1;
$radio_size_class    = $radio_size_class ?? '';
$radio_item_class    = $radio_item_class ?? '';
$radio_name          = $radio_name ?? 'cw_filter_radio';
$empty_behavior      = $empty_behavior ?? 'disable';
$swatch_columns      = isset( $swatch_columns ) ? (int) $swatch_columns : 0;
$swatch_item_class   = isset( $swatch_item_class ) ? (string) $swatch_item_class : '';
$swatch_shape        = $swatch_shape ?? 'avatar';
$single_select       = $single_select ?? false;

// When multiple attribute filters are active, WooCommerce returns 0 products for any
// term in a 3rd+ attribute (AND intersection). 'disable' would render all those terms
// as non-clickable <span> elements, making it impossible to change or add the selection.
// Always treat 'disable' as 'disable_clickable' so empty terms remain navigable
// (greyed out but still linkable — consistent with WooCommerce's own layered nav).
if ( 'disable' === $empty_behavior ) {
	$empty_behavior = 'disable_clickable';
}
?>

<?php if ( 'color' === $display_mode || 'image' === $display_mode ) :
	// When swatchColumns > 0: CSS Grid — each swatch auto-sizes to fit N per row.
	// When swatchColumns = 0: flex-wrap with fixed .w-8 .h-8 (2rem) swatches.
	$use_grid = $swatch_columns > 0;

	if ( $use_grid ) {
		$container_style = 'display:grid;grid-template-columns:repeat(' . $swatch_columns . ',1fr);gap:0.5rem';
		$container_tag   = '<div style="' . esc_attr( $container_style ) . '">';
	} else {
		$container_tag   = '<div class="d-flex flex-wrap gap-2">';
	}
	echo $container_tag; // phpcs:ignore WordPress.Security.EscapeOutput
	?>
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];

			if ( 'default' === $empty_behavior ) { $is_empty = false; }
			elseif ( 'hide' === $empty_behavior && $is_empty ) { continue; }

			// Build swatch background style
			$swatch_data  = function_exists( 'cw_get_term_swatch_data' ) ? cw_get_term_swatch_data( $term->term_id ) : [];
			$bg_style     = '';

			if ( 'color' === $display_mode && ! empty( $swatch_data['color'] ) ) {
				$c = sanitize_hex_color( $swatch_data['color'] );
				if ( $c ) {
					if ( ! empty( $swatch_data['is_dual'] ) && ! empty( $swatch_data['secondary'] ) ) {
						$c2    = sanitize_hex_color( $swatch_data['secondary'] ) ?: '#000000';
						$angle = absint( $swatch_data['dual_angle'] ?? 45 );
						$bg_style = sprintf( 'background:linear-gradient(%ddeg,%s 50%%,%s 50%%)', $angle, $c, $c2 );
					} else {
						$bg_style = 'background-color:' . $c;
					}
				}
			} elseif ( 'image' === $display_mode && ! empty( $swatch_data['image_id'] ) ) {
				$img_url = wp_get_attachment_image_url( (int) $swatch_data['image_id'], 'codeweber_post_100-100' );
				if ( $img_url ) {
					$bg_style = 'background-image:url(' . esc_url( $img_url ) . ');background-size:cover;background-position:center';
				}
			}

			// In grid mode: swatch fills the cell (width:100%; aspect-ratio:1).
			// In flex mode: .w-8 .h-8 provide the fixed 2rem size.
			$size_style   = $use_grid ? 'width:100%;aspect-ratio:1' : '';
			$inline_style = implode( ';', array_filter( [ $size_style, $bg_style ] ) );

			// Grid mode: no .w-8 .h-8 (size comes from grid cell)
			$classes = [ 'cw-swatch', 'cw-swatch--' . $display_mode ];
			if ( $swatch_shape ) {
				$classes[] = $swatch_shape;
			}
			if ( ! $use_grid ) {
				$classes[] = 'w-8';
				$classes[] = 'h-8';
			}
			if ( $is_active ) {
				$classes[] = 'selected';
			}
			if ( $swatch_item_class ) {
				$classes[] = $swatch_item_class;
			}
			$class_attr = implode( ' ', $classes );

			$title_attr = $term->name;
			if ( $show_count ) {
				$title_attr .= ' (' . ( $is_empty ? 0 : $count ) . ')';
			}
			?>
			<?php if ( $is_empty && 'disable_clickable' !== $empty_behavior ) : ?>
				<span class="<?php echo esc_attr( $class_attr ); ?> disabled opacity-50"
					style="<?php echo esc_attr( $inline_style ); ?>"
					aria-disabled="true"
					title="<?php echo esc_attr( $title_attr ); ?>">
				</span>
			<?php else : ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>"
					class="<?php echo esc_attr( $class_attr ); ?> pjax-link<?php echo ( 'disable_clickable' === $empty_behavior && $is_empty && ! $is_active ) ? ' opacity-50' : ''; ?>"
					style="<?php echo esc_attr( $inline_style ); ?>"
					title="<?php echo esc_attr( $title_attr ); ?>"
					aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

<?php elseif ( 'button' === $display_mode ) : ?>

	<div class="d-flex flex-wrap gap-1">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];

			if ( 'default' === $empty_behavior ) { $is_empty = false; }
			elseif ( 'hide' === $empty_behavior && $is_empty ) { continue; }
			?>
			<?php $is_clickable_muted = ( 'disable_clickable' === $empty_behavior && $is_empty ); ?>
		<?php if ( $is_empty && ! $is_clickable_muted ) : ?>
				<span class="btn has-ripple <?php echo esc_attr( $button_class ); ?> disabled opacity-50"
					aria-disabled="true"
					<?php if ( $show_count ) : ?>title="(0)"<?php endif; ?>>
					<?php echo esc_html( $term->name ); ?>
				</span>
			<?php else : ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>"
					class="btn has-ripple pjax-link <?php echo $is_active ? esc_attr( $button_active_class ) : esc_attr( $button_class ); ?><?php echo $is_clickable_muted ? ' opacity-50' : ''; ?>"
					<?php if ( $show_count ) : ?>title="(<?php echo esc_attr( $count ); ?>)"<?php endif; ?>>
					<?php echo esc_html( $term->name ); ?>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

<?php elseif ( 'radio' === $display_mode ) : ?>

	<ul class="list-unstyled ps-0 mb-0">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];
			$uid       = 'cw-term-' . sanitize_html_class( $term->slug );

			if ( 'default' === $empty_behavior ) { $is_empty = false; }
			elseif ( 'hide' === $empty_behavior && $is_empty ) { continue; }

			$is_clickable_muted = ( 'disable_clickable' === $empty_behavior && $is_empty );
			?>
			<li>
				<div class="form-check mb-1 cw-filter-check<?php echo esc_attr( $radio_size_class ); ?><?php echo $radio_item_class ? ' ' . esc_attr( $radio_item_class ) : ''; ?><?php echo $is_empty ? ' opacity-50' : ''; ?>">
					<input class="form-check-input"
						type="radio"
						name="<?php echo esc_attr( $radio_name ); ?>"
						id="<?php echo esc_attr( $uid ); ?>"
						<?php checked( $is_active ); ?>
						<?php if ( $is_empty && ! $is_clickable_muted ) { disabled( true ); } ?>
						tabindex="-1"
						aria-hidden="true">
					<?php if ( $is_empty && ! $is_clickable_muted ) : ?>
						<span class="form-check-label text-muted pe-none">
							<?php echo esc_html( $term->name ); ?>
							<?php if ( $show_count ) : ?>
								<span class="fs-sm ms-1">(0)</span>
							<?php endif; ?>
						</span>
					<?php else : ?>
						<a href="<?php echo esc_url( $item['url'] ); ?>"
							class="form-check-label pjax-link<?php echo $is_clickable_muted ? ' text-muted' : ''; ?>"
							aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
							<?php echo esc_html( $term->name ); ?>
							<?php if ( $show_count ) : ?>
								<span class="fs-sm text-muted ms-1">(<?php echo esc_html( $count ); ?>)</span>
							<?php endif; ?>
						</a>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>

<?php elseif ( 'list' === $display_mode ) : ?>

	<ul class="list-unstyled ps-0 mb-0">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];

			if ( 'default' === $empty_behavior ) { $is_empty = false; }
			elseif ( 'hide' === $empty_behavior && $is_empty ) { continue; }

			$is_clickable_muted = ( 'disable_clickable' === $empty_behavior && $is_empty );
			?>
			<li class="mb-1<?php echo $is_empty ? ' opacity-50' : ''; ?>">
				<?php if ( $is_empty && ! $is_clickable_muted ) : ?>
					<span class="link-body text-muted pe-none" style="text-decoration:none;">
						<?php echo esc_html( $term->name ); ?>
						<?php if ( $show_count ) : ?>
							<span class="fs-sm ms-1">(0)</span>
						<?php endif; ?>
					</span>
				<?php else : ?>
					<a href="<?php echo esc_url( $item['url'] ); ?>"
						class="link-body pjax-link<?php echo $is_active ? ' fw-semibold' : ''; ?><?php echo $is_clickable_muted ? ' text-muted' : ''; ?>"
						style="text-decoration:none;">
						<?php echo esc_html( $term->name ); ?>
						<?php if ( $show_count ) : ?>
							<span class="fs-sm text-muted ms-1">(<?php echo esc_html( $count ); ?>)</span>
						<?php endif; ?>
					</a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>

<?php else : // checkbox — default (or single-select rendered as radio) ?>

	<ul class="list-unstyled ps-0 mb-0<?php echo 2 === $checkbox_columns ? ' cc-2' : ''; ?>">
		<?php foreach ( $terms_data as $item ) :
			$term      = $item['term'];
			$is_active = $item['is_active'];
			$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
			$count     = $item['count'];
			$uid       = 'cw-term-' . sanitize_html_class( $term->slug );

			if ( 'default' === $empty_behavior ) { $is_empty = false; }
			elseif ( 'hide' === $empty_behavior && $is_empty ) { continue; }

			$is_clickable_muted = ( 'disable_clickable' === $empty_behavior && $is_empty );
			?>
			<li>
				<div class="form-check mb-1 cw-filter-check<?php echo esc_attr( $checkbox_size_class ); ?><?php echo $checkbox_item_class ? ' ' . esc_attr( $checkbox_item_class ) : ''; ?><?php echo $is_empty ? ' opacity-50' : ''; ?>">
					<input class="form-check-input"
						type="<?php echo $single_select ? 'radio' : 'checkbox'; ?>"
						<?php if ( $single_select ) : ?>name="<?php echo esc_attr( $radio_name ); ?>"<?php endif; ?>
						id="<?php echo esc_attr( $uid ); ?>"
						<?php checked( $is_active ); ?>
						<?php if ( $is_empty && ! $is_clickable_muted ) { disabled( true ); } ?>
						tabindex="-1"
						aria-hidden="true">
					<?php if ( $is_empty && ! $is_clickable_muted ) : ?>
						<span class="form-check-label text-muted pe-none">
							<?php echo esc_html( $term->name ); ?>
							<?php if ( $show_count ) : ?>
								<span class="fs-sm ms-1">(0)</span>
							<?php endif; ?>
						</span>
					<?php else : ?>
						<a href="<?php echo esc_url( $item['url'] ); ?>"
							class="form-check-label pjax-link<?php echo $is_clickable_muted ? ' text-muted' : ''; ?>"
							aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
							<?php echo esc_html( $term->name ); ?>
							<?php if ( $show_count ) : ?>
								<span class="fs-sm text-muted ms-1">(<?php echo esc_html( $count ); ?>)</span>
							<?php endif; ?>
						</a>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>

<?php endif; ?>
