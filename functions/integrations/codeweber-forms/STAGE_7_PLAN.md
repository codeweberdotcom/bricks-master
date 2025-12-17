# Этап 7: Обновление базы данных - Подробный план

## ⚠️ ВАЖНО: Обратная совместимость и параллельная работа

### Стратегия параллельной работы

**Цель:** Обеспечить одновременную работу старого (legacy) и нового функционала для тестирования и отладки.

**Legacy формы (старые встроенные) - НЕ ТРОГАЕМ:**
- `testimonial` (строковый ID) - продолжает работать как есть
- `newsletter` (строковый ID) - продолжает работать как есть
- `resume` (строковый ID) - продолжает работать как есть
- `callback` (строковый ID) - продолжает работать как есть

**Новые CPT формы - ДОБАВЛЯЕМ:**
- CPT формы с типами работают через `_form_type` и `formType` в блоке
- Новые формы можно создавать через Gutenberg
- Новые формы работают параллельно со старыми

**Все методы поддерживают оба варианта:**
- Автоматическое определение типа для legacy (строка = тип)
- Автоматическое определение типа для новых (из мета/блока)
- Обратная совместимость сохранена на 100%

**После тестирования и отладки:**
- Убедиться, что все работает корректно
- Мигрировать legacy формы в CPT (Этап 10)
- Удалить старый функционал (Этап 11)

### Что НЕ делаем в Этапе 7:

❌ НЕ удаляем legacy формы  
❌ НЕ изменяем логику работы legacy форм  
❌ НЕ ломаем существующие шорткоды  
❌ НЕ удаляем fallback логику  

### Что ДЕЛАЕМ в Этапе 7:

✅ Исправляем баги в работе с `form_id` (VARCHAR)  
✅ Добавляем колонку `form_type` для быстрого поиска  
✅ Мигрируем только CPT формы (добавляем `_form_type`)  
✅ Обновляем методы для поддержки обоих вариантов  
✅ Обеспечиваем параллельную работу старого и нового

---

## Цели этапа

1. Исправить баги в работе с `form_id` (VARCHAR вместо INT) - **с сохранением обратной совместимости**
2. Добавить опциональную колонку `form_type` в таблицу submissions (для быстрого поиска)
3. Мигрировать существующие CPT формы без типа
4. Обновить существующие записи в submissions (опционально)
5. Улучшить методы работы с базой данных
6. **Обеспечить параллельную работу старого и нового функционала**

---

## 1. Исправление багов в CodeweberFormsDatabase

### 1.1. Проблема: Неправильное использование `%d` для VARCHAR `form_id`

**Файл:** `codeweber-forms-database.php`

**Проблемные места:**
- `get_submissions()` - строка 160: `$wpdb->prepare("form_id = %d", intval($args['form_id']))`
- `count_submissions()` - строка 199: `$wpdb->prepare("form_id = %d", intval($args['form_id']))`

**Решение:**
- Заменить `%d` на `%s` для `form_id`
- Убрать `intval()` для `form_id`
- Добавить проверку типа данных перед подготовкой запроса

**Код исправления:**
```php
// Было:
if (isset($args['form_id']) && $args['form_id'] !== '') {
    $where[] = $wpdb->prepare("form_id = %d", intval($args['form_id']));
}

// Станет (с поддержкой legacy):
if (isset($args['form_id']) && $args['form_id'] !== '') {
    $form_id = $args['form_id'];
    // form_id может быть как числом (CPT формы), так и строкой (legacy built-in формы)
    // Поддерживаем оба варианта для обратной совместимости
    if (is_numeric($form_id)) {
        $where[] = $wpdb->prepare("form_id = %s", (string) $form_id);
    } else {
        // Legacy: строковые ID (testimonial, newsletter, resume, callback)
        $where[] = $wpdb->prepare("form_id = %s", sanitize_text_field($form_id));
    }
}
```

**Проверка обратной совместимости:**
- ✅ Запросы с числовыми ID (CPT формы) работают
- ✅ Запросы со строковыми ID (legacy формы) работают
- ✅ Все существующие формы продолжают работать без изменений

---

## 2. Добавление колонки `form_type` в таблицу submissions

### 2.1. Обновление версии базы данных

**Файл:** `codeweber-forms-database.php`

**Изменения:**
- Обновить `$version` с `'1.0.3'` на `'1.0.4'`
- Добавить миграцию для добавления колонки `form_type`

**Структура новой колонки:**
```sql
form_type VARCHAR(50) DEFAULT NULL,
KEY form_type (form_type)
```

**Логика миграции:**
1. Проверить существование колонки
2. Если колонки нет - добавить
3. Если есть старые записи - попытаться определить тип из `form_id` и обновить

**Код:**
```php
private $version = '1.0.4'; // Добавлена колонка form_type

private function create_table() {
    // ... existing code ...
    
    if ($current_version !== $this->version) {
        // ... existing charset_collate ...
        
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") === $this->table_name;
        
        // Миграция 1.0.3 -> 1.0.4: Добавляем колонку form_type
        if ($table_exists && version_compare($current_version, '1.0.4', '<')) {
            // Проверяем, существует ли колонка
            $column_exists = $wpdb->get_results(
                "SHOW COLUMNS FROM {$this->table_name} LIKE 'form_type'"
            );
            
            if (empty($column_exists)) {
                // Добавляем колонку
                $wpdb->query(
                    "ALTER TABLE {$this->table_name} 
                     ADD COLUMN form_type VARCHAR(50) DEFAULT NULL AFTER form_name,
                     ADD KEY form_type (form_type)"
                );
                
                // Попытка заполнить существующие записи
                $this->migrate_existing_form_types();
            }
        }
        
        // ... rest of create_table ...
    }
}
```

### 2.2. Метод миграции существующих типов форм

**Новый метод:** `migrate_existing_form_types()`

**Логика:**
1. Получить все записи без `form_type`
2. Для каждой записи:
   - Если `form_id` - строка (testimonial, newsletter, etc.) → установить `form_type = form_id`
   - Если `form_id` - число → получить тип через `CodeweberFormsCore::get_form_type()`
   - Если тип не определен → установить `'form'`
3. Обновить записи в базе

**Код (с поддержкой legacy):**
```php
private function migrate_existing_form_types() {
    global $wpdb;
    
    // Получаем все записи без form_type
    $submissions = $wpdb->get_results(
        "SELECT id, form_id FROM {$this->table_name} WHERE form_type IS NULL"
    );
    
    if (empty($submissions)) {
        return;
    }
    
    foreach ($submissions as $submission) {
        $form_type = 'form'; // По умолчанию
        
        // LEGACY: Если form_id - строка (старые встроенные формы)
        // Сохраняем обратную совместимость - эти формы продолжают работать
        if (!is_numeric($submission->form_id)) {
            $form_id_str = strtolower($submission->form_id);
            $builtin_types = ['newsletter', 'testimonial', 'resume', 'callback'];
            if (in_array($form_id_str, $builtin_types)) {
                // Для legacy форм тип = ID
                $form_type = $form_id_str;
            }
        } 
        // НОВОЕ: Если form_id - число (CPT форма)
        else {
            if (class_exists('CodeweberFormsCore')) {
                $form_type = CodeweberFormsCore::get_form_type((int) $submission->form_id);
            } else {
                // Fallback: проверяем метаполе
                $meta_type = get_post_meta((int) $submission->form_id, '_form_type', true);
                if (!empty($meta_type)) {
                    $form_type = $meta_type;
                }
            }
        }
        
        // Обновляем запись (не ломая существующие данные)
        $wpdb->update(
            $this->table_name,
            ['form_type' => $form_type],
            ['id' => $submission->id],
            ['%s'],
            ['%d']
        );
    }
}
```

**Проверка обратной совместимости:**
- ✅ Legacy формы (testimonial, newsletter, etc.) получают `form_type = form_id`
- ✅ CPT формы получают тип через `get_form_type()`
- ✅ Старые записи в submissions продолжают работать
- ✅ Новые записи автоматически получают правильный тип

### 2.3. Обновление метода `save_submission()`

**Изменения:**
- Добавить сохранение `form_type` при создании новой записи
- Автоматически определять тип через `CodeweberFormsCore::get_form_type()`

**Код (с поддержкой legacy):**
```php
public function save_submission($data) {
    // ... existing code ...
    
    // Определяем form_type (поддержка legacy + нового функционала)
    $form_type = 'form'; // По умолчанию
    
    // Приоритет 1: Явно переданный тип (для новых форм)
    if (!empty($data['form_type'])) {
        $form_type = sanitize_text_field($data['form_type']);
    } 
    // Приоритет 2: Автоматическое определение через единую функцию
    else if (class_exists('CodeweberFormsCore')) {
        $form_type = CodeweberFormsCore::get_form_type($form_id_value);
    } 
    // Приоритет 3: LEGACY - поддержка старых встроенных форм
    else {
        // Legacy: строковые ID (testimonial, newsletter, resume, callback)
        if (!is_numeric($form_id_value)) {
            $form_id_lower = strtolower($form_id_value);
            $builtin_types = ['newsletter', 'testimonial', 'resume', 'callback'];
            if (in_array($form_id_lower, $builtin_types)) {
                // Для legacy форм тип = ID (обратная совместимость)
                $form_type = $form_id_lower;
            }
        } 
        // Legacy: числовые ID без метаполя
        else {
            $meta_type = get_post_meta((int) $form_id_value, '_form_type', true);
            if (!empty($meta_type)) {
                $form_type = $meta_type;
            }
        }
    }
    
    $insert_data = [
        'form_id' => $form_id_value,
        'form_name' => sanitize_text_field($data['form_name']),
        'form_type' => $form_type, // НОВОЕ: сохраняем тип для быстрого поиска
        // ... rest of fields ...
    ];
    
    // ... rest of method ...
}
```

**Проверка обратной совместимости:**
- ✅ Legacy формы (testimonial, newsletter, etc.) автоматически получают правильный тип
- ✅ Новые CPT формы получают тип из `_form_type` или блока
- ✅ Если тип не определен - используется 'form' (безопасное значение по умолчанию)
- ✅ Все существующие вызовы `save_submission()` продолжают работать без изменений

### 2.4. Обновление SQL схемы в `create_table()`

**Изменения:**
- Добавить `form_type` в CREATE TABLE SQL

**Код:**
```php
$sql = "CREATE TABLE {$this->table_name} (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    form_id VARCHAR(255) NOT NULL DEFAULT '0',
    form_name VARCHAR(255) DEFAULT '',
    form_type VARCHAR(50) DEFAULT NULL, // НОВОЕ
    submission_data LONGTEXT NOT NULL,
    // ... rest of fields ...
    PRIMARY KEY (id),
    KEY form_id (form_id(191)),
    KEY form_type (form_type), // НОВОЕ
    // ... rest of keys ...
) $charset_collate;";
```

---

## 3. Миграция существующих CPT форм

### 3.1. Создание скрипта миграции CPT форм

**Новый файл:** `codeweber-forms-migrate-cpt-types.php`

**Цель:** Обновить все существующие CPT формы, у которых нет `_form_type`

**Логика:**
1. Получить все CPT формы типа `codeweber_form`
2. Для каждой формы:
   - Проверить наличие `_form_type`
   - Если нет - попытаться извлечь из блока Gutenberg
   - Если не найдено - установить `'form'` по умолчанию
3. Сохранить метаполе

**Код:**
```php
<?php
/**
 * CodeWeber Forms - CPT Forms Type Migration
 * 
 * Миграция существующих CPT форм: добавление _form_type для форм без типа
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsCPTMigration {
    
    /**
     * Мигрировать все CPT формы без типа
     * 
     * @return array Результат миграции
     */
    public static function migrate_all_forms() {
        $results = [
            'total' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'errors_list' => [],
        ];
        
        // Получаем все CPT формы
        $forms = get_posts([
            'post_type' => 'codeweber_form',
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft', 'private'],
        ]);
        
        $results['total'] = count($forms);
        
        foreach ($forms as $form) {
            $result = self::migrate_single_form($form->ID);
            
            if ($result === true) {
                $results['updated']++;
            } elseif ($result === 'skipped') {
                $results['skipped']++;
            } else {
                $results['errors']++;
                $results['errors_list'][] = [
                    'form_id' => $form->ID,
                    'form_title' => $form->post_title,
                    'error' => $result,
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Мигрировать одну форму
     * 
     * @param int $form_id ID формы
     * @return bool|string true - обновлено, 'skipped' - пропущено, string - ошибка
     */
    public static function migrate_single_form($form_id) {
        // Проверяем, есть ли уже тип
        $existing_type = get_post_meta($form_id, '_form_type', true);
        if (!empty($existing_type)) {
            return 'skipped'; // Уже есть тип
        }
        
        // Пытаемся извлечь тип из блока
        $form_type = null;
        
        if (class_exists('CodeweberFormsCore')) {
            $form_type = CodeweberFormsCore::get_form_type($form_id);
        } else {
            // Fallback: извлекаем из блока напрямую
            $post = get_post($form_id);
            if ($post && !empty($post->post_content)) {
                $form_type = self::extract_type_from_block($post->post_content);
            }
        }
        
        // Если тип не найден - устанавливаем 'form' по умолчанию
        if (empty($form_type)) {
            $form_type = 'form';
        }
        
        // Сохраняем тип
        $result = update_post_meta($form_id, '_form_type', $form_type);
        
        if ($result !== false) {
            return true;
        }
        
        return 'Failed to save form type';
    }
    
    /**
     * Извлечь тип формы из блока Gutenberg
     * 
     * @param string $content Содержимое поста
     * @return string|null Тип формы или null
     */
    private static function extract_type_from_block($content) {
        if (empty($content) || !has_blocks($content)) {
            return null;
        }
        
        $blocks = parse_blocks($content);
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'codeweber-blocks/form' && !empty($block['attrs']['formType'])) {
                return sanitize_text_field($block['attrs']['formType']);
            }
        }
        
        return null;
    }
    
    /**
     * Запустить миграцию через WP-CLI или админку
     * 
     * @return void
     */
    public static function run_migration() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to run this migration.', 'codeweber'));
        }
        
        $results = self::migrate_all_forms();
        
        // Логируем результаты
        error_log('Codeweber Forms CPT Migration Results: ' . print_r($results, true));
        
        return $results;
    }
}

// Хук для запуска миграции через админку (опционально)
add_action('admin_init', function() {
    if (isset($_GET['codeweber_forms_migrate_cpt']) && 
        current_user_can('manage_options') &&
        wp_verify_nonce($_GET['_wpnonce'], 'codeweber_forms_migrate_cpt')) {
        
        $results = CodeweberFormsCPTMigration::run_migration();
        
        // Показываем результаты
        add_action('admin_notices', function() use ($results) {
            $message = sprintf(
                __('Migration completed: %d total, %d updated, %d skipped, %d errors.', 'codeweber'),
                $results['total'],
                $results['updated'],
                $results['skipped'],
                $results['errors']
            );
            echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
        });
    }
});
```

### 3.2. Интеграция миграции в CodeweberFormsCPT

**Файл:** `codeweber-forms-cpt.php`

**Изменения:**
- Добавить автоматический запуск миграции при обновлении версии
- Добавить опцию для отслеживания статуса миграции
- **Важно:** Миграция НЕ затрагивает legacy формы (они работают как есть)

**Код:**
```php
public function __construct() {
    // ... existing hooks ...
    
    // Автоматический запуск миграции CPT форм при первой загрузке
    // ВАЖНО: Миграция затрагивает только CPT формы, legacy формы не трогаем
    add_action('admin_init', [$this, 'maybe_run_cpt_migration']);
}

/**
 * Запустить миграцию CPT форм, если нужно
 * 
 * ВАЖНО: Эта миграция НЕ затрагивает legacy встроенные формы.
 * Legacy формы (testimonial, newsletter, resume, callback) продолжают
 * работать через строковые ID и не требуют миграции.
 */
public function maybe_run_cpt_migration() {
    // Проверяем, была ли уже выполнена миграция
    $migration_done = get_option('codeweber_forms_cpt_migration_done', false);
    
    if ($migration_done) {
        return; // Уже выполнено
    }
    
    // Запускаем миграцию только для администраторов
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Запускаем миграцию в фоне (один раз)
    // Миграция затрагивает ТОЛЬКО CPT формы типа 'codeweber_form'
    // Legacy формы (строковые ID) не мигрируются и продолжают работать
    if (class_exists('CodeweberFormsCPTMigration')) {
        $results = CodeweberFormsCPTMigration::migrate_all_forms();
        
        // Помечаем миграцию как выполненную
        update_option('codeweber_forms_cpt_migration_done', true);
        update_option('codeweber_forms_cpt_migration_results', $results);
        
        // Логируем
        error_log('Codeweber Forms: CPT migration completed automatically');
        error_log('Codeweber Forms: Legacy forms (testimonial, newsletter, etc.) are NOT migrated and continue to work as before');
    }
}
```

**Важные замечания:**
- ✅ Миграция затрагивает **только CPT формы** (тип поста `codeweber_form`)
- ✅ Legacy формы (строковые ID) **не мигрируются** и продолжают работать
- ✅ Оба варианта работают **параллельно** без конфликтов
- ✅ После миграции можно тестировать оба варианта одновременно

---

## 4. Обновление методов фильтрации по типу формы

### 4.1. Добавление фильтрации по `form_type` в `get_submissions()`

**Файл:** `codeweber-forms-database.php`

**Изменения:**
- Добавить параметр `form_type` в `$args`
- Добавить условие WHERE для фильтрации по типу

**Код:**
```php
public function get_submissions($args = []) {
    $defaults = [
        'form_id'        => '',
        'form_type'      => '', // НОВОЕ
        'status'         => '',
        // ... rest of defaults ...
    ];
    
    $where = [];
    
    // ... existing form_id logic ...
    
    // НОВОЕ: Фильтрация по типу формы
    if (!empty($args['form_type'])) {
        $where[] = $wpdb->prepare("form_type = %s", sanitize_text_field($args['form_type']));
    }
    
    // ... rest of method ...
}
```

### 4.2. Добавление фильтрации по `form_type` в `count_submissions()`

**Аналогичные изменения** в методе `count_submissions()`

---

## 5. Обновление админки для использования `form_type`

### 5.1. Добавление фильтра по типу формы в списке submissions

**Файл:** `class-codeweber-forms-list-table.php`

**Изменения:**
- Добавить выпадающий список для фильтрации по типу
- Обновить запросы для использования `form_type`

---

## 6. Тестирование миграции и обратной совместимости

### 6.1. Проверочный список - Общий функционал

- [ ] Все существующие CPT формы получили `_form_type`
- [ ] Все записи в submissions получили `form_type` (если колонка добавлена)
- [ ] Запросы с `form_id` работают корректно (VARCHAR)
- [ ] Фильтрация по `form_type` работает
- [ ] Новые формы автоматически получают `form_type` при сохранении

### 6.2. Проверочный список - Обратная совместимость (LEGACY)

**Критически важно проверить работу старых встроенных форм:**

- [ ] **Legacy форма `testimonial`** (строковый ID):
  - [ ] Форма отображается корректно
  - [ ] Отправка формы работает
  - [ ] Данные сохраняются в submissions с `form_id = 'testimonial'`
  - [ ] `form_type` автоматически устанавливается в `'testimonial'`
  - [ ] Интеграции (email, hooks) работают

- [ ] **Legacy форма `newsletter`** (строковый ID):
  - [ ] Форма отображается корректно
  - [ ] Отправка формы работает
  - [ ] Подписка на рассылку создается
  - [ ] Данные сохраняются с `form_id = 'newsletter'`
  - [ ] `form_type` автоматически устанавливается в `'newsletter'`

- [ ] **Legacy форма `resume`** (строковый ID):
  - [ ] Форма отображается корректно
  - [ ] Отправка формы работает
  - [ ] Данные сохраняются с `form_id = 'resume'`
  - [ ] `form_type` автоматически устанавливается в `'resume'`

- [ ] **Legacy форма `callback`** (строковый ID):
  - [ ] Форма отображается корректно
  - [ ] Отправка формы работает
  - [ ] Данные сохраняются с `form_id = 'callback'`
  - [ ] `form_type` автоматически устанавливается в `'callback'`

### 6.3. Проверочный список - Новый функционал (CPT формы)

- [ ] **CPT форма типа `form`** (обычная):
  - [ ] Создание формы через Gutenberg
  - [ ] Установка `formType = 'form'` в блоке
  - [ ] Сохранение `_form_type = 'form'` в мета
  - [ ] Отправка и сохранение работают
  - [ ] `form_type` в submissions = `'form'`

- [ ] **CPT форма типа `newsletter`**:
  - [ ] Создание формы через Gutenberg
  - [ ] Установка `formType = 'newsletter'` в блоке
  - [ ] Сохранение `_form_type = 'newsletter'` в мета
  - [ ] Отправка и сохранение работают
  - [ ] Подписка на рассылку создается
  - [ ] `form_type` в submissions = `'newsletter'`

- [ ] **CPT форма типа `testimonial`**:
  - [ ] Создание формы через Gutenberg
  - [ ] Установка `formType = 'testimonial'` в блоке
  - [ ] Отправка и сохранение работают
  - [ ] `form_type` в submissions = `'testimonial'`

### 6.4. Проверочный список - Параллельная работа

- [ ] Legacy форма `newsletter` и CPT форма типа `newsletter` работают одновременно
- [ ] Legacy форма `testimonial` и CPT форма типа `testimonial` работают одновременно
- [ ] Запросы к submissions работают как с legacy, так и с новыми формами
- [ ] Фильтрация по `form_type` работает для обоих типов форм
- [ ] Админка корректно отображает оба типа форм

### 6.5. Сценарии тестирования

**Сценарий 1: Отправка legacy формы**
1. Открыть страницу с `[codeweber_form id="newsletter"]`
2. Заполнить и отправить форму
3. Проверить в submissions: `form_id = 'newsletter'`, `form_type = 'newsletter'`
4. Проверить создание подписки в таблице `newsletter_subscriptions`

**Сценарий 2: Отправка новой CPT формы**
1. Создать CPT форму с типом `newsletter` через Gutenberg
2. Вставить шорткод `[codeweber_form id="123"]` (где 123 - ID CPT формы)
3. Заполнить и отправить форму
4. Проверить в submissions: `form_id = '123'`, `form_type = 'newsletter'`
5. Проверить создание подписки в таблице `newsletter_subscriptions`

**Сценарий 3: Параллельная работа**
1. На одной странице разместить:
   - Legacy форму: `[codeweber_form id="newsletter"]`
   - CPT форму: `[codeweber_form id="123"]` (тип newsletter)
2. Отправить обе формы
3. Проверить, что обе подписки созданы
4. Проверить, что в submissions обе записи имеют `form_type = 'newsletter'`

---

## 7. Порядок выполнения

1. **Исправить баги** в `get_submissions()` и `count_submissions()` (приоритет 1)
2. **Добавить колонку `form_type`** в таблицу submissions (версия 1.0.4)
3. **Обновить `save_submission()`** для автоматического определения типа
4. **Создать скрипт миграции CPT форм**
5. **Интегрировать миграцию** в CodeweberFormsCPT
6. **Добавить фильтрацию по типу** в методы базы данных
7. **Обновить админку** (опционально, можно в следующем этапе)

---

## 8. Откат изменений (если нужно)

Если что-то пойдет не так:

1. **Откат версии БД:** Вернуть `$version = '1.0.3'` в `codeweber-forms-database.php`
2. **Удаление колонки:** `ALTER TABLE wp_codeweber_forms_submissions DROP COLUMN form_type;`
3. **Откат миграции CPT:** Удалить опцию `codeweber_forms_cpt_migration_done`

---

## 9. Заметки

- Миграция должна быть **идемпотентной** (можно запускать несколько раз)
- Все изменения должны быть **обратно совместимыми**
- Использовать **транзакции** для критических операций (если возможно)
- **Логировать** все действия миграции
- Предусмотреть **ручной запуск** миграции через админку или WP-CLI

## 10. Стратегия миграции и удаления legacy кода

### 10.1. Фаза 1: Параллельная работа (ТЕКУЩАЯ)

**Цель:** Обеспечить работу старого и нового функционала одновременно

**Действия:**
- ✅ Все методы поддерживают оба варианта (legacy + новый)
- ✅ Legacy формы продолжают работать через строковые ID
- ✅ Новые CPT формы работают через `_form_type` и `formType`
- ✅ Автоматическое определение типа для обоих вариантов
- ✅ Миграция данных для существующих записей

**Продолжительность:** До завершения тестирования и отладки

### 10.2. Фаза 2: Тестирование и отладка

**Цель:** Убедиться, что все работает корректно

**Действия:**
- Провести все тесты из раздела 6
- Проверить работу legacy форм
- Проверить работу новых CPT форм
- Проверить параллельную работу
- Исправить найденные баги
- Собрать обратную связь от пользователей

**Продолжительность:** До полной уверенности в стабильности

### 10.3. Фаза 3: Удаление legacy кода (БУДУЩЕЕ)

**Цель:** Удалить старый функционал после миграции всех форм

**Действия (выполнятся в Этапе 11):**
- Мигрировать все legacy формы в CPT
- Обновить все шорткоды на новые ID
- Удалить legacy функции и классы
- Удалить проверки на строковые ID
- Упростить код, убрав fallback логику

**Важно:** Эта фаза выполняется только после:
- ✅ Полного тестирования нового функционала
- ✅ Миграции всех legacy форм в CPT
- ✅ Обновления всех шорткодов
- ✅ Подтверждения стабильности работы

### 10.4. Текущий статус

**Мы находимся в Фазе 1** - параллельная работа старого и нового функционала.

**Все изменения в Этапе 7 должны:**
- ✅ Сохранять работу legacy форм
- ✅ Добавлять поддержку нового функционала
- ✅ Не ломать существующий код
- ✅ Позволять тестировать оба варианта одновременно


