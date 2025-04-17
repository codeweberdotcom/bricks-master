<?php

// Добавляем пользовательское поле (чекбокс) в пункты меню
add_action('wp_nav_menu_item_custom_fields', 'add_custom_menu_checkbox', 10, 4);

function add_custom_menu_checkbox($item_id, $item, $depth, $args)
{
   // Получаем сохранённое значение
   $value = get_post_meta($item_id, '_custom_menu_checkbox', true);
?>
   <p class="description">
      <label for="edit-menu-item-checkbox-<?php echo $item_id; ?>">
         <input type="checkbox" id="edit-menu-item-checkbox-<?php echo $item_id; ?>" name="menu-item-checkbox[<?php echo $item_id; ?>]" value="1" <?php checked($value, 1); ?> />
         <?php _e("Enable Smooth Scroll", "codeweber");
         ?>
      </label>
   </p>
<?php
}

// Сохраняем значение пользовательского поля
add_action('wp_update_nav_menu_item', 'save_custom_menu_checkbox', 10, 2);

function save_custom_menu_checkbox($menu_id, $menu_item_db_id)
{
   if (isset($_POST['menu-item-checkbox'][$menu_item_db_id])) {
      update_post_meta($menu_item_db_id, '_custom_menu_checkbox', 1);
   } else {
      delete_post_meta($menu_item_db_id, '_custom_menu_checkbox');
   }
}


add_filter('wp_nav_menu_objects', 'add_checkbox_meta_to_menu_items', 10, 2);

function add_checkbox_meta_to_menu_items($items, $args)
{
   foreach ($items as $item) {
      // Получаем значение мета-поля, если оно существует
      $item->custom_checkbox = get_post_meta($item->ID, '_custom_menu_checkbox', true) ?: false;
   }
   return $items;
}