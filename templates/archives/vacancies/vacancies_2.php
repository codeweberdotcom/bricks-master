<?php
/**
 * Template: Vacancies Archive - Style 2 (Card Grid)
 *
 * Vacancy cards in grid: avatar, title, salary, location.
 * Card markup: templates/post-cards/vacancies/grid-card.php
 *
 * @package Codeweber
 */

if (have_posts()) :
?>
<div class="row <?php echo esc_attr( Codeweber_Options::style( 'grid-gap' ) ); ?> mb-5">
	<?php
	while (have_posts()) :
		the_post();
		?>
		<div class="col-md-6 col-lg-4">
			<?php get_template_part('templates/post-cards/vacancies/grid-card'); ?>
		</div>
		<?php
	endwhile;
	?>
</div>
<?php
endif;
