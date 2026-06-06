<?php
/**
 * Инициализация модуля Яндекс карт для темы Codeweber
 *
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

// Подключаем реестр стилевых пресетов карты (общий для темы и блока)
require_once get_template_directory() . '/functions/integrations/yandex-maps/presets.php';

// Подключаем основной класс
require_once get_template_directory() . '/functions/integrations/yandex-maps/class-codeweber-yandex-maps.php';

// Регистрируем единый экземпляр на событии after_setup_theme, чтобы Redux уже был инициализирован
add_action('after_setup_theme', function () {
    if (class_exists('Codeweber_Yandex_Maps')) {
        Codeweber_Yandex_Maps::get_instance();
    }
}, 40);









