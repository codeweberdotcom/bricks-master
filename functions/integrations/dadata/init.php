<?php
/**
 * DaData — точка входа.
 *
 * Стандартизация адресов: REST-клиент и AJAX-обработчики.
 */

defined( 'ABSPATH' ) || exit;

$_dadata_dir = get_template_directory() . '/functions/integrations/dadata/';

require_once $_dadata_dir . 'class-codeweber-dadata.php';
require_once $_dadata_dir . 'dadata-ajax.php';
