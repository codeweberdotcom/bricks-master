# 📱 Шорткод [social_links] - Краткая справка

## Базовый синтаксис
```
[social_links type="type1" size="md" class=""]
```

## Параметры

| Параметр | Значения | По умолчанию | Описание |
|----------|----------|--------------|----------|
| `type` | type1-type7 | type1 | Тип отображения |
| `size` | sm, md, lg | md | Размер иконок/кнопок |
| `class` | любые CSS классы | пусто | Дополнительные классы |

## Типы отображения

| Тип | Описание | Пример |
|-----|----------|--------|
| **type1** | Круглые кнопки с фоном (каждая соцсеть свой цвет) | `[social_links type="type1"]` |
| **type2** | Иконки в muted-стиле (серые) | `[social_links type="type2"]` |
| **type3** | Обычные цветные иконки без кнопок | `[social_links type="type3"]` |
| **type4** | Белые иконки | `[social_links type="type4"]` |
| **type5** | Тёмные круглые кнопки | `[social_links type="type5"]` |
| **type6** | Кнопки с иконками и названиями (широкие, белые) | `[social_links type="type6"]` |
| **type7** | Кнопки с кастомным фоном соцсети | `[social_links type="type7"]` |
| **type8** | Кнопки с настраиваемым цветом и стилем (без nav social) | `[social_links type="type8" button-color="primary" buttonstyle="solid"]` |

## Размеры

| Размер | Описание | Пример |
|--------|----------|--------|
| **sm** | Маленькие | `[social_links size="sm"]` |
| **md** | Средние (по умолчанию) | `[social_links size="md"]` |
| **lg** | Большие | `[social_links size="lg"]` |

## Быстрые примеры

### Минимальный вариант
```
[social_links]
```

### Все типы
```
[social_links type="type1"]
[social_links type="type2"]
[social_links type="type3"]
[social_links type="type4"]
[social_links type="type5"]
[social_links type="type6"]
[social_links type="type7"]
[social_links type="type8" button-color="primary" buttonstyle="solid"]
```

### Все размеры
```
[social_links size="sm"]
[social_links size="md"]
[social_links size="lg"]
```

### С CSS классами
```
[social_links class="justify-content-center"]
[social_links class="mb-4"]
[social_links class="flex-column"]
```

### Комбинированные примеры
```
[social_links type="type1" size="lg" class="mb-4"]
[social_links type="type6" class="flex-column"]
[social_links type="type2" size="sm" class="justify-content-center gap-2"]
[social_links type="type8" button-color="primary" buttonstyle="outline" size="lg"]
[social_links type="type8" button-color="red" buttonstyle="solid" class="gap-2"]
```

## Использование в PHP

```php
// Через do_shortcode()
echo do_shortcode('[social_links type="type1" size="lg"]');

// Напрямую через функцию
echo social_links('my-class', 'type1', 'lg');
```

## Полный тестовый блок

Скопируйте на тестовую страницу:

```
<h2>Все типы отображения</h2>
<p><strong>Type1:</strong></p>
[social_links type="type1"]

<p><strong>Type2:</strong></p>
[social_links type="type2"]

<p><strong>Type3:</strong></p>
[social_links type="type3"]

<p><strong>Type4:</strong></p>
[social_links type="type4"]

<p><strong>Type5:</strong></p>
[social_links type="type5"]

<p><strong>Type6:</strong></p>
[social_links type="type6"]

<p><strong>Type7:</strong></p>
[social_links type="type7"]

<h2>Размеры (type1)</h2>
<p><strong>Small:</strong></p>
[social_links type="type1" size="sm"]

<p><strong>Medium:</strong></p>
[social_links type="type1" size="md"]

<p><strong>Large:</strong></p>
[social_links type="type1" size="lg"]
```

## Параметры для type8

| Параметр | Значения | По умолчанию | Описание |
|----------|----------|--------------|----------|
| `button-color` | primary, red, blue, green, purple и т.д. | primary | Цвет кнопки (все цвета темы) |
| `buttonstyle` | solid, outline | solid | Стиль кнопки (сплошная или с обводкой) |

### Примеры type8
```
[social_links type="type8" button-color="primary" buttonstyle="solid"]
[social_links type="type8" button-color="red" buttonstyle="outline"]
[social_links type="type8" button-color="blue" buttonstyle="solid" size="lg"]
[social_links type="type8" button-color="green" buttonstyle="outline" class="gap-2"]
```

## Примечания

- Шорткод использует данные из опции WordPress `'socials_urls'`
- Если соцсети не настроены, шорткод вернет пустую строку
- Все параметры опциональны
- CSS классы применяются к обёртке `<nav>` (кроме type8)
- Для type8 обертка `nav social` не используется, только дополнительные классы
- Параметры `button-color` и `buttonstyle` работают только для type8

---
📄 Подробная документация: `social-links-examples.txt`
🌐 HTML версия: `social-links-test-page.html`

