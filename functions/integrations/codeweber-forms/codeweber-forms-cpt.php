<?php
/**
 * CodeWeber Forms CPT Registration
 * 
 * Custom Post Type for storing form configurations
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsCPT {
    public function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('save_post_codeweber_form', [$this, 'save_form_meta'], 10, 3);
        // Автоматическая вставка блока теперь выполняется через JavaScript в редакторе Gutenberg
        // add_action('wp_insert_post', [$this, 'auto_insert_form_block'], 10, 3);
        
        // Добавляем колонку с шорткодом в список форм
        add_filter('manage_codeweber_form_posts_columns', [$this, 'add_shortcode_column']);
        add_action('manage_codeweber_form_posts_custom_column', [$this, 'fill_shortcode_column'], 10, 2);
        
        // Подключаем скрипт для копирования шорткода
        add_action('admin_footer', [$this, 'enqueue_copy_shortcode_script']);
    }
    
    /**
     * Register Custom Post Type for forms
     */
    public function register_post_type() {
        $labels = [
            'name' => __('Формы', 'codeweber'),
            'singular_name' => __('Форма', 'codeweber'),
            'menu_name' => __('Формы', 'codeweber'),
            'add_new' => __('Добавить форму', 'codeweber'),
            'add_new_item' => __('Добавить новую форму', 'codeweber'),
            'edit_item' => __('Редактировать форму', 'codeweber'),
            'new_item' => __('Новая форма', 'codeweber'),
            'view_item' => __('Просмотр формы', 'codeweber'),
            'search_items' => __('Искать формы', 'codeweber'),
            'not_found' => __('Формы не найдены', 'codeweber'),
            'not_found_in_trash' => __('В корзине форм не найдено', 'codeweber'),
        ];
        
        $args = [
            'label' => __('Формы', 'codeweber'),
            'labels' => $labels,
            'description' => __('Контактные формы, созданные через CodeWeber Forms', 'codeweber'),
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_rest' => true, // Важно для Gutenberg!
            'rest_base' => 'codeweber',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'has_archive' => false,
            'show_in_menu' => true,
            'menu_position' => 30,
            'menu_icon' => 'dashicons-email-alt',
            'capability_type' => 'post',
            'supports' => ['title', 'editor'], // Editor для Gutenberg блока
            'can_export' => true,
        ];
        
        register_post_type('codeweber_form', $args);
    }
    
    /**
     * Save form meta fields
     */
    public function save_form_meta($post_id, $post, $update) {
        // Проверка nonce
        if (!isset($_POST['codeweber_forms_meta_nonce']) || 
            !wp_verify_nonce($_POST['codeweber_forms_meta_nonce'], 'save_form_meta')) {
            return;
        }
        
        // Проверка прав
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Проверка автосохранения
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Сохранение метаполей формы
        $meta_fields = [
            '_form_recipient_email',
            '_form_sender_email',
            '_form_sender_name',
            '_form_subject',
            '_form_success_message',
            '_form_error_message',
            '_form_enable_captcha',
            '_form_rate_limit_enabled',
            '_form_auto_reply_enabled',
        ];
        
        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
    
    /**
     * Автоматически добавляет блок формы при создании нового CPT поста
     */
    public function auto_insert_form_block($post_id, $post, $update) {
        // Только для новых постов
        if ($update) {
            return;
        }
        
        // Только для CPT codeweber_form
        if (!isset($post->post_type) || $post->post_type !== 'codeweber_form') {
            return;
        }
        
        // Проверяем, есть ли уже контент
        if (!empty($post->post_content)) {
            return;
        }
        
        // Проверяем права
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Проверяем, что это не автосохранение
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Проверяем, что это не ревизия
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        // Создаем блок формы с дефолтными настройками
        // Используем правильный формат для serialize_block
        $form_block = [
            'blockName' => 'codeweber-blocks/form',
            'attrs' => [
                'formId' => (string) $post_id,
                'formName' => !empty($post->post_title) ? $post->post_title : __('Contact Form', 'codeweber'),
                'submitButtonText' => __('Send Message', 'codeweber'),
                'submitButtonClass' => 'btn btn-primary',
            ],
            'innerBlocks' => [],
            'innerContent' => [''], // Пустая строка для блока без внутреннего контента
        ];
        
        // Конвертируем в HTML блоков
        $content = serialize_block($form_block);
        
        // Обновляем пост (убираем хук, чтобы избежать рекурсии)
        remove_action('wp_insert_post', [$this, 'auto_insert_form_block'], 10);
        wp_update_post([
            'ID' => $post_id,
            'post_content' => $content,
        ]);
        add_action('wp_insert_post', [$this, 'auto_insert_form_block'], 10, 3);
    }
    
    /**
     * Добавить колонку с шорткодом в список форм
     */
    public function add_shortcode_column($columns) {
        // Вставляем колонку после заголовка
        $new_columns = [];
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['shortcode'] = __('Shortcode', 'codeweber');
            }
        }
        return $new_columns;
    }
    
    /**
     * Заполнить колонку с шорткодом
     */
    public function fill_shortcode_column($column, $post_id) {
        if ($column === 'shortcode') {
            $shortcode = '[codeweber_form id="' . esc_attr($post_id) . '"]';
            ?>
            <div style="display: flex; align-items: center; gap: 8px;">
                <code style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-family: monospace;">
                    <?php echo esc_html($shortcode); ?>
                </code>
                <button 
                    type="button" 
                    class="button button-small copy-shortcode-btn" 
                    data-shortcode="<?php echo esc_attr($shortcode); ?>"
                    style="height: 24px; line-height: 22px; padding: 0 8px;"
                    title="<?php esc_attr_e('Copy shortcode', 'codeweber'); ?>"
                >
                    <span class="dashicons dashicons-clipboard" style="font-size: 14px; width: 14px; height: 14px; line-height: 22px;"></span>
                </button>
            </div>
            <?php
        }
    }
    
    /**
     * Подключить скрипт для копирования шорткода
     */
    public function enqueue_copy_shortcode_script() {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'codeweber_form' || $screen->base !== 'edit') {
            return;
        }
        ?>
        <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                // Обработчик клика на кнопку копирования
                $(document).on('click', '.copy-shortcode-btn', function(e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var shortcode = $btn.data('shortcode');
                    
                    // Создаем временный textarea для копирования
                    var $temp = $('<textarea>');
                    $('body').append($temp);
                    $temp.val(shortcode).select();
                    
                    try {
                        document.execCommand('copy');
                        $temp.remove();
                        
                        // Показываем уведомление об успешном копировании
                        var $originalIcon = $btn.find('.dashicons');
                        $originalIcon.removeClass('dashicons-clipboard').addClass('dashicons-yes-alt');
                        $btn.css('color', '#46b450');
                        
                        setTimeout(function() {
                            $originalIcon.removeClass('dashicons-yes-alt').addClass('dashicons-clipboard');
                            $btn.css('color', '');
                        }, 2000);
                    } catch (err) {
                        $temp.remove();
                        alert('<?php echo esc_js(__('Failed to copy shortcode', 'codeweber')); ?>');
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
    }
}



