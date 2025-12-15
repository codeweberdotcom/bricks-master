# Создание новых Custom Post Types (CPT)

Это руководство описывает процесс создания новых типов записей (CPT) в теме Codeweber.

## 📁 Структура файлов

Все файлы CPT находятся в папке `functions/cpt/`. Каждый CPT должен иметь свой отдельный файл с именем `cpt-{название}.php`.

**Пример:** `functions/cpt/cpt-staff.php`, `functions/cpt/cpt-vacancies.php`

## 🔧 Шаги создания нового CPT

### 1. Создание файла CPT

Создайте новый файл в папке `functions/cpt/` с именем `cpt-{название}.php`.

**Пример:** Для CPT "services" создайте файл `functions/cpt/cpt-services.php`

### 2. Регистрация CPT

Используйте функцию `register_post_type()` для регистрации нового типа записи:

```php
<?php

function cptui_register_my_cpts_services()
{
    /**
     * Post Type: Services.
     */
    $labels = [
        "name" => esc_html__("Services", "codeweber"),
        "singular_name" => esc_html__("Service", "codeweber"),
        "menu_name" => esc_html__("Services", "codeweber"),
        "all_items" => esc_html__("All Services", "codeweber"),
        "add_new" => esc_html__("Add Service", "codeweber"),
        "add_new_item" => esc_html__("Add New Service", "codeweber"),
        "edit_item" => esc_html__("Edit Service", "codeweber"),
        "new_item" => esc_html__("New Service", "codeweber"),
        "view_item" => esc_html__("View Service", "codeweber"),
        "view_items" => esc_html__("View Services", "codeweber"),
        "search_items" => esc_html__("Search Services", "codeweber"),
        "not_found" => esc_html__("No Services found", "codeweber"),
        "not_found_in_trash" => esc_html__("No Services found in Trash", "codeweber"),
        "archives" => esc_html__("Services archive", "codeweber"),
        "items_list" => esc_html__("Services list", "codeweber"),
    ];

    $args = [
        "label" => esc_html__("Services", "codeweber"),
        "labels" => $labels,
        "description" => "",
        "public" => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => true,
        "rest_base" => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "rest_namespace" => "wp/v2",
        "has_archive" => true, // или "services" для кастомного slug
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "delete_with_user" => false,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "can_export" => true,
        "rewrite" => ["slug" => "services", "with_front" => true],
        "query_var" => true,
        "supports" => ["title", "thumbnail", "editor", "revisions"],
        "show_in_graphql" => false,
        "menu_icon" => "dashicons-admin-tools", // Опционально: иконка в меню
    ];

    register_post_type("services", $args);
}

add_action('init', 'cptui_register_my_cpts_services');
```

### 3. Подключение файла в functions.php

Добавьте подключение вашего файла CPT в `functions.php`:

```php
require_once get_template_directory() . '/functions/cpt/cpt-services.php';
```

**Важно:** Подключение должно быть до инициализации Redux Framework (до строки 68).

### 4. Регистрация таксономий (опционально)

Если ваш CPT требует категорий или тегов, зарегистрируйте таксономию:

```php
function cptui_register_my_taxes_service_categories()
{
    $labels = [
        "name" => esc_html__("Service Categories", "codeweber"),
        "singular_name" => esc_html__("Service Category", "codeweber"),
        "menu_name" => esc_html__("Service Categories", "codeweber"),
        "all_items" => esc_html__("All Service Categories", "codeweber"),
        "edit_item" => esc_html__("Edit Service Category", "codeweber"),
        "view_item" => esc_html__("View Service Category", "codeweber"),
        "update_item" => esc_html__("Update Service Category", "codeweber"),
        "add_new_item" => esc_html__("Add New Service Category", "codeweber"),
        "new_item_name" => esc_html__("New Service Category Name", "codeweber"),
        "search_items" => esc_html__("Search Service Categories", "codeweber"),
    ];

    $args = [
        "label" => esc_html__("Service Categories", "codeweber"),
        "labels" => $labels,
        "public" => true,
        "publicly_queryable" => true,
        "hierarchical" => true, // true для категорий, false для тегов
        "show_ui" => true,
        "show_in_menu" => true,
        "show_in_nav_menus" => true,
        "query_var" => true,
        "rewrite" => ['slug' => 'service-categories', 'with_front' => true],
        "show_admin_column" => true,
        "show_in_rest" => true,
        "rest_base" => "service_categories",
        "rest_controller_class" => "WP_REST_Terms_Controller",
    ];

    register_taxonomy("service_categories", ["services"], $args);
}

add_action('init', 'cptui_register_my_taxes_service_categories');
```

### 5. Отключение Gutenberg (опционально)

Если вы хотите использовать классический редактор вместо Gutenberg:

```php
add_filter('use_block_editor_for_post_type', 'disable_gutenberg_for_services', 10, 2);
function disable_gutenberg_for_services($current_status, $post_type)
{
    if ($post_type === 'services') {
        return false;
    }
    return $current_status;
}
```

## 📋 Основные параметры CPT

### Важные параметры `$args`:

- **`public`** - Публичный доступ к записям
- **`has_archive`** - Включить архив страницу (true или slug)
- **`rewrite`** - Настройки URL (slug для архива)
- **`supports`** - Поддерживаемые функции:
  - `title` - Заголовок
  - `editor` - Редактор контента
  - `thumbnail` - Миниатюра
  - `excerpt` - Краткое описание
  - `revisions` - Ревизии
  - `author` - Автор
  - `comments` - Комментарии

### Примеры slug для разных CPT:

- `staff` → `/staff/`
- `vacancies` → `/vacancies/`
- `services` → `/services/`

## 🔗 Интеграция с Redux Framework

После создания CPT, он автоматически появится в настройках Redux Framework (если используется система `redux_cpt.php`), где можно:

- Включить/выключить CPT
- Настроить шаблоны архивов
- Настроить шаблоны single страниц
- Настроить позицию сайдбаров

## ✅ Проверка работы

1. Перейдите в админ-панель WordPress
2. В меню должен появиться новый пункт с вашим CPT
3. Создайте тестовую запись
4. Проверьте, что архив доступен по URL: `yoursite.com/services/`
5. Проверьте, что single страница доступна: `yoursite.com/services/test-service/`

## 📝 Примеры существующих CPT

Изучите примеры в теме:

- **Staff:** `functions/cpt/cpt-staff.php`
- **Vacancies:** `functions/cpt/cpt-vacancies.php`
- **Testimonials:** `functions/cpt/cpt-testimonials.php`
- **FAQ:** `functions/cpt/cpt-faq.php`
- **Clients:** `functions/cpt/cpt-clients.php`

## 🎯 Следующие шаги

После создания CPT:

1. Создайте архивный шаблон (см. [ARCHIVE_TEMPLATES.md](ARCHIVE_TEMPLATES.md))
2. Создайте single шаблон (см. [SINGLE_TEMPLATES.md](SINGLE_TEMPLATES.md))
3. Добавьте метаполя (см. [METAFIELDS.md](METAFIELDS.md))
4. Зарегистрируйте сайдбары (см. [SIDEBARS.md](SIDEBARS.md))

---

**Последнее обновление:** 2024-12-13




