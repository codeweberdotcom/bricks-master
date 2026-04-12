# Hoger — Настройка дизайна (2026-04-12)

Сессия по приведению дочерней темы **hoger** к фирменному стилю сайта: шрифт, типографика, цвета, навигация, пагинация.

---

## 1. Прозрачный хедер — sticky остаётся тёмным

**Проблема:** при `transparent + dark` хедер при прокрутке переключался на белый.

**Причина:** в `theme.js` был `onStick` callback, который снимал класс `navbar-dark` и добавлял `navbar-light`.

**Файлы изменены (codeweber parent):**

### `src/assets/js/theme.js`
Удалён `onStick` callback (переключение `navbar-dark` → `navbar-light` при скролле).

### `src/assets/scss/theme/_navbar.scss`
Добавлено правило для sticky dark navbar:

```scss
.navbar-clone.navbar-stick.navbar-dark {
    background: rgba($dark, 0.97) !important;
    // + фиксы для логотипа (светлый/тёмный вариант)
}
```

Изменено: `navbar-bg-dark` использует `$dark` вместо хардкода `$gray-800`.

**Принцип:** `transparent` хедер всегда предполагает `dark` (светлый текст/лого на тёмном фоне). При sticky сохраняет тёмный фон.

---

## 2. Цвет $dark (#292728) для всех тёмных фонов

**Проблема:** `.bg-dark`, `footer.bg-dark`, `navbar-bg-dark` использовали Bootstrap `$gray-800` (#21262c) вместо кастомного `$dark` (#292728).

**Файлы изменены (codeweber parent):**

### `src/assets/scss/theme/_navbar.scss`
```scss
// Было: background: rgba(#21262c, ...)
// Стало:
background: rgba($dark, 0.97);
```

### `src/assets/scss/theme/_wrappers.scss`
```scss
// Было: background: $gray-800 !important
// Стало:
footer.bg-dark,
.footer.bg-dark {
  background: $dark !important;
}

// Angled footer border colors — тоже $dark
footer.bg-dark.angled {
  &.lower-end:after,
  &.upper-end:before  { border-right-color: $dark !important; }
  &.lower-start:after,
  &.upper-start:before { border-left-color: $dark !important; }
}
```

**Принцип:** `$dark` определяется в `hoger/_user-variables.scss` как `#292728`. Этот цвет должен применяться ко всем тёмным секциям. `$gray-800` Bootstrap — не наш цвет.

---

## 3. Шрифт GillSans Pro Cyrillic

**Шрифт:** GillSans Pro Cyrillic — только два начертания:
- `GillSansProCyrillic-Light.woff2/.woff` → `font-weight: 300`
- `GillSansProCyrillic-Medium.woff2/.woff` → `font-weight: 500`

**Важно:** Regular (400) с кириллицей у GillSans Pro **не существует**. Используем 300 как основной (normal) и 500 как полужирный (bold).

**Файлы шрифтов (hoger):**
```
src/assets/fonts/GillSans/
├── GillSansProCyrillic-Light.woff
├── GillSansProCyrillic-Light.woff2
├── GillSansProCyrillic-Medium.woff
└── GillSansProCyrillic-Medium.woff2
```

**`src/assets/scss/fonts/GillSans.scss`:**
```scss
@font-face {
  font-family: 'GillSans';
  src: url('../fonts/GillSans/GillSansProCyrillic-Light.woff2') format('woff2'),
       url('../fonts/GillSans/GillSansProCyrillic-Light.woff') format('woff');
  font-weight: 300;
  font-style: normal;
  font-display: swap;
}
@font-face {
  font-family: 'GillSans';
  src: url('../fonts/GillSans/GillSansProCyrillic-Medium.woff2') format('woff2'),
       url('../fonts/GillSans/GillSansProCyrillic-Medium.woff') format('woff');
  font-weight: 500;
  font-style: normal;
  font-display: swap;
}

@import "../../../../../codeweber/node_modules/bootstrap/scss/mixins";

$font-family-base:      "GillSans", sans-serif;
$font-family-secondary: "GillSans", sans-serif;
```

**Внимание:** `GillSans.scss` импортируется в конце `_user-variables.scss`. Переменные Bootstrap (`$font-family-base` и т.д.) из этого файла **не переопределяют** ранее объявленные переменные в `_user-variables.scss` — Bootstrap SCSS переменные не перезаписываются после первого объявления с `!default`. Но для ясности основные объявления — в `_user-variables.scss`.

---

## 4. Типографика hoger (`_user-variables.scss`)

### Размеры шрифтов
```scss
$font-size-root:  16px;    // (было: 20px)
$font-size-base:  1rem;    // 16px — основной размер
$font-size-sm:    0.875rem; // 14px
```

### Веса через переменные (не хардкод)
```scss
$font-weight-normal: 300;  // Light — основной вес
$font-weight-bold:   500;  // Medium — жирный

// Все остальные переменные используют эти две:
$font-weight-bold:       500;
$headings-font-weight:   $font-weight-normal;  // 300
$display-font-weight:    $font-weight-normal;
$lead-font-weight:       $font-weight-normal;
$btn-font-weight:        $font-weight-normal;
$nav-link-font-weight:   $font-weight-normal;
$dropdown-font-weight:   $font-weight-normal;
$accordion-button-font-weight: $font-weight-normal;
// ... и т.д.
```

**Принцип:** хардкод числа весов запрещён — только `$font-weight-normal` / `$font-weight-bold`.

### Заголовки и display — uppercase −10%
Все h1-h6 и display-1-6 в uppercase (`letter-spacing: 0.04em`). Размеры уменьшены на ~10% для компенсации визуального увеличения от uppercase:
```scss
$h1-font-size: 2.7rem;   // было 3rem
$h2-font-size: 2rem;     // было 2.25rem
// ...
```

---

## 5. Навигация

```scss
$nav-link-font-size:  1rem;
$dropdown-font-size:  1rem;
$btn-border-width:    1px;
```

Letter-spacing reset для nav-link и dropdown-item (компенсирует глобальный `letter-spacing: 0.04em` заголовков):
```scss
.nav-link,
.dropdown-item {
    letter-spacing: normal;
}
```

---

## 6. `.shadow-hoger` — кастомная тень

Утилитарный класс для фирменной тени (полупрозрачный тауп):

```scss
// В _user-variables.scss
.shadow-hoger {
    box-shadow: 0 .25rem 1.75rem rgba(156, 136, 111, 0.17) !important;
}
```

`rgba(156, 136, 111, ...)` — это `$blue` (#9c886f) в RGB. Применять вместо Bootstrap `.shadow` там, где нужна тёплая тень в тон бренду.

---

## 7. `text-reset` в пагинации

**Проблема:** пагинация внутри секций с цветным текстом наследовала цвет ссылок от родителя.

**Файлы изменены (codeweber parent):**

### `functions/bootstrap/bootstrap_pagination.php`
```php
'before_output' => '<nav class="d-flex justify-content-center text-reset" aria-label="pagination"><ul class="pagination">',
```

### `woocommerce/loop/pagination.php`
```html
<nav class="d-flex justify-content-center text-reset mt-6" aria-label="...">
```

---

## Итоговая структура `_user-variables.scss` (ключевые секции)

```
Основные цвета ($blue, $navy, $dark, $body-color)
Типографика (шрифт, размеры, веса, line-height)
Display-заголовки (scaled −10%)
H1-H6 (scaled −10%)
Кнопки (border-radius: pill, border-width: 1px, все размеры)
Формы (input border, padding, focus)
Декоративные линии
Навигация (font-size, font-weight, reset letter-spacing)
Аккордеон
Пагинация
Blockquote, Breadcrumb
CSS overrides (.btn uppercase, h1-h6 uppercase, word-spacing reset, nav-link letter-spacing reset)
UI элементы (размеры шрифтов для мета/фильтров/каунтеров)
Custom shadow (.shadow-hoger)
Minor UI fixes
$swiper-arrow-bg
Импорт шрифтов (@import "fonts/GillSans")
```
