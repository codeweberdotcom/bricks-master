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
    ?>
    <!-- Universal Modal Container -->
    <div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content<?php echo $card_radius ? ' ' . esc_attr($card_radius) : ''; ?>">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'codeweber'); ?>"></button>
                    <div id="modal-content">
                        <!-- Content will be loaded dynamically via REST API -->
                        <div class="modal-loader"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'codeweber_universal_modal_container', 100);

