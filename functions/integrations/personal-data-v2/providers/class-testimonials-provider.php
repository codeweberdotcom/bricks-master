<?php
/**
 * Testimonials Data Provider
 * 
 * Провайдер для данных из формы отзывов
 * Получает данные из CPT 'testimonials'
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../class-data-provider-interface.php';

class Testimonials_Data_Provider implements Personal_Data_Provider_Interface {
    
    /**
     * Получить идентификатор провайдера
     * 
     * @return string
     */
    public function get_provider_id(): string {
        return 'testimonials';
    }
    
    /**
     * Получить название провайдера
     * 
     * @return string
     */
    public function get_provider_name(): string {
        return __('Testimonials', 'codeweber');
    }
    
    /**
     * Получить описание провайдера
     * 
     * @return string
     */
    public function get_provider_description(): string {
        return __('Personal data from testimonial submissions', 'codeweber');
    }
    
    /**
     * Получить персональные данные
     * 
     * @param string $email Email адрес
     * @param int $page Номер страницы
     * @return array
     */
    public function get_personal_data(string $email, int $page = 1): array {
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return ['data' => [], 'done' => true];
        }
        
        // Ищем отзывы по email в meta поле
        $testimonials = get_posts([
            'post_type' => 'testimonials',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_query' => [
                [
                    'key' => '_testimonial_submitted_email',
                    'value' => $email,
                    'compare' => '='
                ]
            ]
        ]);
        
        if (empty($testimonials)) {
            return ['data' => [], 'done' => true];
        }
        
        $export_items = [];
        
        foreach ($testimonials as $testimonial) {
            $group_id = 'testimonial';
            $group_label = __('Testimonial', 'codeweber');
            
            $data = [];
            
            // Email автора
            $author_email = get_post_meta($testimonial->ID, '_testimonial_submitted_email', true);
            if ($author_email) {
                $data[] = [
                    'name' => __('Author Email', 'codeweber'),
                    'value' => $author_email
                ];
            }
            
            // Имя автора
            $author_name = get_post_meta($testimonial->ID, '_testimonial_author_name', true);
            if ($author_name) {
                $data[] = [
                    'name' => __('Author Name', 'codeweber'),
                    'value' => $author_name
                ];
            }
            
            // Должность
            $author_role = get_post_meta($testimonial->ID, '_testimonial_author_role', true);
            if ($author_role) {
                $data[] = [
                    'name' => __('Author Role', 'codeweber'),
                    'value' => $author_role
                ];
            }
            
            // Компания
            $company = get_post_meta($testimonial->ID, '_testimonial_company', true);
            if ($company) {
                $data[] = [
                    'name' => __('Company', 'codeweber'),
                    'value' => $company
                ];
            }
            
            // Текст отзыва
            $testimonial_text = get_post_meta($testimonial->ID, '_testimonial_text', true);
            if ($testimonial_text) {
                $data[] = [
                    'name' => __('Testimonial Text', 'codeweber'),
                    'value' => wp_strip_all_tags($testimonial_text)
                ];
            }
            
            // Рейтинг
            $rating = get_post_meta($testimonial->ID, '_testimonial_rating', true);
            if ($rating) {
                $data[] = [
                    'name' => __('Rating', 'codeweber'),
                    'value' => sprintf(__('%d stars', 'codeweber'), $rating)
                ];
            }
            
            // Тип автора
            $author_type = get_post_meta($testimonial->ID, '_testimonial_author_type', true);
            if ($author_type) {
                $data[] = [
                    'name' => __('Author Type', 'codeweber'),
                    'value' => ucfirst($author_type)
                ];
            }
            
            // User ID (если есть)
            $user_id = get_post_meta($testimonial->ID, '_testimonial_author_user_id', true);
            if ($user_id) {
                $user = get_user_by('id', $user_id);
                if ($user) {
                    $data[] = [
                        'name' => __('Linked User Account', 'codeweber'),
                        'value' => sprintf('%s (ID: %d)', $user->display_name, $user_id)
                    ];
                }
            }
            
            // IP-адрес
            $ip = get_post_meta($testimonial->ID, '_testimonial_submitted_ip', true);
            if ($ip) {
                $data[] = [
                    'name' => __('IP Address', 'codeweber'),
                    'value' => $ip
                ];
            }
            
            // Дата отправки
            $submitted_date = get_post_meta($testimonial->ID, '_testimonial_submitted_date', true);
            if ($submitted_date) {
                $data[] = [
                    'name' => __('Submission Date', 'codeweber'),
                    'value' => date('d.m.Y H:i:s', strtotime($submitted_date))
                ];
            }
            
            // Статус
            $status = get_post_meta($testimonial->ID, '_testimonial_status', true);
            if ($status) {
                $data[] = [
                    'name' => __('Status', 'codeweber'),
                    'value' => ucfirst($status)
                ];
            }
            
            // Дата публикации поста
            $data[] = [
                'name' => __('Post Date', 'codeweber'),
                'value' => get_the_date('d.m.Y H:i:s', $testimonial->ID)
            ];
            
            // Ссылка на отзыв
            $testimonial_url = get_permalink($testimonial->ID);
            if ($testimonial_url) {
                $data[] = [
                    'name' => __('Testimonial URL', 'codeweber'),
                    'value' => $testimonial_url
                ];
            }
            
            // ID записи
            $data[] = [
                'name' => __('Record ID', 'codeweber'),
                'value' => (string)$testimonial->ID
            ];
            
            $export_items[] = [
                'group_id' => $group_id,
                'group_label' => $group_label,
                'item_id' => 'testimonial-' . $testimonial->ID,
                'data' => $data,
            ];
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
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return [
                'items_removed' => false,
                'items_retained' => false,
                'messages' => [__('Invalid email address', 'codeweber')],
                'done' => true
            ];
        }
        
        // Находим отзывы по email
        $testimonials = get_posts([
            'post_type' => 'testimonials',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_query' => [
                [
                    'key' => '_testimonial_submitted_email',
                    'value' => $email,
                    'compare' => '='
                ]
            ]
        ]);
        
        if (empty($testimonials)) {
            return [
                'items_removed' => false,
                'items_retained' => false,
                'messages' => [__('No testimonial data found for this email', 'codeweber')],
                'done' => true
            ];
        }
        
        $items_removed = false;
        
        foreach ($testimonials as $testimonial) {
            // Анонимизируем персональные данные
            update_post_meta($testimonial->ID, '_testimonial_author_name', __('Anonymous', 'codeweber'));
            update_post_meta($testimonial->ID, '_testimonial_submitted_email', 'anonymized@example.com');
            update_post_meta($testimonial->ID, '_testimonial_author_role', '');
            update_post_meta($testimonial->ID, '_testimonial_company', '');
            update_post_meta($testimonial->ID, '_testimonial_submitted_ip', '0.0.0.0');
            
            // Анонимизируем текст отзыва (можно оставить пустым или заменить на стандартный текст)
            $testimonial_text = get_post_meta($testimonial->ID, '_testimonial_text', true);
            if ($testimonial_text) {
                update_post_meta($testimonial->ID, '_testimonial_text', __('[Content anonymized]', 'codeweber'));
            }
            
            $items_removed = true;
        }
        
        return [
            'items_removed' => $items_removed,
            'items_retained' => false,
            'messages' => $items_removed ? 
                [__('Testimonial personal data anonymized', 'codeweber')] : 
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
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return false;
        }
        
        $testimonials = get_posts([
            'post_type' => 'testimonials',
            'posts_per_page' => 1,
            'post_status' => 'any',
            'meta_query' => [
                [
                    'key' => '_testimonial_submitted_email',
                    'value' => $email,
                    'compare' => '='
                ]
            ]
        ]);
        
        return !empty($testimonials);
    }
    
    /**
     * Получить список полей
     * 
     * @return array
     */
    public function get_personal_data_fields(): array {
        return [
            'author_email' => __('Author Email', 'codeweber'),
            'author_name' => __('Author Name', 'codeweber'),
            'author_role' => __('Author Role', 'codeweber'),
            'company' => __('Company', 'codeweber'),
            'testimonial_text' => __('Testimonial Text', 'codeweber'),
            'rating' => __('Rating', 'codeweber'),
            'ip_address' => __('IP Address', 'codeweber'),
            'submission_date' => __('Submission Date', 'codeweber'),
            'status' => __('Status', 'codeweber')
        ];
    }
}


