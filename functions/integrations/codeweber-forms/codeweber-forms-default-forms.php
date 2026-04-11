<?php
/**
 * CodeWeber Forms Default Forms
 * 
 * Default формы всегда хранятся в коде темы в виде HTML шаблонов
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsDefaultForms {
    /**
     * Общий счетчик для всех default форм (гарантирует уникальность ID)
     */
    private static $global_form_instance_counter = 0;
    
    /**
     * Get default form HTML
     * 
     * Default формы всегда берутся из кода темы
     * 
     * @param string $form_type Form type
     * @param bool $is_logged_in Is user logged in (for hiding guest-only fields)
     * @param int $user_id User ID if logged in (for user_id hidden field)
     * @return string HTML of the form
     */
    public function get_default_form_html($form_type, $is_logged_in = false, $user_id = 0) {
        switch ($form_type) {
            case 'testimonial':
                return $this->get_default_testimonial_form_html($is_logged_in, $user_id);
            case 'newsletter':
                return $this->get_default_newsletter_form_html($is_logged_in, $user_id);
            case 'faq':
                return $this->get_default_faq_form_html();
        }

        // event-registration is handled via get_default_event_registration_form_html() directly
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Default Forms] No default form found for type: ' . $form_type);
        }
        
        return '';
    }
    
    /**
     * Get default testimonial form HTML
     * 
     * Возвращает HTML шаблон default формы отзывов
     * 
     * @param bool $is_logged_in Is user logged in (to remove guest-only fields)
     * @param int $user_id User ID if logged in (for user_id hidden field)
     * @return string HTML of the form
     */
    private function get_default_testimonial_form_html($is_logged_in = false, $user_id = 0) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Default Forms] Generating testimonial form HTML, is_logged_in: ' . ($is_logged_in ? 'true' : 'false') . ', user_id: ' . $user_id);
        }
        
        // Генерируем уникальный ID формы для каждого экземпляра на странице
        self::$global_form_instance_counter++;
        $form_unique_id = 'form-0-' . self::$global_form_instance_counter;
        
        // Получаем классы скругления из темы
        $form_radius_class = class_exists('Codeweber_Options') ? Codeweber_Options::style('form-radius') : '';
        $button_radius_class = class_exists('Codeweber_Options') ? Codeweber_Options::style('button') : '';
        
        // Генерируем nonce поле (для REST API используем wp_create_nonce)
        $nonce_value = wp_create_nonce('submit_testimonial');
        $nonce_field = '<input type="hidden" id="testimonial_nonce" name="testimonial_nonce" value="' . esc_attr($nonce_value) . '">';
        
        // Генерируем user_id поле, если пользователь залогинен
        $user_id_field = '';
        if ($is_logged_in && $user_id > 0) {
            $user_id_field = '<input type="hidden" name="user_id" value="' . esc_attr($user_id) . '">';
        }
        
        // Генерируем rating поле
        $rating_field_html = '';
        if (function_exists('codeweber_testimonial_rating_stars')) {
            $rating_field_html = codeweber_testimonial_rating_stars(0, 'rating', 'field-rating', true);
            if (empty($rating_field_html)) {
                // Fallback если функция не вернула HTML
                $rating_field_html = '<div class="testimonial-rating-selector">
                    <label class="form-label d-block mb-0 mt-3">' . esc_html__('Rating', 'codeweber') . ' *</label>
                    <input type="hidden" name="rating" id="field-rating" value="0" required="">
                    <div class="rating-stars-wrapper d-flex gap-1 align-items-center p-0" data-rating-input="field-rating"></div>
                </div>';
            }
        } else {
            // Fallback если функция не существует
            $rating_field_html = '<div class="testimonial-rating-selector">
                <label class="form-label d-block mb-0 mt-3">' . esc_html__('Rating', 'codeweber') . ' *</label>
                <input type="hidden" name="rating" id="field-rating" value="0" required="">
                <div class="rating-stars-wrapper d-flex gap-1 align-items-center p-0" data-rating-input="field-rating"></div>
            </div>';
        }
        
        // Переводы для полей
        $label_message = __('Your Review', 'codeweber');
        $label_name = __('Your Name', 'codeweber');
        $label_email = __('Your Email', 'codeweber');
        $label_role = __('Your Position', 'codeweber');
        $label_company = __('Company', 'codeweber');
        $label_rating = __('Rating', 'codeweber');
        $placeholder_message = __('Your testimonial text', 'codeweber');
        $placeholder_name = __('Your name', 'codeweber');
        $placeholder_email = __('Your email', 'codeweber');
        $placeholder_role = __('Your position', 'codeweber');
        $placeholder_company = __('Company name', 'codeweber');
        $button_text = __('Submit Testimonial', 'codeweber');
        $button_class = 'btn btn-primary' . ($button_radius_class ? ' ' . $button_radius_class : '');
        
        // Генерируем HTML для полей гостей (будут удалены, если пользователь залогинен)
        $guest_fields_html = $this->get_guest_fields_html($form_radius_class, $label_name, $label_email, $label_role, $label_company, $placeholder_name, $placeholder_email, $placeholder_role, $placeholder_company);
        
        // Основной HTML шаблон
        // Consent checkboxes from testimonials settings
        $consent_html = '';
        $builtin_consents = get_option( 'builtin_form_consents', [] );
        $testimonial_consents = isset( $builtin_consents['testimonial'] ) ? $builtin_consents['testimonial'] : [];
        if ( is_array( $testimonial_consents ) && ! empty( $testimonial_consents ) && function_exists( 'codeweber_forms_render_consent_checkbox' ) ) {
            foreach ( $testimonial_consents as $consent ) {
                if ( empty( $consent['document_id'] ) || empty( $consent['label'] ) ) {
                    continue;
                }
                $consent_html .= codeweber_forms_render_consent_checkbox( $consent, 'form_consents', 0 );
            }
        }
        $consent_block = $consent_html ? '<div class="mt-3">' . $consent_html . '</div>' : '';

        $html = '<form id="' . esc_attr($form_unique_id) . '" class="codeweber-form needs-validation" data-form-id="0" data-form-type="testimonial" data-form-name="' . esc_attr(__('Default Testimonial Form', 'codeweber')) . '" data-handled-by="codeweber-forms-universal" method="post" enctype="multipart/form-data" novalidate="">
            ' . $nonce_field . '
            <input type="hidden" name="form_id" value="0">
            ' . $user_id_field . '
            <input type="hidden" name="form_honeypot" value="">
            <div class="testimonial-form-messages" style="display: none;"></div>

            <div class="row g-4">
                <!-- Поле message (textarea, required) -->
                <div class="col-12">
                    <div class="form-floating">
                        <textarea class="form-control' . esc_attr($form_radius_class) . '" id="field-message" name="message" placeholder="' . esc_attr($placeholder_message) . '" required="" style="height: 120px;"></textarea>
                        <label for="field-message">
                            ' . esc_html($label_message) . ' <span class="text-danger">*</span>
                        </label>
                    </div>
                </div>

                <!-- GUEST_ONLY_START -->
                ' . $guest_fields_html . '
                <!-- GUEST_ONLY_END -->

                <!-- Поле rating (rating, required) -->
                <div class="col-12">
                    ' . $rating_field_html . '
                </div>
            </div>
            ' . $consent_block . '
            <div class="form-submit-wrapper mt-4">
                <button type="submit" class="' . esc_attr($button_class) . ' btn-icon btn-icon-start" data-loading-text="' . esc_attr(__('Sending', 'codeweber')) . '">
                    <i class="uil uil-send fs-13"></i>
                    <span class="ms-1">' . esc_html($button_text) . '</span>
                </button>
            </div>
        </form>';
        
        // Удаляем поля для гостей, если пользователь залогинен
        if ($is_logged_in) {
            $html = preg_replace('/<!-- GUEST_ONLY_START -->.*?<!-- GUEST_ONLY_END -->/s', '', $html);
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Default Forms] Generated HTML length: ' . strlen($html));
        }
        
        return $html;
    }
    
    /**
     * Get HTML for guest-only fields
     * 
     * @param string $form_radius_class CSS class for form radius
     * @param string $label_name Label for name field
     * @param string $label_email Label for email field
     * @param string $label_role Label for role field
     * @param string $label_company Label for company field
     * @param string $placeholder_name Placeholder for name field
     * @param string $placeholder_email Placeholder for email field
     * @param string $placeholder_role Placeholder for role field
     * @param string $placeholder_company Placeholder for company field
     * @return string HTML for guest fields
     */
    private function get_guest_fields_html($form_radius_class, $label_name, $label_email, $label_role, $label_company, $placeholder_name, $placeholder_email, $placeholder_role, $placeholder_company) {
        return '
                <!-- Поле name (text, showForGuestsOnly) -->
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control' . esc_attr($form_radius_class) . '" id="field-name" name="name" placeholder="' . esc_attr($placeholder_name) . '" value="" required="">
                        <label for="field-name">
                            ' . esc_html($label_name) . ' <span class="text-danger">*</span>
                        </label>
                    </div>
                </div>
                
                <!-- Поле email (email, showForGuestsOnly) -->
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="email" class="form-control' . esc_attr($form_radius_class) . '" id="field-email" name="email" placeholder="' . esc_attr($placeholder_email) . '" value="" required="">
                        <label for="field-email">
                            ' . esc_html($label_email) . ' <span class="text-danger">*</span>
                        </label>
                    </div>
                </div>
                
                <!-- Поле role (author_role, showForGuestsOnly) -->
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control' . esc_attr($form_radius_class) . '" id="field-role" name="role" placeholder="' . esc_attr($placeholder_role) . '" value="">
                        <label for="field-role">
                            ' . esc_html($label_role) . '
                        </label>
                    </div>
                </div>
                
                <!-- Поле company (text, showForGuestsOnly) -->
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control' . esc_attr($form_radius_class) . '" id="field-company" name="company" placeholder="' . esc_attr($placeholder_company) . '" value="">
                        <label for="field-company">
                            ' . esc_html($label_company) . '
                        </label>
                    </div>
                </div>';
    }
    
    /**
     * Get default event registration form HTML
     *
     * @param int    $event_id     Event post ID
     * @param string $button_label Submit button label
     * @return string
     */
    public function get_default_event_registration_form_html( $event_id, $button_label = '' ) {
        self::$global_form_instance_counter++;
        $form_unique_id = 'form-event-reg-' . self::$global_form_instance_counter;

        $form_radius  = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'form-radius' ) : '';
        $button_style = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button' ) : '';
        $phone_mask   = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::get( 'opt_phone_mask', '' ) : '';

        $nonce_value  = wp_create_nonce( 'codeweber_event_register' );
        $button_label = $button_label ?: __( 'Register', 'codeweber' );
        $btn_class    = 'btn btn-primary has-ripple w-100' . ( $button_style ? ' ' . $button_style : '' );

        $phone_mask_attr = $phone_mask ? ' data-mask="' . esc_attr( $phone_mask ) . '"' : '';

        // Field visibility settings from event meta
        $email_required = get_post_meta( $event_id, '_event_reg_email_required', true );
        $phone_required = get_post_meta( $event_id, '_event_reg_phone_required', true );
        $show_comment   = get_post_meta( $event_id, '_event_reg_show_comment', true );
        $show_seats     = get_post_meta( $event_id, '_event_reg_show_seats', true );
        if ( $email_required === '' ) { $email_required = '1'; }
        if ( $show_comment === '' )   { $show_comment = '1'; }

        $email_required = $email_required === '1';
        $phone_required = $phone_required === '1';
        $show_comment   = $show_comment === '1';
        $show_seats     = $show_seats === '1';

        $fields = '';

        // Email
        if ( $email_required ) {
            $fields .= '<div class="mb-3">
                <input type="email" name="reg_email" class="form-control' . esc_attr( $form_radius ) . '"
                    placeholder="' . esc_attr__( 'Email *', 'codeweber' ) . '" required>
                <div class="invalid-feedback">' . esc_html__( 'Please enter a valid email.', 'codeweber' ) . '</div>
            </div>';
        }

        // Phone
        if ( $phone_required ) {
            $fields .= '<div class="mb-3">
                <input type="tel" name="reg_phone" class="form-control' . esc_attr( $form_radius ) . '"
                    placeholder="' . esc_attr__( 'Phone *', 'codeweber' ) . '"' . $phone_mask_attr . ' required>
                <div class="invalid-feedback">' . esc_html__( 'Please enter your phone number.', 'codeweber' ) . '</div>
            </div>';
        } elseif ( ! $email_required ) {
            // Если email не требуется и телефон не требуется — показываем телефон необязательным
            $fields .= '<div class="mb-3">
                <input type="tel" name="reg_phone" class="form-control' . esc_attr( $form_radius ) . '"
                    placeholder="' . esc_attr__( 'Phone', 'codeweber' ) . '"' . $phone_mask_attr . '>
            </div>';
        } else {
            // Email требуется, телефон — необязательный
            $fields .= '<div class="mb-3">
                <input type="tel" name="reg_phone" class="form-control' . esc_attr( $form_radius ) . '"
                    placeholder="' . esc_attr__( 'Phone', 'codeweber' ) . '"' . $phone_mask_attr . '>
            </div>';
        }

        // Seats
        if ( $show_seats ) {
            $fields .= '<div class="mb-3">
                <input type="number" name="reg_seats" class="form-control' . esc_attr( $form_radius ) . '"
                    placeholder="' . esc_attr__( 'Number of seats', 'codeweber' ) . '" min="1" value="1">
            </div>';
        }

        // Comment
        if ( $show_comment ) {
            $fields .= '<div class="mb-4">
                <textarea name="reg_message" class="form-control' . esc_attr( $form_radius ) . '" rows="3"
                    placeholder="' . esc_attr__( 'Comment (optional)', 'codeweber' ) . '"></textarea>
            </div>';
        }

        // Consent checkboxes
        $reg_consents = get_post_meta( $event_id, '_event_reg_consents', true );
        if ( is_array( $reg_consents ) && ! empty( $reg_consents ) && function_exists( 'codeweber_forms_render_consent_checkbox' ) ) {
            $consent_html = '';
            foreach ( $reg_consents as $consent ) {
                if ( empty( $consent['document_id'] ) || empty( $consent['label'] ) ) {
                    continue;
                }
                $consent_html .= codeweber_forms_render_consent_checkbox( $consent, 'reg_consent', 0 );
            }
            if ( $consent_html ) {
                $fields .= '<div class="mb-3">' . $consent_html . '</div>';
            }
        }

        return '<form id="' . esc_attr( $form_unique_id ) . '"
            class="codeweber-form needs-validation"
            data-form-id="0"
            data-form-type="event-registration"
            data-event-id="' . esc_attr( $event_id ) . '"
            data-handled-by="codeweber-forms-universal"
            method="post"
            novalidate>

            <input type="hidden" name="event_id" value="' . esc_attr( $event_id ) . '">
            <input type="hidden" name="event_reg_nonce" value="' . esc_attr( $nonce_value ) . '">
            <input type="text"   name="event_reg_honeypot" class="d-none" tabindex="-1" autocomplete="off">

            <div class="mb-3">
                <input type="text" name="reg_name" class="form-control' . esc_attr( $form_radius ) . '"
                    placeholder="' . esc_attr__( 'Your name *', 'codeweber' ) . '" required>
                <div class="invalid-feedback">' . esc_html__( 'Please enter your name.', 'codeweber' ) . '</div>
            </div>
            ' . $fields . '
            <div class="event-reg-form-messages mb-3" style="display:none;"></div>

            <button type="submit"
                class="' . esc_attr( $btn_class ) . '"
                data-loading-text="' . esc_attr__( 'Sending...', 'codeweber' ) . '">
                ' . esc_html( $button_label ) . '
            </button>
        </form>';
    }

    /**
     * Get default FAQ question form HTML
     *
     * @return string HTML of the form
     */
    public function get_default_faq_form_html() {
        self::$global_form_instance_counter++;
        $form_unique_id = 'form-faq-' . self::$global_form_instance_counter;

        $form_radius  = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'form-radius' ) : '';
        $button_style = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'button', '' ) : '';
        $phone_mask   = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::get( 'opt_phone_mask', '' ) : '';

        $settings   = get_option( 'codeweber_faq_settings', [] );
        $show_name  = ( $settings['show_name'] ?? '1' ) === '1';
        $show_email = ( $settings['show_email'] ?? '1' ) === '1';
        $show_phone = ( $settings['show_phone'] ?? '1' ) === '1';

        $nonce_value    = wp_create_nonce( 'codeweber_faq_submit' );
        $btn_class      = 'btn btn-primary has-ripple' . ( $button_style ? ' ' . $button_style : '' );
        $phone_mask_attr = $phone_mask ? ' data-mask="' . esc_attr( $phone_mask ) . '"' : '';

        $fields = '';

        // Name
        if ( $show_name ) {
            $fid = 'faq-name-' . self::$global_form_instance_counter;
            $fields .= '<div class="form-floating mb-3">
                <input type="text" name="name" id="' . esc_attr( $fid ) . '" class="form-control' . esc_attr( $form_radius ) . '"
                    placeholder="' . esc_attr__( 'Your name', 'codeweber' ) . '" required>
                <label for="' . esc_attr( $fid ) . '">' . esc_html__( 'Your name', 'codeweber' ) . ' <span class="text-danger">*</span></label>
            </div>';
        }

        // Email
        if ( $show_email ) {
            $fid = 'faq-email-' . self::$global_form_instance_counter;
            $fields .= '<div class="form-floating mb-3">
                <input type="email" name="email" id="' . esc_attr( $fid ) . '" class="form-control' . esc_attr( $form_radius ) . '"
                    placeholder="' . esc_attr__( 'Email', 'codeweber' ) . '" required>
                <label for="' . esc_attr( $fid ) . '">' . esc_html__( 'Email', 'codeweber' ) . ' <span class="text-danger">*</span></label>
            </div>';
        }

        // Phone
        if ( $show_phone ) {
            $fid = 'faq-phone-' . self::$global_form_instance_counter;
            $fields .= '<div class="form-floating mb-3">
                <input type="tel" name="phone" id="' . esc_attr( $fid ) . '" class="form-control' . esc_attr( $form_radius ) . '"
                    placeholder="' . esc_attr__( 'Phone', 'codeweber' ) . '"' . $phone_mask_attr . ' required>
                <label for="' . esc_attr( $fid ) . '">' . esc_html__( 'Phone', 'codeweber' ) . ' <span class="text-danger">*</span></label>
            </div>';
        }

        // Question (always shown)
        $fid = 'faq-question-' . self::$global_form_instance_counter;
        $fields .= '<div class="form-floating mb-4">
            <textarea name="message" id="' . esc_attr( $fid ) . '" class="form-control' . esc_attr( $form_radius ) . '" rows="3" style="height:100px"
                placeholder="' . esc_attr__( 'Your question *', 'codeweber' ) . '" required></textarea>
            <label for="' . esc_attr( $fid ) . '">' . esc_html__( 'Your question', 'codeweber' ) . ' <span class="text-danger">*</span></label>
        </div>';

        // Consent checkboxes
        $consent_html = '';
        $builtin_consents = get_option( 'builtin_form_consents', [] );
        $faq_consents     = isset( $builtin_consents['faq'] ) ? $builtin_consents['faq'] : [];
        if ( is_array( $faq_consents ) && ! empty( $faq_consents ) && function_exists( 'codeweber_forms_render_consent_checkbox' ) ) {
            foreach ( $faq_consents as $consent ) {
                if ( empty( $consent['document_id'] ) || empty( $consent['label'] ) ) {
                    continue;
                }
                $consent_html .= codeweber_forms_render_consent_checkbox( $consent, 'form_consents', 0 );
            }
        }
        $consent_block = $consent_html ? '<div class="mb-3">' . $consent_html . '</div>' : '';

        return '<form id="' . esc_attr( $form_unique_id ) . '"
            class="codeweber-form needs-validation"
            data-form-id="0"
            data-form-type="faq"
            data-form-name="' . esc_attr__( 'FAQ Question Form', 'codeweber' ) . '"
            data-handled-by="codeweber-forms-universal"
            method="post"
            novalidate>

            <input type="hidden" name="form_id" value="0">
            <input type="hidden" name="faq_nonce" value="' . esc_attr( $nonce_value ) . '">
            <input type="text" name="form_honeypot" class="d-none" tabindex="-1" autocomplete="off">

            ' . $fields . '
            ' . $consent_block . '
            <div class="faq-form-messages mb-3" style="display:none;"></div>

            <button type="submit"
                class="' . esc_attr( $btn_class ) . '"
                data-loading-text="' . esc_attr__( 'Sending...', 'codeweber' ) . '">
                ' . esc_html__( 'Ask a Question', 'codeweber' ) . '
            </button>
        </form>';
    }

    /**
     * Get default newsletter form HTML
     * 
     * Возвращает HTML шаблон default формы подписки на рассылку
     * 
     * @param bool $is_logged_in Is user logged in (for user_id hidden field)
     * @param int $user_id User ID if logged in (for user_id hidden field)
     * @return string HTML of the form
     */
    private function get_default_newsletter_form_html($is_logged_in = false, $user_id = 0) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Default Forms] Generating newsletter form HTML, is_logged_in: ' . ($is_logged_in ? 'true' : 'false') . ', user_id: ' . $user_id);
        }
        
        // Генерируем уникальный ID формы для каждого экземпляра на странице
        self::$global_form_instance_counter++;
        $form_unique_id = 'form-0-' . self::$global_form_instance_counter;
        
        // Получаем классы скругления из темы (используем getThemeButton для обоих элементов)
        $button_radius_class = class_exists('Codeweber_Options') ? Codeweber_Options::style('button') : '';
        
        // Генерируем nonce поле (для REST API используем wp_create_nonce)
        $nonce_value = wp_create_nonce('codeweber_form_submit');
        $nonce_field = '<input type="hidden" name="nonce" value="' . esc_attr($nonce_value) . '">';
        
        // Генерируем user_id поле, если пользователь залогинен
        $user_id_field = '';
        if ($is_logged_in && $user_id > 0) {
            $user_id_field = '<input type="hidden" name="user_id" value="' . esc_attr($user_id) . '">';
        }
        
        // Переводы
        $label_email = __('Email', 'codeweber');
        $placeholder_email = __('Email Address', 'codeweber');
        $button_text = __('Subscribe', 'codeweber');
        $button_loading_text = __('Sending...', 'codeweber');
        
        // Уникальный ID для поля email
        $field_email_id = 'field-email-' . self::$global_form_instance_counter;
        
        // Собираем классы для input (используем тот же класс, что и для кнопки)
        $input_class = 'form-control required email';
        if ($button_radius_class) {
            $input_class .= ' ' . $button_radius_class;
        }
        
        // Собираем классы для кнопки
        $button_class = 'btn btn-primary has-ripple';
        if ($button_radius_class) {
            $button_class .= ' ' . $button_radius_class;
        }
        
        // Основной HTML шаблон
        $html = '<form id="' . esc_attr($form_unique_id) . '" class="codeweber-form newsletter-subscription-form needs-validation" data-form-id="0" data-form-type="newsletter" data-form-name="' . esc_attr(__('Default Newsletter Form', 'codeweber')) . '" data-handled-by="codeweber-forms-universal" method="post" enctype="multipart/form-data" novalidate="">
            ' . $nonce_field . '
            <input type="hidden" name="form_id" value="0">
            ' . $user_id_field . '
            <input type="hidden" name="form_honeypot" value="">
            
            <div class="input-group form-floating">
                <input type="email" class="' . esc_attr($input_class) . '" id="' . esc_attr($field_email_id) . '" name="email" placeholder="' . esc_attr($placeholder_email) . '" required="" autocomplete="off">
                <label for="' . esc_attr($field_email_id) . '">
                    ' . esc_html($label_email) . ' <span class="text-danger">*</span>
                </label>
                <input type="submit" value="' . esc_attr($button_text) . '" class="' . esc_attr($button_class) . '" data-loading-text="' . esc_attr($button_loading_text) . '">
            </div>
        </form>';
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Default Forms] Generated newsletter form HTML length: ' . strlen($html));
        }
        
        return $html;
    }
}










