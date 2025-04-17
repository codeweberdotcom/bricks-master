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

    'mainMenuName' => 'header',
    'mainMenuName1' => 'header_1',
    'mainMenuClass' => NULL,
    'mainMenuClass1' => NULL,

    'mainMenuClass' => 'navbar-nav',
    'social-type' => 'type3', //type1, type2, type3, type4, type5
    'social-size' => 'sm', //sm, md, lg

    'languageSelector' => ' <li class="nav-item dropdown language-select text-uppercase"><a class="nav-link dropdown-item dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">En</a><ul class="dropdown-menu"><li class="nav-item"><a class="dropdown-item" href="#">En</a></li><li class="nav-item"><a class="dropdown-item" href="#">Es</a></li></ul></li>',
    'buttonCTA' => ' <li class="nav-item d-none d-md-block"><a href="#" class="btn btn-sm btn-primary rounded-pill">Get Touch</a></li>',
];

global $opt_name;
$global_header_model = Redux::get_option($opt_name, 'global-header-model');
$header_color_text = Redux::get_option($opt_name, 'header-color-text');
$solid_color_header = Redux::get_option($opt_name, 'solid-color-header');
$soft_color_header = Redux::get_option($opt_name, 'soft-color-header');
$header_background = Redux::get_option($opt_name, 'header-background');
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
    $header_navbar_class[] = 'transparent';
}


if ($header_color_text === '1') {
    $config['navbar-color'] = 'dark';
} elseif ($header_color_text === '2') {
    $config['navbar-color'] = 'light';
}

if ($config['navbar-color'] === 'dark') {
    $header_navbar_class[] = 'navbar-dark';
    $btn_close_class = 'btn-close-white';
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
        $navbar_collapse_class[] = 'bg-white';
    }
    $btn_close_class = NULL;
    $logo = 'light';
} else {
    $header_navbar_class[] = 'navbar-light';
    $header_class = NULL;
    if ($config['navbar-transparent'] !== true) {
        $header_navbar_class[] = 'navbar-bg-light';
        $navbar_collapse_class[] = 'bg-white';
    }
    $btn_close_class = NULL;
    $logo = 'light';
}

if (isset($config['header-bg-color']) && $config['navbar-transparent'] !== true && ($config['navbar-color'] === 'dark' || $config['navbar-color'] === 'light')) {
    $header_class = 'bg-' . $config['header-bg-color'];
    $header_navbar_class[] = 'bg-' . $config['header-bg-color'];
}
?>

<header class="wrapper <?= $header_class; ?>">
    <nav class="navbar navbar-expand-lg center-logo <?= implode(" ", $header_navbar_class); ?>">
        <div class="container justify-content-between align-items-center">
            <div class="d-flex flex-row w-100 justify-content-between align-items-center d-lg-none">
                <div class="navbar-brand"><a href="<?= htmlspecialchars($config['homeLink']); ?>">
                        <?= get_custom_logo_type($logo); ?>
                    </a></div>
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
            <div class="navbar-collapse-wrapper d-flex flex-row align-items-center w-100">
                <div class="navbar-collapse offcanvas offcanvas-nav offcanvas-start">
                    <div class="offcanvas-header mx-lg-auto order-0 order-lg-1 d-lg-flex px-lg-15">
                        <a href="<?= htmlspecialchars($config['homeLink']); ?>" class="transition-none d-none d-lg-flex"><?= get_custom_logo_type($logo); ?></a>
                        <a href="<?= htmlspecialchars($config['homeLink']); ?>" class="d-lg-none transition-none"><?= get_custom_logo_type($logo); ?></a>
                        <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
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
                        <div class="d-lg-none mt-auto pt-6 pb-6 order-4">
                            <a href="mailto:<?= $email; ?>"><?= $email; ?></a>
                            <a href="tel:<?= cleanNumber($phone1); ?>"><?= $phone1; ?></a>
                            <a href="tel:<?= cleanNumber($phone2); ?>"><?= $phone2; ?></a>
                            <?= social_links($config['social-type'], $config['social-size'], NULL); ?>
                        </div>
                        <!-- /offcanvas-nav-other -->
                    </div>
                </div>
                <!-- /.navbar-collapse -->
            </div>
            <!-- /.navbar-collapse-wrapper -->
        </div>
    </nav>
</header>