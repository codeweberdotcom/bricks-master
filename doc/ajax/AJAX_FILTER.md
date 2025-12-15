# Универсальный AJAX Фильтр

## Обзор

Универсальный AJAX фильтр - это система фильтрации контента без перезагрузки страницы. Фильтр поддерживает различные типы записей WordPress: вакансии, статьи, товары WooCommerce, staff и другие.

## Основные возможности

- ✅ Фильтрация без перезагрузки страницы
- ✅ Поддержка множественных типов контента
- ✅ Автоматическая инициализация форм
- ✅ Обновление URL без перезагрузки
- ✅ Индикатор загрузки
- ✅ Обработка ошибок
- ✅ Гибкая настройка через data-атрибуты
- ✅ Поддержка различных типов полей (select, input, checkbox, radio)

## Архитектура

Система состоит из двух основных компонентов:

1. **PHP обработчик** (`functions/ajax-filter.php`) - серверная логика фильтрации
2. **JavaScript модуль** (`src/assets/js/ajax-filter.js`) - клиентская логика

## Структура файлов

```
wp-content/themes/codeweber/
├── functions/
│   └── ajax-filter.php          # PHP обработчик AJAX запросов
├── src/assets/js/
│   └── ajax-filter.js           # JavaScript модуль фильтрации
└── dist/assets/js/
    └── ajax-filter.js           # Скомпилированная версия
```

## Установка и настройка

### 1. Подключение файлов

Файлы автоматически подключаются через `functions.php` и `enqueues.php`:

```php
// functions.php
require_once get_template_directory() . '/functions/ajax-filter.php';

// enqueues.php
function codeweber_enqueue_ajax_filter() {
    // Автоматическое подключение скрипта
}
add_action('wp_enqueue_scripts', 'codeweber_enqueue_ajax_filter', 20);
```

### 2. Инициализация на странице

Фильтр автоматически инициализируется для всех форм с классом `.codeweber-filter-form` при загрузке страницы.

## Использование

### Базовый пример

```html
<form class="codeweber-filter-form" 
      data-post-type="vacancies" 
      data-template="vacancies_1" 
      data-container=".vacancies-results">
  <select name="position" data-filter-name="position">
    <option value="">Все позиции</option>
    <option value="1">Design</option>
    <option value="2">Development</option>
  </select>
  
  <select name="type" data-filter-name="type">
    <option value="">Все типы</option>
    <option value="full-time">Полная занятость</option>
    <option value="part-time">Частичная занятость</option>
  </select>
</form>

<div class="vacancies-results">
  <!-- Результаты фильтрации появятся здесь -->
</div>
```

### Обязательные атрибуты формы

| Атрибут | Описание | Обязательный |
|---------|----------|--------------|
| `class="codeweber-filter-form"` | Класс для инициализации фильтра | ✅ Да |
| `data-post-type` | Тип записи WordPress (`vacancies`, `post`, `products`, `staff`) | ✅ Да |
| `data-container` | CSS селектор контейнера для результатов | ✅ Да |
| `data-template` | Название шаблона для рендеринга (опционально) | ❌ Нет |

### Атрибуты полей фильтрации

| Атрибут | Описание | Пример |
|---------|----------|--------|
| `data-filter-name` | Имя фильтра (используется как ключ в AJAX запросе) | `data-filter-name="position"` |
| `name` | Альтернатива `data-filter-name`, используется если `data-filter-name` отсутствует | `name="position"` |

### Поддерживаемые типы полей

#### Select

```html
<select name="category" data-filter-name="category">
  <option value="">Все категории</option>
  <option value="1">Категория 1</option>
  <option value="2">Категория 2</option>
</select>
```

Фильтрация происходит при изменении значения (`change` событие).

#### Input (текст/число)

```html
<input type="text" name="search" data-filter-name="search" placeholder="Поиск...">
<input type="number" name="price_min" data-filter-name="price_min" placeholder="Мин. цена">
```

Для текстовых полей используется debounce (задержка 500ms) для оптимизации запросов.

#### Checkbox

```html
<input type="checkbox" name="featured" data-filter-name="featured" value="1">
<label>Только избранное</label>
```

#### Radio

```html
<input type="radio" name="status" data-filter-name="status" value="active">
<label>Активные</label>
<input type="radio" name="status" data-filter-name="status" value="inactive">
<label>Неактивные</label>
```

## Типы контента

### Вакансии (vacancies)

#### Поддерживаемые фильтры

- `position` - ID термина таксономии `vacancy_type`
- `type` - Тип занятости (`full-time`, `part-time`, `remote`, `contract`)
- `location` - Локация вакансии (мета-поле `_vacancy_location`)

#### Пример использования

```html
<form class="codeweber-filter-form" 
      data-post-type="vacancies" 
      data-template="vacancies_1" 
      data-container=".vacancies-results">
  <select name="position" data-filter-name="position">
    <option value="">Все позиции</option>
    <?php 
    $types = get_terms(['taxonomy' => 'vacancy_type', 'hide_empty' => true]);
    foreach ($types as $type) : 
    ?>
      <option value="<?php echo $type->term_id; ?>"><?php echo $type->name; ?></option>
    <?php endforeach; ?>
  </select>
  
  <select name="type" data-filter-name="type">
    <option value="">Все типы</option>
    <option value="full-time">Полная занятость</option>
    <option value="part-time">Частичная занятость</option>
    <option value="remote">Удаленно</option>
  </select>
  
  <select name="location" data-filter-name="location">
    <option value="">Все локации</option>
    <!-- Опции локаций -->
  </select>
</form>
```

#### Шаблоны рендеринга

- `vacancies_1` - Список вакансий, сгруппированных по типам

### Статьи (post)

#### Поддерживаемые фильтры

- `category` - ID категории
- `tag` - ID тега

#### Пример использования

```html
<form class="codeweber-filter-form" 
      data-post-type="post" 
      data-container=".posts-results">
  <select name="category" data-filter-name="category">
    <option value="">Все категории</option>
    <?php 
    $categories = get_categories();
    foreach ($categories as $category) : 
    ?>
      <option value="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></option>
    <?php endforeach; ?>
  </select>
</form>
```

### Товары WooCommerce (products)

#### Поддерживаемые фильтры

- `category` - ID категории товара (`product_cat`)
- `tag` - ID тега товара (`product_tag`)
- `price_min` - Минимальная цена
- `price_max` - Максимальная цена

#### Пример использования

```html
<form class="codeweber-filter-form" 
      data-post-type="products" 
      data-container=".products-results">
  <select name="category" data-filter-name="category">
    <option value="">Все категории</option>
    <!-- Категории товаров -->
  </select>
  
  <input type="number" name="price_min" data-filter-name="price_min" placeholder="От">
  <input type="number" name="price_max" data-filter-name="price_max" placeholder="До">
</form>
```

### Staff

#### Поддерживаемые фильтры

- `department` - ID термина таксономии `staff_department`

## PHP API

### Основная функция обработки

```php
function codeweber_ajax_filter()
```

Обрабатывает AJAX запросы фильтрации.

**Хуки:**
- `wp_ajax_codeweber_filter` - для авторизованных пользователей
- `wp_ajax_nopriv_codeweber_filter` - для неавторизованных пользователей

**Параметры запроса:**
- `nonce` - WordPress nonce для безопасности
- `post_type` - Тип записи
- `template` - Название шаблона (опционально)
- `container_selector` - Селектор контейнера (опционально)
- `filters` - JSON строка с параметрами фильтрации

**Ответ:**
```json
{
  "success": true,
  "data": {
    "html": "<div>...</div>",
    "found_posts": 10
  }
}
```

### Функции применения фильтров

#### Для вакансий

```php
function codeweber_apply_vacancy_filters($args, $filters)
```

Применяет фильтры к запросу вакансий через `WP_Query`.

**Параметры:**
- `$args` (array) - Аргументы для `WP_Query`
- `$filters` (array) - Массив фильтров

**Возвращает:**
- `array` - Модифицированные аргументы для `WP_Query`

#### Для статей

```php
function codeweber_apply_post_filters($args, $filters)
```

#### Для товаров

```php
function codeweber_apply_product_filters($args, $filters)
```

**Требования:** WooCommerce должен быть активен.

#### Для staff

```php
function codeweber_apply_staff_filters($args, $filters)
```

### Функции рендеринга

#### Рендеринг вакансий

```php
function codeweber_render_vacancies_filtered($query, $filters)
```

Генерирует HTML для отфильтрованных вакансий по шаблону `vacancies_1`.

**Параметры:**
- `$query` (WP_Query) - Объект запроса с результатами
- `$filters` (array) - Примененные фильтры

## JavaScript API

### Инициализация

Фильтр автоматически инициализируется при загрузке DOM:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Автоматическая инициализация всех форм с классом .codeweber-filter-form
});
```

### События

#### codeweber:filter:updated

Событие вызывается после успешного обновления результатов фильтрации.

```javascript
document.addEventListener('codeweber:filter:updated', function(event) {
    const { postType, filters, foundPosts } = event.detail;
    console.log('Filter updated:', postType, filters, foundPosts);
});
```

**Свойства события:**
- `postType` (string) - Тип записи
- `filters` (object) - Примененные фильтры
- `foundPosts` (number) - Количество найденных записей

### Локализация

JavaScript использует объект `codeweberFilter` для доступа к данным:

```javascript
codeweberFilter = {
    ajaxUrl: '/wp-admin/admin-ajax.php',
    nonce: '...',
    translations: {
        error: 'Error',
        loading: 'Loading...'
    }
}
```

## Примеры использования

### Пример 1: Простой фильтр вакансий

```php
// В шаблоне archive-vacancies.php или single-vacancies.php
?>
<form class="codeweber-filter-form" 
      data-post-type="vacancies" 
      data-template="vacancies_1" 
      data-container=".vacancies-results">
  <div class="row">
    <div class="col-md-4">
      <select class="form-select" name="position" data-filter-name="position">
        <option value="">Все позиции</option>
        <?php 
        $types = get_terms(['taxonomy' => 'vacancy_type', 'hide_empty' => true]);
        foreach ($types as $type) : 
        ?>
          <option value="<?php echo esc_attr($type->term_id); ?>">
            <?php echo esc_html($type->name); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <select class="form-select" name="type" data-filter-name="type">
        <option value="">Все типы</option>
        <option value="full-time">Полная занятость</option>
        <option value="part-time">Частичная занятость</option>
        <option value="remote">Удаленно</option>
      </select>
    </div>
  </div>
</form>

<div class="vacancies-results">
  <!-- Изначальный контент или пусто -->
</div>
```

### Пример 2: Комбинированный фильтр с поиском

```html
<form class="codeweber-filter-form" 
      data-post-type="post" 
      data-container=".posts-results">
  <div class="row">
    <div class="col-md-4">
      <select name="category" data-filter-name="category">
        <option value="">Все категории</option>
        <!-- Категории -->
      </select>
    </div>
    <div class="col-md-4">
      <input type="text" name="search" data-filter-name="search" placeholder="Поиск...">
    </div>
    <div class="col-md-4">
      <input type="checkbox" name="featured" data-filter-name="featured" value="1">
      <label>Только избранное</label>
    </div>
  </div>
</form>
```

### Пример 3: Кастомный обработчик результата

```javascript
document.addEventListener('codeweber:filter:updated', function(event) {
    const { postType, filters, foundPosts } = event.detail;
    
    // Обновляем счетчик результатов
    const counter = document.querySelector('.results-count');
    if (counter) {
        counter.textContent = `Найдено: ${foundPosts}`;
    }
    
    // Прокручиваем к результатам
    const container = document.querySelector('.vacancies-results');
    if (container) {
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});
```

## Кастомизация

### Добавление нового типа контента

#### 1. Добавить функцию применения фильтров

В `functions/ajax-filter.php`:

```php
function codeweber_apply_custom_post_type_filters($args, $filters) {
    $meta_query = array();
    $tax_query = array();
    
    // Пример: фильтр по мета-полю
    if (!empty($filters['custom_field'])) {
        $meta_query[] = array(
            'key' => '_custom_field',
            'value' => sanitize_text_field($filters['custom_field']),
            'compare' => '='
        );
    }
    
    // Пример: фильтр по таксономии
    if (!empty($filters['custom_taxonomy'])) {
        $tax_query[] = array(
            'taxonomy' => 'custom_taxonomy',
            'field' => 'term_id',
            'terms' => intval($filters['custom_taxonomy']),
        );
    }
    
    if (!empty($meta_query)) {
        if (count($meta_query) > 1) {
            $meta_query['relation'] = 'AND';
        }
        $args['meta_query'] = $meta_query;
    }
    
    if (!empty($tax_query)) {
        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }
        $args['tax_query'] = $tax_query;
    }
    
    return $args;
}
```

#### 2. Добавить в основную функцию обработки

```php
function codeweber_ajax_filter() {
    // ...
    
    // Добавить в список разрешенных типов
    $allowed_post_types = array('post', 'vacancies', 'products', 'staff', 'custom_post_type');
    
    // Добавить обработку
    if ($post_type === 'custom_post_type') {
        $args = codeweber_apply_custom_post_type_filters($args, $filters);
    }
    
    // ...
}
```

#### 3. Добавить функцию рендеринга (опционально)

```php
function codeweber_render_custom_post_type_filtered($query, $filters, $template) {
    // Кастомная логика рендеринга
    while ($query->have_posts()) {
        $query->the_post();
        // Ваш HTML
    }
}
```

### Кастомизация шаблонов рендеринга

Вы можете создать собственные шаблоны рендеринга, добавив проверку в `codeweber_ajax_filter()`:

```php
if ($post_type === 'vacancies' && $template === 'custom_template') {
    codeweber_render_custom_vacancies_template($query, $filters);
}
```

## Отладка

### Включение логирования

В PHP обработчике можно добавить логирование:

```php
function codeweber_ajax_filter() {
    error_log('Filter request: ' . print_r($_POST, true));
    // ...
}
```

### Проверка в консоли браузера

Откройте консоль разработчика (F12) и проверьте:

1. Загружен ли скрипт: `typeof codeweberFilter !== 'undefined'`
2. Инициализированы ли формы: `document.querySelectorAll('.codeweber-filter-form').length`
3. AJAX запросы в вкладке Network

### Типичные проблемы

#### Фильтр не работает

1. Проверьте, что форма имеет класс `.codeweber-filter-form`
2. Убедитесь, что указан `data-post-type`
3. Проверьте, что контейнер существует и указан правильно в `data-container`
4. Проверьте консоль на наличие JavaScript ошибок

#### Результаты не обновляются

1. Проверьте, что поля имеют `data-filter-name` или `name`
2. Убедитесь, что значения фильтров не пустые (пустые значения игнорируются)
3. Проверьте ответ сервера в Network вкладке

#### Неправильные результаты

1. Проверьте логику в функции применения фильтров
2. Убедитесь, что `WP_Query` аргументы формируются правильно
3. Проверьте, что meta_query и tax_query настроены корректно

## Производительность

### Оптимизация запросов

1. **Используйте индексы** для часто фильтруемых полей
2. **Ограничивайте posts_per_page** вместо использования `-1` для больших наборов данных
3. **Кэшируйте результаты** для часто используемых фильтров
4. **Используйте debounce** для текстовых полей (уже реализовано)

### Рекомендации

- Не используйте `posts_per_page => -1` для больших наборов данных
- Добавьте пагинацию для результатов
- Используйте транзиенты WordPress для кэширования

## Безопасность

### Nonce проверка

Все AJAX запросы проверяются через WordPress nonce:

```php
if (!wp_verify_nonce($_POST['nonce'], 'codeweber_filter_nonce')) {
    wp_send_json_error(['message' => 'Security check failed']);
}
```

### Санитизация данных

Все входные данные санитизируются:

```php
$post_type = sanitize_text_field($_POST['post_type']);
$filters = json_decode(stripslashes($_POST['filters']), true);
// Далее значения фильтров также санитизируются
```

### Валидация типов записей

Разрешенные типы записей проверяются:

```php
$allowed_post_types = array('post', 'vacancies', 'products', 'staff');
if (!in_array($post_type, $allowed_post_types)) {
    wp_send_json_error(['message' => 'Invalid post type']);
}
```

## Changelog

### Версия 1.0.0 (2024-12-13)

- ✅ Первая версия универсального AJAX фильтра
- ✅ Поддержка вакансий, статей, товаров WooCommerce, staff
- ✅ Автоматическая инициализация форм
- ✅ Поддержка различных типов полей
- ✅ Обновление URL без перезагрузки
- ✅ Индикатор загрузки
- ✅ Обработка ошибок

## Лицензия

Код является частью темы Codeweber и следует лицензии темы.

## Автор

Разработано для темы Codeweber.




