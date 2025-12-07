# План создания шаблонов карточек для CPT Clients

## Анализ требований

### Особенности CPT Clients:
- Поддерживает только `title` (нет excerpt, категорий, даты, комментариев)
- Изображение - это логотип компании (не фото поста)
- Размеры изображений: 200x60px, 300x200px, 400x267px
- Варианты использования из Sandbox:
  1. **Swiper Slider** - просто логотип в слайдере
  2. **Grid Layout** - логотип в figure с padding
  3. **Grid с карточками** - логотип в карточке с тенью

---

## Варианты реализации

### Вариант 1: Расширить существующую систему (РЕКОМЕНДУЕТСЯ)

**Преимущества:**
- ✅ Использует существующую инфраструктуру
- ✅ Не дублирует код
- ✅ Единый интерфейс для всех типов записей
- ✅ Легко поддерживать

**Структура:**
```
templates/post-cards/
├── client-simple.php      # Просто логотип (для Swiper)
├── client-grid.php        # Логотип в figure (для Grid)
└── client-card.php        # Логотип в карточке (для Grid с карточками)
```

**Использование:**
```php
// Система автоматически определит тип записи
echo cw_render_post_card($client, 'client-simple', [
    'show_title' => false,  // Для clients обычно не нужен title
    'show_date' => false,
    'show_category' => false,
    'show_comments' => false,
], [
    'image_size' => 'codeweber_clients_200-60',
]);
```

**Адаптация helpers:**
- `cw_get_post_card_data()` уже работает с любыми типами записей
- Для clients можно добавить проверку и упростить данные

---

### Вариант 2: Отдельная система для Clients

**Преимущества:**
- ✅ Полная изоляция
- ✅ Специализированные функции
- ✅ Не влияет на существующую систему

**Недостатки:**
- ❌ Дублирование кода
- ❌ Два интерфейса для поддержки

**Структура:**
```
templates/client-cards/
├── simple.php
├── grid.php
└── card.php

functions/
└── client-card-templates.php
```

**Использование:**
```php
echo cw_render_client_card($client, 'simple', [
    'image_size' => 'codeweber_clients_200-60',
]);
```

---

### Вариант 3: Гибридный подход

**Идея:**
- Использовать существующую систему `cw_render_post_card()`
- Создать отдельную папку `templates/client-cards/` для изоляции
- Адаптировать функцию для поиска шаблонов в обеих папках

**Структура:**
```
templates/
├── post-cards/          # Шаблоны для постов
│   ├── default.php
│   └── ...
└── client-cards/        # Шаблоны для клиентов
    ├── simple.php
    ├── grid.php
    └── card.php
```

**Логика поиска:**
```php
// В cw_render_post_card()
if ($post->post_type === 'clients') {
    $template_path = get_template_directory() . '/templates/client-cards/' . $template_name . '.php';
} else {
    $template_path = get_template_directory() . '/templates/post-cards/' . $template_name . '.php';
}
```

---

## Рекомендуемое решение: Вариант 1 (с улучшениями)

### Структура файлов

```
templates/post-cards/
├── helpers.php                    # Существующий (расширить)
├── default.php                    # Существующий
├── ...                            # Другие существующие шаблоны
├── client-simple.php              # НОВЫЙ - Просто логотип
├── client-grid.php                # НОВЫЙ - Логотип в figure
└── client-card.php                # НОВЫЙ - Логотип в карточке
```

### 1. Расширить `cw_get_post_card_data()` для clients

**Добавить в `templates/post-cards/helpers.php`:**

```php
function cw_get_post_card_data($post, $image_size = 'full') {
    // ... существующий код ...
    
    // Специальная обработка для clients
    if ($post->post_type === 'clients') {
        return [
            'id' => $post->ID,
            'title' => get_the_title($post->ID),
            'link' => get_permalink($post->ID),
            'image_url' => $image_url,
            'image_alt' => $image_alt,
            'post_type' => 'clients',
            // Упрощенные данные - без категорий, даты, комментариев
        ];
    }
    
    // ... остальной код для других типов ...
}
```

### 2. Создать шаблоны для clients

#### `client-simple.php` - Для Swiper

```php
<?php
// Просто логотип, без оберток
if (!isset($post_data) || !$post_data) {
    return;
}

if ($post_data['image_url']) : ?>
    <figure class="mb-0">
        <?php if (!empty($post_data['link'])) : ?>
            <a href="<?php echo esc_url($post_data['link']); ?>">
        <?php endif; ?>
        <img src="<?php echo esc_url($post_data['image_url']); ?>" 
             alt="<?php echo esc_attr($post_data['image_alt']); ?>" 
             class="img-fluid" />
        <?php if (!empty($post_data['link'])) : ?>
            </a>
        <?php endif; ?>
    </figure>
<?php endif; ?>
```

#### `client-grid.php` - Для Grid Layout

```php
<?php
// Логотип в figure с адаптивным padding
if (!isset($post_data) || !$post_data) {
    return;
}

if ($post_data['image_url']) : ?>
    <figure class="px-3 px-md-0 px-xxl-2 mb-0">
        <?php if (!empty($post_data['link'])) : ?>
            <a href="<?php echo esc_url($post_data['link']); ?>">
        <?php endif; ?>
        <img src="<?php echo esc_url($post_data['image_url']); ?>" 
             alt="<?php echo esc_attr($post_data['image_alt']); ?>" 
             class="img-fluid" />
        <?php if (!empty($post_data['link'])) : ?>
            </a>
        <?php endif; ?>
    </figure>
<?php endif; ?>
```

#### `client-card.php` - Для Grid с карточками

```php
<?php
// Логотип в карточке с тенью
if (!isset($post_data) || !$post_data) {
    return;
}

$template_args = wp_parse_args($template_args ?? [], [
    'show_title' => false,
]);
?>
<div class="card shadow-lg h-100">
    <div class="card-body align-items-center d-flex px-3 py-6 p-md-8">
        <figure class="px-md-3 px-xl-0 px-xxl-3 mb-0">
            <?php if (!empty($post_data['link'])) : ?>
                <a href="<?php echo esc_url($post_data['link']); ?>">
            <?php endif; ?>
            <img src="<?php echo esc_url($post_data['image_url']); ?>" 
                 alt="<?php echo esc_attr($post_data['image_alt']); ?>" 
                 class="img-fluid" />
            <?php if (!empty($post_data['link'])) : ?>
                </a>
            <?php endif; ?>
        </figure>
    </div>
</div>
```

### 3. Создать шорткод для clients

**Добавить в `functions/post-card-templates.php`:**

```php
/**
 * Шорткод для отображения клиентов
 * 
 * @param array $atts Атрибуты шорткода
 * @return string HTML
 */
function cw_clients_shortcode($atts) {
    $atts = shortcode_atts([
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'template' => 'client-simple', // client-simple, client-grid, client-card
        'image_size' => 'codeweber_clients_200-60',
        'layout' => 'swiper', // swiper, grid, grid-cards
        // Swiper настройки
        'items_xl' => '7',
        'items_lg' => '6',
        'items_md' => '4',
        'items_sm' => '2',
        'items_xs' => '2',
        'margin' => '0',
        'dots' => 'false',
        'nav' => 'false',
        'autoplay' => 'false',
        'loop' => 'true',
        // Grid настройки
        'columns_xl' => '4',
        'columns_md' => '2',
        'gap' => '12',
    ], $atts);
    
    $args = [
        'post_type' => 'clients',
        'posts_per_page' => intval($atts['posts_per_page']),
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
        'post_status' => 'publish'
    ];
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        return '';
    }
    
    $display_settings = [
        'show_title' => false,
        'show_date' => false,
        'show_category' => false,
        'show_comments' => false,
    ];
    
    $template_args = [
        'image_size' => $atts['image_size'],
    ];
    
    ob_start();
    
    if ($atts['layout'] === 'swiper') {
        // Swiper layout
        $swiper_data = [
            'data-margin' => esc_attr($atts['margin']),
            'data-dots' => esc_attr($atts['dots']),
            'data-nav' => esc_attr($atts['nav']),
            'data-autoplay' => esc_attr($atts['autoplay']),
            'data-loop' => esc_attr($atts['loop']),
            'data-items-xl' => esc_attr($atts['items_xl']),
            'data-items-lg' => esc_attr($atts['items_lg']),
            'data-items-md' => esc_attr($atts['items_md']),
            'data-items-sm' => esc_attr($atts['items_sm']),
            'data-items-xs' => esc_attr($atts['items_xs']),
        ];
        
        $swiper_attrs = '';
        foreach ($swiper_data as $key => $value) {
            $swiper_attrs .= $key . '="' . $value . '" ';
        }
        
        ?>
        <div class="swiper-container clients mb-0" <?php echo $swiper_attrs; ?>>
            <div class="swiper">
                <div class="swiper-wrapper">
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <div class="swiper-slide px-5">
                            <?php echo cw_render_post_card(get_post(), $atts['template'], $display_settings, $template_args); ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <?php
    } elseif ($atts['layout'] === 'grid-cards') {
        // Grid with cards
        ?>
        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-<?php echo esc_attr($atts['columns_xl']); ?> gx-lg-6 gy-6">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <div class="col">
                    <?php echo cw_render_post_card(get_post(), $atts['template'], $display_settings, $template_args); ?>
                </div>
            <?php endwhile; ?>
        </div>
        <?php
    } else {
        // Simple grid
        ?>
        <div class="row row-cols-2 row-cols-md-<?php echo esc_attr($atts['columns_md']); ?> row-cols-xl-<?php echo esc_attr($atts['columns_xl']); ?> gx-0 gx-md-8 gx-xl-<?php echo esc_attr($atts['gap']); ?> gy-12">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <div class="col">
                    <?php echo cw_render_post_card(get_post(), $atts['template'], $display_settings, $template_args); ?>
                </div>
            <?php endwhile; ?>
        </div>
        <?php
    }
    
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('cw_clients', 'cw_clients_shortcode');
```

---

## Примеры использования

### 1. Swiper Slider

```php
[cw_clients 
    layout="swiper" 
    template="client-simple" 
    image_size="codeweber_clients_200-60"
    items_xl="7" 
    items_md="4" 
    items_xs="2"
    loop="true"
]
```

### 2. Grid Layout

```php
[cw_clients 
    layout="grid" 
    template="client-grid" 
    image_size="codeweber_clients_300-200"
    columns_xl="4" 
    columns_md="2"
]
```

### 3. Grid с карточками

```php
[cw_clients 
    layout="grid-cards" 
    template="client-card" 
    image_size="codeweber_clients_300-200"
    columns_xl="5" 
    columns_md="3"
]
```

### 4. Прямой вызов функции

```php
$clients = get_posts(['post_type' => 'clients', 'posts_per_page' => 6]);

foreach ($clients as $client) {
    echo cw_render_post_card($client, 'client-simple', [
        'show_title' => false,
    ], [
        'image_size' => 'codeweber_clients_200-60',
    ]);
}
```

---

## Альтернативный вариант: Отдельная папка

Если хотите полную изоляцию, можно создать:

```
templates/client-cards/
├── simple.php
├── grid.php
└── card.php
```

И модифицировать `cw_render_post_card()`:

```php
// Определяем папку шаблонов
if ($post->post_type === 'clients') {
    $template_dir = 'client-cards';
} else {
    $template_dir = 'post-cards';
}

$template_path = get_template_directory() . '/templates/' . $template_dir . '/' . sanitize_file_name($template_name) . '.php';
```

---

## Рекомендация

**Использовать Вариант 1** (расширить существующую систему) потому что:

1. ✅ Минимальные изменения кода
2. ✅ Единый интерфейс
3. ✅ Легко поддерживать
4. ✅ Переиспользование существующих функций
5. ✅ Можно добавить специфичные шаблоны для clients

**Шаги реализации:**
1. Расширить `cw_get_post_card_data()` для упрощения данных clients
2. Создать 3 шаблона: `client-simple.php`, `client-grid.php`, `client-card.php`
3. Создать шорткод `[cw_clients]` с поддержкой 3 layout'ов
4. Протестировать все варианты

---

## Вопросы для уточнения

1. Нужны ли ссылки на записи clients или просто логотипы?
2. Нужен ли title для clients (обычно не нужен)?
3. Какой размер изображения использовать по умолчанию?
4. Нужна ли поддержка в Gutenberg блоке (как Post Grid)?

