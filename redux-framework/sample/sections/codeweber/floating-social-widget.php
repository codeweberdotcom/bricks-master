<?php

/**
 * Redux Framework floating social widget config.
 * For full documentation, please visit: https://devs.redux.io/
 *
 * @package Redux Framework
 */

defined('ABSPATH') || exit;

// Функция для получения списка доступных соцсетей
function codeweber_get_available_socials() {
	$socials = get_option('socials_urls', array());
	$available_socials = array('' => esc_html__('-- Select Social Network --', 'codeweber'));
	
	if (!empty($socials)) {
		$social_labels = array(
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

// Популярные иконки Unicons для выбора
$unicons_icons = array(
	'' => esc_html__('-- Auto (based on social network) --', 'codeweber'),
	'uil-telegram' => esc_html__('Telegram', 'codeweber'),
	'uil-whatsapp' => esc_html__('WhatsApp', 'codeweber'),
	'uil-viber' => esc_html__('Viber', 'codeweber'),
	'uil-vk' => esc_html__('VKontakte', 'codeweber'),
	'uil-instagram' => esc_html__('Instagram', 'codeweber'),
	'uil-facebook-f' => esc_html__('Facebook', 'codeweber'),
	'uil-youtube' => esc_html__('YouTube', 'codeweber'),
	'uil-twitter' => esc_html__('Twitter', 'codeweber'),
	'uil-linkedin' => esc_html__('LinkedIn', 'codeweber'),
	'uil-tiktok' => esc_html__('TikTok', 'codeweber'),
	'uil-pinterest' => esc_html__('Pinterest', 'codeweber'),
	'uil-snapchat' => esc_html__('Snapchat', 'codeweber'),
	'uil-skype' => esc_html__('Skype', 'codeweber'),
	'uil-twitch' => esc_html__('Twitch', 'codeweber'),
	'uil-github' => esc_html__('GitHub', 'codeweber'),
	'uil-discord' => esc_html__('Discord', 'codeweber'),
	'uil-telegram-alt' => esc_html__('Telegram Alt', 'codeweber'),
	'uil-whatsapp-alt' => esc_html__('WhatsApp Alt', 'codeweber'),
	'uil-viber-alt' => esc_html__('Viber Alt', 'codeweber'),
	'uil-vk-alt' => esc_html__('VKontakte Alt', 'codeweber'),
	'uil-instagram-alt' => esc_html__('Instagram Alt', 'codeweber'),
	'uil-facebook-messenger' => esc_html__('Facebook Messenger', 'codeweber'),
	'uil-linkedin-alt' => esc_html__('LinkedIn Alt', 'codeweber'),
	'uil-x-twitter' => esc_html__('X (Twitter)', 'codeweber'),
	'uil-max' => esc_html__('Max', 'codeweber'),
);

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
				'id'       => 'floating_widget_social',
				'type'     => 'select',
				'title'    => esc_html__('Social Network', 'codeweber'),
				'subtitle' => esc_html__('Select social network to link', 'codeweber'),
				'options'  => codeweber_get_available_socials(),
				'default'  => '',
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_icon',
				'type'     => 'select',
				'title'    => esc_html__('Icon', 'codeweber'),
				'subtitle' => esc_html__('Select icon for widget (or use auto based on social network)', 'codeweber'),
				'options'  => $unicons_icons,
				'default'  => '',
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_icon_color',
				'type'     => 'color',
				'title'    => esc_html__('Icon Color', 'codeweber'),
				'subtitle' => esc_html__('Select icon color', 'codeweber'),
				'default'  => '#ffffff',
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_icon_size',
				'type'     => 'slider',
				'title'    => esc_html__('Icon Size', 'codeweber'),
				'subtitle' => esc_html__('Icon size in pixels', 'codeweber'),
				'desc'     => esc_html__('Min: 20, Max: 100, Default: 48', 'codeweber'),
				'default'  => 48,
				'min'      => 20,
				'max'      => 100,
				'step'     => 2,
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_position',
				'type'     => 'button_set',
				'title'    => esc_html__('Position', 'codeweber'),
				'subtitle' => esc_html__('Widget position on screen', 'codeweber'),
				'options'  => array(
					'left'  => esc_html__('Left', 'codeweber'),
					'right' => esc_html__('Right', 'codeweber'),
				),
				'default'  => 'right',
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_offset_vertical',
				'type'     => 'slider',
				'title'    => esc_html__('Vertical Offset', 'codeweber'),
				'subtitle' => esc_html__('Distance from top/bottom edge in pixels', 'codeweber'),
				'desc'     => esc_html__('Min: 0, Max: 200, Default: 100', 'codeweber'),
				'default'  => 100,
				'min'      => 0,
				'max'      => 200,
				'step'     => 10,
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_offset_horizontal',
				'type'     => 'slider',
				'title'    => esc_html__('Horizontal Offset', 'codeweber'),
				'subtitle' => esc_html__('Distance from left/right edge in pixels', 'codeweber'),
				'desc'     => esc_html__('Min: 0, Max: 100, Default: 20', 'codeweber'),
				'default'  => 20,
				'min'      => 0,
				'max'      => 100,
				'step'     => 5,
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_background_color',
				'type'     => 'color_rgba',
				'title'    => esc_html__('Background Color', 'codeweber'),
				'subtitle' => esc_html__('Widget background color with transparency', 'codeweber'),
				'default'  => array(
					'color' => '#000000',
					'alpha' => '0.7'
				),
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_border_radius',
				'type'     => 'slider',
				'title'    => esc_html__('Border Radius', 'codeweber'),
				'subtitle' => esc_html__('Widget border radius in pixels', 'codeweber'),
				'desc'     => esc_html__('Min: 0, Max: 50, Default: 50 (circle)', 'codeweber'),
				'default'  => 50,
				'min'      => 0,
				'max'      => 50,
				'step'     => 2,
				'required' => array('floating_widget_enabled', '=', true),
			),
			
			array(
				'id'       => 'floating_widget_text',
				'type'     => 'text',
				'title'    => esc_html__('Widget Text', 'codeweber'),
				'subtitle' => esc_html__('Optional text to display on widget (for future use)', 'codeweber'),
				'default'  => '',
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
