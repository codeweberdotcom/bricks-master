<?php

/**
 * Добавляем поле чекбокса "Mega Menu" к пунктам меню
 */
add_action('wp_nav_menu_item_custom_fields', 'add_mega_menu_checkbox_to_menu_item', 10, 4);
function add_mega_menu_checkbox_to_menu_item($item_id, $item, $depth, $args)
{
   $is_mega_menu = get_post_meta($item_id, '_is_mega_menu', true);
?>
   <div class="field-mega-menu description-wide" style="margin: 5px 0;">
      <label for="edit-menu-item-mega-menu-<?php echo $item_id; ?>">
         <input type="checkbox" id="edit-menu-item-mega-menu-<?php echo $item_id; ?>"
            name="menu-item-mega-menu[<?php echo $item_id; ?>]"
            value="1" <?php checked($is_mega_menu, 1); ?> />
         <?php _e('Mega Menu', 'textdomain'); ?>
      </label>
   </div>
<?php
}

/**
 * Сохраняем значение чекбокса при сохранении меню
 */
add_action('wp_update_nav_menu_item', 'save_mega_menu_checkbox', 10, 3);
function save_mega_menu_checkbox($menu_id, $menu_item_db_id, $args)
{
   if (isset($_POST['menu-item-mega-menu'][$menu_item_db_id])) {
      update_post_meta($menu_item_db_id, '_is_mega_menu', 1);
   } else {
      delete_post_meta($menu_item_db_id, '_is_mega_menu');
   }
}

/**
 * Добавляем кастомное поле к объекту меню для фронтенда
 */
add_filter('wp_setup_nav_menu_item', 'add_mega_menu_data_to_menu_item');
function add_mega_menu_data_to_menu_item($menu_item)
{
   $menu_item->is_mega_menu = get_post_meta($menu_item->ID, '_is_mega_menu', true);
   return $menu_item;
}

/**
 * Добавляем CSS для стилизации чекбокса в админке
 */
add_action('admin_head', 'add_mega_menu_admin_css');
function add_mega_menu_admin_css()
{
?>
   <style>
      .field-mega-menu {
         clear: both;
         padding: 8px 0;
      }

      .field-mega-menu label {
         display: inline-block;
         font-weight: 600;
      }
   </style>
<?php
}
