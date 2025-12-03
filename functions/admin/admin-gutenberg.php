<?php

// Растягиваем редактор Gutenberg на всю ширину
add_action('admin_head', 'full_width_gutenberg_editor');
function full_width_gutenberg_editor() {
    // Проверяем, что мы в редакторе блоков
    if (get_current_screen()->base !== 'post') return;
    ?>
    <style type="text/css">
        /* Растягиваем основной контейнер редактора */
        .interface-interface-skeleton__content {
            width: 100% !important;
            max-width: none !important;
        }

        /* Убираем ограничение у блоков */
        .block-editor-block-list__layout .wp-block {
            max-width: none !important;
            width: 100% !important;
        }

        /* Добавляем отступы по бокам, чтобы не прилипало */
        .block-editor-block-list__layout {
            padding-left: 20px !important;
            padding-right: 20px !important;
        }

        /* Опционально: убрать отступы между блоками */
        .block-editor-block-list__layout .wp-block {
            margin-top: 0 !important;
            margin-bottom: 20px !important;
        }
    </style>
    <?php
}