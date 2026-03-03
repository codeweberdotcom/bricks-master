# Функция `social_links()`

Выводит HTML-код списка ссылок на социальные сети в различных стилях отображения. Основная функция для вывода социальных иконок.

**Файл:** `functions/global.php`

---

## Параметры

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| **$class** | string | '' | Дополнительные CSS-классы для обёртки `<nav>` |
| **$type** | string | 'type1' | Тип отображения (type1-type9) |
| **$size** | string | 'md' | Размер иконок/кнопок (sm, md, lg) |
| **$button_color** | string | 'primary' | Цвет кнопки для type8 |
| **$buttonstyle** | string | 'solid' | Стиль кнопки (solid или outline) |
| **$button_form** | string | 'circle' | Форма кнопки (circle или block) |
| **$custom_socials** | array\|null | null | Кастомный массив ссылок |

---

## Возвращаемое значение

`string` — HTML-код со ссылками на социальные сети в обёртке `<nav>`. Возвращает пустую строку, если социальные сети не настроены.

---

## Типы отображения (type)

| Тип | Описание |
|-----|----------|
| **type1** | Круглые кнопки с фоном (каждая соцсеть свой цвет) |
| **type2** | Иконки в muted-стиле (серые) |
| **type3** | Обычные цветные иконки без кнопок |
| **type4** | Белые иконки |
| **type5** | Тёмные круглые кнопки |
| **type6** | Кнопки с иконками и названиями (широкие, белые) |
| **type7** | Кнопки с кастомным фоном соцсети |
| **type8** | Кнопки с настраиваемым цветом и стилем |
| **type9** | Кнопки primary outline |

---

## Размеры (size)

| Размер | Описание |
|--------|----------|
| **sm** | Маленькие |
| **md** | Средние (по умолчанию) |
| **lg** | Большие |

---

## Примеры использования

### Базовые примеры

```php
// Вывод с типом по умолчанию (type1)
echo social_links('', 'type1');

// С указанием класса и типа
echo social_links('my-class', 'type2');

// С указанием всех параметров
echo social_links('mb-4', 'type8', 'lg', 'primary', 'solid', 'circle');
```

### С использованием глобальных настроек

```php
// Получить настройки из Redux
$type = Redux::get_option($opt_name, 'social-icon-type', '1');
$size = Redux::get_option($opt_name, 'global-social-button-size', 'md');

echo social_links('', 'type' . $type, $size);
```

### С кастомными социальными сетями

```php
$custom_socials = array(
    'telegram' => 'https://t.me/mychannel',
    'vk' => 'https://vk.com/mygroup',
    'instagram' => 'https://instagram.com/myaccount'
);

echo social_links('', 'type1', 'md', 'primary', 'solid', 'circle', $custom_socials);
```

### Расширенный формат кастомных социальных сетей

```php
$custom_socials = array(
    'email' => array(
        'url' => 'mailto:info@example.com',
        'icon' => 'envelope',
        'label' => 'Email',
        'social_name' => 'primary',
        'target_blank' => false
    ),
    'linkedin' => array(
        'url' => 'https://linkedin.com/in/example',
        'icon' => 'linkedin',
        'label' => 'LinkedIn',
        'social_name' => 'linkedin',
        'target_blank' => true
    )
);

echo social_links('', 'type8', 'sm', 'primary', 'solid', 'circle', $custom_socials);
```

---

## Использование в темах

### В шаблоне PHP

```php
<?php if (function_exists('social_links')): ?>
    <div class="social-container">
        <?php echo social_links('justify-content-center', 'type3', 'sm'); ?>
    </div>
<?php endif; ?>
```

### Через шорткод

```php
<?php echo do_shortcode('[social_links type="type3" size="sm" class="justify-content-center"]'); ?>
```

---

## Источник данных

Функция использует данные из опции WordPress `socials_urls`. Эта опция обычно настраивается через Redux Framework в разделе настроек темы.

### Пример структуры данных опции

```php
get_option('socials_urls') => array(
    'telegram' => 'https://t.me/mychannel',
    'vk' => 'https://vk.com/mygroup',
    'instagram' => 'https://instagram.com/myaccount',
    'facebook' => 'https://facebook.com/myaccount'
)
```

---

## Дочерние функции

- [`codeweber_single_social_links()`](FUNCTION_CODEWEBER_SINGLE_SOCIAL_LINKS.md) — обёртка для single-страниц
- [`staff_social_links()`](FUNCTION_STAFF_SOCIAL_LINKS.md) — для сотрудников (из метаполей)
- [`vacancy_social_links()`](FUNCTION_VACANCY_SOCIAL_LINKS.md) — для вакансий

---

## Связанные шорткоды

- [`[social_links]`](../Shortcodes/SHORTCODE_SOCIAL_LINKS.md) — шорткод для вызова этой функции
