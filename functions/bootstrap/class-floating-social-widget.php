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
			$this->socials_urls = get_option('socials_urls', []);
			
			// Добавляем демо URL для тестирования, если их нет
			if (empty($this->socials_urls)) {
				$this->socials_urls = array(
					'max' => 'https://max.example.com',
					'telegram' => 'https://t.me/example',
					'whatsapp' => 'https://wa.me/1234567890',
				);
			}
			

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
				// Desktop offsets
				'right_offset_desktop' => '30px',
				'left_offset_desktop' => 'auto',
				'top_offset_desktop' => 'auto',
				'bottom_offset_desktop' => '30px',
				// Tablet offsets
				'right_offset_tablet' => '20px',
				'left_offset_tablet' => 'auto',
				'top_offset_tablet' => 'auto',
				'bottom_offset_tablet' => '20px',
				// Mobile offsets
				'right_offset_mobile' => '15px',
				'left_offset_mobile' => 'auto',
				'top_offset_mobile' => 'auto',
				'bottom_offset_mobile' => '15px',
				'z_index' => 9999,
			);
		}
		
		/**
		 * Load settings from Redux
		 * 
		 * @return void
		 */
		private function load_settings() {

			
			if (!class_exists('Redux')) {
				$this->settings = [];

				return;
			}
			
			$opts = get_option( $this->opt_name, [] );

			$enabled_raw = $opts['floating_widget_enabled'] ?? null;
			$socials_raw = $opts['floating_widget_socials'] ?? null;
			
			// Нормализуем значение enabled - может быть строкой "1"/"0", boolean, или числом
			// Redux switch может возвращать разные типы в зависимости от версии
			$enabled_normalized = false;
			if (is_bool($enabled_raw)) {
				// Если это boolean, используем значение как есть
				$enabled_normalized = $enabled_raw;
			} elseif (is_string($enabled_raw)) {
				// Если это строка, проверяем на "включено"
				$enabled_normalized = ($enabled_raw === '1' || $enabled_raw === 'true' || $enabled_raw === 'on' || $enabled_raw === 'yes');
			} elseif (is_numeric($enabled_raw)) {
				// Если это число, проверяем на 1
				$enabled_normalized = (intval($enabled_raw) === 1);
			} elseif ($enabled_raw === 1) {
				// Прямая проверка на 1
				$enabled_normalized = true;
			} elseif ($enabled_raw === 0 || $enabled_raw === null || $enabled_raw === '') {
				// Прямая проверка на 0, null или пустую строку
				$enabled_normalized = false;
			}
			

			

			
			// Обрабатываем данные repeater с group_values
			// Структура: ['social_network' => [0 => 'telegram', 1 => 'max', ...], 'redux_repeater_data' => [...]]
			$socials = [];
			if (!empty($socials_raw) && is_array($socials_raw)) {

				
				// Если это структура с group_values, где social_network - это массив значений
				if (isset($socials_raw['social_network']) && is_array($socials_raw['social_network'])) {
					// Обрабатываем массив social_network
					foreach ($socials_raw['social_network'] as $index => $social_id) {
						if (!empty($social_id) && is_string($social_id)) {
							$social_item = array('social_network' => $social_id);
							
							// Добавляем custom_text, если он есть
							if (isset($socials_raw['custom_text'][$index]) && !empty($socials_raw['custom_text'][$index])) {
								$social_item['custom_text'] = $socials_raw['custom_text'][$index];
							}
							
							$socials[] = $social_item;

						}
					}
				} else {
					// Старая структура - массив массивов
					foreach ($socials_raw as $key => $item) {

						
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
			

			
			$icon_raw = $opts['floating_widget_icon'] ?? null;
			

			
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
			

			
			// Если значение пустое, используем значение по умолчанию
			if (empty($icon_name)) {
				$icon_name = 'comment-dots';
			}
			
			$button_color = $opts['floating_widget_button_color'] ?? '';
			$animation_type = $opts['floating_widget_animation_type'] ?? '';
			$widget_type = $opts['floating_widget_type'] ?? '';
			$widget_item_type = $opts['floating_widget_item_type'] ?? '';
			$button_text = $opts['floating_widget_button_text'] ?? '';
			$button_action_type = $opts['floating_widget_button_action_type'] ?? '';
			$show_icon_mobile = $opts['floating_widget_show_icon_mobile'] ?? null;
			$widget_position_side = $opts['floating_widget_position_side'] ?? '';
			$icon_style = $opts['floating_widget_icon_style'] ?? '';
			

			
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
			
			// Нормализуем тип элементов виджета - если пусто, используем button
			if (empty($widget_item_type)) {
				$widget_item_type = 'button';
			}
			
			// Нормализуем текст кнопки
			if (empty($button_text)) {
				$button_text = esc_html__('Contact us', 'codeweber');
			}
			
			// Нормализуем тип действия кнопки
			if (empty($button_action_type)) {
				$button_action_type = 'none';
			}
			
			// Нормализуем стиль иконки - если пусто, используем btn-circle
			if (empty($icon_style)) {
				$icon_style = 'btn-circle';
			}
			
			// Форма теперь добавляется автоматически через фильтр Redux в список Social Networks
			// и отображается в интерфейсе, где можно управлять порядком
			
			$this->settings = array(
				'enabled' => $enabled_normalized,
				'socials' => $socials,
				'icon' => $icon_name,
				'button_color' => $button_color,
				'animation_type' => $animation_type,
				'widget_type' => $widget_type,
				'widget_item_type' => $widget_item_type,
				'button_text' => $button_text,
				'button_action_type' => $button_action_type,
				'show_icon_mobile' => !empty($show_icon_mobile) ? true : false,
				'widget_position_side' => !empty($widget_position_side) ? $widget_position_side : 'right',
				'icon_style' => $icon_style,
				'width' => $opts['floating_widget_width'] ?? null,
				// Desktop offsets
				'right_offset_desktop' => $opts['floating_widget_right_offset_desktop'] ?? null,
				'left_offset_desktop' => $opts['floating_widget_left_offset_desktop'] ?? null,
				'top_offset_desktop' => $opts['floating_widget_top_offset_desktop'] ?? null,
				'bottom_offset_desktop' => $opts['floating_widget_bottom_offset_desktop'] ?? null,
				// Tablet offsets
				'right_offset_tablet' => $opts['floating_widget_right_offset_tablet'] ?? null,
				'left_offset_tablet' => $opts['floating_widget_left_offset_tablet'] ?? null,
				'top_offset_tablet' => $opts['floating_widget_top_offset_tablet'] ?? null,
				'bottom_offset_tablet' => $opts['floating_widget_bottom_offset_tablet'] ?? null,
				// Mobile offsets
				'right_offset_mobile' => $opts['floating_widget_right_offset_mobile'] ?? null,
				'left_offset_mobile' => $opts['floating_widget_left_offset_mobile'] ?? null,
				'top_offset_mobile' => $opts['floating_widget_top_offset_mobile'] ?? null,
				'bottom_offset_mobile' => $opts['floating_widget_bottom_offset_mobile'] ?? null,
				'z_index' => $opts['floating_widget_z_index'] ?? null,
			);
			

			
			// Используем демоданные ТОЛЬКО если Redux данные полностью отсутствуют (для тестирования)
			// Если enabled включен, но socials пустые - это ошибка конфигурации, не используем демо
			$use_demo = (empty($this->settings['enabled']) && empty($this->settings['socials']));
			if ($use_demo) {
				$demo_data = $this->get_demo_data();
				$this->settings = array_merge($this->settings, $demo_data);

			}
			

		}
		
		/**
		 * Check if widget is enabled
		 * 
		 * @return bool
		 */
		public function is_enabled() {
			// Проверяем enabled - должно быть строго true (не просто truthy)
			$enabled = isset($this->settings['enabled']) && $this->settings['enabled'] === true;
			$has_socials = !empty($this->settings['socials']) && is_array($this->settings['socials']) && count($this->settings['socials']) > 0;
			

			
			return $enabled && $has_socials;
		}
		
		/**
		 * Get social network URL by ID
		 * 
		 * @param string|array $social_id Social network ID (e.g., 'telegram', 'whatsapp', 'max', 'form_123')
		 * @return string|false URL or false if not found
		 */
		private function get_social_url($social_id) {
			// Если передан массив, извлекаем строку
			if (is_array($social_id)) {
				$social_id = isset($social_id[0]) ? $social_id[0] : (isset($social_id['social_network']) ? $social_id['social_network'] : '');
			}
			
			// Проверяем, что это строка
			if (!is_string($social_id) || empty($social_id)) {

				return false;
			}
			
			// Проверяем, является ли это формой (CodeWeber или CF7)
			if (strpos($social_id, 'form_') === 0 || strpos($social_id, 'cf7_') === 0) {
				// Для формы возвращаем javascript:void(0), ссылка формируется через data-атрибуты
				return 'javascript:void(0)';
			}
			
			$url_exists = isset($this->socials_urls[$social_id]) && !empty($this->socials_urls[$social_id]);
			$url_value = $url_exists ? $this->socials_urls[$social_id] : null;
			

			
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
			// Проверяем, является ли это формой (CodeWeber или CF7)
			if (strpos($social_id, 'form_') === 0 || strpos($social_id, 'cf7_') === 0) {
				return 'uil-envelope';
			}
			
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
		 * @param string|array $social_id_or_item Social network ID or array with social_item data
		 * @return string Label
		 */
		private function get_social_label($social_id_or_item) {
			// Если передан массив, извлекаем social_id и custom_text
			$social_id = '';
			$custom_text = '';
			
			if (is_array($social_id_or_item)) {
				if (isset($social_id_or_item['social_network'])) {
					$social_id = $social_id_or_item['social_network'];
					// Если social_network тоже массив, берем первый элемент
					if (is_array($social_id)) {
						$social_id = isset($social_id[0]) ? $social_id[0] : '';
					}
				} elseif (isset($social_id_or_item[0])) {
					$social_id = $social_id_or_item[0];
				}
				
				// Получаем кастомный текст, если он есть
				if (isset($social_id_or_item['custom_text'])) {
					$custom_text = $social_id_or_item['custom_text'];
					// Если custom_text тоже массив, берем первый элемент
					if (is_array($custom_text)) {
						$custom_text = isset($custom_text[0]) ? $custom_text[0] : '';
					}
				}
			} else {
				$social_id = $social_id_or_item;
			}
			
			// Если указан кастомный текст, возвращаем его
			if (!empty($custom_text) && is_string($custom_text)) {
				return $custom_text;
			}
			
			// Иначе используем стандартную логику
			// Проверяем, является ли это формой (CodeWeber или CF7)
			if (strpos($social_id, 'form_') === 0) {
				$form_id = str_replace('form_', '', $social_id);
				$form_post = get_post($form_id);
				if ($form_post && $form_post->post_status === 'publish') {
					return $form_post->post_title . ' (Email)';
				}
				return 'Form (Email)';
			} elseif (strpos($social_id, 'cf7_') === 0) {
				$form_id = str_replace('cf7_', '', $social_id);
				$form_post = get_post($form_id);
				if ($form_post && $form_post->post_status === 'publish') {
					return $form_post->post_title . ' (Email)';
				}
				return 'Form (Email)';
			}
			
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
			// Проверяем, является ли это формой (CodeWeber или CF7)
			if (strpos($social_id, 'form_') === 0 || strpos($social_id, 'cf7_') === 0) {
				return 'btn btn-navy';
			}
			
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
		 * Normalize offset value (add px if numeric)
		 * 
		 * @param mixed $value Offset value
		 * @return string Normalized value
		 */
		private function normalize_offset($value) {
			if (empty($value)) {
				return 'auto';
			}
			if (is_numeric($value)) {
				return intval($value) . 'px';
			}
			return $value;
		}
		
		/**
		 * Generate responsive CSS styles for widget offsets
		 * 
		 * @return string CSS with media queries
		 */
		private function get_responsive_styles() {
			if (!$this->is_enabled()) {
				return '';
			}
			
			$css = '';
			
			// Отступы теперь определены в inline стилях через CSS переменные, используем их
			// Mobile styles (<768px) - используем CSS переменные из inline стилей
			// Добавляем !important чтобы переопределить inline стили
			$css .= '@media (max-width: 767.98px) {';
			$css .= '.share-buttons.position-fixed {';
			$css .= 'right: var(--right-mobile) !important;';
			$css .= 'left: var(--left-mobile) !important;';
			$css .= 'top: var(--top-mobile) !important;';
			$css .= 'bottom: var(--bottom-mobile) !important;';
			$css .= '}';
			$css .= '}';
			
			// Tablet styles (≥768px and <992px)
			$css .= '@media (min-width: 768px) and (max-width: 991.98px) {';
			$css .= '.share-buttons.position-fixed {';
			$css .= 'right: var(--right-tablet) !important;';
			$css .= 'left: var(--left-tablet) !important;';
			$css .= 'top: var(--top-tablet) !important;';
			$css .= 'bottom: var(--bottom-tablet) !important;';
			$css .= '}';
			$css .= '}';
			
			// Desktop styles (≥992px) - последним, чтобы переопределить на больших экранах
			$css .= '@media (min-width: 992px) {';
			$css .= '.share-buttons.position-fixed {';
			$css .= 'right: var(--right-desktop) !important;';
			$css .= 'left: var(--left-desktop) !important;';
			$css .= 'top: var(--top-desktop) !important;';
			$css .= 'bottom: var(--bottom-desktop) !important;';
			$css .= '}';
			$css .= '}';
			
			// Если включена опция "Show Icon on Mobile" для типа Button, скрываем текст на мобильных
			if (!empty($this->settings['widget_type']) && $this->settings['widget_type'] === 'button' 
				&& !empty($this->settings['show_icon_mobile']) && $this->settings['show_icon_mobile']) {
				// Мобильные устройства (<768px)
				$css .= '@media (max-width: 767.98px) {';
				$css .= '.share-button-main.widget-button-mobile-icon .widget-button-text {';
				$css .= 'display: none !important;';
				$css .= '}';
				$css .= '.share-button-main.widget-button-mobile-icon {';
				$css .= 'padding-left: 0.5rem !important;';
				$css .= 'padding-right: 0.5rem !important;';
				$css .= '}';
				$css .= '.share-button-main.widget-button-mobile-icon i {';
				$css .= 'margin-right: 0 !important;';
				$css .= '}';
				$css .= '}';
				
				// Планшеты (≥768px and <992px)
				$css .= '@media (min-width: 768px) and (max-width: 991.98px) {';
				$css .= '.share-button-main.widget-button-mobile-icon .widget-button-text {';
				$css .= 'display: none !important;';
				$css .= '}';
				$css .= '.share-button-main.widget-button-mobile-icon {';
				$css .= 'padding-left: 0.5rem !important;';
				$css .= 'padding-right: 0.5rem !important;';
				$css .= '}';
				$css .= '.share-button-main.widget-button-mobile-icon i {';
				$css .= 'margin-right: 0 !important;';
				$css .= '}';
				$css .= '}';
			}
			
			return $css;
		}
		
		/**
		 * Render template 1 (верстка 1)
		 * 
		 * @return string HTML output
		 */
		public function render_template_1() {

			
			if (!$this->is_enabled()) {

				return '';
			}
			
			$socials = $this->settings['socials'];
			

			
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
			

			
			// Get width
			$width = !empty($this->settings['width']) ? esc_attr($this->settings['width']) : '180px';
			
			// Get z-index
			$z_index = !empty($this->settings['z_index']) ? intval($this->settings['z_index']) : 9999;
			
			// Get widget position side first to apply correct rules
			$widget_position_side = !empty($this->settings['widget_position_side']) ? $this->settings['widget_position_side'] : 'right';
			
			// Get all offsets for all devices - все значения выводим в inline стилях
			$right_desktop = $this->normalize_offset(!empty($this->settings['right_offset_desktop']) ? $this->settings['right_offset_desktop'] : '30px');
			$left_desktop = $this->normalize_offset(!empty($this->settings['left_offset_desktop']) ? $this->settings['left_offset_desktop'] : 'auto');
			$top_desktop = $this->normalize_offset(!empty($this->settings['top_offset_desktop']) ? $this->settings['top_offset_desktop'] : 'auto');
			$bottom_desktop = $this->normalize_offset(!empty($this->settings['bottom_offset_desktop']) ? $this->settings['bottom_offset_desktop'] : '30px');
			
			$right_tablet = $this->normalize_offset(!empty($this->settings['right_offset_tablet']) ? $this->settings['right_offset_tablet'] : '20px');
			$left_tablet = $this->normalize_offset(!empty($this->settings['left_offset_tablet']) ? $this->settings['left_offset_tablet'] : 'auto');
			$top_tablet = $this->normalize_offset(!empty($this->settings['top_offset_tablet']) ? $this->settings['top_offset_tablet'] : 'auto');
			$bottom_tablet = $this->normalize_offset(!empty($this->settings['bottom_offset_tablet']) ? $this->settings['bottom_offset_tablet'] : '20px');
			
			$right_mobile = $this->normalize_offset(!empty($this->settings['right_offset_mobile']) ? $this->settings['right_offset_mobile'] : '15px');
			$left_mobile = $this->normalize_offset(!empty($this->settings['left_offset_mobile']) ? $this->settings['left_offset_mobile'] : 'auto');
			$top_mobile = $this->normalize_offset(!empty($this->settings['top_offset_mobile']) ? $this->settings['top_offset_mobile'] : 'auto');
			$bottom_mobile = $this->normalize_offset(!empty($this->settings['bottom_offset_mobile']) ? $this->settings['bottom_offset_mobile'] : '15px');
			
			// Apply position rules: в режиме Left right всегда auto на всех устройствах, в режиме Right left всегда auto на всех устройствах
			if ($widget_position_side === 'left') {
				$right_desktop = 'auto';
				$right_tablet = 'auto';
				$right_mobile = 'auto';
			} elseif ($widget_position_side === 'right') {
				$left_desktop = 'auto';
				$left_tablet = 'auto';
				$left_mobile = 'auto';
			}
			
			// Build inline styles - все значения отступов выводим как inline стили
			$styles = array(
				'width: ' . esc_attr($width),
				'z-index: ' . $z_index,
				// Desktop offsets
				'--right-desktop: ' . esc_attr($right_desktop),
				'--left-desktop: ' . esc_attr($left_desktop),
				'--top-desktop: ' . esc_attr($top_desktop),
				'--bottom-desktop: ' . esc_attr($bottom_desktop),
				// Tablet offsets
				'--right-tablet: ' . esc_attr($right_tablet),
				'--left-tablet: ' . esc_attr($left_tablet),
				'--top-tablet: ' . esc_attr($top_tablet),
				'--bottom-tablet: ' . esc_attr($bottom_tablet),
				// Mobile offsets
				'--right-mobile: ' . esc_attr($right_mobile),
				'--left-mobile: ' . esc_attr($left_mobile),
				'--top-mobile: ' . esc_attr($top_mobile),
				'--bottom-mobile: ' . esc_attr($bottom_mobile),
			);
			
			// Add desktop offsets as default (will be overridden by CSS media queries using CSS variables)
			// Добавляем все свойства, даже если они 'auto', чтобы все значения были видны в inline стилях
			$styles[] = 'right: var(--right-desktop)';
			$styles[] = 'left: var(--left-desktop)';
			$styles[] = 'top: var(--top-desktop)';
			$styles[] = 'bottom: var(--bottom-desktop)';
			
			$style_attr = implode('; ', $styles);
			
			// Generate responsive CSS that uses CSS variables from inline styles
			$responsive_css = $this->get_responsive_styles();
			
			// Get animation type and determine class
			$animation_type = !empty($this->settings['animation_type']) ? $this->settings['animation_type'] : 'vertical';
			
			// В режиме Left используем align-items-start, в режиме Right - align-items-end
			$align_class = ($widget_position_side === 'left') ? 'align-items-start' : 'align-items-end';
			$widget_classes = 'share-buttons ' . $align_class . ' position-fixed';
			
			// Добавляем класс top-widget только если анимация вертикальная
			if ($animation_type === 'vertical') {
				$widget_classes .= ' top-widget';
			}
			
			// Добавляем класс right-widget если выбрано Right
			if ($widget_position_side === 'right') {
				$widget_classes .= ' right-widget';
			}
			
			// Добавляем класс для типа элементов виджета (icon или button)
			$widget_item_type = !empty($this->settings['widget_item_type']) ? $this->settings['widget_item_type'] : 'button';
			$widget_classes .= ' widget-item-' . esc_attr($widget_item_type);
			
			// Добавляем класс для комбинации основной кнопки и элементов (для правильного расчета расстояний)
			$widget_type = !empty($this->settings['widget_type']) ? $this->settings['widget_type'] : 'icon';
			$widget_classes .= ' widget-main-' . esc_attr($widget_type) . '-elements-' . esc_attr($widget_item_type);
			

			
			// Start output
			$output = '';
			
			// Add responsive CSS if available
			if (!empty($responsive_css)) {
				$output .= '<style type="text/css">' . $responsive_css . '</style>';
			}
			
			$output .= '<div class="' . esc_attr($widget_classes) . '" style="' . esc_attr($style_attr) . '">';
			
			// Get button color from settings
			$button_color = !empty($this->settings['button_color']) ? esc_attr($this->settings['button_color']) : 'primary';
			$widget_type = !empty($this->settings['widget_type']) ? $this->settings['widget_type'] : 'icon';
			$btn_style = class_exists('Codeweber_Options') ? Codeweber_Options::style('button') : ' rounded-pill';


			
			// Main button - разная верстка для разных типов виджета
			if ($widget_type === 'button') {
				// Верстка для типа Button с текстом
				$button_text = !empty($this->settings['button_text']) ? esc_html($this->settings['button_text']) : esc_html__('Contact us', 'codeweber');
				$show_icon_mobile = !empty($this->settings['show_icon_mobile']) ? $this->settings['show_icon_mobile'] : false;
				// Добавляем класс для управления отображением на мобильных
				$button_mobile_class = $show_icon_mobile ? ' widget-button-mobile-icon' : '';
				$output .= '<button class="btn-text-hide btn btn-' . $button_color . ' py-0 ps-2 pe-2 has-ripple btn-icon btn-icon-start' . esc_attr($btn_style) . ' share-button-main no-rotate zindex-50' . esc_attr($button_mobile_class) . '" type="button">';
				$output .= '<i class="fs-28 uil uil-' . esc_attr($main_icon) . '"></i>';
				$output .= '<span class="ps-1 text-hide pe-2 widget-button-text">' . $button_text . '</span>';
				$output .= '</button>';
			} else {
				// Верстка для типа Icon — передаём скругление из темы (getThemeButton)
				$radius_class = trim($btn_style);
				if ($radius_class === '') {
					$radius_class = 'rounded';
				}
				$icon_style = !empty($this->settings['icon_style']) ? $this->settings['icon_style'] : 'btn-circle';
				$aria_label = !empty($this->settings['button_text'])
					? $this->settings['button_text']
					: __('Contact us', 'codeweber');
				$main_button = new CodeWeber_Floating_Button(array(
					'icon' => 'uil uil-' . esc_attr($main_icon),
					'color' => $button_color,
					'size' => 'lg',
					'class' => 'share-icon-main zindex-50',
					'tag' => 'button',
					'type' => 'button',
					'button_style' => $icon_style,
					'radius_class' => $radius_class,
					'aria_label' => $aria_label,
				));
				$output .= $main_button->render();
			}

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
				
				$label = $this->get_social_label($social_item);
				$button_class = $this->get_button_class($social_id);
				$icon_size = $this->get_icon_size_class($social_id);
				
				// Проверяем, является ли это формой (CodeWeber или CF7)
				$is_form = (strpos($social_id, 'form_') === 0 || strpos($social_id, 'cf7_') === 0);
				
				// Определяем тип формы и ID на основе префикса
				if (strpos($social_id, 'cf7_') === 0) {
					// CF7 форма
					$form_id = str_replace('cf7_', '', $social_id);
					$data_value = 'cf7-' . esc_attr($form_id);
				} elseif (strpos($social_id, 'form_') === 0) {
					// CodeWeber форма
					$form_id = str_replace('form_', '', $social_id);
					$data_value = 'cf-' . esc_attr($form_id);
				} else {
					$form_id = '';
					$data_value = '';
				}
				
				// Получаем тип элементов виджета
				$widget_item_type = !empty($this->settings['widget_item_type']) ? $this->settings['widget_item_type'] : 'button';
				
				// Build social button - разная верстка в зависимости от типа элементов
				if ($widget_item_type === 'icon') {
					// Верстка для типа Icon - только иконки в кружках
					$button_class_raw = $button_class;
					// Убираем префикс 'btn ' из button_class, так как CodeWeber_Floating_Button уже добавляет 'btn'
					$button_class_clean = str_replace('btn ', '', $button_class_raw);
					$button_class_clean = trim($button_class_clean);
					
					// Определяем color и дополнительные классы
					$color = '';
					$additional_classes = array('social', 'widget-social', 'icon-element');
					
					// Проверяем, содержит ли класс btn-gradient (для градиентных кнопок)
					if (strpos($button_class_clean, 'btn-gradient') !== false) {
						// Для градиентных кнопок: btn-gradient gradient-10
						$button_class_parts = explode(' ', $button_class_clean);
						foreach ($button_class_parts as $part) {
							if ($part === 'btn-gradient') {
								$additional_classes[] = 'btn-gradient';
							} elseif ($part !== 'btn-gradient') {
								$additional_classes[] = $part; // gradient-10
							}
						}
					} elseif (strpos($button_class_clean, 'btn-') === 0) {
						// Если начинается с 'btn-', используем как color (убираем префикс)
						$color = str_replace('btn-', '', $button_class_clean);
					} else {
						// Иначе это составной класс, добавляем как дополнительный
						$additional_classes[] = $button_class_clean;
					}
					
					$radius_class = trim($btn_style);
					if ($radius_class === '') {
						$radius_class = 'rounded';
					}
					// Для формы используем data-атрибуты для открытия модального окна
					if ($is_form) {
						$social_button = new CodeWeber_Floating_Button(array(
							'icon' => $icon_class,
							'color' => $color,
							'size' => 'lg',
							'class' => implode(' ', $additional_classes),
							'href' => 'javascript:void(0)',
							'title' => esc_attr($label),
							'data' => array(
								'value' => $data_value,
								'bs-toggle' => 'modal',
								'bs-target' => '#modal'
							),
							'tag' => 'a',
							'button_style' => $icon_style,
							'radius_class' => $radius_class,
						));
					} else {
						// Build social button using CodeWeber_Floating_Button (Icon variant)
						$social_button = new CodeWeber_Floating_Button(array(
							'icon' => $icon_class,
							'color' => $color,
							'size' => 'lg',
							'class' => implode(' ', $additional_classes),
							'href' => $social_url,
							'title' => esc_attr($label),
							'target' => '_blank',
							'rel' => 'noopener noreferrer',
							'tag' => 'a',
							'button_style' => $icon_style,
							'radius_class' => $radius_class,
						));
					}
					$output .= $social_button->render();
				} else {
					// Верстка для типа Button - кнопки с текстом (по умолчанию)
					if ($is_form) {
						// Для формы используем data-атрибуты для открытия модального окна
						$output .= '<a href="javascript:void(0)" ';
						$output .= 'class="' . esc_attr($button_class) . ' py-0 ps-2 pe-2 has-ripple btn-icon btn-icon-start' . esc_attr($btn_style) . ' widget-social button-element justify-content-between w-100" ';
						$output .= 'title="' . esc_attr($label) . '" ';
						$output .= 'data-value="' . esc_attr($data_value) . '" ';
						$output .= 'data-bs-toggle="modal" data-bs-target="#modal">';
					} else {
						$output .= '<a href="' . $social_url . '" ';
						$output .= 'class="' . esc_attr($button_class) . ' py-0 ps-2 pe-2 has-ripple btn-icon btn-icon-start' . esc_attr($btn_style) . ' widget-social button-element justify-content-between w-100" ';
						$output .= 'title="' . esc_attr($label) . '" ';
						$output .= 'target="_blank" rel="noopener noreferrer">';
					}
					$output .= '<i class="' . esc_attr($icon_size) . ' ' . esc_attr($icon_class) . ' me-0"></i>';
					$output .= '<span class="ps-1 pe-2">' . esc_html($label) . '</span>';
					$output .= '</a>';
				}
			}
			
			$output .= '</div>';
			

			
			return $output;
		}
		
		/**
		 * Render template 2 (Icon variant - только иконки в кружках)
		 * 
		 * @return string HTML output
		 */
		public function render_template_2() {

			
			// Проверяем, включен ли виджет
			if (!$this->is_enabled()) {

				return '';
			}
			
			$socials = $this->settings['socials'];
			if (empty($socials)) {

				return '';
			}
			
			// Get width
			$width = !empty($this->settings['width']) ? esc_attr($this->settings['width']) : '180px';
			
			// Get z-index
			$z_index = !empty($this->settings['z_index']) ? intval($this->settings['z_index']) : 9999;
			
			// Get widget position side first to apply correct rules
			$widget_position_side = !empty($this->settings['widget_position_side']) ? $this->settings['widget_position_side'] : 'right';
			
			// Get all offsets for all devices - все значения выводим в inline стилях
			$right_desktop = $this->normalize_offset(!empty($this->settings['right_offset_desktop']) ? $this->settings['right_offset_desktop'] : '30px');
			$left_desktop = $this->normalize_offset(!empty($this->settings['left_offset_desktop']) ? $this->settings['left_offset_desktop'] : 'auto');
			$top_desktop = $this->normalize_offset(!empty($this->settings['top_offset_desktop']) ? $this->settings['top_offset_desktop'] : 'auto');
			$bottom_desktop = $this->normalize_offset(!empty($this->settings['bottom_offset_desktop']) ? $this->settings['bottom_offset_desktop'] : '30px');
			
			$right_tablet = $this->normalize_offset(!empty($this->settings['right_offset_tablet']) ? $this->settings['right_offset_tablet'] : '20px');
			$left_tablet = $this->normalize_offset(!empty($this->settings['left_offset_tablet']) ? $this->settings['left_offset_tablet'] : 'auto');
			$top_tablet = $this->normalize_offset(!empty($this->settings['top_offset_tablet']) ? $this->settings['top_offset_tablet'] : 'auto');
			$bottom_tablet = $this->normalize_offset(!empty($this->settings['bottom_offset_tablet']) ? $this->settings['bottom_offset_tablet'] : '20px');
			
			$right_mobile = $this->normalize_offset(!empty($this->settings['right_offset_mobile']) ? $this->settings['right_offset_mobile'] : '15px');
			$left_mobile = $this->normalize_offset(!empty($this->settings['left_offset_mobile']) ? $this->settings['left_offset_mobile'] : 'auto');
			$top_mobile = $this->normalize_offset(!empty($this->settings['top_offset_mobile']) ? $this->settings['top_offset_mobile'] : 'auto');
			$bottom_mobile = $this->normalize_offset(!empty($this->settings['bottom_offset_mobile']) ? $this->settings['bottom_offset_mobile'] : '15px');
			
			// Apply position rules: в режиме Left right всегда auto на всех устройствах, в режиме Right left всегда auto на всех устройствах
			if ($widget_position_side === 'left') {
				$right_desktop = 'auto';
				$right_tablet = 'auto';
				$right_mobile = 'auto';
			} elseif ($widget_position_side === 'right') {
				$left_desktop = 'auto';
				$left_tablet = 'auto';
				$left_mobile = 'auto';
			}
			
			// Build inline styles - все значения отступов выводим как inline стили
			$styles = array(
				'width: ' . esc_attr($width),
				'z-index: ' . $z_index,
				// Desktop offsets
				'--right-desktop: ' . esc_attr($right_desktop),
				'--left-desktop: ' . esc_attr($left_desktop),
				'--top-desktop: ' . esc_attr($top_desktop),
				'--bottom-desktop: ' . esc_attr($bottom_desktop),
				// Tablet offsets
				'--right-tablet: ' . esc_attr($right_tablet),
				'--left-tablet: ' . esc_attr($left_tablet),
				'--top-tablet: ' . esc_attr($top_tablet),
				'--bottom-tablet: ' . esc_attr($bottom_tablet),
				// Mobile offsets
				'--right-mobile: ' . esc_attr($right_mobile),
				'--left-mobile: ' . esc_attr($left_mobile),
				'--top-mobile: ' . esc_attr($top_mobile),
				'--bottom-mobile: ' . esc_attr($bottom_mobile),
			);
			
			// Add desktop offsets as default (will be overridden by CSS media queries using CSS variables)
			// Добавляем все свойства, даже если они 'auto', чтобы все значения были видны в inline стилях
			$styles[] = 'right: var(--right-desktop)';
			$styles[] = 'left: var(--left-desktop)';
			$styles[] = 'top: var(--top-desktop)';
			$styles[] = 'bottom: var(--bottom-desktop)';
			
			$style_attr = implode('; ', $styles);
			
			// Generate responsive CSS that uses CSS variables from inline styles
			$responsive_css = $this->get_responsive_styles();
			
			// Get animation type and determine class
			$animation_type = !empty($this->settings['animation_type']) ? $this->settings['animation_type'] : 'vertical';
			
			// В режиме Left используем align-items-start, в режиме Right - align-items-end
			$align_class = ($widget_position_side === 'left') ? 'align-items-start' : 'align-items-end';
			$widget_classes = 'share-buttons ' . $align_class . ' position-fixed';
			
			// Добавляем класс top-widget только если анимация вертикальная
			if ($animation_type === 'vertical') {
				$widget_classes .= ' top-widget';
			}
			
			// Добавляем класс right-widget если выбрано Right
			if ($widget_position_side === 'right') {
				$widget_classes .= ' right-widget';
			}
			
			// Добавляем класс для типа элементов виджета (icon или button)
			$widget_item_type = !empty($this->settings['widget_item_type']) ? $this->settings['widget_item_type'] : 'button';
			$widget_classes .= ' widget-item-' . esc_attr($widget_item_type);
			
			// Добавляем класс для комбинации основной кнопки и элементов (для правильного расчета расстояний)
			$widget_type = !empty($this->settings['widget_type']) ? $this->settings['widget_type'] : 'icon';
			$widget_classes .= ' widget-main-' . esc_attr($widget_type) . '-elements-' . esc_attr($widget_item_type);
			
			// Start output
			$output = '';
			
			// Add responsive CSS if available
			if (!empty($responsive_css)) {
				$output .= '<style type="text/css">' . $responsive_css . '</style>';
			}
			
			$output .= '<div class="' . esc_attr($widget_classes) . '" style="' . esc_attr($style_attr) . '">';
			
			// Get main icon
			$main_icon_raw = !empty($this->settings['icon']) ? $this->settings['icon'] : 'comment-dots';
			$main_icon = str_replace('uil-', '', $main_icon_raw);
			
			// Get button color from settings
			$button_color = !empty($this->settings['button_color']) ? esc_attr($this->settings['button_color']) : 'primary';
			$widget_type = !empty($this->settings['widget_type']) ? $this->settings['widget_type'] : 'icon';
			$btn_style = class_exists('Codeweber_Options') ? Codeweber_Options::style('button') : ' rounded-pill';

			// Main button - разная верстка для разных типов виджета
			if ($widget_type === 'button') {
				// Верстка для типа Button с текстом
				$button_text = !empty($this->settings['button_text']) ? esc_html($this->settings['button_text']) : esc_html__('Contact us', 'codeweber');
				$show_icon_mobile = !empty($this->settings['show_icon_mobile']) ? $this->settings['show_icon_mobile'] : false;
				// Добавляем класс для управления отображением на мобильных
				$button_mobile_class = $show_icon_mobile ? ' widget-button-mobile-icon' : '';
				$output .= '<button class="btn-text-hide btn btn-' . $button_color . ' py-0 ps-2 pe-2 has-ripple btn-icon btn-icon-start' . esc_attr($btn_style) . ' share-button-main no-rotate zindex-50' . esc_attr($button_mobile_class) . '" type="button">';
				$output .= '<i class="fs-28 uil uil-' . esc_attr($main_icon) . '"></i>';
				$output .= '<span class="ps-1 text-hide pe-2 widget-button-text">' . $button_text . '</span>';
				$output .= '</button>';
			} else {
				// Верстка для типа Icon — передаём скругление из темы (getThemeButton)
				$radius_class = trim($btn_style);
				if ($radius_class === '') {
					$radius_class = 'rounded';
				}
				$icon_style = !empty($this->settings['icon_style']) ? $this->settings['icon_style'] : 'btn-circle';
				$aria_label = !empty($this->settings['button_text'])
					? $this->settings['button_text']
					: __('Contact us', 'codeweber');
				$main_button = new CodeWeber_Floating_Button(array(
					'icon' => 'uil uil-' . esc_attr($main_icon),
					'color' => $button_color,
					'size' => 'lg',
					'class' => 'share-icon-main zindex-50',
					'tag' => 'button',
					'type' => 'button',
					'button_style' => $icon_style,
					'radius_class' => $radius_class,
					'aria_label' => $aria_label,
				));
				$output .= $main_button->render();
			}

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
				
				$label = $this->get_social_label($social_item);
				$button_class = $this->get_button_class($social_id);
				$icon_size = $this->get_icon_size_class($social_id);
				
				// Проверяем, является ли это формой (CodeWeber или CF7)
				$is_form = (strpos($social_id, 'form_') === 0 || strpos($social_id, 'cf7_') === 0);
				
				// Определяем тип формы и ID на основе префикса
				if (strpos($social_id, 'cf7_') === 0) {
					// CF7 форма
					$form_id = str_replace('cf7_', '', $social_id);
					$data_value = 'cf7-' . esc_attr($form_id);
				} elseif (strpos($social_id, 'form_') === 0) {
					// CodeWeber форма
					$form_id = str_replace('form_', '', $social_id);
					$data_value = 'cf-' . esc_attr($form_id);
				} else {
					$form_id = '';
					$data_value = '';
				}
				
				// Получаем тип элементов виджета
				$widget_item_type = !empty($this->settings['widget_item_type']) ? $this->settings['widget_item_type'] : 'button';
				
				// Build social button - разная верстка в зависимости от типа элементов
				if ($widget_item_type === 'icon') {
					// Верстка для типа Icon - только иконки в кружках
					$icon_style = !empty($this->settings['icon_style']) ? $this->settings['icon_style'] : 'btn-circle';
					$button_class_raw = $button_class;
					// Убираем префикс 'btn ' из button_class, так как CodeWeber_Floating_Button уже добавляет 'btn'
					$button_class_clean = str_replace('btn ', '', $button_class_raw);
					$button_class_clean = trim($button_class_clean);
					
					// Определяем color и дополнительные классы
					$color = '';
					$additional_classes = array('social', 'widget-social', 'icon-element');
					
					// Проверяем, содержит ли класс btn-gradient (для градиентных кнопок)
					if (strpos($button_class_clean, 'btn-gradient') !== false) {
						// Для градиентных кнопок: btn-gradient gradient-10
						$button_class_parts = explode(' ', $button_class_clean);
						foreach ($button_class_parts as $part) {
							if ($part === 'btn-gradient') {
								$additional_classes[] = 'btn-gradient';
							} elseif ($part !== 'btn-gradient') {
								$additional_classes[] = $part; // gradient-10
							}
						}
					} elseif (strpos($button_class_clean, 'btn-') === 0) {
						// Если начинается с 'btn-', используем как color (убираем префикс)
						$color = str_replace('btn-', '', $button_class_clean);
					} else {
						// Иначе это составной класс, добавляем как дополнительный
						$additional_classes[] = $button_class_clean;
					}
					
					$radius_class = trim($btn_style);
					if ($radius_class === '') {
						$radius_class = 'rounded';
					}
					// Для формы используем data-атрибуты для открытия модального окна
					if ($is_form) {
						$social_button = new CodeWeber_Floating_Button(array(
							'icon' => $icon_class,
							'color' => $color,
							'size' => 'lg',
							'class' => implode(' ', $additional_classes),
							'href' => 'javascript:void(0)',
							'title' => esc_attr($label),
							'data' => array(
								'value' => $data_value,
								'bs-toggle' => 'modal',
								'bs-target' => '#modal'
							),
							'tag' => 'a',
							'button_style' => $icon_style,
							'radius_class' => $radius_class,
						));
					} else {
						// Build social button using CodeWeber_Floating_Button (Icon variant)
						$social_button = new CodeWeber_Floating_Button(array(
							'icon' => $icon_class,
							'color' => $color,
							'size' => 'lg',
							'class' => implode(' ', $additional_classes),
							'href' => $social_url,
							'title' => esc_attr($label),
							'target' => '_blank',
							'rel' => 'noopener noreferrer',
							'tag' => 'a',
							'button_style' => $icon_style,
							'radius_class' => $radius_class,
						));
					}
					$output .= $social_button->render();
				} else {
					// Верстка для типа Button - кнопки с текстом (по умолчанию)
					if ($is_form) {
						// Для формы используем data-атрибуты для открытия модального окна
						$output .= '<a href="javascript:void(0)" ';
						$output .= 'class="' . esc_attr($button_class) . ' py-0 ps-2 pe-2 has-ripple btn-icon btn-icon-start' . esc_attr($btn_style) . ' widget-social button-element justify-content-between w-100" ';
						$output .= 'title="' . esc_attr($label) . '" ';
						$output .= 'data-value="' . esc_attr($data_value) . '" ';
						$output .= 'data-bs-toggle="modal" data-bs-target="#modal">';
					} else {
						$output .= '<a href="' . $social_url . '" ';
						$output .= 'class="' . esc_attr($button_class) . ' py-0 ps-2 pe-2 has-ripple btn-icon btn-icon-start' . esc_attr($btn_style) . ' widget-social button-element justify-content-between w-100" ';
						$output .= 'title="' . esc_attr($label) . '" ';
						$output .= 'target="_blank" rel="noopener noreferrer">';
					}
					$output .= '<i class="' . esc_attr($icon_size) . ' ' . esc_attr($icon_class) . ' me-0"></i>';
					$output .= '<span class="ps-1 pe-2">' . esc_html($label) . '</span>';
					$output .= '</a>';
				}
			}
			
			$output .= '</div>';
			

			
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
				

			}
			

			
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

