<?php
/**
 * Active filters — chips showing applied filters with remove links.
 *
 * Expected variables:
 *   $active  array — from cw_get_active_filter_params()
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $active ) ) {
	return;
}
?>

<div class="mb-3">
	<div class="d-flex flex-wrap gap-1 align-items-center">

		<?php foreach ( $active as $filter ) : ?>
			<a href="<?php echo esc_url( $filter['remove_url'] ); ?>"
				class="badge bg-primary bg-opacity-10 text-primary rounded-pill fw-normal d-inline-flex align-items-center gap-1 pjax-link text-decoration-none"
				title="<?php esc_attr_e( 'Убрать фильтр', 'codeweber' ); ?>">
				<?php echo wp_kses_post( $filter['label'] ); ?>
				<svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M1 1L9 9M9 1L1 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
				</svg>
			</a>
		<?php endforeach; ?>

		<a href="<?php echo esc_url( cw_get_clear_filters_url() ); ?>"
			class="pjax-link btn btn-link btn-sm p-0 text-muted text-decoration-none">
			<?php esc_html_e( 'Сбросить всё', 'codeweber' ); ?>
		</a>

	</div>
</div>
