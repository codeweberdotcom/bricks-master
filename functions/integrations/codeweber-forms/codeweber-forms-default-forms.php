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
        }
        
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
        $form_radius_class = function_exists('getThemeFormRadius') ? getThemeFormRadius() : '';
        $button_radius_class = function_exists('getThemeButton') ? getThemeButton() : '';
        
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
        
        // Получаем классы скругления из темы
        $form_radius_class = function_exists('getThemeFormRadius') ? getThemeFormRadius() : '';
        $button_radius_class = function_exists('getThemeButton') ? getThemeButton() : '';
        
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
        
        // Собираем классы для input
        $input_class = 'form-control required email';
        if ($form_radius_class) {
            $input_class .= ' ' . $form_radius_class;
        }
        
        // Собираем классы для кнопки
        $button_class = 'btn btn-primary';
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
