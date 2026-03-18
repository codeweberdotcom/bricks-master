<?php
/**
 * Rating filter — Bootstrap form-check with theme .ratings class.
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

$rating_words = [ 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five' ];
?>

<?php foreach ( $options as $opt ) :
	$val  = (int) $opt['value'];
	$uid  = 'cw-rating-' . $val;
	$word = $rating_words[ $val ] ?? '';
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
			aria-label="<?php echo esc_attr( sprintf( _n( '%d звезда и выше', '%d звёзды и выше', $val, 'codeweber' ), $val ) ); ?>"
			aria-pressed="<?php echo $opt['is_active'] ? 'true' : 'false'; ?>">
			<span class="ratings <?php echo esc_attr( $word ); ?>" aria-hidden="true"></span>
		</a>
	</div>
<?php endforeach; ?>
