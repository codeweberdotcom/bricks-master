<?php
/**
 * Floating Social Widget Class
 * 
 * Класс для формирования плавающего виджета социальных сетей
 * на основе настроек Redux Framework
 * 
 * @package CodeWeber
 * @version 1.0.0
 */

if (!class_exists('CodeWeber_Floating_Social_Widget')) {
	class CodeWeber_Floating_Social_Widget {
		
		/**
		 * Redux option name
		 * 
		 * @var string
		 */
		private $opt_name;
		
		/**
		 * Widget settings from Redux
		 * 
		 * @var array
		 */
		private $settings;
		
		/**
		 * Available social networks URLs
		 * 
		 * @var array
		 */
		private $socials_urls;
		
		/**
		 * Constructor
		 */
		public function __construct() {
			global $opt_name;
			$this->opt_name = !empty($opt_name) ? $opt_name : 'redux_demo';
			$this->socials_urls = get_option('socials_urls', array());
			
			// Добавляем демо URL для тестирования, если их нет
			if (empty($this->socials_urls)) {
				$this->socials_urls = array(
					'max' => 'https://max.example.com',
					'telegram' => 'https://t.me/example',
					'whatsapp' => 'https://wa.me/1234567890',
				);
			}
			
			// #region agent log
			$sample_urls = array();
			$test_socials = array('telegram', 'max', 'whatsapp', 'vk');
			foreach ($test_socials as $test_social) {
				if (isset($this->socials_urls[$test_social])) {
					$sample_urls[$test_social] = $this->socials_urls[$test_social];
				}
			}
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:48', 'message' => 'Constructor called', 'data' => ['opt_name' => $this->opt_name, 'socials_urls_count' => count($this->socials_urls), 'class_exists_Redux' => class_exists('Redux'), 'sample_urls' => $sample_urls], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'C']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			$this->load_settings();
		}
		
		/**
		 * Get demo data for testing
		 * 
		 * @return array Demo settings
		 */
		private function get_demo_data() {
			return array(
				'enabled' => true,
				'socials' => array(
					array('social_network' => 'max'),
					array('social_network' => 'telegram'),
					array('social_network' => 'whatsapp'),
				),
				'icon' => 'uil-comment-dots',
				'width' => '180px',
				'right_offset' => '100px',
				'left_offset' => 'auto',
				'top_offset' => 'auto',
				'bottom_offset' => '100px',
				'z_index' => 9999,
			);
		}
		
		/**
		 * Load settings from Redux
		 * 
		 * @return void
		 */
		private function load_settings() {
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:51', 'message' => 'load_settings entry', 'data' => ['class_exists_Redux' => class_exists('Redux')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			if (!class_exists('Redux')) {
				$this->settings = array();
				// #region agent log
				$log_data = json_encode(['location' => 'class-floating-social-widget.php:54', 'message' => 'Redux class not found', 'data' => [], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
				@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
				// #endregion
				return;
			}
			
			$enabled_raw = Redux::get_option($this->opt_name, 'floating_widget_enabled');
			$socials_raw = Redux::get_option($this->opt_name, 'floating_widget_socials');
			
			// Нормализуем значение enabled - может быть строкой "1"/"0" или boolean
			$enabled_normalized = false;
			if (is_bool($enabled_raw)) {
				$enabled_normalized = $enabled_raw;
			} elseif (is_string($enabled_raw)) {
				$enabled_normalized = ($enabled_raw === '1' || $enabled_raw === 'true' || $enabled_raw === 'on');
			} elseif (is_numeric($enabled_raw)) {
				$enabled_normalized = (intval($enabled_raw) === 1);
			}
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:70', 'message' => 'Redux options retrieved', 'data' => ['enabled_raw' => $enabled_raw, 'enabled_type' => gettype($enabled_raw), 'enabled_normalized' => $enabled_normalized, 'socials_raw_type' => gettype($socials_raw), 'socials_raw_empty' => empty($socials_raw), 'socials_raw_is_array' => is_array($socials_raw), 'socials_raw_count' => is_array($socials_raw) ? count($socials_raw) : 0, 'socials_raw_structure' => is_array($socials_raw) ? array_map(function($item) { return ['type' => gettype($item), 'is_array' => is_array($item), 'keys' => is_array($item) ? array_keys($item) : []]; }, array_slice($socials_raw, 0, 3)) : null], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			// Обрабатываем данные repeater с group_values
			// Структура: ['social_network' => [0 => 'telegram', 1 => 'max', ...], 'redux_repeater_data' => [...]]
			$socials = array();
			if (!empty($socials_raw) && is_array($socials_raw)) {
				// #region agent log
				$log_data = json_encode(['location' => 'class-floating-social-widget.php:72', 'message' => 'Processing socials_raw', 'data' => ['keys' => array_keys($socials_raw), 'has_social_network_key' => isset($socials_raw['social_network'])], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'E']);
				@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
				// #endregion
				
				// Если это структура с group_values, где social_network - это массив значений
				if (isset($socials_raw['social_network']) && is_array($socials_raw['social_network'])) {
					// Обрабатываем массив social_network
					foreach ($socials_raw['social_network'] as $index => $social_id) {
						if (!empty($social_id) && is_string($social_id)) {
							$socials[] = array('social_network' => $social_id);
							// #region agent log
							$log_data = json_encode(['location' => 'class-floating-social-widget.php:80', 'message' => 'Added social from group_values', 'data' => ['index' => $index, 'social_id' => $social_id], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'E']);
							@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
							// #endregion
						}
					}
				} else {
					// Старая структура - массив массивов
					foreach ($socials_raw as $key => $item) {
						// #region agent log
						$log_data = json_encode(['location' => 'class-floating-social-widget.php:88', 'message' => 'Processing social item (old structure)', 'data' => ['key' => $key, 'item_type' => gettype($item), 'item_is_array' => is_array($item), 'item_keys' => is_array($item) ? array_keys($item) : []], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'E']);
						@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
						// #endregion
						
						if (is_array($item)) {
							// Проверяем разные возможные структуры
							if (isset($item['social_network'])) {
								$socials[] = $item;
							} elseif (isset($item[0]) && is_string($item[0])) {
								// Если это массив с индексом 0, содержащий строку
								$socials[] = array('social_network' => $item[0]);
							} elseif (count($item) > 0) {
								// Если это массив, берем первый элемент
								$first_key = array_key_first($item);
								if (isset($item[$first_key]) && is_string($item[$first_key])) {
									$socials[] = array('social_network' => $item[$first_key]);
								}
							}
						} elseif (is_string($item)) {
							// Если это просто строка
							$socials[] = array('social_network' => $item);
						}
					}
				}
			}
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:78', 'message' => 'Socials processed', 'data' => ['socials_count' => count($socials), 'socials' => $socials], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'B']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			$icon_raw = Redux::get_option($this->opt_name, 'floating_widget_icon');
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:187', 'message' => 'Icon retrieved from Redux', 'data' => ['icon_raw' => $icon_raw, 'icon_type' => gettype($icon_raw), 'icon_is_array' => is_array($icon_raw)], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			// Обрабатываем иконку - теперь это просто строка (имя иконки без префикса uil-)
			$icon_name = '';
			if (is_array($icon_raw)) {
				// Если это массив, берем значение из ключа 'icon' или первый элемент
				$icon_name = isset($icon_raw['icon']) ? $icon_raw['icon'] : (isset($icon_raw[0]) ? $icon_raw[0] : '');
			} elseif (is_string($icon_raw)) {
				$icon_name = $icon_raw;
			}
			
			// Убираем префикс 'uil-' если он есть (на случай миграции со старого формата)
			$icon_name = str_replace('uil-', '', $icon_name);
			$icon_name = trim($icon_name);
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:202', 'message' => 'Icon name processed', 'data' => ['icon_raw' => $icon_raw, 'icon_name' => $icon_name], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			// Если значение пустое, используем значение по умолчанию
			if (empty($icon_name)) {
				$icon_name = 'comment-dots';
			}
			
			$button_color = Redux::get_option($this->opt_name, 'floating_widget_button_color');
			$animation_type = Redux::get_option($this->opt_name, 'floating_widget_animation_type');
			$widget_type = Redux::get_option($this->opt_name, 'floating_widget_type');
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:220', 'message' => 'Settings retrieved from Redux', 'data' => ['button_color' => $button_color, 'button_color_type' => gettype($button_color), 'animation_type' => $animation_type, 'animation_type_type' => gettype($animation_type), 'widget_type' => $widget_type, 'widget_type_type' => gettype($widget_type)], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			// Нормализуем цвет кнопки - если пусто, используем primary
			if (empty($button_color)) {
				$button_color = 'primary';
			}
			
			// Нормализуем тип анимации - если пусто, используем vertical
			if (empty($animation_type)) {
				$animation_type = 'vertical';
			}
			
			// Нормализуем тип виджета - если пусто, используем icon
			if (empty($widget_type)) {
				$widget_type = 'icon';
			}
			
			$this->settings = array(
				'enabled' => $enabled_normalized,
				'socials' => $socials,
				'icon' => $icon_name,
				'button_color' => $button_color,
				'animation_type' => $animation_type,
				'widget_type' => $widget_type,
				'width' => Redux::get_option($this->opt_name, 'floating_widget_width'),
				'right_offset' => Redux::get_option($this->opt_name, 'floating_widget_right_offset'),
				'left_offset' => Redux::get_option($this->opt_name, 'floating_widget_left_offset'),
				'top_offset' => Redux::get_option($this->opt_name, 'floating_widget_top_offset'),
				'bottom_offset' => Redux::get_option($this->opt_name, 'floating_widget_bottom_offset'),
				'z_index' => Redux::get_option($this->opt_name, 'floating_widget_z_index'),
			);
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:254', 'message' => 'Icon processed', 'data' => ['icon_name' => $icon_name, 'icon_final' => $this->settings['icon']], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			// Используем демоданные ТОЛЬКО если Redux данные полностью отсутствуют (для тестирования)
			// Если enabled включен, но socials пустые - это ошибка конфигурации, не используем демо
			$use_demo = (empty($this->settings['enabled']) && empty($this->settings['socials']));
			if ($use_demo) {
				$demo_data = $this->get_demo_data();
				$this->settings = array_merge($this->settings, $demo_data);
				// #region agent log
				$log_data = json_encode(['location' => 'class-floating-social-widget.php:120', 'message' => 'Using demo data', 'data' => ['demo_enabled' => $demo_data['enabled'], 'demo_socials_count' => count($demo_data['socials'])], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']);
				@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
				// #endregion
			}
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:110', 'message' => 'Settings loaded', 'data' => ['enabled' => $this->settings['enabled'], 'socials_count' => count($this->settings['socials']), 'socials_urls_count' => count($this->socials_urls), 'use_demo' => $use_demo], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
		}
		
		/**
		 * Check if widget is enabled
		 * 
		 * @return bool
		 */
		public function is_enabled() {
			$enabled = !empty($this->settings['enabled']);
			$has_socials = !empty($this->settings['socials']);
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:108', 'message' => 'is_enabled check', 'data' => ['enabled' => $enabled, 'has_socials' => $has_socials, 'enabled_raw' => $this->settings['enabled'], 'socials_count' => count($this->settings['socials']), 'result' => $enabled && $has_socials], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			return $enabled && $has_socials;
		}
		
		/**
		 * Get social network URL by ID
		 * 
		 * @param string|array $social_id Social network ID (e.g., 'telegram', 'whatsapp', 'max')
		 * @return string|false URL or false if not found
		 */
		private function get_social_url($social_id) {
			// Если передан массив, извлекаем строку
			if (is_array($social_id)) {
				$social_id = isset($social_id[0]) ? $social_id[0] : (isset($social_id['social_network']) ? $social_id['social_network'] : '');
			}
			
			// Проверяем, что это строка
			if (!is_string($social_id) || empty($social_id)) {
				// #region agent log
				$log_data = json_encode(['location' => 'class-floating-social-widget.php:120', 'message' => 'get_social_url invalid social_id', 'data' => ['social_id_type' => gettype($social_id)], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'E']);
				@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
				// #endregion
				return false;
			}
			
			$url_exists = isset($this->socials_urls[$social_id]) && !empty($this->socials_urls[$social_id]);
			$url_value = $url_exists ? $this->socials_urls[$social_id] : null;
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:130', 'message' => 'get_social_url check', 'data' => ['social_id' => $social_id, 'url_exists' => $url_exists, 'url_value' => $url_value, 'available_socials' => array_keys($this->socials_urls)], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'C']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			if (!$url_exists) {
				return false;
			}
			return esc_url($url_value);
		}
		
		/**
		 * Get social icon class
		 * 
		 * @param string $social_id Social network ID
		 * @return string Icon class
		 */
		private function get_social_icon_class($social_id) {
			$icon_map = array(
				'max'         => 'uil-max',
				'telegram'    => 'uil-telegram',
				'whatsapp'    => 'uil-whatsapp',
				'viber'       => 'uil-viber',
				'vk'          => 'uil-vk',
				'odnoklassniki' => 'uil-square-odnoklassniki',
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
			
			return isset($icon_map[$social_id]) ? $icon_map[$social_id] : '';
		}
		
		/**
		 * Get social network label
		 * 
		 * @param string $social_id Social network ID
		 * @return string Label
		 */
		private function get_social_label($social_id) {
			$labels = array(
				'max'         => 'MAX',
				'telegram'    => 'Telegram',
				'whatsapp'    => 'Whatsapp',
				'viber'       => 'Viber',
				'vk'          => 'VK',
				'odnoklassniki' => 'Odnoklassniki',
				'rutube'      => 'Rutube',
				'vkvideo'     => 'VK Video',
				'yandex-dzen' => 'Yandex Dzen',
				'vkmusic'     => 'VK Music',
				'instagram'   => 'Instagram',
				'facebook'    => 'Facebook',
				'tik-tok'     => 'TikTok',
				'youtube'     => 'YouTube',
				'dropbox'     => 'Dropbox',
				'googledrive' => 'Google Drive',
				'googleplay'  => 'Google Play',
				'vimeo'       => 'Vimeo',
				'patreon'     => 'Patreon',
				'meetup'      => 'Meetup',
				'itunes'      => 'iTunes',
				'figma'       => 'Figma',
				'behance'     => 'Behance',
				'pinterest'   => 'Pinterest',
				'dripple'     => 'Dripple',
				'linkedin'    => 'LinkedIn',
				'snapchat'    => 'Snapchat',
				'skype'       => 'Skype',
				'signal'      => 'Signal',
				'twitch'      => 'Twitch',
				'wechat'      => 'WeChat',
				'qq'          => 'QQ',
				'twitter'     => 'Twitter',
				'tumblr'      => 'Tumblr',
				'reddit'      => 'Reddit',
				'airbnb'      => 'Airbnb',
				'discord'     => 'Discord',
				'steam'       => 'Steam',
				'github'      => 'GitHub',
				'gitlab'      => 'GitLab',
				'codepen'     => 'CodePen',
			);
			
			return isset($labels[$social_id]) ? $labels[$social_id] : ucfirst($social_id);
		}
		
		/**
		 * Get button class for social network
		 * 
		 * @param string $social_id Social network ID
		 * @return string Button class
		 */
		private function get_button_class($social_id) {
			// Исключения для специальных случаев
			$exceptions = array(
				'max' => 'btn-gradient gradient-10', // Без префикса btn, так как он добавляется отдельно
			);
			
			// Если есть исключение, используем его
			if (isset($exceptions[$social_id])) {
				return 'btn ' . $exceptions[$social_id];
			}
			
			// Для остальных формируем автоматически: btn-{social_id}
			return 'btn btn-' . $social_id;
		}
		
		/**
		 * Get icon size class for social network
		 * 
		 * @param string $social_id Social network ID
		 * @return string Icon size class
		 */
		private function get_icon_size_class($social_id) {
			if ($social_id === 'max') {
				return 'fs-30';
			} elseif ($social_id === 'telegram' || $social_id === 'whatsapp') {
				return 'fs-28';
			}
			
			return 'fs-28';
		}
		
		/**
		 * Render template 1 (верстка 1)
		 * 
		 * @return string HTML output
		 */
		public function render_template_1() {
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:260', 'message' => 'render_template_1 entry', 'data' => [], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			if (!$this->is_enabled()) {
				// #region agent log
				$log_data = json_encode(['location' => 'class-floating-social-widget.php:264', 'message' => 'Widget not enabled', 'data' => [], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']);
				@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
				// #endregion
				return '';
			}
			
			$socials = $this->settings['socials'];
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:270', 'message' => 'render_template_1 socials check', 'data' => ['socials_type' => gettype($socials), 'socials_empty' => empty($socials), 'socials_is_array' => is_array($socials), 'socials_count' => is_array($socials) ? count($socials) : 0], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'B']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			if (empty($socials) || !is_array($socials)) {
				return '';
			}
			
			// Проверяем, что массив не пустой
			if (count($socials) === 0) {
				return '';
			}
			
			// Get main icon - убираем префикс uil- если есть, так как добавим его при выводе
			$main_icon_raw = !empty($this->settings['icon']) ? $this->settings['icon'] : 'comment-dots';
			// Убираем префикс uil- если он есть (может быть уже обработан в load_settings, но на всякий случай)
			$main_icon = str_replace('uil-', '', $main_icon_raw);
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:465', 'message' => 'Main icon for rendering', 'data' => ['main_icon_raw' => $main_icon_raw, 'main_icon' => $main_icon, 'settings_icon' => $this->settings['icon'], 'final_output' => 'uil ' . $main_icon], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			// Get width
			$width = !empty($this->settings['width']) ? esc_attr($this->settings['width']) : '180px';
			
			// Get offsets
			$right = !empty($this->settings['right_offset']) ? $this->settings['right_offset'] : '100px';
			$bottom = !empty($this->settings['bottom_offset']) ? $this->settings['bottom_offset'] : '100px';
			
			// Нормализуем значения - добавляем px если это число
			if (is_numeric($right)) {
				$right = intval($right) . 'px';
			}
			if (is_numeric($bottom)) {
				$bottom = intval($bottom) . 'px';
			}
			
			// Get z-index
			$z_index = !empty($this->settings['z_index']) ? intval($this->settings['z_index']) : 9999;
			
			// Build inline styles
			$styles = array(
				'min-width: ' . esc_attr($width),
				'right: ' . esc_attr($right),
				'bottom: ' . esc_attr($bottom),
				'z-index: ' . $z_index,
			);
			$style_attr = implode('; ', $styles);
			
			// Get animation type and determine class
			$animation_type = !empty($this->settings['animation_type']) ? $this->settings['animation_type'] : 'vertical';
			$widget_classes = 'share-buttons align-items-end position-fixed';
			
			// Добавляем класс top-widget только если анимация вертикальная
			if ($animation_type === 'vertical') {
				$widget_classes .= ' top-widget';
			}
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:530', 'message' => 'Animation type for rendering', 'data' => ['animation_type' => $animation_type, 'widget_classes' => $widget_classes], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			// Start output
			$output = '<div class="' . esc_attr($widget_classes) . '" style="' . esc_attr($style_attr) . '">';
			
			// Get button color from settings
			$button_color = !empty($this->settings['button_color']) ? esc_attr($this->settings['button_color']) : 'primary';
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:515', 'message' => 'Button color for rendering', 'data' => ['button_color' => $button_color, 'button_class' => 'btn-' . $button_color], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			// Main button
			$output .= '<button class="btn btn-circle btn-' . $button_color . ' btn-lg share-button-main">';
			$output .= '<i class="uil uil-' . esc_attr($main_icon) . '"></i>';
			$output .= '</button>';
			
			// Social network buttons
			// Обрабатываем массив соцсетей из repeater
			foreach ($socials as $social_item) {
				// Проверяем разные возможные структуры данных
				$social_id = '';
				
				if (is_string($social_item)) {
					// Если это просто строка (ID соцсети)
					$social_id = $social_item;
				} elseif (is_array($social_item)) {
					// Если это массив, ищем social_network
					if (isset($social_item['social_network'])) {
						$social_id = $social_item['social_network'];
						// Если social_network тоже массив, берем первый элемент
						if (is_array($social_id)) {
							$social_id = isset($social_id[0]) ? $social_id[0] : '';
						}
					} elseif (isset($social_item[0])) {
						// Альтернативная структура массива
						$social_id = $social_item[0];
					}
				}
				
				// Проверяем, что получили строку
				if (!is_string($social_id) || empty($social_id)) {
					continue;
				}
				
				$social_url = $this->get_social_url($social_id);
				
				// #region agent log
				$log_data = json_encode(['location' => 'class-floating-social-widget.php:496', 'message' => 'Social URL retrieved for rendering', 'data' => ['social_id' => $social_id, 'social_url' => $social_url], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'C']);
				@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
				// #endregion
				
				if (!$social_url) {
					continue;
				}
				
				$icon_class_raw = $this->get_social_icon_class($social_id);
				if (empty($icon_class_raw)) {
					continue;
				}
				
				// Убираем префикс uil- если есть, так как добавим его при выводе
				$icon_class_name = str_replace('uil-', '', $icon_class_raw);
				// Формируем полный класс иконки: uil uil-{icon_name}
				$icon_class = 'uil uil-' . $icon_class_name;
				
				$label = $this->get_social_label($social_id);
				$button_class = $this->get_button_class($social_id);
				$icon_size = $this->get_icon_size_class($social_id);
				
				// Build social button
				$output .= '<a href="' . $social_url . '" ';
				$output .= 'class="' . esc_attr($button_class) . ' py-0 ps-2 pe-2 has-ripple btn-icon btn-icon-start rounded-pill widget-social justify-content-between w-100" ';
				$output .= 'target="_blank" rel="noopener noreferrer">';
				$output .= '<i class="' . esc_attr($icon_size) . ' ' . esc_attr($icon_class) . ' me-0"></i>';
				$output .= '<span class="ps-1 pe-2">' . esc_html($label) . '</span>';
				$output .= '</a>';
			}
			
			$output .= '</div>';
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:360', 'message' => 'render_template_1 exit', 'data' => ['output_length' => strlen($output), 'buttons_count' => substr_count($output, 'widget-social')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			return $output;
		}
		
		/**
		 * Render template 2 (Icon variant - только иконки в кружках)
		 * 
		 * @return string HTML output
		 */
		public function render_template_2() {
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:650', 'message' => 'render_template_2 entry', 'data' => ['is_enabled' => $this->is_enabled(), 'socials_count' => !empty($this->settings['socials']) ? count($this->settings['socials']) : 0, 'settings_keys' => array_keys($this->settings)], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			// Проверяем, включен ли виджет
			if (!$this->is_enabled()) {
				// #region agent log
				$log_data = json_encode(['location' => 'class-floating-social-widget.php:656', 'message' => 'render_template_2: widget not enabled', 'data' => [], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
				@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
				// #endregion
				return '';
			}
			
			$socials = $this->settings['socials'];
			if (empty($socials)) {
				// #region agent log
				$log_data = json_encode(['location' => 'class-floating-social-widget.php:664', 'message' => 'render_template_2: socials empty', 'data' => ['socials' => $socials], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
				@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
				// #endregion
				return '';
			}
			
			// Get width
			$width = !empty($this->settings['width']) ? esc_attr($this->settings['width']) : '180px';
			
			// Get offsets
			$right = !empty($this->settings['right_offset']) ? $this->settings['right_offset'] : '100px';
			$bottom = !empty($this->settings['bottom_offset']) ? $this->settings['bottom_offset'] : '100px';
			
			// Нормализуем значения - добавляем px если это число
			if (is_numeric($right)) {
				$right = intval($right) . 'px';
			}
			if (is_numeric($bottom)) {
				$bottom = intval($bottom) . 'px';
			}
			
			// Get z-index
			$z_index = !empty($this->settings['z_index']) ? intval($this->settings['z_index']) : 9999;
			
			// Build inline styles
			$styles = array(
				'min-width: ' . esc_attr($width),
				'right: ' . esc_attr($right),
				'bottom: ' . esc_attr($bottom),
				'z-index: ' . $z_index,
			);
			$style_attr = implode('; ', $styles);
			
			// Get animation type and determine class
			$animation_type = !empty($this->settings['animation_type']) ? $this->settings['animation_type'] : 'vertical';
			$widget_classes = 'share-buttons align-items-end position-fixed';
			
			// Добавляем класс top-widget только если анимация вертикальная
			if ($animation_type === 'vertical') {
				$widget_classes .= ' top-widget';
			}
			
			// Start output
			$output = '<div class="' . esc_attr($widget_classes) . '" style="' . esc_attr($style_attr) . '">';
			
			// Get main icon
			$main_icon_raw = !empty($this->settings['icon']) ? $this->settings['icon'] : 'comment-dots';
			$main_icon = str_replace('uil-', '', $main_icon_raw);
			
			// Get button color from settings
			$button_color = !empty($this->settings['button_color']) ? esc_attr($this->settings['button_color']) : 'primary';
			
			// Main button (такая же как в template_1)
			$output .= '<button class="btn btn-circle btn-' . $button_color . ' btn-lg share-button-main">';
			$output .= '<i class="uil uil-' . esc_attr($main_icon) . '"></i>';
			$output .= '</button>';
			
			// Social network buttons (Icon variant - только иконки)
			foreach ($socials as $social_item) {
				// Проверяем разные возможные структуры данных
				$social_id = '';
				
				if (is_string($social_item)) {
					$social_id = $social_item;
				} elseif (is_array($social_item)) {
					if (isset($social_item['social_network'])) {
						$social_id = $social_item['social_network'];
						if (is_array($social_id)) {
							$social_id = isset($social_id[0]) ? $social_id[0] : '';
						}
					} elseif (isset($social_item[0])) {
						$social_id = $social_item[0];
					}
				}
				
				// Проверяем, что получили строку
				if (!is_string($social_id) || empty($social_id)) {
					continue;
				}
				
				$social_url = $this->get_social_url($social_id);
				if (!$social_url) {
					continue;
				}
				
				$icon_class_raw = $this->get_social_icon_class($social_id);
				if (empty($icon_class_raw)) {
					continue;
				}
				
				// Убираем префикс uil- если есть, так как добавим его при выводе
				$icon_class_name = str_replace('uil-', '', $icon_class_raw);
				// Формируем полный класс иконки: uil uil-{icon_name}
				$icon_class = 'uil uil-' . $icon_class_name;
				
				$button_class_raw = $this->get_button_class($social_id);
				// Убираем префикс 'btn ' из button_class, так как мы уже добавляем 'btn' в начале
				$button_class = str_replace('btn ', '', $button_class_raw);
				$button_class = trim($button_class);
				
				// Build social button (Icon variant: btn btn-circle {button_class} btn-lg social widget-social)
				$output .= '<a href="' . esc_url($social_url) . '" ';
				$output .= 'class="btn btn-circle ' . esc_attr($button_class) . ' btn-lg social widget-social" ';
				$output .= 'target="_blank" rel="noopener noreferrer">';
				$output .= '<i class="' . esc_attr($icon_class) . '"></i>';
				$output .= '</a>';
			}
			
			$output .= '</div>';
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:720', 'message' => 'render_template_2 exit', 'data' => ['output_length' => strlen($output), 'buttons_count' => substr_count($output, 'widget-social')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			return $output;
		}
		
		/**
		 * Render widget
		 * 
		 * @param string $template Template name (optional, if not provided uses widget_type from settings)
		 * @return string HTML output
		 */
		public function render($template = null) {
			// Если шаблон не указан, используем тип виджета из настроек
			if ($template === null) {
				$widget_type = !empty($this->settings['widget_type']) ? $this->settings['widget_type'] : 'icon';
				$template = ($widget_type === 'icon') ? 'template_2' : 'template_1';
				
				// #region agent log
				$log_data = json_encode(['location' => 'class-floating-social-widget.php:760', 'message' => 'Template selection based on widget_type', 'data' => ['widget_type' => $widget_type, 'template' => $template, 'settings_widget_type' => $this->settings['widget_type']], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
				@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
				// #endregion
			}
			
			// #region agent log
			$log_data = json_encode(['location' => 'class-floating-social-widget.php:768', 'message' => 'Render method called', 'data' => ['template' => $template, 'widget_type' => !empty($this->settings['widget_type']) ? $this->settings['widget_type'] : 'not_set'], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
			
			switch ($template) {
				case 'template_1':
					return $this->render_template_1();
				case 'template_2':
					return $this->render_template_2();
				case 'template_3':
					// TODO: Implement template 3
					return '';
				default:
					return $this->render_template_1();
			}
		}
	}
}

