<?php
return array(
	'title'  => 'Child Theme',
	'id'     => 'child_theme_section',
	'desc'   => 'Настройки дочерней темы.',
	'fields' => array(
		array(
			'id'       => 'enable_child_theme',
			'type'     => 'switch',
			'title'    => 'Включить дочернюю тему',
			'subtitle' => 'Активируйте, если вы хотите использовать дочернюю тему.',
			'default'  => false,
		),
		array(
			'id'       => 'child_theme_description',
			'type'     => 'textarea',
			'title'    => 'Описание дочерней темы',
			'subtitle' => 'Введите описание или инструкции для дочерней темы.',
			'default'  => 'Используйте дочернюю тему для кастомизации без изменений в основной теме.',
		),
		array(
			'id'       => 'child_theme_style',
			'type'     => 'media',
			'title'    => 'Файл стилей дочерней темы',
			'subtitle' => 'Загрузите или укажите путь к файлу стилей дочерней темы.',
		),
	),
);
