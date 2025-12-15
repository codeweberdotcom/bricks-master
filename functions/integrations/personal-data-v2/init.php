<?php
/**
 * Personal Data V2 Module Initialization
 * 
 * Универсальный модуль для работы с персональными данными
 * Поддерживает любые модули, формы, подписки через систему провайдеров
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

// Определяем константы модуля
define('PERSONAL_DATA_V2_PATH', __DIR__);
define('PERSONAL_DATA_V2_URL', get_template_directory_uri() . '/functions/integrations/personal-data-v2');

// Подключаем основные классы
require_once PERSONAL_DATA_V2_PATH . '/class-data-provider-interface.php';
require_once PERSONAL_DATA_V2_PATH . '/class-personal-data-manager.php';

/**
 * Инициализация модуля
 */
function personal_data_v2_init() {
    // Получаем экземпляр менеджера (он сам зарегистрирует GDPR обработчики)
    $manager = Personal_Data_Manager::get_instance();
    
    // Хук для других модулей, чтобы они могли зарегистрировать свои провайдеры
    do_action('personal_data_v2_ready', $manager);
    
    return $manager;
}

// Инициализируем модуль с приоритетом 5 (раньше других модулей)
add_action('init', 'personal_data_v2_init', 5);

/**
 * Вспомогательная функция для получения менеджера
 * 
 * @return Personal_Data_Manager|null
 */
function personal_data_manager(): ?Personal_Data_Manager {
    if (!class_exists('Personal_Data_Manager')) {
        return null;
    }
    return Personal_Data_Manager::get_instance();
}


