<?php
/**
 * Test Script for Personal Data V2 Providers
 * 
 * Этот файл можно использовать для тестирования регистрации и работы провайдеров
 * 
 * Использование:
 * 1. Добавьте в functions.php временно: require_once get_template_directory() . '/functions/integrations/personal-data-v2/test-providers.php';
 * 2. Откройте страницу в браузере: /wp-admin/admin.php?page=personal-data-test
 * 3. Удалите строку из functions.php после тестирования
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

// Добавляем страницу в админке для тестирования
add_action('admin_menu', function() {
    add_submenu_page(
        'tools.php',
        __('Personal Data Providers Test', 'codeweber'),
        __('PD Providers Test', 'codeweber'),
        'manage_options',
        'personal-data-test',
        'personal_data_v2_test_page'
    );
});

/**
 * Страница тестирования провайдеров
 */
function personal_data_v2_test_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'codeweber'));
    }
    
    $manager = Personal_Data_Manager::get_instance();
    $providers = $manager->get_providers();
    
    ?>
    <div class="wrap">
        <h1><?php _e('Personal Data V2 Providers Test', 'codeweber'); ?></h1>
        
        <div class="card" style="max-width: 1200px; margin-top: 20px;">
            <h2><?php _e('Registered Providers', 'codeweber'); ?></h2>
            
            <?php if (empty($providers)): ?>
                <p style="color: red;"><?php _e('No providers registered!', 'codeweber'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Provider ID', 'codeweber'); ?></th>
                            <th><?php _e('Provider Name', 'codeweber'); ?></th>
                            <th><?php _e('Description', 'codeweber'); ?></th>
                            <th><?php _e('Status', 'codeweber'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($providers as $provider): ?>
                            <tr>
                                <td><code><?php echo esc_html($provider->get_provider_id()); ?></code></td>
                                <td><strong><?php echo esc_html($provider->get_provider_name()); ?></strong></td>
                                <td><?php echo esc_html($provider->get_provider_description()); ?></td>
                                <td>
                                    <span style="color: green;">✓ <?php _e('Registered', 'codeweber'); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="card" style="max-width: 1200px; margin-top: 20px;">
            <h2><?php _e('WordPress Privacy Tools Integration', 'codeweber'); ?></h2>
            
            <?php
            // Проверяем регистрацию экспортеров
            $exporters = apply_filters('wp_privacy_personal_data_exporters', []);
            $our_exporters = [];
            foreach ($exporters as $exporter_id => $exporter) {
                if (in_array($exporter_id, $manager->get_provider_ids())) {
                    $our_exporters[$exporter_id] = $exporter;
                }
            }
            ?>
            
            <h3><?php _e('Registered Exporters', 'codeweber'); ?></h3>
            <?php if (empty($our_exporters)): ?>
                <p style="color: orange;"><?php _e('No exporters registered in WordPress Privacy Tools!', 'codeweber'); ?></p>
            <?php else: ?>
                <ul>
                    <?php foreach ($our_exporters as $exporter_id => $exporter): ?>
                        <li>
                            <strong><?php echo esc_html($exporter['exporter_friendly_name'] ?? $exporter_id); ?></strong>
                            <code>(<?php echo esc_html($exporter_id); ?>)</code>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <?php
            // Проверяем регистрацию эрасеров
            $erasers = apply_filters('wp_privacy_personal_data_erasers', []);
            $our_erasers = [];
            foreach ($erasers as $eraser_id => $eraser) {
                if (in_array($eraser_id, $manager->get_provider_ids())) {
                    $our_erasers[$eraser_id] = $eraser;
                }
            }
            ?>
            
            <h3><?php _e('Registered Erasers', 'codeweber'); ?></h3>
            <?php if (empty($our_erasers)): ?>
                <p style="color: orange;"><?php _e('No erasers registered in WordPress Privacy Tools!', 'codeweber'); ?></p>
            <?php else: ?>
                <ul>
                    <?php foreach ($our_erasers as $eraser_id => $eraser): ?>
                        <li>
                            <strong><?php echo esc_html($eraser['eraser_friendly_name'] ?? $eraser_id); ?></strong>
                            <code>(<?php echo esc_html($eraser_id); ?>)</code>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <div class="card" style="max-width: 1200px; margin-top: 20px;">
            <h2><?php _e('Test Data Check', 'codeweber'); ?></h2>
            <p><?php _e('Enter an email address to check if providers have data for it:', 'codeweber'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('personal_data_test', 'personal_data_test_nonce'); ?>
                <p>
                    <label>
                        <strong><?php _e('Email Address:', 'codeweber'); ?></strong><br>
                        <input type="email" name="test_email" value="<?php echo esc_attr($_POST['test_email'] ?? ''); ?>" style="width: 300px;">
                    </label>
                </p>
                <p>
                    <button type="submit" class="button button-primary"><?php _e('Check Data', 'codeweber'); ?></button>
                </p>
            </form>
            
            <?php
            if (isset($_POST['test_email']) && wp_verify_nonce($_POST['personal_data_test_nonce'], 'personal_data_test')) {
                $test_email = sanitize_email($_POST['test_email']);
                
                if (is_email($test_email)) {
                    echo '<h3>' . __('Results for:', 'codeweber') . ' ' . esc_html($test_email) . '</h3>';
                    echo '<table class="wp-list-table widefat fixed striped">';
                    echo '<thead><tr><th>' . __('Provider', 'codeweber') . '</th><th>' . __('Has Data', 'codeweber') . '</th><th>' . __('Data Preview', 'codeweber') . '</th></tr></thead>';
                    echo '<tbody>';
                    
                    foreach ($providers as $provider) {
                        $has_data = $provider->has_personal_data($test_email);
                        echo '<tr>';
                        echo '<td><strong>' . esc_html($provider->get_provider_name()) . '</strong></td>';
                        echo '<td>' . ($has_data ? '<span style="color: green;">✓ ' . __('Yes', 'codeweber') . '</span>' : '<span style="color: gray;">✗ ' . __('No', 'codeweber') . '</span>') . '</td>';
                        
                        // Показываем превью данных, если они есть
                        if ($has_data) {
                            $data = $provider->get_personal_data($test_email);
                            $items_count = count($data['data'] ?? []);
                            echo '<td>';
                            if ($items_count > 0) {
                                echo '<span style="color: green;">' . sprintf(__('%d item(s) found', 'codeweber'), $items_count) . '</span>';
                                echo '<br><small>' . __('Use WordPress Privacy Tools to export full data', 'codeweber') . '</small>';
                            } else {
                                echo '<span style="color: orange;">' . __('Data structure found but empty', 'codeweber') . '</span>';
                            }
                            echo '</td>';
                        } else {
                            echo '<td><span style="color: #999;">—</span></td>';
                        }
                        
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                    
                    // Показываем ссылки на WordPress Privacy Tools
                    $has_any_data = false;
                    foreach ($providers as $provider) {
                        if ($provider->has_personal_data($test_email)) {
                            $has_any_data = true;
                            break;
                        }
                    }
                    
                    if ($has_any_data) {
                        echo '<div style="margin-top: 20px; padding: 15px; background: #f0f6fc; border-left: 4px solid #2271b1;">';
                        echo '<h4>' . __('Next Steps:', 'codeweber') . '</h4>';
                        echo '<ol>';
                        echo '<li><a href="' . admin_url('export-personal-data.php') . '" target="_blank">' . __('Test Export', 'codeweber') . '</a> - ' . __('Export personal data via WordPress Privacy Tools', 'codeweber') . '</li>';
                        echo '<li><a href="' . admin_url('erase-personal-data.php') . '" target="_blank">' . __('Test Erasure', 'codeweber') . '</a> - ' . __('Test data anonymization (use test email only!)', 'codeweber') . '</li>';
                        echo '</ol>';
                        echo '</div>';
                    }
                } else {
                    echo '<p style="color: red;">' . __('Invalid email address!', 'codeweber') . '</p>';
                }
            }
            ?>
        </div>
        
        <div class="card" style="max-width: 1200px; margin-top: 20px;">
            <h2><?php _e('How to Test', 'codeweber'); ?></h2>
            <ol>
                <li><?php _e('Check that all providers are registered above', 'codeweber'); ?></li>
                <li><?php _e('Go to Tools → Export Personal Data in WordPress admin', 'codeweber'); ?></li>
                <li><?php _e('Enter an email address that has data in one of the modules', 'codeweber'); ?></li>
                <li><?php _e('Check that the export includes data from registered providers', 'codeweber'); ?></li>
                <li><?php _e('Test deletion via Tools → Erase Personal Data', 'codeweber'); ?></li>
            </ol>
        </div>
    </div>
    <?php
}

