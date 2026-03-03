# Функция `vacancy_social_links()`

Выводит список ссылок для вакансий (CPT vacancies) из метаполей записи. Строится на базе функции [`social_links()`](FUNCTION_SOCIAL_LINKS.md), собирая кастомный массив ссылок из метаполей вакансии.

**Файл:** `functions/global.php`

---

## Параметры

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| **$post_id** | int | — (обязательный) | ID записи vacancy |
| **$class** | string | '' | Дополнительные CSS-классы для обёртки `<nav>` |
| **$type** | string | 'type1' | Тип отображения (type1-type9) |
| **$size** | string | 'sm' | Размер иконок/кнопок (sm, md, lg) |
| **$button_color** | string | 'primary' | Цвет кнопки для type8 |
| **$buttonstyle** | string | 'solid' | Стиль кнопки (solid или outline) |
| **$button_form** | string | 'circle' | Форма кнопки (circle или block) |

---

## Возвращаемое значение

`string` — HTML-код со ссылками. Возвращает пустую строку, если метаполя вакансии не заполнены.

---

## Поддерживаемые поля вакансий

Функция автоматически собирает данные из следующих метаполей:

| Метаполе | Тип ссылки | Иконка | Метка | Открытие |
|----------|------------|--------|-------|----------|
| `_vacancy_email` | Email | envelope | Email | в почтовом клиенте |
| `_vacancy_linkedin_url` | LinkedIn | linkedin | LinkedIn | в новой вкладке |
| `_vacancy_apply_url` | URL вакансии | link | Vacancy URL | в новой вкладке |

### Примечание

- Для `email` автоматически добавляется префикс `mailto:`
- Для `linkedin_url` и `apply_url` автоматически добавляется `target="_blank" rel="noopener"`

---

## Примеры использования

### Базовый вызов

```php
<?php 
$post_id = get_the_ID();
echo vacancy_social_links($post_id); 
?>
```

### С кастомными параметрами

```php
<?php 
$post_id = get_the_ID();
echo vacancy_social_links(
    $post_id, 
    'mb-4',           // class
    'type8',          // type
    'md',             // size
    'primary',        // button_color
    'solid',          // buttonstyle
    'circle'          // button_form
); 
?>
```

### В шаблоне вакансии

```php
<div class="vacancy-actions">
    <?php echo vacancy_social_links(get_the_ID(), '', 'type3', 'sm'); ?>
</div>
```

---

## Как это работает

1. Функция получает данные из метаполей вакансии (`_vacancy_email`, `_vacancy_linkedin_url`, `_vacancy_apply_url`)
2. Формирует массив кастомных социальных ссылок с указанием:
   - URL (с автоматическим добавлением `mailto:` для email)
   - Иконки
   - Метки
   - Названия соцсети (для стилизации)
   - Флага открытия в новой вкладке
3. Передаёт этот массив в функцию `social_links()` вместе с параметрами стиля

---

## Типы отображения

| Тип | Описание |
|-----|----------|
| **type1** | Круглые кнопки с фоном |
| **type2** | Иконки в muted-стиле |
| **type3** | Цветные иконки без кнопок |
| **type4** | Белые иконки |
| **type5** | Тёмные круглые кнопки |
| **type6** | Кнопки с названиями |
| **type7** | Кнопки с кастомным фоном |
| **type8** | Кнопки с настраиваемым цветом |
| **type9** | Кнопки primary outline |

---

## Связанные функции

- [`social_links()`](FUNCTION_SOCIAL_LINKS.md) — основная функция
- [`codeweber_single_social_links()`](FUNCTION_CODEWEBER_SINGLE_SOCIAL_LINKS.md) — для single-страниц
- [`staff_social_links()`](FUNCTION_STAFF_SOCIAL_LINKS.md) — для сотрудников
