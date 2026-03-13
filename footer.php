	</main> <!-- /.content-wrapper -->

	<?php
	// Плавающий виджет соцсетей (выводим перед футером)
	if ( function_exists( 'codeweber_floating_social_widget_new' ) ) {
		$widget_output = codeweber_floating_social_widget_new();
		if ( ! empty( $widget_output ) ) {
			echo $widget_output;
		} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			echo '<!-- Floating Social Widget: функция вызвана, но вывод пустой -->';
		}
	} elseif ( function_exists( 'codeweber_floating_social_widget' ) ) {
		// Старая версия для обратной совместимости
		echo codeweber_floating_social_widget();
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		echo '<!-- Floating Social Widget: функции не найдены -->';
	}
	?>

	<div class="progress-wrap active-progress">
		<svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
			<path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" style="transition: stroke-dashoffset 10ms linear; stroke-dasharray: 307.919px, 307.919px; stroke-dashoffset: 298.13px;"></path>
		</svg>
	</div>

	<?php
	if ( Codeweber_Options::is_ready() ) {
		$post_type = universal_get_post_type();
		$post_id   = get_the_ID();

		$global_footer_type            = Codeweber_Options::get( 'global_footer_type' );
		$global_template_footer        = Codeweber_Options::get( 'global-footer-model' );
		$global_custom_template_footer = Codeweber_Options::get( 'custom-footer' );

		$single_footer_id  = Codeweber_Options::get( 'single_footer_select_' . $post_type );
		$archive_footer_id = Codeweber_Options::get( 'archive_footer_select_' . $post_type );

		$footer_for_this_page_bool = Codeweber_Options::get_post_meta( $post_id, 'this-post-footer-type' );
		$footer_for_this_page_id   = Codeweber_Options::get_post_meta( $post_id, 'custom-post-footer' );

		// Определяем тип страницы (одиночная или архив)
		if ( is_single() || is_singular( $post_type ) ) {
			if ( $footer_for_this_page_bool === '3' ) {
				return; // Disable — не выводим footer
			}
			if ( $single_footer_id === 'disable' ) {
				return;
			}

			if ( ! empty( $footer_for_this_page_id ) && $footer_for_this_page_bool == '2' ) {
				$template_footer_id = $footer_for_this_page_id;
			} elseif ( ! empty( $single_footer_id ) && $single_footer_id !== 'default' && $footer_for_this_page_bool == '1' ) {
				$template_footer_id = $single_footer_id;
			} elseif ( $global_footer_type === '2' ) {
				$template_footer_id = $global_custom_template_footer;
			} else {
				$template_footer_id = '';
			}
		} elseif ( is_archive() || is_post_type_archive( $post_type ) ) {
			if ( $archive_footer_id === 'disable' ) {
				return;
			}

			if ( ! empty( $archive_footer_id ) && $archive_footer_id !== 'default' ) {
				$template_footer_id = Codeweber_Options::get( 'archive_footer_select_' . $post_type );
			} elseif ( $global_footer_type === '2' ) {
				$template_footer_id = $global_custom_template_footer;
			} else {
				$template_footer_id = '';
			}
		} else {
			// Главная «Последние записи», 404 и т.д.
			if ( $global_footer_type === '2' && ! empty( $global_custom_template_footer ) ) {
				$template_footer_id = $global_custom_template_footer;
			} else {
				$template_footer_id = '';
			}
		}

		// Переменные стилизации для шаблона футера
		if ( ! function_exists( 'get_footer_vars' ) ) {
			function get_footer_vars() {
				return [
					'footer_color_text'  => Codeweber_Options::get( 'footer_color_text' ),
					'footer_background'  => Codeweber_Options::get( 'footer_background' ),
					'footer_solid_color' => Codeweber_Options::get( 'footer_solid_color' ),
					'footer_soft_color'  => Codeweber_Options::get( 'footer_soft_color' ),
				];
			}
		}

		$footer_vars = get_footer_vars();

		if ( $template_footer_id ) {
			$post    = get_post( $template_footer_id );
			$content = $post->post_content;
			$content = apply_filters( 'the_content', $content );
			$content = do_shortcode( $content );
			echo $content;
		} else {
			if ( ! empty( $global_template_footer ) ) {
				$template_part = get_theme_file_path( "templates/footer/footer-{$global_template_footer}.php" );
				if ( file_exists( $template_part ) ) {
					require $template_part;
				}
			}
		}
	} else {
		get_template_part( 'templates/footer/footer' );
	}
	?>

	<?php
	// Закрываем page-frame обёртку, если она была открыта в header.php
	if ( (bool) Codeweber_Options::get( 'page-frame', false ) ) {
		echo '</div><!-- /.page-frame -->';
	}
	?>

	<?php
	// Выводим модальное окно перед wp_footer()
	if ( function_exists( 'codeweber_universal_modal_container' ) ) {
		codeweber_universal_modal_container();
	}
	?>

	<?php wp_footer(); ?>

	</body>
</html>
