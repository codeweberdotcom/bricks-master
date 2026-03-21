# Data-атрибуты темы CodeWeber

Полный справочник по всем `data-*` атрибутам, обрабатываемым JavaScript-кодом темы.

**Источники:** `src/assets/js/theme.js`, `restapi.js`, `notification-triggers.js`, `form-submit-universal.js`, `ajax-filter.js`, `wishlist.js`, `woo-quick-view.js` и вендорные библиотеки.

---

## Содержание

1. [Модальные окна и загрузка файлов](#1-модальные-окна-и-загрузка-файлов)
2. [Bootstrap-компоненты](#2-bootstrap-компоненты)
3. [Swiper-слайдер](#3-swiper-слайдер)
4. [Анимации при скролле (scrollCue)](#4-анимации-при-скролле-scrollcue)
5. [Параллакс (Rellax)](#5-параллакс-rellax)
6. [GLightbox (видео и медиа)](#6-glightbox-видео-и-медиа)
7. [Формы](#7-формы)
8. [Фоновые изображения](#8-фоновые-изображения)
9. [Фильтрация (Isotope / AJAX)](#9-фильтрация-isotope--ajax)
10. [Счётчики и прогресс](#10-счётчики-и-прогресс)
11. [Маска телефона](#11-маска-телефона)
12. [Количество (qty)](#12-количество-qty)
13. [WooCommerce](#13-woocommerce)
14. [Нотификации](#14-нотификации)

---

## 1. Модальные окна и загрузка файлов

Обрабатываются в `restapi.js`.

### Открыть модал

```html
<a href="#"
   data-bs-toggle="modal"
   data-bs-target="#modal"
   data-value="modal-123">
  Открыть
</a>
```

| Атрибут | Обязательный | Значения | Описание |
|---------|-------------|---------|----------|
| `data-bs-toggle="modal"` | Да | `"modal"` | Маркер для обработчика `restapi.js` |
| `data-value` | Да | см. ниже | ID контента для загрузки |
| `data-bs-target="#modal"` | Нет | `"#modal"` | Для Bootstrap-совместимости |

**Форматы `data-value`:**

| Значение | Что открывает | Skeleton |
|---------|--------------|---------|
| `modal-{id}` | CPT `modals` (числовой ID поста) | контентный |
| `cf7-{id}` | Contact Form 7 | форменный |
| `cf-{id}` | CodeWeber Form | форменный |
| `html-{id}` | CPT `html_blocks` | контентный |
| `add-testimonial` | Форма отзыва | форменный |
| `doc-{id}` (action email) | Форма отправки документа по email | контентный |

### Скачать файл

```html
<a href="#"
   data-bs-toggle="download"
   data-value="doc-123">
  Скачать
</a>
```

| Атрибут | Значение | Описание |
|---------|---------|----------|
| `data-bs-toggle="download"` | `"download"` | Не Bootstrap-модал — кастомный обработчик |
| `data-value` | `doc-{id}`, `staff-{id}`, `vac-{id}` | CPT и ID записи |

### FilePond (загрузка файлов в форме)

```html
<input type="file" data-filepond="true">
```

| Атрибут | Описание |
|---------|----------|
| `data-filepond="true"` | Инициализировать FilePond на этом input |
| `data-filepond-initialized` | Внутренний — проставляется JS после инициализации |

---

## 2. Bootstrap-компоненты

### Tooltip

```html
<button data-bs-toggle="tooltip" title="Подсказка">...</button>
<button data-bs-toggle="white-tooltip" title="Белая подсказка">...</button>
```

| Атрибут | Значения | Описание |
|---------|---------|----------|
| `data-bs-toggle` | `"tooltip"`, `"white-tooltip"` | Тип тултипа |
| `title` / `data-bs-title` | строка | Текст подсказки |

### Popover

```html
<button data-bs-toggle="popover"
        data-bs-title="Заголовок"
        data-bs-content="Текст">...</button>
```

### Collapse / Tabs

```html
<button data-bs-target="#my-section">Переключить</button>
```

| Атрибут | Описание |
|---------|----------|
| `data-bs-target="{selector}"` | CSS-селектор целевого элемента |

---

## 3. Swiper-слайдер

Применяются к элементу `.swiper-container` (или `.swiper`).

```html
<div class="swiper-container"
     data-items="3"
     data-items-md="2"
     data-items-xs="1"
     data-speed="400"
     data-autoplay="true"
     data-autoplaytime="4000"
     data-loop="true"
     data-margin="24">
```

### Количество слайдов

| Атрибут | По умолчанию | Описание |
|---------|-------------|----------|
| `data-items` | `1` | Слайдов на экран (все breakpoints) |
| `data-items-auto="true"` | — | Авто-ширина слайда |
| `data-items-xs` | — | xs (`< 576px`) |
| `data-items-sm` | — | sm (`≥ 576px`) |
| `data-items-md` | — | md (`≥ 768px`) |
| `data-items-lg` | — | lg (`≥ 992px`) |
| `data-items-xl` | — | xl (`≥ 1200px`) |
| `data-items-xxl` | — | xxl (`≥ 1400px`) |

### Поведение

| Атрибут | По умолчанию | Описание |
|---------|-------------|----------|
| `data-effect` | `"slide"` | Эффект: `slide`, `fade`, `cube`, `coverflow` |
| `data-speed` | `600` | Скорость перехода (мс) |
| `data-margin` | `0` | Отступ между слайдами (px) |
| `data-loop="true"` | `false` | Бесконечная прокрутка |
| `data-centered="true"` | `false` | Центрировать активный слайд |
| `data-reverse="true"` | `false` | Обратное направление |
| `data-drag="false"` | `true` | Отключить свайп/drag |
| `data-autoheight="true"` | `false` | Высота по текущему слайду |
| `data-resizeupdate="false"` | `true` | Пересчёт при resize |

### Автоплей

| Атрибут | По умолчанию | Описание |
|---------|-------------|----------|
| `data-autoplay="false"` | `true` | Включить / выключить |
| `data-autoplaytime` | `4000` | Интервал (мс) |

### Навигация

| Атрибут | По умолчанию | Описание |
|---------|-------------|----------|
| `data-nav="true"` | `false` | Кнопки prev/next |
| `data-dots="true"` | `false` | Пагинация (точки) |
| `data-thumbs="true"` | `false` | Второй слайдер-превью как thumbs |

---

## 4. Анимации при скролле (scrollCue)

Библиотека `scrollCue.min.js`. Элемент анимируется при попадании в viewport.

### На одном элементе

```html
<div data-cue="fadeIn" data-delay="200" data-duration="600">...</div>
```

### На родителе (дети наследуют настройки)

```html
<div data-cues="fadeIn" data-interval="100" data-duration="500">
  <div><!-- анимируется --></div>
  <div><!-- анимируется с задержкой +100мс --></div>
</div>
```

| Атрибут | По умолчанию | Описание |
|---------|-------------|----------|
| `data-cue="{name}"` | — | Название анимации: `fadeIn`, `slideInDown`, `slideInLeft`, `slideInRight`, `slideInUp`, `zoomIn` и др. |
| `data-cues="{name}"` | — | Задаётся на родителе — дети получают эту анимацию |
| `data-duration="{ms}"` | `600` | Длительность анимации |
| `data-delay="{ms}"` | `0` | Задержка перед стартом |
| `data-interval="{ms}"` | `100` | Задержка между дочерними элементами (при `data-cues`) |
| `data-group` | — | Группировка — все запускаются одновременно |
| `data-sort` | — | Порядок сортировки при группировке |
| `data-addClass` | — | Добавить CSS-класс после анимации |

---

## 5. Параллакс (Rellax)

Библиотека `rellax.min.js`. Применяется к элементу с классом `.rellax`.

```html
<div class="rellax" data-rellax-speed="-2">...</div>
```

| Атрибут | Диапазон | Описание |
|---------|---------|----------|
| `data-rellax-speed` | `-10` до `10` | Скорость параллакса (`0` = без движения) |
| `data-rellax-percentage` | `0.0`–`1.0` | Точка старта параллакса |
| `data-rellax-zindex` | число | z-index элемента |
| `data-rellax-xs-speed` | — | Скорость для xs |
| `data-rellax-mobile-speed` | — | Скорость для mobile |
| `data-rellax-tablet-speed` | — | Скорость для tablet |
| `data-rellax-desktop-speed` | — | Скорость для desktop |
| `data-rellax-vertical-speed` | — | Только вертикальный параллакс |
| `data-rellax-horizontal-speed` | — | Только горизонтальный параллакс |
| `data-rellax-min` / `data-rellax-max` | — | Ограничение вертикального смещения |
| `data-rellax-min-x` / `data-rellax-max-x` | — | Ограничение горизонтального смещения |
| `data-rellax-min-y` / `data-rellax-max-y` | — | То же, по оси Y |

---

## 6. GLightbox (видео и медиа)

Библиотека `glightbox.js`. Инициализация через `theme.js`.

```html
<!-- Изображение -->
<a href="/img/photo.jpg"
   data-glightbox="image"
   data-gallery="gallery-1">
  Открыть фото
</a>

<!-- YouTube -->
<a href="https://youtube.com/watch?v=..."
   data-glightbox="youtube"
   data-gallery="videos">
  Смотреть
</a>

<!-- PDF -->
<a href="/docs/file.pdf"
   data-glightbox="height: 100vh"
   data-gallery="pdf">
  Открыть PDF
</a>
```

| Атрибут | Значения | Описание |
|---------|---------|----------|
| `data-glightbox` | `"image"`, `"youtube"`, `"vimeo"`, `"video"`, `"html5video"`, `"height: 100vh"` | Тип или конфигурация GLightbox |
| `data-gallery="{name}"` | строка | Группа галереи (навигация между элементами) |

> Для Rutube, VK, YouTube и Vimeo блок Button генерирует **скрытый `<iframe>`** и якорный `href`. В этом случае `data-glightbox="width: auto;"` проставляется автоматически.

---

## 7. Формы

### Кнопка отправки (submit-button блок)

```html
<input type="submit"
       value="Отправить"
       data-loading-text="Отправляю...">
```

| Атрибут | Описание |
|---------|----------|
| `data-loading-text` | Текст кнопки во время отправки формы |

### CodeWeber Forms (form-submit-universal.js)

Атрибуты на элементе `<form>`:

| Атрибут (`dataset.*`) | Описание |
|----------------------|----------|
| `data-form-id` | ID формы (поста CPT `codeweber_form`) |
| `data-form-name` | Имя формы для аналитики |
| `data-form-type` | Тип: `form`, `newsletter`, `testimonial` и др. |
| `data-initialized` | `"true"` — форма инициализирована обработчиком |

Атрибуты на элементах внутри формы:

| Атрибут | Описание |
|---------|----------|
| `data-filepond="true"` | Инициализировать FilePond |
| `data-rating="{1-5}"` | Значение звезды рейтинга |
| `data-rating-input="{id}"` | ID `<input>` для хранения значения рейтинга |
| `data-no-file-text` | Placeholder когда файл не выбран |

### reCAPTCHA (CF7 + CodeWeber Forms)

```html
<input data-recaptcha type="hidden">
```

| Атрибут | Описание |
|---------|----------|
| `data-recaptcha` | Маркер поля для токена reCAPTCHA v3 |

---

## 8. Фоновые изображения

```html
<div class="bg-image" data-image-src="/img/hero.jpg">...</div>
```

| Атрибут | Описание |
|---------|----------|
| `data-image-src="{url}"` | URL фонового изображения. JS проставляет `background-image: url(...)` |

Применяется к элементам с классом `.bg-image`. Обрабатывается в `theme.js → setBackgroundImages()`.

---

## 9. Фильтрация (Isotope / AJAX)

### Isotope (портфолио, галерея)

```html
<!-- Кнопки фильтра -->
<button data-filter="*">Все</button>
<button data-filter=".design">Дизайн</button>
<button data-filter=".dev">Разработка</button>
```

| Атрибут | Описание |
|---------|----------|
| `data-filter="{selector}"` | CSS-селектор для фильтрации Isotope (`*` — показать все) |

### AJAX-фильтр (ajax-filter.js)

Атрибуты на контейнере `.ajax-filter`:

```html
<div class="ajax-filter"
     data-post-type="services"
     data-template="service-card"
     data-container="#results"
     data-load-on-init="true">
```

| Атрибут | Описание |
|---------|----------|
| `data-post-type` | CPT для запроса |
| `data-template` | Шаблон карточки (в `templates/post-cards/`) |
| `data-container` | Селектор контейнера результатов |
| `data-load-on-init="true"` | Загружать результаты при инициализации |
| `data-filter-name` | Имя поля фильтра (таксономия или мета) |

---

## 10. Счётчики и прогресс

### CounterUp (цифры с анимацией)

```html
<span class="counter">1500</span>
```

Инициализируется автоматически на `.counter`. Никаких data-атрибутов не требует — текстовое содержимое элемента является целевым числом.

### Progress Bar (Bootstrap)

```html
<div class="progress-bar" data-value="75"></div>
```

| Атрибут | Описание |
|---------|----------|
| `data-value="{0-100}"` | Процент заполнения (0–100). JS проставляет `width: {value}%` |

---

## 11. Маска телефона

Применяется к `<input type="tel">` или `<input type="text">`.

```html
<input type="tel"
       data-mask="+7 (000) 000-00-00"
       data-mask-caret="_"
       data-mask-soft-caret="X">
```

| Атрибут | Описание |
|---------|----------|
| `data-mask="{pattern}"` | Маска ввода. `0` — цифра, `A` — буква, `*` — любой символ |
| `data-mask-caret="{char}"` | Символ-заполнитель (по умолчанию `_`) |
| `data-mask-soft-caret="{char}"` | Мягкий заполнитель (допустим пустой) |
| `data-mask-blur="true"` | Применять маску при потере фокуса (по умолчанию при вводе) |

В блоке `form-field` маска задаётся через атрибут `phoneMask` в Inspector → поле Gutenberg рендерит эти атрибуты в PHP (`render.php`).

---

## 12. Количество (qty)

WooCommerce и пользовательские формы.

```html
<button data-qty-dec>−</button>
<input type="number" data-min="1" data-max="99" value="1">
<button data-qty-inc>+</button>
```

| Атрибут | Описание |
|---------|----------|
| `data-qty-inc` | Кнопка увеличения количества |
| `data-qty-dec` | Кнопка уменьшения количества |
| `data-min="{n}"` | Минимальное значение (по умолчанию `1`) |
| `data-max="{n}"` | Максимальное значение (по умолчанию `Infinity`) |

JS ищет ближайший `<input type="number">` к кнопке.

---

## 13. WooCommerce

### Quick View

```html
<button class="quick-view-btn" data-product-id="42">Быстрый просмотр</button>
```

| Атрибут | Описание |
|---------|----------|
| `data-product-id="{id}"` | ID товара WooCommerce (используется в `woo-quick-view.js`) |

### Wishlist

```html
<button class="wishlist-btn" data-product-id="42" data-bs-toggle="wishlist">...</button>
```

| Атрибут | Описание |
|---------|----------|
| `data-product-id="{id}"` | ID товара для добавления/удаления из вишлиста |
| `data-bs-toggle="wishlist"` | Маркер для обработчика `wishlist.js` |

---

## 14. Нотификации

CPT `notifications` — popup-нотификации. Атрибуты на `#notification-modal`.

```html
<div id="notification-modal"
     class="modal fade"
     data-trigger-type="inactivity"
     data-trigger-inactivity="30"
     data-trigger-viewport=".hero-section"
     data-wait="500">
```

| Атрибут | Описание |
|---------|----------|
| `data-trigger-type` | Тип триггера: `"inactivity"`, `"viewport"`, `"time"` |
| `data-trigger-inactivity="{sec}"` | Показать после N секунд неактивности |
| `data-trigger-viewport="{selector}"` | Показать когда пользователь покинул viewport элемента |
| `data-wait="{ms}"` | Задержка перед показом (мс) |

Обрабатывается `notification-triggers.js`.

---

## Связанные документы

- **[MODAL_SYSTEM.md](../api/MODAL_SYSTEM.md)** — подробно о модальных окнах
- **[BUTTON_LINK_TYPES.md](../../plugins/codeweber-gutenberg-blocks/doc_claude/blocks/BUTTON_LINK_TYPES.md)** — типы ссылок блока Button
- **[CODEWEBER_FORMS.md](../forms/CODEWEBER_FORMS.md)** — система форм
