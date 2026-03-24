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

/**
 * Register CF7 form endpoint for modal windows
 * 
 * Handles requests like /wp/v2/modal/cf7-1072
 */
function codeweber_register_cf7_modal_endpoint()
{
    register_rest_route(
        'wp/v2',
        '/modal/cf7-(?P<id>\d+)',
        array(
            'methods' => 'GET',
            'callback' => 'codeweber_get_cf7_form_for_modal',
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'validate_callback' => function ($param) {
                        return is_numeric($param);
                    },
                ),
            ),
        )
    );
}
// Регистрируем CF7 endpoint только если CF7 активен
if (class_exists('WPCF7')) {
    add_action('rest_api_init', 'codeweber_register_cf7_modal_endpoint', 10);
}

/**
 * Register Codeweber Forms endpoint for modal windows
 * 
 * Handles requests like /wp/v2/modal/cf-6055
 */
function codeweber_register_cf_modal_endpoint()
{
    register_rest_route(
        'wp/v2',
        '/modal/cf-(?P<id>\d+)',
        array(
            'methods' => 'GET',
            'callback' => 'codeweber_get_cf_form_for_modal',
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'validate_callback' => function ($param) {
                        return is_numeric($param);
                    },
                ),
            ),
        )
    );
}
add_action('rest_api_init', 'codeweber_register_cf_modal_endpoint', 10);

/**
 * Register FilePond translations endpoint
 * 
 * Returns FilePond translations for dynamic script loading
 */
function codeweber_register_filepond_translations_endpoint()
{
    register_rest_route(
        'codeweber-forms/v1',
        '/filepond-translations',
        array(
            'methods' => 'GET',
            'callback' => 'codeweber_get_filepond_translations',
            'permission_callback' => '__return_true',
        )
    );
}
add_action('rest_api_init', 'codeweber_register_filepond_translations_endpoint', 10);

/**
 * Get FilePond translations
 * 
 * @return WP_REST_Response Response with translations
 */
function codeweber_get_filepond_translations()
{
    // Load plugin file to access translation functions
    if (!function_exists('__')) {
        require_once ABSPATH . 'wp-includes/l10n.php';
    }
    
    $translations = array(
        'labelIdle' => __('Drag & drop your files or <span class="filepond--label-action">browse</span>', 'codeweber-gutenberg-blocks'),
        'maxFiles' => __('Maximum number of files: %s. Please remove excess files.', 'codeweber-gutenberg-blocks'),
        'fileTooLarge' => __('File is too large. Maximum size: %s', 'codeweber-gutenberg-blocks'),
        'totalSizeTooLarge' => __('Total file size is too large. Maximum: %s', 'codeweber-gutenberg-blocks'),
        'errorUploading' => __('Error uploading file', 'codeweber-gutenberg-blocks'),
        'errorAddingFile' => __('Error adding file', 'codeweber-gutenberg-blocks'),
        'filesRemoved' => __('Maximum number of files: %s. Files removed: %s', 'codeweber-gutenberg-blocks'),
        'totalSizeExceeded' => __('Total file size exceeded. Maximum: %s', 'codeweber-gutenberg-blocks'),
        'uploadComplete' => __('Upload complete', 'codeweber-gutenberg-blocks'),
        'tapToUndo' => __('Tap to undo', 'codeweber-gutenberg-blocks'),
        'uploading' => __('Uploading', 'codeweber-gutenberg-blocks'),
        'tapToCancel' => __('tap to cancel', 'codeweber-gutenberg-blocks'),
    );
    
    return rest_ensure_response($translations);
}

/**
 * Intercept REST API requests for CF7 and Codeweber forms in modals
 * 
 * This filter ensures that requests to /wp/v2/modal/cf7-{id} and /wp/v2/modal/cf-{id} are handled
 * before the standard modal post type endpoint tries to process them
 */
function codeweber_intercept_cf7_modal_requests($result, $server, $request)
{
    $route = $request->get_route();
    
    // Check if this is a modal request with cf7- prefix (Contact Form 7)
    if (preg_match('#^/wp/v2/modal/cf7-(\d+)$#', $route, $matches)) {
        $form_id = (int) $matches[1];
        
        // Check if Contact Form 7 is active
        if (!class_exists('WPCF7_ContactForm')) {
            return new WP_Error(
                'cf7_not_active',
                __('Contact Form 7 plugin is not active.', 'codeweber'),
                array('status' => 404)
            );
        }

        // Get CF7 form instance
        $contact_form = WPCF7_ContactForm::get_instance($form_id);

        if (!$contact_form) {
            return new WP_Error(
                'form_not_found',
                __('Contact form not found.', 'codeweber'),
                array('status' => 404)
            );
        }

        // Generate form HTML
        $form_html = $contact_form->form_html();

        // Return in the same format as modal post type REST API
        return rest_ensure_response(array(
            'id' => $form_id,
            'content' => array(
                'rendered' => $form_html,
            ),
            'modal_size' => '', // CF7 forms don't have modal size setting
        ));
    }
    
    // Check if this is a modal request with cf- prefix (Codeweber Forms)
    if (preg_match('#^/wp/v2/modal/cf-(\d+)$#', $route, $matches)) {
        $form_id = (int) $matches[1];
        
        // Check if CodeweberFormsRenderer class exists
        if (!class_exists('CodeweberFormsRenderer')) {
            return new WP_Error(
                'codeweber_forms_not_available',
                __('Codeweber Forms module is not available.', 'codeweber'),
                array('status' => 404)
            );
        }

        // Get form post
        $form_post = get_post($form_id);
        
        if (!$form_post || $form_post->post_type !== 'codeweber_form') {
            return new WP_Error(
                'form_not_found',
                __('Codeweber form not found.', 'codeweber'),
                array('status' => 404)
            );
        }

        // Render form HTML using CodeweberFormsRenderer
        $renderer = new CodeweberFormsRenderer();
        $form_html = $renderer->render($form_id, $form_post);

        // Get modal size from Redux meta if available
        $modal_size = '';
        if (class_exists('Redux')) {
            global $opt_name;
            $modal_size = Redux::get_post_meta($opt_name, $form_id, 'modal-size');
        }

        // Return in the same format as modal post type REST API
        return rest_ensure_response(array(
            'id' => $form_id,
            'content' => array(
                'rendered' => $form_html,
            ),
            'modal_size' => $modal_size ? $modal_size : '',
        ));
    }
    
    return $result;
}
// Перехватываем CF7 запросы только если CF7 активен
if (class_exists('WPCF7')) {
    add_filter('rest_pre_dispatch', 'codeweber_intercept_cf7_modal_requests', 10, 3);
}

/**
 * Get CF7 form HTML for modal window
 * 
 * @param WP_REST_Request $request REST API request
 * @return WP_REST_Response|WP_Error Response with form HTML or error
 */
function codeweber_get_cf7_form_for_modal($request)
{
    // Check if Contact Form 7 is active
    if (!class_exists('WPCF7_ContactForm')) {
        return new WP_Error(
            'cf7_not_active',
            __('Contact Form 7 plugin is not active.', 'codeweber'),
            array('status' => 404)
        );
    }

    $form_id = (int) $request->get_param('id');

    if (!$form_id) {
        return new WP_Error(
            'invalid_form_id',
            __('Invalid form ID.', 'codeweber'),
            array('status' => 400)
        );
    }

    // Get CF7 form instance
    $contact_form = WPCF7_ContactForm::get_instance($form_id);

    if (!$contact_form) {
        return new WP_Error(
            'form_not_found',
            __('Contact form not found.', 'codeweber'),
            array('status' => 404)
        );
    }

    // Generate form HTML
    // Use form_html() method which returns the complete form HTML
    $form_html = $contact_form->form_html();

    // Return in the same format as modal post type REST API
    // This ensures compatibility with restapi.js
    return rest_ensure_response(array(
        'id' => $form_id,
        'content' => array(
            'rendered' => $form_html,
        ),
        'modal_size' => '', // CF7 forms don't have modal size setting
    ));
}

/**
 * Get Codeweber Form HTML for modal window
 * 
 * @param WP_REST_Request $request REST API request
 * @return WP_REST_Response|WP_Error Response with form HTML or error
 */
function codeweber_get_cf_form_for_modal($request)
{
    // Check if CodeweberFormsRenderer class exists
    if (!class_exists('CodeweberFormsRenderer')) {
        return new WP_Error(
            'codeweber_forms_not_available',
            __('Codeweber Forms module is not available.', 'codeweber'),
            array('status' => 404)
        );
    }

    $form_id = (int) $request->get_param('id');

    if (!$form_id) {
        return new WP_Error(
            'invalid_form_id',
            __('Invalid form ID.', 'codeweber'),
            array('status' => 400)
        );
    }

    // Get form post
    $form_post = get_post($form_id);
    
    if (!$form_post || $form_post->post_type !== 'codeweber_form') {
        return new WP_Error(
            'form_not_found',
            __('Codeweber form not found.', 'codeweber'),
            array('status' => 404)
        );
    }

    // Render form HTML using CodeweberFormsRenderer
    $renderer = new CodeweberFormsRenderer();
    $form_html = $renderer->render($form_id, $form_post);

    // Get modal size from Redux meta if available
    $modal_size = '';
    if (class_exists('Redux')) {
        global $opt_name;
        $modal_size = Redux::get_post_meta($opt_name, $form_id, 'modal-size');
    }

    // Return in the same format as modal post type REST API
    // This ensures compatibility with restapi.js
    return rest_ensure_response(array(
        'id' => $form_id,
        'content' => array(
            'rendered' => $form_html,
        ),
        'modal_size' => $modal_size ? $modal_size : '',
    ));
}

