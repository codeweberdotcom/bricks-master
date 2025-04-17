<?php

function create_superadmin_role()
{
   // Создание роли superadmin с копированием возможностей администратора
   add_role(
      'superadmin',
      __('Super Admin', 'codeweber'),
      get_role('administrator')->capabilities // Копируем все возможности администратора
   );
}
add_action('init', 'create_superadmin_role');