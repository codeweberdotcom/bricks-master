<?php
/**
 * CodeWeber Forms Rate Limit
 * 
 * Rate limiting for form submissions
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsRateLimit {
    /**
     * Check rate limit
     */
    public static function check($form_id, $ip, $user_id = 0) {
        // Получаем настройки из админки
        $options = get_option('codeweber_forms_options', []);
        $enabled = isset($options['rate_limit_enabled']) ? $options['rate_limit_enabled'] : true;
        $limit = isset($options['rate_limit_max_submissions']) ? intval($options['rate_limit_max_submissions']) : 5;
        $period = isset($options['rate_limit_period']) ? intval($options['rate_limit_period']) : 60; // минуты
        
        if (!$enabled) {
            return true;
        }
        
        // Конвертируем минуты в секунды для transient
        $period_seconds = $period * 60;
        
        // Для залогиненных пользователей - отдельный лимит
        $key = $user_id > 0 
            ? "form_rate_user_{$user_id}_{$form_id}"
            : "form_rate_ip_" . md5($ip) . "_{$form_id}";
        
        $count = get_transient($key);
        
        if ($count === false) {
            $count = 0;
        }
        
        if ($count >= $limit) {
            return false;
        }
        
        set_transient($key, $count + 1, $period_seconds);
        return true;
    }
}

