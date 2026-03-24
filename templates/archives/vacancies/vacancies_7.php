<?php
/**
 * Template: Vacancies Archive - Style 7 (Horizontal cards, one per row, whole card clickable, square photo)
 *
 * Same as vacancies_6 but uses square image size (600×600).
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
		<div class="col-12">
			<?php get_template_part('templates/post-cards/vacancies/style7-card'); ?>
		</div>
		<?php
	endwhile;
	?>
</div>
<?php
endif;
