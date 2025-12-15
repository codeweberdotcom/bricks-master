<?php
/**
 * Personal Data Manager
 * 
 * Главный менеджер для работы с персональными данными
 * Регистрирует провайдеры и управляет ими
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-data-provider-interface.php';
require_once __DIR__ . '/class-gdpr-registry.php';

class Personal_Data_Manager {
    
    private static $instance = null;
    
    /**
     * @var Personal_Data_Provider_Interface[] Зарегистрированные провайдеры
     */
    private $providers = [];
    
    /**
     * @var GDPR_Registry Реестр GDPR экспортеров и эрасеров
     */
    private $gdpr_registry;
    
    /**
     * Получить экземпляр менеджера (Singleton)
     * 
     * @return Personal_Data_Manager
     */
    public static function get_instance(): Personal_Data_Manager {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Конструктор
     */
    private function __construct() {
        $this->gdpr_registry = new GDPR_Registry($this);
        $this->init();
    }
    
    /**
     * Инициализация
     */
    private function init(): void {
        // Регистрируем GDPR обработчики
        add_action('init', [$this->gdpr_registry, 'register_exporters'], 10);
        add_action('init', [$this->gdpr_registry, 'register_erasers'], 10);
        
        // Хук для регистрации провайдеров (другие модули могут использовать)
        do_action('personal_data_manager_init', $this);
    }
    
    /**
     * Зарегистрировать провайдер персональных данных
     * 
     * @param Personal_Data_Provider_Interface $provider Провайдер данных
     * @return bool true если успешно зарегистрирован, false если уже существует
     */
    public function register_provider(Personal_Data_Provider_Interface $provider): bool {
        $provider_id = $provider->get_provider_id();
        
        if (isset($this->providers[$provider_id])) {
            error_log(sprintf(
                'Personal Data Provider "%s" is already registered.',
                $provider_id
            ));
            return false;
        }
        
        $this->providers[$provider_id] = $provider;
        
        // Хук для уведомления о регистрации провайдера
        do_action('personal_data_provider_registered', $provider_id, $provider);
        
        return true;
    }
    
    /**
     * Получить провайдера по ID
     * 
     * @param string $provider_id Идентификатор провайдера
     * @return Personal_Data_Provider_Interface|null Провайдер или null если не найден
     */
    public function get_provider(string $provider_id): ?Personal_Data_Provider_Interface {
        return $this->providers[$provider_id] ?? null;
    }
    
    /**
     * Получить все зарегистрированные провайдеры
     * 
     * @return Personal_Data_Provider_Interface[] Массив провайдеров
     */
    public function get_providers(): array {
        return $this->providers;
    }
    
    /**
     * Получить список ID всех провайдеров
     * 
     * @return string[] Массив идентификаторов
     */
    public function get_provider_ids(): array {
        return array_keys($this->providers);
    }
    
    /**
     * Проверить, зарегистрирован ли провайдер
     * 
     * @param string $provider_id Идентификатор провайдера
     * @return bool true если зарегистрирован
     */
    public function is_provider_registered(string $provider_id): bool {
        return isset($this->providers[$provider_id]);
    }
    
    /**
     * Получить GDPR Registry
     * 
     * @return GDPR_Registry
     */
    public function get_gdpr_registry(): GDPR_Registry {
        return $this->gdpr_registry;
    }
    
    /**
     * Получить все персональные данные из всех провайдеров
     * 
     * @param string $email Email адрес
     * @param int $page Номер страницы
     * @return array Объединенные данные всех провайдеров
     */
    public function get_all_personal_data(string $email, int $page = 1): array {
        $all_data = [];
        $all_done = true;
        
        foreach ($this->providers as $provider) {
            $data = $provider->get_personal_data($email, $page);
            
            if (!empty($data['data'])) {
                $all_data = array_merge($all_data, $data['data']);
            }
            
            if (!$data['done']) {
                $all_done = false;
            }
        }
        
        return [
            'data' => $all_data,
            'done' => $all_done
        ];
    }
    
    /**
     * Удалить персональные данные из всех провайдеров
     * 
     * @param string $email Email адрес
     * @param int $page Номер страницы
     * @return array Результат удаления
     */
    public function erase_all_personal_data(string $email, int $page = 1): array {
        $items_removed = false;
        $items_retained = false;
        $messages = [];
        $all_done = true;
        
        foreach ($this->providers as $provider) {
            $result = $provider->erase_personal_data($email, $page);
            
            if ($result['items_removed']) {
                $items_removed = true;
            }
            
            if ($result['items_retained']) {
                $items_retained = true;
            }
            
            if (!empty($result['messages'])) {
                $messages = array_merge($messages, $result['messages']);
            }
            
            if (!$result['done']) {
                $all_done = false;
            }
        }
        
        return [
            'items_removed' => $items_removed,
            'items_retained' => $items_retained,
            'messages' => $messages,
            'done' => $all_done
        ];
    }
}


