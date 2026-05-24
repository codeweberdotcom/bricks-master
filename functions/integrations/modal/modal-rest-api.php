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

// ── Project Quick View REST endpoint ─────────────────────────────────────────

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v2', '/modal/project-(?P<id>\d+)', [
        'methods'             => 'GET',
        'callback'            => function ( WP_REST_Request $request ) {
            return codeweber_project_quick_view_response( (int) $request->get_param( 'id' ) );
        },
        'permission_callback' => '__return_true',
        'args'                => [
            'id' => [
                'required'          => true,
                'validate_callback' => function ( $v ) { return is_numeric( $v ) && $v > 0; },
            ],
        ],
    ] );
} );

/**
 * Build the REST response for project quick view.
 *
 * @param int $post_id Project post ID.
 * @return WP_REST_Response|WP_Error
 */
function codeweber_project_quick_view_response( int $post_id ) {
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'projects' || $post->post_status !== 'publish' ) {
        return new WP_Error( 'project_not_found', __( 'Project not found.', 'codeweber' ), [ 'status' => 404 ] );
    }

    $title        = get_post_meta( $post_id, '_alt_title', true ) ?: get_the_title( $post );
    $client       = get_post_meta( $post_id, 'main_information_client', true );
    $cms          = get_post_meta( $post_id, 'main_information_cms', true );
    $technologies = get_post_meta( $post_id, 'main_information_technologies', true );
    $description  = get_post_meta( $post_id, 'main_information_short_description', true );
    $website_url  = get_post_meta( $post_id, 'project_website_url', true );
    $website_open = get_post_meta( $post_id, 'project_website_open', true ) ?: 'new-tab';
    $website_cta  = get_post_meta( $post_id, 'project_website_cta', true ) ?: __( 'View website', 'codeweber' );
    $date         = get_post_meta( $post_id, 'main_information_date', true );

    $cats     = get_the_terms( $post_id, 'projects_category' );
    $cat_name = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';

    $link_target = $website_open === 'same-tab' ? '_self' : '_blank';
    $link_rel    = $website_open !== 'same-tab' ? 'noopener noreferrer' : '';

    $btn_style   = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : ' rounded-pill';
    $card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';

    // Gallery: use _project_gallery first, fall back to featured image
    $gallery_ids = function_exists( 'codeweber_get_project_gallery_ids' )
        ? codeweber_get_project_gallery_ids( $post_id )
        : [];
    if ( empty( $gallery_ids ) ) {
        $thumb_id = get_post_thumbnail_id( $post_id );
        if ( $thumb_id ) {
            $gallery_ids = [ $thumb_id ];
        }
    }

    ob_start();
    ?>
    <div class="cw-project-qv">

        <?php if ( ! empty( $gallery_ids ) ) : ?>
        <div class="cw-project-qv__gallery <?php echo esc_attr( $card_radius ); ?> overflow-hidden mb-4">
            <?php if ( count( $gallery_ids ) > 1 ) : ?>
            <div class="swiper" data-swiper='{"loop":true,"navigation":true,"pagination":{"clickable":true}}'>
                <div class="swiper-wrapper">
                    <?php foreach ( $gallery_ids as $aid ) : ?>
                    <div class="swiper-slide">
                        <?php echo wp_get_attachment_image( $aid, 'cw_wide_xl', false, [ 'class' => 'w-100 d-block', 'style' => 'max-height:420px;object-fit:cover;' ] ); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-pagination"></div>
            </div>
            <?php else : ?>
                <?php echo wp_get_attachment_image( $gallery_ids[0], 'cw_wide_xl', false, [ 'class' => 'w-100 d-block', 'style' => 'max-height:420px;object-fit:cover;' ] ); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="row g-0">
            <div class="col-12">

                <?php if ( $cat_name ) : ?>
                <div class="post-category text-line mb-2"><?php echo esc_html( $cat_name ); ?></div>
                <?php endif; ?>

                <h3 class="h4 mb-3">
                    <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="link-dark text-decoration-none">
                        <?php echo wp_kses_post( $title ); ?>
                    </a>
                </h3>

                <?php if ( $client || $cms || $date ) : ?>
                <ul class="list-unstyled fs-15 text-muted mb-3">
                    <?php if ( $client ) : ?>
                    <li><strong><?php esc_html_e( 'Client', 'codeweber' ); ?>:</strong> <?php echo esc_html( $client ); ?></li>
                    <?php endif; ?>
                    <?php if ( $cms ) : ?>
                    <li><strong><?php esc_html_e( 'CMS / Framework', 'codeweber' ); ?>:</strong> <?php echo esc_html( $cms ); ?></li>
                    <?php endif; ?>
                    <?php if ( $date ) : ?>
                    <li><strong><?php esc_html_e( 'Year', 'codeweber' ); ?>:</strong> <?php echo esc_html( $date ); ?></li>
                    <?php endif; ?>
                </ul>
                <?php endif; ?>

                <?php if ( $technologies ) : ?>
                <p class="fs-15 mb-3"><strong><?php esc_html_e( 'Technologies', 'codeweber' ); ?>:</strong><br>
                    <?php echo nl2br( esc_html( $technologies ) ); ?>
                </p>
                <?php endif; ?>

                <?php if ( $description ) : ?>
                <p class="mb-4"><?php echo nl2br( esc_html( $description ) ); ?></p>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="btn btn-primary<?php echo esc_attr( $btn_style ); ?> has-ripple">
                        <?php esc_html_e( 'View project', 'codeweber' ); ?>
                    </a>
                    <?php if ( $website_url ) : ?>
                    <a href="<?php echo esc_url( $website_url ); ?>"
                       target="<?php echo esc_attr( $link_target ); ?>"
                       <?php if ( $link_rel ) : ?>rel="<?php echo esc_attr( $link_rel ); ?>"<?php endif; ?>
                       class="btn btn-outline-primary<?php echo esc_attr( $btn_style ); ?> btn-icon btn-icon-start has-ripple">
                        <i class="uil uil-external-link-alt"></i>
                        <?php echo esc_html( $website_cta ); ?>
                    </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>
    <?php
    $html = ob_get_clean();

    return rest_ensure_response( [
        'id'         => $post_id,
        'content'    => [ 'rendered' => $html ],
        'modal_size' => 'modal-lg',
    ] );
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

