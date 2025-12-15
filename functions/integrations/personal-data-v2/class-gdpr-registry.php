<?php
/**
 * GDPR Registry
 * 
 * Автоматическая регистрация экспортеров и эрасеров для всех провайдеров
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class GDPR_Registry {
    
    /**
     * @var Personal_Data_Manager Менеджер персональных данных
     */
    private $manager;
    
    /**
     * Конструктор
     * 
     * @param Personal_Data_Manager $manager
     */
    public function __construct(Personal_Data_Manager $manager) {
        $this->manager = $manager;
    }
    
    /**
     * Регистрация экспортеров для всех провайдеров
     */
    public function register_exporters(): void {
        add_filter('wp_privacy_personal_data_exporters', [$this, 'add_exporters'], 10);
    }
    
    /**
     * Регистрация эрасеров для всех провайдеров
     */
    public function register_erasers(): void {
        add_filter('wp_privacy_personal_data_erasers', [$this, 'add_erasers'], 10);
    }
    
    /**
     * Добавить экспортеры в WordPress Privacy Tools
     * 
     * @param array $exporters Существующие экспортеры
     * @return array Обновленный массив экспортеров
     */
    public function add_exporters(array $exporters): array {
        $providers = $this->manager->get_providers();
        
        foreach ($providers as $provider) {
            $provider_id = $provider->get_provider_id();
            $provider_name = $provider->get_provider_name();
            
            // Регистрируем экспортер для каждого провайдера
            $exporters[$provider_id] = [
                'exporter_friendly_name' => $provider_name,
                'callback' => function($email_address, $page = 1) use ($provider) {
                    return $provider->get_personal_data($email_address, $page);
                }
            ];
        }
        
        return $exporters;
    }
    
    /**
     * Добавить эрасеры в WordPress Privacy Tools
     * 
     * @param array $erasers Существующие эрасеры
     * @return array Обновленный массив эрасеров
     */
    public function add_erasers(array $erasers): array {
        $providers = $this->manager->get_providers();
        
        foreach ($providers as $provider) {
            $provider_id = $provider->get_provider_id();
            $provider_name = $provider->get_provider_name();
            
            // Регистрируем эрасер для каждого провайдера
            $erasers[$provider_id] = [
                'eraser_friendly_name' => $provider_name,
                'callback' => function($email_address, $page = 1) use ($provider) {
                    return $provider->erase_personal_data($email_address, $page);
                }
            ];
        }
        
        return $erasers;
    }
    
    /**
     * Универсальный экспортер (объединяет все провайдеры)
     * Можно использовать для единого экспорта всех данных
     * 
     * @param string $email_address Email адрес
     * @param int $page Номер страницы
     * @return array Данные всех провайдеров
     */
    public function export_all_data(string $email_address, int $page = 1): array {
        return $this->manager->get_all_personal_data($email_address, $page);
    }
    
    /**
     * Универсальный эрасер (объединяет все провайдеры)
     * Можно использовать для единого удаления всех данных
     * 
     * @param string $email_address Email адрес
     * @param int $page Номер страницы
     * @return array Результат удаления
     */
    public function erase_all_data(string $email_address, int $page = 1): array {
        return $this->manager->erase_all_personal_data($email_address, $page);
    }
}


