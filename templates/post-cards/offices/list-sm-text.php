<?php
/**
 * Template: Office Post Card — Text SM (no image)
 *
 * Text-only card: title + contacts, no image column.
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

// Display toggles from Post Grid block
$show_address      = isset( $display_settings['show_office_address'] )     ? (bool) $display_settings['show_office_address']     : true;
$show_phone        = isset( $display_settings['show_office_phone'] )       ? (bool) $display_settings['show_office_phone']       : true;
$show_email        = isset( $display_settings['show_office_email'] )       ? (bool) $display_settings['show_office_email']       : true;
$show_hours        = isset( $display_settings['show_office_hours'] )       ? (bool) $display_settings['show_office_hours']       : true;
$show_description  = isset( $display_settings['show_office_description'] ) ? (bool) $display_settings['show_office_description'] : true;
$show_map          = isset( $display_settings['show_office_map'] )         ? (bool) $display_settings['show_office_map']         : true;
$map_style         = isset( $display_settings['office_map_style'] )        ? $display_settings['office_map_style']               : 'button';

// Address fields
$city          = '';
$town_terms    = wp_get_post_terms( $post_id, 'towns', array( 'fields' => 'names' ) );
if ( ! empty( $town_terms ) && ! is_wp_error( $town_terms ) ) {
	$city = $town_terms[0];
} else {
	$city = get_post_meta( $post_id, '_office_city', true );
}
$street        = get_post_meta( $post_id, '_office_street', true );
$full_address  = get_post_meta( $post_id, '_office_full_address', true );
$phone         = get_post_meta( $post_id, '_office_phone', true );
$email         = get_post_meta( $post_id, '_office_email', true );
$working_hours = get_post_meta( $post_id, '_office_working_hours', true );
$description   = get_post_meta( $post_id, '_office_description', true );
$latitude      = get_post_meta( $post_id, '_office_latitude', true );
$longitude     = get_post_meta( $post_id, '_office_longitude', true );
$has_map       = $latitude && $longitude && class_exists( 'Codeweber_Yandex_Maps' );

$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';

$show_title       = isset( $display_settings['show_title'] )  ? (bool) $display_settings['show_title'] : true;
$title_tag        = isset( $display_settings['title_tag'] )   ? $display_settings['title_tag']          : 'h4';
$title_class_attr = isset( $display_settings['title_class'] ) && $display_settings['title_class']
	? ' ' . $display_settings['title_class']
	: '';
?>
<div class="card h-100<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">

	<div class="card-body p-5 d-flex flex-column">

		<?php if ( $show_title ) : ?>
		<<?php echo esc_attr( $title_tag ); ?> class="post-title mb-2<?php echo esc_attr( $title_class_attr ); ?>">
			<a href="<?php echo esc_url( $link ); ?>" class="link-dark">
				<?php echo esc_html( $title ); ?>
			</a>
		</<?php echo esc_attr( $title_tag ); ?>>
		<?php endif; ?>

		<?php if ( $show_address && ( $full_address || $street ) ) : ?>
			<div class="d-flex align-items-center mb-1">
				<i class="uil uil-map-marker fs-18 text-primary me-2 flex-shrink-0"></i>
				<span class="text-body fs-sm">
					<?php echo esc_html( $full_address ?: $street ); ?>
				</span>
			</div>
		<?php endif; ?>

		<?php if ( $show_phone && $phone ) : ?>
			<div class="d-flex align-items-center mb-1">
				<i class="uil uil-phone fs-18 text-primary me-2 flex-shrink-0"></i>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="text-body fs-sm text-decoration-none">
					<?php echo esc_html( $phone ); ?>
				</a>
			</div>
		<?php endif; ?>

		<?php if ( $show_email && $email ) : ?>
			<div class="d-flex align-items-center mb-1">
				<i class="uil uil-envelope fs-18 text-primary me-2 flex-shrink-0"></i>
				<a href="mailto:<?php echo esc_attr( $email ); ?>" class="text-body fs-sm text-decoration-none">
					<?php echo esc_html( $email ); ?>
				</a>
			</div>
		<?php endif; ?>

		<?php if ( $show_description && $description ) : ?>
			<p class="text-muted fs-sm mt-2"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>

		<?php if ( $show_hours && $working_hours ) : ?>
			<div class="d-flex align-items-start<?php echo ( ! $show_map || ! $has_map ) ? ' mt-auto pt-3' : ' pt-3'; ?>">
				<i class="uil uil-clock fs-18 text-primary me-2 flex-shrink-0 mt-1"></i>
				<span class="text-muted fs-sm"><?php echo esc_html( $working_hours ); ?></span>
			</div>
		<?php endif; ?>

		<?php if ( $show_map && $has_map ) : ?>
			<div class="mt-auto pt-3">
				<?php if ( $map_style === 'text' ) : ?>
					<a href="#" class="hover more"
						data-office-map
						data-office-id="<?php echo esc_attr( $post_id ); ?>">
						<i class="uil uil-map-marker me-1"></i><?php esc_html_e( 'Show on Map', 'codeweber' ); ?>
					</a>
				<?php else : ?>
					<a href="#" class="btn btn-sm btn-soft-primary btn-icon btn-icon-start has-ripple"
						data-office-map
						data-office-id="<?php echo esc_attr( $post_id ); ?>">
						<i class="uil uil-map-marker"></i> <?php esc_html_e( 'Show on Map', 'codeweber' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	</div>
	<!-- /.card-body -->

</div>
<!-- /.card -->
