<?php
/**
 * PDF Thumbnail — точка входа.
 *
 * Генерация превью PDF, установка как featured image, JS метабокс.
 */

defined( 'ABSPATH' ) || exit;

$_pdf_dir = get_template_directory() . '/functions/lib/pdf-thumbnail/';

require_once $_pdf_dir . 'pdf-thumbnail-install.php';
require_once $_pdf_dir . 'pdf-thumbnail.php';
require_once $_pdf_dir . 'pdf-thumbnail-js.php';
