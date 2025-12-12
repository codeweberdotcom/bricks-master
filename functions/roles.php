<?php

function create_simpleadmin_role()
{
   // Создание роли simpleadmin с копированием возможностей администратора
   add_role(
      'simpleadmin',
      __('Simple Admin', 'codeweber'),
      get_role('administrator')->capabilities // Копируем все возможности администратора
   );
   // #region agent log
   $log_data = json_encode(['location' => 'roles.php:12', 'message' => 'Simple admin role created', 'data' => ['role_exists' => get_role('simpleadmin') !== null, 'hook' => 'after_setup_theme', 'priority' => 5], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']);
   $log_file = ABSPATH . '.cursor/debug.log';
   @file_put_contents($log_file, $log_data . "\n", FILE_APPEND);
   // #endregion
}
add_action('after_setup_theme', 'create_simpleadmin_role', 5);
