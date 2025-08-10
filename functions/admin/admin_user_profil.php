<?php

/**
 * Добавляет поле телефона в профиль пользователя в админке и экспортирует его через стандартную систему экспорта персональных данных WordPress.
 */

/**
 * Показывает поле "Телефон" на странице профиля пользователя в админке.
 *
 * @param WP_User $user Пользователь.
 */
function add_phone_field_to_user_profile($user)
{
   $phone = esc_attr(get_user_meta($user->ID, 'phone', true));
   $verified = get_user_meta($user->ID, 'phone_verified', true);

   $status_text = $verified ? __('Verified', 'codeweber') : __('Not verified', 'codeweber');
   $status_color = $verified ? 'green' : 'red';
   global $opt_name;
   $woophonenumbersms  = Redux::get_option($opt_name, 'woophonenumbersms');

   echo '<h3>' . __('Additional information', 'codeweber') . '</h3>';
   echo '<table class="form-table"><tr>';
   echo '<th><label for="phone">' . __('Phone', 'codeweber') . '</label></th>';
   echo '<td>';
   echo '<input type="text" name="phone" id="phone" value="' . $phone . '" class="regular-text" />';
   // Если оба параметра включены — выводим верстку
   if ($woophonenumbersms) {
      echo '<span style="margin-left: 10px; color:' . $status_color . ';">' . $status_text . '</span>';
   };
   echo '</td>';
   echo '</tr></table>';
}
add_action('show_user_profile', 'add_phone_field_to_user_profile');
add_action('edit_user_profile', 'add_phone_field_to_user_profile');


/**
 * Сохраняет значение поля "Телефон" при обновлении профиля пользователя.
 *
 * @param int $user_id ID пользователя.
 * @return bool|void
 */
function save_phone_field($user_id)
{
   if (!current_user_can('edit_user', $user_id)) {
      return false;
   }

   if (isset($_POST['phone'])) {
      update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
      update_user_meta($user_id, 'phone_verified', 0);
   }
}
add_action('personal_options_update', 'save_phone_field');
add_action('edit_user_profile_update', 'save_phone_field');

/**
 * Регистрирует экспортёр персональных данных для телефона пользователя.
 */
add_filter('wp_privacy_personal_data_exporters', function ($exporters) {
   $exporters['user_phone'] = [
      'exporter_friendly_name' => __('User Phone', 'codeweber'),
      'callback' => function ($email_address, $page) {
         $user = get_user_by('email', $email_address);

         if (!$user) {
            return ['data' => [], 'done' => true];
         }

         $phone = get_user_meta($user->ID, 'phone', true);
         $data  = [];

         if (!empty($phone)) {
            $data[] = [
               'name'  => __('Phone', 'codeweber'),
               'value' => $phone,
            ];
         }

         return [
            'data' => $data ? [[
               'group_id'    => 'user',
               'group_label' => __('Data User profil', 'codeweber'),
               'item_id'     => "user-{$user->ID}",
               'data'        => $data,
            ]] : [],
            'done' => true,
         ];
      }
   ];

   return $exporters;
});



// Вставляем отчество сразу после фамилии
add_action('show_user_profile', 'cw_add_middle_name_after_last_name', 1);
add_action('edit_user_profile', 'cw_add_middle_name_after_last_name', 1);

function cw_add_middle_name_after_last_name($user)
{
?>
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const lastNameRow = document.querySelector('tr.user-last-name-wrap');
         if (!lastNameRow) return;

         const middleNameRow = document.createElement('tr');
         middleNameRow.classList.add('user-middle-name-wrap');
         middleNameRow.innerHTML = `
                <th><label for="middle_name">Отчество</label></th>
                <td>
                    <input type="text" name="middle_name" id="middle_name" value="<?php echo esc_attr(get_user_meta($user->ID, 'middle_name', true)); ?>" class="regular-text" />
                </td>
            `;

         lastNameRow.insertAdjacentElement('afterend', middleNameRow);
      });
   </script>
<?php
}

// Сохраняем отчество при сохранении профиля
add_action('personal_options_update', 'cw_save_middle_name_field_inline');
add_action('edit_user_profile_update', 'cw_save_middle_name_field_inline');

function cw_save_middle_name_field_inline($user_id)
{
   if (!current_user_can('edit_user', $user_id)) return;

   if (isset($_POST['middle_name'])) {
      update_user_meta($user_id, 'middle_name', sanitize_text_field($_POST['middle_name']));
   }
}
