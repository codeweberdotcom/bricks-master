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

		add_theme_support('woocommerce');

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
 * Настройки кастомизатора для добавления логотипа темной темы и удаления чекбокса "Отображать название и описание"
 */
function my_customizer_settings($wp_customize)
{

	// Удаляем чекбокс "Отображать название и описание"
	$wp_customize->remove_control('display_header_text'); // Убираем чекбокс "Отображать название и описание"

	// Добавление поля для логотипа темной темы
	$wp_customize->add_setting('custom_dark_logo', array(
		'default'   => '',
		'transport' => 'refresh',
	));

	// Добавляем контрол для логотипа темной темы
	$wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'custom_dark_logo_control', array(
		'label'      => __('Dark Logo', 'codeweber'),
		'section'    => 'title_tagline', // Секция для логотипа
		'settings'   => 'custom_dark_logo',
		'mime_type'  => 'image',
		'priority'   => 15, // Устанавливаем приоритет, чтобы это поле шло сразу после custom-logo
	)));

	// Изменяем приоритет для иконки сайта, чтобы она шла после краткого описания
	$wp_customize->get_control('site_icon')->priority = 40;
}

add_action('customize_register', 'my_customizer_settings');
