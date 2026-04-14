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
    <?php get_loader(); ?>

    <?php
    $codeweber_page_frame       = (bool) Codeweber_Options::get( 'page-frame', false );
    $codeweber_page_frame_class = 'page-frame';
    if ( $codeweber_page_frame ) {
        $frame_bg   = Codeweber_Options::get( 'page-frame-bg', 'light' );
        $frame_type = Codeweber_Options::get( 'page-frame-bg-type', 'solid' );
        $codeweber_page_frame_class .= $frame_bg ? ' bg-' . ( $frame_type === 'soft' ? 'soft-' : '' ) . esc_attr( $frame_bg ) : '';
    }
    if ( $codeweber_page_frame ) {
        echo '<div class="' . esc_attr( trim( $codeweber_page_frame_class ) ) . '">';
    }
    ?>

    <?php
    $_cw_bg       = function_exists( 'cw_content_wrapper_bg_attrs' ) ? cw_content_wrapper_bg_attrs() : [ 'class' => '', 'data' => '', 'style' => '' ];
    $_cw_bg_class = $_cw_bg['class'] ? ' ' . esc_attr( $_cw_bg['class'] ) : '';
    $_cw_bg_data  = $_cw_bg['data'] ? ' ' . $_cw_bg['data'] : '';
    $_cw_bg_style = ! empty( $_cw_bg['style'] ) ? ' style="' . esc_attr( $_cw_bg['style'] ) . '"' : '';

        // Получаем тип контента и ID
        $post_type          = universal_get_post_type();
        $post_id            = get_the_ID();
        $header_post_id     = '';
        $global_header_type = '';

        if ( Codeweber_Options::is_ready() ) {
            // Получаем тип глобального хедера (Base / Custom)
            $global_header_type = Codeweber_Options::get( 'global-header-type' );

            if ( is_single() || is_singular( $post_type ) ) {
                // Проверяем индивидуальные настройки записи
                $this_header_type = Codeweber_Options::get_post_meta( $post_id, 'this-header-type' );
                if ( $this_header_type === '3' ) {
                    return; // Disable — не выводим header
                }

                if ( $this_header_type === '4' ) {
                    // Пропускаем кастомный хедер — используем Base Settings
                    $header_post_id = '';
                } else {
                    $this_header_post_id = Codeweber_Options::get_post_meta( $post_id, 'this-custom-post-header' );
                    if ( ! empty( $this_header_post_id ) ) {
                        $header_post_id = $this_header_post_id;
                    } else {
                        $header_post_id = Codeweber_Options::get( 'single_header_select_' . $post_type );
                    }
                }
            } elseif ( is_archive() || is_post_type_archive( $post_type ) ) {
                // Для архивов archive_header_select_* учитываем только когда глобальный тип = Base.
                // Если выбран глобальный «Пользовательский» хедер — он перекрывает archive_header_select_*.
                if ( $global_header_type !== '2' ) {
                    $header_post_id = Codeweber_Options::get( 'archive_header_select_' . $post_type );
                }
            }

            // Если хедер для страницы не задан — используем глобальный «Пользовательский Хедер»
            $use_global_custom = true;
            if ( is_single() || is_singular( $post_type ) ) {
                $this_header_type_check = Codeweber_Options::get_post_meta( $post_id, 'this-header-type' );
                if ( $this_header_type_check === '4' ) {
                    $use_global_custom = false; // Запись использует Base Settings
                }
            }

            if ( $use_global_custom && ( empty( $header_post_id ) || $header_post_id === 'default' ) ) {
                if ( $global_header_type === '2' ) {
                    $global_custom_header = Codeweber_Options::get( 'custom-header' );
                    if ( ! empty( $global_custom_header ) ) {
                        $header_post_id = $global_custom_header;
                    }
                }
            }
        }

        // На 404 всегда используем дефолтный header (модель из Redux), без кастомного поста
        if ( is_404() ) {
            $header_post_id = '';
        }

        // Фильтр для дочерней темы: переопределить header_post_id
        $header_post_id = apply_filters(
            'codeweber_header_post_id',
            $header_post_id,
            [
                'post_type' => $post_type,
                'post_id'   => $post_id,
                'is_single' => is_single() || is_singular( $post_type ),
                'is_archive' => is_archive() || is_post_type_archive( $post_type ),
                'is_404'    => is_404(),
            ]
        );

        // Проверяем, не отключён ли header
        if ( $header_post_id === 'disable' ) {
            return; // Не выводим header
        }

        $header_post = null;
        if ( ! empty( $header_post_id ) && $header_post_id !== 'default' ) {
            $header_post = get_post( $header_post_id );
            if ( $header_post ) {
                setup_postdata( $header_post ); // setup_postdata() устанавливает глобали автоматически
                the_content(); // Выводим контент записи (с поддержкой шорткодов, HTML и т.д.)
                wp_reset_postdata(); // Сбрасываем глобальные данные
            } else {
                // Пост хедера не найден (удалён/неверный ID) — показываем дефолтный header
                $header_post_id = '';
            }
        }

        if ( empty( $header_post_id ) || $header_post_id === 'default' ) {

            if ( Codeweber_Options::is_ready() ) {
                // Очищаем глобальные переменные для индивидуальных настроек хедера
                $GLOBALS['codeweber_use_this_header_settings'] = false;
                unset( $GLOBALS['codeweber_this_header_rounded'] );
                unset( $GLOBALS['codeweber_this_header_color_text'] );
                unset( $GLOBALS['codeweber_this_header_background'] );
                unset( $GLOBALS['codeweber_this_solid_color_header'] );
                unset( $GLOBALS['codeweber_this_soft_color_header'] );

                // Проверяем, выбран ли тип '4' (Base Settings) для этой страницы
                $this_header_type = '';
                if ( is_single() || is_singular( $post_type ) ) {
                    $this_header_type = Codeweber_Options::get_post_meta( $post_id, 'this-header-type' );
                }

                // Получаем модель хедера
                $global_header_model = '';
                if ( $this_header_type === '4' ) {
                    // Индивидуальные настройки страницы
                    $global_header_model = Codeweber_Options::get_post_meta( $post_id, 'this-global-header-model' );

                    $rounded_value = Codeweber_Options::get_post_meta( $post_id, 'this-header-rounded' );
                    if ( $rounded_value !== false && $rounded_value !== '' ) {
                        $GLOBALS['codeweber_this_header_rounded'] = $rounded_value;
                    }

                    $color_text_value = Codeweber_Options::get_post_meta( $post_id, 'this-header-color-text' );
                    if ( $color_text_value !== false && $color_text_value !== '' ) {
                        $GLOBALS['codeweber_this_header_color_text'] = $color_text_value;
                    }

                    $background_value = Codeweber_Options::get_post_meta( $post_id, 'this-header-background' );
                    if ( $background_value !== false && $background_value !== '' ) {
                        $GLOBALS['codeweber_this_header_background'] = $background_value;
                    }

                    $solid_color_value = Codeweber_Options::get_post_meta( $post_id, 'this-solid-color-header' );
                    if ( $solid_color_value !== false && $solid_color_value !== '' ) {
                        $GLOBALS['codeweber_this_solid_color_header'] = $solid_color_value;
                    }

                    $soft_color_value = Codeweber_Options::get_post_meta( $post_id, 'this-soft-color-header' );
                    if ( $soft_color_value !== false && $soft_color_value !== '' ) {
                        $GLOBALS['codeweber_this_soft_color_header'] = $soft_color_value;
                    }

                    $GLOBALS['codeweber_use_this_header_settings'] = true;
                } else {
                    $global_header_model = Codeweber_Options::get( 'global-header-model' );
                }

                // Если индивидуальная модель не задана — используем глобальную
                if ( empty( $global_header_model ) ) {
                    $global_header_model = Codeweber_Options::get( 'global-header-model' );
                }

                switch ( $global_header_model ) {
                    case '1':
                    case '2':
                        get_template_part( 'templates/header/header', 'classic' );
                        break;
                    case '3':
                        get_template_part( 'templates/header/header', 'center-logo' );
                        break;
                    case '4':
                    case '5':
                        get_template_part( 'templates/header/header', 'fancy' );
                        break;
                    case '6':
                        get_template_part( 'templates/header/header', 'fancy-center-logo' );
                        break;
                    case '7':
                        get_template_part( 'templates/header/header', 'extended' );
                        break;
                    case '8':
                        get_template_part( 'templates/header/header', 'extended-center-logo' );
                        break;
                    default:
                        // Заголовок по умолчанию, если ничего не выбрано
                        get_template_part( 'templates/header/header' );
                }
            } else {
                get_template_part( 'templates/header/header' );
            }
        }
        ?>
    <main class="content-wrapper<?php echo $_cw_bg_class; ?>"<?php echo $_cw_bg_data; ?><?php echo $_cw_bg_style; ?>>
