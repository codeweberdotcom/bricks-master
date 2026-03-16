# Shop PJAX — Plan

**Feature:** PJAX-переключатель колонок товаров (и основа для будущих фильтров)
**Status:** In Progress
**Date:** 2026-03-16

---

## Цель

Переключение количества колонок (2/3/4) на странице магазина без полной перезагрузки страницы — через PJAX (Fetch + частичный рендер).

**Вдохновлено:** WoodMart theme (jQuery PJAX на `.wd-products-shop-view a`)
**Наш подход:** Custom PJAX без библиотеки, на нативном `fetch()`.

---

## Принцип работы

```
1. Пользователь кликает на .shop-per-row-btn (2/3/4 колонки)
2. JS перехватывает клик, делает fetch(url, { headers: { 'X-PJAX': 'true' } })
3. PHP в archive-product.php детектирует заголовок X-PJAX
4. PHP пропускает get_header()/get_footer(), отдаёт только контент #shop-pjax-container
5. JS заменяет innerHTML контейнера, вызывает history.pushState(url)
6. JS ре-инициализирует Isotope в новом контенте
7. Браузер обновляет URL без перезагрузки
```

---

## Файлы

### Изменяемые

| Файл | Что меняем |
|------|-----------|
| `woocommerce/archive-product.php` | PJAX-детектор + обёртка `#shop-pjax-container` |
| `functions/enqueues.php` | Подключение `shop-pjax.js` только на shop/category |

### Создаваемые

| Файл | Назначение |
|------|-----------|
| `src/assets/js/shop-pjax.js` | PJAX-логика: fetch, замена контента, history, Isotope |
| `src/assets/scss/woocommerce/_shop-pjax.scss` | Стили loading-анимации |

---

## archive-product.php: PJAX-логика

### Детектор

```php
$is_pjax = ! empty( $_SERVER['HTTP_X_PJAX'] );
```

### Структура с PJAX

```php
if ( ! $is_pjax ) {
    get_header();
    get_pageheader();
}
```

```html
<!-- если НЕ pjax — обёртка section/container/row -->
<div id="shop-pjax-container" class="col-lg-9 order-lg-2">
    <!-- переключатель колонок -->
    <!-- сетка товаров -->
    <!-- пагинация -->
</div>
<!-- если НЕ pjax — сайдбар + закрывающие теги -->
```

```php
if ( ! $is_pjax ) {
    get_footer();
}
```

**PJAX-ответ содержит:** только содержимое `#shop-pjax-container` (без него самого).
**Полный ответ содержит:** всю страницу с header/footer, `#shop-pjax-container` включён в разметку.

---

## shop-pjax.js: структура

```javascript
(function () {
  'use strict';

  // Делегированный обработчик — работает после PJAX-замены контента
  document.addEventListener('click', function (e) {
    var link = e.target.closest('.pjax-link'); // класс для всех PJAX-ссылок
    if (!link) return;
    e.preventDefault();
    pjaxLoad(link.href);
  });

  function pjaxLoad(url) {
    var container = document.getElementById('shop-pjax-container');
    if (!container) return;

    container.classList.add('shop-pjax-loading');

    fetch(url, {
      headers: { 'X-PJAX': 'true', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    })
      .then(function (r) { return r.text(); })
      .then(function (html) {
        container.innerHTML = html;
        history.pushState({ pjax: true }, '', url);
        container.classList.remove('shop-pjax-loading');
        initIsotope(container);
      })
      .catch(function () {
        // Fallback: обычная навигация
        window.location.href = url;
      });
  }

  function initIsotope(container) {
    var grid = container.querySelector('.isotope');
    if (!grid || typeof Isotope === 'undefined') return;
    // Ждём загрузки изображений, потом инициализируем
    imagesLoaded(grid, function () {
      new Isotope(grid, { itemSelector: '.item', layoutMode: 'fitRows' });
    });
  }

  // Обработка кнопки Back/Forward
  window.addEventListener('popstate', function (e) {
    if (e.state && e.state.pjax) {
      pjaxLoad(location.href);
    }
  });
})();
```

### Класс `pjax-link`

В `archive-product.php` кнопкам переключателя добавляем класс `pjax-link`:
```php
class="shop-per-row-btn pjax-link <?php echo $per_row === $cols ? 'active' : ''; ?>"
```

Когда в будущем добавятся фильтры — их ссылки тоже получат `pjax-link`, и заработают автоматически.

---

## SCSS: loading-анимация

```scss
#shop-pjax-container {
  transition: opacity 0.2s ease;

  &.shop-pjax-loading {
    opacity: 0.4;
    pointer-events: none;
  }
}
```

---

## enqueues.php: подключение

```php
function codeweber_enqueue_shop_pjax() {
    if ( ! function_exists( 'is_woocommerce' ) ) return;
    if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) return;

    $path = get_template_directory() . '/src/assets/js/shop-pjax.js';
    $url  = get_template_directory_uri() . '/src/assets/js/shop-pjax.js';

    if ( ! file_exists( $path ) ) return;

    wp_enqueue_script(
        'shop-pjax',
        $url,
        [],
        codeweber_asset_version( $path ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'codeweber_enqueue_shop_pjax', 30 );
```

---

## Будущее: добавление фильтров

1. Добавить класс `pjax-link` к ссылкам фильтров
2. Фильтры обновляют URL с параметрами (`?filter_cat=X&per_row=3`)
3. `pjaxLoad()` подхватывает любую ссылку с `.pjax-link` — фильтры работают автоматически
4. Для сортировки — аналогично

---

## Checklist реализации

- [ ] `archive-product.php` — PJAX-детектор
- [ ] `archive-product.php` — `#shop-pjax-container` обёртка
- [ ] `archive-product.php` — `pjax-link` на кнопках переключателя
- [ ] `src/assets/js/shop-pjax.js` — создать
- [ ] `src/assets/scss/woocommerce/_shop-pjax.scss` — создать
- [ ] `functions/enqueues.php` — подключить `shop-pjax.js`
- [ ] `npm run build` или проверить что Gulp подхватит
- [ ] Проверить в браузере: переключение без перезагрузки
- [ ] Проверить: URL обновляется
- [ ] Проверить: Back/Forward работает
- [ ] Проверить: Isotope ре-инициализируется
