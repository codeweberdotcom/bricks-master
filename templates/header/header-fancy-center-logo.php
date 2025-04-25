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
    'navbar-color' => 'dark',
    'navbar-transparent' => false,
    'navbar-center-nav' => NULL,
    'header-bg-color' => 'bg-red',
    'navbar-carret' => false,
    'social-type' => 'type2', //type1, type2, type3, type4, type5
    'social-size' => 'sm', //sm, md, lg
    'navbar-rounded' => false,
    'mainMenuName' => 'header',
    'mainMenuName1' => 'header_1',
    'mainMenuClass' => NULL,
    'mainMenuClass1' => NULL,

    'languageSelector' => NULL,
    'buttonCTA' => NULL,
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



$phone1 = Redux::get_option($opt_name, 'phone_01') . '<br>';
$phone2 = Redux::get_option($opt_name, 'phone_02') . '<br>';
$email = Redux::get_option($opt_name, 'e-mail') . '<br>';

$header_navbar_class = array();
$header_navbar_wrapper_class = array();
$navbar_collapse_class = array();

if ($header_rounded === '2') {
    $header_navbar_wrapper_class[] = 'rounded-pill';
} elseif ($header_rounded === '3') {
    $header_navbar_wrapper_class[] = 'rounded-0';
}


$logo = 'light';
$header_navbar_wrapper_class[] = 'bg-white';

if ($header_color_text === '2') {
    $logo = 'light';
    $header_navbar_class[] = 'navbar-bg-light';
    $header_navbar_class[] = 'navbar-light';
} elseif ($header_color_text === '1') {
    $logo = 'dark';
    $header_navbar_class[] = 'navbar-bg-dark';
    $header_navbar_class[] = 'navbar-dark';
}

if ($mobile_menu_background === '1') {
    $navbar_collapse_class[] = 'offcanvas-dark';
    $config['mobileLogo'] = 'light';
    $config['btn-close-mobile'] = '';
} elseif ($mobile_menu_background === '2') {
    $navbar_collapse_class[] = 'offcanvas-light';
    $config['mobileLogo'] = 'dark';
    $config['btn-close-mobile'] = 'btn-close-white';
}

if ($header_background === '3') {
    $config['navbar-transparent'] = true;
} elseif ($header_background === '1') {
    $config['header-bg-color'] = $solid_color_header;
} elseif ($header_background === '2') {
    $config['header-bg-color'] = $soft_color_header;
}

if ($config['navbar-transparent'] === true) {
    $header_navbar_class[] = 'transparent position-absolute';
}


?>

<header class="wrapper <?= $config['header-bg-color']; ?>">
    <?php if ($topbar_enable === '1') { ?>
        <?php get_template_part('templates/header/header', 'topbar'); ?>
    <?php }; ?>
    <nav class="navbar navbar-expand-lg fancy center-logo <?= implode(" ", $header_navbar_class); ?>">
        <div class="container">
            <div class="navbar-collapse-wrapper d-lg-flex flex-row flex-nowrap w-100 justify-content-between align-items-center <?= implode(" ", $header_navbar_wrapper_class); ?>">
                <div class="d-flex flex-row w-100 justify-content-between align-items-center d-lg-none">
                    <div class="navbar-brand">
                        <a href="<?= htmlspecialchars($config['homeLink']); ?>">
                            <?= get_custom_logo_type($logo); ?>
                        </a>
                    </div>
                    <div class="navbar-other ms-auto">
                        <ul class="navbar-nav flex-row align-items-center">
                            <li class="nav-item d-lg-none">
                                <button class="hamburger offcanvas-nav-btn"><span></span></button>
                            </li>
                        </ul>
                        <!-- /.navbar-nav -->
                    </div>
                    <!-- /.navbar-other -->
                </div>
                <!-- /.d-flex -->

                <div class="navbar-collapse-inner d-flex flex-row align-items-center w-100 mt-0">
                    <div class="navbar-collapse offcanvas offcanvas-nav offcanvas-start <?= implode(" ", $navbar_collapse_class); ?>">
                        <div class="offcanvas-header mx-lg-auto order-0 order-lg-1 d-lg-flex px-lg-15">
                            <a href="<?= htmlspecialchars($config['homeLink']); ?>">
                                <?= get_custom_logo_type($logo); ?>
                            </a>
                            <button type="button" class="btn-close d-lg-none <?= $config['btn-close-mobile']; ?>" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="w-100 order-1 order-lg-0 d-lg-flex offcanvas-body">
                            <?php
                            wp_nav_menu(
                                array(
                                    'theme_location'    => $config['mainMenuName'],
                                    'depth'             => 4,
                                    'container'         => '',
                                    'container_class'   => '',
                                    'container_id'      => '',
                                    'menu_class'        => 'navbar-nav ms-lg-auto',
                                    'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
                                    'walker'            => new WP_Bootstrap_Navwalker(),
                                )
                            )
                            ?>
                            <!-- /.navbar-nav -->
                        </div>

                        <div class="w-100 order-3 order-lg-2 d-lg-flex offcanvas-body">
                            <?php
                            wp_nav_menu(
                                array(
                                    'theme_location'    => $config['mainMenuName1'],
                                    'depth'             => 4,
                                    'container'         => '',
                                    'container_class'   => '',
                                    'container_id'      => '',
                                    'menu_class'        => 'navbar-nav me-lg-auto',
                                    'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
                                    'walker'            => new WP_Bootstrap_Navwalker(),
                                )
                            )
                            ?>
                            <!-- /.navbar-nav -->
                        </div>

                        <div class="offcanvas-body order-4 mt-auto">
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
                    </div>
                    <!-- /.navbar-collapse -->
                </div>
                <!-- /.navbar-collapse-wrapper -->
            </div>
            <!-- /.navbar-collapse-wrapper -->
        </div>
        <!-- /.container -->
    </nav>
    <!-- /.navbar -->
</header>