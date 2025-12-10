<?php
/**
 * Testimonial Form API
 * 
 * REST API endpoint for submitting testimonials via AJAX
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class TestimonialFormAPI {
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Endpoint for getting testimonial form HTML (for modal)
        register_rest_route('wp/v2', '/modal/add-testimonial', [
            'methods' => 'GET',
            'callback' => [$this, 'get_testimonial_form_html'],
            'permission_callback' => '__return_true'
        ]);
        
        // Endpoint for submitting testimonial
        register_rest_route('codeweber/v1', '/submit-testimonial', [
            'methods' => 'POST',
            'callback' => [$this, 'submit_testimonial'],
            'permission_callback' => '__return_true',
            'args' => [
                'testimonial_text' => [
                    'required' => true,
                    'sanitize_callback' => 'wp_kses_post',
                    'validate_callback' => function($param) {
                        return !empty(trim(strip_tags($param)));
                    }
                ],
                'author_name' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'author_email' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_email'
                ],
                'user_id' => [
                    'required' => false,
                    'sanitize_callback' => 'absint'
                ],
                'author_role' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'company' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'rating' => [
                    'required' => true,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function($param) {
                        return $param >= 1 && $param <= 5;
                    }
                ],
                'nonce' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
                'honeypot' => [
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ]
            ]
        ]);
    }

    /**
     * Get testimonial form HTML for modal
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_testimonial_form_html($request) {
        // Check if user is logged in
        // Get user_id from request parameter (passed from JS)
        $request_user_id = $request->get_param('user_id');
        $is_logged_in = false;
        $current_user = null;
        
        if ($request_user_id) {
            $user_id = absint($request_user_id);
            // Verify user exists
            $user = get_userdata($user_id);
            if ($user && $user->ID > 0) {
                // User exists, consider them logged in
                // Note: In REST API context, we trust the user_id passed from client
                // because it's only used to show/hide form fields, not for security
                $is_logged_in = true;
                $current_user = $user;
            }
        }
        
        // If no user_id in request, try to get current user (works if cookies are passed)
        if (!$is_logged_in) {
            $current_user_id = get_current_user_id();
            if ($current_user_id > 0) {
                $is_logged_in = true;
                $current_user = wp_get_current_user();
            }
        }
        
        ob_start();
        ?>
        <div class="testimonial-form-modal text-start">
            <h5 class="modal-title mb-4"><?php esc_html_e('Leave Your Testimonial', 'codeweber'); ?></h5>
            <form id="testimonial-form" class="testimonial-form">
                <?php 
                // Generate nonce for form submission
                $form_nonce = wp_create_nonce('submit_testimonial');
                ?>
                <input type="hidden" name="testimonial_nonce" value="<?php echo esc_attr($form_nonce); ?>" id="testimonial_nonce">
                <?php if ($is_logged_in): ?>
                    <input type="hidden" name="user_id" value="<?php echo esc_attr($current_user->ID); ?>" id="user_id">
                <?php endif; ?>
                
                <!-- Honeypot field (hidden, for spam protection) -->
                <input type="text" name="testimonial_honeypot" value="" style="display: none;" tabindex="-1" autocomplete="off">
                
                <!-- Messages container -->
                <div class="testimonial-form-messages" style="display: none;"></div>
                
                <div class="row g-4">
                    <!-- Testimonial Text -->
                    <div class="col-12">
                        <div class="form-floating">
                            <textarea 
                                class="form-control" 
                                name="testimonial_text" 
                                id="testimonial_text" 
                                placeholder="<?php esc_attr_e('Your testimonial text', 'codeweber'); ?>" 
                                style="height: 120px;"
                                required
                            ></textarea>
                            <label for="testimonial_text"><?php esc_html_e('Your Testimonial *', 'codeweber'); ?></label>
                        </div>
                    </div>
                    
                    <?php if (!$is_logged_in): ?>
                        <!-- Author Name -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    name="author_name" 
                                    id="author_name" 
                                    placeholder="<?php esc_attr_e('Your name', 'codeweber'); ?>"
                                    required
                                >
                                <label for="author_name"><?php esc_html_e('Your Name *', 'codeweber'); ?></label>
                            </div>
                        </div>
                        
                        <!-- Author Email -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    name="author_email" 
                                    id="author_email" 
                                    placeholder="<?php esc_attr_e('Your email', 'codeweber'); ?>"
                                    required
                                >
                                <label for="author_email"><?php esc_html_e('Your Email *', 'codeweber'); ?></label>
                            </div>
                        </div>
                        
                        <!-- Author Role -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    name="author_role" 
                                    id="author_role" 
                                    placeholder="<?php esc_attr_e('Your position', 'codeweber'); ?>"
                                >
                                <label for="author_role"><?php esc_html_e('Your Position', 'codeweber'); ?></label>
                            </div>
                        </div>
                        
                        <!-- Company -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    name="company" 
                                    id="company" 
                                    placeholder="<?php esc_attr_e('Company name', 'codeweber'); ?>"
                                >
                                <label for="company"><?php esc_html_e('Company', 'codeweber'); ?></label>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Rating -->
                    <div class="col-12">
                        <?php echo codeweber_testimonial_rating_stars(0, 'rating', 'rating', true); ?>
                    </div>
                </div>
                
                <div class="modal-footer text-center justify-content-center mt-4 pt-0 pb-0">
                    <button type="submit" class="btn btn-primary<?php echo getThemeButton(); ?>" data-loading-text="<?php esc_attr_e('Sending...', 'codeweber'); ?>"><?php esc_html_e('Submit Testimonial', 'codeweber'); ?></button>
                </div>
            </form>
        </div>
        <?php
        $form_html = ob_get_clean();
        
        return new WP_REST_Response([
            'content' => [
                'rendered' => $form_html
            ],
            'modal_size' => 'modal-lg'
        ], 200);
    }

    /**
     * Submit testimonial
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function submit_testimonial($request) {
        // Check nonce - try both form nonce and REST API nonce
        $nonce = $request->get_param('nonce');
        $rest_nonce = $request->get_header('X-WP-Nonce');
        
        // Try form nonce first (submit_testimonial action)
        $nonce_valid = false;
        if (!empty($nonce)) {
            $nonce_valid = wp_verify_nonce($nonce, 'submit_testimonial');
        }
        
        // If form nonce failed, try REST API nonce (wp_rest action)
        if (!$nonce_valid && !empty($rest_nonce)) {
            $nonce_valid = wp_verify_nonce($rest_nonce, 'wp_rest');
        }
        
        if (!$nonce_valid) {
            return new \WP_Error(
                'invalid_nonce',
                __('Security check failed. Please refresh the page and try again.', 'codeweber'),
                ['status' => 403]
            );
        }

        // Honeypot check (spam protection)
        $honeypot = $request->get_param('honeypot');
        if (!empty($honeypot)) {
            // Bot detected, silently fail
            return new WP_REST_Response([
                'success' => true,
                'message' => __('Thank you for your submission.', 'codeweber')
            ], 200);
        }

        // Rate limiting check (prevent spam)
        // Get IP address for logging (needed for all submissions)
        $ip = $this->get_client_ip();
        
        // Get user_id from request first (passed from JavaScript)
        // Try multiple ways to get user_id
        $request_user_id = $request->get_param('user_id');
        if (empty($request_user_id)) {
            // Try JSON params directly
            $json_params = $request->get_json_params();
            if (isset($json_params['user_id'])) {
                $request_user_id = $json_params['user_id'];
            }
        }
        $request_user_id = $request_user_id ? absint($request_user_id) : 0;
        
        // Debug: log request data
        error_log('Testimonial Submit - Request user_id (get_param): ' . $request->get_param('user_id'));
        error_log('Testimonial Submit - Request user_id (after processing): ' . $request_user_id);
        error_log('Testimonial Submit - JSON params: ' . print_r($request->get_json_params(), true));
        error_log('Testimonial Submit - All params: ' . print_r($request->get_params(), true));
        
        // Check if user is logged in (use request parameter or current user)
        $is_logged_in = false;
        $user_id = 0;
        
        if ($request_user_id > 0) {
            // Verify user exists
            $user = get_userdata($request_user_id);
            if ($user && $user->ID > 0) {
                $is_logged_in = true;
                $user_id = $request_user_id;
                error_log('Testimonial Submit - User authenticated via request parameter: ' . $user_id);
            } else {
                error_log('Testimonial Submit - User not found for user_id: ' . $request_user_id);
            }
        } else {
            // Fallback: check current user (works if cookies are passed)
            $current_user_id = get_current_user_id();
            if ($current_user_id > 0) {
                $is_logged_in = true;
                $user_id = $current_user_id;
                error_log('Testimonial Submit - User authenticated via get_current_user_id(): ' . $user_id);
            } else {
                error_log('Testimonial Submit - No user authenticated');
            }
        }
        
        // Rate limiting
        if ($is_logged_in && $user_id > 0) {
            // For logged-in users, skip rate limiting (trusted users)
            $transient_key = null;
            $submissions = 0;
        } else {
            // For guests, use IP address (stricter limit)
            $transient_key = 'testimonial_submit_' . md5($ip);
            $submissions = get_transient($transient_key);
            
            if ($submissions === false) {
                $submissions = 0;
            }
            
            // Limit: 3 submissions per hour per IP for guests
            if ($submissions >= 3) {
                return new \WP_Error(
                    'rate_limit_exceeded',
                    __('Too many submissions. Please try again later.', 'codeweber'),
                    ['status' => 429]
                );
            }
        }
        
        // Get and validate data
        $testimonial_text = $request->get_param('testimonial_text');
        $rating = $request->get_param('rating');
        
        // Initialize variables
        $author_name = '';
        $author_email = '';
        $author_role = '';
        $company = '';
        $author_type = 'custom';
        
        // If user is logged in, use their data
        if ($is_logged_in && $user_id > 0) {
            $current_user = get_userdata($user_id);
            if ($current_user && $current_user->ID > 0) {
                // Use logged-in user's data
                $author_name = $current_user->display_name;
                $author_email = $current_user->user_email;
                $author_role = get_user_meta($user_id, 'position', true) ?: '';
                $company = get_user_meta($user_id, 'company', true) ?: '';
                // Use 'user' to match admin interface (not 'registered')
                $author_type = 'user';
                error_log('Testimonial Submit - Using registered user data: ' . $author_name . ' (' . $author_email . ')');
                error_log('Testimonial Submit - Author type: ' . $author_type . ', User ID: ' . $user_id);
            } else {
                // User not found, treat as guest
                $is_logged_in = false;
                error_log('Testimonial Submit - User data not found, treating as guest');
            }
        } else {
            error_log('Testimonial Submit - User not logged in, using guest data');
        }
        
        // If not logged in, get data from form
        if (!$is_logged_in) {
            $author_name = $request->get_param('author_name');
            $author_email = $request->get_param('author_email');
            $author_role = $request->get_param('author_role');
            $company = $request->get_param('company');
            $author_type = 'custom';
            
            // Validate required fields for guests
            if (empty($author_name) || empty($author_email)) {
                return new \WP_Error(
                    'missing_fields',
                    __('Name and email are required.', 'codeweber'),
                    ['status' => 400]
                );
            }
            
            if (!is_email($author_email)) {
                return new \WP_Error(
                    'invalid_email',
                    __('Please provide a valid email address.', 'codeweber'),
                    ['status' => 400]
                );
            }
        }

        // Create testimonial post
        $post_data = [
            'post_title' => sprintf(__('Testimonial from %s', 'codeweber'), $author_name),
            'post_content' => '',
            'post_status' => 'pending', // Requires approval
            'post_type' => 'testimonials',
            'meta_input' => [
                '_testimonial_text' => wp_kses_post($testimonial_text),
                '_testimonial_author_type' => $author_type,
                '_testimonial_author_name' => sanitize_text_field($author_name),
                '_testimonial_author_role' => sanitize_text_field($author_role),
                '_testimonial_company' => sanitize_text_field($company),
                '_testimonial_rating' => absint($rating),
                '_testimonial_status' => 'pending',
                '_testimonial_submitted_email' => sanitize_email($author_email),
                '_testimonial_submitted_ip' => $ip,
                '_testimonial_submitted_date' => current_time('mysql')
            ]
        ];
        
        // If user is logged in, link to user
        if ($is_logged_in && $user_id > 0) {
            // Use the same meta key as in admin (for consistency)
            $post_data['meta_input']['_testimonial_author_user_id'] = absint($user_id);
            error_log('Testimonial Submit - Setting _testimonial_author_user_id: ' . $user_id);
        } else {
            error_log('Testimonial Submit - NOT setting _testimonial_author_user_id (is_logged_in: ' . ($is_logged_in ? 'true' : 'false') . ', user_id: ' . $user_id . ')');
        }
        
        error_log('Testimonial Submit - Final post_data meta_input: ' . print_r($post_data['meta_input'], true));

        $post_id = wp_insert_post($post_data, true);

        if (is_wp_error($post_id)) {
            return new \WP_Error(
                'post_creation_failed',
                __('Failed to create testimonial. Please try again.', 'codeweber'),
                ['status' => 500]
            );
        }
        
        // Debug: verify meta fields were saved
        error_log('Testimonial Submit - Post created with ID: ' . $post_id);
        error_log('Testimonial Submit - _testimonial_author_type: ' . get_post_meta($post_id, '_testimonial_author_type', true));
        error_log('Testimonial Submit - _testimonial_author_user_id: ' . get_post_meta($post_id, '_testimonial_author_user_id', true));
        error_log('Testimonial Submit - _testimonial_author_name: ' . get_post_meta($post_id, '_testimonial_author_name', true));
        
        // Double-check: if user is logged in, ensure meta fields are set correctly
        if ($is_logged_in && $user_id > 0) {
            // Force update meta fields to ensure they're saved
            // Use 'user' instead of 'registered' to match admin interface
            update_post_meta($post_id, '_testimonial_author_type', 'user');
            update_post_meta($post_id, '_testimonial_author_user_id', absint($user_id));
            error_log('Testimonial Submit - Force updated meta fields for registered user');
        }

        // Update rate limiting
        set_transient($transient_key, $submissions + 1, HOUR_IN_SECONDS);

        // Send notification email to admin (optional)
        $this->send_admin_notification($post_id, $author_name, $author_email);

        return new WP_REST_Response([
            'success' => true,
            'message' => __('Thank you for your testimonial! It will be reviewed and published soon.', 'codeweber'),
            'data' => [
                'post_id' => $post_id
            ]
        ], 200);
    }

    /**
     * Get client IP address
     * 
     * @return string
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }

    /**
     * Send notification email to admin
     * 
     * @param int $post_id
     * @param string $author_name
     * @param string $author_email
     */
    private function send_admin_notification($post_id, $author_name, $author_email) {
        $admin_email = get_option('admin_email');
        if (!$admin_email) {
            return;
        }

        $subject = sprintf(__('New Testimonial Submission from %s', 'codeweber'), $author_name);
        $message = sprintf(
            __("A new testimonial has been submitted and is pending review.\n\nAuthor: %s\nEmail: %s\n\nView: %s", 'codeweber'),
            $author_name,
            $author_email,
            admin_url('post.php?post=' . $post_id . '&action=edit')
        );

        wp_mail($admin_email, $subject, $message);
    }
}

// Initialize
new TestimonialFormAPI();

