<?php
/**
 * CodeWeber Forms Validator
 * 
 * Form field validation
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsValidator {
    /**
     * Validate field
     */
    public static function validate($field, $value, $config) {
        // Обязательность
        if (!empty($config['isRequired']) && empty($value)) {
            return ['valid' => false, 'message' => __('This field is required.', 'codeweber')];
        }
        
        // Длина
        if (!empty($config['minLength']) && strlen($value) < intval($config['minLength'])) {
            return ['valid' => false, 'message' => sprintf(__('Minimum length is %d characters.', 'codeweber'), $config['minLength'])];
        }
        
        if (!empty($config['maxLength']) && strlen($value) > intval($config['maxLength'])) {
            return ['valid' => false, 'message' => sprintf(__('Maximum length is %d characters.', 'codeweber'), $config['maxLength'])];
        }
        
        // Тип валидации
        switch ($field) {
            case 'email':
                if (!is_email($value)) {
                    return ['valid' => false, 'message' => __('Please enter a valid email address.', 'codeweber')];
                }
                break;
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    return ['valid' => false, 'message' => __('Please enter a valid URL.', 'codeweber')];
                }
                break;
        }
        
        return ['valid' => true];
    }
}


