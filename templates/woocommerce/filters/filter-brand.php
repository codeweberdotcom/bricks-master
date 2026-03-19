<?php
/**
 * Brand filter — image swatch (brand logo) or standard text modes.
 *
 * Expected variables:
 *   $terms_data     array  — from cw_get_brand_filter_terms()
 *   $display_mode   string — 'image' | 'checkbox' | 'radio' | 'list' | 'button' | 'badge'
 *   $show_count     bool
 *   $empty_behavior string — 'default' | 'hide' | 'disable' | 'disable_clickable'
 *
 * For non-image modes the attribute template is reused directly since the
 * $terms_data structure is identical.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $terms_data ) ) {
	return;
}

$display_mode  = $display_mode ?? 'image';
$show_count    = $show_count ?? true;
$empty_behavior = $empty_behavior ?? 'disable';

// For all non-image display modes reuse the attribute template — identical structure.
if ( 'image' !== $display_mode ) {
	include __DIR__ . '/filter-attribute.php';
	return;
}

// ── Image swatch mode: brand logos ──────────────────────────────────────────
$swatch_columns    = isset( $swatch_columns ) ? (int) $swatch_columns : 0;
$swatch_item_class = isset( $swatch_item_class ) ? (string) $swatch_item_class : '';
$swatch_shape      = isset( $swatch_shape ) && '' !== $swatch_shape ? (string) $swatch_shape : 'rounded';

// For attribute swatches 'disable' stays as-is; keep brand behaviour the same.
$use_grid = $swatch_columns > 0;
if ( $use_grid ) {
	$container_style = 'display:grid;grid-template-columns:repeat(' . $swatch_columns . ',1fr);gap:0.5rem';
	echo '<div style="' . esc_attr( $container_style ) . '">';
} else {
	echo '<div class="d-flex flex-wrap gap-2">';
}

foreach ( $terms_data as $item ) :
	$term      = $item['term'];
	$is_active = $item['is_active'];
	$is_empty  = ! $is_active && ( $item['is_empty'] ?? false );
	$count     = $item['count'];

	if ( 'default' === $empty_behavior ) {
		$is_empty = false;
	} elseif ( 'hide' === $empty_behavior && $is_empty ) {
		continue;
	}

	$thumbnail_id = (int) ( $item['thumbnail_id'] ?? 0 );
	$img_url      = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'codeweber_post_100-100' ) : '';
	$img_alt      = esc_attr( $term->name );

	$size_style   = $use_grid ? 'width:100%;aspect-ratio:1' : '';
	$inline_style = $size_style; // no background — logo rendered as <img> inside

	$classes = [ 'cw-swatch', 'cw-swatch--image', 'cw-swatch--brand', 'd-flex', 'align-items-center', 'justify-content-center', 'p-1' ];
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

	// Logo <img> — max-width/max-height keeps it within the swatch, never overflows.
	$logo_html = $img_url
		? '<img src="' . esc_url( $img_url ) . '" alt="' . $img_alt . '" style="max-width:100%;max-height:100%;display:block;" loading="lazy">'
		: '<span class="visually-hidden">' . esc_html( $term->name ) . '</span>';

	if ( $is_empty && 'disable_clickable' !== $empty_behavior ) :
		?>
		<span class="<?php echo esc_attr( $class_attr ); ?> disabled cw-swatch--unavailable"
			style="<?php echo esc_attr( $inline_style ); ?>"
			aria-disabled="true"
			title="<?php echo esc_attr( $title_attr ); ?>">
			<?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</span>
		<?php
	else :
		?>
		<a href="<?php echo esc_url( $item['url'] ); ?>"
			class="<?php echo esc_attr( $class_attr ); ?> pjax-link<?php echo ( 'disable_clickable' === $empty_behavior && $is_empty && ! $is_active ) ? ' cw-swatch--unavailable' : ''; ?>"
			style="<?php echo esc_attr( $inline_style ); ?>"
			title="<?php echo esc_attr( $title_attr ); ?>"
			aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
			<?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</a>
		<?php
	endif;
endforeach;
echo '</div>';
