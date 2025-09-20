<?php
// Более простое и надежное решение с поддержкой системы размеров
add_action('show_user_profile', 'custom_user_avatar_field');
add_action('edit_user_profile', 'custom_user_avatar_field');

function custom_user_avatar_field($user)
{
   wp_enqueue_media();

   $avatar_url = get_user_meta($user->ID, 'custom_avatar', true);
   $avatar_id = get_user_meta($user->ID, 'custom_avatar_id', true);
?>
   <table class="form-table">
      <tr>
         <th><label for="custom_avatar_url"><?php _e('Avatar URL', 'codeweber'); ?></label></th>
         <td>
            <?php if ($avatar_url) : ?>
               <img src="<?php echo esc_url($avatar_url); ?>" style="width: 32px; height: 32px; object-fit: cover; margin-bottom: 10px; border-radius: 50%;" /><br>
               <?php if ($avatar_id) : ?>
                  <small>Attachment ID: <?php echo $avatar_id; ?></small><br>
               <?php endif; ?>
            <?php endif; ?>

            <input type="text" name="custom_avatar_url" id="custom_avatar_url" value="<?php echo esc_url($avatar_url); ?>" class="regular-text" />
            <input type="hidden" name="custom_avatar_id" id="custom_avatar_id" value="<?php echo esc_attr($avatar_id); ?>" />
            <button type="button" class="button" id="upload_avatar_button"><?php _e('Select Image', 'codeweber'); ?></button>
            <p class="description"><?php _e('Enter a URL or select an image from media library. Image will be resized to 100×100px.', 'codeweber'); ?></p>
         </td>
      </tr>
   </table>

   <script>
      jQuery(document).ready(function($) {
         $('#upload_avatar_button').on('click', function(e) {
            e.preventDefault();

            var frame = wp.media({
               title: '<?php _e("Select Avatar", "codeweber"); ?>',
               multiple: false,
               library: {
                  type: 'image'
               }
            });

            frame.on('select', function() {
               var attachment = frame.state().get('selection').first().toJSON();
               $('#custom_avatar_url').val(attachment.url);
               $('#custom_avatar_id').val(attachment.id);

               // Обновляем превью
               var img = $('<img>').attr('src', attachment.url)
                  .css({
                     'width': '32px',
                     'height': '32px',
                     'object-fit': 'cover',
                     'margin-bottom': '10px',
                     'border-radius': '50%'
                  });

               $('#custom_avatar_url').prev('img').remove();
               $('#custom_avatar_url').before(img);
            });

            frame.open();
         });
      });
   </script>
<?php
}

// Сохраняем настройки аватара
add_action('personal_options_update', 'save_custom_user_avatar');
add_action('edit_user_profile_update', 'save_custom_user_avatar');

function save_custom_user_avatar($user_id)
{
   if (!current_user_can('edit_user', $user_id)) {
      return false;
   }

   if (isset($_POST['custom_avatar_url'])) {
      $avatar_url = esc_url_raw($_POST['custom_avatar_url']);
      $avatar_id = isset($_POST['custom_avatar_id']) ? intval($_POST['custom_avatar_id']) : 0;

      // Получаем ID из URL если не указан
      if (!$avatar_id && $avatar_url) {
         $avatar_id = attachment_url_to_postid($avatar_url);
      }

      // Если есть ID, используем размер thumbnail
      if ($avatar_id) {
         $staff_url = wp_get_attachment_image_url($avatar_id, 'thumbnail');
         if ($staff_url) {
            $avatar_url = $staff_url;
         }

         // Помечаем изображение как пользовательский аватар
         update_post_meta($avatar_id, '_user_avatar_for', $user_id);
         update_post_meta($avatar_id, '_upload_context', 'user');

         // Принудительно обрабатываем размеры
         process_user_avatar_sizes($avatar_id);
      }

      update_user_meta($user_id, 'custom_avatar', $avatar_url);
      update_user_meta($user_id, 'custom_avatar_id', $avatar_id);
   } else {
      // Удаляем аватар
      delete_user_meta($user_id, 'custom_avatar');
      delete_user_meta($user_id, 'custom_avatar_id');
   }
}

// Функция для принудительной обработки размеров аватара
function process_user_avatar_sizes($attachment_id)
{
   $file_path = get_attached_file($attachment_id);

   if (!$file_path || !file_exists($file_path)) {
      return false;
   }

   require_once(ABSPATH . 'wp-admin/includes/image.php');

   // Регенерируем метаданные
   $metadata = wp_generate_attachment_metadata($attachment_id, $file_path);

   if (!empty($metadata['sizes'])) {
      $upload_dir = wp_upload_dir();
      $keep_size = 'thumbnail'; // Размер который оставляем

      // Удаляем все размеры кроме нужного
      foreach ($metadata['sizes'] as $size_name => $size_info) {
         if ($size_name !== $keep_size) {
            $file_path = path_join($upload_dir['basedir'], dirname($metadata['file']) . '/' . $size_info['file']);

            // Удаляем файл
            if (file_exists($file_path)) {
               @unlink($file_path);
            }

            unset($metadata['sizes'][$size_name]);
         }
      }

      // Обновляем метаданные
      wp_update_attachment_metadata($attachment_id, $metadata);
   }

   return true;
}

// Перехватываем генерацию метаданных для пользовательских аватаров
add_filter('wp_generate_attachment_metadata', 'intercept_avatar_metadata_generation', 10, 2);

function intercept_avatar_metadata_generation($metadata, $attachment_id)
{
   // Проверяем, является ли это пользовательским аватаром
   $is_user_avatar = get_post_meta($attachment_id, '_upload_context', true) === 'user';

   if ($is_user_avatar && !empty($metadata['sizes'])) {
      $upload_dir = wp_upload_dir();
      $keep_size = 'thumbnail';

      // Удаляем все размеры кроме нужного
      foreach ($metadata['sizes'] as $size_name => $size_info) {
         if ($size_name !== $keep_size) {
            $file_path = path_join($upload_dir['basedir'], dirname($metadata['file']) . '/' . $size_info['file']);

            if (file_exists($file_path)) {
               @unlink($file_path);
            }

            unset($metadata['sizes'][$size_name]);
         }
      }
   }

   return $metadata;
}

// Используем кастомный аватар
add_filter('get_avatar_data', 'use_custom_avatar', 10, 2);

function use_custom_avatar($args, $id_or_email)
{
   $user = false;

   if (is_numeric($id_or_email)) {
      $user = get_user_by('id', (int)$id_or_email);
   } elseif (is_object($id_or_email)) {
      if (!empty($id_or_email->user_id)) {
         $user = get_user_by('id', (int)$id_or_email->user_id);
      }
   } else {
      $user = get_user_by('email', $id_or_email);
   }

   if ($user && is_object($user)) {
      $custom_avatar = get_user_meta($user->ID, 'custom_avatar', true);

      if (!empty($custom_avatar)) {
         $args['url'] = $custom_avatar;
         $args['width'] = 32;
         $args['height'] = 32;
      }
   }

   return $args;
}

// Отключаем стандартную фильтрацию размеров для страниц профиля
add_action('admin_init', 'disable_size_filtering_for_profiles');

function disable_size_filtering_for_profiles()
{
   $current_screen = get_current_screen();

   if ($current_screen && ($current_screen->id === 'profile' || $current_screen->id === 'user-edit')) {
      remove_filter('wp_generate_attachment_metadata', 'codeweber_filter_attachment_sizes_by_post_type', 10);
   }
}

// Добавляем фильтр для пользовательских аватаров
add_filter('codeweber_allowed_image_sizes', 'user_avatar_allowed_sizes');

function user_avatar_allowed_sizes($sizes)
{
   $sizes['user'] = ['thumbnail'];
   return $sizes;
}


// Принудительная очистка размеров при загрузке через медиабиблиотеку
add_action('wp_ajax_upload-attachment', 'force_avatar_size_cleanup', 1);

function force_avatar_size_cleanup()
{
   if (
      !empty($_SERVER['HTTP_REFERER']) &&
      (strpos($_SERVER['HTTP_REFERER'], 'profile.php') !== false ||
         strpos($_SERVER['HTTP_REFERER'], 'user-edit.php') !== false)
   ) {

      add_filter('wp_generate_attachment_metadata', 'intercept_avatar_metadata_generation', 10, 2);
   }
}

// Add "Position" field to user profile
add_action('show_user_profile', 'add_user_position_field');
add_action('edit_user_profile', 'add_user_position_field');

function add_user_position_field($user) {
    ?>
    <table class="form-table">
        <tr>
            <th><label for="user_position"><?php _e('Position', 'codeweber'); ?></label></th>
            <td>
                <input type="text" name="user_position" id="user_position" 
                       value="<?php echo esc_attr(get_the_author_meta('user_position', $user->ID)); ?>" 
                       class="regular-text" />
                <p class="description"><?php _e('Enter the user position', 'codeweber'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

// Save "Position" field
add_action('personal_options_update', 'save_user_position_field');
add_action('edit_user_profile_update', 'save_user_position_field');

function save_user_position_field($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    if (isset($_POST['user_position'])) {
        $position = sanitize_text_field($_POST['user_position']);
        update_user_meta($user_id, 'user_position', $position);
    }
}

// Add "Position" column to users table
add_filter('manage_users_columns', 'add_user_position_column');
add_filter('manage_users_custom_column', 'show_user_position_column', 10, 3);

function add_user_position_column($columns) {
    $columns['user_position'] = __('Position', 'codeweber');
    return $columns;
}

function show_user_position_column($value, $column_name, $user_id) {
    if ($column_name === 'user_position') {
        $position = get_user_meta($user_id, 'user_position', true);
        return $position ? esc_html($position) : '—';
    }
    return $value;
}

// Make "Position" column sortable
add_filter('manage_users_sortable_columns', 'make_user_position_column_sortable');

function make_user_position_column_sortable($columns) {
    $columns['user_position'] = 'user_position';
    return $columns;
}

// Handle sorting by position
add_action('pre_get_users', 'handle_user_position_sorting');

function handle_user_position_sorting($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ($orderby === 'user_position') {
        $query->set('meta_key', 'user_position');
        $query->set('orderby', 'meta_value');
    }
}


if (!function_exists('codeweber_author_info')) {
    /**
     * Display author information with optional button
     *
     * @param bool $show_button Whether to show the "All Posts" button
     * @param string $button_class Additional CSS classes for the button
     * @param string $avatar_size Size of the avatar image
     * @return void
     */
    function codeweber_author_info($show_button = true, $button_class = '', $avatar_size = 'thumbnail') {
        $user_id = get_the_author_meta('ID');
        
        // Check both possible avatar meta keys
        $avatar_id = get_user_meta($user_id, 'avatar_id', true);
        if (empty($avatar_id)) {
            $avatar_id = get_user_meta($user_id, 'custom_avatar_id', true);
        }
        
        // Get job title or use default
        $job_title = get_user_meta($user_id, 'user_position', true);
        if (empty($job_title)) {
            $job_title = __('Writer', 'codeweber');
        }
        
        // Default button classes
        $default_button_class = 'btn btn-sm btn-soft-ash ' . GetThemeButton('rounded mt-2') . ' btn-icon btn-icon-start mb-0';
        $final_button_class = $button_class ? $button_class : $default_button_class;
        ?>
        
        <div class="author-info d-md-flex align-items-center mb-3">
            <div class="d-flex align-items-center">
                <?php if (!empty($avatar_id)) : 
                    $avatar_src = wp_get_attachment_image_src($avatar_id, $avatar_size);
                ?>
                    <img decoding="async" class="avatar w-48 me-3" alt="<?php the_author_meta('display_name'); ?>" src="<?php echo esc_url($avatar_src[0]); ?>">
                <?php else : ?>
                    <?php echo get_avatar(get_the_author_meta('user_email'), 96); ?>
                <?php endif; ?>

                <div>
                    <div class="h6">
                        <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>" class="link-dark">
                            <?php the_author_meta('first_name'); ?> <?php the_author_meta('last_name'); ?>
                        </a>
                    </div>
                    <span class="post-meta fs-15"><?php echo esc_html($job_title); ?></span>
                </div>
            </div>

            <?php if ($show_button) : ?>
            <div class="mt-3 mt-md-0 ms-auto">
                <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>" class="<?php echo esc_attr($final_button_class); ?>">
                    <i class="uil uil-file-alt"></i> <?php esc_html_e('All Posts', 'codeweber'); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
        <!-- /.author-info -->
        <?php
    }
}