<?php
/**
 * Redux User Meta config.
 * For full documentation, please visit: https://devs.redux.io
 *
 * @package Redux
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Redux_Users' ) ) {
	return;
}

// Change the priority the Redux_Users boxes appear.
Redux_Users::set_Args(
	$opt_name,
	array(
		'user_priority' => 50,
	)
);

Redux_Users::set_profile(
	$opt_name,
	 array(
	 	'id'       => 'user-socials',
	 	'style'    => 'wp',
	 	'sections' => array(
	array(
		'title'            => esc_html__("User Socials", "codeweber"),
		'id'               => 'socials',
		'desc'             => esc_html__("Settings Socials", "codeweber"),
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'fields'           => array(
			array(
				'id'       => 'telegram1',
				'type'     => 'text',
				'title'    => esc_html__('Telegram', 'codeweber'),
			),
					array(
						'id'       => 'whatsapp',
						'type'     => 'text',
						'title'    => esc_html__('Whatsapp', 'codeweber'),
					),
					array(
						'id'       => 'viber',
						'type'     => 'text',
						'title'    => esc_html__('Viber', 'codeweber'),
					),
					array(
						'id'       => 'vk',
						'type'     => 'text',
						'title'    => esc_html__('Vkontakte', 'codeweber'),
					),
					array(
						'id'       => 'odnoklassniki',
						'type'     => 'text',
						'title'    => esc_html__('Odnoklassniki', 'codeweber'),
					),
					array(
						'id'       => 'rutube',
						'type'     => 'text',
						'title'    => esc_html__('Rutube', 'codeweber'),
					),
					array(
						'id'       => 'vkvideo',
						'type'     => 'text',
						'title'    => esc_html__('VK Video', 'codeweber'),
					),
					array(
						'id'       => 'yandex-dzen',
						'type'     => 'text',
						'title'    => esc_html__('Yandex Dzen', 'codeweber'),
					),
					array(
						'id'       => 'vkmusic',
						'type'     => 'text',
						'title'    => esc_html__('VK Music', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'instagram',
						'type'     => 'text',
						'title'    => esc_html__('Instagram', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'facebook',
						'type'     => 'text',
						'title'    => esc_html__('Facebook', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'tik-tok',
						'type'     => 'text',
						'title'    => esc_html__('Tik-Tok', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'youtube',
						'type'     => 'text',
						'title'    => esc_html__('YouTube', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'dropbox',
						'type'     => 'text',
						'title'    => esc_html__('Dropbox', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'googledrive',
						'type'     => 'text',
						'title'    => esc_html__('Google Drive', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'googleplay',
						'type'     => 'text',
						'title'    => esc_html__('Google Play', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'vimeo',
						'type'     => 'text',
						'title'    => esc_html__('Vimeo', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'patreon',
						'type'     => 'text',
						'title'    => esc_html__('Patreon', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'meetup',
						'type'     => 'text',
						'title'    => esc_html__('Meetup', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'itunes',
						'type'     => 'text',
						'title'    => esc_html__('Itunes', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'figma',
						'type'     => 'text',
						'title'    => esc_html__('Figma', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'behance',
						'type'     => 'text',
						'title'    => esc_html__('Behance', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'pinterest',
						'type'     => 'text',
						'title'    => esc_html__('Pinterest', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'dripple',
						'type'     => 'text',
						'title'    => esc_html__('dripple', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'linkedin',
						'type'     => 'text',
						'title'    => esc_html__('LinkedIn', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),

					array(
						'id'       => 'snapchat',
						'type'     => 'text',
						'title'    => esc_html__('Snapchat', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'skype',
						'type'     => 'text',
						'title'    => esc_html__('Skype', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'signal',
						'type'     => 'text',
						'title'    => esc_html__('Signal', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'twitch',
						'type'     => 'text',
						'title'    => esc_html__('Twitch', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'wechat',
						'type'     => 'text',
						'title'    => esc_html__('WeChat', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'qq',
						'type'     => 'text',
						'title'    => esc_html__('QQ', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),

					array(
						'id'       => 'twitter',
						'type'     => 'text',
						'title'    => esc_html__('Twitter', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),

					array(
						'id'       => 'tumblr',
						'type'     => 'text',
						'title'    => esc_html__('Tumblr', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'reddit',
						'type'     => 'text',
						'title'    => esc_html__('Reddit', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'airbnb',
						'type'     => 'text',
						'title'    => esc_html__('Airbnb', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),

					array(
						'id'       => 'discord',
						'type'     => 'text',
						'title'    => esc_html__('Discord', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'steam',
						'type'     => 'text',
						'title'    => esc_html__('Steam', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'github',
						'type'     => 'text',
						'title'    => esc_html__('GitHub', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'gitlab',
						'type'     => 'text',
						'title'    => esc_html__('GitLab', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),
					array(
						'id'       => 'codepen',
						'type'     => 'text',
						'title'    => esc_html__('Codepen', 'codeweber'),
						'subtitle' => esc_html__('Subtitle', 'codeweber'),
					),

		),
	)
)
)
	// array(
	// 	'id'       => 'user-socials',
	// 	'title'    => esc_html__( 'User Socials', 'codeweber' ),
	// 	'style'    => 'wp',
	// 	'sections' => array(
	// 		array(
	// 			'title'  => esc_html__( 'User Settings', 'codeweber' ),
	// 			'icon'   => 'el-icon-home',
	// 			'fields' => array(
	// 				array(
	// 					'id'    => 'user-text',
	// 					'type'  => 'text',
	// 					'title' => esc_html__( 'Input 1', 'codeweber' ),
	// 				),
	// 				array(
	// 					'id'    => 'user-text-2',
	// 					'type'  => 'text',
	// 					'title' => esc_html__( 'Input 2', 'codeweber' ),
	// 				),
	// 				array(
	// 					'id'    => 'user-text-3',
	// 					'type'  => 'text',
	// 					'title' => esc_html__( 'Input 3', 'codeweber' ),
	// 				),
	// 				array(
	// 					'id'       => 'user-web-fonts',
	// 					'type'     => 'media',
	// 					'title'    => esc_html__( 'Web Fonts', 'codeweber' ),
	// 					'compiler' => 'true',
	// 					'mode'     => false,
	// 					// Can be set to false allowing for any media type, or can also be set to any mime type.
	// 					'desc'     => esc_html__( 'Basic media uploader with disabled URL input field.', 'codeweber' ),
	// 					'subtitle' => esc_html__( 'Upload any media using the WordPress native uploader', 'codeweber' ),
	// 				),
	// 				array(
	// 					'id'       => 'user-section-media-start',
	// 					'type'     => 'section',
	// 					'title'    => esc_html__( 'Media Options', 'codeweber' ),
	// 					'subtitle' => esc_html__( 'With the "section" field you can create indent option sections.', 'codeweber' ),
	// 					'indent'   => true,
	// 				),
	// 				array(
	// 					'id'       => 'user-mediaurl',
	// 					'type'     => 'media',
	// 					'url'      => true,
	// 					'title'    => esc_html__( 'Media w/ URL', 'codeweber' ),
	// 					'compiler' => 'true',
	// 					'desc'     => esc_html__( 'Basic media uploader with disabled URL input field.', 'codeweber' ),
	// 					'subtitle' => esc_html__( 'Upload any media using the WordPress native uploader', 'codeweber' ),
	// 					'default'  => array( 'url' => 'https://s.wordpress.org/style/images/codeispoetry.png' ),
	// 				),
	// 				array(
	// 					'id'     => 'user-section-media-end',
	// 					'type'   => 'section',
	// 					'indent' => false,
	// 				),
	// 				array(
	// 					'id'       => 'user-media-nourl',
	// 					'type'     => 'media',
	// 					'title'    => esc_html__( 'Media w/o URL', 'codeweber' ),
	// 					'desc'     => esc_html__( 'This represents the minimalistic view. It does not have the preview box or the display URL in an input box. ', 'codeweber' ),
	// 					'subtitle' => esc_html__( 'Upload any media using the WordPress native uploader', 'codeweber' ),
	// 				),
	// 				array(
	// 					'id'       => 'user-media-nopreview',
	// 					'type'     => 'media',
	// 					'preview'  => false,
	// 					'title'    => esc_html__( 'Media No Preview', 'codeweber' ),
	// 					'desc'     => esc_html__( 'This represents the minimalistic view. It does not have the preview box or the display URL in an input box. ', 'codeweber' ),
	// 					'subtitle' => esc_html__( 'Upload any media using the WordPress native uploader', 'codeweber' ),
	// 				),
	// 				array(
	// 					'id'       => 'user-gallery',
	// 					'type'     => 'gallery',
	// 					'title'    => esc_html__( 'Add/Edit Gallery', 'codeweber' ),
	// 					'subtitle' => esc_html__( 'Create a new Gallery by selecting existing or uploading new images using the WordPress native uploader', 'codeweber' ),
	// 					'desc'     => esc_html__( 'This is the description field, again good for additional info.', 'codeweber' ),
	// 				),
	// 				array(
	// 					'id'      => 'user-slider-one',
	// 					'type'    => 'slider',
	// 					'title'   => esc_html__( 'JQuery UI Slider Example 1', 'codeweber' ),
	// 					'desc'    => esc_html__( 'JQuery UI slider description. Min: 1, max: 500, step: 3, default value: 45', 'codeweber' ),
	// 					'default' => '46',
	// 					'min'     => '1',
	// 					'step'    => '3',
	// 					'max'     => '500',
	// 				),
	// 				array(
	// 					'id'      => 'user-slider-two',
	// 					'type'    => 'slider',
	// 					'title'   => esc_html__( 'JQuery UI Slider Example 2 w/ Steps (5)', 'codeweber' ),
	// 					'desc'    => esc_html__( 'JQuery UI slider description. Min: 0, max: 300, step: 5, default value: 75', 'codeweber' ),
	// 					'default' => '0',
	// 					'min'     => '0',
	// 					'step'    => '5',
	// 					'max'     => '300',
	// 				),
	// 				array(
	// 					'id'      => 'user-spinner',
	// 					'type'    => 'spinner',
	// 					'title'   => esc_html__( 'JQuery UI Spinner Example 1', 'codeweber' ),
	// 					'desc'    => esc_html__( 'JQuery UI spinner description. Min:20, max: 100, step:20, default value: 40', 'codeweber' ),
	// 					'default' => '40',
	// 					'min'     => '20',
	// 					'step'    => '20',
	// 					'max'     => '100',
	// 				),
	// 				array(
	// 					'id'       => 'user-switch-parent',
	// 					'type'     => 'switch',
	// 					'title'    => esc_html__( 'Switch - Nested Children, Enable to show', 'codeweber' ),
	// 					'subtitle' => esc_html__( 'Look, it\'s on! Also hidden child elements!', 'codeweber' ),
	// 					'default'  => 0,
	// 					'on'       => 'Enabled',
	// 					'off'      => 'Disabled',
	// 				),
	// 				array(
	// 					'id'       => 'user-switch-child',
	// 					'type'     => 'switch',
	// 					'required' => array( 'user-switch-parent', '=', '1' ),
	// 					'title'    => esc_html__( 'Switch - This and the next switch required for patterns to show', 'codeweber' ),
	// 					'subtitle' => esc_html__( 'Also called a "fold" parent.', 'codeweber' ),
	// 					'desc'     => esc_html__( 'Items set with a fold to this ID will hide unless this is set to the appropriate value.', 'codeweber' ),
	// 					'default'  => false,
	// 				),
	// 			),
	// 		),
	// 		array(
	// 			'title'  => esc_html__( 'Home Layout', 'codeweber' ),
	// 			'icon'   => 'el-icon-home',
	// 			'fields' => array(
	// 				array(
	// 					'id'       => 'user-homepage_blocks',
	// 					'type'     => 'sorter',
	// 					'title'    => 'Homepage Layout Manager',
	// 					'desc'     => 'Organize how you want the layout to appear on the homepage',
	// 					'compiler' => 'true',
	// 					'required' => array( 'layout', '=', '1' ),
	// 					'options'  => array(
	// 						'enabled'  => array(
	// 							'highlights' => 'Highlights',
	// 							'slider'     => 'Slider',
	// 							'staticpage' => 'Static Page',
	// 							'services'   => 'Services',
	// 						),
	// 						'disabled' => array(),
	// 					),
	// 				),

	// 				array(
	// 					'id'       => 'user-presets',
	// 					'type'     => 'image_select',
	// 					'presets'  => true,
	// 					'title'    => esc_html__( 'Preset', 'codeweber' ),
	// 					'subtitle' => esc_html__( 'This allows you to set a json string or array to override multiple preferences in your theme.', 'codeweber' ),
	// 					'default'  => 0,
	// 					'desc'     => esc_html__( 'This allows you to set a json string or array to override multiple preferences in your theme.', 'codeweber' ),
	// 					'options'  => array(
	// 						'1' => array(
	// 							'alt'     => 'Preset 1',
	// 							'img'     => Redux_Core::$url . '../sample/presets/preset1.png',
	// 							'presets' => array(
	// 								'switch-on'     => 1,
	// 								'switch-off'    => 1,
	// 								'switch-custom' => 1,
	// 							),
	// 						),
	// 						'2' => array(
	// 							'alt'     => 'Preset 2',
	// 							'img'     => Redux_Core::$url . '../sample/presets/preset2.png',
	// 							'presets' => "{'slider1':'1', 'slider2':'0', 'switch-on':'0'}",
	// 						),
	// 					),
	// 				),
	// 			),
	// 		),
	// 	),
	// )
);

// Recovering user data.
$data = Redux_Users::get_user_meta(
	array(
		'key'      => 'user-text', /* If you're only looking for a key within the meta, otherwise all values will be returned. */
		'opt_name' => $opt_name,   // Optional, but needed to recover default values for unset values.
		'user'     => '',          // User id, else current user ID is returned.
	)
);
