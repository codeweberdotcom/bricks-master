<?php
/**
 * Universal Modal Container
 * 
 * Outputs a single universal Bootstrap modal container that is used
 * to dynamically load and display modal windows via REST API.
 * Works with the Button block and restapi.js
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Output universal modal container in footer
 */
function codeweber_universal_modal_container()
{
    $card_radius = getThemeCardImageRadius();
    
    // Проверяем активное уведомление
    $active_notification = codeweber_get_active_notification_modal();
    $modal_popup_class = $active_notification ? ' modal-popup' : '';
    $data_wait = $active_notification ? ' data-wait="' . esc_attr($active_notification['wait_delay']) . '"' : '';
    $position_class = $active_notification && isset($active_notification['position']) ? ' ' . esc_attr($active_notification['position']) : '';
    
    // Trigger data attributes
    $data_trigger_type = $active_notification && isset($active_notification['trigger_type']) ? ' data-trigger-type="' . esc_attr($active_notification['trigger_type']) . '"' : '';
    $data_trigger_inactivity = $active_notification && isset($active_notification['trigger_inactivity_delay']) ? ' data-trigger-inactivity="' . esc_attr($active_notification['trigger_inactivity_delay']) . '"' : '';
    $data_trigger_viewport = $active_notification && isset($active_notification['trigger_viewport_id']) && !empty($active_notification['trigger_viewport_id']) ? ' data-trigger-viewport="' . esc_attr($active_notification['trigger_viewport_id']) . '"' : '';
    
    // Получаем размер modal
    $modal_size_class = '';
    if ($active_notification && isset($active_notification['size']) && !empty($active_notification['size'])) {
        $modal_size_class = ' ' . esc_attr($active_notification['size']);
    }
    
    // Определяем, нужно ли центрирование (для угловых позиций убираем центрирование)
    $dialog_centered_class = '';
    if ($active_notification && isset($active_notification['position'])) {
        $corner_positions = array('modal-bottom-start', 'modal-bottom-end', 'modal-top-start', 'modal-top-end');
        if (!in_array($active_notification['position'], $corner_positions)) {
            $dialog_centered_class = ' modal-dialog-centered';
        }
    } else {
        $dialog_centered_class = ' modal-dialog-centered';
    }
    
    // Отладка (можно удалить после проверки)
    if (current_user_can('manage_options') && isset($_GET['debug_notifications'])) {
        $current_time = current_time('timestamp');
        $notifications = get_posts(array(
            'post_type' => 'notifications',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        echo '<!-- Debug Notifications START -->';
        echo '<!-- Current time: ' . date('Y-m-d H:i:s', $current_time) . ' (' . $current_time . ') -->';
        echo '<!-- Found notifications: ' . count($notifications) . ' -->';
        
        foreach ($notifications as $notif) {
            $modal_id = get_post_meta($notif->ID, '_notification_modal_id', true);
            $start_date = get_post_meta($notif->ID, '_notification_start_date', true);
            $end_date = get_post_meta($notif->ID, '_notification_end_date', true);
            
            echo '<!-- Notification #' . $notif->ID . ': modal_id=' . $modal_id . ', start=' . $start_date . ', end=' . $end_date . ' -->';
        }
        
        echo '<!-- active_notification = ' . ($active_notification ? 'FOUND (modal_id: ' . $active_notification['modal_id'] . ')' : 'NOT FOUND') . ' -->';
        echo '<!-- Debug Notifications END -->';
    }
    
    ?>
    <!-- Universal Modal Container -->
    <?php if ($active_notification): ?>
        <!-- Notification Modal (separate from universal modal) -->
        <div class="modal fade<?php echo esc_attr($modal_popup_class . $position_class); ?>" id="notification-modal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true"<?php echo $data_wait . $data_trigger_type . $data_trigger_inactivity . $data_trigger_viewport; ?>>
            <div class="modal-dialog<?php echo esc_attr($dialog_centered_class . $modal_size_class); ?>">
                <div class="modal-content<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                    <!-- Notification modal content (without modal-body) -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'codeweber'); ?>"></button>
                    <div id="notification-modal-content">
                        <?php echo apply_filters('the_content', $active_notification['modal_content']); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Universal Modal Container (for REST API, CF7, etc.) -->
    <div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <!-- Content will be loaded dynamically via REST API -->
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'codeweber'); ?>"></button>
                    <div id="modal-content">
                        <div class="modal-loader"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
// Убрали хук wp_footer - теперь функция вызывается напрямую в footer.php перед wp_footer()

