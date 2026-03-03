Краткая выжимка по шорткоду и где лежит полная инструкция.

---

## Шорткод `[cw_blog_posts_slider]`

Выводит записи блога в виде **слайдера (Swiper)** или **обычной сетки** — в зависимости от параметра `layout`. Карточки по выбранному шаблону.

**Подробно (все параметры, значения по умолчанию, примеры, вызов из PHP):**  
код и комментарии в `functions/post-card-templates.php` — функции `cw_blog_posts_slider()`, `cw_blog_posts_slider_shortcode()`.

---

### Режим вывода (layout)

| Параметр | Значение | Назначение |
|----------|----------|------------|
| **layout** | `swiper` (по умолчанию) | Слайдер Swiper с точками/стрелками |
| **layout** | `grid` | Обычная Bootstrap-сетка, все карточки сразу |

---

### Все параметры

| Параметр | По умолчанию | Назначение |
|----------|--------------|------------|
| **posts_per_page** | `4` | Количество постов в блоке. |
| **category** | `''` | Слаги категорий через запятую, например `news,events`. Пусто — все категории. |
| **tag** | `''` | Слаги меток через запятую. Пусто — все метки. |
| **post_type** | `post` | Тип записей (как в WP_Query). |
| **orderby** | `date` | Сортировка: `date`, `rand`, `title`, `comment_count` и др. |
| **order** | `DESC` | Направление: `ASC` или `DESC`. |
| **image_size** | `codeweber_single` | Размер миниатюры: `thumbnail`, `medium`, `medium_large`, `large`, `full` или свой. |
| **excerpt_length** | `20` | Длина цитаты в словах. `0` — не показывать. |
| **title_length** | `0` | Макс. длина заголовка в символах. `0` — без обрезки. |
| **template** | `default` | Шаблон карточки: `default`, `default-clickable`, `slider`, `card`, `card-content`, `overlay-5`. |
| **enable_hover_scale** | `false` | Масштабирование карточки при наведении. |
| **show_title** | `true` | Показывать заголовок поста. |
| **show_date** | `true` | Показывать дату. |
| **show_category** | `true` | Показывать категорию(и). |
| **show_comments** | `true` | Показывать количество комментариев. |
| **title_tag** | `h2` | HTML-тег заголовка: `h1`, `h2`, `h3`, `h4` и т.д. |
| **title_class** | `''` | Дополнительные CSS-классы для заголовка. |
| **enable_lift** | `false` | Подъём карточки при наведении. Для `template="default-clickable"` по умолчанию `true`. |
| **items_xl** | `3` | Кол-во слайдов/колонок при ≥1200px. |
| **items_lg** | `3` | При ≥992px. |
| **items_md** | `2` | При ≥768px. |
| **items_sm** | `2` | При ≥576px. |
| **items_xs** | `1` | Мобильные. |
| **items_xxs** | `1` | Очень малые экраны. |
| **margin** | `30` | Отступ между слайдами в px (только при `layout="swiper"`). |
| **dots** | `true` | Точки (пагинация) под слайдером. |
| **nav** | `false` | Стрелки «назад/вперёд». |
| **autoplay** | `false` | Автопрокрутка. |
| **loop** | `false` | Зацикливание слайдов. |
| **layout** | `swiper` | Режим: `swiper` — слайдер, `grid` — обычная Bootstrap-сетка. |
| **gap** | `30` | Отступ между карточками в сетке в px (только при `layout="grid"`). |

Булевы параметры в шорткоде: `true`, `1` — включено; `false`, `0` — выключено.

---

### Примеры использования

**Минимум — 6 постов, настройки по умолчанию (слайдер):**
```
[cw_blog_posts_slider posts_per_page="6"]
```

**Полный пример (как в single.php):**
```
[cw_blog_posts_slider posts_per_page="6" template="default" enable_hover_scale="true" show_title="true" show_date="true" show_category="true" show_comments="true" title_tag="h3" title_length="50" image_size="codeweber_clients_400-267" items_xl="3" items_lg="3" items_md="2" items_sm="1" items_xs="1" items_xxs="1"]
```

**Только посты из категорий «news» и «events»:**
```
[cw_blog_posts_slider posts_per_page="6" category="news,events"]
```

**С меткой и автопрокруткой:**
```
[cw_blog_posts_slider posts_per_page="5" tag="акции" autoplay="true" loop="true"]
```

**Карточки-ссылки с подъёмом и стрелками:**
```
[cw_blog_posts_slider template="default-clickable" posts_per_page="4" nav="true" items_xl="3" items_md="2" items_sm="1"]
```

**Компактный вид — короткий заголовок, без цитаты, 4 в ряд на больших экранах:**
```
[cw_blog_posts_slider posts_per_page="8" title_length="40" excerpt_length="0" items_xl="4" items_lg="4" items_md="2" items_sm="1"]
```

**Обычная сетка вместо слайдера (все карточки сразу):**
```
[cw_blog_posts_slider layout="grid" posts_per_page="6" items_xl="3" items_lg="3" items_md="2" items_sm="1" gap="24"]
```

**Вызов из PHP (без шорткода):**
```php
<?php
echo cw_blog_posts_slider([
    'posts_per_page'     => 6,
    'template'           => 'default',
    'show_title'         => true,
    'show_date'          => true,
    'title_tag'          => 'h3',
    'title_length'       => 50,
    'image_size'         => 'codeweber_clients_400-267',
    'items_xl'           => '3',
    'items_lg'           => '3',
    'items_md'           => '2',
    'items_sm'           => '1',
    'items_xs'           => '1',
    'items_xxs'          => '1',
    'enable_hover_scale' => true,
]);
?>
```

---

### Образцы Swiper по типам шаблонов

Для каждого шаблона — шорткод и эквивалентный вызов PHP с теми же параметрами (`layout="swiper"` по умолчанию).

---

#### template="default"

**Шорткод:**
```
[cw_blog_posts_slider layout="swiper" posts_per_page="6" template="default" enable_hover_scale="true" show_title="true" show_date="true" show_category="true" show_comments="true" title_tag="h3" title_length="50" image_size="codeweber_clients_400-267" items_xl="3" items_lg="3" items_md="2" items_sm="1" items_xs="1" items_xxs="1" dots="true" nav="false"]
```

**PHP:**
```php
<?php
echo cw_blog_posts_slider([
    'layout'             => 'swiper',
    'posts_per_page'     => 6,
    'template'            => 'default',
    'enable_hover_scale' => true,
    'show_title'         => true,
    'show_date'          => true,
    'show_category'      => true,
    'show_comments'      => true,
    'title_tag'          => 'h3',
    'title_length'       => 50,
    'image_size'         => 'codeweber_clients_400-267',
    'items_xl'           => '3',
    'items_lg'           => '3',
    'items_md'           => '2',
    'items_sm'           => '1',
    'items_xs'           => '1',
    'items_xxs'          => '1',
    'dots'               => true,
    'nav'                => false,
]);
?>
```

---

#### template="default-clickable"

**Шорткод:**
```
[cw_blog_posts_slider layout="swiper" posts_per_page="6" template="default-clickable" enable_lift="true" show_title="true" show_date="true" show_category="true" show_comments="true" title_tag="h3" title_length="50" image_size="codeweber_clients_400-267" items_xl="3" items_lg="3" items_md="2" items_sm="1" items_xs="1" items_xxs="1" dots="true" nav="true"]
```

**PHP:**
```php
<?php
echo cw_blog_posts_slider([
    'layout'        => 'swiper',
    'posts_per_page'=> 6,
    'template'      => 'default-clickable',
    'enable_lift'   => true,
    'show_title'    => true,
    'show_date'     => true,
    'show_category' => true,
    'show_comments' => true,
    'title_tag'     => 'h3',
    'title_length'  => 50,
    'image_size'    => 'codeweber_clients_400-267',
    'items_xl'      => '3',
    'items_lg'      => '3',
    'items_md'      => '2',
    'items_sm'      => '1',
    'items_xs'      => '1',
    'items_xxs'     => '1',
    'dots'          => true,
    'nav'           => true,
]);
?>
```

---

#### template="slider"

**Шорткод:**
```
[cw_blog_posts_slider layout="swiper" posts_per_page="6" template="slider" show_title="true" show_date="true" show_category="true" show_comments="true" title_tag="h3" title_length="50" image_size="codeweber_clients_400-267" items_xl="3" items_lg="3" items_md="2" items_sm="1" items_xs="1" items_xxs="1" dots="true" nav="true"]
```

**PHP:**
```php
<?php
echo cw_blog_posts_slider([
    'layout'        => 'swiper',
    'posts_per_page'=> 6,
    'template'      => 'slider',
    'show_title'    => true,
    'show_date'     => true,
    'show_category' => true,
    'show_comments' => true,
    'title_tag'     => 'h3',
    'title_length'  => 50,
    'image_size'    => 'codeweber_clients_400-267',
    'items_xl'      => '3',
    'items_lg'      => '3',
    'items_md'      => '2',
    'items_sm'      => '1',
    'items_xs'      => '1',
    'items_xxs'     => '1',
    'dots'          => true,
    'nav'           => true,
]);
?>
```

---

#### template="card"

**Шорткод:**
```
[cw_blog_posts_slider layout="swiper" posts_per_page="6" template="card" show_title="true" show_date="true" show_category="true" show_comments="true" title_tag="h3" title_length="50" image_size="codeweber_clients_400-267" items_xl="3" items_lg="3" items_md="2" items_sm="1" items_xs="1" items_xxs="1" dots="true" nav="false"]
```

**PHP:**
```php
<?php
echo cw_blog_posts_slider([
    'layout'        => 'swiper',
    'posts_per_page'=> 6,
    'template'      => 'card',
    'show_title'    => true,
    'show_date'     => true,
    'show_category' => true,
    'show_comments' => true,
    'title_tag'     => 'h3',
    'title_length'  => 50,
    'image_size'    => 'codeweber_clients_400-267',
    'items_xl'      => '3',
    'items_lg'      => '3',
    'items_md'      => '2',
    'items_sm'      => '1',
    'items_xs'      => '1',
    'items_xxs'     => '1',
    'dots'          => true,
    'nav'           => false,
]);
?>
```

---

#### template="card-content"

**Шорткод:**
```
[cw_blog_posts_slider layout="swiper" posts_per_page="6" template="card-content" excerpt_length="20" show_title="true" show_date="true" show_category="true" show_comments="true" title_tag="h3" title_length="50" image_size="codeweber_clients_400-267" items_xl="3" items_lg="3" items_md="2" items_sm="1" items_xs="1" items_xxs="1" dots="true" nav="false"]
```

**PHP:**
```php
<?php
echo cw_blog_posts_slider([
    'layout'         => 'swiper',
    'posts_per_page' => 6,
    'template'       => 'card-content',
    'excerpt_length' => 20,
    'show_title'     => true,
    'show_date'      => true,
    'show_category'  => true,
    'show_comments'  => true,
    'title_tag'      => 'h3',
    'title_length'   => 50,
    'image_size'     => 'codeweber_clients_400-267',
    'items_xl'       => '3',
    'items_lg'       => '3',
    'items_md'       => '2',
    'items_sm'       => '1',
    'items_xs'       => '1',
    'items_xxs'      => '1',
    'dots'           => true,
    'nav'            => false,
]);
?>
```

---

#### template="overlay-5"

**Шорткод:**
```
[cw_blog_posts_slider layout="swiper" posts_per_page="6" template="overlay-5" show_title="true" show_date="true" show_category="true" show_comments="true" title_tag="h3" title_length="50" image_size="codeweber_clients_400-267" items_xl="3" items_lg="3" items_md="2" items_sm="1" items_xs="1" items_xxs="1" dots="true" nav="true"]
```

**PHP:**
```php
<?php
echo cw_blog_posts_slider([
    'layout'        => 'swiper',
    'posts_per_page'=> 6,
    'template'      => 'overlay-5',
    'show_title'    => true,
    'show_date'     => true,
    'show_category' => true,
    'show_comments' => true,
    'title_tag'     => 'h3',
    'title_length'  => 50,
    'image_size'    => 'codeweber_clients_400-267',
    'items_xl'      => '3',
    'items_lg'      => '3',
    'items_md'      => '2',
    'items_sm'      => '1',
    'items_xs'      => '1',
    'items_xxs'     => '1',
    'dots'          => true,
    'nav'           => true,
]);
?>
```
