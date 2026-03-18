<?php
/**
 * Rating filter — Bootstrap form-check style with SVG stars.
 *
 * Expected variables:
 *   $options  array — from cw_get_rating_filter_options()
 *
 * @package CodeWeber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $options ) ) {
	return;
}

$checkbox_size_class = $checkbox_size_class ?? '';
$checkbox_item_class = $checkbox_item_class ?? '';
?>

<?php foreach ( $options as $opt ) :
	$uid = 'cw-rating-' . (int) $opt['value'];
	?>
	<div class="form-check mb-1 cw-filter-check<?php echo esc_attr( $checkbox_size_class ); ?><?php echo $checkbox_item_class ? ' ' . esc_attr( $checkbox_item_class ) : ''; ?>">
		<input class="form-check-input"
			type="radio"
			name="cw_rating_filter"
			id="<?php echo esc_attr( $uid ); ?>"
			<?php checked( $opt['is_active'] ); ?>
			tabindex="-1"
			aria-hidden="true">
		<a href="<?php echo esc_url( $opt['url'] ); ?>"
			class="form-check-label pjax-link"
			aria-label="<?php echo esc_attr( sprintf( _n( '%d звезда и выше', '%d звёзды и выше', (int) $opt['value'], 'codeweber' ), (int) $opt['value'] ) ); ?>"
			aria-pressed="<?php echo $opt['is_active'] ? 'true' : 'false'; ?>">
			<span class="d-inline-flex gap-1 align-items-center" aria-hidden="true">
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<?php if ( $i <= (int) $opt['value'] ) : ?>
						<svg width="13" height="13" viewBox="0 0 14 14" fill="currentColor" style="color:#f5a623" xmlns="http://www.w3.org/2000/svg">
							<path d="M7 1L8.76 4.58L12.73 5.16L9.87 7.94L10.53 11.9L7 9.97L3.47 11.9L4.13 7.94L1.27 5.16L5.24 4.58L7 1Z"/>
						</svg>
					<?php else : ?>
						<svg width="13" height="13" viewBox="0 0 14 14" fill="none" style="color:var(--bs-border-color)" xmlns="http://www.w3.org/2000/svg">
							<path d="M7 1L8.76 4.58L12.73 5.16L9.87 7.94L10.53 11.9L7 9.97L3.47 11.9L4.13 7.94L1.27 5.16L5.24 4.58L7 1Z" stroke="currentColor" stroke-width="1"/>
						</svg>
					<?php endif; ?>
				<?php endfor; ?>
			</span>
		</a>
	</div>
<?php endforeach; ?>
