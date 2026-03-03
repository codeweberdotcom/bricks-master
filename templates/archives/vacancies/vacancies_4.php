<?php
/**
 * Template: Vacancies Archive - Style 4 (Grid of vertical cards)
 *
 * Grid like vacancies_3: col-md-6 col-lg-4, card with title, list (location, schedule, salary), "Read more" button.
 * Card markup: templates/post-cards/vacancies/style5-card.php
 *
 * @package Codeweber
 */

if (have_posts()) :
?>
<div class="row g-3 mb-5">
	<?php
	while (have_posts()) :
		the_post();
		?>
		<div class="col-md-6 col-lg-4">
			<?php get_template_part('templates/post-cards/vacancies/style5-card'); ?>
		</div>
		<?php
	endwhile;
	?>
</div>
<?php
endif;
