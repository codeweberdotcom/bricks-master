<?php
/**
 * Rating filter — Bootstrap form-check with theme .ratings class.
 *
 * Expected variables:
 *   $options         array  — from cw_get_rating_filter_options()
 *   $empty_behavior  string — 'default' | 'hide' | 'disable' | 'disable_clickable'
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
$checkbox_columns    = $checkbox_columns ?? 1;
$empty_behavior      = $empty_behavior ?? 'disable';

$rating_words = [ 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five' ];
?>

<ul class="list-unstyled ps-0 mb-0<?php echo 2 === $checkbox_columns ? ' cc-2' : ''; ?>">
<?php foreach ( $options as $opt ) :
	$val      = (int) $opt['value'];
	$uid      = 'cw-rating-' . $val;
	$word     = $rating_words[ $val ] ?? '';
	$is_empty = $opt['is_empty'] ?? false;
	$aria_lbl = esc_attr( sprintf( _n( '%d звезда и выше', '%d звёзды и выше', $val, 'codeweber' ), $val ) );

	if ( 'default' === $empty_behavior ) { $is_empty = false; }
	elseif ( 'hide' === $empty_behavior && $is_empty ) { continue; }

	$is_clickable_muted = ( 'disable_clickable' === $empty_behavior && $is_empty );
	?>
	<li>
		<div class="form-check mb-1 cw-filter-check<?php echo esc_attr( $checkbox_size_class ); ?><?php echo $checkbox_item_class ? ' ' . esc_attr( $checkbox_item_class ) : ''; ?><?php echo $is_empty ? ' opacity-50' : ''; ?>">
			<input class="form-check-input"
				type="radio"
				name="cw_rating_filter"
				id="<?php echo esc_attr( $uid ); ?>"
				<?php checked( $opt['is_active'] ); ?>
				<?php if ( $is_empty && ! $is_clickable_muted ) { disabled( true ); } ?>
				tabindex="-1"
				aria-hidden="true">
			<?php if ( $is_empty && ! $is_clickable_muted ) : ?>
				<span class="form-check-label pe-none" aria-label="<?php echo $aria_lbl; ?>">
					<span class="ratings <?php echo esc_attr( $word ); ?> opacity-50" aria-hidden="true"></span>
				</span>
			<?php else : ?>
				<a href="<?php echo esc_url( $opt['url'] ); ?>"
					class="form-check-label pjax-link<?php echo $is_clickable_muted ? ' opacity-50' : ''; ?>"
					aria-label="<?php echo $aria_lbl; ?>"
					aria-pressed="<?php echo $opt['is_active'] ? 'true' : 'false'; ?>">
					<span class="ratings <?php echo esc_attr( $word ); ?>" aria-hidden="true"></span>
				</a>
			<?php endif; ?>
		</div>
	</li>
<?php endforeach; ?>
</ul>
