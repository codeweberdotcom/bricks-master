<?php
/**
 * Codeweber Forms Data Provider
 * 
 * Провайдер для данных из модуля Codeweber Forms (Gutenberg блоки)
 * Получает данные из таблицы wp_codeweber_forms_submissions
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../class-data-provider-interface.php';

class Codeweber_Forms_Data_Provider implements Personal_Data_Provider_Interface {
    
    private $table_name;
    
    /**
     * Конструктор
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'codeweber_forms_submissions';
    }
    
    /**
     * Получить идентификатор провайдера
     * 
     * @return string
     */
    public function get_provider_id(): string {
        return 'codeweber-forms';
    }
    
    /**
     * Получить название провайдера
     * 
     * @return string
     */
    public function get_provider_name(): string {
        return __('Codeweber Forms', 'codeweber');
    }
    
    /**
     * Получить описание провайдера
     * 
     * @return string
     */
    public function get_provider_description(): string {
        return __('Personal data from Codeweber Forms submissions', 'codeweber');
    }
    
    /**
     * Получить персональные данные
     * 
     * @param string $email Email адрес
     * @param int $page Номер страницы
     * @return array
     */
    public function get_personal_data(string $email, int $page = 1): array {
        global $wpdb;
        
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return ['data' => [], 'done' => true];
        }
        
        // Ищем отправки, где в submission_data есть этот email
        // submission_data хранится как JSON
        $submissions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE submission_data LIKE %s 
             ORDER BY created_at DESC",
            '%' . $wpdb->esc_like($email) . '%'
        ));
        
        if (empty($submissions)) {
            return ['data' => [], 'done' => true];
        }
        
        $export_items = [];
        $all_attached_files = []; // Собираем все файлы для отдельного раздела
        
        foreach ($submissions as $submission) {
            // Парсим JSON данные
            $submission_data = json_decode($submission->submission_data, true);
            if (!is_array($submission_data)) {
                continue;
            }
            
            // Проверяем, что email действительно есть в данных
            $email_found = false;
            foreach ($submission_data as $value) {
                if (is_string($value) && strtolower($value) === strtolower($email)) {
                    $email_found = true;
                    break;
                }
            }
            
            if (!$email_found) {
                continue;
            }
            
            $group_id = 'codeweber-forms-submission';
            $group_label = __('Codeweber Forms Submission', 'codeweber');
            
            $data = [];
            
            // Название формы (с поддержкой перевода)
            if (!empty($submission->form_id)) {
                $form_title = get_the_title($submission->form_id);
                $form_name = $form_title ? sprintf('%s (ID: %s)', $form_title, $submission->form_id) : '';
                
                if (!empty($form_name)) {
                    // Пытаемся найти перевод по названию формы
                    $translated_form_title = __($form_title, 'codeweber');
                    if (!empty($translated_form_title) && $translated_form_title !== $form_title) {
                        $form_title = $translated_form_title;
                        $form_name = sprintf('%s (ID: %s)', $translated_form_title, $submission->form_id);
                    }
                    
                    // Добавляем ссылку на редактирование формы
                    $form_edit_url = admin_url('post.php?post=' . $submission->form_id . '&action=edit');
                    $form_name_with_link = sprintf(
                        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                        esc_url($form_edit_url),
                        esc_html($form_name)
                    );
                    
                    $data[] = [
                        'name'  => __('Form Name', 'codeweber'),
                        'value' => $form_name_with_link,
                    ];
                }
            }
            
            // ID формы
            if (!empty($submission->form_id)) {
                $data[] = [
                    'name' => __('Form ID', 'codeweber'),
                    'value' => (string)$submission->form_id
                ];
            }
            
            // Все поля формы
            foreach ($submission_data as $field_name => $field_value) {
                if (empty($field_value)) {
                    continue;
                }
                
                // Пропускаем системные поля, они обрабатываются отдельно
                if (in_array($field_name, ['newsletter_consents', '_utm_data', 'form_name', 'Form Name', 'formName'])) {
                    continue;
                }
                
                // Пропускаем поля, которые дублируют уже выведенную информацию
                $field_lower = strtolower($field_name);
                if (in_array($field_lower, ['form_name', 'formname', 'form name'])) {
                    continue;
                }
                
                // Специальная обработка полей с файлами (File[], file[] и т.д.)
                $is_file_field = false;
                if (preg_match('/^(.+)\[\]$/i', $field_name, $matches)) {
                    $is_file_field = true;
                    $field_name_without_brackets = $matches[1];
                }
                
                // Проверяем, является ли значение GUID файлов (строка с GUID через точку с запятой)
                $is_guid_value = false;
                if (is_string($field_value)) {
                    // Проверяем формат GUID (например: "2d57aff7-52a5-4eff-b212-d86a16bc56c0; 29d960cb-068c-4039-b210-0d5fc3cd2d4b")
                    $guid_pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}(;\s*[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})*$/i';
                    if (preg_match($guid_pattern, trim($field_value))) {
                        $is_guid_value = true;
                    }
                }
                
                // Если это поле с файлами (содержит GUID), обрабатываем его специально
                if ($is_file_field || $is_guid_value) {
                    // Получаем данные файлов из files_data
                    $files_data = null;
                    if (!empty($submission->files_data)) {
                        $files_data = json_decode($submission->files_data, true);
                    }
                    
                    // Парсим GUID из значения
                    $guids = [];
                    if (is_string($field_value)) {
                        $guids = array_map('trim', explode(';', $field_value));
                        $guids = array_filter($guids); // Убираем пустые значения
                    } elseif (is_array($field_value)) {
                        $guids = $field_value;
                    }
                    
                    // Находим файлы по GUID и формируем список с именами и ссылками
                    $files_list = [];
                    if (!empty($files_data) && is_array($files_data)) {
                        // Поддержка двух структур: простой массив или объект с ключами по имени поля
                        $files_to_search = [];
                        
                        // Если это простой массив файлов (каждый элемент - объект файла)
                        if (isset($files_data[0]) && is_array($files_data[0]) && isset($files_data[0]['file_id'])) {
                            $files_to_search = $files_data;
                        }
                        // Если это объект с ключами по имени поля
                        else {
                            foreach ($files_data as $field_key => $field_files) {
                                if (is_array($field_files)) {
                                    // Если это массив файлов
                                    if (isset($field_files[0]) && is_array($field_files[0])) {
                                        $files_to_search = array_merge($files_to_search, $field_files);
                                    }
                                    // Если это один файл
                                    elseif (isset($field_files['file_id'])) {
                                        $files_to_search[] = $field_files;
                                    }
                                }
                            }
                        }
                        
                        // Ищем файлы по GUID
                        foreach ($guids as $guid) {
                            foreach ($files_to_search as $file) {
                                if (isset($file['file_id']) && $file['file_id'] === $guid) {
                                    $file_name = $file['file_name'] ?? $guid;
                                    $file_url = $file['file_url'] ?? '';
                                    
                                    if ($file_url) {
                                        $files_list[] = sprintf(
                                            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                                            esc_url($file_url),
                                            esc_html($file_name)
                                        );
                                    } else {
                                        $files_list[] = esc_html($file_name);
                                    }
                                    break; // Найден файл, переходим к следующему GUID
                                }
                            }
                        }
                    }
                    
                    // Если файлы не найдены, показываем GUID как есть (fallback)
                    if (empty($files_list) && !empty($guids)) {
                        foreach ($guids as $guid) {
                            $files_list[] = esc_html($guid);
                        }
                    }
                    
                    // Получаем переведенное название поля (без скобок)
                    $field_label_name = $is_file_field ? $field_name_without_brackets : $field_name;
                    $field_label = $this->get_translated_field_label($field_label_name);
                    
                    $data[] = [
                        'name' => $field_label,
                        'value' => !empty($files_list) ? implode('; ', $files_list) : ''
                    ];
                    
                    continue; // Пропускаем обычную обработку для полей с файлами
                }
                
                // Получаем переведенное название поля
                $field_label = $this->get_translated_field_label($field_name);
                
                // Форматируем значение
                $display_value = $this->format_field_value($field_value);
                
                // Если это email поле, добавляем ссылку на профиль пользователя
                $field_lower = strtolower($field_name);
                if (strpos($field_lower, 'email') !== false && is_email($field_value)) {
                    $user = get_user_by('email', $field_value);
                    if ($user) {
                        $user_profile_url = admin_url('user-edit.php?user_id=' . $user->ID);
                        $display_value = sprintf(
                            '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                            esc_url($user_profile_url),
                            esc_html($display_value)
                        );
                    }
                }
                
                $data[] = [
                    'name' => $field_label,
                    'value' => $display_value
                ];
            }
            
            // Обработка newsletter_consents (специальная обработка)
            // Выводим каждый документ согласия отдельной строкой, в формате как в блоке "Пользовательские согласия"
            if (!empty($submission_data['newsletter_consents']) && is_array($submission_data['newsletter_consents'])) {
                foreach ($submission_data['newsletter_consents'] as $doc_id => $consent_data) {
                    $doc = get_post($doc_id);
                    if ($doc) {
                        $doc_title = $doc->post_title;
                        $doc_url = '';

                        // URL документа: если есть ревизия, используем ссылку на нее, иначе permalink документа
                        if (!empty($consent_data['document_revision_id'])) {
                            $doc_url = admin_url('revision.php?revision=' . $consent_data['document_revision_id']);
                        } else {
                            $doc_url = get_permalink($doc_id);
                        }

                        // Базовая часть: Название (ID: X)
                        $consent_info = sprintf(
                            '%s (ID: %d)',
                            $doc_title,
                            $doc_id
                        );

                        // Информация о ревизии
                        if (!empty($consent_data['document_revision_id'])) {
                            $consent_info .= sprintf(
                                ' - ' . __('Revision ID', 'codeweber') . ': %d',
                                $consent_data['document_revision_id']
                            );
                        }

                        // Информация о версии
                        if (!empty($consent_data['document_version'])) {
                            $consent_info .= ' - ' . __('Version', 'codeweber') . ': ' . $consent_data['document_version'];
                        }

                        // URL (делаем кликабельным)
                        if ($doc_url) {
                            $consent_info .= ' - ' . __('URL', 'codeweber') . ': '
                                . '<a href="' . esc_url($doc_url) . '" target="_blank" rel="noopener noreferrer">'
                                . esc_html($doc_url)
                                . '</a>';
                        }

                        $data[] = [
                            'name'  => __('Consented Document', 'codeweber'),
                            'value' => $consent_info,
                        ];
                    }
                }
            }
            
            // Обработка UTM данных
            if (!empty($submission_data['_utm_data']) && is_array($submission_data['_utm_data'])) {
                $utm_list = [];
                foreach ($submission_data['_utm_data'] as $utm_key => $utm_value) {
                    if (!empty($utm_value)) {
                        $utm_label = $this->get_translated_field_label($utm_key);
                        $utm_list[] = $utm_label . ': ' . $utm_value;
                    }
                }
                
                if (!empty($utm_list)) {
                    $data[] = [
                        'name' => __('UTM Data', 'codeweber'),
                        'value' => implode('; ', $utm_list)
                    ];
                }
            }
            
            // Файлы (если есть) - собираем все файлы из files_data
            $all_files_list = [];
            if (!empty($submission->files_data)) {
                $files_data = json_decode($submission->files_data, true);
                if (is_array($files_data) && !empty($files_data)) {
                    // Поддержка двух структур: простой массив или объект с ключами по имени поля
                    $files_to_process = [];
                    
                    // Если это простой массив файлов (каждый элемент - объект файла)
                    if (isset($files_data[0]) && is_array($files_data[0]) && isset($files_data[0]['file_id'])) {
                        $files_to_process = $files_data;
                    }
                    // Если это объект с ключами по имени поля
                    else {
                        foreach ($files_data as $field_key => $field_files) {
                            if (is_array($field_files)) {
                                // Если это массив файлов
                                if (isset($field_files[0]) && is_array($field_files[0])) {
                                    $files_to_process = array_merge($files_to_process, $field_files);
                                }
                                // Если это один файл
                                elseif (isset($field_files['file_id'])) {
                                    $files_to_process[] = $field_files;
                                }
                            }
                        }
                    }
                    
                    // Формируем список файлов с ссылками
                    foreach ($files_to_process as $file) {
                        if (isset($file['file_name']) && isset($file['file_url'])) {
                            $file_name = $file['file_name'];
                            $file_url = $file['file_url'];
                            
                            $all_files_list[] = sprintf(
                                '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                                esc_url($file_url),
                                esc_html($file_name)
                            );
                        } elseif (isset($file['file_name'])) {
                            $all_files_list[] = esc_html($file['file_name']);
                        }
                    }
                }
            }
            
            // Добавляем строку "Файлы" в раздел формы, если есть файлы
            if (!empty($all_files_list)) {
                $data[] = [
                    'name' => __('Files', 'codeweber'),
                    'value' => implode('; ', $all_files_list)
                ];
                
                // Сохраняем файлы для отдельного раздела
                if (!empty($submission->files_data)) {
                    $files_data = json_decode($submission->files_data, true);
                    if (is_array($files_data) && !empty($files_data)) {
                        // Поддержка двух структур: простой массив или объект с ключами по имени поля
                        $files_to_process = [];
                        
                        // Если это простой массив файлов (каждый элемент - объект файла)
                        if (isset($files_data[0]) && is_array($files_data[0]) && isset($files_data[0]['file_id'])) {
                            $files_to_process = $files_data;
                        }
                        // Если это объект с ключами по имени поля
                        else {
                            foreach ($files_data as $field_key => $field_files) {
                                if (is_array($field_files)) {
                                    // Если это массив файлов
                                    if (isset($field_files[0]) && is_array($field_files[0])) {
                                        $files_to_process = array_merge($files_to_process, $field_files);
                                    }
                                    // Если это один файл
                                    elseif (isset($field_files['file_id'])) {
                                        $files_to_process[] = $field_files;
                                    }
                                }
                            }
                        }
                        
                        // Добавляем информацию о форме к каждому файлу
                        foreach ($files_to_process as $file) {
                            if (isset($file['file_name']) && isset($file['file_url'])) {
                                $file_info = $file;
                                $file_info['form_name'] = !empty($submission->form_id) ? get_the_title($submission->form_id) : '';
                                $file_info['form_id'] = $submission->form_id;
                                $file_info['submission_id'] = $submission->id;
                                $file_info['submission_date'] = $submission->created_at;
                                $all_attached_files[] = $file_info;
                            }
                        }
                    }
                }
            }
            
            // IP-адрес
            if (!empty($submission->ip_address)) {
                $data[] = [
                    'name' => __('IP Address', 'codeweber'),
                    'value' => $submission->ip_address
                ];
            }
            
            // User Agent
            if (!empty($submission->user_agent)) {
                $data[] = [
                    'name' => __('Browser User Agent', 'codeweber'),
                    'value' => $submission->user_agent
                ];
            }
            
            // Дата отправки
            $data[] = [
                'name' => __('Submission Date', 'codeweber'),
                'value' => date('d.m.Y H:i:s', strtotime($submission->created_at))
            ];
            
            // ID записи
            $record_id_value = (string)$submission->id;
            $submission_view_url = admin_url('admin.php?page=codeweber&action=view&id=' . $submission->id);
            $record_id_with_link = sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                esc_url($submission_view_url),
                esc_html($record_id_value)
            );
            
            $data[] = [
                'name' => __('Record ID', 'codeweber'),
                'value' => $record_id_with_link
            ];
            
            $export_items[] = [
                'group_id' => $group_id,
                'group_label' => $group_label,
                'item_id' => 'codeweber-forms-submission-' . $submission->id,
                'data' => $data,
            ];
        }
        
        // Добавляем отдельный раздел "Прикрепленные к формам файлы", если есть файлы
        if (!empty($all_attached_files)) {
            $files_group_data = [];
            
            foreach ($all_attached_files as $file) {
                $file_item = [];
                
                // Название файла со ссылкой
                if (isset($file['file_url'])) {
                    $file_name_with_link = sprintf(
                        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                        esc_url($file['file_url']),
                        esc_html($file['file_name'])
                    );
                } else {
                    $file_name_with_link = esc_html($file['file_name']);
                }
                
                $file_item[] = [
                    'name' => __('File Name', 'codeweber'),
                    'value' => $file_name_with_link
                ];
                
                // Название формы
                if (!empty($file['form_name'])) {
                    $form_name_display = $file['form_name'];
                    if (!empty($file['form_id'])) {
                        $form_edit_url = admin_url('post.php?post=' . $file['form_id'] . '&action=edit');
                        $form_name_display = sprintf(
                            '<a href="%s" target="_blank" rel="noopener noreferrer">%s (ID: %s)</a>',
                            esc_url($form_edit_url),
                            esc_html($file['form_name']),
                            esc_html($file['form_id'])
                        );
                    }
                    
                    $file_item[] = [
                        'name' => __('Form Name', 'codeweber'),
                        'value' => $form_name_display
                    ];
                }
                
                // Размер файла
                if (isset($file['file_size'])) {
                    $file_size_formatted = size_format($file['file_size'], 2);
                    $file_item[] = [
                        'name' => __('File Size', 'codeweber'),
                        'value' => $file_size_formatted
                    ];
                }
                
                // Тип файла
                if (isset($file['file_type'])) {
                    $file_item[] = [
                        'name' => __('File Type', 'codeweber'),
                        'value' => esc_html($file['file_type'])
                    ];
                }
                
                // Дата отправки формы
                if (!empty($file['submission_date'])) {
                    $file_item[] = [
                        'name' => __('Submission Date', 'codeweber'),
                        'value' => date('d.m.Y H:i:s', strtotime($file['submission_date']))
                    ];
                }
                
                // Ссылка на отправку формы
                if (!empty($file['submission_id'])) {
                    $submission_view_url = admin_url('admin.php?page=codeweber&action=view&id=' . $file['submission_id']);
                    $submission_link = sprintf(
                        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                        esc_url($submission_view_url),
                        esc_html(__('View Submission', 'codeweber'))
                    );
                    
                    $file_item[] = [
                        'name' => __('Submission', 'codeweber'),
                        'value' => $submission_link
                    ];
                }
                
                $export_items[] = [
                    'group_id' => 'codeweber-forms-attached-files',
                    'group_label' => __('Attached Form Files', 'codeweber'),
                    'item_id' => 'attached-file-' . (isset($file['file_id']) ? $file['file_id'] : uniqid()),
                    'data' => $file_item,
                ];
            }
        }
        
        return [
            'data' => $export_items,
            'done' => true
        ];
    }
    
    /**
     * Удалить персональные данные
     * 
     * @param string $email Email адрес
     * @param int $page Номер страницы
     * @return array
     */
    public function erase_personal_data(string $email, int $page = 1): array {
        global $wpdb;
        
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return [
                'items_removed' => false,
                'items_retained' => false,
                'messages' => [__('Invalid email address', 'codeweber')],
                'done' => true
            ];
        }
        
        // Находим отправки с этим email
        $submissions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} 
             WHERE submission_data LIKE %s",
            '%' . $wpdb->esc_like($email) . '%'
        ));
        
        if (empty($submissions)) {
            return [
                'items_removed' => false,
                'items_retained' => false,
                'messages' => [__('No Codeweber Forms data found for this email', 'codeweber')],
                'done' => true
            ];
        }
        
        $items_removed = false;
        
        foreach ($submissions as $submission) {
            // Парсим JSON данные
            $submission_data = json_decode($submission->submission_data, true);
            if (!is_array($submission_data)) {
                continue;
            }
            
            // Анонимизируем email и другие персональные данные
            $anonymized = false;
            foreach ($submission_data as $field_name => $field_value) {
                $field_lower = strtolower($field_name);
                $value_lower = is_string($field_value) ? strtolower($field_value) : '';
                
                // Анонимизируем email поля
                if (strpos($field_lower, 'email') !== false && is_email($field_value)) {
                    $submission_data[$field_name] = 'anonymized@example.com';
                    $anonymized = true;
                }
                
                // Анонимизируем телефон
                if (strpos($field_lower, 'phone') !== false || strpos($field_lower, 'tel') !== false) {
                    $submission_data[$field_name] = __('Anonymized', 'codeweber');
                    $anonymized = true;
                }
                
                // Анонимизируем имя
                if (strpos($field_lower, 'name') !== false && $value_lower === strtolower($email)) {
                    $submission_data[$field_name] = __('Anonymous', 'codeweber');
                    $anonymized = true;
                }
            }
            
            if ($anonymized) {
                // Обновляем данные в БД
                $wpdb->update(
                    $this->table_name,
                    [
                        'submission_data' => json_encode($submission_data, JSON_UNESCAPED_UNICODE),
                        'ip_address' => '0.0.0.0',
                        'user_agent' => 'anonymized',
                        'updated_at' => current_time('mysql')
                    ],
                    ['id' => $submission->id],
                    ['%s', '%s', '%s', '%s'],
                    ['%d']
                );
                
                $items_removed = true;
            }
        }
        
        return [
            'items_removed' => $items_removed,
            'items_retained' => false,
            'messages' => $items_removed ? 
                [__('Codeweber Forms submission data anonymized', 'codeweber')] : 
                [__('No personal data found to anonymize', 'codeweber')],
            'done' => true
        ];
    }
    
    /**
     * Проверить наличие данных
     * 
     * @param string $email Email адрес
     * @return bool
     */
    public function has_personal_data(string $email): bool {
        global $wpdb;
        
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return false;
        }
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} 
             WHERE submission_data LIKE %s 
             LIMIT 1",
            '%' . $wpdb->esc_like($email) . '%'
        ));
        
        return !empty($exists);
    }
    
    /**
     * Получить список полей
     * 
     * @return array
     */
    public function get_personal_data_fields(): array {
        return [
            'form_id' => __('Form ID', 'codeweber'),
            'form_name' => __('Form Name', 'codeweber'),
            'submission_data' => __('Form Fields', 'codeweber'),
            'files_data' => __('Uploaded Files', 'codeweber'),
            'ip_address' => __('IP Address', 'codeweber'),
            'user_agent' => __('Browser User Agent', 'codeweber'),
            'user_id' => __('User ID', 'codeweber'),
            'status' => __('Status', 'codeweber'),
            'created_at' => __('Submission Date', 'codeweber')
        ];
    }
    
    /**
     * Получить переведенное название поля
     * 
     * @param string $field_name Имя поля
     * @return string Переведенное название
     */
    private function get_translated_field_label(string $field_name): string {
        // Проверяем язык сайта для прямого перевода
        $locale = get_locale();
        $is_russian = (strpos($locale, 'ru') === 0);
        
        $translations = [
            'email' => __('Email Address', 'codeweber'),
            'name' => __('Name', 'codeweber'),
            'first_name' => __('First Name', 'codeweber'),
            'last_name' => __('Last Name', 'codeweber'),
            'phone' => __('Phone', 'codeweber'),
            'tel' => __('Phone', 'codeweber'),
            'message' => __('Message', 'codeweber'),
            'subject' => __('Subject', 'codeweber'),
            'company' => __('Company', 'codeweber'),
            'file' => $is_russian ? 'Файл' : __('File', 'codeweber'),
            'utm_source' => __('UTM Source', 'codeweber'),
            'utm_medium' => __('UTM Medium', 'codeweber'),
            'utm_campaign' => __('UTM Campaign', 'codeweber'),
            'utm_term' => __('UTM Term', 'codeweber'),
            'utm_content' => __('UTM Content', 'codeweber'),
            'utm_id' => __('UTM ID', 'codeweber'),
            'referrer' => __('Referrer', 'codeweber'),
            'landing_page' => __('Landing Page', 'codeweber'),
        ];
        
        // Если есть перевод, используем его
        if (isset($translations[$field_name])) {
            return $translations[$field_name];
        }
        
        // Иначе создаем читаемое название из имени поля
        $label = str_replace(['_', '-'], ' ', $field_name);
        $label = ucwords($label);
        
        return $label;
    }
    
    /**
     * Форматировать значение поля для экспорта
     * 
     * @param mixed $value Значение поля
     * @return string Отформатированное значение
     */
    private function format_field_value($value): string {
        if (is_array($value)) {
            // Рекурсивно обрабатываем массивы
            $formatted = [];
            foreach ($value as $key => $item) {
                if (is_array($item)) {
                    // Если это массив массивов, форматируем каждый элемент
                    $item_str = [];
                    foreach ($item as $k => $v) {
                        if (!is_array($v)) {
                            $item_str[] = $k . ': ' . $v;
                        }
                    }
                    $formatted[] = implode(', ', $item_str);
                } else {
                    $formatted[] = $item;
                }
            }
            return implode('; ', $formatted);
        }
        
        return (string)$value;
    }
}


