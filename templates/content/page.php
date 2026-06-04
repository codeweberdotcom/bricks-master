<?php get_header(); ?>
<?php while (have_posts()) {
	the_post();
	get_pageheader();
	global $opt_name;

	// Per-post тип заголовка ('1' standard / '2' custom / '3' disable).
	$ph_type = Redux::get_post_meta($opt_name, get_the_ID(), 'this-page-header-type');

	// Эффективная модель заголовка: single → fallback global.
	$pageheader_name = Redux::get_option($opt_name, 'single_page_header_select_page');
	if ($pageheader_name === 'default' || empty($pageheader_name)) {
		$pageheader_name = Redux::get_option($opt_name, 'global_page_header_model');
	}
?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php
		// Заголовок выводим в контенте только для модели «1» (только крошки),
		// и не при Custom ('2') или Disable ('3') — иначе будет дубль или лишний тайтл.
		if ($pageheader_name === '1' && !is_front_page() && $ph_type !== '2' && $ph_type !== '3') { ?>
			<div class="container py-14">
				<div class="row align-items-center mb-10 position-relative zindex-1">
					<div class="col-md-8 col-lg-9 col-xl-8 col-xxl-7">
						<?php echo universal_title('h1','theme'); ?>
					</div>
				</div>
			</div>
		<?php } ?>
		<?php
		the_content();

		wp_link_pages(
			array(
				'before'        => '<nav class="nav"><span class="nav-link">' . esc_html__('Part:', 'codeweber') . '</span>',
				'after'         => '</nav>',
				'link_before'   => '<span class="nav-link">',
				'link_after'    => '</span>',
			)
		);
		?>
	</article> <!-- #post-<?php the_ID(); ?> -->

<?php } ?>
<?php
get_footer();
