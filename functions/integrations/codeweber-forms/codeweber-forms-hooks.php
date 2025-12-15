<?php
/**
 * CodeWeber Forms Hooks
 * 
 * Action and filter hooks for forms
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsHooks {
    /**
     * Fire hook before form send
     */
    public static function before_send($form_id, $form_data, $fields) {
        do_action('codeweber_form_before_send', $form_id, $form_data, $fields);
    }
    
    /**
     * Fire hook after form send
     */
    public static function after_send($form_id, $form_data, $submission_id) {
        do_action('codeweber_form_after_send', $form_id, $form_data, $submission_id);
    }
    
    /**
     * Fire hook after form saved
     */
    public static function after_saved($submission_id, $form_id, $form_data) {
        do_action('codeweber_form_saved', $submission_id, $form_id, $form_data);
    }
    
    /**
     * Fire hook on form send error
     */
    public static function send_error($form_id, $form_data, $error) {
        do_action('codeweber_form_send_error', $form_id, $form_data, $error);
    }
    
    /**
     * Fire hook when form opened
     */
    public static function form_opened($form_id) {
        do_action('codeweber_form_opened', $form_id);
    }
}

