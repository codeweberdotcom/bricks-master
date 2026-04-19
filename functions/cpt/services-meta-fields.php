<?php
/**
 * Services CPT — Short Description meta field + meta-box.
 *
 * Регистрирует дополнительное мета-поле `_service_short_description`
 * для CPT `services` и отдельный метабокс внизу страницы редактирования.
 * Значение сохраняется при сохранении записи, доступно через REST API
 * (потребляется Post Grid блоком / шаблонами карточек услуг).
 *
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

const CODEWEBER_SERVICES_SHORT_DESC_KEY = '_service_short_description';
const CODEWEBER_SERVICES_SHORT_DESC_NONCE = 'codeweber_services_short_desc_nonce';

/**
 * Регистрируем мета-поле. `show_in_rest: true` делает значение доступным
 * в REST-ответе (для editor preview в Post Grid блоке и внешних интеграций).
 */
add_action('init', function () {
    register_post_meta('services', CODEWEBER_SERVICES_SHORT_DESC_KEY, [
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'auth_callback'     => function () {
            return current_user_can('edit_posts');
        },
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
});

/**
 * Регистрируем метабокс. context=normal + priority=low — прибивает метабокс
 * под основной редактор, в нижней части страницы.
 */
add_action('add_meta_boxes_services', function () {
    add_meta_box(
        'services_short_description',
        __('Short Description', 'codeweber'),
        'codeweber_services_short_desc_metabox_cb',
        'services',
        'normal',
        'low'
    );
});

/**
 * Рендер метабокса.
 *
 * @param WP_Post $post Текущий пост services.
 */
function codeweber_services_short_desc_metabox_cb($post) {
    $value = get_post_meta($post->ID, CODEWEBER_SERVICES_SHORT_DESC_KEY, true);
    wp_nonce_field(CODEWEBER_SERVICES_SHORT_DESC_NONCE, CODEWEBER_SERVICES_SHORT_DESC_NONCE);
    ?>
    <p class="description">
        <?php esc_html_e('Short description for service cards. Falls back to the standard excerpt if left empty.', 'codeweber'); ?>
    </p>
    <textarea
        name="<?php echo esc_attr(CODEWEBER_SERVICES_SHORT_DESC_KEY); ?>"
        id="<?php echo esc_attr(CODEWEBER_SERVICES_SHORT_DESC_KEY); ?>"
        class="widefat"
        rows="4"
    ><?php echo esc_textarea($value); ?></textarea>
    <?php
}

/**
 * Сохранение значения. Проверяем: nonce, пермишны, не автосейв, не revision.
 *
 * @param int $post_id
 */
add_action('save_post_services', function ($post_id) {
    if (!isset($_POST[CODEWEBER_SERVICES_SHORT_DESC_NONCE])) {
        return;
    }
    if (!wp_verify_nonce($_POST[CODEWEBER_SERVICES_SHORT_DESC_NONCE], CODEWEBER_SERVICES_SHORT_DESC_NONCE)) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST[CODEWEBER_SERVICES_SHORT_DESC_KEY])) {
        $value = sanitize_textarea_field(wp_unslash($_POST[CODEWEBER_SERVICES_SHORT_DESC_KEY]));
        update_post_meta($post_id, CODEWEBER_SERVICES_SHORT_DESC_KEY, $value);
    } else {
        delete_post_meta($post_id, CODEWEBER_SERVICES_SHORT_DESC_KEY);
    }
});
