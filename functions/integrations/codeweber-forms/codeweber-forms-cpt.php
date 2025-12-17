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
        // НОВОЕ: Хук для сохранения типа формы из блока (работает и через REST API)
        add_action('rest_after_insert_codeweber_form', [$this, 'save_form_type_from_block'], 10, 3);
        add_action('wp_insert_post', [$this, 'save_form_type_from_block'], 10, 3);
        // Автоматическая вставка блока теперь выполняется через JavaScript в редакторе Gutenberg
        // add_action('wp_insert_post', [$this, 'auto_insert_form_block'], 10, 3);
        
        // Добавляем колонки в список форм
        add_filter('manage_codeweber_form_posts_columns', [$this, 'add_custom_columns']);
        add_action('manage_codeweber_form_posts_custom_column', [$this, 'fill_custom_columns'], 10, 2);
        
        // Подключаем скрипт для копирования шорткода
        add_action('admin_footer', [$this, 'enqueue_copy_shortcode_script']);
        
        // НОВОЕ: Автоматический запуск миграции CPT форм при первой загрузке
        // ВАЖНО: Миграция затрагивает только CPT формы, legacy формы не трогаем
        add_action('admin_init', [$this, 'maybe_run_cpt_migration']);
        
        // НОВОЕ: Добавляем фильтр по типу формы на странице списка CPT форм
        add_action('restrict_manage_posts', [$this, 'add_form_type_filter']);
        add_filter('parse_query', [$this, 'filter_posts_by_form_type']);
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
     * Добавить кастомные колонки в список форм
     */
    public function add_custom_columns($columns) {
        // Вставляем колонки после заголовка
        $new_columns = [];
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['form_type'] = __('Form Type', 'codeweber');
                $new_columns['shortcode'] = __('Shortcode', 'codeweber');
            }
        }
        return $new_columns;
    }
    
    /**
     * Заполнить кастомные колонки
     */
    public function fill_custom_columns($column, $post_id) {
        if ($column === 'form_type') {
            // НОВОЕ: Получаем тип формы через единую функцию
            $form_type = 'form'; // По умолчанию
            if (class_exists('CodeweberFormsCore')) {
                $form_type = CodeweberFormsCore::get_form_type($post_id);
            } else {
                // Fallback: из метаполя
                $form_type = get_post_meta($post_id, '_form_type', true) ?: 'form';
            }
            
            // Маппинг типов на читаемые названия
            $type_labels = [
                'form' => __('Regular Form', 'codeweber'),
                'newsletter' => __('Newsletter Subscription', 'codeweber'),
                'testimonial' => __('Testimonial Form', 'codeweber'),
                'resume' => __('Resume Form', 'codeweber'),
                'callback' => __('Callback Request', 'codeweber'),
            ];
            
            $type_label = $type_labels[$form_type] ?? $form_type;
            $type_badge_color = [
                'form' => '#2271b1',
                'newsletter' => '#00a32a',
                'testimonial' => '#d63638',
                'resume' => '#d54e21',
                'callback' => '#826eb4',
            ];
            
            $badge_color = $type_badge_color[$form_type] ?? '#666';
            ?>
            <span style="display: inline-block; padding: 2px 8px; border-radius: 3px; background: <?php echo esc_attr($badge_color); ?>; color: #fff; font-size: 11px; font-weight: 500;">
                <?php echo esc_html($type_label); ?>
            </span>
            <?php
        }
        
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
     * Save form type from block attributes
     * Работает как для обычного сохранения, так и для REST API
     */
    public function save_form_type_from_block($post, $request = null, $creating = null) {
        // Проверяем, что это наш CPT
        if (is_object($post)) {
            $post_id = $post->ID;
            $post_type = $post->post_type;
            $post_content = $post->post_content;
        } else {
            $post_id = $post;
            $post_obj = get_post($post_id);
            if (!$post_obj || $post_obj->post_type !== 'codeweber_form') {
                return;
            }
            $post_type = $post_obj->post_type;
            $post_content = $post_obj->post_content;
        }
        
        if ($post_type !== 'codeweber_form') {
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
        
        // Извлекаем тип формы из блока
        $form_type = $this->extract_form_type_from_content($post_content);
        
        if ($form_type) {
            update_post_meta($post_id, '_form_type', $form_type);
        } else {
            // Если атрибут formType не сериализуется (например, выбрана "Обычная форма")
            // — принудительно сохраняем тип 'form', чтобы сбросить прошлое значение.
            update_post_meta($post_id, '_form_type', 'form');
        }
    }
    
    /**
     * Извлечь тип формы из Gutenberg блоков в post_content
     * 
     * @param string $content Post content с Gutenberg блоками
     * @return string|null Тип формы или null
     */
    private function extract_form_type_from_content($content) {
        if (empty($content) || !has_blocks($content)) {
            return null;
        }
        
        $blocks = parse_blocks($content);
        
        // Ищем блок формы
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'codeweber-blocks/form') {
                // Извлекаем formType из атрибутов блока
                if (!empty($block['attrs']['formType'])) {
                    $form_type = sanitize_text_field($block['attrs']['formType']);
                    // Валидация: разрешенные типы
                    $allowed_types = ['form', 'newsletter', 'testimonial', 'resume', 'callback'];
                    if (in_array($form_type, $allowed_types, true)) {
                        return $form_type;
                    }
                }
            }
        }
        
        return null;
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
    
    /**
     * Запустить миграцию CPT форм, если нужно
     * 
     * ВАЖНО: Эта миграция НЕ затрагивает legacy встроенные формы.
     * Legacy формы (testimonial, newsletter, resume, callback) продолжают
     * работать через строковые ID и не требуют миграции.
     * 
     * Миграция затрагивает ТОЛЬКО CPT формы типа 'codeweber_form'.
     */
    public function maybe_run_cpt_migration() {
        // Проверяем, была ли уже выполнена миграция
        $migration_done = get_option('codeweber_forms_cpt_migration_done', false);
        
        if ($migration_done) {
            return; // Уже выполнено
        }
        
        // Запускаем миграцию только для администраторов
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Запускаем миграцию в фоне (один раз)
        // Миграция затрагивает ТОЛЬКО CPT формы типа 'codeweber_form'
        // Legacy формы (строковые ID) не мигрируются и продолжают работать
        if (class_exists('CodeweberFormsCPTMigration')) {
            $results = CodeweberFormsCPTMigration::migrate_all_forms();
            
            // Помечаем миграцию как выполненную
            update_option('codeweber_forms_cpt_migration_done', true);
            update_option('codeweber_forms_cpt_migration_results', $results);
            
            // Логируем
            error_log('Codeweber Forms: CPT migration completed automatically');
            error_log('Codeweber Forms: Legacy forms (testimonial, newsletter, etc.) are NOT migrated and continue to work as before');
        }
    }
    
    /**
     * Добавить фильтр по типу формы на странице списка CPT форм
     * 
     * @param string $post_type Тип поста
     * @param string $which Расположение ('top' или 'bottom')
     */
    public function add_form_type_filter($post_type, $which = 'top') {
        // Только для нашего CPT и только сверху
        if ($post_type !== 'codeweber_form' || $which !== 'top') {
            return;
        }
        
        // Получаем выбранный тип формы
        $selected_type = isset($_GET['form_type_filter']) ? sanitize_text_field($_GET['form_type_filter']) : '';
        
        // Маппинг типов на читаемые названия с переводами
        $type_labels = array(
            '' => __('Все типы форм', 'codeweber'),
            'form' => __('Обычная форма', 'codeweber'),
            'newsletter' => __('Подписка на рассылку', 'codeweber'),
            'testimonial' => __('Форма отзыва', 'codeweber'),
            'resume' => __('Форма резюме', 'codeweber'),
            'callback' => __('Запрос обратного звонка', 'codeweber'),
        );
        
        ?>
        <select name="form_type_filter" id="form-type-filter">
            <?php foreach ($type_labels as $type_key => $type_label): ?>
                <option value="<?php echo esc_attr($type_key); ?>" <?php selected($selected_type, $type_key); ?>>
                    <?php echo esc_html($type_label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
    
    /**
     * Фильтровать посты по типу формы
     * 
     * @param WP_Query $query Объект запроса
     */
    public function filter_posts_by_form_type($query) {
        global $pagenow, $typenow;
        
        // Только на странице списка постов и для нашего CPT
        if ($pagenow !== 'edit.php' || $typenow !== 'codeweber_form') {
            return;
        }
        
        // Проверяем, есть ли фильтр по типу формы
        if (!isset($_GET['form_type_filter']) || $_GET['form_type_filter'] === '') {
            return;
        }
        
        $form_type = sanitize_text_field($_GET['form_type_filter']);
        
        // Добавляем мета-запрос для фильтрации по типу формы
        $meta_query = $query->get('meta_query');
        if (!is_array($meta_query)) {
            $meta_query = array();
        }
        
        $meta_query[] = array(
            'key' => '_form_type',
            'value' => $form_type,
            'compare' => '='
        );
        
        $query->set('meta_query', $meta_query);
    }
}



