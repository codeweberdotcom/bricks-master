# Функция `codeweber_single_social_links()`

Выводит список ссылок на социальные сети для single-страниц (блог, записи и т.д.). Использует настройки из Redux для определения типа и размера иконок.

**Файл:** `functions/global.php`

---

## Параметры

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| **$args** | array | array() | Ассоциативный массив параметров |

### Параметры массива $args

| Ключ | По умолчанию | Тип | Описание |
|------|--------------|-----|----------|
| **class** | '' | string | Дополнительные CSS-классы для обёртки |
| **type** | type3 | string | Тип отображения (type1-type9) |
| **size** | sm | string | Размер иконок/кнопок (sm, md, lg) |
| **button_color** | primary | string | Цвет кнопки для type8 |
| **buttonstyle** | solid | string | Стиль кнопки (solid или outline) |
| **button_form** | circle | string | Форма кнопки (circle или block) |

---

## Возвращаемое значение

`string` — HTML-код со ссылками на социальные сети. Возвращает пустую строку, если функция `social_links` не существует или социальные сети не настроены.

---

## Описание

Функция является обёрткой над [`social_links()`](FUNCTION_SOCIAL_LINKS.md) с предустановленными настройками для single-страниц:

- **type**: type3 (обычные цветные иконки без кнопок)
- **size**: sm (маленькие)
- **button_form**: circle (круглые)

При этом значения могут быть переопределены через параметр `$args` или автоматически получены из настроек Redux (Theme Style → Codeweber Icons).

---

## Примеры использования

### Базовый вызов (с настройками по умолчанию)

```php
<?php echo codeweber_single_social_links(); ?>
```

### С кастомными параметрами

```php
<?php 
echo codeweber_single_social_links(array(
    'class' => 'mb-4',
    'type' => 'type1',
    'size' => 'md'
)); 
?>
```

### С указанием типа type8

```php
<?php 
echo codeweber_single_social_links(array(
    'type' => 'type8',
    'button_color' => 'primary',
    'buttonstyle' => 'solid',
    'size' => 'sm'
)); 
?>
```

### В шаблоне

```php
<div class="single-social-links">
    <?php echo codeweber_single_social_links(); ?>
</div>
```

---

## Настройки Redux

Функция автоматически получает настройки из Redux:

- `global-social-icon-type` — тип иконок (fallback: `social-icon-type`)
- `global-social-button-size` — размер кнопок
- `global-social-button-style` — стиль кнопок

---

## Связанные функции

- [`social_links()`](FUNCTION_SOCIAL_LINKS.md) — основная функция
- [`staff_social_links()`](FUNCTION_STAFF_SOCIAL_LINKS.md) — для сотрудников
- [`vacancy_social_links()`](FUNCTION_VACANCY_SOCIAL_LINKS.md) — для вакансий
