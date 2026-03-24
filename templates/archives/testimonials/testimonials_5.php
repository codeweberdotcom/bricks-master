<?php
/**
 * Template: Testimonials Archive - Style 5 (Horizontal dark card, full width)
 *
 * One card per row. Dark background with avatar, meta, text, rating, button.
 * Uses: templates/post-cards/testimonials/horizontal.php
 */

$post_id  = absint( get_the_ID() );
$company  = get_post_meta( $post_id, '_testimonial_company', true );
$btn_style = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : '';

$card_html = cw_render_post_card( get_post(), 'horizontal', [], [
	'show_rating'    => true,
	'show_company'   => ! empty( $company ),
	'enable_link'    => false,
	'btn_text'       => __( 'More', 'codeweber' ),
	'excerpt_length' => 30,
] );

if ( empty( $card_html ) ) {
	return;
}
?>

<div id="<?php echo esc_attr( $post_id ); ?>" class="col-12">
	<?php echo $card_html; ?>
</div>
