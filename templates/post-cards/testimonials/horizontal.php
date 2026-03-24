<?php
/**
 * Template: Testimonial Horizontal Card (dark, full-width)
 *
 * Layout: avatar left | meta + name + text + button | rating top-right.
 * One card per row (col-12). Dark background.
 *
 * @param array $post_data       From cw_get_post_card_data (id, author_name, author_role,
 *                               company, text, rating, rating_class, avatar_url, link)
 * @param array $display_settings
 * @param array $template_args  show_rating, show_company, enable_link, btn_text, excerpt_length
 */

if ( ! isset( $post_data ) || ! $post_data ) {
	return;
}

$template_args = wp_parse_args( $template_args ?? [], [
	'show_rating'    => true,
	'show_company'   => true,
	'enable_link'    => false,
	'btn_text'       => __( 'More', 'codeweber' ),
	'excerpt_length' => 30,
] );

$btn_style    = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : '';
$card_radius  = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';

$author_name  = $post_data['author_name'] ?? '';
$author_role  = $post_data['author_role'] ?? '';
$company      = $template_args['show_company'] ? ( $post_data['company'] ?? '' ) : '';
$text         = $post_data['text'] ?? '';
$rating       = intval( $post_data['rating'] ?? 0 );
$rating_class = $post_data['rating_class'] ?? 'five';
$avatar_url   = $post_data['avatar_url'] ?? '';
$link         = $post_data['link'] ?? '#';
$post_id      = $post_data['id'] ?? 0;

// Date
$post_date = $post_id ? get_the_date( '', $post_id ) : '';

// Avatar initials fallback
$initials = '';
if ( ! $avatar_url && $author_name ) {
	$parts    = explode( ' ', trim( $author_name ) );
	$initials = mb_strtoupper( mb_substr( $parts[0], 0, 1 ) );
	if ( count( $parts ) > 1 ) {
		$initials .= mb_strtoupper( mb_substr( end( $parts ), 0, 1 ) );
	}
}

// Avatar colors cycle by post ID
$avatar_colors = [ 'primary', 'leaf', 'grape', 'fuchsia', 'sky', 'yellow' ];
$avatar_color  = $avatar_colors[ $post_id % count( $avatar_colors ) ];

// Excerpt
$excerpt = wp_trim_words( wp_strip_all_tags( $text ), intval( $template_args['excerpt_length'] ), '&hellip;' );

// Meta line: role + company + date
$meta_parts = array_filter( [ $author_role, $company ? '«' . $company . '»' : '', $post_date ] );
$meta_line  = implode( ' — ', $meta_parts );

$radius_cls = $card_radius ? ' ' . esc_attr( $card_radius ) : '';
?>
<div class="card bg-dark<?php echo $radius_cls; ?>">
	<div class="card-body p-5">
		<div class="d-flex gap-4 align-items-start">

			<?php // ---- Avatar --------------------------------------------- ?>
			<figure class="avatar avatar-lg rounded-circle flex-shrink-0 mb-0 overflow-hidden" style="width:60px;height:60px;min-width:60px;">
				<?php if ( $avatar_url ) : ?>
					<img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $author_name ); ?>" class="rounded-circle" style="width:60px;height:60px;object-fit:cover;">
				<?php else : ?>
					<span class="bg-<?php echo esc_attr( $avatar_color ); ?> text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-17" style="width:60px;height:60px;">
						<?php echo esc_html( $initials ?: '?' ); ?>
					</span>
				<?php endif; ?>
			</figure>

			<?php // ---- Content -------------------------------------------- ?>
			<div class="flex-grow-1 overflow-hidden">

				<?php // Top row: meta + rating ?>
				<div class="d-flex justify-content-between align-items-start gap-3 mb-1">
					<?php if ( $meta_line ) : ?>
						<p class="text-muted small mb-0 lh-sm"><?php echo esc_html( $meta_line ); ?></p>
					<?php endif; ?>
					<?php if ( $template_args['show_rating'] && $rating > 0 ) : ?>
						<span class="ratings <?php echo esc_attr( $rating_class ); ?> flex-shrink-0"></span>
					<?php endif; ?>
				</div>

				<?php if ( $author_name ) : ?>
					<p class="fw-bold text-white mb-2"><?php echo esc_html( $author_name ); ?></p>
				<?php endif; ?>

				<?php if ( $excerpt ) : ?>
					<p class="text-white opacity-75 mb-4"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>

				<a href="<?php echo esc_url( $link ); ?>" class="btn btn-sm btn-outline-white has-ripple<?php echo $radius_cls; ?>">
					<?php echo esc_html( $template_args['btn_text'] ); ?>
				</a>

			</div>
		</div>
	</div>
</div>
