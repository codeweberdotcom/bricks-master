# Wishlist — Документация

Кастомная система избранного для WooCommerce. Не зависит от сторонних плагинов.

---

## Файловая структура

```
functions/integrations/wishlist/
├── class-cw-wishlist.php          # Главный класс: AJAX, хуки, установка БД
├── class-cw-wishlist-item.php     # Объект вишлиста пользователя (добавить/удалить/список)
├── class-cw-wishlist-ui.php       # UI: кнопки, шорткод, страница, меню аккаунта
├── class-cw-storage-interface.php # Интерфейс хранилища
├── class-cw-db-storage.php        # Хранилище в БД (авторизованные)
├── class-cw-session-storage.php   # Хранилище в cookie (гости)
├── class-cw-cookie-storage.php    # Вспомогательный класс для cookie-хранения
└── functions.php                  # Хелперы: cw_get_wishlist_url(), виджет иконки, AJAX создания страницы

src/assets/js/wishlist.js          # Исходник JS
dist/assets/js/wishlist.js         # Скомпилированный JS
src/assets/scss/theme/_wishlist.scss  # Стили (Bootstrap-классы + минимум кастомного)

templates/post-cards/product/shop2.php  # Карточка товара, поддерживает wishlist-режим
```

---

## Включение

Redux → **WooCommerce → Wishlist → Enable Wishlist** (switch).

Требования: WooCommerce активен, Redux Framework подключён.

---

## Redux-настройки

| Ключ | Тип | По умолчанию | Описание |
|------|-----|-------------|----------|
| `wishlist_enable` | switch | false | Включить вишлист |
| `wishlist_page` | select (pages) | — | Страница с шорткодом `[cw_wishlist]` |
| `wishlist_guests` | switch | true | Гости могут добавлять (cookie) |
| `wishlist_btn_on_loop` | switch | true | Кнопка на карточках в каталоге |
| `wishlist_btn_on_single` | switch | true | Кнопка на странице товара |
| `wishlist_feedback` | select | `spinner` | Обратная связь при добавлении (см. ниже) |
| `wishlist_toast` | checkbox | false | Показывать toast-уведомление |

### Варианты `wishlist_feedback`

| Значение | Поведение |
|----------|-----------|
| `spinner` | CSS border-spinner на самой кнопке (цвет `currentColor`; у `item-like` — красный) |
| `card` | Overlay-спиннер поверх карточки товара |
| `modal` | Spinner на кнопке + Bootstrap-модал после успешного добавления |
| `none` | Без визуальной обратной связи |

`wishlist_toast` работает **независимо** от `wishlist_feedback` — тост показывается при любом feedbackType если чекбокс включён.

---

## База данных

Таблицы создаются при первой активации темы или при сохранении настроек.

```sql
wp_cw_wishlists       (ID, user_id, date_created)
wp_cw_wishlist_products (ID, wishlist_id, product_id, date_added)
```

Проверка установки: `get_option('cw_wishlist_installed')`.

---

## PHP-классы

### `CW_Wishlist`

Главный класс, регистрирует AJAX-хуки.

| Метод | Описание |
|-------|----------|
| `ajax_add()` | `wp_ajax_cw_add_to_wishlist` — добавляет товар, возвращает `{added, count, product_name}` |
| `ajax_remove()` | `wp_ajax_cw_remove_from_wishlist` — удаляет, возвращает `{count}` |
| `install()` | Создаёт таблицы через `dbDelta()` |

### `CW_Wishlist_Item`

Объект вишлиста текущего пользователя. Для авторизованных — `CW_DB_Storage`, для гостей — `CW_Session_Storage` (cookie `cw_wishlist`).

```php
$wishlist = new CW_Wishlist_Item();
$wishlist->add( $product_id );        // → bool
$wishlist->remove( $product_id );     // → bool
$wishlist->is_in_wishlist( $id );     // → bool
$wishlist->get_all();                 // → array [['product_id' => int], ...]
$wishlist->get_count();               // → int
$wishlist->update_count_cookie();     // обновляет cookie cw_wishlist_count (для JS-виджета)
```

Глобальный экземпляр: `$GLOBALS['cw_wishlist_instance']` (доступен с хука `init`, приоритет 1+).

### `CW_Wishlist_UI`

Регистрирует:
- Хук `woocommerce_after_add_to_cart_button` → кнопка на single product (если включено)
- Шорткод `[cw_wishlist]` → страница вишлиста
- Пункт «Wishlist» в меню «Мой аккаунт»
- Скрипт `cw-wishlist` + локализацию `cwWishlist`
- Фильтр `is_woocommerce` → WC загружает свои скрипты на странице вишлиста

---

## Helper-функции

```php
cw_get_wishlist_url()       // URL страницы вишлиста из Redux или home_url('/wishlist/')
cw_get_wishlist_count()     // Кол-во товаров из cookie (быстро, без БД)
cw_render_wishlist_icon()   // Иконка-виджет для шапки (SVG сердце + счётчик)
```

### `cw_render_wishlist_icon( $args )`

```php
cw_render_wishlist_icon([
    'show_count' => true,   // Показывать badge со счётчиком
    'show_label' => false,  // Показывать текст «Wishlist»
]);
```

Выводит `.cw-wishlist-widget` — ссылку с иконкой и обновляемым счётчиком (`.cw-wishlist-widget__count`).

---

## Шорткод `[cw_wishlist]`

Выводит сетку товаров из вишлиста через шаблон `shop2.php` в wishlist-режиме.

**Wishlist-режим** карточки активируется через `$GLOBALS['cw_wishlist_render'] = true`:
- Фиксированные колонки: `col-6 col-md-4 col-xl-3`
- Добавляется `data-product-id` на обёртку
- Карточка оборачивается в `.card` с кнопкой `×` (`.cw-wishlist-remove`)
- `item-like` кнопка всегда видима (не только на hover)

---

## JavaScript: `CWWishlist`

Скрипт: `dist/assets/js/wishlist.js`, handle: `cw-wishlist`.

### Конфигурация `cwWishlist` (wp_localize_script)

```js
cwWishlist = {
    ajaxUrl,        // admin-ajax.php
    nonce,          // nonce для cw_wishlist_nonce
    wishlistUrl,    // URL страницы вишлиста
    isLoggedIn,     // 'yes' | 'no'
    guestsAllowed,  // 'yes' | 'no'
    loginUrl,       // URL /my-account/
    count,          // текущее кол-во товаров
    feedbackType,   // 'spinner' | 'card' | 'modal' | 'none'
    showToast,      // 'yes' | 'no'
    btnShape,       // CSS-класс формы кнопок из Codeweber_Options::style('button')
    i18n: {
        added,            // 'In Wishlist'
        add,              // 'Add to Wishlist'
        removed,          // 'Removed from Wishlist'
        loginNotice,      // 'Please log in...'
        removeNotice,     // 'Remove from Wishlist?'
        addedTitle,       // 'Added to Wishlist' (заголовок модала)
        continueShopping, // 'Continue Shopping' (кнопка модала)
        goToWishlist,     // 'Go to Wishlist' (кнопка модала)
    }
}
```

### Методы `CWWishlist`

| Метод | Описание |
|-------|----------|
| `init()` | Инициализация: счётчик, события, тултипы |
| `initTooltips()` | Bootstrap Tooltip на `.cw-wishlist-card [data-bs-toggle]` (для страницы вишлиста) |
| `bindEvents()` | Делегирование кликов на `.cw-wishlist-btn` и `.cw-wishlist-remove` |
| `handleToggle(btn)` | Добавить/удалить; если уже в вишлисте — переход к вишлисту (single) или удаление (loop) |
| `handleRemove(btn)` | AJAX-удаление, анимация карточки, обновление счётчика |
| `markAdded(btn)` | Добавляет `--active`, меняет href/title/label |
| `removeCard(productId)` | Fade-out + remove `.cw-wishlist-card`, показывает empty state |
| `showEmptyState()` | Заменяет `.cw-wishlist-grid` блоком «вишлист пуст» |
| `updateCountWidget(count)` | Обновляет `.cw-wishlist-widget__count` |
| `getModal()` | Создаёт Bootstrap-модал `#cwWishlistModal` в DOM (один раз, лениво) |
| `showWishlistModal(productName)` | Обновляет название товара в модале и показывает его |

### Логика feedback при добавлении

```
feedbackType === 'spinner' или 'modal'  → btn.classList.add('cw-wishlist-btn--loading')
feedbackType === 'card'                 → overlay-спиннер .cw-card-spinner на карточку
после success:
    feedbackType === 'modal'  → showWishlistModal(product_name)
    showToast === 'yes'       → CWNotify.show(...)  ← независимо от modal
```

### CSS-классы на кнопке `.cw-wishlist-btn`

| Модификатор | Условие |
|-------------|---------|
| `--active` | Товар в вишлисте |
| `--loading` | Во время AJAX-запроса |
| `--single` | Кнопка на странице товара |
| `--loop` | Кнопка на карточке в каталоге |

---

## Модальное окно `feedbackType = 'modal'`

Создаётся чисто через JS (`getModal()`), один раз при первом клике, затем переиспользуется.

**Структура:**
```html
<div class="modal fade" id="cwWishlistModal">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-5">
        <div class="mb-4">
          <i class="uil uil-heart-alt fs-60 text-red"></i>
        </div>
        <h5 class="mb-1">{i18n.addedTitle}</h5>
        <p class="text-ash text-break mb-5 cw-wishlist-modal__name">{product_name}</p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
          <button class="btn btn-sm btn-outline-ash has-ripple {btnShape}" data-bs-dismiss="modal">
            {i18n.continueShopping}
          </button>
          <a href="{wishlistUrl}" class="btn btn-sm btn-primary has-ripple {btnShape}">
            {i18n.goToWishlist}
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
```

**Используемые классы темы:**
- `fs-60` = 3rem (иконка)
- `text-red` = `#e2626b`
- `text-ash` = `#9499a3`
- `text-break` = Bootstrap word-break
- `btn-outline-ash` = нейтральная серая кнопка

---

## SCSS (`_wishlist.scss`)

Кастомный CSS минимален — всё остальное через Bootstrap/тему:

| Селектор | Назначение |
|----------|-----------|
| `.cw-wishlist-btn--loading i.uil` | `opacity: 0` — скрывает иконку во время загрузки |
| `.cw-wishlist-btn--loading .cw-wishlist-icon::after` | CSS border-spinner того же размера/цвета |
| `.cw-wishlist-btn.item-like.--loading` | Красный цвет спиннера для loop-кнопки |
| `.cw-wishlist-btn--active i.uil` | Красный цвет активной иконки |
| `.cw-wishlist-btn--single.--active` | Заливка btn-red при активном состоянии |
| `.cw-wishlist-icon` | `position: relative` — якорь для `::after` спиннера |
| `.cw-wishlist-widget` | Виджет в хедере |
| `.cw-wishlist-empty` | Блок «вишлист пуст» |
| `.cw-card-spinner.spinner-overlay` | Полупрозрачный overlay на карточке |
| `.cw-notify-container/item` | Стили CWNotify toast-уведомлений |
| `.cw-wishlist-page .item-like` | `opacity:1; right:1rem` — кнопка всегда видима на странице вишлиста |
| `.cw-wishlist-page .post-header .price` | `font-size: 0.8rem` — уменьшенный размер цены |

---

## AJAX-endpoints

| Action | Хук | Доступ | Параметры |
|--------|-----|--------|-----------|
| `cw_add_to_wishlist` | `wp_ajax[_nopriv]_cw_add_to_wishlist` | все | `nonce`, `product_id` |
| `cw_remove_from_wishlist` | `wp_ajax[_nopriv]_cw_remove_from_wishlist` | все | `nonce`, `product_id` |
| `cw_create_wishlist_page` | `wp_ajax_cw_create_wishlist_page` | admin | `nonce` |

Ответ `cw_add_to_wishlist`:
```json
{ "success": true, "data": { "added": true, "count": 3, "product_name": "Название товара" } }
```

---

## Хранилище

| Пользователь | Класс | Хранение |
|-------------|-------|----------|
| Авторизован | `CW_DB_Storage` | таблица `wp_cw_wishlist_products` |
| Гость | `CW_Session_Storage` | cookie `cw_wishlist` (JSON) |

При входе гостя товары из cookie **не мигрируют** автоматически в БД (реализация на стороне разработчика при необходимости).

Счётчик для JS-виджета хранится отдельно в cookie `cw_wishlist_count` (обновляется после каждой AJAX-операции).

---

## Создание страницы вишлиста

1. Redux → WooCommerce → Wishlist → поле «Wishlist Page» → кнопка **«Create Wishlist Page»**
2. Создаётся страница «Wishlist» с блоком Section + шорткодом `[cw_wishlist]`
3. ID страницы автоматически выбирается в select

Или вручную: создать страницу с `[cw_wishlist]` и выбрать её в Redux.

---

## Меню «Мой аккаунт»

Пункт «Wishlist» добавляется автоматически (перед «Выйти») если в Redux выбрана страница вишлиста. Помечается классом `is-active` когда пользователь на странице вишлиста.
