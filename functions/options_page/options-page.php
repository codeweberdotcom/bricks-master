<?php

// Регистрация страницы настроек
function register_dynamic_tab_options_page()
{
   add_menu_page(
      'Options Page',          // Название страницы
      'Options Page',          // Название в меню
      'manage_options',        // Права доступа
      'dynamic-tabs',          // Слаг страницы
      'dynamic_tabs_page',     // Функция, которая выводит содержимое страницы
      'dashicons-admin-tools', // Иконка меню
      100                       // Позиция
   );
}
add_action('admin_menu', 'register_dynamic_tab_options_page');



// Добавление пункта и его подменю в верхнюю панель админки
function add_dynamic_tabs_and_submenus_to_admin_bar($wp_admin_bar)
{
    // Проверка прав пользователя
    if (!current_user_can('manage_options')) {
        return;
    }

    // Основной пункт меню
    $wp_admin_bar->add_node([
        'id'    => 'dynamic-tabs', // Уникальный ID
        'title' => 'Options Page', // Название
        'href'  => admin_url('admin.php?page=dynamic-tabs'), // Ссылка
        'meta'  => [
            'class' => 'dynamic-tabs-class',
            'title' => 'Перейти на страницу Options Page'
        ]
    ]);

    // Подключаем глобальную переменную $submenu
    global $submenu;

    // Проверяем, есть ли подменю у слага 'dynamic-tabs'
    if (isset($submenu['dynamic-tabs'])) {
        foreach ($submenu['dynamic-tabs'] as $key => $item) {
            // Формируем корректный URL для страницы подменю
            $subpage_url = admin_url('admin.php?page=' . $item[2]);

            $wp_admin_bar->add_node([
                'id'     => 'dynamic-tabs-' . $key, // Уникальный ID для подменю
                'parent' => 'dynamic-tabs',         // Родительский пункт меню
                'title'  => $item[0],              // Название подменю
                'href'   => $subpage_url,          // Ссылка на подменю
            ]);
        }
    }
}
add_action('admin_bar_menu', 'add_dynamic_tabs_and_submenus_to_admin_bar', 100);

// Функция для создания вкладок на основе найденных папок
function dynamic_tabs_page()
{
?>
   <div class="wrap">
      <h1>Options Page</h1>

      <form method="post" action="options.php">
         <?php
         // Настройки (выводим вкладки)
         settings_fields('dynamic_tabs_group');

         // Путь к папке с модулями
         $folder_path = get_template_directory() . '/functions/options_page/pages'; // Путь к папке

         // Получаем все папки в папке pages
         $modules = [];
         $directories = array_filter(glob($folder_path . '/*'), 'is_dir');

         foreach ($directories as $directory) {
            $module_name = basename($directory); // Имя модуля (папки)
            $modules[$module_name] = [
               'content_file' => $directory . '/' . $module_name . '-content.php', // Файл для контента вкладки
            ];
         }

         // По умолчанию выбираем первый модуль, если вкладка не выбрана
         $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : (isset($modules) ? key($modules) : '');

         // Выводим вкладки
         ?>
         <h2 class="nav-tab-wrapper">
            <?php
            foreach ($modules as $module_name => $files) {
               $active_class = ($current_tab === $module_name) ? ' nav-tab-active' : '';
            ?>
               <a href="?page=dynamic-tabs&tab=<?php echo esc_attr($module_name); ?>" class="nav-tab<?php echo esc_attr($active_class); ?>">
                  <?php echo esc_html($module_name); ?>
               </a>
            <?php
            }
            ?>
         </h2>

         <?php
         // Вставляем админский файл и контент для выбранной вкладки
         if ($current_tab && isset($modules[$current_tab])) {
            // Вставляем контент вкладки
            if (file_exists($modules[$current_tab]['content_file'])) {
               include $modules[$current_tab]['content_file'];
            }
         } else {
            echo '<p>Выберите вкладку для отображения контента.</p>';
         }

         ?>
      </form>
   </div>
<?php
}

// Регистрация настроек
function dynamic_tabs_settings_init()
{
   // Здесь можно зарегистрировать настройки, если необходимо
   register_setting('dynamic_tabs_group', 'dynamic_tabs_settings');
}
add_action('admin_init', 'dynamic_tabs_settings_init');


/**
 * Функция для добавления динамических подменю
 */
function register_dynamic_submenus()
{
   $submenus_dir = __DIR__ . '/submenus'; // Путь к папке submenus

   // Проверяем, существует ли папка
   if (!is_dir($submenus_dir)) {
      return;
   }

   // Получаем список подкаталогов в папке submenus
   $folders = array_filter(glob($submenus_dir . '/*'), 'is_dir');

   foreach ($folders as $folder) {
      $submenu_slug = basename($folder); // Имя папки используется как slug
      $submenu_file = $folder . '/' . $submenu_slug . '-admin.php'; // Путь к файлу -admin.php

      // Проверяем, существует ли файл -admin.php
      if (file_exists($submenu_file)) {
         // Подключаем файл, где уже определена callback-функция
         require_once $submenu_file;

         // Проверяем, существует ли функция callback
         $callback_function = "{$submenu_slug}_callback";

         if (function_exists($callback_function)) {
            // Регистрируем подменю
            add_submenu_page(
               'dynamic-tabs',        // Родительская страница (слаг основного меню)
               ucfirst($submenu_slug), // Название страницы (с заглавной буквы)
               ucfirst($submenu_slug), // Название меню
               'manage_options',       // Права доступа
               $submenu_slug,          // Слаг подменю
               $callback_function      // Callback-функция
            );
         }
      }
   }
}
add_action('admin_menu', 'register_dynamic_submenus');

// Обработчик AJAX-запроса для Gulp
add_action('wp_ajax_run_gulp_task', 'run_gulp_task');

function run_gulp_task()
{
   // Проверка прав доступа
   if (!current_user_can('manage_options')) {
      wp_send_json_error(['message' => 'У вас нет прав для выполнения этого действия.']);
   }

   // Проверка nonce
   if (!isset($_POST['gulp_nonce']) || !wp_verify_nonce($_POST['gulp_nonce'], 'run_gulp_task')) {
      wp_send_json_error(['message' => 'Ошибка безопасности.']);
   }

   // Команда для запуска Gulp
   $command = 'cd ' . ABSPATH . 'wp-content/themes/codeweber-main/src/ && gulp build:dist 2>&1';

   // Переменные для вывода и кода завершения
   $output = [];
   $return_var = 0;

   // Выполнение команды
   exec($command, $output, $return_var);

   // Обработка результата
   if ($return_var === 0) {
      wp_send_json_success([
         'message' => 'Сборка завершена успешно!',
         'terminal_output' => implode("\n", $output)
      ]);
   } else {
      wp_send_json_error([
         'message' => 'Ошибка при запуске Gulp.',
         'terminal_output' => implode("\n", $output)
      ]);
   }
}


// Подключение JS скрипта для Gulp
function gulp_admin_scripts()
{
   wp_enqueue_script('gulp-admin', get_template_directory_uri() . '/functions/options_page/submenus/gulp/gulp-admin.js', [], null, true);

   // Генерация nonce
   $nonce = wp_create_nonce('run_gulp_task');

   // Передача данных в JS
   wp_localize_script('gulp-admin', 'ajax_object', [
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce'   => $nonce
   ]);
}
add_action('admin_enqueue_scripts', 'gulp_admin_scripts');
?>