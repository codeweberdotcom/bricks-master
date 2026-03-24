<?php
/**
 * Template: Staff Archive — Style 6 (horizontal card, square photo, one per row)
 *
 * Uses: templates/post-cards/staff/horizontal.php
 *
 * @package Codeweber
 */

$post_id   = absint( get_the_ID() );
$card_html = cw_render_post_card( get_post(), 'horizontal', [], [
	'show_description' => true,
	'image_size'       => 'codeweber_staff',
] );

if ( empty( $card_html ) ) {
	return;
}
?>

<div id="<?php echo esc_attr( $post_id ); ?>" class="col-12">
	<?php echo $card_html; ?>
</div>
<!--/column -->
