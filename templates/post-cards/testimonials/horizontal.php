<?php
/**
 * Template: Testimonial Horizontal Card (light, full-width)
 *
 * Layout: one row — avatar + name/meta | rating right; below — blockquote text.
 * One card per row (col-12). Light background.
 *
 * @param array $post_data       From cw_get_post_card_data (id, author_name, author_role,
 *                               company, text, rating, rating_class, avatar_url, link)
 * @param array $display_settings
 * @param array $template_args  show_rating, show_company, excerpt_length
 */

if ( ! isset( $post_data ) || ! $post_data ) {
	return;
}

$template_args = wp_parse_args( $template_args ?? [], [
	'show_rating'    => true,
	'show_company'   => true,
	'excerpt_length' => 30,
] );

$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';

$author_name  = $post_data['author_name'] ?? '';
$author_role  = $post_data['author_role'] ?? '';
$company      = $template_args['show_company'] ? ( $post_data['company'] ?? '' ) : '';
$text         = $post_data['text'] ?? '';
$rating       = intval( $post_data['rating'] ?? 0 );
$rating_class = $post_data['rating_class'] ?? 'five';
$avatar_url   = $post_data['avatar_url'] ?? '';
$post_id      = $post_data['id'] ?? 0;

// Avatar initials fallback
$initials = '';
if ( ! $avatar_url && $author_name ) {
	$parts    = explode( ' ', trim( $author_name ) );
	$initials = mb_strtoupper( mb_substr( $parts[0], 0, 1 ) );
	if ( count( $parts ) > 1 ) {
		$initials .= mb_strtoupper( mb_substr( end( $parts ), 0, 1 ) );
	}
}

// Avatar bg color cycles by post ID
$avatar_colors = [ 'primary', 'leaf', 'grape', 'fuchsia', 'sky', 'yellow' ];
$avatar_color  = $avatar_colors[ $post_id % count( $avatar_colors ) ];

// Excerpt
$excerpt = wp_trim_words( wp_strip_all_tags( $text ), intval( $template_args['excerpt_length'] ), '&hellip;' );

// Meta line: role + company
$meta_parts = array_filter( [ $author_role, $company ? '«' . $company . '»' : '' ] );
$meta_line  = implode( ' — ', $meta_parts );

$radius_cls = $card_radius ? ' ' . esc_attr( $card_radius ) : '';
?>
<div class="card<?php echo $radius_cls; ?>">
	<div class="card-body">

		<?php // ---- Row 1: avatar + name/meta + rating ---- ?>
		<div class="blockquote-details align-items-center mb-4">

			<?php if ( $avatar_url ) : ?>
				<img class="rounded-circle w-12" src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $author_name ); ?>">
			<?php elseif ( $initials ) : ?>
				<span class="avatar bg-<?php echo esc_attr( $avatar_color ); ?> text-white w-11 h-11">
					<span><?php echo esc_html( $initials ); ?></span>
				</span>
			<?php endif; ?>

			<div class="info">
				<?php if ( $author_name ) : ?>
					<h5 class="mb-1"><?php echo esc_html( $author_name ); ?></h5>
				<?php endif; ?>
				<?php if ( $meta_line ) : ?>
					<p class="mb-0"><?php echo esc_html( $meta_line ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $template_args['show_rating'] && $rating > 0 ) : ?>
				<span class="ratings <?php echo esc_attr( $rating_class ); ?> ms-auto flex-shrink-0"></span>
			<?php endif; ?>

		</div>
		<?php // ---- Row 2: quote text ---- ?>
		<?php if ( $excerpt ) : ?>
			<blockquote class="icon mb-0">
				<p><?php echo esc_html( $excerpt ); ?></p>
			</blockquote>
		<?php endif; ?>

	</div>
	<!-- /.card-body -->
</div>
<!-- /.card -->
