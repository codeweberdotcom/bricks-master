# WooCommerce Single Product Gallery

Галерея одиночного товара на базе Swiper с thumbs, скелетоном и поддержкой видео.

---

## Файлы

| Файл | Назначение |
|------|-----------|
| `woocommerce/single-product.php` | Шаблон: HTML галереи, скелетон, видео-слайды |
| `src/assets/scss/theme/_woo-single-product.scss` | Стили галереи, скелетон, shimmer thumbs |
| `src/assets/js/theme.js` | Инициализация Swiper, синхронизация thumbs, skeleton ready |

---

## Skeleton (скелетон-заглушка)

Показывается пока Swiper не инициализирован и первое изображение не загружено.

### HTML структура

```html
<div class="swiper-container swiper-thumbs-container ...">

    <!-- Скелетон (виден сразу) -->
    <div class="cw-gallery-skeleton">
        <div class="cw-skeleton-block cw-gallery-skeleton__main"></div>
        <div class="cw-gallery-skeleton__thumbs">
            <div class="cw-skeleton-block cw-gallery-skeleton__thumb"></div>
            <!-- × N thumbs из Redux настроек -->
        </div>
    </div>

    <!-- Swiper (скрыт до готовности) -->
    <div class="swiper">...</div>

</div>
```

### CSS механика

```scss
// Swiper скрыт (opacity:0) до добавления .cw-gallery-ready
.swiper-thumbs-container:not(.cw-gallery-ready) {
    > .swiper, .swiper-main, .cw-thumbs-area { opacity: 0; }
    // Скелетон явно остаётся видимым
    .cw-gallery-skeleton { opacity: 1; }
}

// После готовности: скелетон скрыть, swiper появляется плавно
.swiper-thumbs-container.cw-gallery-ready {
    .cw-gallery-skeleton { display: none; }
    > .swiper, .swiper-main, .cw-thumbs-area {
        opacity: 1;
        transition: opacity .3s ease;
    }
}
```

**Важно:** `.cw-gallery-skeleton` находится внутри `.swiper-thumbs-container`. Без явного `opacity: 1` он наследует `opacity: 0` от родителя и не виден.

### JS — когда добавляется `.cw-gallery-ready`

```js
var firstSlideImg = swiper.querySelector(".swiper-slide img");
var markGalleryReady = function () {
    slider1.classList.add("cw-gallery-ready");
};
if (!firstSlideImg || firstSlideImg.complete) {
    markGalleryReady(); // уже загружено
} else {
    firstSlideImg.addEventListener("load",  markGalleryReady, { once: true });
    firstSlideImg.addEventListener("error", markGalleryReady, { once: true });
}
```

---

## Shimmer для thumbs пока изображения грузятся

После инициализации Swiper thumbs-слайды рендерятся сразу, но изображения загружаются асинхронно — без заглушки слайды выглядят пустыми.

Решение — `::before` pseudo-element с shimmer-анимацией:

```scss
.swiper-thumbs .swiper-slide {
    position: relative;

    &::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: cw-skeleton-shimmer 1.4s infinite;
        border-radius: inherit;
        z-index: 0;
    }

    img, figure {
        position: relative;
        z-index: 1; // перекрывает shimmer после загрузки
    }
}
```

Анимация `cw-skeleton-shimmer` определена в `_skeleton.scss`.

---

## Варианты расположения thumbs

Управляется из Redux настроек. Передаётся через `data-thumbs-direction`:

| Значение | Расположение | CSS-класс скелетона |
|----------|-------------|---------------------|
| `horizontal` | Thumbs снизу | `.cw-gallery-skeleton` |
| `vertical` | Thumbs слева | `.cw-gallery-skeleton--v` |

---

## Redux настройки галереи

Все настройки читаются через `Codeweber_Options::get()` или `Redux::get_option()`:

| Ключ Redux | Описание |
|-----------|----------|
| `woo_single_thumbs_direction` | `horizontal` / `vertical` |
| `woo_single_thumbs_items` | Количество thumbs в скелетоне |
| `woo_single_thumbs_mousewheel` | Прокрутка колёсиком |
| `woo_single_hover_style` | Стиль hover на main слайде |
| `woo_single_hover_type` | CSS-класс hover-эффекта |
| `woo_show_single_title` | Показывать заголовок товара в правой колонке |

---

## Видео-слайд

Подробнее — в [WC_PRODUCT_VIDEO.md](WC_PRODUCT_VIDEO.md).

### Принцип равного количества слайдов

Видео добавляется как обычный `swiper-slide` в конец **обоих** swipers — main и thumbs. Счётчики всегда совпадают. Синхронизация Swiper thumbs работает корректно.

```text
main swiper:   [фото 1] [фото 2] ... [фото N] [видео-слайд]
thumbs swiper: [thumb 1] [thumb 2] ... [thumb N] [видео-thumb]
```

### Скрытый iframe VK/Rutube

Рендерится **внутри** `.swiper-thumbs-container` **до** swiper-блоков — вне слайдов, не влияет на счётчик и layout:

```html
<div id="cw-vv-OID-ID" class="d-none">
    <iframe src="https://vkvideo.ru/video_ext.php?..." allowfullscreen allow="..."></iframe>
</div>
```

### Overflow-hidden на figure

Все `<figure>` в main swiper имеют класс `overflow-hidden` — необходимо для корректного отображения при скруглениях и hover-эффектах. JS добавляет `height:100%; margin:0` через `syncThumbsHeight()` — это нормально, не убирать.
