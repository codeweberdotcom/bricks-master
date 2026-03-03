# SHORTCODE_GET_CONTACT

## Общее описание

Шорткод `[get_contact]` выводит контактные данные из Redux Framework. Поддерживает вывод email и телефонов как простым текстом, так и с кликабельными ссылками.

---

## Исходный код

**Файл:** [`functions/integrations/redux_framework/redux_contacts.php`](functions/integrations/redux_framework/redux_contacts.php:57)

```php
function get_contact_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'field' => '',
            'type' => 'plain',
            'text' => '',
            'class' => '',
            'wrapper_class' => ''
        ),
        $atts,
        'get_contact'
    );

    global $opt_name;
    $value = Redux::get_option($opt_name, $atts['field']);

    if (empty($value)) {
        return '';
    }

    switch ($atts['type']) {
        case 'link':
            if ($atts['field'] === 'e-mail') {
                $href = 'mailto:' . antispambot($value);
                $link_text = !empty($atts['text']) ? $atts['text'] : antispambot($value);
            } else {
                $phone_number = preg_replace('/[^0-9+]/', '', $value);
                $href = 'tel:' . $phone_number;
                $link_text = !empty($atts['text']) ? $atts['text'] : $value;
            }
            $class_attr = !empty($atts['class']) ? ' class="' . esc_attr($atts['class']) . '"' : '';
            return '<a href="' . esc_attr($href) . '"' . $class_attr . '>' . esc_html($link_text) . '</a>';

        case 'plain':
        default:
            $wrapper_class = !empty($atts['wrapper_class']) ? ' class="' . esc_attr($atts['wrapper_class']) . '"' : '';
            return '<span' . $wrapper_class . '>' . esc_html($value) . '</span>';
    }
}
add_shortcode('get_contact', 'get_contact_shortcode');
```

---

## Параметры шорткода

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `field` | string | (обязательный) | ID поля в Redux (e-mail, phone_01 и т.д.) |
| `type` | string | `plain` | Тип вывода: `plain` (текст) или `link` (ссылка) |
| `text` | string | `''` | Альтернативный текст для ссылки |
| `class` | string | `''` | CSS класс для ссылки (type="link") |
| `wrapper_class` | string | `''` | CSS класс для обёртки (type="plain") |

---

## Использование

### Простой вывод телефона
```php
[get_contact field="phone_01"]
// Вывод: <span>+7(495)000-00-00</span>
```

### Телефон с кликабельной ссылкой
```php
[get_contact field="phone_01" type="link"]
// Вывод: <a href="tel:+74950000000">+7(495)000-00-00</a>
```

### Телефон с кастомным классом
```php
[get_contact field="phone_01" type="link" class="phone-link"]
// Вывод: <a href="tel:+74950000000" class="phone-link">+7(495)000-00-00</a>
```

### Email без ссылки
```php
[get_contact field="e-mail"]
// Вывод: <span>test@mail.com</span>
```

### Email с кликабельной ссылкой
```php
[get_contact field="e-mail" type="link"]
// Вывод: <a href="mailto:test@mail.com">test@mail.com</a>
```

### Email с кастомным текстом
```php
[get_contact field="e-mail" type="link" text="Напишите нам"]
// Вывод: <a href="mailto:test@mail.com">Напишите нам</a>
```

### С кастомным классом обёртки
```php
[get_contact field="phone_01" wrapper_class="text-muted"]
// Вывод: <span class="text-muted">+7(495)000-00-00</span>
```

---

## Поля Redux

Контактные данные заполняются в **Redux → Theme Options → Contacts**.

### Основные поля
| Ключ | Описание |
|------|----------|
| `e-mail` | Основной email |
| `phone_01` | Первый телефон |
| `phone_02` | Второй телефон |
| `phone_03` | Третий телефон |

---

## Особенности

### Защита email от спама
Для email используется функция `antispambot()`, которая obfuscates email для защиты от спам-ботов.

### Очистка телефона для tel: ссылки
Телефон очищается от всех нецифровых символов для корректной ссылки:
```php
+7(495)000-00-00 → tel:+74950000000
```

---

## HTML структура

### type="plain"
```html
<span class="text-muted">+7(495)000-00-00</span>
```

### type="link" для телефона
```html
<a href="tel:+74950000000" class="phone-link">+7(495)000-00-00</a>
```

### type="link" для email
```html
<a href="mailto:test&#64;mail.com">test&#64;mail.com</a>
```

---

## Примеры в контексте

### Контактная карточка
```php
<div class="contact-item">
    <i class="uil uil-phone"></i>
    [get_contact field="phone_01" type="link" class="text-decoration-none"]
</div>

<div class="contact-item">
    <i class="uil uil-envelope"></i>
    [get_contact field="e-mail" type="link" text="Написать письмо"]
</div>
```

---

## Связанные шорткоды

- [`[redux_option]`](SHORTCODE_REDUX_OPTION.md) — универсальный вывод Redux
- [`[address]`](SHORTCODE_ADDRESS.md) — адрес из Redux
- [`[social_links]`](SHORTCODE_SOCIAL_LINKS.md) — социальные иконки

---

## Требования

- Redux Framework должен быть активен
- Поле должно быть заполнено в Redux
