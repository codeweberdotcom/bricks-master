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
    'navbar-transparent' => true,
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
$header_color_text = Redux::get_option($opt_name, 'header-color-text');
$solid_color_header = Redux::get_option($opt_name, 'solid-color-header');
$soft_color_header = Redux::get_option($opt_name, 'soft-color-header');
$header_background = Redux::get_option($opt_name, 'header-background');
$header_rounded = Redux::get_option($opt_name, 'header-rounded');



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

    $header_navbar_class[] = 'navbar-bg-light';
    $navbar_collapse_class[] = 'bg-white';
    $logo = 'light';
    $header_navbar_wrapper_class[] = 'bg-white';

if($header_background === '1'){
    $config['header-bg-color'] = 'bg-' . $solid_color_header;
} elseif ($header_background === '2') {
    $config['header-bg-color'] = 'bg-' . $soft_color_header;
}else{
    $config['header-bg-color'] = '';
}


?>

<header class="wrapper <?= $config['header-bg-color']; ?>">
    <nav class="navbar navbar-expand-lg fancy center-logo <?= implode(" ", $header_navbar_class); ?>">
        <div class="container">
            <div class="navbar-collapse-wrapper d-lg-flex flex-row flex-nowrap w-100 justify-content-between align-items-center <?= implode(" ", $header_navbar_wrapper_class); ?>">
                <div class="d-flex flex-row w-100 justify-content-between align-items-center d-lg-none">
                    <div class="navbar-brand">
                        <a href="<?= htmlspecialchars($config['homeLink']); ?>">
                            <?= get_custom_logo_type($config['mobileLogo']); ?>
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
                    <div class="navbar-collapse bg-light offcanvas offcanvas-nav offcanvas-start <?= implode(" ", $navbar_collapse_class); ?>">
                        <div class="offcanvas-header mx-lg-auto order-0 order-lg-1 d-lg-flex px-lg-15">
                            <a href="<?= htmlspecialchars($config['homeLink']); ?>">
                                <?= get_custom_logo_type($logo); ?>
                            </a>
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
                                <!-- /.social -->
                            </div>
                            <!-- /offcanvas-nav-other -->
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