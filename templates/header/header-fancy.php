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
    'mobileLogo' => 'light',
    'navbar-color' => 'light',
    'navbar-transparent' => false,
    'navbar-center-nav' => true,
    'header-bg-color' => NULL,
    'navbar-carret' => false,
    'navbar-rounded' => false,
    'mainMenuName' => 'header_1',
    'mainMenuClass' => 'navbar-nav',
    'social-type' => 'type2', //type1, type2, type3, type4, type5
    'social-size' => 'sm', //sm, md, lg

    'languageSelector' => ' <li class="nav-item dropdown language-select text-uppercase"><a class="nav-link dropdown-item dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">En</a><ul class="dropdown-menu"><li class="nav-item"><a class="dropdown-item" href="#">En</a></li><li class="nav-item"><a class="dropdown-item" href="#">Es</a></li></ul></li>',
    'buttonCTA' => '<li class="nav-item d-none d-md-block"><a href="#" class="btn btn-sm btn-primary rounded-pill">Get Touch</a></li>',
];
?>


<?php

global $opt_name;
$global_header_model = Redux::get_option($opt_name, 'global-header-model');
$header_color_text = Redux::get_option($opt_name, 'header-color-text');
$solid_color_header = Redux::get_option($opt_name, 'solid-color-header');
$soft_color_header = Redux::get_option($opt_name, 'soft-color-header');
$header_background = Redux::get_option($opt_name, 'header-background');
$header_rounded = Redux::get_option($opt_name, 'header-rounded');
$sort_offcanvas_right = Redux::get_option($opt_name, 'sort-offcanvas-right');
$social_icon_type = Redux::get_option($opt_name, 'social-icon-type');
$social_icon_type_mobile = Redux::get_option($opt_name, 'social-icon-type-mobile-menu');
$config['social-type'] = 'type' . $social_icon_type;
$config['social-type-mobile-menu'] = 'type' . $social_icon_type_mobile;
$mobile_menu_background = Redux::get_option($opt_name, 'mobile-menu-background');
$topbar_enable = Redux::get_option($opt_name, 'header-topbar-enable');

$global_header_offcanvas_right =  Redux::get_option($opt_name, 'global-header-offcanvas-right');
$company_description =  Redux::get_option($opt_name, 'company-description');

$yandex_api_key   = Redux::get_option($opt_name, 'yandexapi');
$coordinates      = Redux::get_option($opt_name, 'yandex_coordinates'); // строка типа "55.76, 37.64"
$zoom_level       = Redux::get_option($opt_name, 'yandex_zoom'); // например, "12"

$address_data = Redux::get_option($opt_name, 'fact-company-adress');
$parts = [];

if (!empty($address_data['box1'])) $parts[] = $address_data['box1'];
if (!empty($address_data['box2'])) $parts[] = $address_data['box2'];

if (!empty($address_data['box3']) || !empty($address_data['box4'])) {
$city_street = trim("{$address_data['box3']} {$address_data['box4']}");
$parts[] = $city_street;
}

if (!empty($address_data['box5']) || !empty($address_data['box6'])) {
$house = !empty($address_data['box5']) ? "д. {$address_data['box5']}" : '';
$office = !empty($address_data['box6']) ? "оф. {$address_data['box6']}" : '';
$house_office = trim("{$house}, {$office}", ', ');
$parts[] = $house_office;
}

if (!empty($address_data['box7'])) $parts[] = $address_data['box7'];
$full_address = implode('<br> ', $parts);


$phone1 = Redux::get_option($opt_name, 'phone_01') . '<br>';
$phone2 = Redux::get_option($opt_name, 'phone_02') . '<br>';
$email = Redux::get_option($opt_name, 'e-mail') . '<br>';

$header_navbar_class = array();
$header_navbar_wrapper_class = array();
$navbar_collapse_class = array();
$logo = 'light';

$header_navbar_class[] = 'navbar-light navbar-bg-light';

if ($global_header_model === '5') {
    $config['navbar-center-nav'] = true;
} elseif ($global_header_model === '4') {
    $config['navbar-center-nav'] = false;
}

if ($header_background === '3') {
    $config['navbar-transparent'] = true;
} elseif ($header_background === '1') {
    $config['header-bg-color'] = $solid_color_header;
} elseif ($header_background === '2') {
    $config['header-bg-color'] = $soft_color_header;
}


if ($header_rounded === '2') {
    $header_navbar_wrapper_class[] = 'rounded-pill';
} elseif ($header_rounded === '3') {
    $header_navbar_wrapper_class[] = 'rounded-0';
}

$header_navbar_wrapper_class[] = 'bg-light';

if (isset($config['header-bg-color']) && $config['navbar-transparent'] !== true && ($config['navbar-color'] === 'dark' || $config['navbar-color'] === 'light')) {
    $header_class = 'bg-' . $config['header-bg-color'];
}else{
    $header_class = '';
}

if ($config['navbar-center-nav'] === true) {
    $navbarotherclass = 'navbar-other w-100 d-flex ms-auto';
} else {
    $navbarotherclass = 'navbar-other ms-lg-4';
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

if ($config['navbar-transparent'] === true) {
    $header_navbar_class[] = 'transparent position-absolute';
}

?>

<header class="wrapper <?= $header_class; ?>">
    <?php if ($topbar_enable === '1') { ?>
        <?php get_template_part('templates/header/header', 'topbar'); ?>
    <?php }; ?>
    <nav class="navbar navbar-expand-lg fancy <?= implode(" ", $header_navbar_class); ?>">
        <div class="container">
            <div class="navbar-collapse-wrapper d-flex flex-row flex-nowrap w-100 justify-content-between align-items-center <?= implode(" ", $header_navbar_wrapper_class); ?>">
                <div class="navbar-brand w-100">
                    <a href="<?= htmlspecialchars($config['homeLink']); ?>">
                        <?= get_custom_logo_type($logo); ?>
                    </a>
                </div>
                <div class="navbar-collapse offcanvas offcanvas-nav offcanvas-start <?= implode(" ", $navbar_collapse_class); ?>">
                    <div class="offcanvas-header d-lg-none">
                        <a href="<?= htmlspecialchars($config['homeLink']); ?>"><?= get_custom_logo_type($config['mobileLogo']); ?></a>
                        <button type="button" class="btn-close btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
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
                                <?= social_links('mt-2', $config['social-type-mobile-menu'], $config['social-size']); ?>
                            </div>
                            <!-- /offcanvas-nav-other -->
                        <?php } ?>
                    </div>
                    <!-- /.offcanvas-body -->
                </div>
                <!-- /.navbar-collapse -->

                <div class="<?= $navbarotherclass; ?>">
                    <?php if (is_active_sidebar('header-right')) { ?>
                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <?php dynamic_sidebar('header-right'); ?>
                        </ul>
                    <?php } else {; ?>
                        <?php if ($config['navbar-center-nav'] === true) { ?>
                            <ul class="navbar-nav flex-row align-items-center ms-auto">
                                <li class="nav-item"><?= social_links('justify-content-end text-end', $config['social-type-mobile-menu'], $config['social-size']); ?></li>
                                <li class="nav-item d-lg-none">
                                    <button class="hamburger offcanvas-nav-btn"><span></span></button>
                                </li>
                            </ul>
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
                        <?php } ?>

                    <?php } ?>





                </div>
                <!-- /.navbar-other -->
            </div>
            <!-- /.navbar-collapse-wrapper -->
        </div>
        <!-- /.container -->
    </nav>
    <!-- /.navbar -->

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
                                echo social_links('', $config['social-type'], $config['social-size']);
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