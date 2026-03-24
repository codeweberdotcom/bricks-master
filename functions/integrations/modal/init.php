<?php
/**
 * Modal — точка входа.
 *
 * Универсальный Bootstrap-контейнер, REST API расширения
 * и единый шаблон сообщения об успешной отправке.
 */

defined( 'ABSPATH' ) || exit;

$_modal_dir = get_template_directory() . '/functions/integrations/modal/';

require_once $_modal_dir . 'modal-container.php';
require_once $_modal_dir . 'modal-rest-api.php';
require_once $_modal_dir . 'success-message-template.php';
