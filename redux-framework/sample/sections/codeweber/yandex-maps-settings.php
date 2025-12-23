<?php
/**
 * Redux Settings for Yandex Maps
 * 
 * Настройки Яндекс карт для темы
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

// Получение API ключа из настроек Redux
$yandex_api_key = Redux::get_option($opt_name, 'yandexapi');

Redux::set_section(
    $opt_name,
    array(
        'title'            => esc_html__("Yandex Maps Settings", "codeweber"),
        'id'               => 'yandex_maps_settings',
        'desc'             => esc_html__("Settings for Yandex Maps integration", "codeweber"),
        'customizer_width' => '300px',
        'icon'             => 'el el-map-marker',
        'subsection'       => true,
        'parent'           => 'geomap',
        'fields'           => array(
            
            // Основные настройки
            array(
                'id'       => 'yandex_maps_section_general',
                'type'     => 'section',
                'title'    => esc_html__('General Settings', 'codeweber'),
                'indent'   => true,
            ),
            
            array(
                'id'       => 'yandex_maps_default_center',
                'type'     => 'text',
                'title'    => esc_html__('Default Center', 'codeweber'),
                'subtitle'  => esc_html__('Default map center coordinates (latitude, longitude)', 'codeweber'),
                'desc'     => esc_html__('Example: 55.76, 37.64', 'codeweber'),
                'default'  => '55.76, 37.64',
            ),
            
            array(
                'id'       => 'yandex_maps_default_zoom',
                'type'     => 'slider',
                'title'    => esc_html__('Default Zoom', 'codeweber'),
                'subtitle' => esc_html__('Default zoom level', 'codeweber'),
                'default'  => 10,
                'min'      => 1,
                'max'      => 19,
                'step'     => 1,
            ),
            
            array(
                'id'       => 'yandex_maps_default_type',
                'type'     => 'select',
                'title'    => esc_html__('Default Map Type', 'codeweber'),
                'subtitle' => esc_html__('Default type of map', 'codeweber'),
                'options'  => array(
                    'yandex#map' => esc_html__('Map', 'codeweber'),
                    'yandex#satellite' => esc_html__('Satellite', 'codeweber'),
                    'yandex#hybrid' => esc_html__('Hybrid', 'codeweber'),
                ),
                'default'  => 'yandex#map',
            ),
            
            array(
                'id'       => 'yandex_maps_default_height',
                'type'     => 'text',
                'title'    => esc_html__('Default Height', 'codeweber'),
                'subtitle' => esc_html__('Default map height in pixels', 'codeweber'),
                'default'  => '500',
            ),
            
            // Поведение карты
            array(
                'id'       => 'yandex_maps_section_behaviors',
                'type'     => 'section',
                'title'    => esc_html__('Map Behaviors', 'codeweber'),
                'indent'   => true,
            ),
            array(
                'id'       => 'yandex_maps_enable_dbl_click_zoom',
                'type'     => 'switch',
                'title'    => esc_html__('Enable Double Click Zoom', 'codeweber'),
                'subtitle' => esc_html__('Allow zooming by double-clicking on the map', 'codeweber'),
                'default'  => true,
            ),
            array(
                'id'       => 'yandex_maps_enable_multi_touch',
                'type'     => 'switch',
                'title'    => esc_html__('Enable Multi-touch', 'codeweber'),
                'subtitle' => esc_html__('Enable multi-touch gestures on mobile devices', 'codeweber'),
                'default'  => true,
            ),
            
            // Элементы управления картой
            array(
                'id'       => 'yandex_maps_section_controls',
                'type'     => 'section',
                'title'    => esc_html__('Map Controls', 'codeweber'),
                'indent'   => true,
            ),
            array(
                'id'       => 'yandex_maps_geolocation_control',
                'type'     => 'switch',
                'title'    => esc_html__('Show Geolocation Control', 'codeweber'),
                'subtitle' => esc_html__('Show "My location" button on the map', 'codeweber'),
                'default'  => false,
            ),
            array(
                'id'       => 'yandex_maps_route_button',
                'type'     => 'switch',
                'title'    => esc_html__('Show Route Button', 'codeweber'),
                'subtitle' => esc_html__('Show button to build route on the map', 'codeweber'),
                'default'  => false,
            ),
            array(
                'id'       => 'yandex_maps_search_control',
                'type'     => 'switch',
                'title'    => esc_html__('Show Search Control', 'codeweber'),
                'subtitle' => esc_html__('Show search box on the map', 'codeweber'),
                'default'  => false, // По умолчанию скрыт
            ),
            
            // Настройки маркеров
            array(
                'id'       => 'yandex_maps_section_markers',
                'type'     => 'section',
                'title'    => esc_html__('Marker Settings', 'codeweber'),
                'indent'   => true,
            ),
            
            array(
                'id'       => 'yandex_maps_marker_type',
                'type'     => 'select',
                'title'    => esc_html__('Default Marker Type', 'codeweber'),
                'subtitle' => esc_html__('Default type of marker', 'codeweber'),
                'options'  => array(
                    'default' => esc_html__('Default (Preset)', 'codeweber'),
                    'custom' => esc_html__('Custom Color', 'codeweber'),
                    'logo' => esc_html__('Logo', 'codeweber'),
                ),
                'default'  => 'default',
            ),
            
            array(
                'id'       => 'yandex_maps_marker_preset',
                'type'     => 'select',
                'title'    => esc_html__('Marker Preset', 'codeweber'),
                'subtitle' => esc_html__('Yandex Maps marker preset', 'codeweber'),
                'options'  => array(
                    'islands#redDotIcon' => esc_html__('Red Dot', 'codeweber'),
                    'islands#blueDotIcon' => esc_html__('Blue Dot', 'codeweber'),
                    'islands#greenDotIcon' => esc_html__('Green Dot', 'codeweber'),
                    'islands#violetDotIcon' => esc_html__('Violet Dot', 'codeweber'),
                    'islands#darkBlueDotIcon' => esc_html__('Dark Blue Dot', 'codeweber'),
                    'islands#grayDotIcon' => esc_html__('Gray Dot', 'codeweber'),
                    'islands#redIcon' => esc_html__('Red Icon', 'codeweber'),
                    'islands#blueIcon' => esc_html__('Blue Icon', 'codeweber'),
                    'islands#greenIcon' => esc_html__('Green Icon', 'codeweber'),
                ),
                'default'  => 'islands#redDotIcon',
                'required' => array('yandex_maps_marker_type', '=', 'default'),
            ),
            
            array(
                'id'       => 'yandex_maps_marker_color',
                'type'     => 'color',
                'title'    => esc_html__('Marker Color', 'codeweber'),
                'subtitle' => esc_html__('Color for custom markers', 'codeweber'),
                'default'  => '#FF0000',
                'required' => array('yandex_maps_marker_type', '=', 'custom'),
            ),
            
            array(
                'id'       => 'yandex_maps_marker_logo',
                'type'     => 'media',
                'title'    => esc_html__('Marker Logo', 'codeweber'),
                'subtitle' => esc_html__('Logo to use instead of marker', 'codeweber'),
                'required' => array('yandex_maps_marker_type', '=', 'logo'),
            ),
            
            array(
                'id'       => 'yandex_maps_marker_logo_size',
                'type'     => 'slider',
                'title'    => esc_html__('Logo Size', 'codeweber'),
                'subtitle' => esc_html__('Size of logo in marker (pixels)', 'codeweber'),
                'default'  => 40,
                'min'      => 20,
                'max'      => 100,
                'step'     => 5,
                'required' => array('yandex_maps_marker_type', '=', 'logo'),
            ),
            array(
                'id'       => 'yandex_maps_marker_open_balloon_on_click',
                'type'     => 'switch',
                'title'    => esc_html__('Open Balloon on Click', 'codeweber'),
                'subtitle' => esc_html__('Open balloon when marker is clicked', 'codeweber'),
                'default'  => true,
            ),
            array(
                'id'       => 'yandex_maps_marker_auto_open_balloon',
                'type'     => 'switch',
                'title'    => esc_html__('Auto Open First Balloon', 'codeweber'),
                'subtitle' => esc_html__('Automatically open balloon for the first marker', 'codeweber'),
                'default'  => false,
            ),
            
            // Настройки кластеризации
            array(
                'id'       => 'yandex_maps_section_clusterer',
                'type'     => 'section',
                'title'    => esc_html__('Clusterer Settings', 'codeweber'),
                'indent'   => true,
            ),
            
            array(
                'id'       => 'yandex_maps_clusterer_enabled',
                'type'     => 'switch',
                'title'    => esc_html__('Enable Clusterer', 'codeweber'),
                'subtitle' => esc_html__('Group nearby markers into clusters', 'codeweber'),
                'default'  => false,
            ),
            
            array(
                'id'       => 'yandex_maps_clusterer_preset',
                'type'     => 'select',
                'title'    => esc_html__('Clusterer Preset', 'codeweber'),
                'subtitle' => esc_html__('Style of cluster icons', 'codeweber'),
                'options'  => array(
                    'islands#invertedVioletClusterIcons' => esc_html__('Violet', 'codeweber'),
                    'islands#invertedBlueClusterIcons' => esc_html__('Blue', 'codeweber'),
                    'islands#invertedRedClusterIcons' => esc_html__('Red', 'codeweber'),
                    'islands#invertedGreenClusterIcons' => esc_html__('Green', 'codeweber'),
                ),
                'default'  => 'islands#invertedVioletClusterIcons',
                'required' => array('yandex_maps_clusterer_enabled', '=', true),
            ),
            
            // Настройки бокового меню
            array(
                'id'       => 'yandex_maps_section_sidebar',
                'type'     => 'section',
                'title'    => esc_html__('Sidebar Settings', 'codeweber'),
                'indent'   => true,
            ),
            
            array(
                'id'       => 'yandex_maps_sidebar_enabled',
                'type'     => 'switch',
                'title'    => esc_html__('Enable Sidebar', 'codeweber'),
                'subtitle' => esc_html__('Show sidebar with list of markers', 'codeweber'),
                'default'  => false,
            ),
            
            array(
                'id'       => 'yandex_maps_sidebar_position',
                'type'     => 'button_set',
                'title'    => esc_html__('Sidebar Position', 'codeweber'),
                'subtitle' => esc_html__('Position of sidebar on map', 'codeweber'),
                'options'  => array(
                    'left' => esc_html__('Left', 'codeweber'),
                    'right' => esc_html__('Right', 'codeweber'),
                ),
                'default'  => 'left',
                'required' => array('yandex_maps_sidebar_enabled', '=', true),
            ),
            
            array(
                'id'       => 'yandex_maps_sidebar_title',
                'type'     => 'text',
                'title'    => esc_html__('Sidebar Title', 'codeweber'),
                'subtitle' => esc_html__('Title for sidebar', 'codeweber'),
                'default'  => esc_html__('Offices', 'codeweber'),
                'required' => array('yandex_maps_sidebar_enabled', '=', true),
            ),
            
            // Настройки фильтров
            array(
                'id'       => 'yandex_maps_section_filters',
                'type'     => 'section',
                'title'    => esc_html__('Filter Settings', 'codeweber'),
                'indent'   => true,
            ),
            
            array(
                'id'       => 'yandex_maps_filters_enabled',
                'type'     => 'switch',
                'title'    => esc_html__('Enable Filters', 'codeweber'),
                'subtitle' => esc_html__('Show filters in sidebar', 'codeweber'),
                'default'  => false,
            ),
            
            array(
                'id'       => 'yandex_maps_filter_by_city',
                'type'     => 'switch',
                'title'    => esc_html__('Filter by City', 'codeweber'),
                'subtitle' => esc_html__('Enable city filter', 'codeweber'),
                'default'  => true,
                'required' => array('yandex_maps_filters_enabled', '=', true),
            ),
            
            array(
                'id'       => 'yandex_maps_filter_by_category',
                'type'     => 'switch',
                'title'    => esc_html__('Filter by Category', 'codeweber'),
                'subtitle' => esc_html__('Enable category filter', 'codeweber'),
                'default'  => false,
                'required' => array('yandex_maps_filters_enabled', '=', true),
            ),
            
            // Настройки маршрутов
            array(
                'id'       => 'yandex_maps_section_route',
                'type'     => 'section',
                'title'    => esc_html__('Route Settings', 'codeweber'),
                'indent'   => true,
            ),
            
            array(
                'id'       => 'yandex_maps_route_enabled',
                'type'     => 'switch',
                'title'    => esc_html__('Enable Route', 'codeweber'),
                'subtitle' => esc_html__('Allow building routes on map', 'codeweber'),
                'default'  => false,
            ),
            
            array(
                'id'       => 'yandex_maps_route_type',
                'type'     => 'select',
                'title'    => esc_html__('Default Route Type', 'codeweber'),
                'subtitle' => esc_html__('Default type of route', 'codeweber'),
                'options'  => array(
                    'auto' => esc_html__('Auto', 'codeweber'),
                    'pedestrian' => esc_html__('Pedestrian', 'codeweber'),
                    'masstransit' => esc_html__('Public Transport', 'codeweber'),
                    'bicycle' => esc_html__('Bicycle', 'codeweber'),
                ),
                'default'  => 'auto',
                'required' => array('yandex_maps_route_enabled', '=', true),
            ),
            // Настройки балунов
            array(
                'id'       => 'yandex_maps_section_balloon',
                'type'     => 'section',
                'title'    => esc_html__('Balloon Settings', 'codeweber'),
                'indent'   => true,
            ),
            
            array(
                'id'       => 'yandex_maps_balloon_max_width',
                'type'     => 'text',
                'title'    => esc_html__('Balloon Max Width', 'codeweber'),
                'subtitle' => esc_html__('Maximum width of balloon in pixels', 'codeweber'),
                'default'  => '300',
            ),
            array(
                'id'       => 'yandex_maps_balloon_close_button',
                'type'     => 'switch',
                'title'    => esc_html__('Show Close Button', 'codeweber'),
                'subtitle' => esc_html__('Show close button on balloon', 'codeweber'),
                'default'  => true,
            ),
            array(
                'id'       => 'yandex_maps_balloon_auto_pan',
                'type'     => 'switch',
                'title'    => esc_html__('Balloon Auto Pan', 'codeweber'),
                'subtitle' => esc_html__('Automatically pan map when balloon is opened', 'codeweber'),
                'default'  => true,
            ),
            
            // Дополнительные настройки (UI / Performance)
            array(
                'id'       => 'yandex_maps_section_advanced',
                'type'     => 'section',
                'title'    => esc_html__('Advanced Settings', 'codeweber'),
                'indent'   => true,
            ),
            array(
                'id'       => 'yandex_maps_lazy_load',
                'type'     => 'switch',
                'title'    => esc_html__('Lazy Load', 'codeweber'),
                'subtitle' => esc_html__('Initialize maps only when they become visible', 'codeweber'),
                'default'  => false,
            ),
            array(
                'id'       => 'yandex_maps_screen_reader_support',
                'type'     => 'switch',
                'title'    => esc_html__('Screen Reader Support', 'codeweber'),
                'subtitle' => esc_html__('Improve accessibility for screen readers', 'codeweber'),
                'default'  => true,
            ),
            array(
                'id'       => 'yandex_maps_responsive',
                'type'     => 'switch',
                'title'    => esc_html__('Responsive Map', 'codeweber'),
                'subtitle' => esc_html__('Optimize map for different screen sizes', 'codeweber'),
                'default'  => true,
            ),
            array(
                'id'       => 'yandex_maps_mobile_optimized',
                'type'     => 'switch',
                'title'    => esc_html__('Mobile Optimized', 'codeweber'),
                'subtitle' => esc_html__('Optimize map for mobile devices', 'codeweber'),
                'default'  => true,
            ),
            array(
                'id'       => 'yandex_maps_touch_optimized',
                'type'     => 'switch',
                'title'    => esc_html__('Touch Optimized', 'codeweber'),
                'subtitle' => esc_html__('Optimize map for touch interactions', 'codeweber'),
                'default'  => true,
            ),
            array(
                'id'       => 'yandex_maps_desktop_optimized',
                'type'     => 'switch',
                'title'    => esc_html__('Desktop Optimized', 'codeweber'),
                'subtitle' => esc_html__('Optimize map for desktop devices', 'codeweber'),
                'default'  => true,
            ),
            array(
                'id'       => 'yandex_maps_custom_style',
                'type'     => 'textarea',
                'title'    => esc_html__('Custom Style (CSS)', 'codeweber'),
                'subtitle' => esc_html__('Custom CSS styles for map wrapper', 'codeweber'),
                'default'  => '',
            ),
            array(
                'id'       => 'yandex_maps_style_json',
                'type'     => 'textarea',
                'title'    => esc_html__('Custom Style (JSON)', 'codeweber'),
                'subtitle' => esc_html__('Custom JSON style configuration for Yandex Map (advanced)', 'codeweber'),
                'default'  => '',
            ),
            
        ),
    )
);


