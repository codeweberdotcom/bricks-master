<?php

/**
 * Search Statistics System
 * Сохраняет все поисковые запросы в базе данных
 */

// Создание таблицы при активации темы
add_action('after_switch_theme', 'create_search_statistics_table');
function create_search_statistics_table()
{
   global $wpdb;

   $table_name = $wpdb->prefix . 'search_statistics';

   $charset_collate = $wpdb->get_charset_collate();

   $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        search_query varchar(255) NOT NULL,
        user_id bigint(20) DEFAULT 0,
        user_ip varchar(45) DEFAULT NULL,
        page_url varchar(500) NOT NULL,
        page_title varchar(255) NOT NULL,
        results_count int(11) DEFAULT 0,
        search_date datetime NOT NULL,
        form_id varchar(100) DEFAULT '',
        PRIMARY KEY (id),
        KEY search_query (search_query),
        KEY search_date (search_date),
        KEY user_id (user_id),
        KEY form_id (form_id)
    ) $charset_collate;";

   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   dbDelta($sql);
}

// AJAX обработчик для сохранения поискового запроса
add_action('wp_ajax_save_search_query', 'handle_save_search_query');
add_action('wp_ajax_nopriv_save_search_query', 'handle_save_search_query');

function handle_save_search_query()
{
   if (!wp_verify_nonce($_POST['nonce'], 'search_statistics_nonce')) {
      wp_send_json_error(__('Security error', 'codeweber'));
   }

   $search_query = sanitize_text_field($_POST['search_query']);

   if (empty($search_query) || strlen($search_query) < 3) {
      wp_send_json_success(__('Query too short', 'codeweber'));
   }

   $results_count = isset($_POST['results_count']) ? intval($_POST['results_count']) : 0;
   $form_id = isset($_POST['form_id']) ? sanitize_text_field($_POST['form_id']) : '';

   // УПРОЩАЕМ: убираем сложные данные Matomo
   $matomo_data = [
      'visitor_id' => '', // Теперь это будет определяться на сервере
      'source' => 'javascript_search'
   ];

   // Вызываем хук для отладки
   do_action('before_save_search_query', $search_query, $results_count, $form_id, $matomo_data);

   save_search_query_to_db($search_query, $results_count, $form_id);

   wp_send_json_success(__('Search query saved', 'codeweber'));
}

// Хук для отладки данных поиска
add_action('before_save_search_query', 'debug_search_data_hook', 10, 3);

function debug_search_data_hook($search_query, $results_count, $form_id)
{
   // Получаем данные Matomo
   $matomo_data = get_matomo_tracking_data();

   // Отладочная информация
   debug_search_data($search_query, $results_count, $form_id, $matomo_data);
}


// Функция для получения данных отслеживания Matomo через PHP API
function get_matomo_tracking_data()
{
   $data = [
      'visitor_id' => '',
      'session_id' => '',
      'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
      'matomo_visitor_id' => '',
      'matomo_session_id' => ''
   ];

   // Получаем visitor_id через PHP Matomo
   $visitor_id = get_matomo_visitor_id_via_php();

   if (!empty($visitor_id)) {
      $data['visitor_id'] = $visitor_id;
      $data['matomo_visitor_id'] = $visitor_id;

      // Создаем session_id на основе visitor_id
      $data['session_id'] = substr($visitor_id, 0, 16) . '_' . time();
      $data['matomo_session_id'] = $data['session_id'];

      return $data;
   }

   // Fallback - создаем на основе IP и User-Agent
   $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';
   $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

   $data['visitor_id'] = 'php_visitor_' . md5($user_ip . $user_agent . date('Y-m-d'));
   $data['matomo_visitor_id'] = $data['visitor_id'];
   $data['session_id'] = 'php_session_' . time() . '_' . rand(1000, 9999);
   $data['matomo_session_id'] = $data['session_id'];

   return $data;
}

// Вспомогательная функция для получения visitor_id через PHP Matomo
function get_matomo_visitor_id_via_php()
{
   // Если есть готовая функция Matomo
   if (function_exists('matomo_get_visitor_id')) {
      return matomo_get_visitor_id();
   }

   // Пытаемся получить через глобальные переменные Matomo
   if (isset($GLOBALS['MATOMO_VISITOR_ID'])) {
      return $GLOBALS['MATOMO_VISITOR_ID'];
   }

   // Пытаемся получить из сессии Matomo
   if (isset($_SESSION['matomo_visitor_id'])) {
      return $_SESSION['matomo_visitor_id'];
   }

   // Пытаемся получить через базу данных Matomo (последний visitor_id для этого IP)
   $visitor_id = get_matomo_visitor_id_from_db();
   if (!empty($visitor_id)) {
      return $visitor_id;
   }

   return '';
}

// Функция для получения visitor_id из базы данных Matomo
function get_matomo_visitor_id_from_db()
{
   global $wpdb;

   $user_ip = $_SERVER['REMOTE_ADDR'] ?? '';

   if (empty($user_ip) || $user_ip === '127.0.0.1') {
      return '';
   }

   // Пробуем разные варианты таблиц Matomo
   $matomo_tables = [
      $wpdb->prefix . 'matomo_log_visit',
      'matomo_log_visit',
      'piwik_log_visit',
      $wpdb->prefix . 'piwik_log_visit',
      $wpdb->prefix . 'matomo_log_link_visit_action',
      'matomo_log_link_visit_action'
   ];

   foreach ($matomo_tables as $table) {
      if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
         // Пытаемся найти последний visitor_id по IP
         $visitor_id = $wpdb->get_var($wpdb->prepare(
            "SELECT idvisitor FROM $table 
                 WHERE location_ip = %s 
                 ORDER BY visit_last_action_time DESC LIMIT 1",
            $user_ip
         ));

         if ($visitor_id) {
            return $visitor_id;
         }

         // Пытаемся найти любой visitor_id
         $visitor_id = $wpdb->get_var(
            "SELECT idvisitor FROM $table ORDER BY visit_last_action_time DESC LIMIT 1"
         );

         if ($visitor_id) {
            return $visitor_id;
         }
      }
   }

   return '';
}

// Временная тестовая функция для отладки
function debug_search_data($search_query, $results_count, $form_id, $matomo_data)
{
   $debug_data = [
      'timestamp' => current_time('mysql'),
      'search_query' => $search_query,
      'results_count' => $results_count,
      'form_id' => $form_id,
      'matomo_data' => $matomo_data,
      'user_info' => [
         'user_id' => get_current_user_id(),
         'user_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
         'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
         'is_user_logged_in' => is_user_logged_in()
      ]
   ];

   // Логируем в debug.log
   error_log('SEARCH STATISTICS DEBUG: ' . print_r($debug_data, true));

   // Также выводим в ответе AJAX для удобства отладки
   if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['debug_search'])) {
      echo "<!-- SEARCH DEBUG: " . base64_encode(json_encode($debug_data)) . " -->";
   }
}


// AJAX обработчик для очистки базы данных
add_action('wp_ajax_clear_search_statistics', 'handle_clear_search_statistics');

function handle_clear_search_statistics()
{
   // Проверяем права администратора
   if (!current_user_can('manage_options')) {
      wp_send_json_error(__('Insufficient permissions', 'codeweber'));
   }

   if (!wp_verify_nonce($_POST['nonce'], 'clear_search_stats_nonce')) {
      wp_send_json_error(__('Security error', 'codeweber'));
   }

   $password = sanitize_text_field($_POST['password']);

   // Проверяем пароль администратора
   $user = wp_get_current_user();
   if (!wp_check_password($password, $user->user_pass, $user->ID)) {
      wp_send_json_error(__('Invalid password', 'codeweber'));
   }

   $result = clear_search_statistics_data();

   if ($result !== false) {
      wp_send_json_success(__('Search statistics cleared successfully', 'codeweber'));
   } else {
      wp_send_json_error(__('Error clearing data', 'codeweber'));
   }
}

// Функция для получения количества результатов поиска
function get_search_results_count($search_query, $search_params)
{
   // Выполняем тот же поиск, чтобы получить количество результатов
   $results = perform_enhanced_search(array(
      'keyword' => $search_query,
      'post_type' => $search_params['post_types'] ?? '',
      'posts_per_page' => -1, // Получаем все результаты для подсчета
      'taxonomy' => $search_params['taxonomy'] ?? '',
      'term' => $search_params['term'] ?? '',
      'include_taxonomies' => $search_params['include_taxonomies'] ?? false,
      'search_content' => $search_params['search_content'] ?? false,
      'show_excerpt' => $search_params['show_excerpt'] ?? true
   ));

   $total_count = 0;
   if (isset($results['all_results'])) {
      foreach ($results['all_results'] as $group) {
         $total_count += $group['total_found'];
      }
   }

   return $total_count;
}

// Функция сохранения поискового запроса в базу данных
function save_search_query_to_db($search_query, $results_count = 0, $form_id = '', $search_params = array())
{
   global $wpdb;

   $table_name = $wpdb->prefix . 'search_statistics';

   $current_user = wp_get_current_user();
   $user_ip = $_SERVER['REMOTE_ADDR'];

   // Получаем реальный адрес страницы (не admin-ajax.php)
   if (isset($_SERVER['HTTP_REFERER'])) {
      $page_url = sanitize_url($_SERVER['HTTP_REFERER']);
   } else {
      $page_url = home_url($_SERVER['REQUEST_URI']);
   }

   $page_title = wp_get_document_title();

   // Если количество результатов не передано, вычисляем его
   if ($results_count === 0 && !empty($search_params)) {
      $results_count = get_search_results_count($search_query, $search_params);
   }

   $wpdb->insert(
      $table_name,
      array(
         'search_query' => $search_query,
         'user_id' => $current_user->ID,
         'user_ip' => $user_ip,
         'page_url' => $page_url,
         'page_title' => $page_title,
         'results_count' => $results_count,
         'search_date' => current_time('mysql'),
         'form_id' => $form_id
      ),
      array(
         '%s',
         '%d',
         '%s',
         '%s',
         '%s',
         '%d',
         '%s',
         '%s'
      )
   );

   return $wpdb->insert_id;
}

// Функция очистки всех данных статистики
function clear_search_statistics_data()
{
   global $wpdb;

   $table_name = $wpdb->prefix . 'search_statistics';

   // Очищаем таблицу
   $result = $wpdb->query("TRUNCATE TABLE $table_name");

   return $result;
}

// Добавляем скрипты для отслеживания поиска
add_action('wp_enqueue_scripts', 'enqueue_search_statistics_scripts');
function enqueue_search_statistics_scripts()
{
   wp_enqueue_script(
      'search-statistics',
      get_template_directory_uri() . '/functions/integrations/ajax-search-module/assets/js/search-statistics.js',
      array('jquery'),
      time(),
      true
   );

   wp_localize_script('search-statistics', 'search_stats_params', array(
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('search_statistics_nonce'),
      'i18n' => array(
         'security_error' => __('Security error', 'codeweber'),
         'query_too_short' => __('Query too short', 'codeweber'),
         'search_saved' => __('Search query saved', 'codeweber'),
         'clearing_data' => __('Clearing data...', 'codeweber'),
         'data_cleared' => __('Data cleared successfully', 'codeweber'),
         'error_clearing' => __('Error clearing data', 'codeweber'),
         'invalid_password' => __('Invalid password', 'codeweber')
      )
   ));
}

// Создаем страницу статистики в админке
add_action('admin_menu', 'add_search_statistics_admin_page');
function add_search_statistics_admin_page()
{
   add_menu_page(
      __('Search Statistics', 'codeweber'),
      __('Search Stats', 'codeweber'),
      'manage_options',
      'search-statistics',
      'display_search_statistics_page',
      'dashicons-search',
      30
   );
}

// Обработка экспорта ДО начала вывода контента
add_action('admin_init', 'handle_search_statistics_export');
function handle_search_statistics_export()
{
   if (!isset($_POST['export_csv']) || !wp_verify_nonce($_POST['export_nonce'], 'export_search_stats')) {
      return;
   }

   // Проверяем права
   if (!current_user_can('manage_options')) {
      wp_die(__('Insufficient permissions', 'codeweber'));
   }

   export_search_statistics_to_csv();
}

// Функция получения списка всех форм
function get_search_forms_list()
{
   global $wpdb;

   $table_name = $wpdb->prefix . 'search_statistics';

   $forms = $wpdb->get_results("
        SELECT DISTINCT form_id 
        FROM $table_name 
        WHERE form_id != '' 
        ORDER BY form_id
    ");

   $forms_list = array();
   foreach ($forms as $form) {
      $forms_list[] = $form->form_id;
   }

   return $forms_list;
}

// Функция отображения страницы статистики
function display_search_statistics_page()
{
   global $wpdb;

   $table_name = $wpdb->prefix . 'search_statistics';

   // Получаем параметры фильтрации
   $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
   $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
   $form_id = isset($_GET['form_id']) ? sanitize_text_field($_GET['form_id']) : '';

   // Обработка очистки данных через форму
   if (isset($_POST['clear_data']) && wp_verify_nonce($_POST['clear_nonce'], 'clear_search_data')) {
      $password = sanitize_text_field($_POST['admin_password']);
      $user = wp_get_current_user();

      if (wp_check_password($password, $user->user_pass, $user->ID)) {
         $result = clear_search_statistics_data();
         if ($result !== false) {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Search statistics data cleared successfully!', 'codeweber') . '</p></div>';
         } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Error clearing data!', 'codeweber') . '</p></div>';
         }
      } else {
         echo '<div class="notice notice-error is-dismissible"><p>' . __('Invalid password!', 'codeweber') . '</p></div>';
      }
   }

   // Строим WHERE условие для фильтрации
   $where_conditions = array();
   $query_params = array();

   if (!empty($start_date)) {
      $where_conditions[] = "DATE(search_date) >= %s";
      $query_params[] = $start_date;
   }

   if (!empty($end_date)) {
      $where_conditions[] = "DATE(search_date) <= %s";
      $query_params[] = $end_date;
   }

   if (!empty($form_id)) {
      if ($form_id === '_none') {
         $where_conditions[] = "(form_id = '' OR form_id IS NULL)";
         // Для случая "_none" не добавляем параметры в query_params
      } else {
         $where_conditions[] = "form_id = %s";
         $query_params[] = $form_id;
      }
   }

   $where_sql = '';
   if (!empty($where_conditions)) {
      $where_sql = "WHERE " . implode(" AND ", $where_conditions);
   }

   // Получаем статистику с учетом фильтров
   if (!empty($where_sql)) {
      // Для случаев с "_none" используем прямой запрос без prepare
      if ($form_id === '_none') {
         $total_searches = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where_sql");
         $unique_queries = $wpdb->get_var("SELECT COUNT(DISTINCT search_query) FROM $table_name $where_sql");
         $popular_queries = $wpdb->get_results("
            SELECT search_query, COUNT(*) as search_count 
            FROM $table_name 
            $where_sql 
            GROUP BY search_query 
            ORDER BY search_count DESC 
            LIMIT 20
         ");
         $recent_searches = $wpdb->get_results("
            SELECT * FROM $table_name 
            $where_sql 
            ORDER BY search_date DESC 
            LIMIT 50
         ");
      } else {
         $total_searches = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table_name $where_sql", $query_params)
         );

         $unique_queries = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(DISTINCT search_query) FROM $table_name $where_sql", $query_params)
         );

         $popular_queries = $wpdb->get_results(
            $wpdb->prepare("
                   SELECT search_query, COUNT(*) as search_count 
                   FROM $table_name 
                   $where_sql 
                   GROUP BY search_query 
                   ORDER BY search_count DESC 
                   LIMIT 20
               ", $query_params)
         );

         $recent_searches = $wpdb->get_results(
            $wpdb->prepare("
                   SELECT * FROM $table_name 
                   $where_sql 
                   ORDER BY search_date DESC 
                   LIMIT 50
               ", $query_params)
         );
      }
   } else {
      // Без фильтров
      $total_searches = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
      $unique_queries = $wpdb->get_var("SELECT COUNT(DISTINCT search_query) FROM $table_name");
      $popular_queries = $wpdb->get_results("
            SELECT search_query, COUNT(*) as search_count 
            FROM $table_name 
            GROUP BY search_query 
            ORDER BY search_count DESC 
            LIMIT 20
        ");
      $recent_searches = $wpdb->get_results("
            SELECT * FROM $table_name 
            ORDER BY search_date DESC 
            LIMIT 50
        ");
   }

   // Сегодняшние поиски (отдельно, так как всегда нужна фильтрация по дате)
   $today_where_sql = "WHERE DATE(search_date) = %s";
   $today_params = [current_time('Y-m-d')];

   if (!empty($where_sql)) {
      $today_where_sql .= " AND " . substr($where_sql, 6);
      if ($form_id !== '_none') {
         $today_params = array_merge($today_params, $query_params);
      }
   }

   if ($form_id === '_none' && !empty($where_sql)) {
      $today_searches = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $today_where_sql");
   } else {
      $today_searches = $wpdb->get_var(
         $wpdb->prepare("SELECT COUNT(*) FROM $table_name $today_where_sql", $today_params)
      );
   }

   // Получаем размер таблицы в базе данных
   $table_size = $wpdb->get_var("
        SELECT ROUND((data_length + index_length) / 1024 / 1024, 2) 
        FROM information_schema.TABLES 
        WHERE table_schema = '" . DB_NAME . "' 
        AND table_name = '$table_name'
    ");

   // Получаем список всех форм для фильтра
   $forms_list = get_search_forms_list();
?>
   <div class="wrap">
      <h1><?php _e('Search Statistics', 'codeweber'); ?></h1>

      <!-- Фильтры -->
      <div class="search-filters" style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
         <h3><?php _e('Filters', 'codeweber'); ?></h3>
         <form method="get" action="">
            <input type="hidden" name="page" value="search-statistics">

            <div style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
               <div>
                  <label for="start_date" style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e('Start Date:', 'codeweber'); ?></label>
                  <input type="date" id="start_date" name="start_date" value="<?php echo esc_attr($start_date); ?>" style="padding: 5px;">
               </div>

               <div>
                  <label for="end_date" style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e('End Date:', 'codeweber'); ?></label>
                  <input type="date" id="end_date" name="end_date" value="<?php echo esc_attr($end_date); ?>" style="padding: 5px;">
               </div>

               <div>
                  <label for="form_id" style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e('Search Form:', 'codeweber'); ?></label>
                  <select id="form_id" name="form_id" style="padding: 5px; min-width: 200px;">
                     <option value=""><?php _e('All Forms', 'codeweber'); ?></option>
                     <option value="_none" <?php selected($form_id, '_none'); ?>><?php _e('No Form ID', 'codeweber'); ?></option>
                     <?php foreach ($forms_list as $form): ?>
                        <option value="<?php echo esc_attr($form); ?>" <?php selected($form_id, $form); ?>>
                           <?php echo esc_html($form); ?>
                        </option>
                     <?php endforeach; ?>
                  </select>
               </div>

               <div>
                  <input type="submit" class="button button-primary" value="<?php _e('Apply Filters', 'codeweber'); ?>" style="margin-top: 20px;">
                  <a href="?page=search-statistics" class="button" style="margin-top: 20px;"><?php _e('Clear Filters', 'codeweber'); ?></a>
               </div>
            </div>
         </form>

         <?php if (!empty($start_date) || !empty($end_date) || !empty($form_id)): ?>
            <div style="margin-top: 10px; padding: 10px; background: #e7f3ff; border-radius: 3px;">
               <strong><?php _e('Active Filters:', 'codeweber'); ?></strong>
               <?php
               $active_filters = [];
               if (!empty($start_date)) $active_filters[] = __("From:", 'codeweber') . " " . $start_date;
               if (!empty($end_date)) $active_filters[] = __("To:", 'codeweber') . " " . $end_date;
               if (!empty($form_id)) {
                  $form_label = ($form_id === '_none') ? __('No Form ID', 'codeweber') : $form_id;
                  $active_filters[] = __("Form:", 'codeweber') . " " . $form_label;
               }
               echo implode(', ', $active_filters);
               ?>
            </div>
         <?php endif; ?>
      </div>

      <div class="search-stats-overview" style="margin: 20px 0;">
         <div class="stats-container" style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div class="stat-box" style="background: #f8f9fa; padding: 20px; border-radius: 5px; min-width: 200px;">
               <h3><?php _e('Total Searches', 'codeweber'); ?></h3>
               <p style="font-size: 2em; margin: 0; color: #0073aa;"><?php echo number_format($total_searches); ?></p>
            </div>
            <div class="stat-box" style="background: #f8f9fa; padding: 20px; border-radius: 5px; min-width: 200px;">
               <h3><?php _e('Unique Queries', 'codeweber'); ?></h3>
               <p style="font-size: 2em; margin: 0; color: #0073aa;"><?php echo number_format($unique_queries); ?></p>
            </div>
            <div class="stat-box" style="background: #f8f9fa; padding: 20px; border-radius: 5px; min-width: 200px;">
               <h3><?php _e("Today's Searches", 'codeweber'); ?></h3>
               <p style="font-size: 2em; margin: 0; color: #0073aa;"><?php echo number_format($today_searches); ?></p>
            </div>
            <div class="stat-box" style="background: #f8f9fa; padding: 20px; border-radius: 5px; min-width: 200px;">
               <h3><?php _e('Database Size', 'codeweber'); ?></h3>
               <p style="font-size: 2em; margin: 0; color: #0073aa;"><?php echo $table_size ? $table_size . ' MB' : 'N/A'; ?></p>
            </div>
         </div>
      </div>

      <div class="search-stats-content" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
         <div class="popular-queries">
            <h2><?php _e('Popular Search Queries', 'codeweber'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
               <thead>
                  <tr>
                     <th><?php _e('Query', 'codeweber'); ?></th>
                     <th><?php _e('Count', 'codeweber'); ?></th>
                     <th><?php _e('Form ID', 'codeweber'); ?></th>
                  </tr>
               </thead>
               <tbody>
                  <?php if ($popular_queries): ?>
                     <?php foreach ($popular_queries as $query): ?>
                        <tr>
                           <td><?php echo esc_html($query->search_query); ?></td>
                           <td><?php echo number_format($query->search_count); ?></td>
                           <td>
                              <?php
                              // Получаем form_id для этого запроса
                              $query_form_id = $wpdb->get_var($wpdb->prepare(
                                 "SELECT form_id FROM $table_name WHERE search_query = %s LIMIT 1",
                                 $query->search_query
                              ));
                              echo $query_form_id ? esc_html($query_form_id) : '<em>' . __('none', 'codeweber') . '</em>';
                              ?>
                           </td>
                        </tr>
                     <?php endforeach; ?>
                  <?php else: ?>
                     <tr>
                        <td colspan="3"><?php _e('No search queries found', 'codeweber'); ?></td>
                     </tr>
                  <?php endif; ?>
               </tbody>
            </table>
         </div>

         <div class="recent-searches">
            <h2><?php _e('Recent Searches', 'codeweber'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
               <thead>
                  <tr>
                     <th><?php _e('Query', 'codeweber'); ?></th>
                     <th><?php _e('User', 'codeweber'); ?></th>
                     <th><?php _e('Date', 'codeweber'); ?></th>
                     <th><?php _e('Results', 'codeweber'); ?></th>
                     <th><?php _e('Form ID', 'codeweber'); ?></th>
                     <th><?php _e('Page', 'codeweber'); ?></th>
                  </tr>
               </thead>
               <tbody>
                  <?php if ($recent_searches): ?>
                     <?php foreach ($recent_searches as $search): ?>
                        <tr>
                           <td><?php echo esc_html($search->search_query); ?></td>
                           <td>
                              <?php if ($search->user_id): ?>
                                 <?php
                                 $user = get_user_by('id', $search->user_id);
                                 echo $user ? esc_html($user->display_name) : __('User #', 'codeweber') . $search->user_id;
                                 ?>
                              <?php else: ?>
                                 <?php _e('Guest', 'codeweber'); ?>
                              <?php endif; ?>
                           </td>
                           <td><?php echo date('Y-m-d H:i', strtotime($search->search_date)); ?></td>
                           <td><?php echo number_format($search->results_count); ?></td>
                           <td>
                              <?php echo $search->form_id ? esc_html($search->form_id) : '<em>' . __('none', 'codeweber') . '</em>'; ?>
                           </td>
                           <td>
                              <a href="<?php echo esc_url($search->page_url); ?>" target="_blank">
                                 <?php echo esc_html(wp_trim_words($search->page_title, 5)); ?>
                              </a>
                           </td>
                        </tr>
                     <?php endforeach; ?>
                  <?php else: ?>
                     <tr>
                        <td colspan="6"><?php _e('No recent searches found', 'codeweber'); ?></td>
                     </tr>
                  <?php endif; ?>
               </tbody>
            </table>
         </div>
      </div>

      <div class="data-management" style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
         <div class="export-section">
            <h2><?php _e('Export Data', 'codeweber'); ?></h2>
            <form method="post">
               <?php wp_nonce_field('export_search_stats', 'export_nonce'); ?>
               <input type="hidden" name="export_start_date" value="<?php echo esc_attr($start_date); ?>">
               <input type="hidden" name="export_end_date" value="<?php echo esc_attr($end_date); ?>">
               <input type="hidden" name="export_form_id" value="<?php echo esc_attr($form_id); ?>">

               <p><?php _e('Export current filtered data:', 'codeweber'); ?></p>
               <input type="submit" name="export_csv" class="button button-primary" value="<?php _e('Export to CSV', 'codeweber'); ?>">
            </form>
         </div>

         <div class="clear-section">
            <h2><?php _e('Clear Data', 'codeweber'); ?></h2>
            <div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
               <p><strong><?php _e('Warning:', 'codeweber'); ?></strong> <?php _e('This action will permanently delete all search statistics data. This cannot be undone.', 'codeweber'); ?></p>

               <form method="post" id="clear-data-form">
                  <?php wp_nonce_field('clear_search_data', 'clear_nonce'); ?>
                  <p>
                     <label for="admin_password"><strong><?php _e('Enter your admin password to confirm:', 'codeweber'); ?></strong></label><br>
                     <input type="password" id="admin_password" name="admin_password" style="width: 300px; margin-top: 5px;" required>
                  </p>
                  <p>
                     <input type="submit" name="clear_data" class="button button-danger" value="<?php _e('Clear All Data', 'codeweber'); ?>"
                        style="background: #dc3232; border-color: #dc3232; color: white;"
                        onclick="return confirm('<?php _e('Are you sure you want to delete ALL search statistics data? This action cannot be undone.', 'codeweber'); ?>')">
                  </p>
               </form>

               <div style="margin-top: 15px;">
                  <button type="button" id="clear-data-ajax" class="button button-danger"
                     style="background: #dc3232; border-color: #dc3232; color: white;">
                     <?php _e('Clear Data (AJAX)', 'codeweber'); ?>
                  </button>
                  <div id="clear-result" style="margin-top: 10px;"></div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <script>
      jQuery(document).ready(function($) {
         $('#clear-data-ajax').on('click', function() {
            if (!confirm('<?php _e('Are you sure you want to delete ALL search statistics data? This action cannot be undone.', 'codeweber'); ?>')) {
               return;
            }

            const password = prompt('<?php _e('Enter your admin password to confirm:', 'codeweber'); ?>');
            if (!password) {
               return;
            }

            const $button = $(this);
            const $result = $('#clear-result');

            $button.prop('disabled', true).text('<?php _e('Clearing...', 'codeweber'); ?>');
            $result.html('<div class="notice notice-warning is-dismissible"><p><?php _e('Clearing data...', 'codeweber'); ?></p></div>');

            $.ajax({
               url: ajaxurl,
               type: 'POST',
               data: {
                  action: 'clear_search_statistics',
                  password: password,
                  nonce: '<?php echo wp_create_nonce('clear_search_stats_nonce'); ?>'
               },
               success: function(response) {
                  if (response.success) {
                     $result.html('<div class="notice notice-success is-dismissible"><p>' + response.data + '</p></div>');
                     // Обновляем страницу через 2 секунды
                     setTimeout(function() {
                        location.reload();
                     }, 2000);
                  } else {
                     $result.html('<div class="notice notice-error is-dismissible"><p>' + response.data + '</p></div>');
                  }
               },
               error: function() {
                  $result.html('<div class="notice notice-error is-dismissible"><p><?php _e('Error clearing data', 'codeweber'); ?></p></div>');
               },
               complete: function() {
                  $button.prop('disabled', false).text('<?php _e('Clear Data (AJAX)', 'codeweber'); ?>');
               }
            });
         });
      });
   </script>

   <style>
      .button-danger:hover {
         background: #a00 !important;
         border-color: #a00 !important;
      }
   </style>
<?php
}

// Функция экспорта в CSV
function export_search_statistics_to_csv()
{
   global $wpdb;

   $table_name = $wpdb->prefix . 'search_statistics';

   // Получаем параметры фильтрации из формы
   $start_date = isset($_POST['export_start_date']) ? sanitize_text_field($_POST['export_start_date']) : '';
   $end_date = isset($_POST['export_end_date']) ? sanitize_text_field($_POST['export_end_date']) : '';
   $form_id = isset($_POST['export_form_id']) ? sanitize_text_field($_POST['export_form_id']) : '';

   // Строим WHERE условие для фильтрации
   $where_conditions = array();
   $query_params = array();

   if (!empty($start_date)) {
      $where_conditions[] = "DATE(search_date) >= %s";
      $query_params[] = $start_date;
   }

   if (!empty($end_date)) {
      $where_conditions[] = "DATE(search_date) <= %s";
      $query_params[] = $end_date;
   }

   if (!empty($form_id)) {
      if ($form_id === '_none') {
         $where_conditions[] = "(form_id = '' OR form_id IS NULL)";
         // Для случая "_none" не добавляем параметры в query_params
      } else {
         $where_conditions[] = "form_id = %s";
         $query_params[] = $form_id;
      }
   }

   $where_sql = '';
   if (!empty($where_conditions)) {
      $where_sql = "WHERE " . implode(" AND ", $where_conditions);
   }

   // Строим SQL запрос
   $sql = "SELECT * FROM $table_name $where_sql ORDER BY search_date DESC";

   if (!empty($where_sql) && $form_id !== '_none') {
      $searches = $wpdb->get_results($wpdb->prepare($sql, $query_params));
   } else {
      $searches = $wpdb->get_results($sql);
   }

   // Устанавливаем заголовки для CSV
   header('Content-Type: text/csv; charset=utf-8');
   header('Content-Disposition: attachment; filename=search-statistics-' . date('Y-m-d') . '.csv');

   // Создаем output stream
   $output = fopen('php://output', 'w');

   // Добавляем BOM для корректного отображения кириллицы в Excel
   fputs($output, "\xEF\xBB\xBF");

   // Заголовки CSV с переводами
   fputcsv($output, array(
      __('ID', 'codeweber'),
      __('Search Query', 'codeweber'),
      __('User ID', 'codeweber'),
      __('User IP', 'codeweber'),
      __('Page URL', 'codeweber'),
      __('Page Title', 'codeweber'),
      __('Results Count', 'codeweber'),
      __('Search Date', 'codeweber'),
      __('Form ID', 'codeweber')
   ), ';');

   // Данные
   foreach ($searches as $search) {
      fputcsv($output, array(
         $search->id,
         $search->search_query,
         $search->user_id,
         $search->user_ip,
         $search->page_url,
         $search->page_title,
         $search->results_count,
         $search->search_date,
         $search->form_id
      ), ';');
   }

   fclose($output);
   exit;
}