<?php
/**
 * Modal REST API Extensions
 * 
 * Adds modal-size field to the modal post type REST API response
 * so it can be used dynamically in JavaScript
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register modal-size meta field for REST API
 */
function codeweber_register_modal_size_meta()
{
    register_rest_field(
        'modal',
        'modal_size',
        array(
            'get_callback' => 'codeweber_get_modal_size',
            'update_callback' => null,
            'schema' => array(
                'description' => __('Modal window size class', 'codeweber'),
                'type' => 'string',
                'context' => array('view', 'edit'),
            ),
        )
    );
}
add_action('rest_api_init', 'codeweber_register_modal_size_meta');

/**
 * Get modal size from Redux meta
 * 
 * @param array $object Post object
 * @return string Modal size class
 */
function codeweber_get_modal_size($object)
{
    if (!class_exists('Redux')) {
        return '';
    }

    global $opt_name;
    $post_id = $object['id'];
    
    // Get modal-size from Redux meta
    $modal_size = Redux::get_post_meta($opt_name, $post_id, 'modal-size');
    
    return $modal_size ? $modal_size : '';
}

