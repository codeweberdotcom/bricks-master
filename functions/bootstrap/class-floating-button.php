<?php
/**
 * Floating Button Class
 * 
 * Универсальный класс для создания плавающих кнопок
 * Поддерживает любые иконки, цвета, data-атрибуты, кастомные ID и классы
 * 
 * @package CodeWeber
 * @version 1.0.0
 */

if (!class_exists('CodeWeber_Floating_Button')) {
	class CodeWeber_Floating_Button {
		
		/**
		 * Button configuration
		 * 
		 * @var array
		 */
		private $config;
		
		/**
		 * Constructor
		 * 
		 * @param array $config Configuration array
		 */
		public function __construct($config = array()) {
			$this->config = wp_parse_args($config, array(
				'icon' => '',              // Класс иконки (например, 'uil uil-comment-dots')
				'icon_color' => '',        // Цвет иконки (CSS color)
				'background' => '',       // Цвет фона (CSS color)
				'size' => 'lg',            // Размер: 'sm', 'md', 'lg', 'xl'
				'color' => 'primary',      // Цвет кнопки (Bootstrap класс, например 'btn-primary')
				'position' => '',          // position CSS: 'fixed', 'absolute', 'relative' (пусто = без позиционирования)
				'top' => 'auto',           // top offset (число или строка с единицами)
				'right' => 'auto',         // right offset
				'bottom' => 'auto',        // bottom offset
				'left' => 'auto',          // left offset
				'z_index' => 0,            // z-index (0 = не добавлять)
				'id' => '',                // HTML id атрибут
				'class' => '',             // Дополнительные CSS классы (строка или массив)
				'data' => array(),         // data-атрибуты ['toggle' => 'modal', 'target' => '#myModal']
				'href' => '',             // URL для ссылки (если пусто, будет button)
				'target' => '',            // target для ссылки ('_blank', '_self' и т.д.)
				'rel' => '',               // rel для ссылки ('noopener', 'noreferrer' и т.д.)
				'title' => '',             // title атрибут (подсказка при наведении)
				'aria_label' => '',        // aria-label
			'aria_labelledby' => '',   // aria-labelledby
			'onclick' => '',           // onclick handler (JavaScript)
			'tag' => 'auto',           // HTML тег: 'a', 'button' или 'auto' (определяется по href)
			'type' => 'button',        // type для button ('button', 'submit', 'reset')
			'disabled' => false,       // disabled атрибут для button
			'style' => array(),        // Дополнительные inline стили ['margin' => '10px']
			'button_style' => 'btn-circle', // Стиль кнопки: 'btn-circle' или 'btn-block'
			'radius_class' => '',     // Класс скругления из темы (getThemeButton): rounded-pill, rounded, rounded-xl, rounded-0
		));
		}
		
		/**
		 * Render button HTML
		 * 
		 * @return string HTML output
		 */
		public function render() {
			// Определяем тег
			$tag = $this->get_tag();
			
			// Формируем атрибуты
			$attributes = $this->get_attributes();
			
			// Формируем классы
			$classes = $this->get_classes();
			if (!empty($classes)) {
				$attributes['class'] = $classes;
			}
			
			// Формируем стили
			$styles = $this->get_styles();
			if (!empty($styles)) {
				$attributes['style'] = $styles;
			}
			
			// Формируем HTML
			$output = '<' . esc_attr($tag) . ' ' . $this->build_attributes_string($attributes) . '>';
			
			// Добавляем иконку
			if (!empty($this->config['icon'])) {
				$icon_style = '';
				if (!empty($this->config['icon_color'])) {
					$icon_style = ' style="color: ' . esc_attr($this->config['icon_color']) . ';"';
				}
				$output .= '<i class="' . esc_attr($this->config['icon']) . '"' . $icon_style . '></i>';
			}
			
			$output .= '</' . esc_attr($tag) . '>';
			
			return $output;
		}
		
		/**
		 * Get HTML tag (a or button)
		 * 
		 * @return string
		 */
		private function get_tag() {
			if ($this->config['tag'] !== 'auto') {
				return $this->config['tag'];
			}
			
			// Если есть href, используем <a>, иначе <button>
			return !empty($this->config['href']) ? 'a' : 'button';
		}
		
		/**
		 * Get CSS classes
		 * 
		 * @return string
		 */
		private function get_classes() {
			$radius_class = !empty($this->config['radius_class']) ? trim($this->config['radius_class']) : '';
			// Если задан класс скругления из темы — используем его вместо фиксированного btn-circle
			if ($radius_class !== '') {
				$button_style = 'btn-block';
				$classes = array('btn', $button_style, $radius_class, 'has-ripple');
			} else {
				$button_style = !empty($this->config['button_style']) ? $this->config['button_style'] : 'btn-circle';
				$classes = array('btn', $button_style, 'has-ripple');
			}
			
			// Размер
			if (!empty($this->config['size'])) {
				$size_class = 'btn-' . $this->config['size'];
				if (in_array($this->config['size'], array('sm', 'md', 'lg', 'xl'))) {
					$classes[] = $size_class;
				}
			}
			
			// Цвет (если не задан background и color не пустой)
			if (empty($this->config['background']) && !empty($this->config['color'])) {
				// Проверяем, что color не содержит пробелов (не составной класс)
				if (strpos($this->config['color'], ' ') === false) {
					$color_class = 'btn-' . $this->config['color'];
					$classes[] = $color_class;
				} else {
					// Если это составной класс (например "gradient gradient-10"), добавляем как есть
					$classes[] = $this->config['color'];
				}
			}
			
			// Дополнительные классы
			if (!empty($this->config['class'])) {
				if (is_array($this->config['class'])) {
					$classes = array_merge($classes, $this->config['class']);
				} else {
					$additional_classes = explode(' ', $this->config['class']);
					$classes = array_merge($classes, $additional_classes);
				}
			}
			
			return implode(' ', array_unique(array_filter($classes)));
		}
		
		/**
		 * Get inline styles
		 * 
		 * @return string
		 */
		private function get_styles() {
			$styles = array();
			
			// Проверяем, есть ли offsets (top, right, bottom, left)
			$has_offsets = false;
			$offsets = array('top', 'right', 'bottom', 'left');
			foreach ($offsets as $offset) {
				if (!empty($this->config[$offset]) && $this->config[$offset] !== 'auto') {
					$has_offsets = true;
					break;
				}
			}
			
			// Position - добавляем только если явно указано или есть offsets
			// Это позволяет использовать кнопку внутри контейнеров без позиционирования
			if (!empty($this->config['position']) || $has_offsets) {
				$position = !empty($this->config['position']) ? $this->config['position'] : 'fixed';
				$styles[] = 'position: ' . esc_attr($position);
			}
			
			// Offsets - добавляем только если есть позиционирование
			if (!empty($this->config['position']) || $has_offsets) {
				foreach ($offsets as $offset) {
					if (!empty($this->config[$offset]) && $this->config[$offset] !== 'auto') {
						$value = $this->config[$offset];
						// Добавляем px если это число
						if (is_numeric($value)) {
							$value = intval($value) . 'px';
						}
						$styles[] = $offset . ': ' . esc_attr($value);
					}
				}
			}
			
			// Z-index - добавляем только если есть позиционирование и z_index > 0
			if ((!empty($this->config['position']) || $has_offsets) && !empty($this->config['z_index']) && intval($this->config['z_index']) > 0) {
				$styles[] = 'z-index: ' . intval($this->config['z_index']);
			}
			
			// Background
			if (!empty($this->config['background'])) {
				$styles[] = 'background-color: ' . esc_attr($this->config['background']);
			}
			
			// Дополнительные стили
			if (!empty($this->config['style']) && is_array($this->config['style'])) {
				foreach ($this->config['style'] as $property => $value) {
					$styles[] = esc_attr($property) . ': ' . esc_attr($value);
				}
			}
			
			return implode('; ', $styles);
		}
		
		/**
		 * Get HTML attributes
		 * 
		 * @return array
		 */
		private function get_attributes() {
			$attributes = array();
			
			// ID
			if (!empty($this->config['id'])) {
				$attributes['id'] = esc_attr($this->config['id']);
			}
			
			// href (для ссылок)
			$tag = $this->get_tag();
			if ($tag === 'a') {
				$href = !empty($this->config['href']) ? $this->config['href'] : '#';
				// Для javascript:void(0) не используем esc_url, так как это не валидный URL
				if ($href === 'javascript:void(0)') {
					$attributes['href'] = 'javascript:void(0)';
				} else {
					$attributes['href'] = esc_url($href);
				}
				
				// target
				if (!empty($this->config['target'])) {
					$attributes['target'] = esc_attr($this->config['target']);
				}
				
				// rel
				if (!empty($this->config['rel'])) {
					$attributes['rel'] = esc_attr($this->config['rel']);
				} elseif (!empty($this->config['target']) && $this->config['target'] === '_blank') {
					// Автоматически добавляем rel="noopener noreferrer" для _blank
					$attributes['rel'] = 'noopener noreferrer';
				}
			} else {
				// type для button
				if (!empty($this->config['type'])) {
					$attributes['type'] = esc_attr($this->config['type']);
				}
				
				// disabled для button
				if (!empty($this->config['disabled'])) {
					$attributes['disabled'] = 'disabled';
				}
			}
			
			// title
			if (!empty($this->config['title'])) {
				$attributes['title'] = esc_attr($this->config['title']);
			}
			
			// aria-label
			if (!empty($this->config['aria_label'])) {
				$attributes['aria-label'] = esc_attr($this->config['aria_label']);
			}
			
			// aria-labelledby
			if (!empty($this->config['aria_labelledby'])) {
				$attributes['aria-labelledby'] = esc_attr($this->config['aria_labelledby']);
			}
			
			// onclick
			if (!empty($this->config['onclick'])) {
				$attributes['onclick'] = $this->config['onclick'];
			}
			
			// data-атрибуты
			if (!empty($this->config['data']) && is_array($this->config['data'])) {
				foreach ($this->config['data'] as $key => $value) {
					$data_key = 'data-' . str_replace('_', '-', $key);
					$attributes[$data_key] = esc_attr($value);
				}
			}
			
			return $attributes;
		}
		
		/**
		 * Build attributes string from array
		 * 
		 * @param array $attributes
		 * @return string
		 */
		private function build_attributes_string($attributes) {
			$output = array();
			foreach ($attributes as $key => $value) {
				if ($value === '' || $value === null) {
					continue;
				}
				if ($value === true) {
					$output[] = esc_attr($key);
				} else {
					$output[] = esc_attr($key) . '="' . esc_attr($value) . '"';
				}
			}
			return implode(' ', $output);
		}
		
		/**
		 * Get configuration (for debugging or extending)
		 * 
		 * @return array
		 */
		public function get_config() {
			return $this->config;
		}
		
		/**
		 * Set configuration value
		 * 
		 * @param string $key
		 * @param mixed $value
		 * @return void
		 */
		public function set_config($key, $value) {
			$this->config[$key] = $value;
		}
	}
}

