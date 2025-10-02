<?php

/**
 * ИНСТРУКЦИЯ 1: HTML DATA-ПАРАМЕТРЫ ДЛЯ INPUT
 * 
 * Базовая структура:
 * <input type="text" class="search-form form-control" placeholder="Поиск..." autocomplete="off"
 *        data-posts-per-page="10"
 *        data-post-types="post,page" 
 *        data-search-content="false"
 *        data-taxonomy="category"
 *        data-term="news"
 *        data-include-taxonomies="false"
 *        data-show-excerpt="true">
 * 
 * Параметры:
 * - data-posts-per-page: количество результатов (число, по умолчанию: 10)
 * - data-post-types: типы записей через запятую (post, page, product) 
 * - data-search-content: поиск в контенте (true/false, по умолчанию: false)
 * - data-taxonomy: таксономия для фильтрации (category, post_tag)
 * - data-term: термин таксономии (news, urgent)  
 * - data-include-taxonomies: включать таксономии в результаты (true/false)
 * - data-show-excerpt: показывать отрывки текста (true/false)
 * 
 * Примеры:
 * <input data-posts-per-page="5" data-post-types="product"> - 5 товаров
 * <input data-search-content="true" data-show-excerpt="true"> - поиск в контенте с отрывками
 * <input data-taxonomy="category" data-term="news"> - только в категории "news"
 */

/**
 * ИНСТРУКЦИЯ 2: ИСПОЛЬЗОВАНИЕ ШОРТКОДА
 * 
 * Базовый синтаксис:
 * [ajax_search_form параметр="значение"]
 * 
 * Доступные параметры:
 * - placeholder: текст в поле (по умолчанию: "Поиск...")
 * - posts_per_page: количество результатов (число, по умолчанию: 10)  
 * - post_types: типы записей через запятую (post, page, product)
 * - search_content: поиск в контенте (true/false, по умолчанию: false)
 * - taxonomy: таксономия для фильтрации (category, post_tag)
 * - term: термин таксономии (news, urgent)
 * - include_taxonomies: включать таксономии (true/false, по умолчанию: false)
 * - show_excerpt: показывать отрывки (true/false, по умолчанию: true)
 * - class: CSS классы для стилизации
 * 
 * Примеры использования:
 * [ajax_search_form] - базовая форма
 * [ajax_search_form placeholder="Поиск товаров..." posts_per_page="8" post_types="product"] - поиск товаров
 * [ajax_search_form search_content="true" show_excerpt="true"] - поиск в контенте с отрывками
 * [ajax_search_form taxonomy="category" term="news" include_taxonomies="true"] - с фильтрацией по категории
 * 
 * Использование в PHP:
 * <?php echo do_shortcode('[ajax_search_form placeholder="Поиск..."]'); ?>
 */

add_action('wp_enqueue_scripts', 'enqueue_ajax_search_scripts');
function enqueue_ajax_search_scripts()
{
   wp_enqueue_script(
      'ajax-search',
      get_template_directory_uri() . '/functions/integrations/ajax-search-module/assets/js/ajax-search.js',
      array('jquery'),
      time(),
      true
   );

   // Локализация с правильным склонением для русского языка
   wp_localize_script('ajax-search', 'ajax_search_params', array(
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('ajax-search_nonce'),
      'i18n' => array(
         'searching' => __('Searching...', 'codeweber'),
         'security_error' => __('Security error', 'codeweber'),
         'short_query' => __('Query too short', 'codeweber'),
         'connection_error' => __('Connection error', 'codeweber'),
         'no_results' => __('No results found', 'codeweber'),
         'total_found' => __('Total found', 'codeweber'),
         'result' => array(
            'singular' => _n('result', 'results', 1, 'codeweber'),  // 1 результат
            'few' => _n('result', 'results', 2, 'codeweber'),       // 2-4 результата  
            'many' => _n('result', 'results', 5, 'codeweber')       // 5+ результатов
         ),
         'taxonomy' => __('Taxonomy', 'codeweber'),
         'no_title' => __('No title', 'codeweber'),
         'title' => __('Title', 'codeweber'),
         'content' => __('Content', 'codeweber'),
         'show_all' => __('Show all', 'codeweber'),
         'showing' => __('Found', 'codeweber'),
         'of' => __('of', 'codeweber'),
      )
   ));
}

// Добавьте этот код после существующего handle_ajax_search

add_action('wp_ajax_ajax_search_load_all', 'handle_ajax_search_load_all');
add_action('wp_ajax_nopriv_ajax_search_load_all', 'handle_ajax_search_load_all');

function handle_ajax_search_load_all()
{
   if (!wp_verify_nonce($_POST['nonce'], 'ajax-search_nonce')) {
      wp_send_json_error(__('Security error', 'codeweber'));
   }

   $query = sanitize_text_field($_POST['search_query']);

   if (empty($query) || strlen($query) < 3) {
      wp_send_json_error(__('Query too short. Minimum 3 characters required.', 'codeweber'));
   }

   $post_types = isset($_POST['post_types']) ? sanitize_text_field($_POST['post_types']) : '';
   $search_content = isset($_POST['search_content']) ? filter_var($_POST['search_content'], FILTER_VALIDATE_BOOLEAN) : false;
   $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
   $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
   $include_taxonomies = isset($_POST['include_taxonomies']) ? filter_var($_POST['include_taxonomies'], FILTER_VALIDATE_BOOLEAN) : false;
   $show_excerpt = isset($_POST['show_excerpt']) ? filter_var($_POST['show_excerpt'], FILTER_VALIDATE_BOOLEAN) : true;

   // Загружаем все результаты (posts_per_page = -1)
   $results = perform_enhanced_search(array(
      'keyword' => $query,
      'post_type' => $post_types,
      'posts_per_page' => -1, // Все результаты
      'taxonomy' => $taxonomy,
      'term' => $term,
      'include_taxonomies' => $include_taxonomies,
      'search_content' => $search_content,
      'show_excerpt' => $show_excerpt
   ));

   $total_items = 0;
   if (isset($results['all_results'])) {
      foreach ($results['all_results'] as $group) {
         $total_items += $group['count'];
      }
   }

   $response = array(
      'results' => $results,
      'search_query' => $query,
      'found_posts' => $total_items
   );

   wp_send_json_success($response);
}

add_action('wp_ajax_ajax_search', 'handle_ajax_search');
add_action('wp_ajax_nopriv_ajax_search', 'handle_ajax_search');

function handle_ajax_search()
{
   if (!wp_verify_nonce($_POST['nonce'], 'ajax-search_nonce')) {
      wp_send_json_error(__('Security error', 'codeweber'));
   }

   $query = sanitize_text_field($_POST['search_query']);

   if (empty($query) || strlen($query) < 3) {
      wp_send_json_error(__('Query too short. Minimum 3 characters required.', 'codeweber'));
   }

   $posts_per_page = isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : 10;
   $post_types = isset($_POST['post_types']) ? sanitize_text_field($_POST['post_types']) : '';
   $search_content = isset($_POST['search_content']) ? filter_var($_POST['search_content'], FILTER_VALIDATE_BOOLEAN) : false;
   $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : '';
   $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
   $include_taxonomies = isset($_POST['include_taxonomies']) ? filter_var($_POST['include_taxonomies'], FILTER_VALIDATE_BOOLEAN) : false;
   $show_excerpt = isset($_POST['show_excerpt']) ? filter_var($_POST['show_excerpt'], FILTER_VALIDATE_BOOLEAN) : true;

   $results = perform_enhanced_search(array(
      'keyword' => $query,
      'post_type' => $post_types,
      'posts_per_page' => $posts_per_page,
      'taxonomy' => $taxonomy,
      'term' => $term,
      'include_taxonomies' => $include_taxonomies,
      'search_content' => $search_content,
      'show_excerpt' => $show_excerpt
   ));

   $total_items = 0;
   $displayed_items = 0;
   if (isset($results['all_results'])) {
      foreach ($results['all_results'] as $group) {
         $total_items += $group['total_found'];
         $displayed_items += $group['count'];
      }
   }

   $response = array(
      'results' => $results,
      'search_query' => $query,
      'found_posts' => $total_items,
      'displayed_posts' => $displayed_items,
      'has_more' => $total_items > $displayed_items
   );

   wp_send_json_success($response);
}

function perform_enhanced_search($atts)
{
   $atts = shortcode_atts(array(
      'keyword' => '',
      'post_type' => '',
      'posts_per_page' => 10,
      'taxonomy' => '',
      'term' => '',
      'include_taxonomies' => 'false',
      'search_content' => 'false',
      'show_excerpt' => 'true'
   ), $atts);

   if (empty($atts['keyword'])) {
      return array('all_results' => array());
   }

   // Типы записей, которые нужно исключить из поиска
   $excluded_post_types = array(
      'header',
      'footer',
      'media_license',
      'page-header',
      'modal',
      'html_blocks'
   );

   if (empty($atts['post_type'])) {
      // Получаем все публичные типы записей и исключаем ненужные
      $all_post_types = get_post_types(array('public' => true));
      $post_types = array_diff($all_post_types, $excluded_post_types);
   } else {
      // Если указаны конкретные типы, фильтруем их
      $requested_types = array_map('trim', explode(',', $atts['post_type']));
      $post_types = array_diff($requested_types, $excluded_post_types);

      // Если после фильтрации не осталось типов, возвращаем пустой результат
      if (empty($post_types)) {
         return array('all_results' => array());
      }
   }

   $args = array(
      'post_type' => $post_types,
      'posts_per_page' => $atts['posts_per_page'],
      'post_status' => 'publish'
   );

   if (!empty($atts['taxonomy'])) {
      $tax_query = array(
         'taxonomy' => $atts['taxonomy'],
         'operator' => 'EXISTS'
      );

      if (!empty($atts['term'])) {
         $terms = array_map('trim', explode(',', $atts['term']));
         $tax_query['field'] = 'slug';
         $tax_query['terms'] = $terms;
         unset($tax_query['operator']);
      }

      $args['tax_query'] = array($tax_query);
   }

   $filter_callback = function ($where) use ($atts) {
      global $wpdb;

      $search_conditions = array();

      $search_conditions[] = "{$wpdb->posts}.post_title LIKE '%" . esc_sql($wpdb->esc_like($atts['keyword'])) . "%'";

      if ($atts['search_content'] === 'true') {
         $search_conditions[] = "{$wpdb->posts}.post_content LIKE '%" . esc_sql($wpdb->esc_like($atts['keyword'])) . "%'";
      }

      $where .= " AND (" . implode(' OR ', $search_conditions) . ")";

      return $where;
   };

   add_filter('posts_where', $filter_callback);

   $search_query = new WP_Query($args);

   remove_filter('posts_where', $filter_callback);

   // Получаем общее количество найденных записей (без ограничения posts_per_page)
   $total_found_args = $args;
   $total_found_args['posts_per_page'] = -1;
   $total_found_args['fields'] = 'ids';

   add_filter('posts_where', $filter_callback);
   $total_found_query = new WP_Query($total_found_args);
   remove_filter('posts_where', $filter_callback);

   $total_found_posts = $total_found_query->found_posts;

   $taxonomy_results = array();
   if ($atts['include_taxonomies'] === 'true') {
      $taxonomy_results = search_taxonomy_terms_by_name($atts['keyword'], $atts['taxonomy']);
   }

   $grouped_posts = array();
   if ($search_query->have_posts()) {
      while ($search_query->have_posts()) {
         $search_query->the_post();
         $post_type = get_post_type();
         $post_type_obj = get_post_type_object($post_type);

         if (!isset($grouped_posts[$post_type])) {
            $grouped_posts[$post_type] = array(
               'label' => $post_type_obj->labels->name,
               'posts' => array(),
               'total_found' => 0
            );
         }

         $post_info = array(
            'title' => get_the_title(),
            'permalink' => get_permalink(),
            'found_locations' => array(),
            'excerpts' => array(),
            'type' => 'post'
         );

         if (stripos(get_the_title(), $atts['keyword']) !== false) {
            $post_info['found_locations'][] = __('title', 'codeweber');

            if ($atts['show_excerpt'] === 'true') {
               $title_excerpt = get_text_excerpt(get_the_title(), $atts['keyword']);
               $post_info['excerpts'][] = $title_excerpt;
            }
         }

         if ($atts['search_content'] === 'true' && stripos(get_the_content(), $atts['keyword']) !== false) {
            $post_info['found_locations'][] = __('content', 'codeweber');
            if ($atts['show_excerpt'] === 'true') {
               $content_excerpt = get_text_excerpt(get_the_content(), $atts['keyword']);
               $post_info['excerpts'][] = $content_excerpt;
            }
         }

         $grouped_posts[$post_type]['posts'][] = $post_info;
      }
   }

   // Подсчитываем общее количество найденных записей для каждого типа
   foreach ($grouped_posts as $post_type => $group) {
      $total_for_type_args = array(
         'post_type' => $post_type,
         'posts_per_page' => -1,
         'fields' => 'ids',
         'post_status' => 'publish'
      );

      if (!empty($atts['taxonomy'])) {
         $total_for_type_args['tax_query'] = $args['tax_query'];
      }

      add_filter('posts_where', $filter_callback);
      $total_for_type_query = new WP_Query($total_for_type_args);
      remove_filter('posts_where', $filter_callback);

      $grouped_posts[$post_type]['total_found'] = $total_for_type_query->found_posts;
   }

   $grouped_taxonomies = array();
   if (!empty($taxonomy_results)) {
      foreach ($taxonomy_results as $term) {
         $taxonomy_obj = get_taxonomy($term->taxonomy);
         $taxonomy_label = $taxonomy_obj->labels->name;

         if (!isset($grouped_taxonomies[$taxonomy_label])) {
            $grouped_taxonomies[$taxonomy_label] = array(
               'items' => array(),
               'total_found' => 0
            );
         }

         $grouped_taxonomies[$taxonomy_label]['items'][] = array(
            'name' => $term->name,
            'permalink' => get_term_link($term),
            'type' => 'taxonomy'
         );
      }
   }

   // Подсчитываем общее количество для таксономий
   foreach ($grouped_taxonomies as $taxonomy_label => $group) {
      $grouped_taxonomies[$taxonomy_label]['total_found'] = count($group['items']);
   }

   $all_results = array();

   foreach ($grouped_posts as $post_type => $group) {
      $all_results[$group['label']] = array(
         'type' => 'post_type',
         'count' => count($group['posts']),
         'total_found' => $group['total_found'],
         'items' => $group['posts']
      );
   }

   foreach ($grouped_taxonomies as $taxonomy_label => $group) {
      $all_results[$taxonomy_label] = array(
         'type' => 'taxonomy',
         'count' => count($group['items']),
         'total_found' => $group['total_found'],
         'items' => $group['items']
      );
   }

   wp_reset_postdata();

   return array(
      'all_results' => $all_results,
      'grouped_posts' => $grouped_posts,
      'grouped_taxonomies' => $grouped_taxonomies,
      'total_found_posts' => $total_found_posts
   );
}

function search_taxonomy_terms_by_name($keyword, $specific_taxonomy = '')
{
   $taxonomies = array();

   if (!empty($specific_taxonomy)) {
      $taxonomies = array($specific_taxonomy);
   } else {
      $taxonomies = get_taxonomies(array('public' => true));
   }

   $found_terms = array();

   foreach ($taxonomies as $taxonomy) {
      $terms = get_terms(array(
         'taxonomy' => $taxonomy,
         'name__like' => $keyword,
         'hide_empty' => false,
         'number' => 10
      ));

      if (!is_wp_error($terms) && !empty($terms)) {
         $found_terms = array_merge($found_terms, $terms);
      }
   }

   return $found_terms;
}

function get_text_excerpt($text, $keyword, $context_length = 50)
{
   $keyword_pos = stripos($text, $keyword);

   if ($keyword_pos === false) {
      return '';
   }

   $start = max(0, $keyword_pos - $context_length);
   $end = min(strlen($text), $keyword_pos + strlen($keyword) + $context_length);

   $excerpt = substr($text, $start, $end - $start);

   if ($start > 0) {
      $excerpt = '...' . $excerpt;
   }
   if ($end < strlen($text)) {
      $excerpt = $excerpt . '...';
   }

   $excerpt = preg_replace('/(' . preg_quote($keyword, '/') . ')/i', '<mark style="background: yellow; padding: 2px 0;">$1</mark>', $excerpt);

   return $excerpt;
}

add_shortcode('ajax_search_form', 'ajax_search_form_shortcode');
function ajax_search_form_shortcode($atts)
{
   $atts = shortcode_atts(array(
      'placeholder' => __('Search...', 'codeweber'),
      'posts_per_page' => '10',
      'post_types' => '',
      'search_content' => 'false',
      'taxonomy' => '',
      'term' => '',
      'include_taxonomies' => 'false',
      'show_excerpt' => 'true',
      'class' => ''
   ), $atts);

   $unique_id = uniqid('search-');

   ob_start();
?>
   <div class="position-relative <?php echo esc_attr($atts['class']); ?>">
      <form class="search-form">
         <input
            type="text"
            id="<?php echo esc_attr($unique_id); ?>"
            class="search-form form-control"
            placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
            autocomplete="off"
            data-posts-per-page="<?php echo esc_attr($atts['posts_per_page']); ?>"
            data-post-types="<?php echo esc_attr($atts['post_types']); ?>"
            data-search-content="<?php echo esc_attr($atts['search_content']); ?>"
            data-taxonomy="<?php echo esc_attr($atts['taxonomy']); ?>"
            data-term="<?php echo esc_attr($atts['term']); ?>"
            data-include-taxonomies="<?php echo esc_attr($atts['include_taxonomies']); ?>"
            data-show-excerpt="<?php echo esc_attr($atts['show_excerpt']); ?>">
      </form>
   </div>
<?php
   return ob_get_clean();
}

add_action('wp_head', 'ajax_search_css');
function ajax_search_css()
{
?>
   <style>
      .search-results-container {
         display: none;
         z-index: 999!important;
      }

      .search-results-container:not(:empty) {
         display: block;
      }

      .search-result-item:hover {
         background-color: #f8f9fa !important;
      }


      .hover-bg-light:hover {
         background-color: #f8f9fa !important;
      }

      .search-loader {
         pointer-events: none;
      }
   </style>
<?php
}
