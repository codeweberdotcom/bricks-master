<?php
/**
 * Template: Vacancies Archive - Style 3 (Grid with Post Cards)
 *
 * Grid of vacancy cards: logo, company, title, level badge, salary, "Read more".
 * Card markup: templates/post-cards/vacancies/style3-card.php
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
			<?php get_template_part('templates/post-cards/vacancies/style3-card'); ?>
		</div>
		<?php
	endwhile;
	?>
</div>
<?php
endif;
