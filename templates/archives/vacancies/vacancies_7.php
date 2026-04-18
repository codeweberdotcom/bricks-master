<?php
/**
 * Template: Vacancies Archive — Style 7 (Avatar cards grid)
 *
 * Сетка компактных карточек с цветными аватарами (инициалы), badge типа
 * вакансии, название и локация. 3 колонки на desktop, 2 на tablet, 1 на mobile.
 *
 * Использует карточку templates/post-cards/vacancies/avatar-card.php.
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

if ( have_posts() ) :
	?>
	<div class="row gy-6 mb-5">
		<?php
		$avatar_index = 0;
		while ( have_posts() ) :
			the_post();
			?>
			<div class="col-md-6 col-lg-4">
				<?php
				get_template_part(
					'templates/post-cards/vacancies/avatar-card',
					null,
					[ 'avatar_index' => $avatar_index ]
				);
				$avatar_index++;
				?>
			</div>
		<?php endwhile; ?>
	</div>
	<?php
endif;
