<?php
/**
 * Personal Data Provider Interface
 * 
 * Интерфейс для провайдеров персональных данных
 * Все модули, формы, подписки должны реализовывать этот интерфейс
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Интерфейс провайдера персональных данных
 */
interface Personal_Data_Provider_Interface {
    
    /**
     * Получить уникальный идентификатор провайдера
     * 
     * @return string Идентификатор провайдера (например, 'newsletter-subscription', 'contact-form-7')
     */
    public function get_provider_id(): string;
    
    /**
     * Получить название провайдера (для отображения в админке)
     * 
     * @return string Название провайдера
     */
    public function get_provider_name(): string;
    
    /**
     * Получить описание провайдера (опционально)
     * 
     * @return string Описание провайдера
     */
    public function get_provider_description(): string;
    
    /**
     * Получить все персональные данные по email
     * 
     * @param string $email Email адрес пользователя
     * @param int $page Номер страницы (для пагинации)
     * @return array Массив данных в формате WordPress Privacy Tools:
     *               [
     *                   'data' => [
     *                       [
     *                           'group_id' => 'group-id',
     *                           'group_label' => 'Group Label',
     *                           'item_id' => 'item-id',
     *                           'data' => [
     *                               ['name' => 'Field Name', 'value' => 'Field Value'],
     *                               ...
     *                           ]
     *                       ],
     *                       ...
     *                   ],
     *                   'done' => true/false
     *               ]
     */
    public function get_personal_data(string $email, int $page = 1): array;
    
    /**
     * Удалить или анонимизировать персональные данные
     * 
     * @param string $email Email адрес пользователя
     * @param int $page Номер страницы (для пагинации)
     * @return array Массив результата в формате WordPress Privacy Tools:
     *               [
     *                   'items_removed' => true/false,
     *                   'items_retained' => true/false,
     *                   'messages' => ['Message 1', 'Message 2'],
     *                   'done' => true/false
     *               ]
     */
    public function erase_personal_data(string $email, int $page = 1): array;
    
    /**
     * Проверить, есть ли персональные данные для этого email
     * 
     * @param string $email Email адрес пользователя
     * @return bool true если данные есть, false если нет
     */
    public function has_personal_data(string $email): bool;
    
    /**
     * Получить список полей с персональными данными
     * Используется для документации и отображения в админке
     * 
     * @return array Массив полей:
     *               [
     *                   'field_name' => 'Field Label',
     *                   ...
     *               ]
     */
    public function get_personal_data_fields(): array;
}


