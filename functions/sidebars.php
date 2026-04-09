<?php

/**
 * Регистрация сайдбаров в теме CodeWeber
 */

// Проверка наличия функции codeweber_sidebars
if (!function_exists('codeweber_sidebars')) {
    function codeweber_sidebars($sidebar_name, $sidebar_id, $sidebar_description, $title_tag = 'h3', $title_class = 'mb-4')
    {
        register_sidebar([
            'name'          => esc_html__($sidebar_name, 'codeweber'),
            'id'            => $sidebar_id,
            'description'   => esc_html__($sidebar_description, 'codeweber'),
            'before_widget' => '<div class="widget %2$s clearfix">',
            'after_widget'  => '</div>',
            'before_title'  => "<{$title_tag} class=\"{$title_class}\">",
            'after_title'   => "</{$title_tag}>",
        ]);
    }
}

/**
 * Регистрация основного сайдбара
 */
function codeweber_register_main_sidebar()
{
    codeweber_sidebars(
        __('Main Sidebar', 'codeweber'),
        'sidebar-main',
        __('Main Sidebar', 'codeweber'),
        'h3',
        'custom-title-class'
    );
}
add_action('widgets_init', 'codeweber_register_main_sidebar');

/**
 * Регистрация сайдбара для WooCommerce, если плагин активен
 */
function codeweber_register_woo_sidebar()
{
    if (class_exists('WooCommerce')) {
        codeweber_sidebars(
            __('Woo Sidebar', 'codeweber'),
            'sidebar-woo',
            __('Woo Sidebar', 'codeweber'),
            'h3',
            'custom-title-class'
        );
    }
}
add_action('widgets_init', 'codeweber_register_woo_sidebar');

/**
 * Регистрация сайдбара в правой части заголовка
 */
function theme_register_header_widget()
{
    register_sidebar([
        'name'          => __('Header Right', 'codeweber'),
        'id'            => 'header-right',
        'description'   => __('Widget area on the right side of the Header', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Header Right 1', 'codeweber'),
        'id'            => 'header-right-1',
        'description'   => __('Widget area on the right side of the Header', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Mobile Menu Footer', 'codeweber'),
        'id'            => 'mobile-menu-footer',
        'description'   => __('Widget area on the right side of the Header', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Header Widget 1', 'codeweber'),
        'id'            => 'header-widget-1',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Header Widget 2', 'codeweber'),
        'id'            => 'header-widget-2',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Header Widget 3', 'codeweber'),
        'id'            => 'header-widget-3',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

}
add_action('widgets_init', 'theme_register_header_widget');

/**
 * Регистрация областей виджетов для оффканваса (мобильное меню / панель «Инфо»)
 * Используются в блоке Navbar / Header Widgets при включении «Widget 1/2/3» в списке элементов оффканваса.
 */
function theme_register_offcanvas_widgets()
{
    register_sidebar([
        'name'          => __('Widget Offcanvas 1', 'codeweber'),
        'id'            => 'widget-offcanvas-1',
        'description'   => __('Widget area in the offcanvas panel (mobile menu / info panel).', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Widget Offcanvas 2', 'codeweber'),
        'id'            => 'widget-offcanvas-2',
        'description'   => __('Widget area in the offcanvas panel (mobile menu / info panel).', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);

    register_sidebar([
        'name'          => __('Widget Offcanvas 3', 'codeweber'),
        'id'            => 'widget-offcanvas-3',
        'description'   => __('Widget area in the offcanvas panel (mobile menu / info panel).', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
}
add_action('widgets_init', 'theme_register_offcanvas_widgets');

/**
 * Регистрация областей виджетов для футера
 */
function codeweber_register_footer_widgets()
{
    register_sidebar([
        'name'          => __('Footer 1', 'codeweber'),
        'id'            => 'footer-1',
        'description'   => __('Widget area for Footer 1', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title mb-3">',
        'after_title'   => '</h4>',
    ]);

    register_sidebar([
        'name'          => __('Footer 2', 'codeweber'),
        'id'            => 'footer-2',
        'description'   => __('Widget area for Footer 2', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title mb-3">',
        'after_title'   => '</h4>',
    ]);

    register_sidebar([
        'name'          => __('Footer 3', 'codeweber'),
        'id'            => 'footer-3',
        'description'   => __('Widget area for Footer 3', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title mb-3">',
        'after_title'   => '</h4>',
    ]);

    register_sidebar([
        'name'          => __('Footer 4', 'codeweber'),
        'id'            => 'footer-4',
        'description'   => __('Widget area for Footer 4', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title mb-3">',
        'after_title'   => '</h4>',
    ]);

    register_sidebar([
        'name'          => __('Bottom Footer', 'codeweber'),
        'id'            => 'bottom-footer',
        'description'   => __('Widget area for Bottom Footer', 'codeweber'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title mb-3">',
        'after_title'   => '</h4>',
    ]);
}
add_action('widgets_init', 'codeweber_register_footer_widgets');

/**
 * Виджет сайдбара для Legal
 * Можно отключить из дочерней темы: remove_action('codeweber_before_sidebar', 'codeweber_sidebar_widget_legal');
 */
function codeweber_sidebar_widget_legal($sidebar_id) {
    if ($sidebar_id !== 'legal') {
        return;
    }

    if (!post_type_exists('legal')) {
        return;
    }

    if (is_active_sidebar('legal')) {
        return;
    }

    $nav = codeweber_nav( 'cpt', 'legal', [ 'list_type' => '4', 'theme' => 'light' ] );
    if ( $nav ) {
        echo '<div class="widget">' . $nav . '</div>';
    }
}
add_action('codeweber_before_sidebar', 'codeweber_sidebar_widget_legal');

/**
 * Виджет сайдбара для Vacancies
 * Можно отключить из дочерней темы: remove_action('codeweber_before_sidebar', 'codeweber_sidebar_widget_vacancies');
 */
function codeweber_sidebar_widget_vacancies($sidebar_id) {
    if ($sidebar_id !== 'vacancies') {
        return;
    }

    if (!post_type_exists('vacancies')) {
        return;
    }

    if (!is_singular('vacancies')) {
        return;
    }

    if (is_active_sidebar('vacancies')) {
        return;
    }

        $vacancy_data = get_vacancy_data_array();

        $type_term = !empty($vacancy_data['vacancy_types']) && !is_wp_error($vacancy_data['vacancy_types']) ? $vacancy_data['vacancy_types'][0] : null;
        $schedule_term = !empty($vacancy_data['vacancy_schedules']) && !is_wp_error($vacancy_data['vacancy_schedules']) ? $vacancy_data['vacancy_schedules'][0] : null;

        $user_id = get_the_author_meta('ID');
        
        // Проверяем оба возможных ключа для аватара
        $avatar_id = get_user_meta($user_id, 'avatar_id', true);
        if (empty($avatar_id)) {
            $avatar_id = get_user_meta($user_id, 'custom_avatar_id', true);
        }

        $job_title = get_user_meta($user_id, 'user_position', true);
        if (empty($job_title)) {
            $job_title = __('Writer', 'codeweber');
        }
        
        // Получаем стиль кнопок из Redux
        $button_style = class_exists('Codeweber_Options') ? Codeweber_Options::style('button', '') : '';
        $card_radius = class_exists('Codeweber_Options') ? Codeweber_Options::style('card-radius') : '';
        $widget_h = class_exists('Codeweber_Options') ? Codeweber_Options::get('widget_heading_size', 'h3') : 'h3';
        $sidebar_disable_image = !empty($vacancy_data['sidebar_disable_image']);
        $sidebar_hide_author   = !empty($vacancy_data['sidebar_hide_author']);
?>
        <div class="widget">
            <div class="card text-dark<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <?php
                if (!$sidebar_disable_image) :
                    $thumbnail_id = get_post_thumbnail_id();
                    $image_url = '';
                    if ($thumbnail_id) {
                        $image_url = wp_get_attachment_image_url($thumbnail_id, 'codeweber_vacancy_383-250');
                    }
                    if (empty($image_url)) {
                        $image_url = get_template_directory_uri() . '/dist/assets/img/photos/about6.jpg';
                    }
                ?>
                <figure<?php echo $card_radius ? ' class="' . esc_attr($card_radius) . '"' : ''; ?>>
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="img-fluid">
                </figure>
                <?php endif; ?>

                <div class="card-body">
                    <div class="mb-6">
                        <?php
                        $vac_types  = $vacancy_data['vacancy_types'] ?? [];
                        $vac_status = $vacancy_data['status'] ?? '';
                        $vac_status_map = [
                            'open'   => 'badge bg-soft-green text-green rounded-pill',
                            'closed' => 'badge bg-red text-white rounded-pill',
                        ];
                        $vac_status_labels = [
                            'open'   => __( 'Open', 'codeweber' ),
                            'closed' => __( 'Closed', 'codeweber' ),
                        ];
                        $has_badges = ( $vac_types && ! is_wp_error( $vac_types ) ) || ! empty( $vac_status );
                        if ( $has_badges ) : ?>
                            <div class="mb-3">
                                <?php if ( $vac_types && ! is_wp_error( $vac_types ) ) : ?>
                                    <?php foreach ( $vac_types as $term ) : ?>
                                        <a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="badge bg-soft-primary text-primary rounded-pill me-1">
                                            <?php echo esc_html( $term->name ); ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if ( ! empty( $vac_status ) && isset( $vac_status_map[ $vac_status ] ) ) : ?>
                                    <span class="<?php echo esc_attr( $vac_status_map[ $vac_status ] ); ?> me-1">
                                        <?php echo esc_html( $vac_status_labels[ $vac_status ] ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <h3 class="mb-4 <?php echo esc_attr($widget_h); ?>"><?php esc_html_e('Details', 'codeweber'); ?></h3>

                        <?php if (!empty($vacancy_data['location'])) : ?>
                            <p class="mb-1 d-flex align-items-baseline">
                                <i class="uil uil-map-marker-alt text-primary me-2"></i>
                                <span><?php echo esc_html($vacancy_data['location']); ?></span>
                            </p>
                        <?php endif; ?>


                        <?php if ($schedule_term) : ?>
                            <p class="mb-1 d-flex align-items-baseline">
                                <i class="uil uil-calendar-alt text-primary me-2"></i>
                                <span><?php echo esc_html($schedule_term->name); ?></span>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($vacancy_data['experience'])) : ?>
                            <p class="mb-1 d-flex align-items-baseline">
                                <i class="uil uil-clock text-primary me-2"></i>
                                <span><?php echo esc_html($vacancy_data['experience']); ?></span>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($vacancy_data['education'])) : ?>
                            <p class="mb-1 d-flex align-items-baseline">
                                <i class="uil uil-graduation-cap text-primary me-2"></i>
                                <span><?php echo esc_html($vacancy_data['education']); ?></span>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($vacancy_data['salary'])) : ?>
                            <p class="mb-1 d-flex align-items-baseline">
                                <i class="uil uil-money-stack text-primary me-2"></i>
                                <span><?php echo esc_html($vacancy_data['salary']); ?></span>
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if (!$sidebar_hide_author) : ?>
                    <div class="mb-6">
                        <div class="author-info d-flex align-items-center">
                            <div class="d-flex align-items-center">
                                <?php if (!empty($avatar_id)) : ?>
                                    <?php $avatar_src = wp_get_attachment_image_src($avatar_id, 'thumbnail'); ?>
                                    <figure class="user-avatar me-3">
                                        <img class="rounded-circle" alt="<?php the_author_meta('display_name'); ?>" src="<?php echo esc_url($avatar_src[0]); ?>">
                                    </figure>
                                <?php else : ?>
                                    <figure class="user-avatar me-3">
                                        <?php echo get_avatar(get_the_author_meta('user_email'), 96, '', '', ['class' => 'rounded-circle']); ?>
                                    </figure>
                                <?php endif; ?>

                                <div>
                                    <h6 class="mb-0">
                                        <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>" class="link-dark">
                                            <?php
                                            $first = get_the_author_meta('first_name');
                                            $last  = get_the_author_meta('last_name');
                                            echo esc_html(trim("$first $last") ?: get_the_author_meta('display_name'));
                                            ?>
                                        </a>
                                    </h6>
                                    <span class="post-meta fs-15"><?php echo esc_html($job_title); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div data-group="page-title-buttons">
                        <?php if (!empty($vacancy_data['pdf_url'])) : ?>
                            <span data-group="page-title-buttons">
                                <a href="javascript:void(0)" class="btn btn-primary btn-icon btn-icon-start has-ripple w-100 mb-2<?php echo esc_attr($button_style); ?>" data-bs-toggle="download" data-value="vac-<?php echo esc_attr(get_the_ID()); ?>">
                                    <i class="uil uil-file-download"></i>
                                    <?php _e('Download document', 'codeweber'); ?>
                                </a>
                            </span>
                        <?php endif; ?>

                        <?php 
                        $cf7_form_id = isset($vacancy_data['cf7_form_id']) ? $vacancy_data['cf7_form_id'] : '';
                        $cf7_form_id = !empty($cf7_form_id) ? intval($cf7_form_id) : 0;
                        
                        if ($cf7_form_id > 0) : ?>
                            <span data-group="page-title-buttons">
                                <a href="javascript:void(0)" class="btn has-ripple w-100 btn-outline-primary mb-2<?php echo esc_attr($button_style); ?>" data-value="cf7-<?php echo esc_attr($cf7_form_id); ?>" data-bs-toggle="modal" data-bs-target="#modal">
                                    <?php _e('Submit a request', 'codeweber'); ?>
                                </a>
                            </span>
                        <?php endif; ?>

                        <?php 
                        $codeweber_form_id = isset($vacancy_data['codeweber_form_id']) ? $vacancy_data['codeweber_form_id'] : '';
                        $codeweber_form_id = !empty($codeweber_form_id) ? intval($codeweber_form_id) : 0;
                        
                        if ($codeweber_form_id > 0) : ?>
                            <span data-group="page-title-buttons">
                                <a href="javascript:void(0)" class="btn has-ripple w-100 btn-outline-primary mb-2<?php echo esc_attr($button_style); ?>" data-value="cf-<?php echo esc_attr($codeweber_form_id); ?>" data-bs-toggle="modal" data-bs-target="#modal">
                                    <?php _e('Submit a request', 'codeweber'); ?>
                                </a>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <!--/.card-body -->
            </div>
            <!--/.card -->
        </div>
        <!--/.widget -->

        <?php
        // Вывод карты через общий модуль Яндекс.Карт (с Loader и единым JS)
        $show_map = isset($vacancy_data['show_map']) ? $vacancy_data['show_map'] : '';
        $latitude = isset($vacancy_data['latitude']) ? $vacancy_data['latitude'] : '';
        $longitude = isset($vacancy_data['longitude']) ? $vacancy_data['longitude'] : '';
        $yandex_address = isset($vacancy_data['yandex_address']) ? $vacancy_data['yandex_address'] : '';
        
        if ($show_map === '1' && !empty($latitude) && !empty($longitude) && class_exists('Codeweber_Yandex_Maps')) :
            $yandex_maps = Codeweber_Yandex_Maps::get_instance();
            if ($yandex_maps->has_api_key()) :
                $zoom = isset($vacancy_data['zoom']) && !empty($vacancy_data['zoom']) ? absint($vacancy_data['zoom']) : 15;
                $lat = floatval($latitude);
                $lon = floatval($longitude);
                $args = array(
                    'map_id'           => 'vacancy-sidebar-map',
                    'center'           => array($lat, $lon),
                    'zoom'             => $zoom,
                    'height'           => 250,
                    'width'            => '100%',
                    'controls'         => array('zoomControl'),
                    'enable_scroll_zoom' => false,
                    'show_sidebar'     => false,
                    'show_route'       => false,
                    'clusterer'        => false,
                    'marker_auto_open_balloon' => false,
                );
                $markers = array(
                    array(
                        'latitude'  => $lat,
                        'longitude' => $lon,
                        'hintContent' => !empty($yandex_address) ? $yandex_address : '',
                    ),
                );
                ?>
            <div class="widget">
                <h3 class="mb-3 <?php echo esc_attr($widget_h); ?>"><?php esc_html_e('On the map', 'codeweber'); ?></h3>
                <div class="card<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                    <?php echo $yandex_maps->render_map($args, $markers); ?>
                </div>
            </div>
            <?php
            endif;
        endif;
        ?>
        
        <?php
}
add_action('codeweber_before_sidebar', 'codeweber_sidebar_widget_vacancies');

/**
 * Виджет сайдбара для FAQ
 * Можно отключить из дочерней темы: remove_action('codeweber_before_sidebar', 'codeweber_sidebar_widget_faq');
 */
function codeweber_sidebar_widget_faq($sidebar_id) {
    if ($sidebar_id !== 'faq') {
        return;
    }

    if (!post_type_exists('faq')) {
        return;
    }

    if (is_active_sidebar('faq')) {
        return;
    }

        // Получаем текущий якорь из URL (hash)
        $current_anchor = '';
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '#') !== false) {
            $current_anchor = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '#') + 1);
        }

        // Получаем все категории FAQ
        $faq_categories = get_terms([
            'taxonomy'   => 'faq_categories',
            'hide_empty' => true,
        ]);

        $card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
        $widget_h    = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::get( 'widget_heading_size', 'h3' ) : 'h3';

        if (!empty($faq_categories) && !is_wp_error($faq_categories)) {
            echo '<div class="widget">';
            echo '<div class="card' . ( $card_radius ? ' ' . esc_attr( $card_radius ) : '' ) . '">';
            echo '<div class="card-body">';
            echo '<h3 class="mb-4 ' . esc_attr( $widget_h ) . '">' . esc_html__( 'Contents', 'codeweber' ) . '</h3>';
            echo '<nav id="sidebar-nav"><ul class="list-unstyled text-reset">';

            $index = 1;

            // Получаем FAQ записи без категорий для проверки наличия секции "Other Questions"
            $uncategorized_faqs = get_posts([
                'post_type'      => 'faq',
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
                'tax_query'      => [
                    [
                        'taxonomy' => 'faq_categories',
                        'operator' => 'NOT EXISTS',
                    ],
                ],
            ]);

            foreach ($faq_categories as $category) {
                $category_anchor = sanitize_title($category->name);
                $anchor_url = '#' . $category_anchor;
                $active_class = ($current_anchor === $category_anchor) ? ' active' : '';

                echo '<li><a class="nav-link scroll' . $active_class . '" href="' . esc_attr($anchor_url) . '">' . $index . '. ' . esc_html($category->name) . '</a></li>';
                $index++;
            }

            // Добавляем пункт для некатегоризированных FAQ, если они есть
            if (!empty($uncategorized_faqs)) {
                $uncategorized_anchor = 'faq-uncategorized';
                $active_class = ($current_anchor === $uncategorized_anchor) ? ' active' : '';
                echo '<li><a class="nav-link scroll' . $active_class . '" href="#' . esc_attr($uncategorized_anchor) . '">' . $index . '. ' . esc_html__('Other Questions', 'codeweber') . '</a></li>';
            }

            echo '</ul></nav>';

            // FAQ Question Form — modal button inside card-body, after nav
            $faq_settings = get_option( 'codeweber_faq_settings', [] );
            $form_mode    = $faq_settings['form_mode'] ?? 'inline';
            $button_style = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button', '' ) : '';

            if ( $form_mode === 'modal' ) {
                echo '<hr class="my-4">';
                echo '<a href="javascript:void(0)" class="btn btn-primary has-ripple w-100' . esc_attr( $button_style ) . '"'
                    . ' data-bs-toggle="modal" data-bs-target="#modal" data-value="faq-form">';
                echo esc_html__( 'Ask a Question', 'codeweber' );
                echo '</a>';
            }

            echo '</div></div></div>';
        } else {
            // Если нет категорий, выводим ссылку на секцию "faq-all"
            echo '<div class="widget">';
            echo '<div class="card' . ( $card_radius ? ' ' . esc_attr( $card_radius ) : '' ) . '">';
            echo '<div class="card-body">';
            echo '<h3 class="mb-4 ' . esc_attr( $widget_h ) . '">' . esc_html__( 'Contents', 'codeweber' ) . '</h3>';
            echo '<nav id="sidebar-nav"><ul class="list-unstyled text-reset">';
            echo '<li><a class="nav-link scroll active" href="#faq-all">' . esc_html__('All FAQs', 'codeweber') . '</a></li>';
            echo '</ul></nav>';

            // FAQ Question Form — modal button inside card-body, after nav
            $faq_settings = get_option( 'codeweber_faq_settings', [] );
            $form_mode    = $faq_settings['form_mode'] ?? 'inline';
            $button_style = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button', '' ) : '';

            if ( $form_mode === 'modal' ) {
                echo '<hr class="my-4">';
                echo '<a href="javascript:void(0)" class="btn btn-primary has-ripple w-100' . esc_attr( $button_style ) . '"'
                    . ' data-bs-toggle="modal" data-bs-target="#modal" data-value="faq-form">';
                echo esc_html__( 'Ask a Question', 'codeweber' );
                echo '</a>';
            }

            echo '</div></div></div>';
        }

        // FAQ Question Form — inline mode (separate card below nav)
        $faq_settings = $faq_settings ?? get_option( 'codeweber_faq_settings', [] );
        $form_mode    = $form_mode ?? ( $faq_settings['form_mode'] ?? 'inline' );

        if ( $form_mode === 'inline' && class_exists( 'CodeweberFormsDefaultForms' ) ) {
            $forms     = new CodeweberFormsDefaultForms();
            $form_html = $forms->get_default_faq_form_html();
            if ( $form_html ) {
                echo '<div class="widget">';
                echo '<div class="card mt-4' . ( $card_radius ? ' ' . esc_attr( $card_radius ) : '' ) . '">';
                echo '<div class="card-body">';
                echo '<h3 class="mb-4 ' . esc_attr( $widget_h ) . '">' . esc_html__( 'Ask a Question', 'codeweber' ) . '</h3>';
                echo $form_html;
                echo '</div></div></div>';
            }
        }
}
add_action('codeweber_before_sidebar', 'codeweber_sidebar_widget_faq');


/**
 * Виджет сайдбара для Events
 * Можно отключить из дочерней темы: remove_action('codeweber_before_sidebar', 'codeweber_sidebar_widget_events');
 */
function codeweber_sidebar_widget_events($sidebar_id) {
    if ($sidebar_id !== 'events') {
        return;
    }

    if (!post_type_exists('events')) {
        return;
    }

    if (!is_singular('events')) {
        return;
    }

    if (is_active_sidebar('events')) {
        return;
    }

    $event_id          = get_the_ID();
    $date_start        = get_post_meta( $event_id, '_event_date_start', true );
    $date_end          = get_post_meta( $event_id, '_event_date_end', true );
    $location          = get_post_meta( $event_id, '_event_location', true );
    $address           = get_post_meta( $event_id, '_event_address', true );
    $organizer         = get_post_meta( $event_id, '_event_organizer', true );
    $price             = get_post_meta( $event_id, '_event_price', true );
    $external_reg_url  = get_post_meta( $event_id, '_event_registration_url', true );
    $max_participants  = (int) get_post_meta( $event_id, '_event_max_participants', true );
    $reg_open          = get_post_meta( $event_id, '_event_registration_open', true );
    $reg_close         = get_post_meta( $event_id, '_event_registration_close', true );
    $reg_status        = codeweber_events_get_registration_status( $event_id );
    $formats           = get_the_terms( $event_id, 'event_format' );
    $categories        = get_the_terms( $event_id, 'event_category' );
    $settings          = get_option( 'codeweber_events_settings', [] );
    $show_seats_taken  = ( $settings['show_seats_taken'] ?? '1' ) === '1';
    $show_seats_left   = ( $settings['show_seats_left'] ?? '1' ) === '1';
    $show_seats_bar    = ( $settings['show_seats_progress'] ?? '1' ) === '1';

    $registered_count       = codeweber_events_get_registration_count( $event_id );
    $seats_left             = $max_participants > 0 ? max( 0, $max_participants - $registered_count ) : null;
    $seats_pct              = ( $max_participants > 0 ) ? min( 100, round( ( $registered_count / $max_participants ) * 100 ) ) : 0;
    $sidebar_hide_author    = get_post_meta( $event_id, '_event_sidebar_hide_author', true );
    $sidebar_disable_image  = get_post_meta( $event_id, '_event_sidebar_disable_image', true );
    $hide_seats_counter     = get_post_meta( $event_id, '_event_hide_seats_counter', true );
    $event_show_map         = get_post_meta( $event_id, '_event_show_map', true );
    $event_latitude         = get_post_meta( $event_id, '_event_latitude', true );
    $event_longitude        = get_post_meta( $event_id, '_event_longitude', true );
    $event_zoom             = get_post_meta( $event_id, '_event_zoom', true );
    $event_yandex_address   = get_post_meta( $event_id, '_event_yandex_address', true );
    $card_radius      = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : '';
    $widget_h         = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::get( 'widget_heading_size', 'h3' ) : 'h3';
    $button_style     = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button', '' ) : '';
    $form_radius      = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'form-radius' ) : '';
    $phone_mask       = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::get( 'opt_phone_mask', '' ) : '';
    $reg_form_title   = get_post_meta( $event_id, '_event_reg_form_title', true );
    $reg_button_label = get_post_meta( $event_id, '_event_reg_button_label', true );

    // Countdown timer
    $countdown_until = null;
    $countdown_label = '';
    $countdown_date  = '';
    $now = current_time( 'timestamp' );
    if ( $reg_status['status'] === 'open' && $reg_close ) {
        $ts = strtotime( $reg_close );
        if ( $ts && $ts > $now ) {
            $countdown_until = $ts;
            $countdown_label = __( 'Registration closes:', 'codeweber' );
            $countdown_date  = date_i18n( get_option( 'date_format' ), $ts );
        }
    } elseif ( $reg_status['status'] === 'not_open_yet' && $reg_open ) {
        $ts = strtotime( $reg_open );
        if ( $ts && $ts > $now ) {
            $countdown_until = $ts;
            $countdown_label = __( 'Registration opens:', 'codeweber' );
            $countdown_date  = date_i18n( get_option( 'date_format' ), $ts );
        }
    }

    // Seats counter flags
    $show_bar       = $max_participants > 0 && $show_seats_bar;
    $show_left      = $max_participants > 0 && $show_seats_left && $seats_left !== null;
    $show_taken     = $show_seats_taken && $registered_count > 0;
    $show_any_seats = $show_bar || $show_left || $show_taken;
    ?>
    <div class="widget">
    <div class="card text-dark<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">

        <?php if ( ! $sidebar_disable_image && has_post_thumbnail() ) :
            $sidebar_img = get_the_post_thumbnail_url( $event_id, 'codeweber_event_383-250' );
            if ( $sidebar_img ) : ?>
            <figure<?php echo $card_radius ? ' class="' . esc_attr( $card_radius ) . '"' : ''; ?>>
                <img src="<?php echo esc_url( $sidebar_img ); ?>"
                    alt="<?php echo esc_attr( get_the_title() ); ?>" class="img-fluid">
            </figure>
        <?php endif; endif; ?>

        <div class="card-body">

            <?php if ( ( $categories && ! is_wp_error( $categories ) ) || ( $formats && ! is_wp_error( $formats ) ) ) : ?>
                <div class="mb-3">
                    <?php if ( $categories && ! is_wp_error( $categories ) ) : ?>
                        <?php foreach ( $categories as $cat ) : ?>
                            <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="badge bg-soft-primary text-primary rounded-pill me-1">
                                <?php echo esc_html( $cat->name ); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if ( $formats && ! is_wp_error( $formats ) ) : ?>
                        <?php foreach ( $formats as $fmt ) : ?>
                            <span class="badge bg-soft-ash text-navy me-1"><?php echo esc_html( $fmt->name ); ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="mb-6">
                <h3 class="mb-2 <?php echo esc_attr( $widget_h ); ?>"><?php esc_html_e( 'Event Details', 'codeweber' ); ?></h3>

                <?php if ( $countdown_until ) : ?>
                <p class="small text-muted mb-2">
                    <?php echo esc_html( $countdown_label ); ?>
                    <span class="fw-semibold text-reset"><?php echo esc_html( $countdown_date ); ?></span>
                </p>
                <div class="event-countdown d-flex align-items-start gap-1 mb-4"
                    data-countdown="<?php echo esc_attr( $countdown_until ); ?>">
                    <div class="event-countdown-unit">
                        <div class="fw-bold lh-1 event-countdown-days">0</div>
                        <div class="text-muted" style="font-size:0.6875rem;"><?php esc_html_e( 'days', 'codeweber' ); ?></div>
                    </div>
                    <div class="fw-bold lh-1 px-1">:</div>
                    <div class="event-countdown-unit">
                        <div class="fw-bold lh-1 event-countdown-hours">00</div>
                        <div class="text-muted" style="font-size:0.6875rem;"><?php esc_html_e( 'hrs', 'codeweber' ); ?></div>
                    </div>
                    <div class="fw-bold lh-1 px-1">:</div>
                    <div class="event-countdown-unit">
                        <div class="fw-bold lh-1 event-countdown-mins">00</div>
                        <div class="text-muted" style="font-size:0.6875rem;"><?php esc_html_e( 'min', 'codeweber' ); ?></div>
                    </div>
                    <div class="fw-bold lh-1 px-1">:</div>
                    <div class="event-countdown-unit">
                        <div class="fw-bold lh-1 event-countdown-secs">00</div>
                        <div class="text-muted" style="font-size:0.6875rem;"><?php esc_html_e( 'sec', 'codeweber' ); ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( $date_start ) : ?>
                    <p class="mb-1 d-flex align-items-baseline">
                        <i class="uil uil-calendar-alt text-primary me-2 flex-shrink-0"></i>
                        <span>
                            <span class="text-muted me-1"><?php esc_html_e( 'Start:', 'codeweber' ); ?></span>
                            <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date_start ) ) ); ?>
                        </span>
                    </p>
                <?php endif; ?>

                <?php if ( $date_end ) : ?>
                    <p class="mb-1 d-flex align-items-baseline">
                        <i class="uil uil-calendar-alt text-primary me-2 flex-shrink-0"></i>
                        <span>
                            <span class="text-muted me-1"><?php esc_html_e( 'End:', 'codeweber' ); ?></span>
                            <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date_end ) ) ); ?>
                        </span>
                    </p>
                <?php endif; ?>

                <?php if ( $location ) : ?>
                    <p class="mb-1 d-flex align-items-baseline">
                        <i class="uil uil-map-marker-alt text-primary me-2 flex-shrink-0"></i>
                        <span><?php echo esc_html( $address ? $location . ', ' . $address : $location ); ?></span>
                    </p>
                <?php endif; ?>

                <?php if ( $organizer ) : ?>
                    <p class="mb-1 d-flex align-items-center">
                        <i class="uil uil-user text-primary me-2 flex-shrink-0"></i>
                        <span><?php echo esc_html( $organizer ); ?></span>
                    </p>
                <?php endif; ?>

                <?php if ( $price ) : ?>
                    <p class="mb-1 d-flex align-items-center">
                        <i class="uil uil-money-stack text-primary me-2 flex-shrink-0"></i>
                        <span><?php echo esc_html( $price ); ?></span>
                    </p>
                <?php endif; ?>

                <?php if ( $date_start && ! get_post_meta( $event_id, '_event_hide_add_to_calendar', true ) ) :
                    $tz       = wp_timezone();
                    $_ics_fmt = static function ( string $d ) use ( $tz ): string {
                        return ( new DateTime( $d, $tz ) )->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' );
                    };
                    $gc_start = $_ics_fmt( $date_start );
                    $gc_end   = $_ics_fmt( $date_end ?: $date_start );
                    $gc_loc   = trim( implode( ', ', array_filter( [ $location, $address ] ) ) );
                    $gc_url   = 'https://calendar.google.com/calendar/render?' . http_build_query( [
                        'action'   => 'TEMPLATE',
                        'text'     => get_the_title(),
                        'dates'    => $gc_start . '/' . $gc_end,
                        'details'  => wp_strip_all_tags( get_the_excerpt() ),
                        'location' => $gc_loc,
                    ] );
                    $ics_url  = add_query_arg( 'ics', '1', get_permalink() );
                ?>

                <div class="mt-3 pt-3 border-top">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?php echo esc_url( $ics_url ); ?>"
                            class="btn btn-apple btn-xs rounded-pill has-ripple">
                            <i class="uil uil-apple me-1"></i><?php esc_html_e( 'Apple Calendar', 'codeweber' ); ?>
                        </a>
                        <a href="<?php echo esc_url( $gc_url ); ?>" target="_blank" rel="noopener noreferrer"
                            class="btn btn-google btn-xs rounded-pill has-ripple">
                            <i class="uil uil-google me-1"></i><?php esc_html_e( 'Google Calendar', 'codeweber' ); ?>
                        </a>
                    </div>
                </div>

                <?php endif; ?>

                </div>

            <?php
            if ( ! $sidebar_hide_author ) :
                $evt_user_id  = get_the_author_meta( 'ID' );
                $evt_avatar   = get_user_meta( $evt_user_id, 'avatar_id', true );
                if ( empty( $evt_avatar ) ) $evt_avatar = get_user_meta( $evt_user_id, 'custom_avatar_id', true );
                $evt_job      = get_user_meta( $evt_user_id, 'user_position', true ) ?: __( 'Author', 'codeweber' );
            ?>
            <hr class="my-4">
            <div class="author-info d-flex align-items-center">
                <div class="d-flex align-items-center">
                    <?php if ( ! empty( $evt_avatar ) ) :
                        $evt_avatar_src = wp_get_attachment_image_src( $evt_avatar, 'codeweber_avatar' ); ?>
                        <figure class="user-avatar me-3">
                            <img class="rounded-circle" alt="<?php the_author_meta( 'display_name' ); ?>"
                                src="<?php echo esc_url( $evt_avatar_src[0] ); ?>">
                        </figure>
                    <?php else : ?>
                        <figure class="user-avatar me-3">
                            <?php echo get_avatar( get_the_author_meta( 'user_email' ), 96, '', '', [ 'class' => 'rounded-circle' ] ); ?>
                        </figure>
                    <?php endif; ?>
                    <div>
                        <h6 class="mb-0">
                            <a href="<?php echo esc_url( get_author_posts_url( $evt_user_id ) ); ?>" class="link-dark">
                                <?php
                            $first = get_the_author_meta( 'first_name' );
                            $last  = get_the_author_meta( 'last_name' );
                            echo esc_html( trim( "$first $last" ) ?: get_the_author_meta( 'display_name' ) );
                            ?>
                            </a>
                        </h6>
                        <span class="post-meta fs-15"><?php echo esc_html( $evt_job ); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( $show_any_seats && ! $hide_seats_counter ) : ?>
                <hr class="my-4">
                <div class="event-seats-counter"
                    data-event-seats-counter="<?php echo esc_attr( $event_id ); ?>"
                    data-seats-taken="<?php echo esc_attr( $registered_count ); ?>"
                    data-seats-max="<?php echo esc_attr( $max_participants ); ?>">

                    <?php if ( $show_bar ) : ?>
                        <div class="progress event-seats-progress mb-2" role="progressbar"
                            aria-valuenow="<?php echo esc_attr( $seats_pct ); ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-primary event-seats-bar"
                                style="width: <?php echo esc_attr( $seats_pct ); ?>%"></div>
                        </div>
                    <?php endif; ?>

                    <p class="event-seats-label mb-0 small">
                        <?php if ( $show_taken ) : ?>
                            <?php printf(
                                /* translators: %s: count */
                                esc_html__( '%s registered', 'codeweber' ),
                                '<strong><span class="event-seats-taken">' . esc_html( $registered_count ) . '</span></strong>'
                            ); ?>
                        <?php endif; ?>
                        <?php if ( $show_taken && $show_left ) : ?>&nbsp;&middot;&nbsp;<?php endif; ?>
                        <?php if ( $show_left ) : ?>
                            <?php printf(
                                /* translators: %s: count */
                                esc_html__( '%s seats left', 'codeweber' ),
                                '<strong><span class="event-seats-left">' . esc_html( $seats_left ) . '</span></strong>'
                            ); ?>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php // Кнопка регистрации — внизу первой карточки (modal / external) ?>
            <?php if ( $reg_status['status'] === 'modal' || $reg_status['status'] === 'external' ) : ?>
                <hr class="mt-4 mb-3">
                <?php if ( $reg_status['status'] === 'external' && $external_reg_url ) : ?>
                    <a href="<?php echo esc_url( $external_reg_url ); ?>" target="_blank" rel="noopener"
                        class="btn btn-primary btn-icon btn-icon-start has-ripple w-100<?php echo esc_attr( $button_style ); ?>">
                        <i class="uil uil-external-link-alt"></i>
                        <?php echo esc_html( __( ! empty( $reg_button_label ) ? $reg_button_label : 'Register', 'codeweber' ) ); ?>
                    </a>
                <?php elseif ( $reg_status['status'] === 'modal' ) : ?>
                    <?php
                    $modal_label = ! empty( $reg_button_label )
                        ? __( $reg_button_label, 'codeweber' )
                        : ( ! empty( $reg_status['label'] ) ? $reg_status['label'] : __( 'Register', 'codeweber' ) );
                    ?>
                    <a href="javascript:void(0)"
                       class="btn btn-primary has-ripple w-100<?php echo esc_attr( $button_style ); ?>"
                       data-bs-toggle="modal"
                       data-bs-target="#modal"
                       data-value="event-reg-<?php echo esc_attr( $event_id ); ?>">
                        <?php echo esc_html( $modal_label ); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
    </div>
    <!--/.widget -->

    <div class="sticky-top" style="top:80px;">
    <?php if ( $reg_status['show_form'] ) : ?>
    <div class="widget">
    <div class="card mt-4<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">
        <div class="card-body">
            <?php $nonce = wp_create_nonce( 'codeweber_event_register' ); ?>
            <div class="event-registration-wrap">
                <h3 class="mb-4 <?php echo esc_attr( $widget_h ); ?>"><?php echo esc_html( __( ! empty( $reg_form_title ) ? $reg_form_title : 'Register', 'codeweber' ) ); ?></h3>
                <form class="event-registration-form needs-validation"
                    data-event-id="<?php echo esc_attr( $event_id ); ?>"
                    novalidate>

                    <input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>">
                    <input type="hidden" name="event_reg_nonce" value="<?php echo esc_attr( $nonce ); ?>">
                    <input type="text" name="event_reg_honeypot" class="d-none" tabindex="-1" autocomplete="off">

                    <div class="form-floating mb-3">
                        <input type="text" name="reg_name" id="event-reg-name-<?php echo esc_attr( $event_id ); ?>"
                            class="form-control<?php echo esc_attr( $form_radius ); ?>"
                            placeholder="<?php esc_attr_e( 'Your name *', 'codeweber' ); ?>"
                            required>
                        <label for="event-reg-name-<?php echo esc_attr( $event_id ); ?>"><?php esc_html_e( 'Your name *', 'codeweber' ); ?></label>
                        <div class="invalid-feedback"><?php esc_html_e( 'Please enter your name.', 'codeweber' ); ?></div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" name="reg_email" id="event-reg-email-<?php echo esc_attr( $event_id ); ?>"
                            class="form-control<?php echo esc_attr( $form_radius ); ?>"
                            placeholder="<?php esc_attr_e( 'Email *', 'codeweber' ); ?>"
                            required>
                        <label for="event-reg-email-<?php echo esc_attr( $event_id ); ?>"><?php esc_html_e( 'Email *', 'codeweber' ); ?></label>
                        <div class="invalid-feedback"><?php esc_html_e( 'Please enter a valid email.', 'codeweber' ); ?></div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="tel" name="reg_phone" id="event-reg-phone-<?php echo esc_attr( $event_id ); ?>"
                            class="form-control<?php echo esc_attr( $form_radius ); ?>"
                            placeholder="<?php esc_attr_e( 'Phone', 'codeweber' ); ?>"
                            <?php if ( ! empty( $phone_mask ) ) : ?>data-mask="<?php echo esc_attr( $phone_mask ); ?>"<?php endif; ?>>
                        <label for="event-reg-phone-<?php echo esc_attr( $event_id ); ?>"><?php esc_html_e( 'Phone', 'codeweber' ); ?></label>
                    </div>

                    <div class="form-floating mb-4">
                        <textarea name="reg_message" id="event-reg-message-<?php echo esc_attr( $event_id ); ?>"
                            class="form-control<?php echo esc_attr( $form_radius ); ?>" rows="3" style="height:100px"
                            placeholder="<?php esc_attr_e( 'Comment (optional)', 'codeweber' ); ?>"></textarea>
                        <label for="event-reg-message-<?php echo esc_attr( $event_id ); ?>"><?php esc_html_e( 'Comment (optional)', 'codeweber' ); ?></label>
                    </div>

                    <div class="event-reg-form-messages mb-3"></div>

                    <button type="submit"
                        class="btn btn-primary has-ripple w-100<?php echo esc_attr( $button_style ); ?>"
                        data-loading-text="<?php esc_attr_e( 'Sending...', 'codeweber' ); ?>">
                        <?php echo esc_html( __( ! empty( $reg_button_label ) ? $reg_button_label : 'Register', 'codeweber' ) ); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
    </div>
    <?php endif; ?>

    <?php if ( $event_show_map === '1' && ! empty( $event_latitude ) && ! empty( $event_longitude ) && class_exists( 'Codeweber_Yandex_Maps' ) ) :
        $_evt_maps = Codeweber_Yandex_Maps::get_instance();
        if ( $_evt_maps->has_api_key() ) :
            $_evt_zoom    = ! empty( $event_zoom ) ? absint( $event_zoom ) : 15;
            $_evt_map_args = [
                'map_id'                   => 'event-sidebar-map-' . $event_id,
                'center'                   => [ floatval( $event_latitude ), floatval( $event_longitude ) ],
                'zoom'                     => $_evt_zoom,
                'height'                   => 250,
                'show_sidebar'             => false,
                'show_route'               => false,
                'clusterer'                => false,
                'marker_auto_open_balloon' => false,
            ];
            $_evt_markers = [[
                'latitude'    => floatval( $event_latitude ),
                'longitude'   => floatval( $event_longitude ),
                'hintContent' => ! empty( $event_yandex_address ) ? $event_yandex_address : '',
            ]];
        ?>
        <div class="widget mt-4">
            <h3 class="mb-3 <?php echo esc_attr( $widget_h ); ?>"><?php esc_html_e( 'On the map', 'codeweber' ); ?></h3>
            <div class="card<?php echo $card_radius ? ' ' . esc_attr( $card_radius ) : ''; ?>">
                <?php echo $_evt_maps->render_map( $_evt_map_args, $_evt_markers ); ?>
            </div>
        </div>
        <?php
        endif;
    endif; ?>
    </div>

    <?php if ( $countdown_until ) : ?>
    <script>
    (function () {
        var target = <?php echo (int) $countdown_until; ?> * 1000;
        document.querySelectorAll('.event-countdown').forEach(function (el) {
            var dEl = el.querySelector('.event-countdown-days');
            var hEl = el.querySelector('.event-countdown-hours');
            var mEl = el.querySelector('.event-countdown-mins');
            var sEl = el.querySelector('.event-countdown-secs');
            function pad(n) { return String(n).padStart(2, '0'); }
            function tick() {
                var diff = Math.max(0, Math.floor((target - Date.now()) / 1000));
                var d = Math.floor(diff / 86400);
                var h = Math.floor((diff % 86400) / 3600);
                var m = Math.floor((diff % 3600) / 60);
                var s = diff % 60;
                if (dEl) dEl.textContent = d;
                if (hEl) hEl.textContent = pad(h);
                if (mEl) mEl.textContent = pad(m);
                if (sEl) sEl.textContent = pad(s);
                if (diff <= 0) clearInterval(timer);
            }
            tick();
            var timer = setInterval(tick, 1000);
        });
    })();
    </script>
    <?php endif; ?>
    <?php
}
add_action('codeweber_before_sidebar', 'codeweber_sidebar_widget_events');


/**
 * Получает позицию сайдбара для текущей страницы/записи
 * 
 * @param string $opt_name Имя опции Redux
 * @return string Позиция сайдбара (left|right|none)
 */
function get_sidebar_position($opt_name)
{
    $post_type = universal_get_post_type();
    $post_id = get_the_ID();


    // Для архивов сразу возвращаем глобальную настройку
    if (!is_singular($post_type)) {
        $position = Redux::get_option($opt_name, 'sidebar_position_archive_' . $post_type);

        return $position;
    }

    // Для одиночных записей
    $custom_sidebar_enabled = Redux::get_post_meta($opt_name, $post_id, 'custom-page-sidebar-type') === '2';

    // Если кастомный сайдбар включен, получаем его позицию
    if ($custom_sidebar_enabled) {
        $custom_position = Redux::get_post_meta($opt_name, $post_id, 'custom-page-sidebar-position');
        if (!empty($custom_position)) {
            return $custom_position;
        }
    }

    // Возвращаем глобальную настройку по умолчанию
    return Redux::get_option($opt_name, 'sidebar_position_single_' . $post_type);
}

/**
 * Возвращает классы вертикальных отступов для контента и сайдбара
 *
 * @return string Классы Bootstrap, например "py-10 py-md-14"
 */
function get_content_padding_classes(): string {
    $mobile  = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::get( 'content_padding_mobile', 'py-10' ) : 'py-10';
    $desktop = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::get( 'content_padding_desktop', 'py-14' ) : 'py-14';
    $desktop_bp = preg_replace( '/^py-/', 'py-md-', $desktop );
    return $mobile . ' ' . $desktop_bp;
}

/**
 * Получает breakpoint для сайдбара текущего типа записи
 *
 * @param string $opt_name Имя опции Redux
 * @return string Breakpoint (md|lg|xl)
 */
function get_sidebar_breakpoint( string $opt_name ): string {
    $post_type = universal_get_post_type();
    $bp = Redux::get_option( $opt_name, 'sidebar_breakpoint_' . $post_type );
    return in_array( $bp, [ 'always', 'sm', 'md', 'lg', 'xl' ], true ) ? $bp : 'xl';
}