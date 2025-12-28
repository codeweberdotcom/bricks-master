<?php

/**
 * Header template for horizons theme
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

    <main class="content-wrapper">
        <?php
        // Получаем тип контента и ID
        $post_type = universal_get_post_type();
        $post_id = get_the_ID();
        $header_post_id = '';

        if (class_exists('Redux')) {
            global $opt_name;
            // Убеждаемся, что $opt_name установлена
            if (empty($opt_name)) {
                $opt_name = 'redux_demo';
            }
            // Проверяем, что Redux экземпляр инициализирован
            $redux_instance = Redux_Instances::get_instance($opt_name);
            // #region agent log
            $log_data = json_encode(['location' => 'header.php:28', 'message' => 'Header render start', 'data' => ['opt_name' => $opt_name ?? 'NOT_SET', 'class_exists_Redux' => class_exists('Redux'), 'redux_instance_exists' => $redux_instance !== null, 'post_type' => $post_type, 'post_id' => $post_id], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']);
            $log_file = ABSPATH . '.cursor/debug.log';
            @file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
            // #endregion
            if ($redux_instance !== null) {
                if (is_single() || is_singular($post_type)) {
                    // Проверяем индивидуальные настройки записи
                    $this_header_type = Redux::get_post_meta($opt_name, $post_id, 'this-header-type');
                    if ($this_header_type === '3') {
                        return; // Disable - не выводим header
                    }
                    
                    // Если выбран тип '4' (Base Settings), используем индивидуальные настройки
                    if ($this_header_type === '4') {
                        // Пропускаем выбор кастомного хедера и используем Base Settings
                        $header_post_id = '';
                    } else {
                        $this_header_post_id = Redux::get_post_meta($opt_name, $post_id, 'this-custom-post-header');
                        if (!empty($this_header_post_id)) {
                            $header_post_id = $this_header_post_id;
                        } else {
                            $header_post_id = Redux::get_option($opt_name, 'single_header_select_' . $post_type);
                        }
                    }
                } elseif (is_archive() || is_post_type_archive($post_type)) {
                    $header_post_id = Redux::get_option($opt_name, 'archive_header_select_' . $post_type);
                }
            }
        }

        // Проверяем, не отключен ли header
        if ($header_post_id === 'disable') {
            return; // Не выводим header
        }

        if (!empty($header_post_id) && $header_post_id !== 'default') {
            $header_post = get_post($header_post_id);
            if ($header_post) {
                setup_postdata($header_post);
                the_content(); // Выводим контент записи (с поддержкой шорткодов, HTML и т.д.)
                wp_reset_postdata(); // Важно: сбрасываем глобальные данные
            }
        } else {

            if (class_exists('Redux')) {
                global $opt_name;
                if (empty($opt_name)) {
                    $opt_name = 'redux_demo';
                }
                $redux_instance = Redux_Instances::get_instance($opt_name);
                if ($redux_instance !== null) {
                    // Очищаем глобальные переменные для индивидуальных настроек хедера
                    $GLOBALS['codeweber_use_this_header_settings'] = false;
                    unset($GLOBALS['codeweber_this_header_rounded']);
                    unset($GLOBALS['codeweber_this_header_color_text']);
                    unset($GLOBALS['codeweber_this_header_background']);
                    unset($GLOBALS['codeweber_this_solid_color_header']);
                    unset($GLOBALS['codeweber_this_soft_color_header']);
                    
                    // Проверяем, выбран ли тип '4' (Base Settings) для этой страницы
                    $this_header_type = '';
                    if (is_single() || is_singular($post_type)) {
                        $this_header_type = Redux::get_post_meta($opt_name, $post_id, 'this-header-type');
                    }
                    
                    // Получаем модель хедера
                    $global_header_model = '';
                    if ($this_header_type === '4') {
                        // Если выбран тип '4', используем индивидуальные настройки страницы
                        $global_header_model = Redux::get_post_meta($opt_name, $post_id, 'this-global-header-model');
                        // Получаем индивидуальные настройки и сохраняем в глобальные переменные для использования в шаблонах
                        $rounded_value = Redux::get_post_meta($opt_name, $post_id, 'this-header-rounded');
                        if ($rounded_value !== false && $rounded_value !== '') {
                            $GLOBALS['codeweber_this_header_rounded'] = $rounded_value;
                        }
                        
                        $color_text_value = Redux::get_post_meta($opt_name, $post_id, 'this-header-color-text');
                        if ($color_text_value !== false && $color_text_value !== '') {
                            $GLOBALS['codeweber_this_header_color_text'] = $color_text_value;
                        }
                        
                        $background_value = Redux::get_post_meta($opt_name, $post_id, 'this-header-background');
                        if ($background_value !== false && $background_value !== '') {
                            $GLOBALS['codeweber_this_header_background'] = $background_value;
                        }
                        
                        $solid_color_value = Redux::get_post_meta($opt_name, $post_id, 'this-solid-color-header');
                        if ($solid_color_value !== false && $solid_color_value !== '') {
                            $GLOBALS['codeweber_this_solid_color_header'] = $solid_color_value;
                        }
                        
                        $soft_color_value = Redux::get_post_meta($opt_name, $post_id, 'this-soft-color-header');
                        if ($soft_color_value !== false && $soft_color_value !== '') {
                            $GLOBALS['codeweber_this_soft_color_header'] = $soft_color_value;
                        }
                        
                        $GLOBALS['codeweber_use_this_header_settings'] = true;
                    } else {
                        $global_header_model = Redux::get_option($opt_name, 'global-header-model');
                    }
                    
                    // Если индивидуальная модель не задана, используем глобальную
                    if (empty($global_header_model)) {
                        $global_header_model = Redux::get_option($opt_name, 'global-header-model');
                    }
                } else {
                    $global_header_model = '';
                }
                // #region agent log
                $log_data = json_encode(['location' => 'header.php:60', 'message' => 'Global header model check', 'data' => ['opt_name' => $opt_name ?? 'NOT_SET', 'global_header_model' => $global_header_model, 'header_post_id' => $header_post_id ?? 'EMPTY', 'redux_instance_exists' => $redux_instance !== null], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'B']);
                $log_file = ABSPATH . '.cursor/debug.log';
                @file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
                // #endregion
                switch ($global_header_model) {
                    case '1':
                    case '2':
                        get_template_part('templates/header/header', 'classic');
                        break;
                    case '3':
                        get_template_part('templates/header/header', 'center-logo');
                        break;
                    case '4':
                    case '5':
                        get_template_part('templates/header/header', 'fancy');
                        break;
                    case '6':
                        get_template_part('templates/header/header', 'fancy-center-logo');
                        break;
                    case '7':
                        get_template_part('templates/header/header', 'extended');
                        break;
                    case '8':
                        get_template_part('templates/header/header', 'extended-center-logo');
                        break;
                    default:
                        // Заголовок по умолчанию, если ничего не выбрано
                        get_template_part('templates/header/header');
                }
            } else {
                get_template_part('templates/header/header');
            }
        }
        ?>