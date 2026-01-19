<?php
/**
 * CodeWeber Yandex Maps Class
 * 
 * Класс для работы с Яндекс картами в теме
 * Поддерживает множество настроек для использования в шаблонах и Гутенберг блоках
 * 
 * @package Codeweber
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Codeweber_Yandex_Maps {
    
    /**
     * @var Codeweber_Yandex_Maps Экземпляр класса (Singleton)
     */
    private static $instance = null;
    
    /**
     * @var string Версия модуля
     */
    private $version = '1.0.0';
    
    /**
     * @var string Путь к модулю
     */
    private $path;
    
    /**
     * @var string URL модуля
     */
    private $url;
    
    /**
     * @var string API ключ Яндекс карт
     */
    private $api_key;
    
    /**
     * @var array Настройки по умолчанию
     */
    private $default_settings = array(
        'api_key' => '',
        'center' => array(55.76, 37.64), // Москва
        'zoom' => 10,
        'language' => 'ru_RU',
        'controls' => array('zoomControl', 'searchControl', 'typeSelector', 'fullscreenControl'),
        // Поведение карты
        'enable_dbl_click_zoom' => true,
        'enable_multi_touch' => true,
        // Дополнительные контролы
        'geolocation_control' => false,
        'route_button' => false,
        'search_control' => false, // По умолчанию скрыт
        'marker_type' => 'default', // default, custom, logo
        'marker_preset' => 'islands#redDotIcon',
        'marker_color' => '#FF0000',
        'marker_logo' => '',
        'marker_logo_size' => 40,
        'show_sidebar' => false,
        'sidebar_position' => 'left', // left, right
        'sidebar_title' => '',
        'show_filters' => false,
        'filter_by_city' => false,
        'filter_by_category' => false,
        'show_route' => false,
        'route_type' => 'auto', // auto, pedestrian, masstransit, bicycle
        'route_start_point' => '',
        'auto_fit_bounds' => true,
        'clusterer' => false,
        'clusterer_preset' => 'islands#invertedVioletClusterIcons',
        'balloon_template' => 'default',
        'balloon_max_width' => 300,
        'map_type' => 'yandex#map', // yandex#map, yandex#satellite, yandex#hybrid
        'height' => 500,
        'width' => '100%',
        'border_radius' => 8,
        'enable_scroll_zoom' => true,
        'enable_drag' => true,
        // Маркеры и балуны
        'marker_open_balloon_on_click' => true,
        'marker_auto_open_balloon' => false,
        'marker_hint_auto_pan' => true,
        'marker_balloon_auto_pan' => true,
        'balloon_close_button' => true,
        'balloon_auto_pan' => true,
        'balloon_layout' => 'default',
        'balloon_content_layout' => 'default',
        // Оптимизация и адаптивность
        'lazy_load' => false,
        'screen_reader_support' => true,
        'custom_style' => '',
        'style_json' => '',
        'responsive' => true,
        'mobile_optimized' => true,
        'touch_optimized' => true,
        'desktop_optimized' => true,
    );
    
    /**
     * Получить экземпляр класса (Singleton)
     * 
     * @return Codeweber_Yandex_Maps
     */
    public static function get_instance(): Codeweber_Yandex_Maps {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Конструктор
     */
    private function __construct() {
        $this->path = __DIR__;
        $this->url = get_template_directory_uri() . '/functions/integrations/yandex-maps';
        
        // Получаем API ключ и настройки из Redux
        global $opt_name;
        if (empty($opt_name)) {
            $opt_name = 'redux_demo';
        }
        
        if (class_exists('Redux')) {
            $this->api_key = Redux::get_option($opt_name, 'yandexapi');
            // Загружаем настройки из Redux
            $this->load_redux_settings($opt_name);
        }
        
        $this->init();
    }
    
    /**
     * Загрузка настроек из Redux
     */
    private function load_redux_settings($opt_name): void {
        if (!class_exists('Redux')) {
            return;
        }
        // Основные настройки
        $default_center = Redux::get_option($opt_name, 'yandex_maps_default_center');
        if (!empty($default_center)) {
            $coords = explode(',', $default_center);
            if (count($coords) === 2) {
                $this->default_settings['center'] = array(
                    floatval(trim($coords[0])),
                    floatval(trim($coords[1]))
                );
            }
        }
        
        $default_zoom = Redux::get_option($opt_name, 'yandex_maps_default_zoom');
        if (!empty($default_zoom)) {
            $this->default_settings['zoom'] = intval($default_zoom);
        }
        
        $default_type = Redux::get_option($opt_name, 'yandex_maps_default_type');
        if (!empty($default_type)) {
            $this->default_settings['map_type'] = $default_type;
        }
        
        $default_height = Redux::get_option($opt_name, 'yandex_maps_default_height');
        if (!empty($default_height)) {
            $this->default_settings['height'] = intval($default_height);
        }
        
        // Настройки маркеров
        $marker_type = Redux::get_option($opt_name, 'yandex_maps_marker_type');
        if (!empty($marker_type)) {
            $this->default_settings['marker_type'] = $marker_type;
        }
        
        $marker_preset = Redux::get_option($opt_name, 'yandex_maps_marker_preset');
        if (!empty($marker_preset)) {
            $this->default_settings['marker_preset'] = $marker_preset;
        }
        
        $marker_color = Redux::get_option($opt_name, 'yandex_maps_marker_color');
        if (!empty($marker_color)) {
            $this->default_settings['marker_color'] = $marker_color;
        }
        
        $marker_logo = Redux::get_option($opt_name, 'yandex_maps_marker_logo');
        if (!empty($marker_logo) && is_array($marker_logo) && !empty($marker_logo['url'])) {
            $this->default_settings['marker_logo'] = $marker_logo['url'];
        }
        
        $marker_logo_size = Redux::get_option($opt_name, 'yandex_maps_marker_logo_size');
        if (!empty($marker_logo_size)) {
            $this->default_settings['marker_logo_size'] = intval($marker_logo_size);
        }
        
        // Настройки кластеризации
        $clusterer_enabled = Redux::get_option($opt_name, 'yandex_maps_clusterer_enabled');
        $this->default_settings['clusterer'] = !empty($clusterer_enabled);
        
        $clusterer_preset = Redux::get_option($opt_name, 'yandex_maps_clusterer_preset');
        if (!empty($clusterer_preset)) {
            $this->default_settings['clusterer_preset'] = $clusterer_preset;
        }
        
        // Настройки сайдбара
        $sidebar_enabled = Redux::get_option($opt_name, 'yandex_maps_sidebar_enabled');
        $this->default_settings['show_sidebar'] = !empty($sidebar_enabled);

        // #region agent log
        try {
            $log = array(
                'sessionId'    => 'debug-session',
                'runId'        => 'pre-fix',
                'hypothesisId' => 'S2',
                'location'     => 'class-codeweber-yandex-maps.php:load_redux_settings',
                'message'      => 'Sidebar settings loaded from Redux',
                'data'         => array(
                    'raw_sidebar_enabled' => $sidebar_enabled,
                    'show_sidebar'        => $this->default_settings['show_sidebar'],
                    'opt_name'            => $opt_name,
                ),
                'timestamp'    => round(microtime(true) * 1000),
            );
            @file_put_contents(ABSPATH . '.cursor/debug.log', json_encode($log) . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
            // silent
        }
        // #endregion
        
        $sidebar_position = Redux::get_option($opt_name, 'yandex_maps_sidebar_position');
        if (!empty($sidebar_position)) {
            $this->default_settings['sidebar_position'] = $sidebar_position;
        }
        
        $sidebar_title = Redux::get_option($opt_name, 'yandex_maps_sidebar_title');
        if (!empty($sidebar_title)) {
            $this->default_settings['sidebar_title'] = $sidebar_title;
        }
        
        // Настройки фильтров
        $filters_enabled = Redux::get_option($opt_name, 'yandex_maps_filters_enabled');
        $this->default_settings['show_filters'] = !empty($filters_enabled);
        
        $filter_by_city = Redux::get_option($opt_name, 'yandex_maps_filter_by_city');
        $this->default_settings['filter_by_city'] = !empty($filter_by_city);
        
        $filter_by_category = Redux::get_option($opt_name, 'yandex_maps_filter_by_category');
        $this->default_settings['filter_by_category'] = !empty($filter_by_category);
        
        // Настройки маршрутов
        $route_enabled = Redux::get_option($opt_name, 'yandex_maps_route_enabled');
        $this->default_settings['show_route'] = !empty($route_enabled);
        
        $route_type = Redux::get_option($opt_name, 'yandex_maps_route_type');
        if (!empty($route_type)) {
            $this->default_settings['route_type'] = $route_type;
        }
        
        // Настройки балунов
        $balloon_max_width = Redux::get_option($opt_name, 'yandex_maps_balloon_max_width');
        if (!empty($balloon_max_width)) {
            $this->default_settings['balloon_max_width'] = intval($balloon_max_width);
        }
        
        // Дополнительные настройки маркеров и балунов
        $marker_open_on_click = Redux::get_option($opt_name, 'yandex_maps_marker_open_balloon_on_click');
        if ($marker_open_on_click !== null) {
            $this->default_settings['marker_open_balloon_on_click'] = (bool) $marker_open_on_click;
        }
        $marker_auto_open = Redux::get_option($opt_name, 'yandex_maps_marker_auto_open_balloon');
        if ($marker_auto_open !== null) {
            $this->default_settings['marker_auto_open_balloon'] = (bool) $marker_auto_open;
        }
        $balloon_close_button = Redux::get_option($opt_name, 'yandex_maps_balloon_close_button');
        if ($balloon_close_button !== null) {
            $this->default_settings['balloon_close_button'] = (bool) $balloon_close_button;
        }
        $balloon_auto_pan = Redux::get_option($opt_name, 'yandex_maps_balloon_auto_pan');
        if ($balloon_auto_pan !== null) {
            $this->default_settings['balloon_auto_pan'] = (bool) $balloon_auto_pan;
        }
        
        // Поведение карты
        $dbl_click_zoom = Redux::get_option($opt_name, 'yandex_maps_enable_dbl_click_zoom');
        if ($dbl_click_zoom !== null) {
            $this->default_settings['enable_dbl_click_zoom'] = (bool) $dbl_click_zoom;
        }
        $multi_touch = Redux::get_option($opt_name, 'yandex_maps_enable_multi_touch');
        if ($multi_touch !== null) {
            $this->default_settings['enable_multi_touch'] = (bool) $multi_touch;
        }
        
        // Элементы управления картой
        $geolocation_control = Redux::get_option($opt_name, 'yandex_maps_geolocation_control');
        if ($geolocation_control !== null) {
            $this->default_settings['geolocation_control'] = (bool) $geolocation_control;
        }
        $route_button = Redux::get_option($opt_name, 'yandex_maps_route_button');
        if ($route_button !== null) {
            $this->default_settings['route_button'] = (bool) $route_button;
        }
        $search_control = Redux::get_option($opt_name, 'yandex_maps_search_control');
        if ($search_control !== null) {
            $this->default_settings['search_control'] = (bool) $search_control;
        }
        
        // Advanced / UI
        $lazy_load = Redux::get_option($opt_name, 'yandex_maps_lazy_load');
        if ($lazy_load !== null) {
            $this->default_settings['lazy_load'] = (bool) $lazy_load;
        }
        $screen_reader = Redux::get_option($opt_name, 'yandex_maps_screen_reader_support');
        if ($screen_reader !== null) {
            $this->default_settings['screen_reader_support'] = (bool) $screen_reader;
        }
        $responsive = Redux::get_option($opt_name, 'yandex_maps_responsive');
        if ($responsive !== null) {
            $this->default_settings['responsive'] = (bool) $responsive;
        }
        $mobile_optimized = Redux::get_option($opt_name, 'yandex_maps_mobile_optimized');
        if ($mobile_optimized !== null) {
            $this->default_settings['mobile_optimized'] = (bool) $mobile_optimized;
        }
        $touch_optimized = Redux::get_option($opt_name, 'yandex_maps_touch_optimized');
        if ($touch_optimized !== null) {
            $this->default_settings['touch_optimized'] = (bool) $touch_optimized;
        }
        $desktop_optimized = Redux::get_option($opt_name, 'yandex_maps_desktop_optimized');
        if ($desktop_optimized !== null) {
            $this->default_settings['desktop_optimized'] = (bool) $desktop_optimized;
        }
        $custom_style = Redux::get_option($opt_name, 'yandex_maps_custom_style');
        if (!empty($custom_style)) {
            $this->default_settings['custom_style'] = $custom_style;
        }
        $style_json = Redux::get_option($opt_name, 'yandex_maps_style_json');
        if (!empty($style_json)) {
            $this->default_settings['style_json'] = $style_json;
        }
    }
    
    /**
     * Инициализация
     */
    private function init(): void {
        // Подключаем скрипты и стили
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Хук для регистрации настроек
        do_action('codeweber_yandex_maps_init', $this);
    }
    
    /**
     * Подключение скриптов и стилей на фронтенде
     */
    public function enqueue_scripts(): void {
        // #region agent log
        try {
            $log = array(
                'sessionId' => 'debug-session',
                'runId'     => 'pre-fix',
                'hypothesisId' => 'P1',
                'location'  => 'class-codeweber-yandex-maps.php:enqueue_scripts',
                'message'   => 'Enqueue Yandex Maps assets (before has_api_key)',
                'data'      => array(
                    'raw_api_key_empty' => empty($this->api_key),
                ),
                'timestamp' => round(microtime(true) * 1000),
            );
            @file_put_contents(ABSPATH . '.cursor/debug.log', json_encode($log) . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
            // silent
        }
        // #endregion

        // Гарантированно обновляем ключ из Redux перед подключением скрипта
        $has_key = $this->has_api_key();

        // #region agent log
        try {
            $log = array(
                'sessionId' => 'debug-session',
                'runId'     => 'pre-fix',
                'hypothesisId' => 'P1',
                'location'  => 'class-codeweber-yandex-maps.php:enqueue_scripts',
                'message'   => 'Enqueue Yandex Maps assets (after has_api_key)',
                'data'      => array(
                    'has_api_key' => $has_key,
                ),
                'timestamp' => round(microtime(true) * 1000),
            );
            @file_put_contents(ABSPATH . '.cursor/debug.log', json_encode($log) . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
            // silent
        }
        // #endregion

        // Подключаем Яндекс Maps API только если есть ключ
        // Используем API 3.0 для поддержки нового формата JSON (tags/elements/stylers)
        // Загружаем динамически через JavaScript для обработки ошибок
        if ($has_key && !empty($this->api_key)) {
            // Не подключаем API здесь - будем загружать динамически через JavaScript
            // Это позволит обработать ошибки загрузки
        }
        
        // Подключаем наш JavaScript (не зависит от API, API будет загружен динамически)
        wp_enqueue_script(
            'codeweber-yandex-maps',
            $this->url . '/assets/js/yandex-maps.js',
            array(), // Убираем зависимость от yandex-maps-api
            $this->version,
            true
        );
        
        // Локализация скрипта
        wp_localize_script('codeweber-yandex-maps', 'codeweberYandexMaps', array(
            'apiKey' => $this->api_key,
            'language' => $this->default_settings['language'],
            'defaultCenter' => $this->default_settings['center'],
            'defaultZoom' => $this->default_settings['zoom'],
            'i18n' => array(
                'route' => __('Route', 'codeweber'),
                'buildRoute' => __('Build Route', 'codeweber'),
                'from' => __('From', 'codeweber'),
                'to' => __('To', 'codeweber'),
                'filterByCity' => __('Filter by City', 'codeweber'),
                'allCities' => __('All Cities', 'codeweber'),
                'offices' => __('Offices', 'codeweber'),
                'city' => __('City', 'codeweber'),
                'address' => __('Address', 'codeweber'),
                'phone' => __('Phone', 'codeweber'),
                'workingHours' => __('Working Hours', 'codeweber'),
                'viewDetails' => __('View Details', 'codeweber'),
            ),
        ));
        
        // Подключаем стили
        wp_enqueue_style(
            'codeweber-yandex-maps',
            $this->url . '/assets/css/yandex-maps.css',
            array(),
            $this->version
        );
    }
    
    /**
     * Подключение скриптов и стилей в админке
     */
    public function enqueue_admin_scripts(): void {
        // Подключаем стили для админки если нужно
    }
    
    /**
     * Получить API ключ
     * 
     * @return string
     */
    public function get_api_key(): string {
        return $this->api_key;
    }
    
    /**
     * Проверить, есть ли API ключ
     * 
     * @return bool
     */
    public function has_api_key(): bool {
        // Перепроверяем ключ на случай, если Redux инициализировался позже конструктора
        if (empty($this->api_key) && class_exists('Redux')) {
            global $opt_name;
            if (empty($opt_name)) {
                $opt_name = 'redux_demo';
            }
            $this->api_key = Redux::get_option($opt_name, 'yandexapi');
        }
        return !empty($this->api_key);
    }
    
    /**
     * Рендеринг карты
     * 
     * @param array $args Настройки карты
     * @param array $markers Массив маркеров
     * @return string HTML код карты
     */
    public function render_map(array $args = array(), array $markers = array()): string {
        if (!$this->has_api_key()) {
            return '<div class="alert alert-warning">' . __('Yandex Maps API key is not configured.', 'codeweber') . '</div>';
        }
        
        // Объединяем настройки с дефолтными
        $settings = wp_parse_args($args, $this->default_settings);
        
        // Генерируем уникальный ID для карты (или используем переданный)
        $map_id = !empty($settings['map_id']) ? $settings['map_id'] : 'yandex-map-' . uniqid();
        
        // #region agent log
        try {
            $log = array(
                'sessionId' => 'debug-session',
                'runId'     => 'pre-fix',
                'hypothesisId' => 'P2',
                'location'  => 'class-codeweber-yandex-maps.php:render_map',
                'message'   => 'Render map called',
                'data'      => array(
                    'map_id'       => $map_id,
                    'markersCount' => is_array($markers) ? count($markers) : 0,
                    'height'       => $settings['height'],
                    'sidebar'      => array(
                        'show'         => $settings['show_sidebar'],
                        'show_filters' => $settings['show_filters'],
                        'filter_by_city' => $settings['filter_by_city'],
                    ),
                ),
                'timestamp' => round(microtime(true) * 1000),
            );
            @file_put_contents(ABSPATH . '.cursor/debug.log', json_encode($log) . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
            // silent
        }
        // #endregion

        // Удаляем searchControl из массива controls, если опция отключена
        $controls = $settings['controls'];
        if (!$settings['search_control']) {
            $key = array_search('searchControl', $controls);
            if ($key !== false) {
                unset($controls[$key]);
                $controls = array_values($controls); // Переиндексируем массив
            }
        }

        // Подготавливаем маркеры
        $prepared_markers = $this->prepare_markers($markers, $settings);
        
        // Подготавливаем данные для JavaScript
        $map_data = array(
            'id' => $map_id,
            'center' => $settings['center'],
            'zoom' => $settings['zoom'],
            'mapType' => $settings['map_type'],
            'controls' => $controls,
            'enableScrollZoom' => $settings['enable_scroll_zoom'],
            'enableDrag' => $settings['enable_drag'],
            'enableDblClickZoom' => $settings['enable_dbl_click_zoom'],
            'enableMultiTouch' => $settings['enable_multi_touch'],
            'geolocationControl' => $settings['geolocation_control'],
            'routeButton' => $settings['route_button'],
            'autoFitBounds' => $settings['auto_fit_bounds'],
            'markers' => $prepared_markers,
            'markerSettings' => array(
                'type' => $settings['marker_type'],
                'preset' => $settings['marker_preset'],
                'color' => $settings['marker_color'],
                'logo' => $settings['marker_logo'],
                'logoSize' => $settings['marker_logo_size'],
            ),
            'sidebar' => array(
                'show' => $settings['show_sidebar'],
                'position' => $settings['sidebar_position'],
                'title' => $settings['sidebar_title'],
                'showFilters' => $settings['show_filters'],
                'filterByCity' => $settings['filter_by_city'],
                'filterByCategory' => $settings['filter_by_category'],
                'fields' => isset($settings['sidebar_fields']) ? $settings['sidebar_fields'] : array(
                    'showCity' => true,
                    'showAddress' => false,
                    'showPhone' => false,
                    'showWorkingHours' => true,
                    'showDescription' => true,
                ),
            ),
            'route' => array(
                'show' => $settings['show_route'],
                'type' => $settings['route_type'],
                'startPoint' => $settings['route_start_point'],
            ),
            'clusterer' => array(
                'enabled' => $settings['clusterer'],
                'preset' => $settings['clusterer_preset'],
            ),
            'balloon' => array(
                'template' => $settings['balloon_template'],
                'maxWidth' => $settings['balloon_max_width'],
                'closeButton' => $settings['balloon_close_button'],
                'autoPan' => $settings['balloon_auto_pan'],
                'layout' => $settings['balloon_layout'],
                'contentLayout' => $settings['balloon_content_layout'],
                'fields' => !empty($settings['balloon_fields']) && is_array($settings['balloon_fields']) 
                    ? $settings['balloon_fields'] 
                    : array(
                        'showCity' => true,
                        'showAddress' => true,
                        'showPhone' => true,
                        'showWorkingHours' => true,
                        'showLink' => true,
                        'showDescription' => false,
                    ),
            ),
            
            // #region agent log
            // Логирование настроек балуна для отладки
            // #endregion
            'markerBehavior' => array(
                'openBalloonOnClick' => $settings['marker_open_balloon_on_click'],
                'autoOpenBalloon' => $settings['marker_auto_open_balloon'],
                'hintAutoPan' => $settings['marker_hint_auto_pan'],
                'balloonAutoPan' => $settings['marker_balloon_auto_pan'],
            ),
            'ui' => array(
                'responsive' => $settings['responsive'],
                'mobileOptimized' => $settings['mobile_optimized'],
                'touchOptimized' => $settings['touch_optimized'],
                'desktopOptimized' => $settings['desktop_optimized'],
                'screenReaderSupport' => $settings['screen_reader_support'],
            ),
            'lazyLoad' => $settings['lazy_load'],
            'customStyle' => $settings['custom_style'],
            'styleJson' => $settings['style_json'],
        );

        // #region agent log
        try {
            $log = array(
                'sessionId'    => 'debug-session',
                'runId'        => 'pre-fix',
                'hypothesisId' => 'DZ1',
                'location'     => 'class-codeweber-yandex-maps.php:render_map',
                'message'      => 'Double click zoom setting passed to JS',
                'data'         => array(
                    'enable_dbl_click_zoom' => $settings['enable_dbl_click_zoom'],
                ),
                'timestamp'    => round(microtime(true) * 1000),
            );
            @file_put_contents(ABSPATH . '.cursor/debug.log', json_encode($log) . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
            // silent
        }
        // #endregion
        
        // Стили для карты
        $map_style = sprintf(
            'width: %s; height: %spx; border-radius: %spx;',
            esc_attr($settings['width']),
            esc_attr($settings['height']),
            esc_attr($settings['border_radius'])
        );
        
        ob_start();
        ?>
        <div class="codeweber-yandex-map-wrapper" data-map-config="<?php echo esc_attr(wp_json_encode($map_data)); ?>">
            <div class="spinner spinner-overlay" id="<?php echo esc_attr($map_id); ?>-loader"></div>
            <div id="<?php echo esc_attr($map_id); ?>" class="codeweber-yandex-map" style="<?php echo $map_style; ?>"></div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Подготовка маркеров для карты
     * 
     * @param array $markers Массив маркеров
     * @param array $settings Настройки
     * @return array Подготовленные маркеры
     */
    private function prepare_markers(array $markers, array $settings): array {
        $prepared = array();
        
        // #region agent log
        try {
            $log = array(
                'sessionId' => 'debug-session',
                'runId' => 'pre-fix',
                'hypothesisId' => 'B',
                'location' => 'class-codeweber-yandex-maps.php:prepare_markers',
                'message' => 'Preparing markers',
                'data' => array(
                    'inputMarkersCount' => count($markers),
                    'firstMarkerKeys' => !empty($markers) ? array_keys($markers[0]) : array(),
                ),
                'timestamp' => round(microtime(true) * 1000),
            );
            @file_put_contents(ABSPATH . '.cursor/debug.log', json_encode($log) . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
            // silent
        }
        // #endregion
        
        foreach ($markers as $marker) {
            // Проверяем обязательные поля
            if (empty($marker['latitude']) || empty($marker['longitude'])) {
                // #region agent log
                try {
                    $log = array(
                        'sessionId' => 'debug-session',
                        'runId' => 'pre-fix',
                        'hypothesisId' => 'B',
                        'location' => 'class-codeweber-yandex-maps.php:prepare_markers',
                        'message' => 'Marker skipped - missing coordinates',
                        'data' => array(
                            'markerKeys' => array_keys($marker),
                            'hasLat' => isset($marker['latitude']),
                            'hasLng' => isset($marker['longitude']),
                            'hasCoords' => isset($marker['coords']),
                        ),
                        'timestamp' => round(microtime(true) * 1000),
                    );
                    @file_put_contents(ABSPATH . '.cursor/debug.log', json_encode($log) . PHP_EOL, FILE_APPEND);
                } catch (\Throwable $e) {
                    // silent
                }
                // #endregion
                continue;
            }
            
            $prepared_marker = array(
                'id' => isset($marker['id']) ? $marker['id'] : uniqid(),
                'latitude' => floatval($marker['latitude']),
                'longitude' => floatval($marker['longitude']),
                'title' => isset($marker['title']) ? $marker['title'] : '',
                'balloonContent' => isset($marker['balloonContent']) ? $marker['balloonContent'] : '',
                'balloonContentHeader' => isset($marker['balloonContentHeader']) ? $marker['balloonContentHeader'] : '',
                'hintContent' => isset($marker['hintContent']) ? $marker['hintContent'] : '',
                'city' => isset($marker['city']) ? $marker['city'] : '',
                'category' => isset($marker['category']) ? $marker['category'] : '',
                'link' => isset($marker['link']) ? $marker['link'] : '',
                // Дополнительные поля для балуна офиса
                'address' => isset($marker['address']) ? $marker['address'] : '',
                'phone' => isset($marker['phone']) ? $marker['phone'] : '',
                'workingHours' => isset($marker['workingHours']) ? $marker['workingHours'] : '',
                'description' => isset($marker['description']) ? $marker['description'] : '',
            );
            
            // Если используется кастомный маркер или логотип
            if ($settings['marker_type'] === 'custom' || $settings['marker_type'] === 'logo') {
                $prepared_marker['icon'] = array(
                    'type' => $settings['marker_type'],
                    'preset' => $settings['marker_preset'],
                    'color' => $settings['marker_color'],
                    'logo' => $settings['marker_logo'],
                    'logoSize' => $settings['marker_logo_size'],
                );
            }
            
            $prepared[] = $prepared_marker;
        }
        
        // #region agent log
        try {
            $log = array(
                'sessionId' => 'debug-session',
                'runId' => 'pre-fix',
                'hypothesisId' => 'B',
                'location' => 'class-codeweber-yandex-maps.php:prepare_markers',
                'message' => 'Markers prepared',
                'data' => array(
                    'preparedCount' => count($prepared),
                ),
                'timestamp' => round(microtime(true) * 1000),
            );
            @file_put_contents(ABSPATH . '.cursor/debug.log', json_encode($log) . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
            // silent
        }
        // #endregion
        
        return $prepared;
    }
    
    /**
     * Получить настройки по умолчанию
     * 
     * @return array
     */
    public function get_default_settings(): array {
        return $this->default_settings;
    }
    
    /**
     * Получить путь к модулю
     * 
     * @return string
     */
    public function get_path(): string {
        return $this->path;
    }
    
    /**
     * Получить URL модуля
     * 
     * @return string
     */
    public function get_url(): string {
        return $this->url;
    }
    
    /**
     * Получить версию модуля
     * 
     * @return string
     */
    public function get_version(): string {
        return $this->version;
    }
}


