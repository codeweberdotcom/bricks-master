# WooCommerce Subcategories — Тема CodeWeber

Блок подкатегорий на главной магазина и на страницах категорий товаров: карточки или чипы, сеткой Bootstrap или Swiper-слайдером. Включается и настраивается через Redux, каждую категорию можно переопределить.

**Модуль:** `functions/woocommerce/subcategories.php` (подключается из `functions/woocommerce/init.php`)
**Шаблоны карточек:** `templates/post-cards/taxonomy/` (`card-image.php`, `chips.php`, `card-subcats.php` + готовые `overlay-5*.php`)
**Вывод:** `woocommerce/archive-product.php`
**Redux:** Woocommerce → Subcategories (`redux-framework/sample/sections/codeweber/woocommerce.php`)

---

## Как это работает

```
archive-product.php
  └─ cw_subcats_render()                      // → ['html' => ..., 'only' => bool]
       ├─ cw_subcats_resolve()                // показывать ли блок + настройки + термины
       │    ├─ cw_subcats_global_settings()   // Redux woo_subcats_*
       │    ├─ cw_subcats_get_term_overrides()// мета категории
       │    └─ cw_subcats_get_terms()         // дочерние product_cat
       └─ cw_subcats_render_card()
            └─ cw_render_term_card()          // шаблон из templates/post-cards/taxonomy/
```

Приоритет настроек: **WooCommerce «Display type» категории (если не Default) → мета категории (`cw_subcats_*`) → Redux → дефолты** (`cw_subcats_defaults()`).

Стандартный механизм WooCommerce (`woocommerce_product_loop_start` → `woocommerce_maybe_show_product_subcategories`) в теме **не используется**: шаблон `archive-product.php` не вызывает `woocommerce_product_loop_start()`, поэтому опция WC «Shop page display / Category display» ни на что не влияет. Подкатегориями управляет только этот модуль.

### Когда блок НЕ показывается

- не магазин и не `product_cat` (теги, поиск, другие страницы);
- `is_search()`, `is_paged()` (2+ страница), активные фильтры (`cw_has_active_filters()`) — как у стандартного вывода WC;
- у категории выбрано **Hide** (или WooCommerce Display type = **Products**);
- глобально выключено и у категории не стоит **Show** (при Display type = Default);
- нет ни одной подкатегории (после `hide_empty` и `exclude`).

### Режим «Instead of products»

`only = true` → в `archive-product.php` скрываются строка управления (количество, колонки, сортировка), сетка товаров, Load More и пагинация. Применяется только когда блок реально выведен.

Режим задаёт Redux (`woo_subcats_mode`) глобально, а для конкретной категории его переопределяет штатное поле WooCommerce **Display type** (см. ниже).

Если товаров в категории нет вообще (`woocommerce_product_loop()` ложно), а подкатегории есть — выводятся подкатегории, а блок «No products found» не показывается.

---

## Redux (`woo_subcats_*`)

| Ключ | Значения | Default | Описание |
|------|----------|---------|----------|
| `woo_subcats_enable` | bool | `false` | Главный выключатель |
| `woo_subcats_on_shop` | bool | `true` | Показывать на главной магазина (верхний уровень) |
| `woo_subcats_on_category` | bool | `true` | Показывать на страницах категорий |
| `woo_subcats_mode` | `above` / `only` | `above` | Над товарами / вместо товаров |
| `woo_subcats_template` | slug шаблона | `card-image` | Список берётся из реестра Post Cards (`taxonomy`), без `cells*` |
| `woo_subcats_layout` | `grid` / `slider` | `grid` | Сетка или Swiper |
| `woo_subcats_cols_xs` | 1–2 | `2` | Колонки / слайдов на экран (< 576px) |
| `woo_subcats_cols_sm` | 1–3 | `2` | ≥ 576px |
| `woo_subcats_cols_md` | 1–4 | `3` | ≥ 768px |
| `woo_subcats_cols_lg` | 1–6 | `3` | ≥ 992px |
| `woo_subcats_cols_xl` | 1–6 | `4` | ≥ 1200px |
| `woo_subcats_slider_margin` | 0–60 (px) | `20` | `data-margin` (только slider) |
| `woo_subcats_slider_nav` | bool | `true` | Стрелки |
| `woo_subcats_slider_dots` | bool | `false` | Точки |
| `woo_subcats_slider_autoplay` | bool | `false` | Автоплей (интервал 4000 мс) |
| `woo_subcats_show_count` | bool | `true` | Количество товаров |
| `woo_subcats_hide_empty` | bool | `true` | Скрывать пустые |
| `woo_subcats_exclude` | int[] (ID категорий) | `[]` | Исключить категории |
| `woo_subcats_image_size` | `thumbnail` / `medium` / `medium_large` / `large` / `full` | `medium_large` | Размер картинки |
| `woo_subcats_lift` | bool | `false` | Класс `lift` на карточке |

Значения читаются из `get_option('redux_demo')` с собственными дефолтами (зеркалят `default` полей Redux), поэтому блок работает и до первого сохранения настроек.

---

## Мета-поля категории (Products → Categories)

Поля есть и в форме «Add new», и в «Edit». Пустое значение = **Inherit** (мета удаляется).

| Meta key | Значения | Эффект |
|----------|----------|--------|
| `cw_subcats_enable` | `''` / `on` / `off` | `on` — показать, даже если глобально выключено; `off` — скрыть. Работает, пока Display type = Default |
| `cw_subcats_template` | `''` / slug | Стиль карточки для этой категории |
| `cw_subcats_layout` | `''` / `grid` / `slider` | Сетка или слайдер для этой категории |
| `cw_subcats_cols_xs` … `cw_subcats_cols_xl` | `''` / целое `1…max` | Колонки (Grid) или слайдов на экран (Slider) на каждом брейкпоинте. Пусто — глобальное значение из Redux. Лимиты: xs 2, sm 3, md 4, lg 6, xl 6 (`cw_subcats_cols_limits()`) |

Колонки наследуются **по каждому брейкпоинту отдельно**: можно задать только `md`, а остальные останутся глобальными. В форме у каждого select первый пункт — `Inherit (global: N)` с текущим глобальным значением. Для flow-шаблонов (`chips`) колонки не используются.

Мета применяется к подкатегориям, выводимым **на странице самой категории**. Для главной магазина действуют только глобальные настройки.

Сохранение: nonce `cw_subcats_term`, хуки `created_product_cat` / `edited_product_cat`, значения валидируются по белому списку (колонки — целое в пределах лимита, иначе мета удаляется).

### WooCommerce «Display type»

В настройках категории у WooCommerce есть собственное поле **Display type** (term meta `display_type`). Тема его учитывает; если выбрано не **Default**, оно главнее наших `cw_subcats_enable` и режима из Redux:

| Display type | Результат на странице категории |
|--------------|---------------------------------|
| Both | Подкатегории над товарами (режим `above`), блок показывается всегда |
| Subcategories | Только подкатегории (режим `only`), блок показывается всегда |
| Products | Блок скрыт, только товары |
| Default | Решают `cw_subcats_enable` → Redux |

Как и у самого WooCommerce, если у категории нет подкатегорий, блок не выводится и показываются товары. Глобальные опции WC «Shop page display» / «Default category display» по-прежнему игнорируются.

---

## Шаблоны карточек

Каждый шаблон — файл в `templates/post-cards/taxonomy/`, рендерится через `cw_render_term_card()`. Метаданные (label, описание, порядок) читаются сканером из шапки файла (`Card Template:`, `Description:`, `Supports:`, `Order:`), в `post-cards-registry.php` их дописывать не нужно. Те же шаблоны доступны в блоке **Post Grid** (источник Taxonomy).

| Slug | Вид | Особенности |
|------|-----|-------------|
| `overlay-5` | Картинка с тёмным оверлеем | Существующий; счётчик и описание на hover |
| `overlay-5-primary` | То же, primary-оверлей | Существующий |
| `card-image` | Bootstrap `card`: картинка 4:3 сверху, название и счётчик ниже | Placeholder (`dist/assets/img/image-placeholder.jpg`), равная высота (`h-100`), радиус из `Codeweber_Options::style('card-radius')` |
| `chips` | Пилюля `btn btn-sm btn-soft-primary` | Без картинки; радиус из `style('button')`; выводится «потоком» (см. ниже) |
| `card-subcats` | Карточка с картинкой (если есть), названием и списком дочерних терминов | Первые 6 + ссылка «View all» (`children_limit`); для `product_cat` берёт `cw_subcats_get_terms()` |

Исключены из выбора: `cells`, `cells-1` (рассчитаны на рамку-сетку блока Post Grid).

Шаблон получает `$term`, `$term_link`, `$image_url`, `$image_alt`, `$display`, `$template_args`. Из `cw_subcats_render_card()` в `$template_args` передаются: `image_size`, `enable_lift`, `show_term_count`, `hide_empty`, `exclude`, `full_overlay`.

`full_overlay` понимают шаблоны `overlay-5` и `overlay-5-primary`: добавляют на `.bottom-overlay` класс `bottom-overlay-full`, и тёмный градиент (`_overlay.scss`) закрывает всю карточку, а не нижние 50%. Нужен на мелких карточках, где многострочный заголовок иначе выходит на голое фото. По умолчанию `false`, поэтому Post Grid и остальные карточки не меняются; блок подкатегорий включает его всегда.

> Не использовать `stretched-link`: карточки целиком обёрнуты в `<a>` (`card-image`) или содержат отдельные ссылки.

### Разметка контейнера

**Grid** — Bootstrap, как у сетки товаров:

```html
<div class="cw-subcats cw-subcats-grid cw-subcats-tpl-card-image mb-10">
  <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-4 {grid-gap}">
    <div class="col">…карточка…</div>
  </div>
</div>
```

Отступы — `Codeweber_Options::style('grid-gap')`.

**Slider** — Swiper темы (инициализируется `window.theme.swiperSlider()`), без собственного JS:

```html
<div class="swiper-container cw-subcats cw-subcats-slider …"
     data-items="4" data-items-xs="2" data-items-sm="2" data-items-md="3" data-items-lg="3" data-items-xl="4"
     data-margin="20" data-nav="true" data-dots="false" data-loop="false" data-autoplay="false">
  <div class="swiper"><div class="swiper-wrapper">
    <div class="swiper-slide">…карточка…</div>
  </div></div>
</div>
```

**Flow-шаблоны** (по умолчанию только `chips`, список расширяется фильтром `cw_subcats_flow_templates`): колонки не используются.
- Grid → `d-flex flex-wrap gap-2`.
- Slider → `data-items-auto="true"`, слайды с классом `w-auto`.

---

## Хуки

| Хук | Тип | Описание |
|-----|-----|----------|
| `cw_subcats_templates` | filter | Список доступных шаблонов (`slug => label`) |
| `cw_subcats_flow_templates` | filter | Шаблоны, выводимые потоком, а не колонками |
| `woocommerce_product_subcategories_args` | filter (WC) | Применяется к запросу терминов — порядок и счётчики как в WC |

### Добавить свой вариант

1. Положить `my-style.php` в `templates/post-cards/taxonomy/` (или одноимённо в дочерней теме) с шапкой:

   ```php
   /**
    * Card Template: My Style
    * Description: What it looks like
    * Supports: title, image
    * Order: 40
    */
   ```
2. Шаблон появится в Redux и в мета-полях категорий автоматически. Вне `WP_DEBUG` результат скана кешируется в transient — сбросить: `cw_flush_post_card_templates_cache()` (или переключить тему / активировать плагин).

---

## Gotchas

- **Счётчики.** `$term->count` для родительской категории — «padded» (с учётом дочерних), т.к. запрос идёт с `pad_counts`, как в WooCommerce.
- **`hide_empty`** сравнивает именно padded-счётчик; родитель, у которого товары только в дочерних, не скрывается.
- **Приватные/черновые товары** в счётчики категорий не попадают и гостям не видны. При `hide_empty = on` подкатегории, где все товары приватные, скрыты; если в категории все товары приватные, гость видит только подкатегории (даже при Display type = Both). Для показа таких подкатегорий опубликуйте товары или выключите «Hide empty».
- **Блок не показывается, хотя подкатегории есть.** Сначала проверить Redux → Woocommerce → Subcategories → **Enable** (по умолчанию выключено и хранится в БД, при выкладке кода на другой сервер не переносится) и Display type категории.
- **PJAX.** Блок лежит внутри `#shop-pjax-wrapper`, поэтому обновляется вместе с ним. Инициализации из `theme.js` выполняются один раз при загрузке страницы, поэтому обработчик `cw:pjax:complete` в `src/assets/js/shop-pjax.js` повторяет их для подменённого HTML: `window.theme.swiperSlider()` (пропускает уже созданные инстансы), а также `span.bg` внутри `.overlay > a` / `.overlay > span` (без него не работает hover у `overlay-5*`) и Bootstrap-тултипы `tooltip` / `white-tooltip`. Overlay и тултипы сделаны **внутри `shop-pjax.js`**, в пределах контейнера и идемпотентно: `theme.imageHoverOverlay()` не защищён от повторных вызовов (задвоил бы `span.bg` по всей странице), а у child-тем `theme.js` может быть старее. Новый шаблон карточки, которому нужна JS-инициализация, добавляйте в тот же обработчик. **Правка в `src/`, поэтому нужна пересборка темы** — без неё в режиме Slider после смены per_page / вида / сортировки чипы слипаются (зазор 0), последний обрезан, листать нельзя. Режим Grid от этого не зависит.
- **Стрелки слайдера.** У слайдеров темы стрелки видны только при наведении на контейнер (`opacity: 0` у `.swiper-button`, `_carousel.scss`), а на телефоне hover нет. Поэтому блок подкатегорий в режиме Slider добавляет контейнеру модификатор **`nav-visible`** (если стрелки включены в Redux): стрелки видны всегда, у неактивной `opacity: .5`. Другие слайдеры темы не затронуты; модификатор можно использовать и в них. Дополнительный признак слайдера без кода: Redux → «Slider dots».
- **Chips + Slider.** Чипы лучше выглядят в Grid (переносятся на новые строки); Slider прокручивает их в одну строку. Подсказка есть в Redux у поля Layout.
- **Text Inverse.** Если на странице `<main class="content-wrapper text-inverse">` (светлый текст для тёмного фона, см. [TEXT_INVERSE.md](TEXT_INVERSE.md)), то и обычный текст, и ссылки (`link-dark` тоже) становятся светлыми. У карточек `card-image` и `card-subcats` фон белый, поэтому цвет в них задан явно через `text-dark` / `text-primary` — `!important`-утилиты это переживают, `link-dark` — нет. Новые шаблоны с белым фоном делайте так же.
- **Картинка в `ratio`.** Стили темы перебивают `height: 100%` у `.ratio > img`, и картинка вылезает за блок и закрывает текст. Поэтому у `<img>` явно стоят `w-100 h-100 object-fit-cover`, а у `<figure>` — `overflow-hidden`.
- **Ошибка консоли `Cannot read properties of undefined (reading 'rows')`.** Появляется при инициализации любого не-auto слайдера темы (одна строка: в `breakpoints` уходит `grid: undefined`), в том числе штатной галереи на странице товара. Слайдер при этом работает; к этому блоку ошибка отношения не имеет. У flow-шаблонов (`data-items-auto`) её нет.
- **Нет картинки у категории.** `card-image` показывает placeholder, `card-subcats` — карточку без картинки, `overlay-5*` — серый блок.
- **Кириллица / транслит** слагов шаблонов не нужны: слаги — латиница из имени файла.
