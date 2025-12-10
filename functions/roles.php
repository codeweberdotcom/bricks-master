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
add_action('init', 'create_simpleadmin_role');