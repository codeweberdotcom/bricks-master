<?php
/**
 * Template: Vacancies Archive - Style 5 (Horizontal cards, one per row)
 *
 * One horizontal card per row: image left (4 cols), content right (8 cols). Card is clickable.
 * Card markup: templates/post-cards/vacancies/style4-card.php
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
		<div class="col-12">
			<?php get_template_part('templates/post-cards/vacancies/style4-card'); ?>
		</div>
		<?php
	endwhile;
	?>
</div>
<?php
endif;
