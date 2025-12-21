<?php
/**
 * CodeWeber Forms CF7 Integration
 * 
 * Интеграция Contact Form 7 с системой codeweber-forms
 * Сохраняет все отправки CF7 форм в базу данных codeweber-forms
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsCF7Integration {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Перехватываем успешную отправку CF7
        add_action('wpcf7_mail_sent', [$this, 'save_cf7_submission'], 10, 1);
    }
    
    /**
     * Сохраняет отправку CF7 формы в базу данных codeweber-forms
     * 
     * @param WPCF7_ContactForm $contact_form Объект формы CF7
     */
    public function save_cf7_submission($contact_form) {
        // Проверяем, что класс базы данных доступен
        if (!class_exists('CodeweberFormsDatabase')) {
            return;
        }
        
        // Получаем объект submission
        $submission = WPCF7_Submission::get_instance();
        if (!$submission) {
            return;
        }
        
        // Получаем ID формы
        $cf7_form_id = $contact_form->id();
        
        // Получаем название формы
        $form_name = $contact_form->title();
        
        // Получаем данные формы
        $posted_data = $submission->get_posted_data();
        
        
        // Получаем загруженные файлы
        $uploaded_files = $submission->uploaded_files();
        
        // Получаем IP адрес
        $ip_address = $this->get_client_ip();
        
        // Получаем User Agent
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Получаем ID пользователя (авторизованного пользователя, который отправил форму)
        // ПРИОРИТЕТ 1: Пробуем получить из $_POST (может быть передан через скрытое поле)
        $user_id = 0;
        if (!empty($_POST['user_id']) && (int) $_POST['user_id'] > 0) {
            $user_id = (int) $_POST['user_id'];
        }
        // ПРИОРИТЕТ 2: Пробуем получить из posted_data (может быть передан через поле формы)
        elseif (!empty($posted_data['user_id']) && (int) $posted_data['user_id'] > 0) {
            $user_id = (int) $posted_data['user_id'];
        }
        // ПРИОРИТЕТ 3: Восстанавливаем пользователя из cookie (для AJAX запросов)
        else {
            // При AJAX запросах CF7 WordPress может не видеть авторизованного пользователя
            // Пробуем восстановить сессию через валидацию cookie
            $cookie_name = LOGGED_IN_COOKIE;
            
            
            if (!empty($_COOKIE[$cookie_name])) {
                $cookie_value = $_COOKIE[$cookie_name];
                
                // Пробуем парсить cookie вручную (более надежный способ для AJAX)
                $cookie_parts = explode('|', $cookie_value);
                
                
                if (!empty($cookie_parts[0])) {
                    $cookie_username = $cookie_parts[0];
                    
                    
                    // В WordPress cookie формат: username|expiration|token|hmac
                    // Первая часть - это логин, а не user_id
                    // Ищем пользователя по логину
                    $user = get_user_by('login', $cookie_username);
                    if (!$user) {
                        // Если не нашли по логину, пробуем по email
                        $user = get_user_by('email', $cookie_username);
                    }
                    
                    if ($user && $user->ID > 0) {
                        // Восстанавливаем текущего пользователя
                        wp_set_current_user($user->ID);
                        $user_id = get_current_user_id();
                    }
                }
                
                // Если парсинг не сработал, пробуем валидацию через WordPress функцию
                if ($user_id === 0) {
                    $cookie_user = wp_validate_logged_in_cookie($cookie_value);
                    if ($cookie_user && $cookie_user > 0) {
                        // Восстанавливаем текущего пользователя
                        wp_set_current_user($cookie_user);
                        $user_id = get_current_user_id();
                    }
                }
            }
            
            // ПРИОРИТЕТ 4: Стандартный способ (может не работать при AJAX)
            if ($user_id === 0) {
                $user_id = get_current_user_id();
            }
        }
        
        
        // Подготавливаем данные для сохранения
        $submission_data = $this->prepare_submission_data($posted_data);
        
        // Подготавливаем данные файлов
        $files_data = $this->prepare_files_data($uploaded_files);
        
        // Проверяем, был ли email отправлен успешно
        $email_sent = $submission->get_status() === 'mail_sent' ? 1 : 0;
        $email_error = $email_sent ? null : $submission->get_response();
        
        // Сохраняем в базу данных
        $db = new CodeweberFormsDatabase();
        
        $save_data = [
            'form_id' => 'cf7_' . $cf7_form_id, // Префикс для идентификации CF7 форм
            'form_name' => $form_name,
            'form_type' => 'cf7',
            'submission_data' => $submission_data,
            'files_data' => !empty($files_data) ? $files_data : null,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'user_id' => $user_id,
            'status' => 'new',
            'email_sent' => $email_sent,
            'email_error' => $email_error,
            'auto_reply_sent' => 0, // CF7 обрабатывает auto-reply самостоятельно
            'auto_reply_error' => null,
        ];
        
        $submission_id = $db->save_submission($save_data);
        
        // Вызываем хуки codeweber-forms после сохранения
        // ВАЖНО: Обернуто в try-catch, чтобы ошибки в хуках не блокировали отправку CF7
        if ($submission_id && class_exists('CodeweberFormsHooks')) {
            try {
                $form_id = 'cf7_' . $cf7_form_id;
                
                // Подготавливаем данные формы для хуков (аналогично codeweber-forms-api.php)
                $form_data = $submission_data; // Используем уже подготовленные данные
                
                // Передаем user_id в form_data для использования в newsletter integration
                if ($user_id > 0) {
                    $form_data['_user_id'] = $user_id;
                }
                
                // Вызываем хук после сохранения
                // Параметры: submission_id, form_id, form_data (поля формы)
                CodeweberFormsHooks::after_saved($submission_id, $form_id, $form_data);
                
                // Подготавливаем настройки формы для хука after_send
                $form_settings = $this->get_form_settings_for_cf7($contact_form);
                
                // Вызываем хук после отправки
                // Параметры: form_id, form_settings, submission_id
                CodeweberFormsHooks::after_send($form_id, $form_settings, $submission_id);
            } catch (Exception $e) {
                // Игнорируем ошибки в хуках, чтобы не блокировать отправку CF7
            } catch (Error $e) {
                // Игнорируем фатальные ошибки в хуках, чтобы не блокировать отправку CF7
            }
        }
        
        return $submission_id;
    }
    
    /**
     * Подготавливает данные формы для сохранения
     * 
     * @param array $posted_data Данные формы из CF7
     * @return array Подготовленные данные
     */
    private function prepare_submission_data($posted_data) {
        
        $prepared = [];
        $form_consents = []; // Собираем согласия в универсальном формате
        
        // Фильтруем служебные поля CF7
        $excluded_fields = ['_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag', '_wpcf7_container_post'];
        
        foreach ($posted_data as $key => $value) {
            // Пропускаем служебные поля
            if (in_array($key, $excluded_fields) || strpos($key, '_') === 0) {
                continue;
            }
            
            // Преобразуем согласия CF7 в form_consents[ID]
            // ПРИОРИТЕТ 1: Новый формат form_consents_ID (прямой формат, не требует преобразования)
            if (strpos($key, 'form_consents_') === 0) {
                $match = preg_match('/form_consents_(\d+)/', $key, $matches);
                if ($match && isset($matches[1])) {
                    $document_id = intval($matches[1]);
                    
                    // CF7 может передавать acceptance поля как массивы или строки
                    // Обрабатываем оба случая
                    $consent_value = null;
                    if (is_array($value)) {
                        // Если массив, берем первое значение или проверяем наличие '1'
                        $consent_value = isset($value[0]) ? $value[0] : (in_array('1', $value) ? '1' : null);
                    } else {
                        $consent_value = $value;
                    }
                    
                    
                    if ($consent_value === '1' || $consent_value === 'on' || $consent_value === 1 || (is_array($value) && !empty($value))) {
                        $form_consents[$document_id] = '1';
                    }
                }
                // Не добавляем исходное поле в prepared, так как оно преобразовано
                continue;
            }
            
            // ПРИОРИТЕТ 2: Старый формат soglasie-{document_slug} (обратная совместимость)
            if (strpos($key, 'soglasie-') === 0) {
                // Извлекаем slug документа из имени поля
                $document_slug = substr($key, 9); // Убираем префикс "soglasie-"
                
                // Ищем документ по post_name (slug) в CPT legal
                $document = null;
                $documents = get_posts([
                    'post_type' => 'legal',
                    'post_status' => 'publish',
                    'name' => $document_slug, // Ищем по post_name (slug)
                    'posts_per_page' => 1,
                ]);
                
                if (!empty($documents)) {
                    $document = $documents[0];
                } else {
                    // Если не нашли по post_name, пробуем найти по sanitized title
                    $documents = get_posts([
                        'post_type' => 'legal',
                        'post_status' => 'publish',
                        'posts_per_page' => -1,
                    ]);
                    
                    foreach ($documents as $doc) {
                        $doc_slug = $doc->post_name ?: sanitize_title($doc->post_title);
                        if ($doc_slug === $document_slug) {
                            $document = $doc;
                            break;
                        }
                    }
                }
                
                // Если документ найден и согласие дано
                if ($document && ($value === '1' || $value === 'on' || $value === 1)) {
                    $document_id = $document->ID;
                    $form_consents[$document_id] = '1';
                }
                
                // Не добавляем исходное поле в prepared, так как оно преобразовано
                continue;
            }
            
            // ПРИОРИТЕТ 3: Формат form_consents[ID] (с квадратными скобками, обратная совместимость)
            if (preg_match('/^form_consents\[(\d+)\]$/', $key, $matches)) {
                $document_id = intval($matches[1]);
                if ($value === '1' || $value === 'on' || $value === 1) {
                    $form_consents[$document_id] = '1';
                }
                // Не добавляем исходное поле в prepared, так как оно преобразовано
                continue;
            }
            
            // Очищаем данные
            if (is_array($value)) {
                $prepared[$key] = array_map('sanitize_text_field', $value);
            } else {
                $prepared[$key] = sanitize_text_field($value);
            }
        }
        
        // Добавляем преобразованные согласия в подготовленные данные
        if (!empty($form_consents)) {
            $prepared['form_consents'] = $form_consents;
        }
        
        // Обрабатываем UTM данные, если они переданы через скрытое поле из JavaScript
        $utm_data_from_form = null;
        
        if (isset($posted_data['_utm_data']) && !empty($posted_data['_utm_data'])) {
            // UTM данные переданы через скрытое поле (JSON строка)
            $utm_data_json = is_array($posted_data['_utm_data']) 
                ? $posted_data['_utm_data'][0] 
                : $posted_data['_utm_data'];
            
            $utm_data_from_form = json_decode($utm_data_json, true);
            
            if (!is_array($utm_data_from_form) || empty($utm_data_from_form)) {
                $utm_data_from_form = null; // Invalid JSON, will fallback to server-side collection
            }
        }
        
        // Собираем UTM данные аналогично Codeweber Forms API
        if (class_exists('CodeweberFormsUTM')) {
            $utm_tracker = new CodeweberFormsUTM();
            
            // Получаем UTM параметры отдельно (как в Codeweber Forms API)
            $utm_params = $utm_tracker->get_utm_params();
            
            // Получаем tracking данные (referrer, landing_page)
            $tracking_data = $utm_tracker->get_tracking_data();
            
            // Извлекаем UTM параметры из URL referrer и landing_page, если они там есть
            $utm_keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id'];
            
            // Обрабатываем referrer
            $referrer = $tracking_data['referrer'] ?? '';
            $referrer_clean = $referrer;
            if (!empty($referrer) && filter_var($referrer, FILTER_VALIDATE_URL)) {
                $referrer_parsed = parse_url($referrer);
                if (!empty($referrer_parsed['query'])) {
                    parse_str($referrer_parsed['query'], $referrer_params);
                    // Извлекаем UTM параметры из referrer
                    foreach ($utm_keys as $utm_key) {
                        if (isset($referrer_params[$utm_key]) && !empty($referrer_params[$utm_key])) {
                            // Если UTM параметр еще не установлен (ни в серверных данных, ни в данных из формы), используем из referrer
                            $has_utm_param = !empty($utm_params[$utm_key]) || 
                                           ($utm_data_from_form && !empty($utm_data_from_form[$utm_key]));
                            if (!$has_utm_param) {
                                $utm_params[$utm_key] = sanitize_text_field($referrer_params[$utm_key]);
                            }
                        }
                    }
                    // Удаляем UTM параметры из query string для чистого referrer
                    foreach ($utm_keys as $utm_key) {
                        unset($referrer_params[$utm_key]);
                    }
                    // Пересобираем URL без UTM параметров
                    $referrer_clean = $referrer_parsed['scheme'] . '://' . $referrer_parsed['host'];
                    if (!empty($referrer_parsed['port'])) {
                        $referrer_clean .= ':' . $referrer_parsed['port'];
                    }
                    if (!empty($referrer_parsed['path'])) {
                        $referrer_clean .= $referrer_parsed['path'];
                    }
                    if (!empty($referrer_params)) {
                        $referrer_clean .= '?' . http_build_query($referrer_params);
                    }
                    if (!empty($referrer_parsed['fragment'])) {
                        $referrer_clean .= '#' . $referrer_parsed['fragment'];
                    }
                }
            }
            
            // Обрабатываем landing_page
            $landing_page = $tracking_data['landing_page'] ?? '';
            $landing_page_clean = $landing_page;
            if (!empty($landing_page) && filter_var($landing_page, FILTER_VALIDATE_URL)) {
                $landing_parsed = parse_url($landing_page);
                if (!empty($landing_parsed['query'])) {
                    parse_str($landing_parsed['query'], $landing_params);
                    // Извлекаем UTM параметры из landing_page
                    foreach ($utm_keys as $utm_key) {
                        if (isset($landing_params[$utm_key]) && !empty($landing_params[$utm_key])) {
                            // Если UTM параметр еще не установлен (ни в серверных данных, ни в данных из формы), используем из landing_page
                            $has_utm_param = !empty($utm_params[$utm_key]) || 
                                           ($utm_data_from_form && !empty($utm_data_from_form[$utm_key]));
                            if (!$has_utm_param) {
                                $utm_params[$utm_key] = sanitize_text_field($landing_params[$utm_key]);
                            }
                        }
                    }
                    // Удаляем UTM параметры из query string для чистого landing_page
                    foreach ($utm_keys as $utm_key) {
                        unset($landing_params[$utm_key]);
                    }
                    // Пересобираем URL без UTM параметров
                    $landing_page_clean = $landing_parsed['scheme'] . '://' . $landing_parsed['host'];
                    if (!empty($landing_parsed['port'])) {
                        $landing_page_clean .= ':' . $landing_parsed['port'];
                    }
                    if (!empty($landing_parsed['path'])) {
                        $landing_page_clean .= $landing_parsed['path'];
                    }
                    if (!empty($landing_params)) {
                        $landing_page_clean .= '?' . http_build_query($landing_params);
                    }
                    if (!empty($landing_parsed['fragment'])) {
                        $landing_page_clean .= '#' . $landing_parsed['fragment'];
                    }
                }
            }
            
            // Объединяем все UTM данные (аналогично Codeweber Forms API)
            // Приоритет: данные из формы > извлеченные из URL > серверные данные
            $utm_data = array_merge(
                $utm_params,
                $utm_data_from_form ?: []
            );
            
            // Добавляем tracking данные с очищенными URL
            // Используем данные из формы, если они есть, иначе очищенные URL
            if (!isset($utm_data['referrer']) || empty($utm_data['referrer'])) {
                if (!empty($referrer_clean)) {
                    $utm_data['referrer'] = $referrer_clean;
                }
            }
            if (!isset($utm_data['landing_page']) || empty($utm_data['landing_page'])) {
                if (!empty($landing_page_clean)) {
                    $utm_data['landing_page'] = $landing_page_clean;
                }
            }
            
            // Добавляем UTM данные в подготовленные данные для сохранения
            if (!empty($utm_data)) {
                $prepared['_utm_data'] = $utm_data;
            }
        }
        
        return $prepared;
    }
    
    /**
     * Подготавливает данные файлов для сохранения
     * 
     * @param array $uploaded_files Массив загруженных файлов из CF7
     * @return array|null Подготовленные данные файлов или null
     */
    private function prepare_files_data($uploaded_files) {
        if (empty($uploaded_files) || !is_array($uploaded_files)) {
            return null;
        }
        
        $files_data = [];
        $upload_dir = wp_upload_dir();
        
        foreach ($uploaded_files as $field_name => $file_paths) {
            // CF7 может хранить как один путь, так и массив путей
            $paths = is_array($file_paths) ? $file_paths : [$file_paths];
            
            $field_files = [];
            
            foreach ($paths as $file_path) {
                if (empty($file_path) || !file_exists($file_path)) {
                    continue;
                }
                
                // Получаем информацию о файле
                $file_name = basename($file_path);
                $file_size = filesize($file_path);
                $file_type = wp_check_filetype($file_name)['type'];
                
                // Определяем URL файла
                // CF7 хранит файлы во временной папке, нужно скопировать в постоянную
                $copy_result = $this->copy_cf7_file_to_permanent($file_path, $file_name);
                
                // Если удалось скопировать, используем новый путь
                if ($copy_result && isset($copy_result['file_path']) && isset($copy_result['file_url'])) {
                    $permanent_path = $copy_result['file_path'];
                    $file_url = $copy_result['file_url'];
                } else {
                    // Если не удалось скопировать, используем временный путь
                    // (файл может быть удален CF7 позже, но мы хотя бы сохраним информацию)
                    $permanent_path = $file_path;
                    if (strpos($file_path, $upload_dir['basedir']) !== false) {
                        $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);
                    } else {
                        $file_url = '';
                    }
                }
                
                $field_files[] = [
                    'name' => $file_name,
                    'file_name' => $file_name,
                    'file_path' => $permanent_path,
                    'file_url' => $file_url,
                    'file_size' => $file_size,
                    'size' => $file_size,
                    'file_type' => $file_type,
                    'type' => $file_type,
                ];
            }
            
            if (!empty($field_files)) {
                $files_data[$field_name] = $field_files;
            }
        }
        
        return !empty($files_data) ? $files_data : null;
    }
    
    /**
     * Копирует файл из временной папки CF7 в постоянную папку codeweber-forms
     * 
     * @param string $temp_path Временный путь к файлу
     * @param string $file_name Имя файла
     * @return array|false Массив с file_path и file_url или false при ошибке
     */
    private function copy_cf7_file_to_permanent($temp_path, $file_name) {
        $upload_dir = wp_upload_dir();
        
        // Проверяем, что исходный файл существует
        if (!file_exists($temp_path) || !is_readable($temp_path)) {
            return false;
        }
        
        // Создаем структуру папок: codeweber-forms/YYYY/MM/
        $year = date('Y');
        $month = date('m');
        $cf7_forms_dir = $upload_dir['basedir'] . '/codeweber-forms/' . $year . '/' . $month;
        
        // Создаем директорию, если не существует
        if (!file_exists($cf7_forms_dir)) {
            wp_mkdir_p($cf7_forms_dir);
        }
        
        // Проверяем, что директория создана и доступна для записи
        if (!is_dir($cf7_forms_dir) || !is_writable($cf7_forms_dir)) {
            return false;
        }
        
        // Генерируем уникальное имя файла
        $file_info = pathinfo($file_name);
        $base_name = $file_info['filename'];
        $extension = isset($file_info['extension']) ? '.' . $file_info['extension'] : '';
        $unique_name = $base_name . '_' . time() . '_' . wp_generate_password(8, false) . $extension;
        $new_path = $cf7_forms_dir . '/' . $unique_name;
        
        // Копируем файл
        if (@copy($temp_path, $new_path)) {
            // Проверяем, что файл скопирован
            if (file_exists($new_path)) {
                // Получаем URL
                $new_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $new_path);
                // Нормализуем путь для Windows/Unix совместимости
                $new_path = wp_normalize_path($new_path);
                return [
                    'file_path' => $new_path,
                    'file_url' => $new_url,
                ];
            }
        }
        
        return false;
    }
    
    /**
     * Получает IP адрес клиента
     * 
     * @return string IP адрес
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    /**
     * Получает настройки формы CF7 для передачи в хуки codeweber-forms
     * 
     * @param WPCF7_ContactForm $contact_form Объект формы CF7
     * @return array Настройки формы
     */
    private function get_form_settings_for_cf7($contact_form) {
        // Получаем настройки по умолчанию из codeweber-forms
        $default_options = get_option('codeweber_forms_options', []);
        
        // Получаем свойства формы CF7
        $properties = $contact_form->get_properties();
        
        // Получаем email получателя из настроек CF7
        $mail = $properties['mail'] ?? [];
        $recipient_email = !empty($mail['recipient']) ? $mail['recipient'] : ($default_options['default_recipient_email'] ?? get_option('admin_email'));
        
        // Получаем email отправителя
        $sender_email = !empty($mail['sender']) ? $mail['sender'] : ($default_options['default_sender_email'] ?? get_option('admin_email'));
        
        // Получаем имя отправителя
        $sender_name = !empty($mail['sender']) ? $mail['sender'] : ($default_options['default_sender_name'] ?? get_bloginfo('name'));
        
        // Получаем тему письма
        $subject = !empty($mail['subject']) ? $mail['subject'] : ($default_options['default_subject'] ?? __('New Form Submission', 'codeweber'));
        
        // Получаем сообщение об успехе из настроек CF7 или используем дефолтное
        $messages = $properties['messages'] ?? [];
        $success_message = !empty($messages['mail_sent_ok']) 
            ? $messages['mail_sent_ok'] 
            : ($default_options['success_message'] ?? __('Thank you! Your message has been sent.', 'codeweber'));
        
        // Получаем сообщение об ошибке
        $error_message = !empty($messages['mail_sent_ng']) 
            ? $messages['mail_sent_ng'] 
            : ($default_options['error_message'] ?? __('An error occurred. Please try again.', 'codeweber'));
        
        return [
            'formTitle' => $contact_form->title(),
            'recipientEmail' => $recipient_email,
            'senderEmail' => $sender_email,
            'senderName' => $sender_name,
            'subject' => $subject,
            'successMessage' => $success_message,
            'errorMessage' => $error_message,
        ];
    }
}

// Инициализация только если CF7 активен
if (class_exists('WPCF7')) {
    new CodeweberFormsCF7Integration();
    
}

