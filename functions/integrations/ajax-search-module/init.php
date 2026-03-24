<?php
/**
 * Ajax Search Module — точка входа.
 *
 * Поиск, статистика запросов, интеграция с Matomo.
 */

defined( 'ABSPATH' ) || exit;

$_search_dir = get_template_directory() . '/functions/integrations/ajax-search-module/';

require_once $_search_dir . 'ajax-search.php';
require_once $_search_dir . 'search-statistics.php';
require_once $_search_dir . 'matomo-search-integration.php';
