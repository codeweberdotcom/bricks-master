<?php

/**
 * Навигация «предыдущая / следующая запись» для single.
 * Вывод в стиле single.php: ссылки с классами hover more-left / hover more.
 */
function codeweber_posts_nav()
{
	$previous_post = get_adjacent_post(false, '', true);
	$next_post    = get_adjacent_post(false, '', false);

	if (!$previous_post && !$next_post) {
		return;
	}

	echo '<nav class="nav mt-8 justify-content-between">';

	if ($previous_post) {
		printf(
			'<a href="%s" class="hover more-left me-4 mb-5">%s</a>',
			esc_url(get_permalink($previous_post->ID)),
			esc_html__('Previous', 'codeweber')
		);
	}

	if ($next_post) {
		printf(
			'<a href="%s" class="hover more ms-auto mb-5">%s</a>',
			esc_url(get_permalink($next_post->ID)),
			esc_html__('Next', 'codeweber')
		);
	}

	echo '</nav>';
}

/**
 * Навигация для single CPT Projects.
 * Тип выбирается в Redux: text (ссылки) или buttons (кнопки + Share).
 */
function codeweber_projects_nav() {
	$nav_type = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::get( 'projects_nav_type', 'text' ) : 'text';

	if ( $nav_type !== 'buttons' ) {
		codeweber_posts_nav();
		return;
	}

	$prev_post = get_adjacent_post( false, '', true );
	$next_post = get_adjacent_post( false, '', false );

	if ( ! $prev_post && ! $next_post ) {
		return;
	}

	$btn_style = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';
	?>
	<section class="wrapper bg-light">
		<div class="container py-10">
			<div class="row gx-md-6 gy-3 gy-md-0">
				<div class="col-md-8 align-self-center text-center text-md-start navigation">
					<?php if ( $prev_post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="btn btn-soft-ash<?php echo esc_attr( $btn_style ); ?> btn-icon btn-icon-start mb-0 me-1">
						<i class="uil uil-arrow-left"></i> <?php esc_html_e( 'Prev', 'codeweber' ); ?>
					</a>
					<?php endif; ?>
					<?php if ( $next_post ) : ?>
					<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="btn btn-soft-ash<?php echo esc_attr( $btn_style ); ?> btn-icon btn-icon-end mb-0">
						<?php esc_html_e( 'Next', 'codeweber' ); ?> <i class="uil uil-arrow-right"></i>
					</a>
					<?php endif; ?>
				</div>
				<!--/column -->
				<aside class="col-md-4 sidebar text-center text-md-end">
					<?php codeweber_share_page(); ?>
				</aside>
				<!-- /column .sidebar -->
			</div>
			<!--/.row -->
		</div>
		<!-- /.container -->
	</section>
	<?php
}
