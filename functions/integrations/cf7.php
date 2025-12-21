<?php

/**
 * Подключение панели согласий для CF7
 */
if (class_exists('WPCF7')) {
    require_once get_template_directory() . '/functions/integrations/cf7-consents-panel.php';
}

/**
 * Отключает автоматическую загрузку стилей и скриптов Contact Form 7 на всех страницах.
 *
 * По умолчанию Contact Form 7 подключает свои стили и скрипты на каждой странице сайта.
 * Эта функция позволяет отменить их автоматическую загрузку, чтобы подключать их
 * вручную только на нужных страницах (например, где используется форма).
 *
 * Ссылки:
 * @link https://contactform7.com/loading-javascript-and-stylesheet-only-when-it-is-necessary/
 * @link https://orbitingweb.com/blog/prevent-cf7-from-loading-css-js/
 *
 * Для активации — раскомментируйте нужные строки внутри функции.
 *
 * @return void
 */
function codeweber_cf7_styles_scripts()
{
   // Раскомментируйте строки ниже, чтобы отключить глобальную загрузку ресурсов CF7
   // wp_dequeue_script('contact-form-7');
   // wp_dequeue_style('contact-form-7');
}


/**
 * Удаляет стандартное сообщение об ошибке "Некорректные поля" в Contact Form 7.
 *
 * Contact Form 7 по умолчанию выводит сообщение при наличии невалидных полей,
 * но этот фильтр очищает его, чтобы не отображалось общее сообщение об ошибке.
 *
 * @param array $messages Массив сообщений Contact Form 7.
 * @return array Модифицированный массив сообщений.
 */
if (class_exists('WPCF7')) {
add_filter('wpcf7_messages', function ($messages) {
   // Очищаем конкретное сообщение об ошибке
   if (isset($messages['invalid_fields'])) {
      $messages['invalid_fields']['default'] = '';
   }

   return $messages;
});


/**
 * Регистрирует скрипты и стили для фронтенда, связанные с Contact Form 7.
 * 
 * Эта функция должна быть определена отдельно. Обычно используется для подключения
 * дополнительных JS или CSS, нужных для обработки согласий, кастомных полей и т.п.
 */
add_action('wp_enqueue_scripts', 'codeweber_cf7_styles_scripts');
}

/**
 * Подключение кастомных JavaScript файлов для CF7
 */
if (class_exists('WPCF7')) {
add_action('wp_enqueue_scripts', 'codeweber_cf7_custom_scripts', 20);
}
function codeweber_cf7_custom_scripts() {
    // Подключаем скрипты только если CF7 активирован
    if (!class_exists('WPCF7') || !wp_script_is('contact-form-7', 'registered')) {
        return;
    }

    // Подключаем валидацию форм (для CF7 форм с классом needs-validation)
    // Prefer dist version, fallback to src
    $form_validation_path = brk_get_dist_file_path('dist/assets/js/form-validation.js');
    $form_validation_url = brk_get_dist_file_url('dist/assets/js/form-validation.js');
    
    if ($form_validation_path && $form_validation_url) {
        $script_path = $form_validation_path;
        $script_url = $form_validation_url;
    } else {
        // Fallback to src
        $src_path = get_template_directory() . '/src/assets/js/form-validation.js';
        $src_url = get_template_directory_uri() . '/src/assets/js/form-validation.js';
        
        if (file_exists($src_path)) {
            $script_path = $src_path;
            $script_url = $src_url;
        } else {
            return; // File doesn't exist
        }
    }
    
    wp_enqueue_script(
        'codeweber-form-validation',
        $script_url,
        array(),
        filemtime($script_path),
        true
    );

    // Подключаем кастомную логику для CF7 acceptance чекбоксов
    $cf7_acceptance_path = brk_get_dist_file_path('dist/assets/js/cf7-acceptance-required.js');
    $cf7_acceptance_url = brk_get_dist_file_url('dist/assets/js/cf7-acceptance-required.js');
    
    if ($cf7_acceptance_path && $cf7_acceptance_url) {
        $acceptance_path = $cf7_acceptance_path;
        $acceptance_url = $cf7_acceptance_url;
    } else {
        // Fallback to src
        $src_path = get_template_directory() . '/src/assets/js/cf7-acceptance-required.js';
        $src_url = get_template_directory_uri() . '/src/assets/js/cf7-acceptance-required.js';
        
        if (file_exists($src_path)) {
            $acceptance_path = $src_path;
            $acceptance_url = $src_url;
        } else {
            return; // File doesn't exist
        }
    }
    
    wp_enqueue_script(
        'codeweber-cf7-acceptance-required',
        $acceptance_url,
        array('contact-form-7', 'codeweber-form-validation'),
        filemtime($acceptance_path),
        true
    );

    // Подключаем показ успешного сообщения через механизм codeweber forms
    $cf7_success_path = brk_get_dist_file_path('dist/assets/js/cf7-success-message.js');
    $cf7_success_url = brk_get_dist_file_url('dist/assets/js/cf7-success-message.js');
    
    if ($cf7_success_path && $cf7_success_url) {
        $success_path = $cf7_success_path;
        $success_url = $cf7_success_url;
    } else {
        // Fallback to src
        $src_path = get_template_directory() . '/src/assets/js/cf7-success-message.js';
        $src_url = get_template_directory_uri() . '/src/assets/js/cf7-success-message.js';
        
        if (file_exists($src_path)) {
            $success_path = $src_path;
            $success_url = $src_url;
        } else {
            return; // File doesn't exist
        }
    }
    
    wp_enqueue_script(
        'codeweber-cf7-success-message',
        $success_url,
        array('contact-form-7'),
        filemtime($success_path),
        true
    );

    // Подключаем UTM tracking для CF7 форм
    $cf7_utm_path = brk_get_dist_file_path('dist/assets/js/cf7-utm-tracking.js');
    $cf7_utm_url = brk_get_dist_file_url('dist/assets/js/cf7-utm-tracking.js');
    
    if ($cf7_utm_path && $cf7_utm_url) {
        $utm_path = $cf7_utm_path;
        $utm_url = $cf7_utm_url;
    } else {
        // Fallback to src
        $src_path = get_template_directory() . '/src/assets/js/cf7-utm-tracking.js';
        $src_url = get_template_directory_uri() . '/src/assets/js/cf7-utm-tracking.js';
        
        if (file_exists($src_path)) {
            $utm_path = $src_path;
            $utm_url = $src_url;
        } else {
            return; // File doesn't exist
        }
    }
    
    wp_enqueue_script(
        'codeweber-cf7-utm-tracking',
        $utm_url,
        array('contact-form-7'),
        filemtime($utm_path),
        true
    );
}




/**
 * Интеграция с Contact Form 7
 */

// Загружаем CSS и JS только при необходимости
// https://contactform7.com/loading-javascript-and-stylesheet-only-when-it-is-necessary/
// https://orbitingweb.com/blog/prevent-cf7-from-loading-css-js/

// Удаляет лишние обертки <span class="wpcf7-form-control-wrap">...</span> вокруг полей формы
add_filter('wpcf7_form_elements', function ($content) {
   $content = preg_replace('/<(span).*?class="\s*(?:.*\s)?wpcf7-form-control-wrap(?:\s[^"]+)?\s*"[^\>]*>(.*)<\/\1>/i', '\2', $content);
   $content = str_replace('<br />', '', $content); // Удаляет теги <br />
   return $content;
});

// Отключает автоматическое добавление абзацев и <br> тегов в разметку формы
add_filter('wpcf7_autop_or_not', '__return_false');



/**
 * Добавляет дополнительный CSS класс к атрибуту class формы Contact Form 7.
 *
 * Позволяет добавить кастомный класс (в данном случае 'needs-validation'), 
 * который можно использовать для стилизации или валидации формы.
 *
 * @param string $class Текущий список CSS классов формы.
 * @return string Обновленный список CSS классов с добавленным 'needs-validation'.
 */
if (class_exists('WPCF7')) {
add_filter('wpcf7_form_class_attr', 'custom_custom_form_class_attr');
}

function custom_custom_form_class_attr($class)
{
   $class .= ' needs-validation';
   return $class;
}


/**
 * Фильтр для элементов формы Contact Form 7
 *
 * Добавляет атрибуты required для обязательных полей и для отмеченных чекбоксов.
 * Это улучшает валидацию на стороне браузера, делая поля обязательными по стандарту HTML5.
 *
 * Рекомендуется подключать этот код в functions.php дочерней темы или основной темы.
 *
 * @param string $content HTML код формы CF7.
 * @return string Модифицированный HTML с добавленными атрибутами required.
 */
if (class_exists('WPCF7')) {
add_filter('wpcf7_form_elements', 'dd_wpcf7_form_elements_replace', 5);
}
function dd_wpcf7_form_elements_replace($content)
{
   // ШАГ 1: Добавляем атрибут required для элементов с aria-required="true"
   $content = preg_replace('/aria-required="true"/', 'aria-required="true"  required', $content);

   // ШАГ 2: Добавляем атрибуты required и aria-required="true" для отмеченных чекбоксов
   $content = preg_replace('/checked="checked"/', 'checked="checked" aria-required="true"  required', $content);

   return $content;
}

// ИСПРАВЛЕНО: Удаляем required из опциональных acceptance полей после всех других фильтров
// Используем более высокий приоритет (999), чтобы сработать после всех других фильтров
if (class_exists('WPCF7')) {
add_filter('wpcf7_form_elements', 'dd_wpcf7_remove_required_from_optional_acceptance', 999);
}
function dd_wpcf7_remove_required_from_optional_acceptance($content)
{
   // Получаем текущую форму CF7
   $contact_form = WPCF7_ContactForm::get_current();
   if (!$contact_form) {
      return $content;
   }
   
   $form_id = $contact_form->id();
   
   // Получаем согласия формы
   if (!class_exists('CF7_Consents_Panel')) {
      return $content;
   }
   
   $consents_panel = new CF7_Consents_Panel();
   $consents = $consents_panel->get_consents($form_id);
   
   if (empty($consents)) {
      return $content;
   }
   
   // Для каждого опционального согласия удаляем required из соответствующего input
   foreach ($consents as $consent) {
      if (empty($consent['document_id']) || !empty($consent['required'])) {
         continue; // Пропускаем обязательные согласия
      }
      
      $document_id = intval($consent['document_id']);
      $acceptance_name = 'form_consents_' . $document_id;
      
      // Удаляем required и aria-required из input с этим именем
      // Используем точный поиск полного тега input с сохранением структуры
      $pattern = '/(<input)(\s+[^>]*name=["\']' . preg_quote($acceptance_name, '/') . '["\'][^>]*)(>)/i';
      $content = preg_replace_callback($pattern, function ($matches) {
         $input_open = $matches[1]; // <input
         $input_attrs = $matches[2]; // все атрибуты с пробелом в начале
         $input_close = $matches[3]; // >
         
         // Удаляем required (в любом формате) - аккуратно, чтобы не сломать структуру
         $input_attrs = preg_replace('/\s+required(?=\s|>|$)/i', '', $input_attrs);
         $input_attrs = preg_replace('/\s+required="[^"]*"/i', '', $input_attrs);
         $input_attrs = preg_replace('/\s+required=\'[^\']*\'/i', '', $input_attrs);
         
         // Удаляем aria-required="true"
         $input_attrs = preg_replace('/\s+aria-required="true"/i', '', $input_attrs);
         $input_attrs = preg_replace('/\s+aria-required=\'true\'/i', '', $input_attrs);
         
         // Нормализуем множественные пробелы в один (но сохраняем пробел после <input)
         $input_attrs = preg_replace('/\s{2,}/', ' ', $input_attrs);
         $input_attrs = trim($input_attrs);
         
         // Если атрибуты не пустые, добавляем пробел после <input
         if (!empty($input_attrs)) {
            $input_attrs = ' ' . $input_attrs;
         }
         
         // Возвращаем полный тег
         return $input_open . $input_attrs . $input_close;
      }, $content);
   }
   
   return $content;
}




/**
 * Добавляет атрибут required ко всем checkbox в формах Contact Form 7.
 *
 * Это заставляет браузер требовать отметку всех чекбоксов перед отправкой формы.
 * Используйте с осторожностью, так как может сделать все чекбоксы обязательными,
 * что не всегда желательно.
 *
 * @param string $content HTML код формы CF7.
 * @return string Модифицированный HTML с добавленным required у чекбоксов.
 */
if (class_exists('WPCF7')) {
add_filter('wpcf7_form_elements', function ($content) {
   // Используем preg_replace_callback для точечной обработки каждого input[type="checkbox"]
   $content = preg_replace_callback(
      '/<input([^>]+type="checkbox"[^>]*)>/i',
      function ($matches) {
         $input = $matches[1];

         // Если в атрибутах уже есть класс "optional" — не добавляем required
         if (preg_match('/class="[^"]*optional[^"]*"/i', $input)) {
            return '<input' . $input . '>';
         }

         // Если already required — не дублируем
         if (strpos($input, 'required') !== false) {
            return '<input' . $input . '>';
         }

         // Добавляем required перед закрытием тега
         return '<input' . $input . ' required>';
      },
      $content
   );

   return $content;
});
}



/**
 * Фильтр для Contact Form 7, который удаляет обертки <span> вокруг input-элементов
 * с классами "wpcf7-list-item" и "wpcf7-form-control wpcf7-acceptance".
 * 
 * Это позволяет упростить HTML-разметку формы, оставляя только сами input элементы,
 * что может быть полезно для кастомной стилизации и улучшения контроля над формой.
 *
 * @param string $content HTML-код формы CF7.
 * @return string Модифицированный HTML-код без указанных span-оберток.
 */
if (class_exists('WPCF7')) {
add_filter('wpcf7_form_elements', function ($content) {
   // Удаляем <span class="wpcf7-list-item"> вокруг input
   $content = preg_replace_callback(
      '/<span class="wpcf7-list-item">\s*(<input[^>]+>)\s*<\/span>/i',
      function ($matches) {
         return $matches[1]; // Оставляем только <input>
      },
      $content
   );

   // Удаляем <span class="wpcf7-form-control wpcf7-acceptance"> вокруг input
   $content = preg_replace_callback(
      '/<span class="wpcf7-form-control wpcf7-acceptance">\s*(<input[^>]+>)\s*<\/span>/i',
      function ($matches) {
         return $matches[1]; // Оставляем только <input>
      },
      $content
   );

   return $content;
});
}


/**
 * Добавляет атрибут data-mask к полям телефона в формах CF7.
 * 
 * Ищет поля по классу wpcf7-tel, который CF7 автоматически добавляет к полям [tel*].
 * Поскольку CF7 не поддерживает произвольные data-атрибуты в синтаксисе тегов,
 * мы добавляем их программно через фильтр после рендеринга формы.
 *
 * @param string $content HTML-код формы CF7.
 * @return string Модифицированный HTML с добавленным data-mask у полей телефона.
 */
if (class_exists('WPCF7')) {
    add_filter('wpcf7_form_elements', function ($content) {
   // Добавляем data-mask ко всем input с классом wpcf7-tel
   $content = preg_replace_callback(
      '/<input([^>]*class=["\'][^"\']*wpcf7-tel[^"\']*["\'][^>]*)>/i',
      function ($matches) {
         $input_attrs = $matches[1];
         
         // Проверяем, нет ли уже data-mask
         if (strpos($input_attrs, 'data-mask') !== false) {
            return '<input' . $input_attrs . '>';
         }
         
         // Добавляем data-mask перед закрывающей скобкой
         return '<input' . $input_attrs . ' data-mask="+7 (___) ___-__-__">';
      },
      $content
   );
   
   return $content;
    }, 20); // Приоритет 20, чтобы сработать после других фильтров
}




add_action('rest_api_init', function () {
   register_rest_route('custom/v1', '/cf7-title/(?P<id>\d+)', [
      'methods' => 'GET',
      'callback' => 'get_cf7_form_title',
      'permission_callback' => '__return_true'
   ]);
});

function get_cf7_form_title($data)
{
   $form_id = $data['id'];

   // Находим пост формы по ID
   $form_post = get_post($form_id);

   if (!$form_post || $form_post->post_type !== 'wpcf7_contact_form') {
      return new WP_Error('form_not_found', 'Форма не найдена', ['status' => 404]);
   }

   return [
      'id' => $form_id,
      'title' => $form_post->post_title,
      'slug' => $form_post->post_name
   ];
}

/**
 * Добавление типов форм для CF7 (аналогично codeweber forms)
 * 
 * Добавляет панель "Form Type" в редактор CF7 для выбора типа формы.
 * Тип формы сохраняется в метаполе и добавляется как data-form-type атрибут к тегу <form>.
 */

if (class_exists('WPCF7')) {
    // Добавляем панель "Form Type" в редактор CF7
    add_filter('wpcf7_editor_panels', 'codeweber_cf7_add_form_type_panel');
    
    // Сохраняем тип формы при сохранении CF7 формы
    add_action('wpcf7_save_contact_form', 'codeweber_cf7_save_form_type', 10, 1);
    
    // Добавляем data-form-type атрибут к тегу <form> через правильный фильтр
    add_filter('wpcf7_form_additional_atts', 'codeweber_cf7_add_form_type_attribute', 10, 1);
}

/**
 * Добавляет панель "Form Type" в редактор CF7
 * 
 * @param array $panels Массив существующих панелей
 * @return array Массив панелей с добавленной панелью типов форм
 */
function codeweber_cf7_add_form_type_panel($panels) {
    $panels['form-type-panel'] = [
        'title' => __('Form Type', 'codeweber'),
        'callback' => 'codeweber_cf7_render_form_type_panel',
    ];
    return $panels;
}

/**
 * Отображает панель выбора типа формы
 * 
 * @param WPCF7_ContactForm $contact_form Объект формы CF7
 */
function codeweber_cf7_render_form_type_panel($contact_form) {
    $form_id = $contact_form->id();
    $form_type = get_post_meta($form_id, '_cf7_form_type', true);
    
    if (empty($form_type)) {
        $form_type = 'form'; // По умолчанию
    }
    
    wp_nonce_field('cf7_form_type_save', 'cf7_form_type_nonce');
    ?>
    <div id="cf7-form-type-panel">
        <h2><?php _e('Form Type', 'codeweber'); ?></h2>
        
        <p class="description">
            <?php _e('Select the type of this form. This will add a data-form-type attribute to the form wrapper, similar to codeweber forms.', 'codeweber'); ?>
        </p>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="cf7_form_type"><?php _e('Form Type', 'codeweber'); ?></label>
                </th>
                <td>
                    <select name="cf7_form_type" id="cf7_form_type" style="width: 100%; max-width: 300px;">
                        <option value="form" <?php selected($form_type, 'form'); ?>><?php _e('Regular Form', 'codeweber'); ?></option>
                        <option value="callback" <?php selected($form_type, 'callback'); ?>><?php _e('Callback Request', 'codeweber'); ?></option>
                        <option value="newsletter" <?php selected($form_type, 'newsletter'); ?>><?php _e('Newsletter Subscription', 'codeweber'); ?></option>
                        <option value="testimonial" <?php selected($form_type, 'testimonial'); ?>><?php _e('Testimonial Form', 'codeweber'); ?></option>
                        <option value="resume" <?php selected($form_type, 'resume'); ?>><?php _e('Resume Form', 'codeweber'); ?></option>
                        <option value="contact" <?php selected($form_type, 'contact'); ?>><?php _e('Contact Form', 'codeweber'); ?></option>
                    </select>
                    <p class="description">
                        <?php _e('This will add data-form-type="..." attribute to the form element, allowing JavaScript to identify the form type.', 'codeweber'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

/**
 * Сохраняет тип формы при сохранении CF7 формы
 * 
 * @param WPCF7_ContactForm $contact_form Объект формы CF7
 */
function codeweber_cf7_save_form_type($contact_form) {
    // Проверка nonce
    if (!isset($_POST['cf7_form_type_nonce']) || !wp_verify_nonce($_POST['cf7_form_type_nonce'], 'cf7_form_type_save')) {
        return;
    }
    
    // Проверка прав
    if (!current_user_can('wpcf7_edit_contact_form', $contact_form->id())) {
        return;
    }
    
    $form_id = $contact_form->id();
    $form_type = isset($_POST['cf7_form_type']) ? sanitize_text_field($_POST['cf7_form_type']) : 'form';
    
    // Валидация типа формы
    $allowed_types = ['form', 'callback', 'newsletter', 'testimonial', 'resume', 'contact'];
    if (!in_array($form_type, $allowed_types)) {
        $form_type = 'form'; // Fallback к дефолтному типу
    }
    
    // Сохраняем в метаполе
    update_post_meta($form_id, '_cf7_form_type', $form_type);
}

/**
 * Добавляет data-form-type атрибут к тегу <form> в CF7
 * 
 * Использует фильтр wpcf7_form_additional_atts для добавления атрибутов к форме
 * 
 * @param array $atts Массив дополнительных атрибутов формы
 * @return array Массив атрибутов с добавленным data-form-type
 */
function codeweber_cf7_add_form_type_attribute($atts) {
    // #region agent log
    $log_file = 'c:\laragon\www\bricksnew\.cursor\debug.log';
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'post-fix',
        'hypothesisId' => 'FIX',
        'location' => 'cf7.php:581',
        'message' => 'Function called with wpcf7_form_additional_atts',
        'data' => ['atts' => $atts, 'atts_type' => gettype($atts)],
        'timestamp' => time() * 1000
    ]) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    // #endregion
    
    // Получаем текущую форму CF7
    $contact_form = WPCF7_ContactForm::get_current();
    
    // #region agent log
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'post-fix',
        'hypothesisId' => 'FIX',
        'location' => 'cf7.php:590',
        'message' => 'get_current result',
        'data' => ['has_contact_form' => ($contact_form !== null), 'form_id' => $contact_form ? $contact_form->id() : null],
        'timestamp' => time() * 1000
    ]) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    // #endregion
    
    if (!$contact_form) {
        // #region agent log
        $log_entry = json_encode([
            'sessionId' => 'debug-session',
            'runId' => 'post-fix',
            'hypothesisId' => 'FIX',
            'location' => 'cf7.php:595',
            'message' => 'Early return - no contact form',
            'data' => [],
            'timestamp' => time() * 1000
        ]) . "\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        // #endregion
        return $atts;
    }
    
    $form_id = $contact_form->id();
    $form_type = get_post_meta($form_id, '_cf7_form_type', true);
    
    // #region agent log
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'post-fix',
        'hypothesisId' => 'FIX',
        'location' => 'cf7.php:603',
        'message' => 'Form type from meta',
        'data' => ['form_id' => $form_id, 'form_type' => $form_type, 'form_type_empty' => empty($form_type)],
        'timestamp' => time() * 1000
    ]) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    // #endregion
    
    // Если тип не задан, используем 'form' по умолчанию
    if (empty($form_type)) {
        $form_type = 'form';
    }
    
    // Убеждаемся, что $atts - массив
    if (!is_array($atts)) {
        $atts = [];
    }
    
    // Добавляем data-form-type атрибут
    $atts['data-form-type'] = $form_type;
    
    // #region agent log
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'post-fix',
        'hypothesisId' => 'FIX',
        'location' => 'cf7.php:625',
        'message' => 'Added data-form-type to atts',
        'data' => ['form_type' => $form_type, 'final_atts' => $atts],
        'timestamp' => time() * 1000
    ]) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    // #endregion
    
    return $atts;
}