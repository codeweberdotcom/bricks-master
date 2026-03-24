<?php
/**
 * Template: Staff Horizontal Card (square photo left col-3, content right col-9)
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
	'show_social'      => false,
	'image_size'       => 'codeweber_staff',
] );

$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
$staff_url   = get_permalink( $post_data['id'] );
$image_url   = $post_data['image_url'] ?? '';
$image_alt   = $post_data['image_alt'] ?? '';
?>
<div class="card lift overflow-hidden<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">
	<div class="row g-0 h-100">
		<div class="col-3">
			<?php if ( $image_url ) : ?>
			<figure class="mb-0 h-100">
				<a href="<?php echo esc_url( $staff_url ); ?>" class="d-block h-100 text-decoration-none">
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" class="w-100 h-100 object-fit-cover">
				</a>
			</figure>
			<?php endif; ?>
		</div>
		<div class="col-9">
			<div class="card-body">
				<?php if ( ! empty( $post_data['title'] ) ) : ?>
				<h4 class="mb-1">
					<a href="<?php echo esc_url( $staff_url ); ?>" class="link-dark text-decoration-none"><?php echo esc_html( $post_data['title'] ); ?></a>
				</h4>
				<?php endif; ?>
				<?php if ( ! empty( $post_data['position'] ) ) : ?>
				<div class="text-muted mb-2"><?php echo esc_html( $post_data['position'] ); ?></div>
				<?php endif; ?>
				<?php if ( $template_args['show_description'] && ! empty( $post_data['excerpt'] ) ) : ?>
				<p class="mb-0"><?php echo wp_kses_post( $post_data['excerpt'] ); ?></p>
				<?php endif; ?>
				<?php if ( $template_args['show_social'] && function_exists( 'staff_social_links' ) && function_exists( 'codeweber_global_social_style' ) ) :
					$social_style = codeweber_global_social_style();
					$social_html  = staff_social_links( $post_data['id'], 'mb-0 gap-0', $social_style['type'], $social_style['size'], 'primary', 'solid', $social_style['button_form'] );
					if ( ! empty( $social_html ) ) : ?>
					<div class="mt-3"><?php echo $social_html; ?></div>
					<?php endif;
				endif; ?>
			</div><!-- /.card-body -->
		</div><!-- /.col-9 -->
	</div><!-- /.row -->
</div><!-- /.card -->
