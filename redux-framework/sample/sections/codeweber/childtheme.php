<?php

/**
 * Redux Framework Child Theme Creator
 * For full documentation, please visit: https://devs.redux.io/
 *
 * @package Redux Framework
 */

Redux::set_section(
	$opt_name,
	array(
		'title'  => esc_html__('Child Theme Creator', 'codeweber'),
		'id'     => 'child_theme_creator',
		'desc'   => esc_html__('Create and manage child themes', 'codeweber'),
		'icon'   => 'el el-briefcase',
		'fields' => array(
			array(
				'id'       => 'child_theme_name',
				'type'     => 'text',
				'title'    => esc_html__('Child Theme Name', 'codeweber'),
				'desc'     => esc_html__('Enter the name for your child theme', 'codeweber'),
				'default'  => '',
			),
			array(
				'id'       => 'child_theme_description',
				'type'     => 'textarea',
				'title'    => esc_html__('Description', 'codeweber'),
				'desc'     => esc_html__('Brief description of the child theme', 'codeweber'),
				'default'  => '',
			),
			array(
				'id'       => 'child_theme_author',
				'type'     => 'text',
				'title'    => esc_html__('Author', 'codeweber'),
				'default'  => get_option('blogname'),
			),
			array(
				'id'       => 'child_theme_author_uri',
				'type'     => 'text',
				'title'    => esc_html__('Author URI', 'codeweber'),
				'default'  => get_option('home'),
			),
			array(
				'id'       => 'child_theme_version',
				'type'     => 'text',
				'title'    => esc_html__('Version', 'codeweber'),
				'default'  => '1.0.0',
			),
			array(
				'id'       => 'child_theme_template',
				'type'     => 'select',
				'title'    => esc_html__('Parent Theme', 'codeweber'),
				'desc'     => esc_html__('Select the parent theme', 'codeweber'),
				'options'  => redux_get_parent_themes_options(),
				'default'  => get_template(),
			),
			array(
				'id'   => 'child_theme_create',
				'type' => 'raw',
				'title' => esc_html__('Create Child Theme', 'codeweber'),
				'content' => '
                    <button type="button" class="button button-primary" id="create-child-theme">' . esc_html__('Create Child Theme', 'codeweber') . '</button>
                    <span id="child-theme-result" style="margin-left: 15px; line-height: 2.2;"></span>

                    <script>
                    jQuery(document).ready(function($) {
                        $("#create-child-theme").on("click", function() {
                            $("#child-theme-result").text("' . esc_js(__('Creating...', 'codeweber')) . '");

                            const data = {
                                action: "redux_create_child_theme",
                                nonce: "' . wp_create_nonce('redux_child_theme_nonce') . '",
                                name: $("[name*=\'[child_theme_name]\']").val(),
                                description: $("[name*=\'[child_theme_description]\']").val(),
                                author: $("[name*=\'[child_theme_author]\']").val(),
                                author_uri: $("[name*=\'[child_theme_author_uri]\']").val(),
                                version: $("[name*=\'[child_theme_version]\']").val(),
                                template: $("[name*=\'[child_theme_template]\']").val()
                            };

                            if (!data.name) {
                                $("#child-theme-result").css("color", "red").text("' . esc_js(__('Theme name is required', 'codeweber')) . '");
                                return;
                            }

                            if (!data.template) {
                                $("#child-theme-result").css("color", "red").text("' . esc_js(__('Parent theme is required', 'codeweber')) . '");
                                return;
                            }

                            $.post("' . admin_url('admin-ajax.php') . '", data, function(response) {
                                if (response.success) {
                                    $("#child-theme-result").css("color", "green").text(response.data.message);
                                    // Обновляем список тем
                                    setTimeout(function() {
                                        location.reload();
                                    }, 2000);
                                } else {
                                    $("#child-theme-result").css("color", "red").text(response.data);
                                }
                            }).fail(function(jqXHR, textStatus, errorThrown) {
                                $("#child-theme-result").css("color", "red").text("' . esc_js(__('AJAX error: ', 'codeweber')) . '" + textStatus);
                            });
                        });

                        // Обработчик активации темы
                        $(document).on("click", ".activate-child-theme", function(e) {
                            e.preventDefault();
                            var themeSlug = $(this).data("theme");
                            var button = $(this);

                            button.text("' . esc_js(__('Activating...', 'codeweber')) . '").prop("disabled", true);

                            $.post("' . admin_url('admin-ajax.php') . '", {
                                action: "redux_activate_child_theme",
                                nonce: "' . wp_create_nonce('redux_activate_theme_nonce') . '",
                                theme: themeSlug
                            }, function(response) {
                                if (response.success) {
                                    button.text("' . esc_js(__('Activated', 'codeweber')) . '").css("background-color", "#46b450");
                                    setTimeout(function() {
                                        location.reload();
                                    }, 1000);
                                } else {
                                    button.text("' . esc_js(__('Activate', 'codeweber')) . '").prop("disabled", false);
                                    alert(response.data);
                                }
                            }).fail(function() {
                                button.text("' . esc_js(__('Activate', 'codeweber')) . '").prop("disabled", false);
                                alert("' . esc_js(__('AJAX error', 'codeweber')) . '");
                            });
                        });
                    });
                    </script>
                ',
			),
			array(
				'id'   => 'child_themes_list',
				'type' => 'raw',
				'title' => esc_html__('Existing Child Themes', 'codeweber'),
				'content' => redux_get_child_themes_list(),
			),
		),
	)
);

/**
 * Get parent themes options for select field
 */
function redux_get_parent_themes_options()
{
	$themes = wp_get_themes();
	$options = array('' => esc_html__('Select Parent Theme', 'codeweber'));

	foreach ($themes as $theme) {
		// Показываем только темы, которые не являются child themes
		if (!$theme->get('Template')) {
			$theme_name = $theme->get('Name');
			$template = $theme->get('Template');
			$options[$theme->get_stylesheet()] = $theme_name;
		}
	}

	if (count($options) === 1) {
		return array('' => esc_html__('No parent themes found', 'codeweber'));
	}

	return $options;
}

/**
 * Get list of all child themes
 */
function redux_get_child_themes_list()
{
	$themes = wp_get_themes();
	$child_themes = array();
	$current_theme = get_stylesheet();
	$output = '';

	foreach ($themes as $theme) {
		if ($theme->get('Template') && $theme->get('Template') !== '') {
			$child_themes[] = $theme;
		}
	}

	if (empty($child_themes)) {
		return '<p>' . esc_html__('No child themes found.', 'codeweber') . '</p>';
	}

	$output .= '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">';
	$output .= '<ul style="list-style: none; margin: 0; padding: 0;">';

	foreach ($child_themes as $theme) {
		$theme_slug = $theme->get_stylesheet();
		$is_active = ($current_theme === $theme_slug);

		$output .= '<li style="margin-bottom: 15px; padding: 10px; border-bottom: 1px solid #eee;' . ($is_active ? ' background-color: #f0f9ff;' : '') . '">';
		$output .= '<div style="display: flex; justify-content: space-between; align-items: center;">';
		$output .= '<div>';
		$output .= '<strong>' . esc_html($theme->get('Name')) . '</strong>';
		if ($is_active) {
			$output .= ' <span style="color: #46b450; font-weight: bold;">(' . esc_html__('Active', 'codeweber') . ')</span>';
		}
		$output .= '<br>';
		$output .= '<small>' . esc_html__('Parent:', 'codeweber') . ' ' . esc_html($theme->get('Template')) . '</small><br>';
		$output .= '<small>' . esc_html__('Version:', 'codeweber') . ' ' . esc_html($theme->get('Version')) . '</small><br>';
		$output .= '<small>' . esc_html__('Author:', 'codeweber') . ' ' . esc_html($theme->get('Author')) . '</small>';
		$output .= '</div>';

		if (!$is_active) {
			$output .= '<button class="button button-primary activate-child-theme" data-theme="' . esc_attr($theme_slug) . '" style="margin-left: 10px;">' . esc_html__('Activate', 'codeweber') . '</button>';
		} else {
			$output .= '<button class="button" disabled style="margin-left: 10px; background-color: #46b450; color: white;">' . esc_html__('Active', 'codeweber') . '</button>';
		}

		$output .= '</div>';
		$output .= '</li>';
	}

	$output .= '</ul>';
	$output .= '</div>';

	return $output;
}

/**
 * AJAX handler for creating child theme
 */
add_action('wp_ajax_redux_create_child_theme', 'redux_create_child_theme_callback');
function redux_create_child_theme_callback()
{
	// Check nonce
	if (!wp_verify_nonce($_POST['nonce'], 'redux_child_theme_nonce')) {
		wp_send_json_error(esc_html__('Security check failed', 'codeweber'));
	}

	// Check permissions
	if (!current_user_can('switch_themes')) {
		wp_send_json_error(esc_html__('Insufficient permissions', 'codeweber'));
	}

	$name = sanitize_text_field($_POST['name'] ?? '');
	$description = sanitize_text_field($_POST['description'] ?? '');
	$author = sanitize_text_field($_POST['author'] ?? '');
	$author_uri = esc_url_raw($_POST['author_uri'] ?? '');
	$version = sanitize_text_field($_POST['version'] ?? '1.0.0');
	$template = sanitize_text_field($_POST['template'] ?? '');

	if (empty($name)) {
		wp_send_json_error(esc_html__('Theme name is required', 'codeweber'));
	}

	if (empty($template)) {
		wp_send_json_error(esc_html__('Parent theme is required', 'codeweber'));
	}

	// Проверяем, существует ли родительская тема
	$parent_theme = wp_get_theme($template);
	if (!$parent_theme->exists()) {
		wp_send_json_error(esc_html__('Parent theme does not exist', 'codeweber'));
	}

	// Create theme directory
	$theme_slug = sanitize_title($name);
	$theme_dir = get_theme_root() . '/' . $theme_slug;

	if (file_exists($theme_dir)) {
		wp_send_json_error(esc_html__('Theme directory already exists', 'codeweber'));
	}

	// Create directory
	if (!wp_mkdir_p($theme_dir)) {
		wp_send_json_error(esc_html__('Could not create theme directory', 'codeweber'));
	}

	// Create style.css
	$style_content = "/*
Theme Name: {$name}
Theme URI:
Description: {$description}
Author: {$author}
Author URI: {$author_uri}
Template: {$template}
Version: {$version}
Text Domain: {$theme_slug}
*/
";

	if (!file_put_contents($theme_dir . '/style.css', $style_content)) {
		// Удаляем директорию если не удалось создать файл
		rmdir($theme_dir);
		wp_send_json_error(esc_html__('Could not create style.css', 'codeweber'));
	}

	// Create functions.php
	$functions_content = "<?php
/**
 * {$name} functions and definitions
 *
 * @package {$name}
 */

add_action( 'wp_enqueue_scripts', '{$theme_slug}_enqueue_styles' );
function {$theme_slug}_enqueue_styles() {
    wp_enqueue_style( '{$theme_slug}-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( '{$template}-style' ),
        wp_get_theme()->get('Version')
    );
}
";

	if (!file_put_contents($theme_dir . '/functions.php', $functions_content)) {
		// Удаляем файлы если не удалось создать functions.php
		unlink($theme_dir . '/style.css');
		rmdir($theme_dir);
		wp_send_json_error(esc_html__('Could not create functions.php', 'codeweber'));
	}

	// Create screenshot (optional)
	$screenshot_path = get_theme_root() . '/' . $template . '/screenshot.png';
	if (file_exists($screenshot_path)) {
		copy($screenshot_path, $theme_dir . '/screenshot.png');
	}

	wp_send_json_success(array(
		'message' => esc_html__('Child theme created successfully!', 'codeweber'),
		'theme_dir' => $theme_dir
	));
}

/**
 * AJAX handler for activating child theme
 */
add_action('wp_ajax_redux_activate_child_theme', 'redux_activate_child_theme_callback');
function redux_activate_child_theme_callback()
{
	// Check nonce
	if (!wp_verify_nonce($_POST['nonce'], 'redux_activate_theme_nonce')) {
		wp_send_json_error(esc_html__('Security check failed', 'codeweber'));
	}

	// Check permissions
	if (!current_user_can('switch_themes')) {
		wp_send_json_error(esc_html__('Insufficient permissions', 'codeweber'));
	}

	$theme_slug = sanitize_text_field($_POST['theme'] ?? '');

	if (empty($theme_slug)) {
		wp_send_json_error(esc_html__('Theme slug is required', 'codeweber'));
	}

	// Проверяем, существует ли тема
	$theme = wp_get_theme($theme_slug);
	if (!$theme->exists()) {
		wp_send_json_error(esc_html__('Theme does not exist', 'codeweber'));
	}

	// Проверяем, является ли тема child theme
	if (!$theme->get('Template')) {
		wp_send_json_error(esc_html__('This is not a child theme', 'codeweber'));
	}

	// Активируем тему
	switch_theme($theme_slug);

	wp_send_json_success(esc_html__('Theme activated successfully!', 'codeweber'));
}
