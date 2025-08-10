<article id="post-<?php the_ID(); ?>" <?php post_class('post'); ?>>
	<div class="card">
		<figure class="card-img-top overlay overlay-1 hover-scale">
			<a href="<?php the_permalink(); ?>">
				<?php
				the_post_thumbnail(
					'codeweber_single',
					array(
						'class' => 'img-fluid mb-3',
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
				<div class="post-category text-line">
					<a href="#" class="hover" rel="category"><?php the_category(', '); ?></a>
				</div>
				<!-- /.post-category -->
				<h2 class="post-title mt-1 mb-0"><a class="link-dark" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			</div>
			<!-- /.post-header -->
			<div class="post-content">
				<p><?php the_excerpt(); ?></p>
			</div>
			<!-- /.post-content -->
		</div>

		<div class="card-footer">
			<ul class="post-meta d-flex mb-0">
				<li class="post-date">
					<i class="uil uil-calendar-alt"></i>
					<span><?php the_time(get_option('date_format')); ?></span>
				</li>

				<li class="post-author">
					<a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>">
						<i class="uil uil-user"></i>
						<span><?php printf(esc_html__('Author %s', 'codeweber'), get_the_author()); ?></span>
					</a>
				</li>


				<li class="post-comments">
					<a href="<?php comments_link(); ?>">
						<i class="uil uil-comment"></i>
						<?php
						$comments_number = get_comments_number();
						echo esc_html($comments_number) . '<span> ' . _n('Comment', 'Comments', $comments_number, 'codeweber') . '</span>';
						?>
					</a>
				</li>

				<li class="post-likes ms-auto">
					<a href="#">
						<i class="uil uil-heart-alt"></i>
						<?php echo get_post_meta(get_the_ID(), 'likes', true) ?: 0; ?>
					</a>
				</li>
			</ul>

		</div>




	</div>
</article> <!-- #post-<?php the_ID(); ?> -->