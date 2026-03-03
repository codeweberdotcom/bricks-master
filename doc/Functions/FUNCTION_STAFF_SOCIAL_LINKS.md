# Функция `staff_social_links()`

Выводит список ссылок на социальные сети для сотрудников (CPT staff) из метаполей записи.

**Файл:** `functions/global.php`

---

## Параметры

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| **$post_id** | int | — (обязательный) | ID записи staff |
| **$class** | string | '' | Дополнительные CSS-классы для обёртки `<nav>` |
| **$type** | string | 'type1' | Тип отображения (type1-type9) |
| **$size** | string | 'sm' | Размер иконок/кнопок (sm, md, lg) |
| **$button_color** | string | 'primary' | Цвет кнопки для type8 |
| **$buttonstyle** | string | 'solid' | Стиль кнопки (solid или outline) |
| **$button_form** | string | 'circle' | Форма кнопки (circle или block) |

---

## Возвращаемое значение

`string` — HTML-код со ссылками на социальные сети. Возвращает пусту строку, если метаполя сотрудника не заполнены.

---

## Поддерживаемые социальные сети

Функция автоматически собирает данные из следующих метаполей:

| Метаполе | Социальная сеть | Иконка |
|----------|----------------|--------|
| `_staff_facebook` | Facebook | facebook-f |
| `_staff_twitter` | Twitter | twitter |
| `_staff_linkedin` | LinkedIn | linkedin |
| `_staff_instagram` | Instagram | instagram |
| `_staff_telegram` | Telegram | telegram-alt |
| `_staff_vk` | VK | vk |
| `_staff_whatsapp` | WhatsApp | whatsapp |
| `_staff_skype` | Skype | skype |
| `_staff_website` | Website | globe |

---

## Примеры использования

### Базовый вызов

```php
<?php 
$post_id = get_the_ID();
echo staff_social_links($post_id); 
?>
```

### С кастомными параметрами

```php
<?php 
$post_id = get_the_ID();
echo staff_social_links(
    $post_id, 
    'mb-4',           // class
    'type3',          // type
    'md',             // size
    'primary',        // button_color
    'solid',          // buttonstyle
    'circle'          // button_form
); 
?>
```

### В шаблоне staff

```php
<div class="staff-social-links">
    <?php echo staff_social_links(get_the_ID(), '', 'type2', 'sm'); ?>
</div>
```

---

## Типы отображения

| Тип | Описание |
|-----|----------|
| **type1** | Круглые кнопки с фоном (каждая соцсеть свой цвет) |
| **type2** | Иконки в muted-стиле (серые) |
| **type3** | Обычные цветные иконки без кнопок |
| **type4** | Белые иконки |
| **type8** | Кнопки с настраиваемым цветом и стилем |
| **type9** | Кнопки primary outline |

---

## Связанные функции

- [`social_links()`](FUNCTION_SOCIAL_LINKS.md) — основная функция
- [`codeweber_single_social_links()`](FUNCTION_CODEWEBER_SINGLE_SOCIAL_LINKS.md) — для single-страниц
- [`vacancy_social_links()`](FUNCTION_VACANCY_SOCIAL_LINKS.md) — для вакансий
