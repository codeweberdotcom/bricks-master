# Design Extract — allcorp3-demo.ru — 2026-03-14

## Источник
- URL: https://allcorp3-demo.ru/info/more/buttons/
- Сайт: Aspro AllCorp3 — корпоративный шаблон на Bitrix
- Визуал: Чистый корпоративный дизайн. Синий акцент (#365edc), серый текст, белый фон. Кнопки со скруглением 4px, без uppercase. Два шрифта: Montserrat (основной) + Roboto (заголовки H1).

## Извлечённые токены

### Цвета

| Роль | Извлечённый HEX | Переменная CodeWeber | Текущее значение | Рекомендация |
|------|-----------------|---------------------|-----------------|--------------|
| Default/Акцент (кнопки, ссылки) | **#365edc** | `$blue` | #3f78e0 | **Заменить** |
| Primary (Bootstrap btn-primary) | **#00b290** | `$green` / `$primary` | #45c4a0 / $blue | Решить: primary = синий или зелёный? |
| Success | **#84bc29** | `$success` / `$leaf` | #45c4a0 / #7cb798 | **Заменить** если нужно |
| Info | **#0ca9e3** | `$info` / `$sky` | $sky / #5eb9f0 | **Заменить** |
| Warning | **#f38b04** | `$warning` / `$orange` | $yellow / #f78b77 | **Заменить** |
| Danger | **#dc130d** | `$danger` / `$red` | $red / #e2626b | **Заменить** |
| Body text | **#555555** | `$body-color` | зависит от $navy | **Заменить** |
| Headings | **#333333** | — | — | Через `$headings-color` |
| Body background | **#ffffff** | `$body-bg` | $white | Совпадает |
| Secondary bg (секции) | **#f8f8f8** | `$gray-100` | — | **Заменить** если нужно |
| Link color | **#365edc** | `$link-color` | — | Будет = `$blue` |
| Border основной | **#e3e3e3** | `$border-color` | — | **Задать** |
| Dark | **#333333** | `$dark` / `$navy` | #343f52 | **Заменить** |

### Типографика

| Параметр | Извлечённое | Переменная CodeWeber | Текущее значение | Рекомендация |
|----------|------------|---------------------|-----------------|--------------|
| Root font size | **15px** | `$font-size-root` | 20px | **Заменить** |
| Body font | **Montserrat** | `$font-family-sans-serif` | Manrope | **Заменить** |
| Body font size | **15px (1rem)** | `$font-size-base` | 0.8rem | **1rem** (= rootFontSize) |
| Body font weight | **400** | `$font-weight-normal` | 500 | **Заменить** |
| Body line height | **25px (1.667)** | `$line-height-base` | 1.7 | Близко, оставить 1.7 |
| Body color | **#555555** | `$body-color` | — | **Задать** |
| H1 font | **Roboto** | — | — | Нужна доп. переменная |
| H1 size | **42px (2.8rem)** | `$h1-font-size` | — | **Задать** |
| H2 size | **30px (2rem)** | `$h2-font-size` | — | **Задать** |
| H4 size | **20.6px (1.375rem)** | `$h4-font-size` | — | **Задать** |
| Headings weight | **700** | `$headings-font-weight` | — | Совпадает с Bootstrap default |
| Headings color | **#333333** | `$headings-color` | — | **Задать** |

### Кнопки

| Размер | Padding Y | Padding X | Font Size | Font Weight | Класс |
|--------|-----------|-----------|-----------|-------------|-------|
| **Default** | 0.6rem (9px) | 1.333rem (20px) | 0.933rem (14px) | 700 | `.btn` |
| **XS** | 0.6rem (9px) | 0.933rem (14px) | 0.733rem (11px) | 400 | `.btn-xs` |
| **SM** | 0.6rem (9px) | 1rem (15px) | 0.8rem (12px) | 700 | `.btn-sm` |
| **MD** | 0.6rem (9px) | 1rem (15px) | 0.867rem (13px) | 700 | `.btn-md` |
| **LG** | 0.867rem (13px) | 1.6rem (24px) | 1rem (15px) | 700 | `.btn-lg` |
| **ELG** | 1.067rem (16px) | 1.733rem (26px) | 1.067rem (16px) | 700 | `.btn-elg` |

**Общие параметры кнопок:**
- Border radius: **4px (0.267rem)**
- Border width: **1px**
- Text transform: **none** (без uppercase)
- Letter spacing: **normal**

### Сравнение с текущими значениями CodeWeber

| Переменная | AllCorp3 | CodeWeber | Отличие |
|------------|----------|-----------|---------|
| `$btn-padding-y` | 0.6rem | 0.5rem | +0.1rem |
| `$btn-padding-x` | 1.333rem | 1.2rem | +0.133rem |
| `$btn-font-size` | 0.933rem | — | Новая |
| `$btn-border-width` | 1px | 2px | -1px |
| `$border-radius` | 0.267rem | 0.4rem | -0.133rem |
| `$btn-font-weight` | 700 | bold (700) | Совпадает |

## Шрифты для подключения

1. **Montserrat** — основной шрифт сайта. Google Fonts. Нужно подключить вместо Manrope.
2. **Roboto** — используется для H1. Google Fonts. Опционально — можно оставить один Montserrat.

## Особенности сайта-образца

- Это **НЕ Bootstrap** сайт — это Aspro/Bitrix шаблон. CSS-переменные Bootstrap (--bs-*) отсутствуют.
- У них `.btn-default` = наш `.btn-primary` (синий залитый)
- У них `.btn-primary` = зелёный (#00b290) — это Bootstrap-primary, но визуально используется редко
- Основной рабочий цвет — **#365edc** (синий), он же default, он же link color
