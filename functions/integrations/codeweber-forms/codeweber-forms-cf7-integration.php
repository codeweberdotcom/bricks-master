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
        
        // Получаем ID пользователя
        $user_id = get_current_user_id();
        
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
        
        // Логируем для отладки (если включен WP_DEBUG)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('CF7 Integration: Saved submission ID ' . $submission_id . ' for form ' . $form_name . ' (CF7 ID: ' . $cf7_form_id . ')');
        }
        
        // Вызываем хуки codeweber-forms после сохранения
        // ВАЖНО: Обернуто в try-catch, чтобы ошибки в хуках не блокировали отправку CF7
        if ($submission_id && class_exists('CodeweberFormsHooks')) {
            try {
                $form_id = 'cf7_' . $cf7_form_id;
                
                // Подготавливаем данные формы для хуков (аналогично codeweber-forms-api.php)
                $form_data = $submission_data; // Используем уже подготовленные данные
                
                // Логируем для отладки (если включен WP_DEBUG)
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('CF7 Integration: Calling codeweber_form_saved hook with submission_id: ' . $submission_id . ', form_id: ' . $form_id);
                    error_log('CF7 Integration: Data passed to hook: ' . print_r($form_data, true));
                }
                
                // Вызываем хук после сохранения
                // Параметры: submission_id, form_id, form_data (поля формы)
                CodeweberFormsHooks::after_saved($submission_id, $form_id, $form_data);
                
                // Подготавливаем настройки формы для хука after_send
                $form_settings = $this->get_form_settings_for_cf7($contact_form);
                
                // Логируем для отладки (если включен WP_DEBUG)
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('CF7 Integration: Calling codeweber_form_after_send hook with form_id: ' . $form_id . ', submission_id: ' . $submission_id);
                }
                
                // Вызываем хук после отправки
                // Параметры: form_id, form_settings, submission_id
                CodeweberFormsHooks::after_send($form_id, $form_settings, $submission_id);
            } catch (Exception $e) {
                // Логируем ошибку, но не блокируем отправку CF7
                error_log('CF7 Integration: Error in codeweber-forms hooks: ' . $e->getMessage());
                error_log('CF7 Integration: Stack trace: ' . $e->getTraceAsString());
            } catch (Error $e) {
                // Логируем фатальную ошибку, но не блокируем отправку CF7
                error_log('CF7 Integration: Fatal error in codeweber-forms hooks: ' . $e->getMessage());
                error_log('CF7 Integration: Stack trace: ' . $e->getTraceAsString());
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
        
        // Фильтруем служебные поля CF7
        $excluded_fields = ['_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag', '_wpcf7_container_post'];
        
        foreach ($posted_data as $key => $value) {
            // Пропускаем служебные поля
            if (in_array($key, $excluded_fields) || strpos($key, '_') === 0) {
                continue;
            }
            
            // Очищаем данные
            if (is_array($value)) {
                $prepared[$key] = array_map('sanitize_text_field', $value);
            } else {
                $prepared[$key] = sanitize_text_field($value);
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
    
    // ТЕСТОВЫЙ ХУК: Для проверки работы нового функционала
    // Удалите этот блок после проверки
    add_action('codeweber_form_saved', function($submission_id, $form_id, $form_data) {
        // Проверяем, что это CF7 форма (начинается с 'cf7_')
        if (strpos($form_id, 'cf7_') === 0) {
            error_log('✅ НОВЫЙ ФУНКЦИОНАЛ РАБОТАЕТ! CF7 форма сохранена через codeweber-forms хуки.');
            error_log('   Submission ID: ' . $submission_id);
            error_log('   Form ID: ' . $form_id);
            error_log('   Form Data keys: ' . implode(', ', array_keys($form_data)));
        }
    }, 5, 3);
    
    add_action('codeweber_form_after_send', function($form_id, $form_settings, $submission_id) {
        // Проверяем, что это CF7 форма
        if (strpos($form_id, 'cf7_') === 0) {
            error_log('✅ НОВЫЙ ФУНКЦИОНАЛ РАБОТАЕТ! CF7 форма обработана через codeweber_form_after_send хук.');
            error_log('   Form ID: ' . $form_id);
            error_log('   Submission ID: ' . $submission_id);
            error_log('   Success Message: ' . ($form_settings['successMessage'] ?? 'N/A'));
        }
    }, 5, 3);
}

