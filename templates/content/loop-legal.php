<article id="<?php $post->post_name; ?>" <?php post_class('post'); ?>>
	<?php $card_radius = getThemeCardImageRadius(); ?>
	<div class="card<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
		<figure class="card-img-top overlay overlay-1 hover-scale<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
			<a href="<?php the_permalink(); ?>">
				<?php
				$img_classes = 'img-fluid mb-3';
				if ($card_radius) {
					$img_classes .= ' ' . esc_attr($card_radius);
				}
				the_post_thumbnail(
					'codeweber_single',
					array(
						'class' => $img_classes,
						'alt' => get_the_title(),
					)
				);
				?><span class="bg"></span></a>
			<figcaption>
				<h5 class="from-top mb-0"><?php esc_html_e('Read More', 'codeweber'); ?></h5>
			</figcaption>
		</figure>

		<div class="card-body">
			<div class="post-header">
				<h2 class="post-title mt-1 mb-0"><a class="link-dark" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			</div>
			<!-- /.post-header -->
			<div class="post-content">
				<p><?php the_excerpt(); ?></p>
			</div>
			<!-- /.post-content -->
		</div>
	</div>
</article> <!-- #post-<?php the_ID(); ?> -->