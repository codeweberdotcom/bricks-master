<?php
function cptui_register_my_cpts_html_blocks()
{

	/**
	 * Post Type: Html Blocks.
	 */

	$labels = [
		"name" => esc_html__("Html Blocks", "codeweber"),
		"singular_name" => esc_html__("Html Block", "codeweber"),
		"menu_name" => esc_html__("Html Blocks", "codeweber"),
		"all_items" => esc_html__("All Html Blocks", "codeweber"),
		"add_new" => esc_html__("Add New Html Block", "codeweber"),
		"add_new_item" => esc_html__("Add Html Block", "codeweber"),
		"edit_item" => esc_html__("Edit Html Block", "codeweber"),
		"new_item" => esc_html__("New Html Block", "codeweber"),
		"view_item" => esc_html__("View Html Block", "codeweber"),
		"view_items" => esc_html__("View Html Blocks", "codeweber"),
		"search_items" => esc_html__("Search Html Blocks", "codeweber"),
		"not_found" => esc_html__("No Html Blocks found", "codeweber"),
		"items_list" => esc_html__("Html Blocks list", "codeweber"),
		"name_admin_bar" => esc_html__("Html Block", "codeweber"),
		"item_published" => esc_html__("Html Block published", "codeweber"),
		"item_updated" => esc_html__("Html Block updated", "codeweber"),
	];

	$args = [
		"label" => esc_html__("Html Blocks", "codeweber"),
		"labels" => $labels,
		"description" => "",
		"public" => false,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => false,
		"rewrite" => false, // Не отображать Single и Archive на фронтенде
		"query_var" => false,
		"supports" => ["title", "editor"],
		"show_in_graphql" => false,
	];

	register_post_type("html_blocks", $args);
}

add_action('init', 'cptui_register_my_cpts_html_blocks');

/**
 * Запрет отображения Single и Archive Html Blocks на фронтенде — отдаём 404.
 */
add_action('template_redirect', function () {
	if (is_singular('html_blocks') || is_post_type_archive('html_blocks')) {
		global $wp_query;
		$wp_query->set_404();
		status_header(404);
	}
});

/**
 * Shortcode [html_block id="X"] — выводит контент HTML-блока по ID
 *
 * @param array $atts Атрибуты: id — ID поста html_blocks
 * @return string HTML-контент блока или пустая строка
 */
function codeweber_html_block_shortcode($atts)
{
	$atts = shortcode_atts(['id' => ''], $atts, 'html_block');
	if (empty($atts['id'])) {
		return '';
	}
	$post = get_post((int) $atts['id']);
	if (!$post || $post->post_type !== 'html_blocks' || $post->post_status !== 'publish') {
		return '';
	}
	return do_shortcode(apply_filters('the_content', $post->post_content));
}
add_shortcode('html_block', 'codeweber_html_block_shortcode');

/**
 * Добавляет колонку Shortcode в список HTML Blocks
 */
function codeweber_add_html_blocks_shortcode_column($columns)
{
	$new_columns = [];
	foreach ($columns as $key => $value) {
		$new_columns[$key] = $value;
		if ($key === 'title') {
			$new_columns['shortcode'] = __('Shortcode', 'codeweber');
		}
	}
	return $new_columns;
}
add_filter('manage_html_blocks_posts_columns', 'codeweber_add_html_blocks_shortcode_column');

/**
 * Заполняет колонку Shortcode
 */
function codeweber_fill_html_blocks_shortcode_column($column, $post_id)
{
	if ($column !== 'shortcode') {
		return;
	}
	$shortcode = '[html_block id="' . esc_attr($post_id) . '"]';
	?>
	<div style="display: flex; align-items: center; gap: 8px;">
		<code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-family: monospace;">
			<?php echo esc_html($shortcode); ?>
		</code>
		<button
			type="button"
			class="button button-small copy-shortcode-btn"
			data-shortcode="<?php echo esc_attr($shortcode); ?>"
			style="height: 24px; line-height: 22px; padding: 0 8px;"
			title="<?php esc_attr_e('Copy shortcode', 'codeweber'); ?>"
		>
			<span class="dashicons dashicons-clipboard" style="font-size: 14px; width: 14px; height: 14px; line-height: 22px;"></span>
		</button>
	</div>
	<?php
}
add_action('manage_html_blocks_posts_custom_column', 'codeweber_fill_html_blocks_shortcode_column', 10, 2);

/**
 * Подключает скрипт копирования шорткода на странице списка HTML Blocks
 */
function codeweber_html_blocks_copy_shortcode_script()
{
	$screen = get_current_screen();
	if (!$screen || $screen->post_type !== 'html_blocks' || $screen->base !== 'edit') {
		return;
	}
	?>
	<script type="text/javascript">
	(function($) {
		$(document).ready(function() {
			$(document).on('click', '.copy-shortcode-btn', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var shortcode = $btn.data('shortcode');
				var $temp = $('<textarea>');
				$('body').append($temp);
				$temp.val(shortcode).select();
				try {
					document.execCommand('copy');
					$temp.remove();
					var $originalIcon = $btn.find('.dashicons');
					$originalIcon.removeClass('dashicons-clipboard').addClass('dashicons-yes-alt');
					$btn.css('color', '#46b450');
					setTimeout(function() {
						$originalIcon.removeClass('dashicons-yes-alt').addClass('dashicons-clipboard');
						$btn.css('color', '');
					}, 2000);
				} catch (err) {
					$temp.remove();
					alert('<?php echo esc_js(__('Failed to copy shortcode', 'codeweber')); ?>');
				}
			});
		});
	})(jQuery);
	</script>
	<?php
}
add_action('admin_footer', 'codeweber_html_blocks_copy_shortcode_script');
