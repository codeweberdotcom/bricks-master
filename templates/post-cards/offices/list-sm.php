<?php
/**
 * Template: Office Post Card — Horizontal SM
 *
 * Horizontal card: image/logo left (1/3), content right (2/3).
 * Stays horizontal on all screen sizes.
 *
 * Variables available from cw_render_post_card():
 *   $post      WP_Post object
 *   $post_data array  — id, title, link, image_url, image_alt, ...
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

$post_id = $post->ID;
$title   = $post_data['title'];
$link    = $post_data['link'];

// Address fields
$city         = '';
$town_terms   = wp_get_post_terms( $post_id, 'towns', array( 'fields' => 'names' ) );
if ( ! empty( $town_terms ) && ! is_wp_error( $town_terms ) ) {
	$city = $town_terms[0];
} else {
	$city = get_post_meta( $post_id, '_office_city', true );
}
$street       = get_post_meta( $post_id, '_office_street', true );
$full_address = get_post_meta( $post_id, '_office_full_address', true );
$phone        = get_post_meta( $post_id, '_office_phone', true );
$email        = get_post_meta( $post_id, '_office_email', true );
$working_hours = get_post_meta( $post_id, '_office_working_hours', true );

// Image: featured → _office_image meta → site logo fallback
$image_url = get_the_post_thumbnail_url( $post_id, 'medium' );
if ( ! $image_url ) {
	$image_id = get_post_meta( $post_id, '_office_image', true );
	if ( $image_id ) {
		$image_url = wp_get_attachment_image_url( $image_id, 'medium' );
	}
}
if ( ! $image_url ) {
	global $opt_name;
	$options     = get_option( $opt_name );
	$custom_logo = get_post_meta( $post_id, 'custom-logo-dark-header', true );
	$image_url   = codeweber_get_media_url( $custom_logo );
	if ( empty( $image_url ) && ! empty( $options['opt-dark-logo'] ) ) {
		$image_url = codeweber_get_media_url( $options['opt-dark-logo'] );
	}
	if ( empty( $image_url ) ) {
		$image_url = get_template_directory_uri() . '/dist/assets/img/logo-dark.png';
	}
}

$is_svg = strtolower( pathinfo( parse_url( $image_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) === 'svg';

$card_radius   = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
$figure_radius = $card_radius && $card_radius !== 'rounded-0' ? ' rounded-start' : ( $card_radius ? ' ' . trim( $card_radius ) : '' );
?>
<div class="card card-horizontal card-horizontal-always<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">

	<figure class="card-img position-relative text-reset<?php echo $figure_radius ? ' ' . esc_attr( trim( $figure_radius ) ) : ''; ?>">
		<a href="<?php echo esc_url( $link ); ?>">
			<img
				src="<?php echo esc_url( $image_url ); ?>"
				alt="<?php echo esc_attr( $title ); ?>"
				class="w-100 h-100 <?php echo $is_svg ? 'object-fit-contain p-4' : 'object-fit-cover'; ?>"
				loading="lazy"
			>
		</a>
	</figure>

	<div class="card-body p-5 d-flex flex-column">

		<h4 class="post-title mb-2">
			<a href="<?php echo esc_url( $link ); ?>" class="link-dark">
				<?php echo esc_html( $title ); ?>
			</a>
		</h4>

		<?php if ( $full_address || $street ) : ?>
			<div class="d-flex align-items-center mb-1">
				<i class="uil uil-map-marker fs-18 text-primary me-2 flex-shrink-0"></i>
				<span class="text-body fs-sm">
					<?php echo esc_html( $full_address ?: $street ); ?>
				</span>
			</div>
		<?php endif; ?>

		<?php if ( $phone ) : ?>
			<div class="d-flex align-items-center mb-1">
				<i class="uil uil-phone fs-18 text-primary me-2 flex-shrink-0"></i>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="text-body fs-sm text-decoration-none">
					<?php echo esc_html( $phone ); ?>
				</a>
			</div>
		<?php endif; ?>

		<?php if ( $email ) : ?>
			<div class="d-flex align-items-center mb-1">
				<i class="uil uil-envelope fs-18 text-primary me-2 flex-shrink-0"></i>
				<a href="mailto:<?php echo esc_attr( $email ); ?>" class="text-body fs-sm text-decoration-none">
					<?php echo esc_html( $email ); ?>
				</a>
			</div>
		<?php endif; ?>

		<?php if ( $working_hours ) : ?>
			<div class="d-flex align-items-start mt-auto pt-3">
				<i class="uil uil-clock fs-18 text-primary me-2 flex-shrink-0 mt-1"></i>
				<span class="text-muted fs-sm"><?php echo esc_html( $working_hours ); ?></span>
			</div>
		<?php endif; ?>

	</div>
	<!-- /.card-body -->

</div>
<!-- /.card -->
