<?php

/**
 * CPT для хранения подписчиков и их согласий
 */

class Consent_CPT
{

   private static $instance = null;
   private $post_type = 'consent_subscriber';

   public static function get_instance()
   {
      if (null === self::$instance) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   private function __construct()
   {
      $this->init_hooks();
   }

   private function init_hooks()
   {
      add_action('init', [$this, 'register_post_type']);
      add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
      add_action('save_post', [$this, 'save_meta_boxes'], 10, 2);
      add_filter('manage_' . $this->post_type . '_posts_columns', [$this, 'add_custom_columns']);
      add_action('manage_' . $this->post_type . '_posts_custom_column', [$this, 'render_custom_columns'], 10, 2);
      add_filter('manage_edit-' . $this->post_type . '_sortable_columns', [$this, 'add_sortable_columns']);
      add_action('pre_get_posts', [$this, 'handle_sortable_columns']);
   }

   /**
    * Регистрация CPT
    */
   public function register_post_type()
   {
      $labels = array(
         'name'                  => __('Data Subjects', 'codeweber'),
         'singular_name'         => __('Data Subject', 'codeweber'),
         'menu_name'             => __('Consent Data Subjects', 'codeweber'),
         'name_admin_bar'        => __('Data Subject', 'codeweber'),
         'edit_item'             => __('Edit Data Subject', 'codeweber'),
         'view_item'             => __('View Data Subject', 'codeweber'),
         'all_items'             => __('All Data Subjects', 'codeweber'),
         'search_items'          => __('Search Data Subjects', 'codeweber'),
         'parent_item_colon'     => __('Parent Data Subjects:', 'codeweber'),
         'not_found'             => __('No data subjects found.', 'codeweber'),
         'not_found_in_trash'    => __('No data subjects found in Trash.', 'codeweber'),
      );


      $args = array(
         'labels'             => $labels,
         'public'             => false,
         'publicly_queryable' => false,
         'show_ui'            => true,
         // вместо true указываем slug CPT "legal"
         'show_in_menu'       => 'edit.php?post_type=legal',
         'query_var'          => true,
         'rewrite'            => array('slug' => 'consent-subscriber'),
         'capability_type'    => 'post',
         'has_archive'        => false,
         'hierarchical'       => false,
         'menu_position'      => null,
         'menu_icon'          => 'dashicons-groups',
         'supports'           => array('title'),
         'show_in_rest'       => false,
         'capabilities'       => array(
            'create_posts' => 'do_not_allow', // запрет на создание
         ),
         'map_meta_cap'       => true,
      );

      register_post_type($this->post_type, $args);
   }

   /**
    * Добавление метабоксов
    */
   public function add_meta_boxes()
   {
      add_meta_box(
         'consent_subscriber_details',
         __('Data Subjects Details', 'codeweber'),
         [$this, 'render_subscriber_details_meta_box'],
         $this->post_type,
         'normal',
         'high'
      );

      add_meta_box(
         'consent_subscriber_consents',
         __('Consents History', 'codeweber'),
         [$this, 'render_consents_history_meta_box'],
         $this->post_type,
         'normal',
         'default'
      );
   }

   /**
    * Рендер метабокса деталей подписчика
    */
   public function render_subscriber_details_meta_box($post)
   {
      wp_nonce_field('consent_subscriber_nonce', 'consent_subscriber_nonce');

      $email = get_post_meta($post->ID, '_subscriber_email', true);
      $phone = get_post_meta($post->ID, '_subscriber_phone', true);
      $user_id = get_post_meta($post->ID, '_subscriber_user_id', true);
      $registration_date = get_post_meta($post->ID, '_subscriber_registration_date', true);
?>
      <div class="consent-subscriber-details">
         <p>
            <strong><?php _e('Email:', 'codeweber'); ?></strong><br>
            <span class="subscriber-detail-value"><?php echo esc_html($email); ?></span>
            <input type="hidden" name="subscriber_email" value="<?php echo esc_attr($email); ?>">
         </p>

         <p>
            <strong><?php _e('Phone:', 'codeweber'); ?></strong><br>
            <span class="subscriber-detail-value"><?php echo esc_html($phone); ?></span>
            <input type="hidden" name="subscriber_phone" value="<?php echo esc_attr($phone); ?>">
         </p>

         <p>
            <strong><?php _e('User ID (if registered):', 'codeweber'); ?></strong><br>
            <span class="subscriber-detail-value">
               <?php if ($user_id) : ?>
                  <?php $user = get_user_by('id', $user_id); ?>
                  <?php if ($user) : ?>
                     <a href="<?php echo esc_url(get_edit_user_link($user_id)); ?>" target="_blank">
                        <?php echo esc_html($user->display_name); ?>
                     </a>
                     (<?php echo esc_html($user_id); ?>)
                  <?php else : ?>
                     <?php echo esc_html($user_id); ?>
                  <?php endif; ?>
               <?php else : ?>
                  <?php _e('Not registered', 'codeweber'); ?>
               <?php endif; ?>
            </span>
            <input type="hidden" name="subscriber_user_id" value="<?php echo esc_attr($user_id); ?>">
         </p>


         <?php if ($registration_date) : ?>
            <p>
               <strong><?php _e('Registration Date:', 'codeweber'); ?></strong><br>
               <span class="subscriber-detail-value">
                  <?php echo date_i18n(get_option('date_format') . ' H:i', strtotime($registration_date)); ?>
               </span>
            </p>
         <?php endif; ?>
      </div>

      <style>
         .consent-subscriber-details {
            line-height: 1.6;
         }

         .subscriber-detail-value {
            display: inline-block;
            padding: 5px 0;
            font-size: 14px;
            color: #2c3338;
         }

         .consent-subscriber-details p {
            margin-bottom: 15px;
         }
      </style>
   <?php
   }

   /**
    * Рендер метабокса истории согласий
    */
   public function render_consents_history_meta_box($post)
   {
      $consents = get_post_meta($post->ID, '_subscriber_consents', true);
      $consents = is_array($consents) ? $consents : [];
   ?>
      <div class="consent-subscriber-consents">
         <?php if (empty($consents)) : ?>
            <p><?php _e('No consents recorded yet.', 'codeweber'); ?></p>
         <?php else : ?>
            <style>
               .consent-details-table {
                  width: 100%;
                  border-collapse: collapse;
                  margin-bottom: 15px;
               }

               .consent-details-table th {
                  background-color: #f1f1f1;
                  padding: 8px;
                  text-align: left;
                  border: 1px solid #ddd;
               }

               .consent-details-table td {
                  padding: 8px;
                  border: 1px solid #ddd;
                  vertical-align: top;
               }

               .consent-item {
                  margin-bottom: 20px;
                  padding: 15px;
                  border: 1px solid #ccc;
                  border-radius: 4px;
                  background-color: #f9f9f9;
               }

               .consent-header {
                  background-color: #e0e0e0;
                  padding: 10px;
                  margin: -15px -15px 15px -15px;
                  border-bottom: 1px solid #ccc;
                  font-weight: bold;
               }

               .consent-details {
                  display: grid;
                  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                  gap: 15px;
               }

               .consent-detail-item {
                  margin-bottom: 8px;
               }

               .consent-detail-label {
                  font-weight: bold;
                  color: #555;
                  margin-bottom: 3px;
               }

               .consent-detail-value {
                  word-break: break-word;
               }
            </style>

            <?php foreach ($consents as $index => $consent) : ?>
               <div class="consent-item">
                  <div class="consent-header">
                     <?php printf(__('Consent #%d - %s', 'codeweber'), $index + 1, date_i18n(get_option('date_format') . ' H:i', strtotime($consent['date']))); ?>
                  </div>

                  <div class="consent-details">
                     <div class="consent-detail-item">
                        <div class="consent-detail-label"><?php _e('Consent Label', 'codeweber'); ?></div>
                        <div class="consent-detail-value"><?php echo esc_html($consent['type'] ?? ''); ?></div>
                     </div>

                     <div class="consent-detail-item">
                        <div class="consent-detail-label"><?php _e('Session ID', 'codeweber'); ?></div>
                        <div class="consent-detail-value"><?php echo esc_html($consent['session_id'] ?? ''); ?></div>
                     </div>

                     <div class="consent-detail-item">
                        <div class="consent-detail-label"><?php _e('Form Title', 'codeweber'); ?></div>
                        <div class="consent-detail-value"><?php echo esc_html($consent['form_title'] ?? ''); ?></div>
                     </div>

                     <div class="consent-detail-item">
                        <div class="consent-detail-label"><?php _e('Agreed on', 'codeweber'); ?></div>
                        <div class="consent-detail-value"><?php echo date_i18n(get_option('date_format') . ' H:i', strtotime($consent['date'])); ?></div>
                     </div>

                     <div class="consent-detail-item">
                        <div class="consent-detail-label"><?php _e('IP Address', 'codeweber'); ?></div>
                        <div class="consent-detail-value"><?php echo esc_html($consent['ip'] ?? ''); ?></div>
                     </div>

                     <div class="consent-detail-item">
                        <div class="consent-detail-label"><?php _e('User Agent', 'codeweber'); ?></div>
                        <div class="consent-detail-value"><?php echo esc_html($consent['user_agent'] ?? __('Not provided', 'codeweber')); ?></div>
                     </div>

                     <div class="consent-detail-item">
                        <div class="consent-detail-label"><?php _e('Document', 'codeweber'); ?></div>
                        <div class="consent-detail-value"><?php echo esc_html($consent['document_title'] ?? ''); ?></div>
                     </div>

                     <div class="consent-detail-item">
                        <div class="consent-detail-label"><?php _e('Consent Html', 'codeweber'); ?></div>
                        <div class="consent-detail-value"><?php echo wp_kses_post($consent['acceptance_html'] ?? ''); ?></div>
                     </div>

                     <div class="consent-detail-item">
                        <div class="consent-detail-label"><?php _e('Document Link', 'codeweber'); ?></div>
                        <div class="consent-detail-value">
                           <?php if (!empty($consent['document_url'])) : ?>
                              <a href="<?php echo esc_url($consent['document_url']); ?>" target="_blank">
                                 <?php echo esc_html($consent['document_url']); ?>
                              </a>
                           <?php else : ?>
                              <?php _e('Not provided', 'codeweber'); ?>
                           <?php endif; ?>
                        </div>
                     </div>

                     <div class="consent-detail-item">
                        <div class="consent-detail-label"><?php _e('Agreed on Page', 'codeweber'); ?></div>
                        <div class="consent-detail-value">
                           <?php if (!empty($consent['page_url'])) : ?>
                              <a href="<?php echo esc_url($consent['page_url']); ?>" target="_blank">
                                 <?php echo esc_html($consent['page_url']); ?>
                              </a>
                           <?php else : ?>
                              <?php _e('Not provided', 'codeweber'); ?>
                           <?php endif; ?>
                        </div>
                     </div>

                     <div class="consent-detail-item">
                        <div class="consent-detail-label"><?php _e('Phone', 'codeweber'); ?></div>
                        <div class="consent-detail-value"><?php echo esc_html($consent['phone'] ?? __('Not provided', 'codeweber')); ?></div>
                     </div>

                     <div class="consent-detail-item">
                        <div class="consent-detail-label"><?php _e('Revision', 'codeweber'); ?></div>
                        <div class="consent-detail-value"><?php echo wp_kses_post($consent['revision'] ?? ''); ?></div>
                     </div>
                  </div>
               </div>
            <?php endforeach; ?>
         <?php endif; ?>
      </div>
<?php
   }


   /**
    * Сохранение метабоксов
    */
   public function save_meta_boxes($post_id, $post)
   {
      // Проверяем nonce
      if (
         !isset($_POST['consent_subscriber_nonce']) ||
         !wp_verify_nonce($_POST['consent_subscriber_nonce'], 'consent_subscriber_nonce')
      ) {
         return;
      }

      // Проверяем права пользователя
      if (!current_user_can('edit_post', $post_id)) {
         return;
      }

      // Проверяем тип поста
      if ($post->post_type !== $this->post_type) {
         return;
      }

      // УБРАЛИ сохранение email, phone и user_id так как они теперь только для чтения
      // Эти данные должны устанавливаться только при создании подписчика

      // Если это новый пост, устанавливаем дату регистрации
      if (empty(get_post_meta($post_id, '_subscriber_registration_date', true))) {
         update_post_meta($post_id, '_subscriber_registration_date', current_time('mysql'));
      }
   }

   /**
    * Добавление кастомных колонок
    */
   public function add_custom_columns($columns)
   {
      $new_columns = [
         'cb' => $columns['cb'],
         'title' => $columns['title'],
         'email' => __('Email', 'codeweber'),
         'phone' => __('Phone', 'codeweber'),
         'user_id' => __('User ID', 'codeweber'),
         'consents_count' => __('Consents', 'codeweber'),
         'registration_date' => __('Registration Date', 'codeweber'),
         'date' => $columns['date']
      ];

      return $new_columns;
   }

   /**
    * Рендер кастомных колонок
    */
   public function render_custom_columns($column, $post_id)
   {
      switch ($column) {
         case 'email':
            echo esc_html(get_post_meta($post_id, '_subscriber_email', true));
            break;

         case 'phone':
            echo esc_html(get_post_meta($post_id, '_subscriber_phone', true));
            break;

         case 'user_id':
            $user_id = get_post_meta($post_id, '_subscriber_user_id', true);

            if ($user_id && is_numeric($user_id)) {
               $user = get_user_by('id', $user_id);

               if ($user instanceof WP_User) {
                  // ссылка на профиль в админке
                  echo '<a href="' . esc_url(get_edit_user_link($user->ID)) . '">'
                     . esc_html($user->user_login) . '</a>';
               } else {
                  echo __('User not found', 'codeweber');
               }
            } else {
               echo __('Not registered', 'codeweber');
            }
            break;


         case 'consents_count':
            $consents = get_post_meta($post_id, '_subscriber_consents', true);
            echo is_array($consents) ? count($consents) : 0;
            break;

         case 'registration_date':
            $date = get_post_meta($post_id, '_subscriber_registration_date', true);
            echo $date ? date_i18n(get_option('date_format'), strtotime($date)) : '—';
            break;
      }
   }

   /**
    * Добавление сортируемых колонок
    */
   public function add_sortable_columns($columns)
   {
      $columns['email'] = 'email';
      $columns['registration_date'] = 'registration_date';
      $columns['consents_count'] = 'consents_count';
      return $columns;
   }

   /**
    * Обработка сортировки колонок
    */
   public function handle_sortable_columns($query)
   {
      if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== $this->post_type) {
         return;
      }

      $orderby = $query->get('orderby');

      switch ($orderby) {
         case 'email':
            $query->set('meta_key', '_subscriber_email');
            $query->set('orderby', 'meta_value');
            break;

         case 'registration_date':
            $query->set('meta_key', '_subscriber_registration_date');
            $query->set('orderby', 'meta_value');
            break;

         case 'consents_count':
            // Сортировка по количеству согласий требует более сложной логики
            break;
      }
   }

   /**
    * Найти или создать подписчика по email
    */
   public function find_or_create_subscriber($email, $phone = '', $user_id = 0)
   {
      $email = sanitize_email($email);
      if (!is_email($email)) {
         return false;
      }

      // Ищем существующего подписчика
      $existing = get_posts([
         'post_type' => $this->post_type,
         'meta_key' => '_subscriber_email',
         'meta_value' => $email,
         'posts_per_page' => 1,
         'post_status' => 'any'
      ]);

      if (!empty($existing)) {
         $subscriber_id = $existing[0]->ID;

         // Обновляем только если переданы новые данные
         if ($phone) {
            $current_phone = get_post_meta($subscriber_id, '_subscriber_phone', true);
            if (!$current_phone) {
               update_post_meta($subscriber_id, '_subscriber_phone', sanitize_text_field($phone));
            }
         }

         if ($user_id) {
            $current_user_id = get_post_meta($subscriber_id, '_subscriber_user_id', true);
            if (!$current_user_id) {
               update_post_meta($subscriber_id, '_subscriber_user_id', intval($user_id));
            }
         }

         return $subscriber_id;
      }

      // Создаем нового подписчика
      $post_id = wp_insert_post([
         'post_title' => $email,
         'post_type' => $this->post_type,
         'post_status' => 'publish'
      ]);

      if (is_wp_error($post_id)) {
         return false;
      }

      update_post_meta($post_id, '_subscriber_email', $email);

      if ($phone) {
         update_post_meta($post_id, '_subscriber_phone', sanitize_text_field($phone));
      }

      if ($user_id) {
         update_post_meta($post_id, '_subscriber_user_id', intval($user_id));
      }

      update_post_meta($post_id, '_subscriber_registration_date', current_time('mysql'));

      return $post_id;
   }

   /**
    * Добавить согласие подписчику
    */

   public function add_consent($subscriber_id, $consent_data)
   {
      $consents = get_post_meta($subscriber_id, '_subscriber_consents', true);
      $consents = is_array($consents) ? $consents : [];

      // Сохраняем HTML-контент ревизии как есть
      $consents[] = [
         'date' => $consent_data['date'] ?? current_time('mysql'),
         'type' => $consent_data['type'] ?? '',
         'document_title' => $consent_data['document_title'] ?? '',
         'document_url' => $consent_data['document_url'] ?? '',
         'ip' => $consent_data['ip'] ?? '',
         'user_agent' => $consent_data['user_agent'] ?? '',
         'form_title' => $consent_data['form_title'] ?? '',
         'session_id' => $consent_data['session_id'] ?? '',
         'revision' => $consent_data['revision'] ?? '', // HTML сохраняется как есть
         'acceptance_html' => $consent_data['acceptance_html'] ?? '',
         'page_url' => $consent_data['page_url'] ?? '',
         'phone' => $consent_data['phone'] ?? ''
      ];

      return update_post_meta($subscriber_id, '_subscriber_consents', $consents);
   }

   /**
    * Получить подписчика по email
    */
   public function get_subscriber_by_email($email)
   {
      $email = sanitize_email($email);
      $posts = get_posts([
         'post_type' => $this->post_type,
         'meta_key' => '_subscriber_email',
         'meta_value' => $email,
         'posts_per_page' => 1,
         'post_status' => 'any'
      ]);

      return !empty($posts) ? $posts[0] : false;
   }

   /**
    * Получить подписчика по user_id
    */
   public function get_subscriber_by_user_id($user_id)
   {
      $posts = get_posts([
         'post_type' => $this->post_type,
         'meta_key' => '_subscriber_user_id',
         'meta_value' => intval($user_id),
         'posts_per_page' => 1,
         'post_status' => 'any'
      ]);

      return !empty($posts) ? $posts[0] : false;
   }
}
