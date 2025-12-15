# Post Card Templates Structure

## Структура папок

```
templates/post-cards/
├── helpers.php          # Общие вспомогательные функции
├── post/                # Шаблоны для постов (post type: post)
│   ├── default.php
│   ├── card.php
│   ├── card-content.php
│   ├── slider.php
│   ├── default-clickable.php
│   └── overlay-5.php
└── clients/            # Шаблоны для клиентов (post type: clients)
    ├── simple.php       # Используется как 'client-simple'
    ├── grid.php         # Используется как 'client-grid'
    └── card.php         # Используется как 'client-card'
```

## Логика поиска шаблонов

Функция `cw_render_post_card()` автоматически определяет, где искать шаблон:

1. **Если имя шаблона начинается с `client-`**:
   - Ищет в папке `clients/`
   - Убирает префикс `client-` из имени файла
   - Пример: `client-simple` → `clients/simple.php`

2. **Если тип записи `clients`**:
   - Ищет в папке `clients/`
   - Использует имя шаблона как есть
   - Пример: `simple` → `clients/simple.php`

3. **Иначе**:
   - Ищет в папке `post/`
   - Пример: `default` → `post/default.php`

4. **Fallback**:
   - Если шаблон не найден в новой структуре, проверяет старую (корневая папка)
   - Затем ищет `default.php` в соответствующей папке
   - Последний fallback: `post/default.php`

## Использование

### Для постов:
```php
// Использует post/default.php
cw_render_post_card($post, 'default');

// Использует post/card.php
cw_render_post_card($post, 'card');
```

### Для клиентов:
```php
// Использует clients/simple.php
cw_render_post_card($client, 'client-simple');

// Или можно использовать без префикса, если тип записи clients
cw_render_post_card($client, 'simple');
```

## Добавление новых шаблонов

### Для постов:
1. Создайте файл в `templates/post-cards/post/new-template.php`
2. Используйте: `cw_render_post_card($post, 'new-template')`

### Для клиентов:
1. Создайте файл в `templates/post-cards/clients/new-template.php`
2. Используйте: `cw_render_post_card($client, 'client-new-template')`
   - Или: `cw_render_post_card($client, 'new-template')` (если тип записи clients)

## Обратная совместимость

Старые вызовы продолжают работать:
- `client-simple` → автоматически ищет в `clients/simple.php`
- `default` → автоматически ищет в `post/default.php`

Если файл не найден в новой структуре, функция проверяет старую структуру (корневая папка) для обратной совместимости.








