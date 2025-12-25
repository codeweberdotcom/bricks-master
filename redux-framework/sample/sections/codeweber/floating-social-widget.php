<?php

/**
 * Redux Framework floating social widget config.
 * For full documentation, please visit: https://devs.redux.io/
 *
 * @package Redux Framework
 */

defined('ABSPATH') || exit;

// Функция для получения списка Unicons иконок из темы
// Использует тот же метод парсинга, что и плагин codeweber-gutenberg-blocks (ImageHotspotCPT::get_unicons_list())
function codeweber_get_unicons_icons() {
	// Используем путь к плагину, как в ImageHotspotCPT
	if (defined('WP_PLUGIN_DIR')) {
		$icons_file = WP_PLUGIN_DIR . '/codeweber-gutenberg-blocks/src/utilities/font_icon.js';
	} else {
		$icons_file = ABSPATH . 'wp-content/plugins/codeweber-gutenberg-blocks/src/utilities/font_icon.js';
	}
	
	$icons = array();
	
	// Если файл существует, парсим его (используем тот же метод, что и плагин)
	if (file_exists($icons_file)) {
		$content = file_get_contents($icons_file);
		
		// Используем тот же метод парсинга, что и ImageHotspotCPT::get_unicons_list()
		// Извлекаем весь массив fontIcons
		if (preg_match('/export\s+const\s+fontIcons\s*=\s*\[(.*?)\];/s', $content, $matches)) {
			if (!empty($matches[1])) {
				// Парсим строки вида { value: 'uil-icon-name', label: 'icon-name' },
				// Поддерживаем табы, пробелы и переносы строк
				preg_match_all("/\{\s*value:\s*['\"]([^'\"]+)['\"],\s*label:\s*['\"]([^'\"]+)['\"]\s*\}/", $matches[1], $icon_matches, PREG_SET_ORDER);
				
				foreach ($icon_matches as $match) {
					$full_icon_name = $match[1]; // Полное имя с префиксом uil-
					$icon_name = str_replace('uil-', '', $full_icon_name); // Убираем префикс uil-
					$label = $match[2]; // Label из файла
					
					// Сохраняем только имя иконки без префикса uil-
					$icons[$icon_name] = ucwords(str_replace('-', ' ', $label));
				}
			}
		}
	}
	
	// Если не удалось загрузить из файла, используем базовый набор
	if (empty($icons)) {
		$icons = array(
			'comment-dots' => 'Comment Dots',
			'comment' => 'Comment',
			'comments' => 'Comments',
			'phone' => 'Phone',
			'envelope' => 'Envelope',
			'whatsapp' => 'WhatsApp',
			'telegram' => 'Telegram',
			'vk' => 'VK',
		);
	}
	
	// Сортируем по алфавиту
	asort($icons);
	
	return $icons;
}

// Функция для получения списка доступных соцсетей
function codeweber_get_available_socials() {
	$socials = get_option('socials_urls', array());
	$available_socials = array('' => esc_html__('-- Select Social Network --', 'codeweber'));
	
	if (!empty($socials)) {
		$social_labels = array(
			'max'         => esc_html__('Max', 'codeweber'),
			'telegram'    => esc_html__('Telegram', 'codeweber'),
			'whatsapp'    => esc_html__('WhatsApp', 'codeweber'),
			'viber'       => esc_html__('Viber', 'codeweber'),
			'vk'          => esc_html__('VKontakte', 'codeweber'),
			'odnoklassniki' => esc_html__('Odnoklassniki', 'codeweber'),
			'rutube'      => esc_html__('Rutube', 'codeweber'),
			'vkvideo'     => esc_html__('VK Video', 'codeweber'),
			'yandex-dzen' => esc_html__('Yandex Dzen', 'codeweber'),
			'vkmusic'     => esc_html__('VK Music', 'codeweber'),
			'instagram'   => esc_html__('Instagram', 'codeweber'),
			'facebook'    => esc_html__('Facebook', 'codeweber'),
			'tik-tok'     => esc_html__('TikTok', 'codeweber'),
			'youtube'     => esc_html__('YouTube', 'codeweber'),
			'dropbox'     => esc_html__('Dropbox', 'codeweber'),
			'googledrive' => esc_html__('Google Drive', 'codeweber'),
			'googleplay'  => esc_html__('Google Play', 'codeweber'),
			'vimeo'       => esc_html__('Vimeo', 'codeweber'),
			'patreon'     => esc_html__('Patreon', 'codeweber'),
			'meetup'      => esc_html__('Meetup', 'codeweber'),
			'itunes'      => esc_html__('iTunes', 'codeweber'),
			'figma'       => esc_html__('Figma', 'codeweber'),
			'behance'     => esc_html__('Behance', 'codeweber'),
			'pinterest'   => esc_html__('Pinterest', 'codeweber'),
			'dripple'     => esc_html__('Dripple', 'codeweber'),
			'linkedin'    => esc_html__('LinkedIn', 'codeweber'),
			'snapchat'    => esc_html__('Snapchat', 'codeweber'),
			'skype'       => esc_html__('Skype', 'codeweber'),
			'signal'      => esc_html__('Signal', 'codeweber'),
			'twitch'      => esc_html__('Twitch', 'codeweber'),
			'wechat'      => esc_html__('WeChat', 'codeweber'),
			'qq'          => esc_html__('QQ', 'codeweber'),
			'twitter'     => esc_html__('Twitter', 'codeweber'),
			'tumblr'      => esc_html__('Tumblr', 'codeweber'),
			'reddit'      => esc_html__('Reddit', 'codeweber'),
			'airbnb'      => esc_html__('Airbnb', 'codeweber'),
			'discord'     => esc_html__('Discord', 'codeweber'),
			'steam'       => esc_html__('Steam', 'codeweber'),
			'github'      => esc_html__('GitHub', 'codeweber'),
			'gitlab'      => esc_html__('GitLab', 'codeweber'),
			'codepen'     => esc_html__('CodePen', 'codeweber'),
		);
		
		foreach ($socials as $social_key => $social_url) {
			if (!empty($social_url)) {
				$label = isset($social_labels[$social_key]) ? $social_labels[$social_key] : ucfirst($social_key);
				$available_socials[$social_key] = $label;
			}
		}
	}
	
	return $available_socials;
}

// Функция для получения списка цветов темы
function codeweber_get_theme_colors() {
	$colors = array(
		'primary'   => esc_html__('Primary', 'codeweber'),
		'dark'      => esc_html__('Dark', 'codeweber'),
		'light'     => esc_html__('Light', 'codeweber'),
		'yellow'    => esc_html__('Yellow', 'codeweber'),
		'orange'    => esc_html__('Orange', 'codeweber'),
		'red'       => esc_html__('Red', 'codeweber'),
		'pink'      => esc_html__('Pink', 'codeweber'),
		'fuchsia'   => esc_html__('Fuchsia', 'codeweber'),
		'violet'    => esc_html__('Violet', 'codeweber'),
		'purple'     => esc_html__('Purple', 'codeweber'),
		'blue'      => esc_html__('Blue', 'codeweber'),
		'aqua'      => esc_html__('Aqua', 'codeweber'),
		'sky'       => esc_html__('Sky', 'codeweber'),
		'green'     => esc_html__('Green', 'codeweber'),
		'leaf'      => esc_html__('Leaf', 'codeweber'),
		'ash'       => esc_html__('Ash', 'codeweber'),
		'navy'      => esc_html__('Navy', 'codeweber'),
		'grape'     => esc_html__('Grape', 'codeweber'),
		'muted'     => esc_html__('Muted', 'codeweber'),
		'white'     => esc_html__('White', 'codeweber'),
		'pinterest' => esc_html__('Pinterest', 'codeweber'),
		'dewalt'    => esc_html__('Dewalt', 'codeweber'),
		'facebook'  => esc_html__('Facebook', 'codeweber'),
		'telegram'  => esc_html__('Telegram', 'codeweber'),
	);
	
	return $colors;
}


Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Floating Social Widget", "codeweber"),
		'id'               => 'floating-social-widget',
		'desc'             => esc_html__("Settings for floating social network widget", "codeweber"),
		'customizer_width' => '300px',
		'icon'             => 'el el-share-alt',
		'fields'           => array(
			array(
				'id'       => 'floating_widget_enabled',
				'type'     => 'switch',
				'title'    => esc_html__('Enable Floating Widget', 'codeweber'),
				'subtitle' => esc_html__('Show/hide floating social widget', 'codeweber'),
				'default'  => false,
			),
			
			array(
				'id'       => 'floating_widget_type',
				'type'     => 'button_set',
				'title'    => esc_html__('Widget Type', 'codeweber'),
				'subtitle' => esc_html__('Select widget display type', 'codeweber'),
				'options'  => array(
					'button' => esc_html__('Button', 'codeweber'),
					'icon'   => esc_html__('Icon', 'codeweber'),
				),
				'default'  => 'icon',
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'               => 'floating_widget_icon',
				'type'             => 'select',
				'title'            => esc_html__('Icon', 'codeweber'),
				'subtitle'         => esc_html__('Select icon from Unicons library', 'codeweber'),
				'options'          => codeweber_get_unicons_icons(),
				'default'          => 'comment-dots',
				'required'         => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_button_text',
				'type'     => 'text',
				'title'    => esc_html__('Button Text', 'codeweber'),
				'subtitle' => esc_html__('Text displayed on button', 'codeweber'),
				'default'  => esc_html__('Написать нам', 'codeweber'),
				'required' => array(
					array('floating_widget_enabled', '=', true),
					array('floating_widget_type', '=', 'button'),
				),
			),
			
			array(
				'id'       => 'floating_widget_button_color',
				'type'     => 'select',
				'title'    => esc_html__('Button Color', 'codeweber'),
				'subtitle' => esc_html__('Select button color from theme colors', 'codeweber'),
				'options'  => codeweber_get_theme_colors(),
				'default'  => 'primary',
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_animation_type',
				'type'     => 'button_set',
				'title'    => esc_html__('Animation Type', 'codeweber'),
				'subtitle' => esc_html__('Select animation type for widget', 'codeweber'),
				'options'  => array(
					'horizontal' => esc_html__('Horizontal', 'codeweber'),
					'vertical'   => esc_html__('Vertical', 'codeweber'),
				),
				'default'  => 'vertical',
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_width',
				'type'     => 'text',
				'title'    => esc_html__('Widget Width', 'codeweber'),
				'subtitle' => esc_html__('Widget width in pixels', 'codeweber'),
				'default'  => '180px',
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'          => 'floating_widget_socials',
				'type'        => 'repeater',
				'title'       => esc_html__('Social Networks', 'codeweber'),
				'subtitle'    => esc_html__('Add multiple social networks to display', 'codeweber'),
				'group_values' => true,
				'item_name'   => esc_html__('Social Network', 'codeweber'),
				'bind_title'  => 'social_network',
				'panels_closed' => false,
				'active'      => 0,
				'fields'      => array(
					array(
						'id'       => 'social_network',
						'type'     => 'select',
						'title'    => esc_html__('Social Network', 'codeweber'),
						'subtitle' => esc_html__('Select social network to link', 'codeweber'),
						'options'  => codeweber_get_available_socials(),
						'default'  => '',
					),
				),
				'default'     => array(),
				'required'    => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_right_offset',
				'type'     => 'text',
				'title'    => esc_html__('Right Offset', 'codeweber'),
				'subtitle' => esc_html__('Distance from right edge (enter number in px or "auto")', 'codeweber'),
				'default'  => '30px',
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_left_offset',
				'type'     => 'text',
				'title'    => esc_html__('Left Offset', 'codeweber'),
				'subtitle' => esc_html__('Distance from left edge (enter number in px or "auto")', 'codeweber'),
				'default'  => 'auto',
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_top_offset',
				'type'     => 'text',
				'title'    => esc_html__('Top Offset', 'codeweber'),
				'subtitle' => esc_html__('Distance from top edge (enter number in px or "auto")', 'codeweber'),
				'default'  => 'auto',
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_bottom_offset',
				'type'     => 'text',
				'title'    => esc_html__('Bottom Offset', 'codeweber'),
				'subtitle' => esc_html__('Distance from bottom edge (enter number in px or "auto")', 'codeweber'),
				'default'  => '30px',
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_z_index',
				'type'     => 'slider',
				'title'    => esc_html__('Z-Index', 'codeweber'),
				'subtitle' => esc_html__('Widget stacking order', 'codeweber'),
				'desc'     => esc_html__('Higher values appear on top. Min: 100, Max: 9999, Default: 999', 'codeweber'),
				'default'  => 999,
				'min'      => 100,
				'max'      => 9999,
				'step'     => 10,
				'required' => array('floating_widget_enabled', '=', true),
			),
		),
	)
);
