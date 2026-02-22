<?php
/**
 * Single related block: "You Might Also Like" для постов блога.
 * Args via get_query_var( 'codeweber_single_related_type', 'none' ):
 * - 'blog_slider' — заголовок + cw_blog_posts_slider()
 * - 'none' — ничего не выводим
 */
if (!defined('ABSPATH')) {
	return;
}
$related_type = get_query_var('codeweber_single_related_type', 'none');
if ($related_type !== 'blog_slider') {
	return;
}
?>
<h3 class="mb-6"><?php echo esc_html__('You Might Also Like', 'codeweber'); ?></h3>
<?php
if (function_exists('cw_blog_posts_slider')) {
	echo cw_blog_posts_slider([
		'posts_per_page' => 6,
		'template' => 'default',
		'enable_hover_scale' => true,
		'show_title' => true,
		'show_date' => true,
		'show_category' => true,
		'show_comments' => true,
		'title_tag' => 'h3',
		'title_length' => 50,
		'image_size' => 'codeweber_post_560-350',
		'items_xl' => '2',
		'items_lg' => '2',
		'items_md' => '2',
		'items_sm' => '1',
		'items_xs' => '1',
		'items_xxs' => '1',
	]);
}
