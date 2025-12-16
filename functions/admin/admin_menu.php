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




// Добавляем чекбокс Mega Menu в пункты меню
add_action('wp_nav_menu_item_custom_fields', 'add_mega_menu_checkbox', 10, 4);

function add_mega_menu_checkbox($item_id, $item, $depth, $args)
{
   // Получаем сохранённое значение
   $value = get_post_meta($item_id, '_mega_menu', true);
?>
   <p class="description">
      <label for="edit-menu-item-mega-menu-<?php echo $item_id; ?>">
         <input type="checkbox" id="edit-menu-item-mega-menu-<?php echo $item_id; ?>" name="menu-item-mega-menu[<?php echo $item_id; ?>]" value="1" <?php checked($value, 1); ?> />
         <?php _e("Mega Menu", "textdomain"); ?>
      </label>
   </p>
<?php
}

// Сохраняем значение чекбокса Mega Menu
add_action('wp_update_nav_menu_item', 'save_mega_menu_checkbox', 10, 2);

function save_mega_menu_checkbox($menu_id, $menu_item_db_id)
{
   if (isset($_POST['menu-item-mega-menu'][$menu_item_db_id])) {
      update_post_meta($menu_item_db_id, '_mega_menu', 1);
   } else {
      delete_post_meta($menu_item_db_id, '_mega_menu');
   }
}

// Добавляем мета-данные к объектам меню для использования в шаблоне
add_filter('wp_nav_menu_objects', 'add_mega_menu_meta_to_menu_items', 10, 2);

function add_mega_menu_meta_to_menu_items($items, $args)
{
   foreach ($items as $item) {
      // Получаем значение мета-поля Mega Menu
      $item->is_mega_menu = get_post_meta($item->ID, '_mega_menu', true) ?: false;
   }
   return $items;
}


// ============================================
// РАЗДЕЛИТЕЛИ В АДМИН-МЕНЮ WORDPRESS
// ============================================

/**
 * Находит позицию пункта меню по его slug
 * 
 * @param string $menu_slug Slug пункта меню (например, 'newsletter-subscriptions')
 * @return int|string|false Позиция пункта меню или false, если не найден
 */
function find_menu_position_by_slug($menu_slug)
{
   global $menu;
   
   if (empty($menu)) {
      return false;
   }
   
   foreach ($menu as $position => $menu_item) {
      // Проверяем как основной slug, так и plugin_basename версию
      $item_slug = !empty($menu_item[2]) ? $menu_item[2] : '';
      if ($item_slug === $menu_slug || plugin_basename($item_slug) === $menu_slug) {
         // Возвращаем позицию как есть (может быть int или string)
         return $position;
      }
   }
   
   return false;
}

/**
 * Добавляет разделитель перед указанным пунктом меню
 * 
 * @param string $menu_slug Slug пункта меню, перед которым нужно добавить разделитель
 * @param string $separator_id Уникальный ID разделителя
 * @return bool true если разделитель добавлен, false если пункт меню не найден
 */
function add_admin_menu_separator_before($menu_slug, $separator_id = '')
{
   global $menu;
   
   $position = find_menu_position_by_slug($menu_slug);
   
   if ($position === false) {
      return false;
   }
   
   // Если ID не указан, генерируем автоматически
   if (empty($separator_id)) {
      $separator_id = 'separator-before-' . sanitize_key($menu_slug);
   }
   
   // Преобразуем позицию в число для вычислений
   // Позиция может быть int, float или string
   if (is_numeric($position)) {
      $pos_num = (float) $position;
   } else {
      // Если позиция - строка, пытаемся извлечь число
      $pos_num = (float) $position;
   }
   
   // Используем строковый ключ с десятичным значением для вставки между позициями
   // Например, если пункт на позиции 30, используем '29.5'
   // Важно: используем строку, чтобы strnatcasecmp правильно отсортировал
   $separator_position = (string) ($pos_num - 0.5);
   
   // Добавляем разделитель в массив меню
   // Формат: array( '', 'read', 'separator-id', '', 'wp-menu-separator' )
   // Важно: slug должен начинаться с 'separator' для правильной обработки WordPress
   $menu[$separator_position] = array(
      '',                    // Название (пустое для разделителя)
      'read',                // Минимальные права доступа
      $separator_id,         // Уникальный ID разделителя (должен начинаться с 'separator')
      '',                    // Заголовок страницы (пустое)
      'wp-menu-separator'   // CSS класс для разделителя
   );
   
   return true;
}

/**
 * Добавляет разделитель после указанного пункта меню
 * 
 * @param string $menu_slug Slug пункта меню, после которого нужно добавить разделитель
 * @param string $separator_id Уникальный ID разделителя
 * @return bool true если разделитель добавлен, false если пункт меню не найден
 */
function add_admin_menu_separator_after($menu_slug, $separator_id = '')
{
   global $menu;
   
   $position = find_menu_position_by_slug($menu_slug);
   
   if ($position === false) {
      return false;
   }
   
   // Если ID не указан, генерируем автоматически
   if (empty($separator_id)) {
      $separator_id = 'separator-after-' . sanitize_key($menu_slug);
   }
   
   // Используем строковый ключ с десятичным значением для вставки между позициями
   // Например, если пункт на позиции 30, используем '30.5'
   $separator_position = (string) ($position + 0.5);
   
   // Добавляем разделитель в массив меню
   $menu[$separator_position] = array(
      '',
      'read',
      $separator_id,
      '',
      'wp-menu-separator'
   );
   
   return true;
}

/**
 * Добавляет разделитель на указанной позиции (используйте для точного контроля)
 * 
 * @param string|int $position Позиция в меню (можно использовать строку типа '29.5')
 * @param string $separator_id Уникальный ID разделителя
 */
function add_admin_menu_separator_at_position($position, $separator_id = '')
{
   global $menu;
   
   // Преобразуем в строку, чтобы избежать проблем с float
   $position = (string) $position;
   
   // Если ID не указан, генерируем автоматически
   if (empty($separator_id)) {
      $separator_id = 'separator-custom-' . sanitize_key($position);
   }
   
   // Добавляем разделитель в массив меню
   $menu[$position] = array(
      '',
      'read',
      $separator_id,
      '',
      'wp-menu-separator'
   );
}

/**
 * Добавляет разделители в админ-меню
 * Используем хук admin_menu с очень высоким приоритетом
 */
add_action('admin_menu', 'add_custom_admin_menu_separators', 99999);

function add_custom_admin_menu_separators()
{
   global $menu;
   
   // Отладка: добавьте ?debug_menu=1 к URL админки для просмотра структуры меню
   // Раскомментируйте следующую строку для включения отладки:
   // if (current_user_can('manage_options') && isset($_GET['debug_menu'])) {
   if (false && current_user_can('manage_options') && isset($_GET['debug_menu'])) {
      echo '<pre style="background: #fff; padding: 20px; margin: 20px; border: 2px solid #000; z-index: 99999; position: relative;">';
      echo "Все пункты меню:\n\n";
      foreach ($menu as $pos => $item) {
         if (!empty($item[2])) {
            echo "Позиция: " . var_export($pos, true) . " (тип: " . gettype($pos) . ")\n";
            echo "  Slug: {$item[2]}\n";
            echo "  Название: {$item[0]}\n";
            echo "  Класс: " . (!empty($item[4]) ? $item[4] : 'нет') . "\n";
            echo "\n";
         }
      }
      echo '</pre>';
      die();
   }
   
   if (empty($menu)) {
      return;
   }
   
   // Ищем пункт меню "Subscriptions" по slug или названию
   $target_position = false;
   foreach ($menu as $pos => $item) {
      $item_slug = !empty($item[2]) ? $item[2] : '';
      $item_title = !empty($item[0]) ? $item[0] : '';
      
      // Пропускаем разделители
      if (!empty($item[4]) && strpos($item[4], 'wp-menu-separator') !== false) {
         continue;
      }
      
      // Проверяем по slug или названию
      if ($item_slug === 'newsletter-subscriptions' || 
          stripos($item_title, 'Subscriptions') !== false || 
          stripos($item_title, 'Подписки') !== false) {
         $target_position = $pos;
         break;
      }
   }
   
   // Если нашли пункт меню, добавляем разделитель перед ним
   if ($target_position !== false) {
      // Преобразуем позицию в число
      $pos_num = is_numeric($target_position) ? (float) $target_position : (float) $target_position;
      
      // Вычисляем позицию для разделителя (перед найденным пунктом)
      $separator_position = $pos_num - 0.5;
      
      // Используем строковый ключ для правильной сортировки через strnatcasecmp
      $separator_key = (string) $separator_position;
      
      // Проверяем, нет ли уже разделителя на этой позиции
      if (!isset($menu[$separator_key]) || 
          (isset($menu[$separator_key][4]) && strpos($menu[$separator_key][4], 'wp-menu-separator') === false)) {
         
         // Добавляем разделитель в правильном формате WordPress
         // Формат точно как в wp-admin/menu.php: array( '', 'read', 'separator-id', '', 'wp-menu-separator' )
         $menu[$separator_key] = array(
            '',                                    // [0] Название (пустое)
            'read',                                // [1] Минимальные права
            'separator-before-subscriptions',      // [2] Slug (должен начинаться с 'separator')
            '',                                    // [3] Заголовок страницы
            'wp-menu-separator'                    // [4] CSS класс
         );
      }
   } else {
      // Запасной вариант: добавляем разделитель на позицию 29.5
      // (если меню "Subscriptions" находится на позиции 30)
      if (!isset($menu['29.5'])) {
         $menu['29.5'] = array(
            '',
            'read',
            'separator-before-subscriptions',
            '',
            'wp-menu-separator'
         );
      }
   }
   
   // Примеры для других разделителей (раскомментируйте при необходимости):
   
   // Разделитель после "Subscriptions":
   // if (!isset($menu['30.5'])) {
   //    $menu['30.5'] = array('', 'read', 'separator-after-subscriptions', '', 'wp-menu-separator');
   // }
   
   // Разделитель перед "Search Stats":
   // if (!isset($menu['31.5'])) {
   //    $menu['31.5'] = array('', 'read', 'separator-before-search-stats', '', 'wp-menu-separator');
   // }
}

/**
 * ПРИМЕРЫ ИСПОЛЬЗОВАНИЯ:
 * 
 * 1. Разделитель перед конкретным пунктом меню (по slug):
 *    add_admin_menu_separator_before('newsletter-subscriptions', 'separator-before-subscriptions');
 * 
 * 2. Разделитель после конкретного пункта меню (по slug):
 *    add_admin_menu_separator_after('newsletter-subscriptions', 'separator-after-subscriptions');
 * 
 * 3. Разделитель на конкретной позиции:
 *    add_admin_menu_separator_at_position('29.5', 'separator-custom-1');
 * 
 * 4. Узнать slug существующих пунктов меню:
 *    Добавьте временно в functions.php:
 *    add_action('admin_menu', function() {
 *       global $menu;
 *       echo '<pre>';
 *       foreach ($menu as $pos => $item) {
 *          if (!empty($item[2])) {
 *             echo "Позиция: $pos, Slug: {$item[2]}, Название: {$item[0]}\n";
 *          }
 *       }
 *       echo '</pre>';
 *    }, 9999);
 */