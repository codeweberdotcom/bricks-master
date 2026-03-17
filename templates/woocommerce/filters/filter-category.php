<?php
/**
 * Category filter — uses Bootstrap list-unstyled / link-body (shop2.html style).
 *
 * Expected variables:
 *   $terms_data  array — from cw_get_category_filter_terms()
 *   $show_count  bool
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

<ul class="list-unstyled ps-0 mb-0">
	<?php foreach ( $terms_data as $item ) :
		$term      = $item['term'];
		$is_active = $item['is_active'];
		$count     = (int) $term->count;
		?>
		<li class="mb-1">
			<a href="<?php echo esc_url( $item['url'] ); ?>"
				class="link-body pjax-link<?php echo $is_active ? ' fw-semibold' : ''; ?>"
				style="text-decoration:none;">
				<?php echo esc_html( $term->name ); ?>
				<?php if ( $show_count ) : ?>
					<span class="fs-sm text-muted ms-1">(<?php echo esc_html( $count ); ?>)</span>
				<?php endif; ?>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
