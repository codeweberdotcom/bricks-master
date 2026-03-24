<?php

/**
 * Функция для настройки темы и активации поддержки логотипов и других опций
 */

if (! function_exists('codeweber_setup_theme')) {

	function codeweber_setup_theme()
	{

		// Включаем поддержку миниатюр
		add_theme_support('post-thumbnails');

		// Включаем RSS ленты
		add_theme_support('automatic-feed-links');

		// Включаем HTML5 разметку
		add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));

		// Включаем title meta tag в <head>
		add_theme_support('title-tag');

		// Включаем обновление виджетов через кастомизатор
		add_theme_support('customize-selective-refresh-widgets');

		// Поддержка WooCommerce
		add_theme_support('woocommerce');

		// Включаем поддержку хлебных крошек Rank Math и Yoast SEO
		add_theme_support('rank-math-breadcrumbs');
		add_theme_support('yoast-seo-breadcrumbs');

		// Устанавливаем максимальную ширину контента (встраиваемое содержимое)
		if (! isset($content_width)) {
			$content_width = 1400;
		}

		// Добавляем поддержку логотипа для темной темы
		add_theme_support('custom-logo', array(
			'height'      => 100,
			'width'       => 400,
			'flex-height' => true,
			'flex-width'  => true,
			'header-text' => array('site-title', 'site-description'),
		));
	}

	// Регистрируем функцию для настройки темы
	add_action('after_setup_theme', 'codeweber_setup_theme');
}



/**
 * Загрузка переводов в правильный момент
 */
function load_codeweber_translations()
{
	load_theme_textdomain('codeweber', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'load_codeweber_translations', 10);

/**
 * Сброс permalink'ов при активации темы.
 * Предотвращает 404 для CPT-маршрутов сразу после смены темы.
 */
add_action( 'after_switch_theme', 'flush_rewrite_rules' );

/**
 * Инициализация Redux Framework
 */
function codeweber_initialize_redux()
{
	if ( ! class_exists( 'Redux' ) ) {
		require_once get_template_directory() . '/redux-framework/redux-core/framework.php';
	}

	global $opt_name;
	$opt_name = 'redux_demo';
	require_once get_template_directory() . '/redux-framework/sample/theme-config.php';
	require_once get_template_directory() . '/functions/cpt/redux_cpt.php';
	require_once get_template_directory() . '/functions/sidebars-redux.php';

	if ( file_exists( get_template_directory() . '/redux-framework/theme-settings/theme-settings.php' ) ) {
		require_once get_template_directory() . '/redux-framework/theme-settings/theme-settings.php';
	}
}
add_action( 'after_setup_theme', 'codeweber_initialize_redux', 30 );
