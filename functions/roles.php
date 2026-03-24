<?php

function create_simpleadmin_role()
{
   // Создание роли simpleadmin с копированием возможностей администратора
   add_role(
      'simpleadmin',
      __('Simple Admin', 'codeweber'),
      get_role('administrator')->capabilities // Копируем все возможности администратора
   );
}
add_action('after_setup_theme', 'create_simpleadmin_role', 5);
