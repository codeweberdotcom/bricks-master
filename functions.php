<?php

/**
 *  https://developer.wordpress.org/themes/basics/theme-functions/
 */

require_once get_template_directory() . '/functions/setup.php'; // --- Theme setup ---

require_once get_template_directory() . '/functions/enqueues.php'; // --- Include CSS & JavaScript ---

require_once get_template_directory() . '/functions/images.php'; // --- Image settings ---

require_once get_template_directory() . '/functions/navmenus.php'; // --- Register navmenus ---

require_once get_template_directory() . '/functions/sidebars.php'; // --- Register sidebars ---

require_once get_template_directory() . '/functions/lib/class-wp-bootstrap-navwalker.php'; // --- Nav Walker ---

foreach (glob(get_template_directory() . '/functions/cpt/*.php') as $cpt) {
	require_once $cpt;
}; // --- Register Custom Post Types & Taxonomies ---

require_once get_template_directory() . '/functions/global.php'; // --- Various global functions ---


// require_once get_template_directory() . '/functions/integrations/cf7.php'; // --- Contact Form 7 integration ---

// require_once get_template_directory() . '/functions/searchfilter.php'; // --- Search results filter ---

require_once get_template_directory() . '/functions/cleanup.php'; // --- Cleanup ---

require_once get_template_directory() . '/functions/custom.php'; // --- Custom user functions ---

require_once get_template_directory() . '/functions/options_page/options-page.php'; // --- Options page ---




function my_customize_register($wp_customize)
{
	// 1. Создаем родительскую вкладку "CPT Settings"
	$wp_customize->add_panel('cpt_settings_panel', [
		'title'    => __('CPT Settings', 'mytheme'),
		'priority' => 30,  // Позиция вкладки в Customizer
	]);

	// 2. Получаем все кастомные типы записей (CPT)
	$cpt_args = [
		'public' => true,
		'_builtin' => false, // Только пользовательские типы записей
	];
	$cpts = get_post_types($cpt_args, 'objects');

	// 3. Для каждого типа записи создаем вкладку и под-вкладку
	foreach ($cpts as $cpt) {
		$cpt_name = $cpt->name;

		// 4. Создаем под-вкладку для каждого CPT
		$wp_customize->add_section($cpt_name . '_settings_section', [
			'title'    => ucfirst($cpt_name) . ' Settings', // Название под-вкладки
			'priority' => 10,  // Позиция под-вкладки
			'panel'    => 'cpt_settings_panel',  // Родительская вкладка
		]);

		// Пример настройки для выбора цвета для этого CPT
		$wp_customize->add_setting($cpt_name . '_color_picker', [
			'default'   => 'primary',
			'transport' => 'refresh',
		]);

		// Список цветов из JSON
		$colors_json = file_get_contents(get_template_directory() . '/colors.json');
		$colors = json_decode($colors_json, true);  // Декодируем JSON в массив

		$color_choices = array_reduce($colors, function ($choices, $color) {
			$choices[$color['value']] = $color['label'];
			return $choices;
		}, []);

		// Добавляем выпадающий список для выбора цвета
		$wp_customize->add_control($cpt_name . '_color_control', [
			'label'    => __('Select Color', 'mytheme'),
			'section'  => $cpt_name . '_settings_section',
			'settings' => $cpt_name . '_color_picker',
			'type'     => 'select',
			'choices'  => $color_choices,
		]);
	}
}
add_action('customize_register', 'my_customize_register');