<?php
/**
 * Template: Vacancies Archive - Style 6 (Horizontal cards, one per row, whole card clickable)
 *
 * Full duplicate of vacancies_5 layout: one horizontal card per row.
 * Uses style6-card: no "Перейти" button, no hover on photo, entire card is a link.
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
			<?php get_template_part('templates/post-cards/vacancies/style6-card'); ?>
		</div>
		<?php
	endwhile;
	?>
</div>
<?php
endif;
