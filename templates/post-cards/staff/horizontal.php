<?php
/**
 * Template: Staff Horizontal Card (square photo left col-3, content right col-9)
 * Whole card is a link — no inner <a> elements.
 *
 * @param array $post_data         From cw_get_post_card_data()
 * @param array $display_settings
 * @param array $template_args
 *
 * @package Codeweber
 */

if ( ! isset( $post_data ) || ! $post_data ) {
	return;
}

$template_args = wp_parse_args( $template_args ?? [], [
	'show_description' => false,
	'image_size'       => 'codeweber_staff',
] );

$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
$staff_url   = get_permalink( $post_data['id'] );
$image_url   = $post_data['image_url'] ?? '';
$image_alt   = $post_data['image_alt'] ?? '';

$email      = get_post_meta( $post_data['id'], '_staff_email', true );
$phone      = get_post_meta( $post_data['id'], '_staff_phone', true );
$department_id   = get_post_meta( $post_data['id'], '_staff_department', true );
$department_term = $department_id ? get_term( (int) $department_id, 'departments' ) : null;
$department      = ( $department_term && ! is_wp_error( $department_term ) ) ? $department_term->name : '';
$city         = get_post_meta( $post_data['id'], '_staff_city', true );
$office_title = '';

if ( post_type_exists( 'offices' ) ) {
	$office_id = get_post_meta( $post_data['id'], '_staff_office', true );
	if ( $office_id ) {
		$office_post = get_post( (int) $office_id );
		if ( $office_post ) {
			$office_title = $office_post->post_title;
			$office_towns = wp_get_post_terms( (int) $office_id, 'towns', [ 'fields' => 'names' ] );
			if ( ! empty( $office_towns ) && ! is_wp_error( $office_towns ) ) {
				$city = $office_towns[0];
			}
		}
	}
}
?>
<a href="<?php echo esc_url( $staff_url ); ?>" class="card card-interactive lift overflow-hidden text-inherit text-decoration-none<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">
	<div class="row g-0 h-100">
		<div class="col-12 col-md-3">
			<?php if ( $image_url ) : ?>
			<figure class="mb-0 h-100">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" class="w-100 h-100 object-fit-cover">
			</figure>
			<?php endif; ?>
		</div>
		<div class="col-12 col-md-9">
			<div class="card-body h-100 p-12 position-relative">
				<?php if ( ! empty( $post_data['position'] ) ) : ?>
				<p class="mb-1 text-muted"><?php echo esc_html( $post_data['position'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $post_data['title'] ) ) : ?>
				<h2 class="mb-3 display-6"><?php echo esc_html( $post_data['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $template_args['show_description'] && ! empty( $post_data['excerpt'] ) ) : ?>
				<p class="mb-2"><?php echo wp_kses_post( $post_data['excerpt'] ); ?></p>
				<?php endif; ?>
				<?php if ( $email || $phone || $department || $city || $office_title ) : ?>
				<ul class="list-unstyled cc-2 mb-0">
					<?php if ( $office_title ) : ?>
					<li class="mb-1 d-flex align-items-center">
						<i class="uil uil-map-pin text-primary me-2"></i>
						<span><?php echo esc_html( $office_title ); ?></span>
					</li>
					<?php endif; ?>
					<?php if ( $department ) : ?>
					<li class="mb-1 d-flex align-items-center">
						<i class="uil uil-building text-primary me-2"></i>
						<span><?php echo esc_html( $department ); ?></span>
					</li>
					<?php endif; ?>
					<?php if ( $city ) : ?>
					<li class="mb-1 d-flex align-items-center">
						<i class="uil uil-map-marker-alt text-primary me-2"></i>
						<span><?php echo esc_html( $city ); ?></span>
					</li>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
					<li class="mb-1 d-flex align-items-center">
						<i class="uil uil-phone text-primary me-2"></i>
						<span><?php echo esc_html( $phone ); ?></span>
					</li>
					<?php endif; ?>
					<?php if ( $email ) : ?>
					<li class="mb-1 d-flex align-items-center">
						<i class="uil uil-envelope text-primary me-2"></i>
						<span><?php echo esc_html( $email ); ?></span>
					</li>
					<?php endif; ?>
				</ul>
				<?php endif; ?>
				<div class="hover_card_button position-absolute p-7 top-0 end-0">
					<i class="fs-25 uil uil-arrow-right lh-1"></i>
				</div>
			</div><!-- /.card-body -->
		</div><!-- /.col-9 -->
	</div><!-- /.row -->
</a><!-- /.card -->
