# Personal Data V2 — GDPR-инфраструктура

## Что делает этот модуль

Универсальная инфраструктура для GDPR-совместимости. Интегрирует несколько источников персональных данных с встроенными инструментами WordPress:
- **Инструменты → Экспорт персональных данных**
- **Инструменты → Удаление персональных данных**

Модуль работает через паттерн провайдеров: каждый источник данных реализует интерфейс `Personal_Data_Provider_Interface` и регистрируется в менеджере.

---

## Файлы модуля

| Файл | Назначение |
|------|-----------|
| `functions/integrations/personal-data-v2/init.php` | Точка входа, инициализация менеджера |
| `functions/integrations/personal-data-v2/class-data-provider-interface.php` | Интерфейс `Personal_Data_Provider_Interface` |
| `functions/integrations/personal-data-v2/class-personal-data-manager.php` | Менеджер `Personal_Data_Manager` (singleton) |
| `functions/integrations/personal-data-v2/class-gdpr-registry.php` | Регистрация экспортеров/эрасеров в WP Privacy |
| `functions/integrations/personal-data-v2/providers/` | Готовые провайдеры (5 штук) |
| `functions/integrations/personal-data-v2/test-providers.php` | Тестовая страница в админке |

---

## Зарегистрированные провайдеры

| ID провайдера | Класс | Источник данных | Где регистрируется |
|-------------|-------|----------------|------------------|
| `newsletter-subscription-v2` | `Newsletter_Data_Provider` | `wp_newsletter_subscriptions` | `newsletter-init.php` |
| `contact-form-7` | `CF7_Data_Provider` | CPT `flamingo_inbound` (плагин Flamingo) | `functions.php` |
| `codeweber-forms` | `Codeweber_Forms_Data_Provider` | `wp_codeweber_forms_submissions` | `codeweber-forms-init.php` |
| `testimonials` | `Testimonials_Data_Provider` | CPT `testimonials` | `functions.php` |
| `user-consents-v2` | `Consent_Data_Provider` | CPT `consent_subscriber` | `functions.php` |

---

## Как это работает

```
WP запрос экспорта email@example.com
    ↓
WP вызывает зарегистрированные экспортеры
    ↓
GDPR_Registry вызывает каждый провайдер: provider->get_personal_data(email)
    ↓
Провайдер делает запрос к своему источнику (БД таблица / CPT)
    ↓
Возвращает данные в стандартном формате WP Privacy
    ↓
WP собирает всё в ZIP-архив для скачивания
```

---

## Интерфейс провайдера

```php
interface Personal_Data_Provider_Interface {
    public function get_provider_id(): string;       // Уникальный ID
    public function get_provider_name(): string;     // Название для UI
    public function get_provider_description(): string;
    public function get_personal_data(string $email, int $page = 1): array;
    public function erase_personal_data(string $email, int $page = 1): array;
    public function has_personal_data(string $email): bool;
    public function get_personal_data_fields(): array;  // Список полей (для документации)
}
```

---

## Формат возвращаемых данных

### `get_personal_data()` — экспорт

```php
return [
    'data' => [
        [
            'group_id'    => 'newsletter-subscriptions',      // Технический ключ группы
            'group_label' => 'Newsletter Subscriptions',      // Отображаемый заголовок
            'item_id'     => 'newsletter-item-123',
            'data'        => [
                ['name' => 'Email',   'value' => 'user@example.com'],
                ['name' => 'Status',  'value' => 'confirmed'],
                ['name' => 'Created', 'value' => '2025-01-01 12:00:00'],
                // ...
            ],
        ],
    ],
    'done' => true,  // false если есть ещё страницы (пагинация)
];
```

### `erase_personal_data()` — удаление/анонимизация

```php
return [
    'items_removed'  => true,    // Были ли данные удалены/анонимизированы
    'items_retained' => false,   // true если часть данных сохранена (юр. требования)
    'messages'       => [
        'Email anonymized',
        'Subscription marked as deleted',
    ],
    'done' => true,
];
```

---

## Как зарегистрировать новый провайдер

### Шаг 1: Создать класс провайдера

```php
// functions/integrations/personal-data-v2/providers/class-my-provider.php

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

    public function has_personal_data(string $email): bool {
        global $wpdb;
        return (bool) $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM {$wpdb->prefix}my_table WHERE email = %s LIMIT 1", $email)
        );
    }

    public function get_personal_data(string $email, int $page = 1): array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}my_table WHERE email = %s", $email
        ));

        if (!$row) {
            return ['data' => [], 'done' => true];
        }

        return [
            'data' => [[
                'group_id'    => 'my-module-data',
                'group_label' => __('My Module Data', 'codeweber'),
                'item_id'     => 'my-item-' . $row->id,
                'data'        => [
                    ['name' => __('Email', 'codeweber'), 'value' => $row->email],
                    ['name' => __('Created', 'codeweber'), 'value' => $row->created_at],
                ],
            ]],
            'done' => true,
        ];
    }

    public function erase_personal_data(string $email, int $page = 1): array {
        global $wpdb;
        $wpdb->delete("{$wpdb->prefix}my_table", ['email' => $email]);

        return [
            'items_removed'  => true,
            'items_retained' => false,
            'messages'       => [__('Data removed', 'codeweber')],
            'done'           => true,
        ];
    }

    public function get_personal_data_fields(): array {
        return [
            'email'      => __('Email Address', 'codeweber'),
            'created_at' => __('Created Date', 'codeweber'),
        ];
    }
}
```

### Шаг 2: Зарегистрировать провайдер

В `init.php` своего модуля или в `functions.php`:

```php
add_action('personal_data_v2_ready', function($manager) {
    require_once get_template_directory() . '/functions/integrations/personal-data-v2/providers/class-my-provider.php';
    $manager->register_provider(new My_Module_Provider());
}, 10);
```

---

## Менеджер `Personal_Data_Manager`

**Файл:** `class-personal-data-manager.php`

Singleton. Доступ:
```php
$manager = Personal_Data_Manager::get_instance();
```

| Метод | Описание |
|-------|---------|
| `register_provider($provider)` | Зарегистрировать провайдер |
| `get_providers()` | Получить все провайдеры |
| `get_provider($id)` | Получить провайдер по ID |
| `get_all_personal_data($email)` | Получить данные из всех провайдеров |
| `erase_all_personal_data($email)` | Удалить данные во всех провайдерах |

---

## Хуки

```php
// Срабатывает когда менеджер готов — здесь нужно регистрировать провайдеры
add_action('personal_data_v2_ready', function($manager) {
    $manager->register_provider(new My_Provider());
});

// Срабатывает после регистрации каждого провайдера
add_action('personal_data_provider_registered', function($provider_id, $provider) {
    // Логирование, уведомления
}, 10, 2);
```

---

## Тестирование

### Через тестовую страницу в админке

1. Временно добавьте в `functions.php`:
   ```php
   require_once get_template_directory() . '/functions/integrations/personal-data-v2/test-providers.php';
   ```
2. Откройте **Инструменты → PD Providers Test**
3. Проверьте список зарегистрированных провайдеров
4. После проверки — удалите строку из `functions.php`

### Через WordPress Privacy Tools

1. **Инструменты → Экспорт персональных данных** — создать запрос, скачать ZIP
2. **Инструменты → Удаление персональных данных** — создать запрос, подтвердить удаление

### Программно

```php
$manager = Personal_Data_Manager::get_instance();

// Проверить список провайдеров
foreach ($manager->get_providers() as $p) {
    echo $p->get_provider_id() . ': ' . $p->get_provider_name() . "\n";
}

// Проверить наличие данных
$provider = $manager->get_provider('newsletter-subscription-v2');
if ($provider && $provider->has_personal_data('test@example.com')) {
    $data = $provider->get_personal_data('test@example.com');
    print_r($data);
}
```

---

## Требования

- WordPress 5.0+ (WordPress Privacy Tools встроены)
- PHP 7.4+
- Для `CF7_Data_Provider` — плагин Flamingo (опционально)
- Для `Consent_Data_Provider` — CPT `consent_subscriber` должен быть зарегистрирован
