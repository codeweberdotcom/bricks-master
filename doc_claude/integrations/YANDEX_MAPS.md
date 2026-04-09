# Яндекс Карты — интеграция

## Что делает этот модуль

Обёртка над Яндекс Maps API 2.1. Позволяет рендерить карты с маркерами прямо из PHP-кода, из Gutenberg-блока `yandex-map`, или из шаблонов CPT Offices.

Возможности:
- Несколько типов маркеров (стандартный, кастомный цвет, логотип компании)
- Кластеризация маркеров
- Боковая панель с фильтрами (по городу, категории)
- Маршруты (авто, пешком, транспорт, велосипед)
- Балуны с произвольным содержимым
- Lazy load, адаптивность, поддержка screen reader
- Кастомные стили карты (через CSS или JSON)
- Подключение API в редакторе блоков (preview карты в Gutenberg)

---

## Файлы модуля

| Файл | Назначение |
|------|-----------|
| `functions/integrations/yandex-maps/yandex-maps-init.php` | Точка входа, инициализация singleton |
| `functions/integrations/yandex-maps/class-codeweber-yandex-maps.php` | Класс `Codeweber_Yandex_Maps` |
| `functions/integrations/yandex-maps/assets/js/yandex-maps.js` | JS-логика инициализации карт |
| `functions/integrations/yandex-maps/assets/css/yandex-maps.css` | Стили обёртки, сайдбара, спиннера |
| `functions/integrations/yandex-maps/example-style-json.json` | Пример JSON-стиля для карты |

---

## Настройка (Redux)

**Redux-ключ API:** `yandexapi` (тип: `text`, секция API Keys)

Все настройки по умолчанию тоже управляются через Redux. Redux-ключи имеют префикс `yandex_maps_*`:

### Основные

| Redux-ключ | Описание | По умолчанию |
|-----------|---------|-------------|
| `yandexapi` | API-ключ | — |
| `yandex_maps_default_center` | Центр карты `"lat,lng"` | `55.76,37.64` (Москва) |
| `yandex_maps_default_zoom` | Уровень зума | `10` |
| `yandex_maps_default_type` | Тип карты | `yandex#map` |
| `yandex_maps_default_height` | Высота в px | `500` |

### Маркеры

| Redux-ключ | Описание | Варианты |
|-----------|---------|---------|
| `yandex_maps_marker_type` | Тип маркера | `default`, `custom`, `logo` |
| `yandex_maps_marker_preset` | Preset Яндекс | `islands#redDotIcon` |
| `yandex_maps_marker_color` | Цвет (для custom) | `#FF0000` |
| `yandex_maps_marker_logo` | URL логотипа (array с `url`) | — |
| `yandex_maps_marker_logo_size` | Размер логотипа px | `40` |
| `yandex_maps_marker_open_balloon_on_click` | Открывать балун при клике | `true` |
| `yandex_maps_marker_auto_open_balloon` | Открывать балун автоматически | `false` |

### Кластеризация

| Redux-ключ | Описание |
|-----------|---------|
| `yandex_maps_clusterer_enabled` | Включить кластеризацию |
| `yandex_maps_clusterer_preset` | Стиль кластера (default: `islands#invertedVioletClusterIcons`) |

### Сайдбар

| Redux-ключ | Описание |
|-----------|---------|
| `yandex_maps_sidebar_enabled` | Показывать сайдбар |
| `yandex_maps_sidebar_position` | Позиция: `left` / `right` |
| `yandex_maps_sidebar_title` | Заголовок сайдбара |
| `yandex_maps_filters_enabled` | Показывать фильтры |
| `yandex_maps_filter_by_city` | Фильтр по городу |
| `yandex_maps_filter_by_category` | Фильтр по категории |

### Маршруты

| Redux-ключ | Описание | Варианты |
|-----------|---------|---------|
| `yandex_maps_route_enabled` | Включить маршруты | — |
| `yandex_maps_route_type` | Тип маршрута | `auto`, `pedestrian`, `masstransit`, `bicycle` |

### Поведение и UI

| Redux-ключ | По умолчанию |
|-----------|-------------|
| `yandex_maps_balloon_max_width` | `300` |
| `yandex_maps_balloon_close_button` | `true` |
| `yandex_maps_balloon_auto_pan` | `true` |
| `yandex_maps_enable_dbl_click_zoom` | `true` |
| `yandex_maps_enable_multi_touch` | `true` |
| `yandex_maps_geolocation_control` | `false` |
| `yandex_maps_route_button` | `false` |
| `yandex_maps_search_control` | `false` |
| `yandex_maps_lazy_load` | `false` |
| `yandex_maps_responsive` | `true` |
| `yandex_maps_mobile_optimized` | `true` |
| `yandex_maps_screen_reader_support` | `true` |
| `yandex_maps_custom_style` | `''` |
| `yandex_maps_style_json` | `''` |

---

## Инициализация

Singleton инициализируется на хуке `after_setup_theme` (приоритет 40):

```php
// yandex-maps-init.php
add_action('after_setup_theme', function () {
    Codeweber_Yandex_Maps::get_instance();
}, 40);
```

Приоритет 40 гарантирует, что Redux уже инициализирован (стандарт Redux — приоритет 10).

**Хук расширения:**
```php
// Выполняется при инициализации модуля — можно добавить кастомные действия
add_action('codeweber_yandex_maps_init', function($maps_instance) {
    // $maps_instance — объект Codeweber_Yandex_Maps
});
```

---

## Рендеринг карты из PHP

```php
$maps = Codeweber_Yandex_Maps::get_instance();

$html = $maps->render_map(
    // Настройки карты (переопределяют defaults из Redux)
    [
        'height'       => 400,
        'zoom'         => 12,
        'center'       => [55.76, 37.64],
        'map_type'     => 'yandex#map',    // yandex#map | yandex#satellite | yandex#hybrid
        'clusterer'    => true,
        'show_sidebar' => true,
        'sidebar_title' => 'Наши офисы',
        'map_id'       => 'my-offices-map',  // Если нужен конкретный id для CSS
    ],
    // Маркеры
    [
        [
            'latitude'       => 55.76,
            'longitude'      => 37.64,
            'title'          => 'Главный офис',
            'city'           => 'Москва',
            'category'       => 'office',
            'address'        => 'ул. Тверская, 1',
            'phone'          => '+7 (495) 000-00-00',
            'workingHours'   => 'Пн-Пт: 9:00-18:00',
            'balloonContent' => '<p>Дополнительный HTML в балуне</p>',
            'hintContent'    => 'Подсказка при наведении',
            'link'           => '/contact/',
        ],
    ]
);

echo $html;
```

### Структура маркера

| Поле | Обязательное | Описание |
|------|-------------|---------|
| `latitude` | ДА | Широта (float) |
| `longitude` | ДА | Долгота (float) |
| `title` | нет | Название (используется в сайдбаре) |
| `city` | нет | Для фильтра по городу |
| `category` | нет | Для фильтра по категории |
| `address` | нет | Адрес в балуне |
| `phone` | нет | Телефон в балуне |
| `workingHours` | нет | Часы работы в балуне |
| `description` | нет | Описание |
| `balloonContent` | нет | Произвольный HTML в балуне |
| `balloonContentHeader` | нет | Заголовок балуна (HTML) |
| `hintContent` | нет | Подсказка при наведении |
| `link` | нет | Ссылка «Подробнее» в балуне |

---

## Рендеринг из шаблона

```php
// В шаблоне archive-offices.php или любом другом шаблоне:
$maps = Codeweber_Yandex_Maps::get_instance();

if ($maps->has_api_key()) {
    $markers = [];
    $offices = new WP_Query(['post_type' => 'offices', 'posts_per_page' => -1]);

    while ($offices->have_posts()) {
        $offices->the_post();
        $lat = get_post_meta(get_the_ID(), 'office_lat', true);
        $lng = get_post_meta(get_the_ID(), 'office_lng', true);

        if ($lat && $lng) {
            $markers[] = [
                'latitude'  => $lat,
                'longitude' => $lng,
                'title'     => get_the_title(),
                'address'   => get_post_meta(get_the_ID(), 'office_address', true),
                'phone'     => get_post_meta(get_the_ID(), 'office_phone', true),
                'link'      => get_permalink(),
            ];
        }
    }
    wp_reset_postdata();

    echo $maps->render_map(['height' => 500], $markers);
}
```

---

## HTML-вывод `render_map()`

```html
<div class="codeweber-yandex-map-wrapper"
     data-map-config="{...json конфиг...}">
    <div class="spinner spinner-overlay" id="yandex-map-abc123-loader"></div>
    <div id="yandex-map-abc123"
         class="codeweber-yandex-map"
         style="width: 100%; height: 500px; border-radius: 8px;"></div>
</div>
```

JS-файл `yandex-maps.js` находит все `.codeweber-yandex-map-wrapper`, читает `data-map-config` и инициализирует карту.

---

## JS-переменные (`codeweberYandexMaps`)

```javascript
window.codeweberYandexMaps = {
    apiKey:        'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    language:      'ru_RU',
    defaultCenter: [55.76, 37.64],
    defaultZoom:   10,
    i18n: {
        route:         'Route',
        buildRoute:    'Build Route',
        from:          'From',
        to:            'To',
        filterByCity:  'Filter by City',
        allCities:     'All Cities',
        offices:       'Offices',
        city:          'City',
        address:       'Address',
        phone:         'Phone',
        workingHours:  'Working Hours',
        viewDetails:   'View Details',
    },
};
```

---

## Gutenberg-блок

Модуль подключает Яндекс Maps API также в редакторе блоков (хук `admin_enqueue_scripts`) — **только на страницах с блочным редактором и только если задан API-ключ**. Это позволяет блоку `yandex-map` из плагина `codeweber-gutenberg-blocks` показывать превью карты прямо в редакторе.

Условие подключения в админке:
```php
$is_block_editor = $screen->is_block_editor();
// + has_api_key() === true
```

---

## Публичные методы класса

| Метод | Описание |
|-------|---------|
| `get_instance()` | Получить singleton |
| `has_api_key()` | Проверить наличие API-ключа (lazy-обновление из Redux) |
| `get_api_key()` | Получить API-ключ |
| `get_default_settings()` | Получить массив defaults (с учётом Redux) |
| `get_path()` | Абсолютный путь к директории модуля |
| `get_url()` | URL директории модуля |
| `get_version()` | Версия модуля (`'1.0.0'`) |
| `render_map($args, $markers)` | Рендер HTML карты |

---

## Типы карт

| Значение | Описание |
|----------|---------|
| `yandex#map` | Обычная карта (схема) |
| `yandex#satellite` | Спутниковый снимок |
| `yandex#hybrid` | Спутник + подписи |

## Типы маркеров

| Значение | Описание |
|----------|---------|
| `default` | Стандартный маркер Яндекс (через `marker_preset`) |
| `custom` | Маркер с кастомным цветом (`marker_color`) |
| `logo` | Изображение-логотип (`marker_logo` + `marker_logo_size`) |

---

## Безопасность — защита API-ключа

### Уровни защиты

| Уровень | Метод | Файл | Строка |
| ------- | ----- | ---- | ------ |
| Enqueue | Ранний `return` если нет ключа | `class-codeweber-yandex-maps.php` | `enqueue_scripts()` |
| Admin enqueue | Проверка `has_api_key()` | `class-codeweber-yandex-maps.php` | `enqueue_admin_scripts()` |
| PHP render | Предупреждение если нет ключа | `class-codeweber-yandex-maps.php` | `render_map()` |
| Блок render | Ранний `return` с алертом | `build/blocks/yandex-map/render.php` | — |
| Navbar шаблон | `!empty($yandex_api_key)` перед выводом | `offcanvas-info-panel.php`, `offcanvas-info-simple.php` | — |

### Как работает защита enqueue

```php
// class-codeweber-yandex-maps.php → enqueue_scripts()
public function enqueue_scripts(): void {
    if (!$this->has_api_key()) {
        return; // Без ключа — не грузится ничего: ни yandex-maps-api, ни наш JS, ни CSS
    }
    // ...
}
```

`has_api_key()` перепроверяет ключ из Redux на случай отложенной инициализации:

```php
public function has_api_key(): bool {
    if (empty($this->api_key) && class_exists('Redux')) {
        $this->api_key = Redux::get_option($opt_name, 'yandexapi');
    }
    return !empty($this->api_key);
}
```

### Что происходит без ключа

- Запрос к `api-maps.yandex.ru` — **не отправляется**
- Скрипт `yandex-maps.js` — **не загружается**
- CSS — **не загружается**
- `render_map()` — возвращает `<div class="alert alert-warning">Yandex Maps API key is not configured.</div>`
- Gutenberg-блок — аналогичный алерт через `render.php`
- Редактор блоков — скрипт не грузится (нет preview карты)

---

## Часто задаваемые вопросы

**Карта не отображается — что проверить?**
1. API-ключ задан в Redux (`yandexapi`)
2. Ключ активен в кабинете Яндекс (нет ограничений по домену)
3. Скрипт `yandex-maps-api` загружен (проверить в DevTools → Network)
4. В консоли нет ошибок инициализации

**Как отключить прокрутку колесом мыши?**
```php
$maps->render_map(['enable_scroll_zoom' => false], $markers);
```

**Как задать кастомный стиль карты?**
```php
$maps->render_map([
    'style_json' => '[{"featureType": "road", "stylers": [{"color": "#f0f0f0"}]}]',
], $markers);
```
Пример JSON стиля: `functions/integrations/yandex-maps/example-style-json.json`.
