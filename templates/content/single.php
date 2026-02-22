<?php
$card_radius_class = function_exists('getThemeCardImageRadius') ? getThemeCardImageRadius('rounded') : 'rounded';
if (empty(trim($card_radius_class))) {
	$card_radius_class = 'rounded';
}
$figure_class = ($card_radius_class === 'rounded-0') ? '' : 'card-img-top';
?>
<section id="post-<?php the_ID(); ?>" <?php post_class('blog single'); ?>>
	<div class="card <?php echo esc_attr($card_radius_class); ?>">
		<figure<?php echo $figure_class !== '' ? ' class="' . esc_attr($figure_class) . '"' : ''; ?>>
			<?php
			// Получаем ID миниатюры текущего поста
			$thumbnail_id = get_post_thumbnail_id();

			// Lightbox — крупный размер
			$large_image_url = wp_get_attachment_image_src($thumbnail_id, 'codeweber_extralarge');

			if ($large_image_url) :
			?>
				<a href="<?php echo esc_url($large_image_url[0]); ?>" data-glightbox data-gallery="g1">
					<?php the_post_thumbnail('codeweber_post_960-600', array('class' => 'img-fluid')); ?>
				</a>
			<?php endif; ?>
		</figure>
		<!-- /.figure -->
		<div class="card-body">
			<div class="classic-view">
				<article class="post">
					<div class="post-content mb-5">
						<?php the_content(); ?>
					</div>
					<!-- /.post-content -->
					<?php codeweber_single_post_footer(); ?>
					<!-- /.post-footer -->
				</article>
				<!-- /.post -->
			</div>
			<!-- /.classic-view -->
			<hr class="mt-5 mb-5">
			<?php codeweber_single_link_pages(); ?>
			<!-- /.link-pages -->
			<?php codeweber_single_post_author(); ?>
			<!-- /.author-info -->
			<?php echo codeweber_single_social_links(); ?>
			<!-- /.social -->
			<hr class="my-5">
			<?php codeweber_single_related('post'); ?>
			<!-- /.related -->
			<?php codeweber_single_comments(); ?>
			<!-- /.comments -->
		</div>
		<!-- /.card-body -->
	</div>
	<!-- /.card -->
</section> <!-- #post-<?php the_ID(); ?> -->