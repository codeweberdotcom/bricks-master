<?php
/**
 * OpenStreetMap (Leaflet) module init
 *
 * @package Codeweber
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/functions/integrations/openstreet-map/class-codeweber-openstreet-map.php';

add_action( 'after_setup_theme', function () {
	Codeweber_OpenStreet_Map::get_instance();
}, 40 );
