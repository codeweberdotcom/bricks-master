# SHORTCODE_REDUX_OPTION

## Общее описание

Шорткод `[redux_option]` позволяет вывести любое значение из Redux Framework по ключу. Это универсальный шорткод для доступа к настройкам темы.

---

## Исходный код

**Файл:** [`functions/integrations/redux_framework/redux_framework.php`](functions/integrations/redux_framework/redux_framework.php:68)

```php
add_shortcode('redux_option', function ($atts) {
    global $opt_name;

    $atts = shortcode_atts(array(
        'key'     => '',
        'default' => '',
        'format'  => '',
        'list'    => '',
    ), $atts, 'redux_option');

    if (empty($atts['key']) || empty($opt_name)) {
        return '';
    }

    $value = Redux::get_option($opt_name, $atts['key']);

    if (empty($value)) {
        return esc_html($atts['default']);
    }

    // Special handling for arrays and dates...
});
```

---

## Параметры шорткода

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `key` | string | (обязательный) | Ключ поля Redux |
| `default` | string | `''` | Значение по умолчанию |
| `format` | string | `''` | Формат даты (d.m.Y и т.д.) |
| `list` | string | `''` | Формат массива: `inline` или `ul` |

---

## Использование

### Простое значение
```php
[redux_option key="opt-text-field"]
```

### С значением по умолчанию
```php
[redux_option key="opt-text-field" default="Значение по умолчанию"]
```

### Форматирование даты
```php
[redux_option key="date_field" format="d.m.Y"]
// Вывод: 01.01.2024
```

### Массив как список (ul)
```php
[redux_option key="checkbox_field" list="ul"]
// Вывод: <ul><li>Значение 1</li><li>Значение 2</li></ul>
```

### Массив inline
```php
[redux_option key="checkbox_field" list="inline"]
// Вывод: Значение 1, Значение 2
```

---

## Примеры для персональных данных

### Поле personal_data_actions
Для специального поля `personal_data_actions` поддерживается перевод ключей:
```php
[redux_option key="personal_data_actions" list="ul"]
// Вывод: <ul><li>Collection Data</li><li>Storage Data</li></ul>
```

---

## Доступные поля Redux

Поля определяются в конфигурации Redux. Основные секции:
- **Theme Options** — основные настройки
- **Header Settings** — настройки хедера
- **Social Links** — ссылки на соцсети
- **Contacts** — контактные данные
- **Footer Settings** — настройки футера

---

## Обработка разных типов данных

| Тип значения | Поведение |
|--------------|-----------|
| Строка | Выводится как есть |
| Массив | Зависит от параметра `list` |
| Дата | Форматируется через `format` |
| Пустое значение | Выводится `default` |

---

## Использование в PHP

```php
global $opt_name;
$value = Redux::get_option($opt_name, 'your_field_key');
```

---

## Связанные шорткоды

- [`[get_contact]`](SHORTCODE_GET_CONTACT.md) — контактные данные
- [`[address]`](SHORTCODE_ADDRESS.md) — адрес из Redux
- [`[social_links]`](SHORTCODE_SOCIAL_LINKS.md) — социальные иконки

---

## Требования

- Redux Framework должен быть активен
- Переменная `$opt_name` должна быть определена
