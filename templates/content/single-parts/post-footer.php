<?php
/**
 * Single post footer: tags + share button.
 * Args via get_query_var( 'codeweber_single_footer_args', [] ):
 * - share_args (array) — аргументы для codeweber_share_page()
 * - show_tags (bool) — показывать теги. По умолчанию true.
 */
if (!defined('ABSPATH')) {
	return;
}
$args = get_query_var('codeweber_single_footer_args', []);
$show_tags = isset($args['show_tags']) ? $args['show_tags'] : true;
$share_args = isset($args['share_args']) ? $args['share_args'] : [];
?>
<div class="post-footer d-md-flex flex-md-row justify-content-md-between align-items-center mt-8">
	<div>
		<?php
		if ($show_tags) {
			$tags = get_the_tags();
			if ($tags) :
		?>
			<ul class="list-unstyled tag-list mb-0">
				<?php foreach ($tags as $tag) : ?>
					<li>
						<a
							href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"
							class="btn btn-soft-ash btn-sm mb-0<?php echo function_exists('getThemeButton') ? getThemeButton() : ''; ?>">
							<?php echo esc_html($tag->name); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php
			endif;
		}
		?>
	</div>
	<div class="mb-0 mb-md-2">
		<?php
		if (function_exists('codeweber_share_page')) {
			codeweber_share_page($share_args);
		}
		?>
	</div>
</div>
