	</div> <!-- #content-wrapper -->

	<div class="progress-wrap active-progress">
		<svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
			<path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" style="transition: stroke-dashoffset 10ms linear; stroke-dasharray: 307.919px, 307.919px; stroke-dashoffset: 298.13px;"></path>
		</svg>
	</div>

	
	<?php


	if (class_exists('Redux')) {
		global $opt_name;
		// Убеждаемся, что $opt_name установлена
		if (empty($opt_name)) {
			$opt_name = 'redux_demo';
		}
		// Проверяем, что Redux экземпляр инициализирован
		$redux_instance = Redux_Instances::get_instance($opt_name);
		$post_type = universal_get_post_type();
		$post_id = get_the_ID();
		
		// Если Redux не инициализирован, выходим
		if ($redux_instance === null) {
			get_template_part('templates/footer/footer');
			return;
		}

		$global_footer_type = Redux::get_option($opt_name, 'global-footer-type');
		$global_template_footer = Redux::get_option($opt_name, 'global-footer-model');
		$global_custom_template_footer = Redux::get_option($opt_name, 'custom-footer');

		$single_footer_id = Redux::get_option($opt_name, 'single_footer_select_' . $post_type);
		$archive_footer_id = Redux::get_option($opt_name, 'archive_footer_select_' . $post_type);

		$footer_for_this_page_bool = Redux::get_post_meta($opt_name, $post_id, 'this-post-footer-type');
		$footer_for_this_page_id = Redux::get_post_meta($opt_name, $post_id, 'custom-post-footer');

		// Определяем тип страницы (одиночная или архив)
		if (is_single() || is_singular($post_type)) {
			// Проверяем индивидуальные настройки записи
			if ($footer_for_this_page_bool === '3') {
				return; // Disable - не выводим footer
			}
			
			// Проверяем, не отключен ли footer
			if ($single_footer_id === 'disable') {
				return; // Не выводим footer
			}

			if (!empty($footer_for_this_page_id) && $footer_for_this_page_bool == '2') {
				$template_footer_id = $footer_for_this_page_id;
			} elseif (!empty($single_footer_id) && $single_footer_id !== 'default' && $footer_for_this_page_bool == '1') {
				$template_footer_id = $single_footer_id;
			} elseif ($global_footer_type === '2') {
				$template_footer_id = $global_custom_template_footer;
			} else {
				$template_footer_id = '';
			}
		} elseif (is_archive() || is_post_type_archive($post_type)) {
			// Проверяем, не отключен ли footer
			if ($archive_footer_id === 'disable') {
				return; // Не выводим footer
			}

			if (!empty($archive_footer_id) && $archive_footer_id !== 'default') {
				$template_footer_id = Redux::get_option($opt_name, 'archive_footer_select_' . $post_type);
			} elseif ($global_footer_type === '2') {
				$template_footer_id = $global_custom_template_footer;
			} else {
				$template_footer_id = '';
			}
		} else {
			// Другие случаи (можете задать значение по умолчанию)
			$template_footer_id = '';
		}

		// Функция подготовки всех необходимых переменных для pageheader
		if (!function_exists('get_footer_vars')) {
			function get_footer_vars()
			{
				if (!class_exists('Redux')) {
					return [];
				}
				global $opt_name;

				// Заголовок и стили
				$footer_color_text = Redux::get_option($opt_name, 'footer_color_text') ?? '';
				$footer_background = Redux::get_option($opt_name, 'footer_background') ?? '';
				$footer_solid_color = Redux::get_option($opt_name, 'footer_solid_color') ?? '';
				$footer_soft_color = Redux::get_option($opt_name, 'footer_soft_color') ?? '';

				return [
					'footer_color_text' => $footer_color_text,
					'footer_background' => $footer_background,
					'footer_solid_color' => $footer_solid_color,
					'footer_soft_color' => $footer_soft_color,
				];
			}
		}

		// Получаем переменные для шаблона
		$footer_vars = get_footer_vars();

		if ($template_footer_id) {
			$post = get_post($template_footer_id);
			$content = $post->post_content;
			$content = apply_filters('the_content', $content);
			$content = do_shortcode($content); // Обрабатываем шорткоды
			echo $content;
		} else {
			if (!empty($global_template_footer)) {
				$template_part = get_theme_file_path("templates/footer/footer-{$global_template_footer}.php");
				if (file_exists($template_part)) {
					// Подключаем шаблон с переменными
					require $template_part;
				}
			}
		}
	} else {
		get_template_part('templates/footer/footer');
	}
	?>
	
	<?php wp_footer(); ?>
	
	<?php
	// Плавающий виджет соцсетей (после wp_footer, чтобы был вне всех контейнеров)
	// Новая версия с поддержкой множественных соцсетей
	// #region agent log
	$log_data = json_encode(['location' => 'footer.php:131', 'message' => 'Footer widget section entry', 'data' => ['function_exists_new' => function_exists('codeweber_floating_social_widget_new'), 'function_exists_old' => function_exists('codeweber_floating_social_widget')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
	@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
	// #endregion
	
	if (function_exists('codeweber_floating_social_widget_new')) {
		// Не передаем шаблон - метод render() сам выберет на основе widget_type из Redux
		$widget_output = codeweber_floating_social_widget_new();
		
		// #region agent log
		$log_data = json_encode(['location' => 'footer.php:137', 'message' => 'Widget output received in footer', 'data' => ['output_length' => strlen($widget_output), 'output_empty' => empty($widget_output), 'output_preview' => substr($widget_output, 0, 100)], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
		@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
		// #endregion
		
		if (!empty($widget_output)) {
			// Временный тестовый комментарий для проверки вывода
			echo '<!-- FLOATING WIDGET START: ' . strlen($widget_output) . ' bytes -->';
			echo $widget_output;
			echo '<!-- FLOATING WIDGET END -->';
			// #region agent log
			$log_data = json_encode(['location' => 'footer.php:147', 'message' => 'Widget output echoed', 'data' => ['output_length' => strlen($widget_output)], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']);
			@file_put_contents(ABSPATH . '.cursor/debug.log', $log_data . "\n", FILE_APPEND);
			// #endregion
		} elseif (defined('WP_DEBUG') && WP_DEBUG) {
			// Временный тестовый вывод для отладки
			echo '<!-- Floating Social Widget: функция вызвана, но вывод пустой -->';
		}
	} elseif (function_exists('codeweber_floating_social_widget')) {
		// Старая версия для обратной совместимости
		echo codeweber_floating_social_widget();
	} elseif (defined('WP_DEBUG') && WP_DEBUG) {
		// Временный тестовый вывод для отладки
		echo '<!-- Floating Social Widget: функции не найдены -->';
	}
	?>
	
	</body>