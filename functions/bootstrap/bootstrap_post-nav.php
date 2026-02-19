<?php

/**
 * Навигация «предыдущая / следующая запись» для single.
 * Вывод в стиле single.php: ссылки с классами hover more-left / hover more.
 */
function codeweber_posts_nav()
{
	$previous_post = get_adjacent_post(false, '', true);
	$next_post    = get_adjacent_post(false, '', false);

	if (!$previous_post && !$next_post) {
		return;
	}

	echo '<nav class="nav mt-8 justify-content-between">';

	if ($previous_post) {
		printf(
			'<a href="%s" class="hover more-left me-4 mb-5">%s</a>',
			esc_url(get_permalink($previous_post->ID)),
			esc_html__('Previous', 'codeweber')
		);
	}

	if ($next_post) {
		printf(
			'<a href="%s" class="hover more ms-auto mb-5">%s</a>',
			esc_url(get_permalink($next_post->ID)),
			esc_html__('Next', 'codeweber')
		);
	}

	echo '</nav>';
}
