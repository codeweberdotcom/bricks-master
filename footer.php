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
		// #region agent log
		$log_data = json_encode(['location' => 'footer.php:14', 'message' => 'Footer render start', 'data' => ['opt_name' => $opt_name ?? 'NOT_SET', 'class_exists_Redux' => class_exists('Redux'), 'redux_instance_exists' => $redux_instance !== null], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']);
		$log_file = ABSPATH . '.cursor/debug.log';
		@file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
		// #endregion
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

			if (!empty($footer_for_this_page_id) && $footer_for_this_page_bool == '2') {
				$template_footer_id = $footer_for_this_page_id;
			} elseif (!empty($single_footer_id) && $footer_for_this_page_bool == '1') {
				$template_footer_id = $single_footer_id;
			} elseif ($global_footer_type === '2') {
				$template_footer_id = $global_custom_template_footer;
			} else {
				$template_footer_id = '';
			}
		} elseif (is_archive() || is_post_type_archive($post_type)) {
			if (!empty($archive_footer_id)) {
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
		// #region agent log
		$log_data = json_encode(['location' => 'footer.php:78', 'message' => 'Footer template decision', 'data' => ['opt_name' => $opt_name ?? 'NOT_SET', 'template_footer_id' => $template_footer_id ?? 'EMPTY', 'global_template_footer' => $global_template_footer ?? 'EMPTY', 'global_footer_type' => $global_footer_type ?? 'EMPTY', 'is_single' => is_single(), 'is_archive' => is_archive(), 'post_type' => $post_type], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'C']);
		$log_file = ABSPATH . '.cursor/debug.log';
		@file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
		// #endregion

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
	</body>