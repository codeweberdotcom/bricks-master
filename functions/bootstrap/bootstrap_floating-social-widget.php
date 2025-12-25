<?php
/**
 * Floating Social Widget
 * 
 * Displays a floating social network widget based on Redux settings
 * Uses Bootstrap classes: btn btn-circle
 */

// Подключаем класс для плавающего виджета
require_once get_template_directory() . '/functions/bootstrap/class-floating-social-widget.php';

if (!function_exists('codeweber_floating_social_widget_new')) {
	/**
	 * Выводит плавающий виджет соцсетей (новая версия с поддержкой множественных соцсетей)
	 * 
	 * @param string $template Имя шаблона (template_1, template_2, template_3)
	 * @return string HTML код виджета или пустая строка
	 */
	function codeweber_floating_social_widget_new($template = null) {
		// #region agent log
		$log_data = json_encode(['location' => 'bootstrap_floating-social-widget.php:19', 'message' => 'codeweber_floating_social_widget_new called', 'data' => ['template' => $template, 'class_exists' => class_exists('CodeWeber_Floating_Social_Widget')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
		@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
		// #endregion
		
		if (!class_exists('CodeWeber_Floating_Social_Widget')) {
			// #region agent log
			$log_data = json_encode(['location' => 'bootstrap_floating-social-widget.php:22', 'message' => 'Class not found', 'data' => [], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			return '';
		}
		
		$widget = new CodeWeber_Floating_Social_Widget();
		// Если шаблон не указан, метод render() сам выберет шаблон на основе widget_type из настроек
		$output = $widget->render($template);
		
		// #region agent log
		$log_data = json_encode(['location' => 'bootstrap_floating-social-widget.php:30', 'message' => 'codeweber_floating_social_widget_new exit', 'data' => ['output_length' => strlen($output), 'output_empty' => empty($output)], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
		@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
		// #endregion
		
		return $output;
	}
}

if (!function_exists('codeweber_floating_social_widget')) {
	/**
	 * Выводит плавающий виджет соцсетей
	 * 
	 * @return string HTML код виджета или пустая строка
	 */
	function codeweber_floating_social_widget() {
		if (!class_exists('Redux')) {
			return '';
		}
		
		global $opt_name;
		if (empty($opt_name)) {
			$opt_name = 'redux_demo';
		}
		
		// Проверяем, включен ли виджет
		$enabled = Redux::get_option($opt_name, 'floating_widget_enabled');
		if (empty($enabled)) {
			return '';
		}
		
		// Получаем настройки
		$social_key = Redux::get_option($opt_name, 'floating_widget_social');
		$icon_custom = Redux::get_option($opt_name, 'floating_widget_icon');
		$icon_color = Redux::get_option($opt_name, 'floating_widget_icon_color');
		$icon_size = Redux::get_option($opt_name, 'floating_widget_icon_size');
		$position = Redux::get_option($opt_name, 'floating_widget_position');
		$offset_vertical = Redux::get_option($opt_name, 'floating_widget_offset_vertical');
		$offset_horizontal = Redux::get_option($opt_name, 'floating_widget_offset_horizontal');
		$bg_color = Redux::get_option($opt_name, 'floating_widget_background_color');
		$z_index = Redux::get_option($opt_name, 'floating_widget_z_index');
		
		// Проверяем, что выбрана соцсеть
		if (empty($social_key)) {
			return '';
		}
		
		// Получаем URL соцсети
		$socials = get_option('socials_urls', array());
		if (empty($socials[$social_key])) {
			return '';
		}
		
		$social_url = esc_url($socials[$social_key]);
		
		// Определяем иконку
		$icon_class = '';
		if (!empty($icon_custom)) {
			// Используем выбранную иконку
			$icon_class = esc_attr($icon_custom);
		} else {
			// Автоматически определяем иконку на основе соцсети
			$icon_class = codeweber_get_social_icon_class($social_key);
		}
		
		if (empty($icon_class)) {
			return '';
		}
		
		// Определяем размер кнопки на основе настроек
		// Конвертируем пиксели в классы Bootstrap
		$size_class = '';
		if ($icon_size <= 20) {
			$size_class = 'btn-sm';
		} elseif ($icon_size >= 60) {
			$size_class = 'btn-lg';
		}
		// Для среднего размера (20-60px) не добавляем класс, используется размер по умолчанию
		
		// Формируем классы Bootstrap
		$btn_classes = array('btn', 'btn-circle');
		if (!empty($size_class)) {
			$btn_classes[] = $size_class;
		}
		$btn_class_attr = implode(' ', $btn_classes);
		
		// Формируем inline стили только для позиционирования и кастомных значений
		$styles = array();
		$styles[] = 'position: fixed';
		$styles[] = $position . ': ' . intval($offset_horizontal) . 'px';
		$styles[] = 'bottom: ' . intval($offset_vertical) . 'px';
		$styles[] = 'z-index: ' . intval($z_index);
		
		// Фон
		if (!empty($bg_color)) {
			if (is_array($bg_color)) {
				// Если есть готовый rgba
				if (isset($bg_color['rgba']) && !empty($bg_color['rgba'])) {
					$styles[] = 'background-color: ' . esc_attr($bg_color['rgba']) . ' !important';
				} elseif (isset($bg_color['color']) && !empty($bg_color['color'])) {
					// Конвертируем в rgba если есть alpha
					if (isset($bg_color['alpha']) && $bg_color['alpha'] < 1) {
						$rgb = $bg_color['color'];
						$alpha = floatval($bg_color['alpha']);
						$rgb_clean = ltrim($rgb, '#');
						if (strlen($rgb_clean) == 6) {
							$r = hexdec(substr($rgb_clean, 0, 2));
							$g = hexdec(substr($rgb_clean, 2, 2));
							$b = hexdec(substr($rgb_clean, 4, 2));
							$styles[] = 'background-color: rgba(' . $r . ',' . $g . ',' . $b . ',' . $alpha . ') !important';
						} else {
							$styles[] = 'background-color: ' . esc_attr($rgb) . ' !important';
						}
					} else {
						$styles[] = 'background-color: ' . esc_attr($bg_color['color']) . ' !important';
					}
				}
			} else {
				$styles[] = 'background-color: ' . esc_attr($bg_color) . ' !important';
			}
		}
		
		// Цвет иконки
		if (!empty($icon_color)) {
			$styles[] = 'color: ' . esc_attr($icon_color) . ' !important';
		}
		
		$style_attr = implode('; ', $styles);
		
		// Формируем HTML с Bootstrap классами
		$output = '<a href="' . $social_url . '" ';
		$output .= 'class="' . esc_attr($btn_class_attr) . ' codeweber-floating-social-widget" ';
		$output .= 'style="' . esc_attr($style_attr) . '" ';
		$output .= 'target="_blank" rel="noopener noreferrer" ';
		$output .= 'aria-label="' . esc_attr__('Open social network', 'codeweber') . '">';
		$output .= '<i class="' . esc_attr($icon_class) . '"';
		// Если цвет иконки задан, добавляем стиль для иконки
		if (!empty($icon_color)) {
			$output .= ' style="color: ' . esc_attr($icon_color) . ' !important"';
		}
		$output .= '></i>';
		$output .= '</a>';
		
		return $output;
	}
}

if (!function_exists('codeweber_get_social_icon_class')) {
	/**
	 * Получает класс иконки для соцсети
	 * 
	 * @param string $social_key Ключ соцсети
	 * @return string Класс иконки
	 */
	function codeweber_get_social_icon_class($social_key) {
		$icon_map = array(
			'telegram'    => 'uil-telegram',
			'whatsapp'    => 'uil-whatsapp',
			'viber'       => 'uil-viber',
			'vk'          => 'uil-vk',
			'odnoklassniki' => 'uil-ok-znakomstva',
			'rutube'      => 'uil-rutube',
			'vkvideo'     => 'uil-vkvideo',
			'yandex-dzen' => 'uil-yandex-dzen',
			'vkmusic'     => 'uil-vk-music',
			'instagram'   => 'uil-instagram',
			'facebook'    => 'uil-facebook-f',
			'tik-tok'     => 'uil-tiktok',
			'youtube'     => 'uil-youtube',
			'dropbox'     => 'uil-dropbox',
			'googledrive' => 'uil-google-drive',
			'googleplay'  => 'uil-google-play',
			'vimeo'       => 'uil-vimeo',
			'patreon'     => 'uil-patreon',
			'meetup'      => 'uil-meetup',
			'itunes'      => 'uil-apple',
			'figma'       => 'uil-figma',
			'behance'     => 'uil-behance',
			'pinterest'   => 'uil-pinterest',
			'dripple'     => 'uil-dripple',
			'linkedin'    => 'uil-linkedin',
			'snapchat'    => 'uil-snapchat',
			'skype'       => 'uil-skype',
			'signal'      => 'uil-signal',
			'twitch'      => 'uil-twitch',
			'wechat'      => 'uil-wechat',
			'qq'          => 'uil-qq',
			'twitter'     => 'uil-twitter',
			'tumblr'      => 'uil-tumblr',
			'reddit'      => 'uil-reddit',
			'airbnb'      => 'uil-airbnb',
			'discord'     => 'uil-discord',
			'steam'       => 'uil-steam',
			'github'      => 'uil-github',
			'gitlab'      => 'uil-gitlab',
			'codepen'     => 'uil-codepen',
		);
		
		return isset($icon_map[$social_key]) ? $icon_map[$social_key] : '';
	}
}
