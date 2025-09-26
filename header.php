<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    
</head>

<body>

    <main class="content-wrapper">
        <?php 
        // Получаем тип контента и ID
        $post_type = universal_get_post_type();
        $post_id = get_the_ID();

        if (class_exists('Redux')) {
            global $opt_name; 
            if (is_single() || is_singular($post_type)) {
                $this_header_post_id = Redux::get_post_meta($opt_name, $post_id, 'this-custom-post-header');
                if (!empty($this_header_post_id)) {
                    $header_post_id = $this_header_post_id;
                } else {
                    $header_post_id = Redux::get_option($opt_name, 'single_header_select_' . $post_type);
                }
            } elseif (is_archive() || is_post_type_archive($post_type)) {
                $header_post_id = Redux::get_option($opt_name, 'archive_header_select_' . $post_type);
            }
        }

        if (!empty($header_post_id)) {
            $header_post = get_post($header_post_id);
            if ($header_post) {
                setup_postdata($header_post);
                the_content(); // Выводим контент записи (с поддержкой шорткодов, HTML и т.д.)
                wp_reset_postdata(); // Важно: сбрасываем глобальные данные
            }
        } else {

            if (class_exists('Redux')) {
            $global_header_model = Redux::get_option($opt_name, 'global-header-model');
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
        }else{
            get_template_part('templates/header/header');
        }
        }
        ?>