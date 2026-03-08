<hr class="mt-13 mt-md-15 mb-7">
<div class="d-md-flex align-items-center justify-content-between">
<p class="mb-2 mb-lg-0"><a class="text-white" href="<?php echo esc_attr(wp_get_theme()->get('ThemeURI')); ?>" target="_blank">
			Made with Codeweber
		</a></p>
<?php
if (function_exists('social_links')) {
	// Простые цветные иконки без обводок и фона (type3)
	echo social_links('text-md-end', 'type3', 'md', 'primary', 'solid', 'circle');
}
?>
	<!-- /.social -->
</div>