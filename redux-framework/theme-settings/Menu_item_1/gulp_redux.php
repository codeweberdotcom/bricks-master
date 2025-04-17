<?php
return array(
	'title'  => 'Gulp',
	'id'     => 'gulp_section',
	'desc'   => 'Настройки Gulp.',
	'fields' => array(
		array(
			'id'       => 'run_gulp',
			'type'     => 'js_button',
			'title'    => 'Запустить Gulp',
			'script'   => array(
				'url'       => './gulp_handler.js',
				'dep'       => array('jquery'),
				'ver'       => time(),
				'in_footer' => true,
			),
			'buttons'  => array(
				array(
					'text'     => 'Запуск',
					'class'    => 'button-primary',
					'function' => 'run_gulp_task',
				),
			),
		),
	),
);



