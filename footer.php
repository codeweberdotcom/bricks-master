	</div> <!-- #content-wrapper -->

	<?php


	if (class_exists('Redux')) {
		global $opt_name;
		$post_type = get_post_type();
		$post_id = get_the_ID();

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