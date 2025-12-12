# План создания Demo данных для CPT Clients

## Цель
Создать систему для генерации demo данных (записей) для CPT `clients` с использованием изображений из папки `src/assets/img/brands/`.

## Ограничения
- **Нельзя править существующий код** (файлы CPT, Redux и т.д.)
- Все изменения должны быть в новых файлах

---

## Структура решения

### 1. Структура папок

```
wp-content/themes/codeweber/
├── demo/                          # Новая папка для demo данных
│   ├── clients/                   # Данные для CPT clients
│   │   ├── data.json              # JSON файл с данными клиентов
│   │   └── README.md              # Описание структуры данных
│   └── README.md                  # Общее описание системы demo
├── functions/
│   └── demo/                      # Новая папка для функций demo
│       ├── demo-clients.php       # Функции для создания demo clients
│       └── demo-ajax.php          # AJAX обработчики
└── redux-framework/sample/sections/codeweber/
    └── demo.php                   # Обновить: добавить кнопку
```

---

## 2. Структура JSON файла (`demo/clients/data.json`)

```json
{
  "version": "1.0.0",
  "post_type": "clients",
  "description": "Demo данные для CPT Clients",
  "items": [
    {
      "id": 1,
      "title": "Client Company 1",
      "slug": "client-company-1",
      "status": "publish",
      "image": "c1.png",
      "image_alt": "Client Company 1 Logo",
      "order": 1
    },
    {
      "id": 2,
      "title": "Client Company 2",
      "slug": "client-company-2",
      "status": "publish",
      "image": "c2.png",
      "image_alt": "Client Company 2 Logo",
      "order": 2
    }
    // ... остальные клиенты
  ]
}
```

**Примечание:** 
- `image` - имя файла из папки `src/assets/img/brands/`
- Можно добавить дополнительные поля (URL, описание и т.д.)

---

## 3. Файлы для создания

### 3.1. `functions/demo/demo-clients.php`

**Назначение:** Основные функции для создания demo записей clients

**Функции:**
- `cw_demo_get_clients_data()` - загрузка данных из JSON
- `cw_demo_create_client_post()` - создание одной записи client
- `cw_demo_import_client_image()` - импорт изображения в медиабиблиотеку
- `cw_demo_create_clients()` - основная функция создания всех клиентов
- `cw_demo_delete_clients()` - удаление всех demo клиентов (опционально)

**Логика:**
1. Читать JSON файл из `demo/clients/data.json`
2. Для каждого элемента:
   - Создать запись типа `clients` через `wp_insert_post()`
   - Скопировать изображение из `src/assets/img/brands/` в медиабиблиотеку
   - Установить изображение как featured image для записи
   - Установить правильный размер изображения (через систему размеров темы)

### 3.2. `functions/demo/demo-ajax.php`

**Назначение:** AJAX обработчики для кнопки в Redux

**Функции:**
- `cw_demo_ajax_create_clients()` - AJAX обработчик для создания клиентов
- `cw_demo_ajax_delete_clients()` - AJAX обработчик для удаления (опционально)

**Безопасность:**
- Проверка `current_user_can('manage_options')`
- Проверка nonce
- Валидация данных

### 3.3. Обновление `redux-framework/sample/sections/codeweber/demo.php`

**Изменения:**
- Добавить кнопку типа `raw` с JavaScript для AJAX запроса
- Или использовать поле типа `button` (если доступно в Redux)
- Добавить индикатор загрузки
- Показать результат (успех/ошибка)

---

## 4. Процесс работы

### 4.1. Создание demo данных

```
Пользователь нажимает кнопку "Создать Demo Clients"
    ↓
AJAX запрос → wp_ajax_cw_demo_create_clients
    ↓
cw_demo_create_clients()
    ↓
1. Читает demo/clients/data.json
2. Для каждого клиента:
   a. Создает запись через wp_insert_post()
   b. Импортирует изображение через cw_demo_import_client_image()
   c. Устанавливает featured image
   d. Генерирует размеры изображений (через систему темы)
    ↓
Возвращает результат (количество созданных записей)
```

### 4.2. Импорт изображения

```
cw_demo_import_client_image($image_filename, $post_id)
    ↓
1. Проверяет существование файла в src/assets/img/brands/
2. Копирует файл во временную папку
3. Использует wp_handle_upload() для загрузки в медиабиблиотеку
4. Создает attachment через wp_insert_attachment()
5. Генерирует метаданные через wp_generate_attachment_metadata()
6. Устанавливает post_parent = $post_id (для правильной работы системы размеров)
7. Устанавливает featured image через set_post_thumbnail()
```

---

## 5. Подключение файлов

### 5.1. В `functions.php`

```php
// Подключение demo функций
require_once get_template_directory() . '/functions/demo/demo-clients.php';
require_once get_template_directory() . '/functions/demo/demo-ajax.php';
```

**Важно:** Добавить в конец файла, чтобы не конфликтовать с существующим кодом.

---

## 6. Структура JSON данных (детально)

### 6.1. Полный пример `demo/clients/data.json`

```json
{
  "version": "1.0.0",
  "post_type": "clients",
  "description": "Demo данные для CPT Clients",
  "source_images": "src/assets/img/brands/",
  "items": [
    {
      "title": "TechCorp Solutions",
      "slug": "techcorp-solutions",
      "status": "publish",
      "image": "c1.png",
      "image_alt": "TechCorp Solutions Logo",
      "order": 1,
      "meta": {
        "website": "https://example.com",
        "description": "Leading technology solutions provider"
      }
    },
    {
      "title": "Global Industries",
      "slug": "global-industries",
      "status": "publish",
      "image": "c2.png",
      "image_alt": "Global Industries Logo",
      "order": 2
    }
    // ... до 19 клиентов (c1-c11, z1-z8)
  ]
}
```

---

## 7. Безопасность

### 7.1. Проверки в AJAX обработчике

```php
// Проверка прав
if (!current_user_can('manage_options')) {
    wp_send_json_error(['message' => 'Недостаточно прав']);
}

// Проверка nonce
if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cw_demo_create_clients')) {
    wp_send_json_error(['message' => 'Ошибка безопасности']);
}

// Проверка существования JSON файла
$json_path = get_template_directory() . '/demo/clients/data.json';
if (!file_exists($json_path)) {
    wp_send_json_error(['message' => 'Файл данных не найден']);
}
```

### 7.2. Валидация данных из JSON

- Проверка структуры JSON
- Валидация имен файлов изображений
- Проверка существования файлов изображений
- Санитизация данных перед вставкой в БД

---

## 8. Обработка ошибок

### 8.1. Типы ошибок

1. **Файл данных не найден** - показать сообщение пользователю
2. **Изображение не найдено** - пропустить этот элемент, продолжить
3. **Ошибка создания записи** - логировать, продолжить
4. **Ошибка импорта изображения** - логировать, продолжить

### 8.2. Логирование

```php
// Использовать error_log() для отладки
error_log('Demo Clients: Создано записей: ' . $created_count);
error_log('Demo Clients: Ошибки: ' . print_r($errors, true));
```

---

## 9. UI/UX в Redux

### 9.1. Кнопка в секции Demo

```php
array(
    'id'      => 'demo-create-clients-btn',
    'type'    => 'raw',
    'content' => '
        <div class="demo-controls">
            <button id="cw-demo-create-clients" class="button button-primary">
                Создать Demo Clients
            </button>
            <span id="cw-demo-status" class="demo-status"></span>
        </div>
        <script>
        // JavaScript для AJAX запроса
        </script>
    ',
)
```

### 9.2. Индикатор загрузки

- Показывать спиннер при выполнении
- Блокировать кнопку во время выполнения
- Показывать прогресс (если возможно)

### 9.3. Результат

- Успех: "Создано X записей клиентов"
- Ошибка: "Ошибка: [описание]"
- Частичный успех: "Создано X из Y записей. Ошибки: [список]"

---

## 10. Дополнительные возможности

### 10.1. Удаление demo данных

- Кнопка "Удалить Demo Clients"
- Удаление только записей, созданных через demo систему
- Маркировка записей (meta поле `_demo_created = true`)

### 10.2. Проверка существующих данных

- Перед созданием проверять, есть ли уже demo данные
- Предупреждение: "Demo данные уже существуют. Пересоздать?"

### 10.3. Выборочное создание

- Чекбоксы для выбора, какие клиенты создавать
- Или создавать все сразу

---

## 11. Файлы для создания (список)

1. ✅ `demo/clients/data.json` - JSON с данными клиентов
2. ✅ `demo/clients/README.md` - Описание структуры данных
3. ✅ `demo/README.md` - Общее описание системы
4. ✅ `functions/demo/demo-clients.php` - Основные функции
5. ✅ `functions/demo/demo-ajax.php` - AJAX обработчики
6. ✅ Обновить `redux-framework/sample/sections/codeweber/demo.php` - Добавить кнопку

---

## 12. Порядок реализации

1. **Создать структуру папок** (`demo/clients/`, `functions/demo/`)
2. **Создать JSON файл** с данными для всех 19 изображений (c1-c11, z1-z8)
3. **Создать функции импорта** (`demo-clients.php`)
4. **Создать AJAX обработчики** (`demo-ajax.php`)
5. **Обновить Redux секцию** (добавить кнопку и JavaScript)
6. **Подключить файлы** в `functions.php`
7. **Протестировать** создание demo данных

---

## 13. Примеры кода (ключевые функции)

### 13.1. Загрузка данных из JSON

```php
function cw_demo_get_clients_data() {
    $json_path = get_template_directory() . '/demo/clients/data.json';
    
    if (!file_exists($json_path)) {
        return false;
    }
    
    $json_content = file_get_contents($json_path);
    $data = json_decode($json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }
    
    return $data;
}
```

### 13.2. Импорт изображения

```php
function cw_demo_import_client_image($image_filename, $post_id) {
    $source_path = get_template_directory() . '/src/assets/img/brands/' . $image_filename;
    
    if (!file_exists($source_path)) {
        return false;
    }
    
    // Копируем во временную папку
    $upload_dir = wp_upload_dir();
    $temp_file = $upload_dir['path'] . '/' . $image_filename;
    copy($source_path, $temp_file);
    
    // Создаем attachment
    $file_array = [
        'name' => $image_filename,
        'tmp_name' => $temp_file,
    ];
    
    $attachment_id = media_handle_sideload($file_array, $post_id);
    
    if (is_wp_error($attachment_id)) {
        return false;
    }
    
    // Устанавливаем featured image
    set_post_thumbnail($post_id, $attachment_id);
    
    return $attachment_id;
}
```

---

## 14. Тестирование

### 14.1. Чек-лист

- [ ] JSON файл корректно читается
- [ ] Записи создаются с правильными данными
- [ ] Изображения импортируются в медиабиблиотеку
- [ ] Featured image устанавливается корректно
- [ ] Размеры изображений генерируются (через систему темы)
- [ ] AJAX запрос работает
- [ ] Обработка ошибок работает
- [ ] UI показывает правильные сообщения

---

## Заключение

Этот план позволяет создать систему demo данных **без изменения существующего кода**, используя только новые файлы и подключение их в `functions.php`. Все функции изолированы и не конфликтуют с существующим функционалом.





