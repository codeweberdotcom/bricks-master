# Personal Data V2 Module

Универсальный модуль для работы с персональными данными в WordPress. Поддерживает автоматическую регистрацию GDPR экспортеров и эрасеров для любых модулей, форм и подписок.

## Возможности

- ✅ Универсальный интерфейс для провайдеров данных
- ✅ Автоматическая регистрация в WordPress Privacy Tools
- ✅ Поддержка любых источников данных (БД таблицы, CPT, meta)
- ✅ Не ломает существующий функционал
- ✅ Легко расширяется новыми провайдерами

## Структура

```
personal-data-v2/
├── class-data-provider-interface.php  # Интерфейс для провайдеров
├── class-personal-data-manager.php    # Главный менеджер
├── class-gdpr-registry.php            # Регистрация GDPR обработчиков
├── init.php                            # Инициализация модуля
├── providers/                          # Провайдеры для разных модулей
│   ├── class-newsletter-provider.php
│   └── ...
└── storage/                            # Универсальные хранилища (будущее)
```

## Как создать провайдер

### 1. Создайте класс провайдера

```php
<?php
require_once __DIR__ . '/../class-data-provider-interface.php';

class My_Module_Provider implements Personal_Data_Provider_Interface {
    
    public function get_provider_id(): string {
        return 'my-module';
    }
    
    public function get_provider_name(): string {
        return __('My Module', 'codeweber');
    }
    
    public function get_provider_description(): string {
        return __('Personal data from My Module', 'codeweber');
    }
    
    public function get_personal_data(string $email, int $page = 1): array {
        // Получаем данные из вашего источника
        // Возвращаем в формате WordPress Privacy Tools
        return [
            'data' => [
                [
                    'group_id' => 'my-module-data',
                    'group_label' => __('My Module Data', 'codeweber'),
                    'item_id' => 'my-module-item-1',
                    'data' => [
                        ['name' => __('Field Name', 'codeweber'), 'value' => 'Field Value'],
                        // ...
                    ]
                ]
            ],
            'done' => true
        ];
    }
    
    public function erase_personal_data(string $email, int $page = 1): array {
        // Удаляем или анонимизируем данные
        return [
            'items_removed' => true,
            'items_retained' => false,
            'messages' => [__('Data anonymized', 'codeweber')],
            'done' => true
        ];
    }
    
    public function has_personal_data(string $email): bool {
        // Проверяем наличие данных
        return false;
    }
    
    public function get_personal_data_fields(): array {
        return [
            'field1' => __('Field 1', 'codeweber'),
            'field2' => __('Field 2', 'codeweber'),
        ];
    }
}
```

### 2. Зарегистрируйте провайдер

В файле инициализации вашего модуля:

```php
// Регистрация провайдера
add_action('personal_data_v2_ready', function($manager) {
    require_once __DIR__ . '/../personal-data-v2/providers/class-my-module-provider.php';
    $provider = new My_Module_Provider();
    $manager->register_provider($provider);
}, 10);
```

### 3. Готово!

После регистрации провайдер автоматически:
- Появится в WordPress Privacy Tools (Экспорт/Удаление персональных данных)
- Будет экспортировать данные при запросе пользователя
- Будет удалять/анонимизировать данные при запросе

## Примеры использования

### Получить менеджер

```php
$manager = Personal_Data_Manager::get_instance();
```

### Получить все провайдеры

```php
$providers = $manager->get_providers();
foreach ($providers as $provider) {
    echo $provider->get_provider_name();
}
```

### Получить данные из всех провайдеров

```php
$all_data = $manager->get_all_personal_data('user@example.com');
```

### Удалить данные из всех провайдеров

```php
$result = $manager->erase_all_personal_data('user@example.com');
```

## Формат данных

### Экспорт (get_personal_data)

```php
[
    'data' => [
        [
            'group_id' => 'group-identifier',
            'group_label' => 'Group Label',
            'item_id' => 'item-identifier',
            'data' => [
                ['name' => 'Field Name', 'value' => 'Field Value'],
                // ...
            ]
        ],
        // ...
    ],
    'done' => true  // true если все данные получены, false если есть еще страницы
]
```

### Удаление (erase_personal_data)

```php
[
    'items_removed' => true,      // Были ли удалены данные
    'items_retained' => false,     // Остались ли данные (например, по юридическим причинам)
    'messages' => [               // Сообщения о результате
        'Data anonymized',
        // ...
    ],
    'done' => true                // true если все данные обработаны
]
```

## Существующие провайдеры

### 1. Newsletter_Data_Provider
- **ID:** `newsletter-subscription-v2`
- **Источник:** таблица `wp_newsletter_subscriptions`
- **Данные:** Email, имя, фамилия, телефон, IP, User Agent, статус подписки, даты
- **Регистрация:** автоматически в `newsletter-init.php`

### 2. CF7_Data_Provider
- **ID:** `contact-form-7`
- **Источник:** плагин Flamingo (CPT `flamingo_inbound`)
- **Данные:** Email отправителя, имя, телефон, сообщение, все поля формы, IP, User Agent
- **Регистрация:** автоматически в `functions.php`

### 3. Codeweber_Forms_Data_Provider
- **ID:** `codeweber-forms`
- **Источник:** таблица `wp_codeweber_forms_submissions`
- **Данные:** Все поля формы (JSON), загруженные файлы, IP, User Agent, статус
- **Регистрация:** автоматически в `codeweber-forms-init.php`

### 4. Testimonials_Data_Provider
- **ID:** `testimonials`
- **Источник:** CPT `testimonials`
- **Данные:** Email автора, имя, должность, компания, текст отзыва, рейтинг, IP, дата
- **Регистрация:** автоматически в `functions.php`

### 5. Consent_Data_Provider
- **ID:** `user-consents-v2`
- **Источник:** CPT `consent_subscriber` (через Consent_CPT)
- **Данные:** Email подписчика, телефон, история согласий (тип, документ, дата, IP, User Agent, ревизия)
- **Регистрация:** автоматически в `functions.php`

## Миграция со старого функционала

Старый функционал продолжает работать параллельно. Новые модули могут использовать Personal Data V2, а старые постепенно мигрировать.

### Преимущества миграции

1. Единая точка регистрации
2. Автоматическая GDPR интеграция
3. Упрощенное тестирование
4. Лучшая организация кода

## Хуки

### `personal_data_v2_ready`
Срабатывает когда менеджер готов к регистрации провайдеров.

```php
add_action('personal_data_v2_ready', function($manager) {
    // Регистрируем провайдеры
}, 10);
```

### `personal_data_provider_registered`
Срабатывает когда провайдер зарегистрирован.

```php
add_action('personal_data_provider_registered', function($provider_id, $provider) {
    // Логирование, уведомления и т.д.
}, 10, 2);
```

## Тестирование

### Способ 1: Тестовая страница в админке

1. Временно добавьте в `functions.php`:
```php
require_once get_template_directory() . '/functions/integrations/personal-data-v2/test-providers.php';
```

2. Откройте в админке: **Инструменты → PD Providers Test**

3. Проверьте:
   - Все провайдеры зарегистрированы
   - Экспортеры и эрасеры зарегистрированы в WordPress Privacy Tools
   - Проверка наличия данных по email

4. Удалите строку из `functions.php` после тестирования

### Способ 2: Через WordPress Privacy Tools

1. Перейдите в **Инструменты → Экспорт персональных данных**
2. Введите email адрес пользователя
3. Проверьте, что в экспорте присутствуют данные из всех зарегистрированных провайдеров
4. Проверьте удаление через **Инструменты → Удаление персональных данных**

### Проверка регистрации провайдеров

```php
$manager = Personal_Data_Manager::get_instance();
$providers = $manager->get_providers();

foreach ($providers as $provider) {
    echo $provider->get_provider_name() . ' (' . $provider->get_provider_id() . ')' . PHP_EOL;
}
```

### Проверка наличия данных

```php
$manager = Personal_Data_Manager::get_instance();
$provider = $manager->get_provider('newsletter-subscription-v2');

if ($provider && $provider->has_personal_data('user@example.com')) {
    echo 'Data found!';
}
```

## Совместимость

- WordPress 5.0+
- PHP 7.4+
- Требует WordPress Privacy Tools (встроено в WordPress)
- Для CF7_Data_Provider требуется плагин Flamingo (опционально)

