# WooCommerce Product Video

Модуль добавляет видео к товару через метабокс в редакторе. Видео отображается как дополнительный слайд в галерее одиночного товара.

---

## Файлы

| Файл | Назначение |
|------|-----------|
| `functions/woocommerce/product-video.php` | Метабокс, сохранение мета, хелпер `cw_product_video_parse()` |
| `woocommerce/single-product.php` | Рендер видео-слайдов в main и thumbs swiper |

---

## Мета-поля товара

| Ключ | Тип | Описание |
|------|-----|----------|
| `_cw_product_video_type` | string | Тип: `youtube / vimeo / vk / rutube / mp4` |
| `_cw_product_video_url` | string | URL видео (исходный, от пользователя) |
| `_cw_product_video_poster_id` | int | ID вложения-постера (необязательно) |

---

## Хелпер `cw_product_video_parse()`

```php
cw_product_video_parse( string $url, string $type = '' ): ?array
```

Разбирает URL и возвращает данные для рендера или `null` если URL не распознан.

### Возвращаемый массив

| Ключ | Описание |
|------|----------|
| `type` | `youtube / vimeo / vk / rutube / video` |
| `glightbox_href` | URL или `#uid` для inline-контента |
| `glightbox_attrs` | `data-glightbox` или `data-glightbox="type: inline; width: 90vw; height: 90vh;"` |
| `embed_id` | ID скрытого div (только для VK/Rutube) |
| `embed_url` | URL iframe (только для VK/Rutube) |

### Поддерживаемые форматы URL

| Тип | Пример |
|-----|--------|
| YouTube | `https://www.youtube.com/watch?v=VIDEO_ID` |
| Vimeo | `https://vimeo.com/VIDEO_ID` |
| VK | `https://vkvideo.ru/video-67416969_456241429` или `https://vk.com/video-67416969_456241429` |
| Rutube | `https://rutube.ru/video/HASH32CHARS/` |
| MP4 | `/wp-content/uploads/video.mp4` |

---

## Как работает GLightbox по типу

### YouTube / Vimeo / MP4

`glightbox_href` — прямой URL. GLightbox сам определяет тип и открывает нативно.

```html
<a href="https://www.youtube.com/watch?v=ID" data-glightbox data-gallery="product-123">
```

### VK / Rutube — inline режим

Рядом с галереей рендерится скрытый `div` с `<iframe>`:

```html
<div id="cw-vv-67416969-456241429" class="d-none" style="width:100%;height:100%;">
    <iframe src="https://vkvideo.ru/video_ext.php?oid=...&id=...&autoplay=1"
            style="width:100%;height:100%;border:0;"
            allowfullscreen allow="autoplay; encrypted-media; fullscreen;..."></iframe>
</div>
```

Кнопка ссылается на `#cw-vv-...` с `type: inline`:

```html
<a href="#cw-vv-67416969-456241429"
   data-glightbox="type: inline; width: 90vw; height: 90vh;">
```

**Важно:** iframe должен иметь `style="width:100%;height:100%;"`, иначе GLightbox отображает его в размере по умолчанию (~300×150px).

---

## Рендер в галерее

### Main swiper слайд

```html
<figure class="overflow-hidden rounded position-relative bg-dark">
    <!-- если задан постер: -->
    <img src="poster.jpg" class="img-fluid" style="height:100%;width:100%;object-fit:cover;" alt="">
    <!-- кнопка воспроизведения: -->
    <a href="..." class="position-absolute top-50 start-50 translate-middle"
       data-glightbox="...">
        <span class="btn btn-circle btn-white btn-lg">
            <i class="icn-caret-right"></i>
        </span>
    </a>
</figure>
```

- Без постера — фон `bg-dark`
- С постером — изображение заполняет слайд (size `woocommerce_single`)

### Thumbs swiper слайд

```html
<div class="position-relative overflow-hidden rounded bg-dark">
    <!-- если задан постер: -->
    <img src="poster-thumb.jpg" class="rounded" style="width:100%;height:100%;object-fit:cover;display:block;" alt="">
    <span class="position-absolute top-50 start-50 translate-middle">
        <i class="uil uil-play-circle text-white" style="font-size:1.5rem;opacity:.85;line-height:1;"></i>
    </span>
</div>
```

- Видео-слайд находится как обычный `swiper-slide` в конце thumbs swiper — счётчики main и thumbs всегда равны
- Постер для thumbs использует size `thumbnail`

---

## Метабокс в редакторе

**Расположение:** редактор товара → метабокс «Product Video»

**Поля:**
- Radio-кнопки типа (YouTube / Vimeo / VK Video / Rutube / MP4 WebM)
- URL-поле — placeholder меняется при смене типа
- Постер (необязательно) — медиа-пикер WP без перезагрузки страницы

**JS поведения постера:**
- После выбора изображения: превью обновляется динамически, ID записывается в hidden input
- Страница **не перезагружается** (ранее использовался `location.reload()`)
- Кнопка «Remove» появляется динамически если её не было

---

## Автодетект типа

Если `$type` не задан — определяется по URL:

```
youtube.com / youtu.be → youtube
vimeo.com             → vimeo
vkvideo.ru OR vk.com/video → vk
rutube.ru             → rutube
иначе                 → mp4
```
