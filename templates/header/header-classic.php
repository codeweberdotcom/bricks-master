<?php

/**
 * Шаблон для навигационного меню сайта.
 *
 * Этот шаблон создает Header сайта, включая логотипы, меню, контактную информацию и социальные ссылки. 
 * Он динамически наполняется данными, переданными через массив $config.
 *
 * Массив $config должен содержать следующие ключи:
 * - 'homeLink' (string): Ссылка на главную страницу.
 * - 'logo' (string): HTML код логотипа для десктопной версии.
 * - 'mobileLogo' (string): HTML код логотипа для мобильной версии.
 * - 'mainMenu' (string): HTML код для основного меню.
 * - 'contactInfo' (string): Контактная информация, отображаемая на мобильных устройствах.
 * - 'socialLinks' (string): HTML код иконок социальных сетей.
 * - 'languageSelector' (string): HTML код для отображения переключателя языков.
 * - 'contactLink' (string): Ссылка на страницу контактов.
 * - 'contactButtonText' (string): Текст кнопки для перехода на страницу контактов. *
 * @package YourPackage
 * @version 1.0.0
 */

    


$config = [
    'homeLink' => '/',

    'mobileLogo' => 'both',
    'navbar-color' => 'dark',
    'navbar-transparent' => false,
    'header-bg-color' => 'yellow',

    'navbar-center-nav' => false,
    'navbar-carret' => false,
    'navbar-rounded' => false,
    'mainMenuName' => 'header_1',
    'mainMenuClass' => 'navbar-nav',
    'social-type' => 'type3', //type1, type2, type3, type4, type5
    'social-size' => 'sm', //sm, md, lg

    'languageSelector' => ' <li class="nav-item dropdown language-select text-uppercase"><a class="nav-link dropdown-item dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">En</a><ul class="dropdown-menu"><li class="nav-item"><a class="dropdown-item" href="#">En</a></li><li class="nav-item"><a class="dropdown-item" href="#">Es</a></li></ul></li>',
    'buttonCTA' => ' <li class="nav-item d-none d-md-block"><a href="#" data-bs-toggle="modal" data-bs-target="#modal-form" class="btn btn-sm btn-primary rounded-pill">Get Touch</a></li>',
];

global $opt_name;
$global_header_model = Redux::get_option($opt_name, 'global-header-model');
// Используем функцию-хелпер для получения настроек с учетом индивидуальных настроек страницы
$header_color_text = function_exists('codeweber_get_header_option') ? codeweber_get_header_option('header-color-text') : Redux::get_option($opt_name, 'header-color-text');
$solid_color_header = function_exists('codeweber_get_header_option') ? codeweber_get_header_option('solid-color-header') : Redux::get_option($opt_name, 'solid-color-header');
$soft_color_header = function_exists('codeweber_get_header_option') ? codeweber_get_header_option('soft-color-header') : Redux::get_option($opt_name, 'soft-color-header');
$header_background = function_exists('codeweber_get_header_option') ? codeweber_get_header_option('header-background') : Redux::get_option($opt_name, 'header-background');
$header_rounded = function_exists('codeweber_get_header_option') ? codeweber_get_header_option('header-rounded') : Redux::get_option($opt_name, 'header-rounded');
$sort_offcanvas_right = Redux::get_option($opt_name, 'sort-offcanvas-right');
$social_icon_type = Redux::get_option($opt_name, 'social-icon-type');
$social_icon_type_mobile = Redux::get_option($opt_name, 'social-icon-type-mobile-menu');
$social_button_style_offcanvas = Redux::get_option($opt_name, 'social-button-style-offcanvas', 'circle');
$social_button_style_mobile = Redux::get_option($opt_name, 'social-button-style-mobile-menu', 'circle');
$social_button_size_offcanvas = Redux::get_option($opt_name, 'social-button-size-offcanvas', 'md');
$social_button_size_mobile = Redux::get_option($opt_name, 'social-button-size-mobile-menu', 'md');
$config['social-type'] = 'type' . $social_icon_type;
$config['social-type-mobile-menu'] = 'type' . $social_icon_type_mobile;
$config['social-button-style-offcanvas'] = $social_button_style_offcanvas;
$config['social-button-style-mobile-menu'] = $social_button_style_mobile;
$config['social-button-size-offcanvas'] = $social_button_size_offcanvas;
$config['social-button-size-mobile-menu'] = $social_button_size_mobile;
$mobile_menu_background = Redux::get_option($opt_name, 'mobile-menu-background');
$topbar_enable = Redux::get_option($opt_name, 'header-topbar-enable');






$global_header_offcanvas_right =  Redux::get_option($opt_name, 'global-header-offcanvas-right');
$company_description =  Redux::get_option($opt_name, 'text-about-company');

$yandex_api_key   = Redux::get_option($opt_name, 'yandexapi');
$coordinates      = Redux::get_option($opt_name, 'yandex_coordinates'); // строка типа "55.76, 37.64"
$zoom_level       = Redux::get_option($opt_name, 'yandex_zoom'); // например, "12"

$address_data = Redux::get_option($opt_name, 'fact-company-adress');

$parts = [];


$country      = Redux::get_option($opt_name, 'fact-country') ?? '';
$region       = Redux::get_option($opt_name, 'fact-region') ?? '';
$city         = Redux::get_option($opt_name, 'fact-city') ?? '';
$street       = ', ' . Redux::get_option($opt_name, 'fact-street') ?? '';
$house_number = ', ' . Redux::get_option($opt_name, 'fact-house') ?? '';
$office       = Redux::get_option($opt_name, 'fact-office') ?? '';
$postal_code  = Redux::get_option($opt_name, 'fact-postal') ?? '';
// Формируем строку улицы и номера
$street_line = trim(implode(' ', array_filter([$street, $house_number])), ' ,');
// Добавляем офис/квартиру, если есть
$office_line = $office ?  $office : '';
// Собираем городскую строку
$city_line = trim(implode(', ', array_filter([$city, $street_line, $office_line])), ' ,');
// Составляем полный адрес в международном формате
$full_address = trim(implode(",\n", array_filter([$country, $region, $city_line, $postal_code])), "\n");


$phone1 = Redux::get_option($opt_name, 'phone_01') . '<br>';
$phone2 = Redux::get_option($opt_name, 'phone_02') . '<br>';
$email = Redux::get_option($opt_name, 'e-mail') . '<br>';


if ($global_header_model === '1') {
    $config['navbar-center-nav'] = true;
} elseif ($global_header_model === '2') {
    $config['navbar-center-nav'] = false;
}

if ($header_background === '3') {
    $config['navbar-transparent'] = true;
} elseif ($header_background === '1') {
    $config['header-bg-color'] = $solid_color_header;
} elseif ($header_background === '2') {
    $config['header-bg-color'] = $soft_color_header;
}



$header_navbar_class = array();
$navbar_collapse_class = array();
if ($config['navbar-transparent'] === true) {
    $header_navbar_class[] = 'transparent position-absolute';
}


if ($header_color_text === '2') {
    $config['navbar-color'] = 'dark';
} elseif ($header_color_text === '1') {
    $config['navbar-color'] = 'light';
}

if ($config['navbar-color'] === 'dark') {
    $header_navbar_class[] = 'navbar-dark';
    $header_class = NULL;
    if ($config['navbar-transparent'] !== true) {
        $header_navbar_class[] = 'navbar-bg-dark';
    }
    $logo = 'both';
} elseif ($config['navbar-color'] === 'light') {
    $header_navbar_class[] = 'navbar-light';
    $header_class = NULL;
    if ($config['navbar-transparent'] !== true) {
        $header_navbar_class[] = 'navbar-bg-light';
    }
    $logo = 'light';
} else {
    $header_navbar_class[] = 'navbar-light';
    $header_class = NULL;
    if ($config['navbar-transparent'] !== true) {
        $header_navbar_class[] = 'navbar-bg-light';
    }
    $logo = 'dark';
}

if ($mobile_menu_background === '1') {
    $navbar_collapse_class[] = 'offcanvas-light';
    $config['mobileLogo'] = 'light';
    $config['btn-close-mobile'] = '';
} elseif ($mobile_menu_background === '2') {
    $navbar_collapse_class[] = 'offcanvas-dark';
    $config['mobileLogo'] = 'dark';
    $config['btn-close-mobile'] = 'btn-close-white';
}


if (isset($config['header-bg-color']) && $config['navbar-transparent'] !== true && ($config['navbar-color'] === 'dark' || $config['navbar-color'] === 'light')) {
    $header_class = 'bg-' . $config['header-bg-color'];
    $header_navbar_class[] = 'bg-' . $config['header-bg-color'];
}

if ($config['navbar-center-nav'] === true) {
    $navbarotherclass = 'navbar-other w-100 d-flex ms-auto';
} else {
    $navbarotherclass = 'navbar-other ms-lg-4';
}
?>

<header class="wrapper <?= $header_class; ?>">
    <?php if ($topbar_enable === '1') { ?>
        <?php get_template_part('templates/header/header', 'topbar'); ?>
    <?php }; ?>
    <nav class="navbar navbar-expand-lg <?= implode(" ", $header_navbar_class); ?>">
        <div class="container flex-lg-row flex-nowrap align-items-center">
            <div class="navbar-brand w-100">
                <a href="<?= htmlspecialchars($config['homeLink']); ?>">
                    <?= get_custom_logo_type($logo); ?>
                </a>
            </div>
            <div class="navbar-collapse offcanvas offcanvas-nav offcanvas-start <?= implode(" ", $navbar_collapse_class); ?>">
                <div class="offcanvas-header d-lg-none">
                    <a href="<?= htmlspecialchars($config['homeLink']); ?>"><?= get_custom_logo_type($config['mobileLogo']); ?></a>
                    <button type="button" class="btn-close <?= $config['btn-close-mobile']; ?>" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body ms-lg-auto d-flex flex-column h-100">
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location'    => $config['mainMenuName'],
                            'depth'             => 4,
                            'container'         => '',
                            'container_class'   => '',
                            'container_id'      => '',
                            'menu_class'        => $config['mainMenuClass'],
                            'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
                            'walker'            => new WP_Bootstrap_Navwalker(),
                        )
                    )
                    ?>

                    <?php if (is_active_sidebar('mobile-menu-footer')) { ?>
                        <div class="d-lg-none mt-auto pt-6 pb-6 order-4">
                            <?php dynamic_sidebar('mobile-menu-footer'); ?>
                        </div>
                    <?php } else {; ?>
                        <div class="d-lg-none mt-auto pt-6 pb-6 order-4">
                            <a href="mailto:<?php $email; ?>"><?php $email; ?></a>
                            <a href="tel:<?php cleanNumber($phone1); ?>"><?= $phone1; ?></a>
                            <a href="tel:<?php cleanNumber($phone2); ?>"><?= $phone2; ?></a>
                            <?= social_links('mt-2', $config['social-type-mobile-menu'], $config['social-button-size-mobile-menu'], 'primary', 'solid', $config['social-button-style-mobile-menu']); ?>
                        </div>
                        <!-- /offcanvas-nav-other -->
                    <?php } ?>


                </div>
            </div>
            <div class="<?= $navbarotherclass; ?>">


                <?php if (is_active_sidebar('header-right')) { ?>
                    <ul class="navbar-nav flex-row align-items-center ms-auto">
                        <?php dynamic_sidebar('header-right'); ?>
                        <li class="nav-item d-lg-none">
                            <button class="hamburger offcanvas-nav-btn"><span></span></button>
                        </li>
                    </ul>
                    <!-- /.navbar-nav -->
                <?php } else { ?>
                    <ul class="navbar-nav flex-row align-items-center ms-auto">
                        <?php if ($global_header_offcanvas_right === '1') { ?>
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-info"><i class="uil uil-info-circle"></i></a></li>
                        <?php } ?>
                        <?= $config['languageSelector']; ?>
                        <?= $config['buttonCTA']; ?>
                        <li class="nav-item d-lg-none">
                            <button class="hamburger offcanvas-nav-btn"><span></span></button>
                        </li>
                    </ul>
                    <!-- /.navbar-nav -->
                <?php } ?>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-top bg-light" id="offcanvas-search" data-bs-scroll="true">
        <div class="container d-flex flex-row py-6">
            <form class="search-form w-100">
                <input id="search-form" type="text" class="form-control" placeholder="<?= esc_html__('Type keyword and hit enter', 'codeweber'); ?>">
            </form>
            <!-- /.search-form -->
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <!-- /.container -->
    </div>
    <!-- /.offcanvas -->

    <?php
    if ($global_header_offcanvas_right === '1') { ?>
        <div class="offcanvas offcanvas-end bg-light" id="offcanvas-info" data-bs-scroll="true">
            <div class="offcanvas-header">
                <?= get_custom_logo_type('light'); ?>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pb-6">
                <?php
                if (
                    isset($sort_offcanvas_right['enabled']) &&
                    is_array($sort_offcanvas_right['enabled'])
                ) {
                    foreach ($sort_offcanvas_right['enabled'] as $key => $value) {
                        if ($key === 'placebo') continue;
                        switch ($key) {

                            case 'widget_offcanvas_1':
                                if (is_active_sidebar('header-widget-1')) {
                                    echo '<div class="widget mb-5">';
                                    dynamic_sidebar('header-widget-1');
                                    echo '</div><!-- /.widget -->';
                                }
                                break;

                            case 'widget_offcanvas_2':
                                if (is_active_sidebar('header-widget-2')) {
                                    echo '<div class="widget mb-5">';
                                    dynamic_sidebar('header-widget-2');
                                    echo '</div><!-- /.widget -->';
                                }
                                break;

                            case 'widget_offcanvas_3':
                                if (is_active_sidebar('header-widget-3')) {
                                    echo '<div class="widget mb-5">';
                                    dynamic_sidebar('header-widget-3');
                                    echo '</div><!-- /.widget -->';
                                }
                                break;


                            case 'description':
                                echo '<div class="widget mb-5">
                                      <p>' . $company_description . '</p>
                                      </div>
                                      <!-- /.widget -->';
                                break;
                            case 'phones':
                                echo '<div class="widget mb-5">
                                      <div>
                                      <div class="mb-1 h5">' . esc_html__('Phone', 'codeweber') . '</div>
                                      <a href="tel:' . cleanNumber($phone1) . '">' . $phone1 . '</a>
                                      <a href="tel:' . cleanNumber($phone2) . '">' . $phone2 . '</a>
                                      </div>
                                      </div>
                                      <!-- /.widget -->
                
                                      <div class="widget mb-5">
                                      <div>
                                      <div class="mb-1 h5">' . esc_html__('E-mail', 'codeweber') . '</div>
                                      <a href="mailto:' . $email . '">' . $email . ' </a>
                                      </div>
                                       </div>
                                      <!-- /.widget -->';
                                break;
                                // Выводим для проверки
                               
                            case 'address':
                                echo '<div class="widget mb-5">
                                      <div class="align-self-start justify-content-start">
                                      <div class="mb-1 h5">' . esc_html__('Address', 'codeweber') . '</div>
                                      <address>' . $full_address . '</address>
                                      </div>
                                      </div>
                                      <!-- /.widget -->';
                                break;

                            case 'menu':
                                echo '<div class="widget mb-5">';
                                $locations = get_nav_menu_locations();
                                if (isset($locations['offcanvas'])) {
                                    $menu = wp_get_nav_menu_object($locations['offcanvas']);
                                    echo '<div class="widget-title mb-3 h4">' . esc_html__($menu->name, 'codeweber') . '</div>';
                                }
                                wp_nav_menu(
                                    array(
                                        'theme_location'    => 'offcanvas',
                                        'depth'             => 1,
                                        'container'         => 'ul',
                                        'container_class'   => '',
                                        'container_id'      => '',
                                        'menu_class'        => 'list-unstyled',
                                    )
                                );
                                echo '</div>';
                                break;

                            case 'map':

                                if (!empty($coordinates)) : ?>
                                    <div class="widget mb-5">
                                        <div class="widget-title mb-3 h4"><?= esc_html__('On Map', 'codeweber'); ?></div>
                                        <div id="frontend-yandex-map" style="width: 100%; height: 200px;"></div>
                                    </div>
                                    <script src="https://api-maps.yandex.ru/2.1/?apikey=<?php echo esc_attr($yandex_api_key); ?>&lang=ru_RU"></script>
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function() {
                                            ymaps.ready(function() {
                                                var coords = "<?php echo esc_js($coordinates); ?>".split(",").map(function(coord) {
                                                    return parseFloat(coord.trim());
                                                });
                                                var zoom = parseInt("<?php echo esc_js($zoom_level); ?>") || 10;

                                                var map = new ymaps.Map("frontend-yandex-map", {
                                                    center: coords,
                                                    zoom: zoom
                                                });

                                                var placemark = new ymaps.Placemark(coords);
                                                map.geoObjects.add(placemark);
                                            });
                                        });
                                    </script>
                <?php endif;
                                break;

                            case 'socials':
                                echo '<div class="widget mb-5">
                                       <div class="widget-title mb-3 h4">' . esc_html__('Social Media', 'codeweber') . ' </div>';
                                echo social_links('', $config['social-type'], $config['social-button-size-offcanvas'], 'primary', 'solid', $config['social-button-style-offcanvas']);
                                echo '</div>';
                                break;

                            default:
                                echo "<!-- Блок {$key} не найден -->";
                        }
                    }
                }

                ?>
            </div>
        </div>
        <!-- /.offcanvas -->
    <?php } ?>



   


</header>