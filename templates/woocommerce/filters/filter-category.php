<?php
/**
 * Category filter — hierarchical list of product_cat terms.
 *
 * Expected variables:
 *   $terms_data   array  — from cw_get_category_filter_terms()
 *   $show_count   bool
 *
 * Category links navigate to the term archive URL (not filter param URL).
 * They still use .pjax-link so PJAX intercepts navigation.
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $terms_data ) ) {
	return;
}

$show_count = $show_count ?? true;
?>

<ul class="cw-filter-list list-unstyled mb-0">
	<?php foreach ( $terms_data as $item ) :
		$term      = $item['term'];
		$is_active = $item['is_active'];
		$count     = (int) $term->count;
		?>
		<li class="cw-filter-list__item<?php echo $is_active ? ' active' : ''; ?>">
			<a href="<?php echo esc_url( $item['url'] ); ?>" class="cw-filter-list__link pjax-link">
				<?php echo esc_html( $term->name ); ?>
				<?php if ( $show_count ) : ?>
					<span class="cw-filter-count">(<?php echo esc_html( $count ); ?>)</span>
				<?php endif; ?>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
