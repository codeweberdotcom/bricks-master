<?php

/**
 * Инициализация модуля пользовательских согласий
 */

// Проверяем, что файл вызывается в контексте WordPress
if (!defined('ABSPATH')) {
   exit;
}

// Подключаем основной класс менеджера
require_once __DIR__ . '/class-consent-manager.php';

// Инициализируем модуль
function codeweber_consent_module_init()
{
   return Consent_Manager::get_instance();
}

// Запускаем модуль с более высоким приоритетом
add_action('init', 'codeweber_consent_module_init', 5);
