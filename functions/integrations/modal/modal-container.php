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
    $card_radius = Codeweber_Options::style('card-radius');
    
    // Проверяем активное уведомление
    $active_notification = codeweber_get_active_notification_modal();
    $notif_type = $active_notification ? ($active_notification['notification_type'] ?? 'modal') : '';

    // Общие data-атрибуты (триггеры — одинаковы для обоих типов)
    $data_wait             = $active_notification ? ' data-wait="' . esc_attr($active_notification['wait_delay']) . '"' : '';
    $data_trigger_type     = $active_notification ? ' data-trigger-type="' . esc_attr($active_notification['trigger_type']) . '"' : '';
    $data_trigger_inactivity = $active_notification ? ' data-trigger-inactivity="' . esc_attr($active_notification['trigger_inactivity_delay']) . '"' : '';
    $data_trigger_viewport = ($active_notification && !empty($active_notification['trigger_viewport_id'])) ? ' data-trigger-viewport="' . esc_attr($active_notification['trigger_viewport_id']) . '"' : '';
    $data_trigger_utm_param = ($active_notification && !empty($active_notification['trigger_utm_param'])) ? ' data-trigger-utm-param="' . esc_attr($active_notification['trigger_utm_param']) . '"' : '';
    $data_trigger_utm_value = ($active_notification && !empty($active_notification['trigger_utm_value'])) ? ' data-trigger-utm-value="' . esc_attr($active_notification['trigger_utm_value']) . '"' : '';

    // Переменные только для типа modal
    $modal_popup_class    = ($notif_type === 'modal') ? ' modal-popup' : '';
    $position_class       = ($notif_type === 'modal' && isset($active_notification['position'])) ? ' ' . esc_attr($active_notification['position']) : '';
    $modal_size_class     = ($notif_type === 'modal' && !empty($active_notification['size'])) ? ' ' . esc_attr($active_notification['size']) : '';

    $dialog_centered_class = ' modal-dialog-centered';
    if ($notif_type === 'modal' && isset($active_notification['position'])) {
        $corner_positions = array('modal-bottom-start', 'modal-bottom-end', 'modal-top-start', 'modal-top-end');
        if (in_array($active_notification['position'], $corner_positions)) {
            $dialog_centered_class = '';
        }
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
        
        $debug_notif_info = $active_notification ? 'FOUND (type: ' . ($active_notification['notification_type'] ?? 'modal') . ', id: ' . $active_notification['notification_id'] . ')' : 'NOT FOUND';
        echo '<!-- active_notification = ' . esc_html($debug_notif_info) . ' -->';
        echo '<!-- Debug Notifications END -->';
    }
    
    ?>
    <!-- Universal Modal Container -->
    <?php if ($active_notification && $notif_type === 'modal'): ?>
        <!-- Notification Modal -->
        <div class="modal fade<?php echo esc_attr($modal_popup_class . $position_class); ?>" id="notification-modal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true"<?php echo $data_wait . $data_trigger_type . $data_trigger_inactivity . $data_trigger_viewport . $data_trigger_utm_param . $data_trigger_utm_value; ?>>
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
    <?php elseif ($active_notification && $notif_type === 'cw_notify'): ?>
        <!-- CW Notify Toast Notification (hidden data carrier) -->
        <div id="notification-modal" class="d-none"
            data-notification-type="cw_notify"
            data-cw-message="<?php echo esc_attr($active_notification['cw_message']); ?>"
            data-cw-type="<?php echo esc_attr($active_notification['cw_type']); ?>"
            data-cw-position="<?php echo esc_attr($active_notification['cw_position']); ?>"
            data-cw-delay="<?php echo esc_attr($active_notification['cw_delay']); ?>"
            <?php echo $data_wait . $data_trigger_type . $data_trigger_inactivity . $data_trigger_viewport . $data_trigger_utm_param . $data_trigger_utm_value; ?>></div>
    <?php elseif ($active_notification && $notif_type === 'telegram'): ?>
        <!-- Telegram Notification (hidden data carrier) -->
        <div id="notification-modal" class="d-none"
            data-notification-type="telegram"
            data-notification-id="<?php echo esc_attr($active_notification['notification_id']); ?>"
            data-nonce="<?php echo esc_attr(wp_create_nonce('codeweber_notification_telegram')); ?>"
            <?php echo $data_wait . $data_trigger_type . $data_trigger_inactivity . $data_trigger_viewport . $data_trigger_utm_param . $data_trigger_utm_value; ?>></div>
    <?php endif; ?>
    
    <!-- Universal REST API modal — статично в DOM, Bootstrap не крашит делегированный обработчик.
         restapi.js переиспользует этот элемент: наполняет контентом и уничтожает Bootstrap-инстанс после закрытия. -->
    <div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'codeweber'); ?>"></button>
                    <div id="modal-content">
                        <div class="p-2">
                            <div class="cw-skeleton-block mb-3" style="height:1.4em;width:65%"></div>
                            <div class="cw-skeleton-block mb-2" style="height:.8em;width:100%"></div>
                            <div class="cw-skeleton-block mb-2" style="height:.8em;width:92%"></div>
                            <div class="cw-skeleton-block mb-2" style="height:.8em;width:85%"></div>
                            <div class="cw-skeleton-block mb-4" style="height:.8em;width:68%"></div>
                            <div class="cw-skeleton-block mb-2" style="height:.8em;width:100%"></div>
                            <div class="cw-skeleton-block mb-2" style="height:.8em;width:88%"></div>
                            <div class="cw-skeleton-block" style="height:.8em;width:55%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Config for dynamically created universal modal (created by restapi.js on demand) -->
    <meta id="cw-modal-config"
          data-card-radius="<?php echo esc_attr($card_radius); ?>"
          data-close-label="<?php echo esc_attr__('Close', 'codeweber'); ?>">
    <?php
}
// Убрали хук wp_footer - теперь функция вызывается напрямую в footer.php перед wp_footer()

