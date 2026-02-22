<?php
/**
 * Обёртки для единых блоков single: post-footer, post-author, related.
 * Одинаковые настройки для single post и single legal (кроме related только у поста).
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Дефолтные аргументы для кнопки «Поделиться» в single (блог/legal).
 * Одиначный вид на всех single.
 *
 * @return array
 */
function codeweber_single_share_args()
{
	$button_class = 'has-ripple btn btn-red btn-sm btn-icon btn-icon-start dropdown-toggle mb-0 me-0';
	if (function_exists('getThemeButton')) {
		$button_class .= getThemeButton();
	}
	return [
		'region'       => 'ru',
		'button_class' => $button_class,
	];
}

/**
 * Выводит блок post-footer (теги + кнопка «Поделиться»).
 *
 * @param array $args {
 *     @type array  $share_args  Аргументы для codeweber_share_page(). По умолчанию codeweber_single_share_args().
 *     @type bool   $show_tags   Показывать теги. По умолчанию true.
 * }
 */
function codeweber_single_post_footer($args = [])
{
	$defaults = [
		'share_args' => codeweber_single_share_args(),
		'show_tags'   => true,
	];
	$args = wp_parse_args($args, $defaults);
	set_query_var('codeweber_single_footer_args', $args);
	get_template_part('templates/content/single-parts/post-footer');
}

/**
 * Выводит навигацию по страницам записи (wp_link_pages) для постов с <!--nextpage-->.
 */
function codeweber_single_link_pages()
{
	echo '<div>';
	wp_link_pages([
		'before'      => '<nav class="nav"><span class="nav-link">' . esc_html__('Part:', 'codeweber') . '</span>',
		'after'       => '</nav>',
		'link_before' => '<span class="nav-link">',
		'link_after'  => '</span>',
	]);
	echo '</div>';
}

/**
 * Выводит блок автора записи (аватар, имя, должность, «Все статьи», опционально био).
 *
 * @param array $args {
 *     @type bool $show_bio Показывать описание автора. По умолчанию true.
 * }
 */
function codeweber_single_post_author($args = [])
{
	$defaults = [
		'show_bio' => true,
	];
	$args = wp_parse_args($args, $defaults);
	set_query_var('codeweber_single_author_args', $args);
	get_template_part('templates/content/single-parts/post-author');
}

/**
 * Выводит блок «You Might Also Like» только для типа post; для legal и др. — ничего.
 *
 * @param string $post_type Тип записи (post, legal, …).
 */
function codeweber_single_related($post_type = null)
{
	if ($post_type === null) {
		$post_type = get_post_type();
	}
	$related_type = ($post_type === 'post') ? 'blog_slider' : 'none';
	set_query_var('codeweber_single_related_type', $related_type);
	get_template_part('templates/content/single-parts/related-posts');
}

/**
 * Выводит блок комментариев: разделитель и comments_template(), если комментарии открыты или есть.
 */
function codeweber_single_comments()
{
	if (comments_open() || get_comments_number()) {
		echo '<hr class="my-5" />';
		comments_template();
	}
}
