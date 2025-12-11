<?php
/**
 * Success Message Template
 * 
 * Единый шаблон для отображения сообщения об успешной отправке
 * Используется для CF7 форм и отправки документов на email
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Генерирует HTML шаблона успешной отправки
 * 
 * @param string $message Текст сообщения (опционально)
 * @param string $icon_type Тип иконки: 'svg' (по умолчанию) или 'icon'
 * @return string HTML шаблона
 */
function codeweber_get_success_message_template($message = '', $icon_type = 'svg') {
    // Если сообщение не указано, используем стандартное
    if (empty($message)) {
        $message = __('Message sent successfully.', 'codeweber');
    }
    
    ob_start();
    ?>
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <?php if ($icon_type === 'svg') : ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 395.7" class="mb-3 svg-inject icon-svg icon-svg-lg text-primary">
                        <path class="lineal-stroke" d="M483.6 395.7H53.3C23.9 395.7 0 371.9 0 342.4V53.3C0 23.9 23.9 0 53.3 0h405.4C488.1 0 512 23.9 512 53.3v222.8c0 7.9-6.4 14.2-14.2 14.2s-14.2-6.4-14.2-14.2V53.3c0-13.7-11.1-24.8-24.8-24.8H53.3c-13.7 0-24.8 11.1-24.8 24.8v289.2c0 13.7 11.1 24.8 24.8 24.8h430.3c7.9.2 14.1 6.7 13.8 14.6-.2 7.5-6.3 13.6-13.8 13.8z"></path>
                        <path class="lineal-fill" d="M497.8 53.3L256 236.4 14.2 53.3c0-21.6 17.5-39.1 39.1-39.1h405.4c21.6 0 39.1 17.5 39.1 39.1z"></path>
                        <path class="lineal-stroke" d="M256 250.6c-3.1 0-6.1-1-8.6-2.9L5.6 64.6C2.1 61.9 0 57.7 0 53.3 0 23.9 23.9 0 53.3 0h405.4C488.1 0 512 23.9 512 53.3c0 4.4-2.1 8.6-5.6 11.3L264.6 247.7c-2.5 1.9-5.5 2.9-8.6 2.9zM29.3 46.8L256 218.6 482.7 46.8c-2.9-10.9-12.8-18.4-24-18.4H53.3c-11.3.1-21.1 7.6-24 18.4zm454.2 348.7c-3.1 0-6.1-1-8.6-2.9l-99.6-75.4c-6.3-4.7-7.5-13.7-2.7-19.9 4.7-6.3 13.7-7.5 19.9-2.7l99.6 75.4c6.3 4.7 7.5 13.7 2.8 19.9-2.7 3.6-6.9 5.7-11.4 5.6zm-449-4.6c-7.9 0-14.2-6.4-14.2-14.2 0-4.5 2.1-8.7 5.6-11.4l93.5-70.8c6.3-4.7 15.2-3.5 19.9 2.7 4.7 6.3 3.5 15.2-2.7 19.9L43.1 388c-2.5 1.9-5.5 2.9-8.6 2.9z"></path>
                    </svg>
                <?php else : ?>
                    <div class="mb-3">
                        <i class="uil uil-check-circle text-success" style="font-size: 3rem;"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-12 text-center">
                <div class="card-title h4"><?php echo esc_html($message); ?></div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Генерирует HTML шаблона успешной отправки для модального окна (полная версия)
 * 
 * @param string $message Текст сообщения (опционально)
 * @param string $icon_type Тип иконки: 'svg' (по умолчанию) или 'icon'
 * @return string HTML шаблона
 */
function codeweber_get_success_message_modal_template($message = '', $icon_type = 'svg') {
    $content = codeweber_get_success_message_template($message, $icon_type);
    
    ob_start();
    ?>
    <div class="modal-body align-content-center">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php esc_attr_e('Close', 'codeweber'); ?>"></button>
        <?php echo $content; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * REST API endpoint для получения шаблона успешной отправки
 */
function register_success_message_template_endpoint() {
	register_rest_route('codeweber/v1', '/success-message-template', [
		'methods' => 'GET',
		'callback' => 'get_success_message_template_api',
		'permission_callback' => '__return_true',
		'args' => [
			'message' => [
				'required' => false,
				'sanitize_callback' => 'sanitize_text_field'
			],
			'icon_type' => [
				'required' => false,
				'sanitize_callback' => 'sanitize_text_field',
				'default' => 'svg'
			]
		]
	]);
}
add_action('rest_api_init', 'register_success_message_template_endpoint');

/**
 * Callback для REST API получения шаблона успешной отправки
 * 
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function get_success_message_template_api($request) {
	$message = $request->get_param('message');
	$icon_type = $request->get_param('icon_type') ?: 'svg';
	
	$template = codeweber_get_success_message_template($message, $icon_type);
	
	return rest_ensure_response([
		'success' => true,
		'html' => $template
	]);
}

