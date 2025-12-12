# Система ограничений доступа для Redux Framework

## Описание

Эта система позволяет скрывать определенные секции и поля Redux Framework для роли `simpleadmin`. Все ограничения применяются на серверной стороне, без использования JavaScript.

## Как это работает

1. **Проверка роли**: Система проверяет, является ли текущий пользователь `simpleadmin`
2. **Применение ограничений**: Для указанных секций/полей добавляется параметр `permissions` с capability `manage_theme_options_full`
3. **Скрытие элементов**: Redux Framework автоматически скрывает элементы, к которым у пользователя нет доступа

## Настройка

### Шаг 1: Откройте файл конфигурации

Откройте файл:
```
wp-content/themes/codeweber/functions/integrations/redux_framework/redux_permissions_config.php
```

### Шаг 2: Настройте ограничения

В функции `codeweber_get_redux_permissions_config()` укажите, что нужно скрыть:

#### Скрыть всю секцию:

```php
'sections' => array(
   'gulp_section',           // Секция Gulp
   'child_theme_section',     // Секция Child Theme
   'cpt_section',             // Секция Custom Post Types
),
```

#### Скрыть отдельные поля (глобально):

```php
'fields' => array(
   'run_gulp',
   'enable_child_theme',
   'site_logo',
),
```

#### Скрыть поля в конкретной секции:

```php
'section_fields' => array(
   'general_settings' => array(
      'site_logo',
      'logo_dark',
   ),
   'gulp_section' => array(
      'run_gulp',
   ),
),
```

### Шаг 3: Сохраните изменения

После сохранения файла ограничения будут применены автоматически.

## Примеры использования

### Пример 1: Скрыть секцию Gulp полностью

```php
'sections' => array(
   'gulp_section',
),
```

### Пример 2: Скрыть только кнопку запуска Gulp

```php
'section_fields' => array(
   'gulp_section' => array(
      'run_gulp',
   ),
),
```

### Пример 3: Скрыть несколько полей в разных секциях

```php
'section_fields' => array(
   'general_settings' => array(
      'site_logo',
      'logo_dark',
   ),
   'child_theme_section' => array(
      'enable_child_theme',
   ),
),
```

## Как узнать ID секции или поля?

1. Откройте страницу настроек Redux в админке WordPress
2. Откройте нужную секцию
3. Посмотрите в исходный код страницы или используйте инструменты разработчика
4. ID секции обычно указан в атрибуте `id` элемента секции
5. ID поля можно найти в атрибуте `id` поля или в консоли браузера

Также можно посмотреть в файлы Redux настроек:
- `wp-content/themes/codeweber/redux-framework/theme-settings/Menu_item_*/`

## Фильтры для расширения

Вы можете использовать фильтр `codeweber_redux_permissions_config` для программного изменения конфигурации:

```php
add_filter('codeweber_redux_permissions_config', function($config) {
   // Добавить секцию для скрытия
   $config['sections'][] = 'my_custom_section';
   
   // Добавить поле для скрытия
   $config['fields'][] = 'my_custom_field';
   
   return $config;
});
```

## Технические детали

- Система использует встроенную систему permissions Redux Framework
- Ограничения применяются через фильтры WordPress
- Проверка происходит на серверной стороне
- JavaScript не используется для блокировки (только для скрытия элементов Redux)

## Файлы системы

- `redux_permissions_config.php` - конфигурация ограничений
- `redux_permissions.php` - логика применения ограничений
- `roles.php` - helper-функции для работы с ролями

