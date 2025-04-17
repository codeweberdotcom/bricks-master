<?php

// Проверяем, существует ли уже функция get_cpt_files_list
if (!function_exists('get_cpt_files_list')) {
	function get_cpt_files_list()
	{
		$directory = get_template_directory() . '/functions/cpt';

		if (is_dir($directory)) {
			$files = scandir($directory);

			// Фильтруем только те, что начинаются с "cpt-" и имеют расширение ".php"
			$cpt_files = array_filter($files, function ($file) use ($directory) {
				$is_valid = is_file($directory . '/' . $file)
					&& strpos($file, 'cpt-') === 0
					&& pathinfo($file, PATHINFO_EXTENSION) === 'php';
				return $is_valid;
			});

			return array_values($cpt_files); // Возвращаем отфильтрованный список
		}

		return [];
	}
}

// Получаем список файлов с кастомными типами записей
$cpt_list = get_cpt_files_list();

// Пример возвращаемого массива для Redux Framework
return array(
	'title'  => __('Custom Post Types', 'redux-framework'), // Название секции
	'id'     => 'cpt_section',
	'desc'   => __('Settings for custom post types.', 'redux-framework'), // Описание секции
	'fields' => array(
		// Переключатели для каждого кастомного типа записи
		...array_map(function ($file) {
			$label = ucwords(str_replace(array('cpt-', '.php'), '', $file)); // Преобразуем имя файла в читаемый формат

			// Убедимся, что у нас есть заголовок
			$label = $label ?: __('Unnamed', 'redux-framework');

			return array(
				'id'       => 'cpt_switch_' . sanitize_title($label), // Уникальный ID для переключателя
				'type'     => 'switch', // Тип поля: переключатель
				'title'    => __(ucwords(str_replace(array('cpt-', '.php'), '', $file)), 'redux-framework'), // Преобразование имени в читаемый формат с переводом
				'subtitle' => __('Enable/disable this post type', 'redux-framework'), // Описание переключателя
				'default'  => false, // По умолчанию выключено
				'on'       => __('Enabled', 'redux-framework'), // Переведённая строка для включения
				'off'      => __('Disabled', 'redux-framework'), // Переведённая строка для выключения
			);
		}, $cpt_list),
	),
	// Добавляем субсекции для каждого кастомного типа записи
	'subsections' => array_map(function ($file) {
		$label = ucwords(str_replace(array('cpt-', '.php'), '', $file)); // Преобразуем имя файла в читаемый формат

		// Убедимся, что у нас есть заголовок
		$label = $label ?: __('Unnamed', 'redux-framework');

		return array(
			'title'      => $label,
			'id'         => 'cpt_subsection_' . sanitize_title($label),
			'subsection' => true,
			'desc'       => sprintf(__('Settings for the %s custom post type.', 'redux-framework'), $label),
			'fields'     => array(
				array(
					'id'       => 'cpt_enable_' . sanitize_title($label),
					'type'     => 'switch',
					'title'    => sprintf(__('Enable %s', 'redux-framework'), $label),
					'subtitle' => __('Enable or disable this custom post type.', 'redux-framework'),
					'default'  => false,
					'on'       => __('Enabled', 'redux-framework'),
					'off'      => __('Disabled', 'redux-framework'),
				),
				array(
					'id'       => 'cpt_slug_' . sanitize_title($label),
					'type'     => 'text',
					'title'    => sprintf(__('Slug for %s', 'redux-framework'), $label),
					'subtitle' => __('Custom slug for the post type', 'redux-framework'),
					'default'  => sanitize_title($label),
				),
				array(
					'id'       => 'cpt_archive_' . sanitize_title($label),
					'type'     => 'switch',
					'title'    => sprintf(__('Enable archive for %s', 'redux-framework'), $label),
					'subtitle' => __('Enable or disable the archive page for this custom post type.', 'redux-framework'),
					'default'  => true,
					'on'       => __('Enabled', 'redux-framework'),
					'off'      => __('Disabled', 'redux-framework'),
				),
			),
		);
	}, $cpt_list),
);
