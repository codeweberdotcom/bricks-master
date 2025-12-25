<?php

Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Socials", "codeweber"),
		'id'               => 'socials',
		'desc'             => esc_html__("Settings Socials", "codeweber"),
		'customizer_width' => '300px',
		'icon'             => 'el el-home',
		'fields'           => array(
			array(
				'id'       => 'max',
				'type'     => 'text',
				'title'    => esc_html__('Max', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
			),
			array(
				'id'       => 'telegram',
				'type'     => 'text',
				'title'    => esc_html__('Telegram', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
			),
			array(
				'id'       => 'whatsapp',
				'type'     => 'text',
				'title'    => esc_html__('Whatsapp', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
			),
			array(
				'id'       => 'viber',
				'type'     => 'text',
				'title'    => esc_html__('Viber', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
			),
			array(
				'id'       => 'vk',
				'type'     => 'text',
				'title'    => esc_html__('Vkontakte', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
			),
			array(
				'id'       => 'odnoklassniki',
				'type'     => 'text',
				'title'    => esc_html__('Odnoklassniki', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
			),
			array(
				'id'       => 'rutube',
				'type'     => 'text',
				'title'    => esc_html__('Rutube', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
			),
			array(
				'id'       => 'vkvideo',
				'type'     => 'text',
				'title'    => esc_html__('VK Video', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
			),
			array(
				'id'       => 'yandex-dzen',
				'type'     => 'text',
				'title'    => esc_html__('Yandex Dzen', 'codeweber'),
				'subtitle' => esc_html__('Subtitle', 'codeweber'),
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
);


// Хук, который срабатывает после сохранения настроек
add_action('redux/options/' . $opt_name . '/saved', 'combine_socials_urls', 10, 2);
function combine_socials_urls($options, $opt_name) {
	// Получаем значения всех полей
	$max_url        = isset($options['max']) ? $options['max'] : '';
	$telegram_url   = isset($options['telegram']) ? $options['telegram'] : '';
	$whatsapp_url   = isset($options['whatsapp']) ? $options['whatsapp'] : '';
	$viber_url      = isset($options['viber']) ? $options['viber'] : '';
	$vkontakte_url  = isset($options['vk']) ? $options['vk'] : '';
	$odnoklassniki_url = isset($options['odnoklassniki']) ? $options['odnoklassniki'] : '';
	$rutube_url     = isset($options['rutube']) ? $options['rutube'] : '';
	$vkvideo_url    = isset($options['vkvideo']) ? $options['vkvideo'] : '';
	$yandexdzen_url = isset($options['yandex-dzen']) ? $options['yandex-dzen'] : '';
	$vkmusic_url    = isset($options['vkmusic']) ? $options['vkmusic'] : '';
	$instagram_url  = isset($options['instagram']) ? $options['instagram'] : '';
	$facebook_url   = isset($options['facebook']) ? $options['facebook'] : '';
	$tiktok_url     = isset($options['tik-tok']) ? $options['tik-tok'] : '';
	$youtube_url    = isset($options['youtube']) ? $options['youtube'] : '';
	$dropbox_url    = isset($options['dropbox']) ? $options['dropbox'] : '';
	$googledrive_url = isset($options['googledrive']) ? $options['googledrive'] : '';
	$googleplay_url = isset($options['googleplay']) ? $options['googleplay'] : '';
	$vimeo_url      = isset($options['vimeo']) ? $options['vimeo'] : '';
	$patreon_url    = isset($options['patreon']) ? $options['patreon'] : '';
	$meetup_url     = isset($options['meetup']) ? $options['meetup'] : '';
	$itunes_url     = isset($options['itunes']) ? $options['itunes'] : '';
	$figma_url      = isset($options['figma']) ? $options['figma'] : '';
	$behance_url    = isset($options['behance']) ? $options['behance'] : '';
	$pinterest_url  = isset($options['pinterest']) ? $options['pinterest'] : '';
	$dripple_url    = isset($options['dripple']) ? $options['dripple'] : '';
	$linkedin_url   = isset($options['linkedin']) ? $options['linkedin'] : '';
	$snapchat_url   = isset($options['snapchat']) ? $options['snapchat'] : '';
	$skype_url      = isset($options['skype']) ? $options['skype'] : '';
	$signal_url     = isset($options['signal']) ? $options['signal'] : '';
	$twitch_url     = isset($options['twitch']) ? $options['twitch'] : '';
	$wechat_url     = isset($options['wechat']) ? $options['wechat'] : '';
	$qq_url         = isset($options['qq']) ? $options['qq'] : '';
	$twitter_url    = isset($options['twitter']) ? $options['twitter'] : '';
	$tumblr_url     = isset($options['tumblr']) ? $options['tumblr'] : '';
	$reddit_url     = isset($options['reddit']) ? $options['reddit'] : '';
	$airbnb_url     = isset($options['airbnb']) ? $options['airbnb'] : '';
	$discord_url    = isset($options['discord']) ? $options['discord'] : '';
	$steam_url      = isset($options['steam']) ? $options['steam'] : '';
	$github_url     = isset($options['github']) ? $options['github'] : '';
	$gitlab_url     = isset($options['gitlab']) ? $options['gitlab'] : '';
	$codepen_url    = isset($options['codepen']) ? $options['codepen'] : '';

	// Формируем массив с соцсетями
	$socials = array(
		'max'         => $max_url,
		'telegram'    => $telegram_url,
		'whatsapp'    => $whatsapp_url,
		'viber'       => $viber_url,
		'vk'   => $vkontakte_url,
		'odnoklassniki' => $odnoklassniki_url,
		'rutube'      => $rutube_url,
		'vkvideo'     => $vkvideo_url,
		'yandex-dzen'  => $yandexdzen_url,
		'vkmusic'     => $vkmusic_url,
		'instagram'   => $instagram_url,
		'facebook'    => $facebook_url,
		'tik-tok'     => $tiktok_url,
		'youtube'     => $youtube_url,
		'dropbox'     => $dropbox_url,
		'googledrive' => $googledrive_url,
		'googleplay'  => $googleplay_url,
		'vimeo'       => $vimeo_url,
		'patreon'     => $patreon_url,
		'meetup'      => $meetup_url,
		'itunes'      => $itunes_url,
		'figma'       => $figma_url,
		'behance'     => $behance_url,
		'pinterest'   => $pinterest_url,
		'dripple'     => $dripple_url,
		'linkedin'    => $linkedin_url,
		'snapchat'    => $snapchat_url,
		'skype'       => $skype_url,
		'signal'      => $signal_url,
		'twitch'      => $twitch_url,
		'wechat'      => $wechat_url,
		'qq'          => $qq_url,
		'twitter'     => $twitter_url,
		'tumblr'      => $tumblr_url,
		'reddit'      => $reddit_url,
		'airbnb'      => $airbnb_url,
		'discord'     => $discord_url,
		'steam'       => $steam_url,
		'github'      => $github_url,
		'gitlab'      => $gitlab_url,
		'codepen'     => $codepen_url,
	);


    // Сохраняем объединенные данные в опцию WordPress
    update_option('socials_urls', $socials);
}

