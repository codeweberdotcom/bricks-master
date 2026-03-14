---
name: design-extract
description: Извлечь цвета, шрифты, кнопки, формы, breadcrumb, навигацию с веб-страницы и применить к _user-variables.scss
argument-hint: <URL страницы-образца (одна или несколько через пробел)>
---

Извлеки дизайн-токены (цвета, типографика, кнопки, формы, breadcrumb, навигация) со страницы-образца и обнови `_user-variables.scss` темы CodeWeber.

**URL(ы) для анализа:** `$ARGUMENTS`

---

## Шаг 1: Получение дизайн-токенов

Для каждого URL из аргументов:

1. Открой страницу в Playwright:
   ```
   browser_navigate → URL
   ```

2. Дождись загрузки (browser_wait_for → networkidle или 3 секунды)

3. Сделай скриншот для визуального контекста:
   ```
   browser_take_screenshot
   ```

4. Выполни скрипт извлечения через `browser_evaluate`:
   Прочитай файл `.claude/skills/design-extract/scripts/extract-design-tokens.js`
   и выполни его содержимое через `browser_evaluate`.
   Скрипт возвращает JSON с полями:
   - `colors` — text, background, border, links, cssVariables
   - `typography` — rootFontSize, body (fontFamily, fontSize, fontWeight, lineHeight, **lineHeightRatio**), headings (h1-h6), usedFonts
   - `buttons` — styles (backgroundColor, color, borderRadius, padding, fontSize, fontWeight и т.д.)
   - `forms` — styles инпутов (height, fontSize, fontWeight, bg, borderColor, padding и т.д.), **focus** (bg, borderColor, boxShadow, bgChanged, borderChanged, shadowChanged)
   - `breadcrumb` — linkColor, dividerColor, activeColor, divider
   - `navigation` — fontSize, fontWeight, color, textTransform, letterSpacing

5. Сохрани результат — он понадобится на шагах 2 и 3.

---

## Шаг 2: Анализ и рекомендации

Прочитай текущие значения из:
- `src/assets/scss/_theme-colors.scss` — базовые цвета темы
- `src/assets/scss/_variables.scss` — все переменные (типографика, кнопки, компоненты)
- `src/assets/scss/_user-variables.scss` — текущие пользовательские переопределения

Сопоставь извлечённые токены с переменными темы и создай отчёт:

**Создай файл** `doc_claude/reports/YYYY-MM-DD/DESIGN-EXTRACT-[domain].md`:

```markdown
# Design Extract — [domain] — YYYY-MM-DD

## Источник
- URL: [url]
- Скриншот: (описание визуального впечатления)

## Извлечённые токены

### Цвета

| Роль | Извлечённый HEX | Текущая переменная | Текущее значение | Рекомендация |
|------|-----------------|-------------------|-----------------|--------------|
| Primary (основной акцент) | #XXXXXX | $primary / $blue | #3f78e0 | Заменить |
| Secondary | #XXXXXX | $secondary | $gray-400 | Заменить |
| Body text | #XXXXXX | $body-color | — | — |
| Body background | #XXXXXX | $body-bg | — | — |
| Link color | #XXXXXX | $link-color | — | — |
| Success | #XXXXXX | $green | #45c4a0 | Оставить |
| ... | ... | ... | ... | ... |

### Типографика

| Параметр | Извлечённое | Текущая переменная | Текущее значение | Рекомендация |
|----------|------------|-------------------|-----------------|--------------|
| Root font size | XXpx | $font-size-root | 20px | Изменить |
| Body font size | X.Xrem | $font-size-base | 0.8rem | Изменить |
| Body font family | "Font Name" | $font-family-sans-serif | Manrope | Изменить |
| Body font weight | 400 | $font-weight-normal | 500 | Изменить |
| Body line height | 1.X | $line-height-base | 1.7 | — |
| Headings font weight | XXX | $headings-font-weight | 700 | — |
| H1 size / weight | X.Xrem / XXX | $h1-font-size | — | — |
| H2 size / weight | X.Xrem / XXX | $h2-font-size | — | — |
| H3 size / weight | X.Xrem / XXX | $h3-font-size | — | — |
| H4 size / weight | X.Xrem / XXX | $h4-font-size | — | — |
| ... | ... | ... | ... | ... |

### Кнопки

| Параметр | Извлечённое | Текущая переменная | Текущее значение | Рекомендация |
|----------|------------|-------------------|-----------------|--------------|
| Padding Y | X.Xrem | $btn-padding-y | 0.5rem | Изменить |
| Padding X | X.Xrem | $btn-padding-x | 1.2rem | Изменить |
| Border radius | X.Xrem | $btn-border-radius / $border-radius | 0.4rem | Изменить |
| Font weight | XXX | $btn-font-weight | bold | — |
| Font size | X.Xrem | $btn-font-size | — | — |
| Border width | Xpx | $btn-border-width | 2px | — |
| ... | ... | ... | ... | ... |

### Формы (inputs)

| Параметр | Извлечённое | Текущая переменная | Текущее значение | Рекомендация |
|----------|------------|-------------------|-----------------|--------------|
| **Height** | XXpx | (вычисляемая) | — | **Подогнать через $form-floating-height** |
| Font size | X.Xrem | $input-font-size | 0.75rem | Изменить |
| Font weight | XXX | $input-font-weight | — | — |
| Background | #XXXXXX | $input-bg | body-bg | Изменить |
| Border color | #XXXXXX | $input-border-color | rgba($shadow-border, 0.07) | Изменить |
| Border radius | X.Xrem | $input-border-radius | $border-radius | — |
| Padding Y | X.Xrem | $input-padding-y | 0.6rem | **Вычислить по формуле** |
| Padding X | X.Xrem | $input-padding-x | 1rem | Изменить |
| **Focus: bg** | #XXXXXX | $input-focus-bg | — | Сравнить с blur |
| **Focus: border** | #XXXXXX | $input-focus-border-color | $focus-border | Сравнить с blur |
| **Focus: box-shadow** | none/значение | $input-focus-box-shadow | unset | Сравнить с blur |

> **Правило focus-стилей инпутов:** Всегда проверяй стили инпута при focus на образце.
> Скрипт возвращает `forms.focus` с полями `bgChanged`, `borderChanged`, `shadowChanged`.
> Если на образце при focus ничего не меняется — задай `$input-focus-border-color` равным `$input-border-color`,
> `$input-focus-box-shadow: none`, `$input-focus-bg` равным `$input-bg`.

> **Правило высоты инпутов:** Высота input на нашем сайте должна совпадать с образцом.
> Высота вычисляется: `height = paddingY×2 + fontSize×lineHeight + borderWidth×2`.
> Для подгонки высоты рассчитай `$input-padding-y`:
> `paddingY = (targetHeight - fontSize×$input-btn-line-height - borderWidth×2) / 2`, затем переведи в rem.

### Breadcrumb

| Параметр | Извлечённое | Текущая переменная | Текущее значение | Рекомендация |
|----------|------------|-------------------|-----------------|--------------|
| Link color | #XXXXXX | $breadcrumb-color | $gray-600 | — |
| Link font weight | XXX | — | — | — |
| Link font size | X.Xrem | — | — | — |
| Divider color | #XXXXXX | $breadcrumb-divider-color | rgba($gray-600, 0.35) | Изменить |
| Active color | #XXXXXX | $breadcrumb-active-color | $gray-600 | — |

### Навигация

| Параметр | Извлечённое | Текущая переменная | Текущее значение | Рекомендация |
|----------|------------|-------------------|-----------------|--------------|
| Font size | X.Xrem | $nav-link-font-size | 0.8rem | Изменить |
| Font weight | XXX | $nav-link-font-weight | $font-weight-bold | Изменить |
| Color | #XXXXXX | — | — | — |
| Text transform | none/uppercase | — | — | — |

## Шрифты для подключения
Если обнаружены Google Fonts или другие веб-шрифты, которых нет в теме:
- Название шрифта
- URL для подключения
- Рекомендация по подключению (enqueue в functions.php или @import)

## Переменные, которые нужно создать
Если обнаружены токены, для которых нет переменных в _variables.scss:
- Предложить имя переменной
- Значение
- Где определить
```

**Покажи отчёт пользователю и жди подтверждения** перед обновлением `_user-variables.scss`.

---

## Шаг 3: Обновление _user-variables.scss

После подтверждения пользователя, запиши переменные в файл
`src/assets/scss/_user-variables.scss`.

**Образец структуры** (по аналогии с дочерней темой Horizons — см. `wp-content/themes/horizons/src/assets/scss/_user-variables.scss`):

```scss
//--------------------------------------------------------------
// User Variables — переопределения для конкретного проекта
// Извлечено из: [URL]
// Дата: YYYY-MM-DD
//--------------------------------------------------------------

// ── Кастомные цвета (карта) ──
// Если на сайте-образце есть цвета, которых нет в теме,
// создай карту — они станут доступны как утилитарные классы (.text-*, .bg-*, .btn-*)
$custom-colors: (
  "brand-accent": #XXXXXX,
  "brand-dark": #XXXXXX,
  "brand-light": #XXXXXX,
);
$custom-theme-colors: $custom-colors;

// ── Основные цвета ──
$primary: #XXXXXX;           // (было: $blue / #3f78e0)
$primary-soft: #XXXXXX;      // Мягкий вариант primary

$body-bg: #XXXXXX;           // (было: $white)
$body-color: #XXXXXX;        // (было: зависит от $navy)
$dark: #XXXXXX;              // (было: $navy / #343f52)

// Переопределение именованных цветов — если нужно
// $blue:    #XXXXXX;   // (было: #3f78e0)
// $navy:    #XXXXXX;   // (было: #343f52)

// Серые — если палитра серых отличается
$gray-100: #XXXXXX;
$gray-200: #XXXXXX;
// ... $gray-300 — $gray-900

// Глобальное скругление
$border-radius: 0;           // (было: 0.4rem) — 0 для строгого дизайна

// ── Типографика ──
$font-family-sans-serif: "New Font", sans-serif;  // (было: Manrope)
$font-size-root: XXpx;       // (было: 20px)
$font-size-base: X.Xrem;     // (было: 0.8rem)
$font-weight-normal: 400;    // (было: 500)
$line-height-base: 1.X;      // (было: 1.7)

$h1-font-size: X.Xrem;
$h2-font-size: X.Xrem;
// ... только изменённые размеры заголовков

// ── Кнопки ──
$btn-border-width: 1px;      // (было: 2px)
$input-btn-line-height: 1;   // (было: зависит от Bootstrap)
$btn-font-weight: 600;       // (было: $font-weight-bold / 700)

// Размеры кнопок — все варианты
// Default
$btn-padding-y:     X.Xrem;  // (было: 0.5rem)
$btn-padding-x:     X.Xrem;  // (было: 1.2rem)
$btn-font-size:     X.Xrem;

// Extra Small (XS)
$btn-padding-y-xs:  X.Xrem;
$btn-padding-x-xs:  X.Xrem;
$btn-font-size-xs:  X.Xrem;

// Small (SM)
$btn-padding-y-sm:  X.Xrem;  // (было: 0.35rem)
$btn-padding-x-sm:  X.Xrem;  // (было: 0.9rem)
$btn-font-size-sm:  X.Xrem;

// Medium (MD)
$btn-padding-y-md:  X.Xrem;
$btn-padding-x-md:  X.Xrem;
$btn-font-size-md:  X.Xrem;

// Large (LG)
$btn-padding-y-lg:  X.Xrem;  // (было: 0.65rem)
$btn-padding-x-lg:  X.Xrem;  // (было: 1.4rem)
$btn-font-size-lg:  X.Xrem;

// Extra Large (ELG)
$btn-padding-y-elg: X.Xrem;
$btn-padding-x-elg: X.Xrem;
$btn-font-size-elg: X.Xrem;

// Font weights по размерам (если все одинаковые — можно только $btn-font-weight)
$btn-font-weight-xs:  600;
$btn-font-weight-sm:  600;
$btn-font-weight-md:  600;
$btn-font-weight-lg:  600;
$btn-font-weight-elg: 600;

// ── Формы ──
$input-font-size: X.Xrem;              // (было: 0.75rem)
$input-bg: #XXXXXX;                     // (было: body-bg / white)
$input-border-color: #XXXXXX;           // (было: rgba($shadow-border, 0.07))
$input-padding-y: X.Xrem;              // (было: 0.6rem)
$input-padding-x: X.Xrem;              // (было: 1rem)
$input-color: #XXXXXX;
$input-focus-border-color: #XXXXXX;
$input-focus-bg: #XXXXXX;
$form-floating-height: XXpx;
$form-floating-padding-x: X.Xrem;
$form-floating-padding-y: X.Xrem;

// ── Навигация ──
$nav-link-font-size: X.Xrem;
$nav-link-font-weight: 400;
$nav-link-text-transform: uppercase;    // или none
$dropdown-font-size: X.Xrem;
$dropdown-font-weight: 400;

// ── Аккордеон ──
// См. полный набор переменных в справочнике ниже

// ── Breadcrumb ──
$breadcrumb-divider-color: #XXXXXX;
$breadcrumb-color: #XXXXXX;
$breadcrumb-hover-color: $primary;
$breadcrumb-active-color: $primary;

// ── Карточки ──
$card-cap-padding-y: X.Xrem;
$card-cap-padding-x: X.Xrem;
$card-border-radius: $border-radius;

//--------------------------------------------------------------
// Кастомные CSS-правила (hover-эффекты, декоративные элементы)
//--------------------------------------------------------------

// Пример: text-transform для кнопок
.btn {
    text-transform: uppercase;
}

//--------------------------------------------------------------
// Импорт шрифтов
//--------------------------------------------------------------
//START IMPORT FONTS
// @import "fonts/NewFont";
//END IMPORT FONTS
```

**Правила записи:**
- Только переменные, значения которых **отличаются** от дефолтных
- Комментарий с прежним значением `// (было: ...)`
- Группировка по секциям: Цвета → Типографика → Кнопки → Формы → Навигация → Кастомные правила → Шрифты
- Все переменные **без** `!default` — они перехватят дефолты в `_variables.scss`
- CSS-правила (`.btn { text-transform: ... }`) допустимы — они попадут в итоговый CSS

**ВАЖНО:** Порядок импорта в `style.scss`:
```scss
@import "theme-colors";      // 1. Базовые цвета ($blue, $navy и т.д.)
@import 'user-variables';    // 2. Пользовательские переопределения ← СЮДА ПИШЕМ
@import "variables";          // 3. Все переменные с !default
```

Поэтому в `_user-variables.scss`:
- **Можно** переопределить цвета из `_theme-colors.scss` (они уже загружены и используют `!default`)
- **Можно** задать любые переменные из `_variables.scss` — они перехватят `!default`
- **Можно** использовать `$primary` и другие цвета из `_theme-colors.scss` в своих значениях

---

## Шаг 4: Проверка (опционально)

Если пользователь хочет сразу увидеть результат:
1. Запусти `/build` для компиляции темы
2. Открой страницу сайта в Playwright и сделай скриншот для сравнения

---

## Справочник переменных

### Цвета

**Именованные (из _theme-colors.scss):**
`$sky` (#5eb9f0), `$blue` (#3f78e0), `$grape` (#605dba), `$purple` (#747ed1), `$violet` (#a07cc5), `$pink` (#d16b86), `$fuchsia` (#e668b3), `$red` (#e2626b), `$orange` (#f78b77), `$yellow` (#fab758), `$green` (#45c4a0), `$leaf` (#7cb798), `$aqua` (#54a8c7), `$navy` (#343f52), `$ash` (#9499a3)

**Семантические (из _variables.scss):**
`$primary` (= $blue), `$secondary` (= $gray-400), `$success` (= $green), `$info` (= $sky), `$warning` (= $yellow), `$danger` (= $red)

**Серые:** `$gray-100` — `$gray-900`, `$white`, `$black`

**Тело и ссылки:** `$body-color`, `$body-bg`, `$link-color`, `$link-hover-color`

**Кастомные цвета (карта):** `$custom-colors`, `$custom-theme-colors` — создают утилитарные классы

### Типографика

| Переменная | Дефолт | Описание |
|------------|--------|----------|
| `$font-family-sans-serif` | Manrope, sans-serif | Основной шрифт |
| `$font-size-root` | 20px | Корень (rem-база) |
| `$font-size-base` | 0.8rem | Базовый размер |
| `$font-size-sm` | 0.7rem | Уменьшенный |
| `$font-size-lg` | 1rem | Увеличенный |
| `$h1-font-size` — `$h6-font-size` | — | Размеры заголовков |
| `$headings-font-weight` | 700 | Жирность всех заголовков |
| `$headings-color` | — | Цвет всех заголовков |
| `$font-weight-light` | 400 | Тонкий |
| `$font-weight-normal` | 500 | Нормальный |
| `$font-weight-bold` | 700 | Жирный |
| `$line-height-base` | 1.7 | Межстрочный |

### Кнопки

| Переменная | Дефолт | Описание |
|------------|--------|----------|
| `$btn-border-width` | 2px | Ширина рамки |
| `$input-btn-line-height` | 1.7 ($line-height-base) | Line-height кнопок и инпутов. Влияет на высоту! Вычислять: lineHeight / fontSize образца |
| `$btn-font-weight` | $font-weight-bold | Жирность (все размеры) |
| **Default** | | |
| `$btn-padding-y` / `$btn-padding-x` | 0.5rem / 1.2rem | Padding |
| `$btn-font-size` | — | Размер шрифта |
| **XS** | | |
| `$btn-padding-y-xs` / `$btn-padding-x-xs` | — | Padding XS |
| `$btn-font-size-xs` | — | Размер XS |
| **SM** | | |
| `$btn-padding-y-sm` / `$btn-padding-x-sm` | 0.35rem / 0.9rem | Padding SM |
| `$btn-font-size-sm` | — | Размер SM |
| **MD** | | |
| `$btn-padding-y-md` / `$btn-padding-x-md` | — | Padding MD |
| `$btn-font-size-md` | — | Размер MD |
| **LG** | | |
| `$btn-padding-y-lg` / `$btn-padding-x-lg` | 0.65rem / 1.4rem | Padding LG |
| `$btn-font-size-lg` | — | Размер LG |
| **ELG** | | |
| `$btn-padding-y-elg` / `$btn-padding-x-elg` | — | Padding ELG |
| `$btn-font-size-elg` | — | Размер ELG |
| **Per-size weights** | | |
| `$btn-font-weight-xs` — `$btn-font-weight-elg` | — | Жирность по размерам |

### Скругление

| Переменная | Дефолт | Определена в | Описание |
|------------|--------|-------------|----------|
| `$border-radius` | 0.4rem | _variables.scss | Глобальное скругление (кнопки, карточки, инпуты) |
| `$border-radius-sm` | 0.2rem | _variables.scss | SM |
| `$border-radius-lg` | 0.4rem | _variables.scss | LG |
| `$border-radius-xl` | 0.8rem | _variables.scss | XL |
| `$rounded-pill` | 1.5rem | _variables.scss | Для `.rounded-pill` класса |
| `$border-radius-pill` | 50rem | Bootstrap | Для `.rounded-pill` утилиты и `.btn-expand` |

### Формы

| Переменная | Дефолт | Описание |
|------------|--------|----------|
| `$input-font-size` | 0.75rem | Размер шрифта input |
| `$input-font-weight` | — | Жирность шрифта input |
| `$input-bg` | — | Фон input |
| `$input-color` | — | Цвет текста input |
| `$input-focus-border-color` | — | Бордер в фокусе |
| `$input-focus-bg` | — | Фон в фокусе |
| `$input-focus-color` | — | Цвет текста в фокусе |
| `$form-floating-height` | add(2.5rem, border) | Высота floating label input. **Обязательно подгоняй**, чтобы совпадала с образцом |
| `$form-floating-padding-x` / `$form-floating-padding-y` | — | Padding floating |

> **ВАЖНО: Высота инпутов.** На сайте используются floating-label инпуты (`.form-floating`). Их высота задаётся `$form-floating-height`, а НЕ вычисляется из padding+font. Для подгонки высоты к образцу: `$form-floating-height: calc(Xrem + 2px)`, где `Xrem = (targetHeight - 2) / rootFontSize`.

### Навигация

| Переменная | Дефолт | Описание |
|------------|--------|----------|
| `$nav-link-font-size` | — | Размер шрифта nav-link |
| `$nav-link-font-weight` | — | Жирность nav-link |
| `$nav-link-text-transform` | — | Трансформация (uppercase/none) |
| `$nav-link-letter-spacing` | — | Межбуквенный интервал |
| `$dropdown-font-size` | — | Размер шрифта dropdown |
| `$dropdown-font-weight` | — | Жирность dropdown |

### Аккордеон

| Переменная | Дефолт | Описание |
|------------|--------|----------|
| `$accordion-button-font-size` | — | Размер шрифта заголовка |
| `$accordion-button-font-weight` | — | Жирность заголовка |
| `$accordion-icon-font-size` | — | Размер иконки |
| `$accordion-icon-color` | — | Цвет иконки |
| `$accordion-icon-type` | — | "one" (с поворотом) или "two" (открыть/закрыть) |
| `$accordion-button-padding-left` | — | Padding (иконка слева) |
| `$accordion-button-padding-right` | — | Padding (иконка справа) |

### Breadcrumb

| Переменная | Дефолт | Описание |
|------------|--------|----------|
| `$breadcrumb-divider-color` | — | Цвет разделителя |
| `$breadcrumb-color` | — | Цвет текста |
| `$breadcrumb-hover-color` | — | Цвет при наведении |
| `$breadcrumb-active-color` | — | Цвет активного элемента |

### Карточки

| Переменная | Дефолт | Описание |
|------------|--------|----------|
| `$card-spacer-y` / `$card-spacer-x` | 2rem | Padding карточки |
| `$card-cap-padding-y` / `$card-cap-padding-x` | — | Padding шапки |
| `$card-border-radius` | $border-radius | Скругление |

### Брейкпоинты и контейнеры (если отличаются)

```scss
$grid-breakpoints: (xs: 0, sm: 576px, md: 768px, lg: 992px, xl: 1200px, xxl: 1400px);
$container-max-widths: (sm: 540px, md: 720px, lg: 960px, xl: 1140px, xxl: 1320px);
```
