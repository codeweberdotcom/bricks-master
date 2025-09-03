<?php

/**
 * Включает поддержку шорткодов внутри HTML-кода форм CF7.
 * Это позволяет использовать шорткоды вроде [cf7_legal_consent_link] прямо в шаблоне формы.
 */
add_filter('wpcf7_form_elements', function ($content) {
   return do_shortcode($content);
});

/**
 * Сохраняет или обновляет подписчика в CPT
 */
function save_subscriber_to_cpt($email, $phone = '', $user_id = 0)
{
   if (!class_exists('Consent_CPT')) {
      error_log('Consent_CPT class not found');
      return false;
   }

   $cpt_manager = Consent_CPT::get_instance();
   $subscriber_id = $cpt_manager->find_or_create_subscriber($email, $phone, $user_id);

   if (is_wp_error($subscriber_id) || !$subscriber_id) {
      error_log('Failed to create subscriber: ' . ($subscriber_id->get_error_message() ?? 'Unknown error'));
      return false;
   }

   return $subscriber_id;
}

/**
 * Сохраняет согласия в CPT
 */
function save_consents_to_cpt($subscriber_id, $consents_data)
{
   if (!class_exists('Consent_CPT')) {
      error_log('Consent_CPT class not found');
      return false;
   }

   $cpt_manager = Consent_CPT::get_instance();

   foreach ($consents_data as $consent) {
      $result = $cpt_manager->add_consent($subscriber_id, $consent);
      if (!$result) {
         error_log('Failed to save consent for subscriber: ' . $subscriber_id);
      }
   }

   return true;
}

/**
 * Добавляет новую панель «Legal Consent» в редактор Contact Form 7.
 * Эта панель позволяет выбрать связанные юридические документы:
 * - Документ согласия на обработку данных (post_type = 'legal')
 * - Страницу с политикой конфиденциальности (post_type = 'page')
 */
add_filter('wpcf7_editor_panels', function ($panels) {
   $panels['legal_consent'] = [
      'title'    => __('Legal Consent', 'codeweber'),
      'callback' => 'codeweber_cf7_legal_consent_panel',
   ];
   return $panels;
});


/**
 * Отображает HTML-содержимое панели «Legal Consent» в редакторе формы.
 *
 * @param WPCF7_ContactForm $contact_form Объект текущей формы CF7.
 */
function codeweber_cf7_legal_consent_panel($contact_form)
{
   $form_id = $contact_form->id();

   $legal_posts = get_posts([
      'post_type'      => 'legal',
      'posts_per_page' => -1,
      'post_status'    => 'publish',
      'orderby'        => 'title',
      'order'          => 'ASC',
   ]);


   // Legal consent
   $selected_legal_id = get_post_meta($form_id, '_legal_consent_doc', true);
   if (empty($selected_legal_id)) {
      $legal_post = get_page_by_path('consent-processing', OBJECT, 'legal');
      if ($legal_post) $selected_legal_id = $legal_post->ID;
   }

   // Mailing consent (новое поле)
   $selected_mailing_id = get_post_meta($form_id, '_mailing_consent_doc', true);
   if (empty($selected_mailing_id)) {
      $mailing_post = get_page_by_path('email-consent', OBJECT, 'legal');
      if ($mailing_post) $selected_mailing_id = $mailing_post->ID;
   }

   $selected_privacy_id = get_post_meta($form_id, '_privacy_policy_page', true);
   if (empty($selected_privacy_id)) {
      $default_privacy_page_id = (int) get_option('wp_page_for_privacy_policy');
      if ($default_privacy_page_id) $selected_privacy_id = $default_privacy_page_id;
   }

?>
   <fieldset>
      <h2><?php _e('Select Legal Documents for This contact form:', 'codeweber'); ?></h2>

      <!-- Legal Consent -->
      <p>
         <label>
            <?php _e('Consent Document - Legal:', 'codeweber'); ?><br>
            <p><?php _e('Shortcode for displaying a document link in the form code: [cf7_legal_consent_link]', 'codeweber'); ?></p>
            <select name="legal_consent_doc">
               <option value=""><?php _e('— Select —', 'codeweber'); ?></option>
               <?php foreach ($legal_posts as $post): ?>
                  <option value="<?php echo esc_attr($post->ID); ?>" <?php selected($selected_legal_id, $post->ID); ?>>
                     <?php echo esc_html($post->post_title); ?>
                  </option>
               <?php endforeach; ?>
            </select>
         </label>
      </p>

      <!-- Mailing Consent (НОВОЕ) -->
      <p>
         <label>
            <?php _e('Consent Document - Mailing:', 'codeweber'); ?><br>
            <p><?php _e('Shortcode for displaying a document link in the form code: [cf7_mailing_consent_link]', 'codeweber'); ?></p>
            <select name="mailing_consent_doc">
               <option value=""><?php _e('— Select —', 'codeweber'); ?></option>
               <?php foreach ($legal_posts as $post): ?>
                  <option value="<?php echo esc_attr($post->ID); ?>" <?php selected($selected_mailing_id, $post->ID); ?>>
                     <?php echo esc_html($post->post_title); ?>
                  </option>
               <?php endforeach; ?>
            </select>
         </label>
      </p>

      <!-- Privacy Policy -->
      <p>
         <label>
            <?php _e('Privacy Policy - Page:', 'codeweber'); ?><br>
            <p><?php _e('Shortcode for displaying a document link in the form code: [cf7_privacy_policy]', 'codeweber'); ?></p>
            <select name="privacy_policy_page">
               <option value=""><?php _e('— Select —', 'codeweber'); ?></option>
               <?php foreach ($legal_posts as $page): ?>
                  <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($selected_privacy_id, $page->ID); ?>>
                     <?php echo esc_html($page->post_title); ?>
                  </option>
               <?php endforeach; ?>
            </select>
         </label>
      </p>
   </fieldset>

<?php
   $form_content = $contact_form->prop('form');

   // Privacy Policy check
   if (preg_match('/\[cf7_privacy_policy[^\]]*\]/i', $form_content)) {
      $doc_id = get_post_meta($form_id, '_privacy_policy_page', true);

      if ($doc_id) {
         if (get_post_status($doc_id) === 'publish') {
            echo '<p><strong><a href="' . esc_url(get_permalink($doc_id)) . '" target="_blank" rel="noopener noreferrer">'
               . sprintf(__('Privacy Policy: %s', 'codeweber'), esc_html(get_the_title($doc_id)))
               . '</a></strong></p>';
         } else {
            echo '<p><strong style="color:red;">'
               . __('The privacy policy document is selected but not published. Please publish the document.', 'codeweber')
               . '</strong></p>';
         }
      } else {
         echo '<p><strong style="color:red;">'
            . __('No privacy policy document selected. Please select a document in the form settings.', 'codeweber')
            . '</strong></p>';
      }
   } else {
      echo '<p><strong style="color:red;"><em>' . __('Privacy policy shortcode not found in the form.', 'codeweber') . '</em></p>';
   }

   // Legal Consent check
   if (preg_match('/\[cf7_legal_consent_link[^\]]*\]/i', $form_content)) {
      $doc_id = get_post_meta($form_id, '_legal_consent_doc', true);

      if ($doc_id) {
         if (get_post_status($doc_id) === 'publish') {
            echo '<p><strong><a href="' . esc_url(get_permalink($doc_id)) . '" target="_blank" rel="noopener noreferrer">'
               . sprintf(__('Consent Document: %s', 'codeweber'), esc_html(get_the_title($doc_id)))
               . '</a></strong></p>';
         } else {
            echo '<p><strong style="color:red;">'
               . __('The consent document is selected but not published. Please publish the document.', 'codeweber')
               . '</strong></p>';
         }
      } else {
         echo '<p><strong style="color:red;">'
            . __('No consent document selected. Please select a document in the form settings.', 'codeweber')
            . '</strong></p>';
      }
   } else {
      echo '<p><strong style="color:red;"><em>' . __('Consent document shortcode not found in the form.', 'codeweber') . '</em></p>';
   }

   // Mailing Consent check
   if (preg_match('/\[cf7_mailing_consent_link[^\]]*\]/i', $form_content)) {
      $doc_id = get_post_meta($form_id, '_mailing_consent_doc', true);

      if ($doc_id) {
         if (get_post_status($doc_id) === 'publish') {
            echo '<p><strong><a href="' . esc_url(get_permalink($doc_id)) . '" target="_blank" rel="noopener noreferrer">'
               . sprintf(__('Mailing Consent: %s', 'codeweber'), esc_html(get_the_title($doc_id)))
               . '</a></strong></p>';
         } else {
            echo '<p><strong style="color:red;">'
               . __('The mailing consent document is selected but not published. Please publish the document.', 'codeweber')
               . '</strong></p>';
         }
      } else {
         echo '<p><strong style="color:red;">'
            . __('No mailing consent document selected. Please select a document in the form settings.', 'codeweber')
            . '</strong></p>';
      }
   } else {
      echo '<p><strong style="color:red;"><em>' . __('Mailing consent shortcode not found in the form.', 'codeweber') . '</em></p>';
   }
}




/**
 * Сохраняет мета-данные формы CF7 после её сохранения в админке.
 * 
 * Сохраняются ID юридических документов:
 * - `_legal_consent_doc`: документ согласия на обработку данных
 * - `_privacy_policy_page`: страница политики конфиденциальности
 * 
 * @hook wpcf7_after_save
 * @param WPCF7_ContactForm $contact_form Объект формы CF7.
 */
add_action('wpcf7_after_save', function ($contact_form) {
   $form_id = $contact_form->id();

   if (isset($_POST['legal_consent_doc'])) {
      update_post_meta($form_id, '_legal_consent_doc', intval($_POST['legal_consent_doc']));
   }

   if (isset($_POST['privacy_policy_page'])) {
      update_post_meta($form_id, '_privacy_policy_page', intval($_POST['privacy_policy_page']));
   }

   // Добавь это для mailing_consent_doc
   if (isset($_POST['mailing_consent_doc'])) {
      update_post_meta($form_id, '_mailing_consent_doc', intval($_POST['mailing_consent_doc']));
   }
});




/**
 * Шорткод `[cf7_legal_consent_link id="123"]`
 * 
 * Возвращает ссылку на опубликованный документ согласия, 
 * связанный с указанной формой Contact Form 7.
 * 
 * @param array $atts Атрибуты шорткода. Требуется `id` — ID формы.
 * @return string URL документа или пустая строка, если не найден.
 */
add_shortcode('cf7_legal_consent_link', function ($atts) {

   $atts = shortcode_atts(['id' => 0], $atts);

   $form_id = intval($atts['id']);

   if (!$form_id) {
      return '';
   }

   $doc_id = get_post_meta($form_id, '_legal_consent_doc', true);

   if (!$doc_id) {
      return '';
   }

   $post_status = get_post_status($doc_id);

   if ($post_status !== 'publish') {
      return '';
   }

   $url = esc_url(get_permalink($doc_id));

   return $url;
});


/**
 * Шорткод `[cf7_mailing_consent_link id="123"]`
 * 
 * Возвращает ссылку на опубликованный документ согласия для рассылки,
 * связанный с указанной формой Contact Form 7.
 * 
 * @param array $atts Атрибуты шорткода. Требуется `id` — ID формы.
 * @return string URL документа или пустая строка, если не найден.
 */
add_shortcode('cf7_mailing_consent_link', function ($atts) {

   $atts = shortcode_atts(['id' => 0], $atts);

   $form_id = intval($atts['id']);

   if (!$form_id) {
      return '';
   }

   $doc_id = get_post_meta($form_id, '_mailing_consent_doc', true);

   if (!$doc_id) {
      return '';
   }

   $post_status = get_post_status($doc_id);

   if ($post_status !== 'publish') {
      return '';
   }

   $url = esc_url(get_permalink($doc_id));

   return $url;
});


/**
 * Шорткод `[cf7_privacy_policy id="123"]`
 * 
 * Возвращает ссылку на опубликованную страницу политики конфиденциальности,
 * связанную с указанной формой Contact Form 7.
 * 
 * @param array $atts Атрибуты шорткода. Требуется `id` — ID формы.
 * @return string URL страницы политики или пустая строка, если не найдена.
 */
add_shortcode('cf7_privacy_policy', function ($atts) {
   $atts = shortcode_atts([
      'id' => 0, // ID формы CF7
   ], $atts);

   $form_id = intval($atts['id']);
   if (!$form_id) {
      return ''; // ID не передан — ничего не возвращаем
   }

   $page_id = get_post_meta($form_id, '_privacy_policy_page', true);
   if (!$page_id || get_post_status($page_id) !== 'publish') {
      return ''; // Страница не задана или не опубликована
   }

   return esc_url(get_permalink($page_id));
});


/**
 * Автоматически обновляет шорткоды `[cf7_legal_consent_link]`, `[cf7_privacy_policy]`
 * и `[cf7_mailing_consent_link]` в контенте формы Contact Form 7 при её сохранении.
 *
 * Заменяет их на версии с текущим ID формы, чтобы обеспечить корректную работу ссылок.
 * Это необходимо, если формы дублируются или создаются новые, чтобы ID формы передавался внутрь шорткодов.
 *
 * @hook wpcf7_after_save
 * @param WPCF7_ContactForm $contact_form Объект сохранённой формы.
 */
add_action('wpcf7_after_save', function ($contact_form) {
   $form_id = $contact_form->id();
   $form_content = $contact_form->prop('form');

   if (empty($form_content)) {
      return;
   }

   $new_content = $form_content;

   // Шорткод [cf7_legal_consent_link ...]
   $pattern1 = '/\[cf7_legal_consent_link[^\]]*\]/i';
   $replacement1 = "[cf7_legal_consent_link id='{$form_id}']";
   $new_content = preg_replace($pattern1, $replacement1, $new_content);

   // Шорткод [cf7_privacy_policy ...]
   $pattern2 = '/\[cf7_privacy_policy[^\]]*\]/i';
   $replacement2 = "[cf7_privacy_policy id='{$form_id}']";
   $new_content = preg_replace($pattern2, $replacement2, $new_content);

   // Шорткод [cf7_mailing_consent_link ...]
   $pattern3 = '/\[cf7_mailing_consent_link[^\]]*\]/i';
   $replacement3 = "[cf7_mailing_consent_link id='{$form_id}']";
   $new_content = preg_replace($pattern3, $replacement3, $new_content);

   // Сохраняем только если что-то изменилось
   if ($new_content !== $form_content) {
      $contact_form->set_properties(['form' => $new_content]);
      $contact_form->save();
   }
});


/**
 *  * Добавляет новую вкладку "Field Mapping" (Сопоставление полей) в редактор форм Contact Form 7.
 *
 * Contact Form 7 (CF7) предоставляет в админке редактор форм,
 * который разбит на несколько панелей (вкладок).
 * Этот фильтр позволяет расширить набор вкладок, добавляя свои.
 *
 * @param array $panels Массив панелей редактора CF7
 * @return array Обновленный массив панелей с добавленной вкладкой
 */
add_filter('wpcf7_editor_panels', 'cw_add_field_mapping_panel');
function cw_add_field_mapping_panel($panels)
{
   $panels['field_mapping'] = [
      'title'    => __('Field Mapping', 'codeweber'),
      'callback' => 'cw_render_field_mapping_panel',
   ];
   return $panels;
}

/**
 * Отображает содержимое вкладка "Field Mapping" в редакторе CF7
 * Позволяет сопоставить поля формы с заданными типами данных
 *
 * @param WPCF7_ContactForm $contact_form Объект формы CF7
 * @return void
 */
function cw_render_field_mapping_panel($contact_form)
{
   $form_id = $contact_form->id();
   $form_content = $contact_form->prop('form');

   // Получаем имена полей из формы CF7
   preg_match_all('/\[(?:[^\s\]]+\*?)\s+([^\s\]]+)/', $form_content, $matches);
   $form_fields = !empty($matches[1]) ? $matches[1] : [];

   // Field types for mapping
   $fields = [
      'first_name'  => __('First Name', 'codeweber'),
      'last_name'   => __('Last Name', 'codeweber'),
      'middle_name' => __('Middle Name', 'codeweber'),
      'phone'       => __('Phone', 'codeweber'),
      'email'       => __('E-mail', 'codeweber'),
      'checkboxprivacy'     => __('Checkbox for Privacy Policy', 'codeweber'),
      'checkboxconsent'     => __('Checkbox for Consent', 'codeweber'),
      'checkboxnewsletter'  => __('Checkbox for Newsletter', 'codeweber'),
   ];

   echo '<h2>' . __('Field Mapping:', 'codeweber') . '</h2>';
   echo '<p>' . __("Mapping form fields to module fields for storing user consent logs with the site's legal documents.", "codeweber") . '</p>';

   echo '<table class="form-table">';
   foreach ($fields as $key => $label) {
      echo '<tr>';
      echo '<th scope="row"><label for="field_mapping_' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
      echo '<td>';
      echo '<select name="cw_field_mapping[' . esc_attr($key) . ']" id="field_mapping_' . esc_attr($key) . '">';
      echo '<option value="">' . __('— Not selected —', 'codeweber') . '</option>';

      foreach ($form_fields as $field_name) {
         $selected = selected(cw_get_saved_field_mapping($form_id, $key), $field_name, false);
         echo '<option value="' . esc_attr($field_name) . '"' . $selected . '>' . esc_html($field_name) . '</option>';
      }

      echo '</select>';
      echo '</td>';
      echo '</tr>';
   }

   echo '</table>';
}


/**
 * Получает сохранённое сопоставление поля формы CF7 по ключу.
 *
 * @param int    $form_id ID формы CF7
 * @param string $key     Ключ поля для сопоставления (например, 'first_name', 'email' и т.п.)
 * @return string Значение сохранённого сопоставления или пустая строка
 */
function cw_get_saved_field_mapping($form_id, $key)
{
   $mappings = get_post_meta($form_id, '_cw_field_mapping', true);
   return isset($mappings[$key]) ? $mappings[$key] : '';
}


/**
 * Сохраняет сопоставление полей формы при сохранении CF7
 *
 * @param WPCF7_ContactForm $contact_form Объект формы CF7
 * @return void
 */
add_action('wpcf7_save_contact_form', function ($contact_form) {
   if (isset($_POST['cw_field_mapping']) && is_array($_POST['cw_field_mapping'])) {
      $cleaned = array_map('sanitize_text_field', $_POST['cw_field_mapping']);
      update_post_meta($contact_form->id(), '_cw_field_mapping', $cleaned);
   }
});




add_action('wpcf7_mail_sent', function ($contact_form) {
   $submission = WPCF7_Submission::get_instance();
   if (!$submission) return;

   $data = $submission->get_posted_data();
   $form_id = $contact_form->id();
   $form_title = get_the_title($form_id);

   $field_map = get_post_meta($form_id, '_cw_field_mapping', true);
   if (!is_array($field_map)) $field_map = [];

   $first_name  = trim($data[$field_map['first_name']] ?? '');
   $last_name   = trim($data[$field_map['last_name']] ?? '');
   $middle_name = trim($data[$field_map['middle_name']] ?? '');
   $phone       = trim($data[$field_map['phone']] ?? '');
   $email       = trim($data[$field_map['email']] ?? '');

   $ip_address  = $_SERVER['REMOTE_ADDR'] ?? '';
   $user_agent  = $_SERVER['HTTP_USER_AGENT'] ?? '';
   $timestamp   = current_time('mysql');
   $page_url    = $submission->get_meta('url');

   $phone_digits = preg_replace('/\D+/', '', $phone);

   if (empty($email) && !empty($phone_digits)) {
      $site_url = parse_url(home_url(), PHP_URL_HOST);
      $email = $phone_digits . '@' . $site_url;
   }

   // Сохраняем подписчика в CPT
   $subscriber_id = save_subscriber_to_cpt($email, $phone, 0); // user_id = 0
   if (!$subscriber_id) {
      error_log('Failed to save subscriber to CPT');
      return;
   }

   $session_id = uniqid('cf7_', true);
   $consents_to_save = [];

   // Получаем ID документов
   $privacy_page_id    = (int) get_option('wp_page_for_privacy_policy');
   $processing_doc_id  = (int) get_post_meta($form_id, '_legal_consent_doc', true);
   $mailing_doc_id     = (int) get_post_meta($form_id, '_mailing_consent_doc', true);

   // Получаем поля acceptance
   $privacy_field    = $field_map['checkboxprivacy'] ?? '';
   $processing_field = $field_map['checkboxconsent'] ?? '';
   $mailing_field    = $field_map['checkboxnewsletter'] ?? '';

   // Подготавливаем данные для согласий
   if (!empty($data[$privacy_field]) && $privacy_page_id && get_post_status($privacy_page_id) === 'publish') {
      $checkboxtext = get_acceptance_label_html($privacy_field, $form_id);

      $consents_to_save[] = [
         'type'        => 'privacy_policy',
         'document_title' => get_the_title($privacy_page_id),
         'document_url' => get_permalink($privacy_page_id),
         'revision'    => get_latest_revision_link($privacy_page_id),
         'ip'          => $ip_address,
         'user_agent'  => $user_agent,
         'date'        => $timestamp,
         'page_url'    => $page_url,
         'session_id'  => $session_id,
         'form_title'  => $form_title,
         'phone'       => $phone,
         'acceptance_html' => $checkboxtext,
      ];
   }

   if (!empty($data[$mailing_field]) && $mailing_doc_id && get_post_status($mailing_doc_id) === 'publish') {
      $checkboxtext = get_acceptance_label_html($mailing_field, $form_id);

      $consents_to_save[] = [
         'type'        => 'mailing_consent',
         'document_title' => get_the_title($mailing_doc_id),
         'document_url' => get_permalink($mailing_doc_id),
         'revision'    => get_latest_revision_link($mailing_doc_id),
         'ip'          => $ip_address,
         'user_agent'  => $user_agent,
         'date'        => $timestamp,
         'page_url'    => $page_url,
         'session_id'  => $session_id,
         'form_title'  => $form_title,
         'phone'       => $phone,
         'acceptance_html' => $checkboxtext,
      ];
   }

   if (!empty($data[$processing_field]) && $processing_doc_id && get_post_status($processing_doc_id) === 'publish') {
      $checkboxtext = get_acceptance_label_html($processing_field, $form_id);

      $consents_to_save[] = [
         'type'        => 'pdn_processing',
         'document_title' => get_the_title($processing_doc_id),
         'document_url' => get_permalink($processing_doc_id),
         'revision'    => get_latest_revision_link($processing_doc_id),
         'ip'          => $ip_address,
         'user_agent'  => $user_agent,
         'date'        => $timestamp,
         'page_url'    => $page_url,
         'session_id'  => $session_id,
         'form_title'  => $form_title,
         'phone'       => $phone,
         'acceptance_html' => $checkboxtext,
      ];
   }

   // Сохраняем согласия в CPT
   if (!empty($consents_to_save)) {
      save_consents_to_cpt($subscriber_id, $consents_to_save);
      error_log('Successfully saved ' . count($consents_to_save) . ' consents to CPT');
   } else {
      error_log('No consents to save');
   }
});


/**
 * Извлекает HTML-текст согласия по имени acceptance-поля из формы Contact Form 7
 *
 * @param string $acceptance_name — имя поля (например, 'acceptance-1')
 * @param int $form_id — ID формы CF7
 * @return string|null — HTML текста согласия или null, если не найден
 */
function get_acceptance_label_html($acceptance_name, $form_id)
{
   if (!function_exists('do_shortcode')) return null;

   $form_post = get_post($form_id);
   if (!$form_post || $form_post->post_type !== 'wpcf7_contact_form') return null;

   $content = $form_post->post_content;

   // Ищем блок с нужным acceptance
   $pattern = '/\[acceptance[^\]]*?' . preg_quote($acceptance_name, '/') . '[^\]]*\].*?<label[^>]*>(.*?)<\/label>/is';

   if (preg_match($pattern, $content, $matches)) {
      $raw_label_html = $matches[1];

      // Обрабатываем шорткоды внутри текста
      $processed = do_shortcode($raw_label_html);

      return $processed;
   }

   return null;
}


/**
 * Получает ссылку на последнюю ревизию документа
 */
function get_latest_revision_link($post_id)
{
   if (!$post_id) {
      return '';
   }

   $revisions = wp_get_post_revisions($post_id, [
      'numberposts' => 1,
      'order' => 'DESC'
   ]);

   if (!empty($revisions)) {
      $last_revision = reset($revisions);
      $rev_id = $last_revision->ID;
      $rev_date = date_i18n('Y-m-d H:i', strtotime($last_revision->post_modified));
      $link = admin_url('revision.php?revision=' . $rev_id);

      return sprintf(
         '<a href="%s" target="_blank">%s</a>',
         esc_url($link),
         sprintf(__('Version from %s', 'codeweber'), esc_html($rev_date))
      );
   } else {
      $post = get_post($post_id);
      if ($post) {
         $post_date = date_i18n('Y-m-d H:i', strtotime($post->post_modified));
         $link = get_permalink($post_id);

         return sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url($link),
            sprintf(__('Current version from %s', 'codeweber'), esc_html($post_date))
         );
      }
   }

   return '';
}
