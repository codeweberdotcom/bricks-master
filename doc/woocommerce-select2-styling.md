# Стилизация Select2 (WooCommerce) в теме codeweber

Select2/SelectWoo снова включён на странице «Редактировать адрес» — отображается поиск по странам/регионам. Ниже план, как привести его вид к стилю темы (Bootstrap / form-select).

---

## 1. Где применяется Select2

- **Страница:** `/my-account/edit-address/` (billing и shipping).
- **Элементы:** выпадающие «Страна» и «Регион» (если регион — select).
- **Классы:** WooCommerce вешает Select2 на `select.country_select` и `select.state_select`; контейнер получает классы `.select2-container`, `.select2-selection`, `.select2-selection__rendered`, выпадающий список — `.select2-dropdown`, поиск — `.select2-search__field`.

---

## 2. Что стилизовать (приоритет)

| Элемент | Класс / селектор | Цель |
|--------|-------------------|------|
| Поле выбора (триггер) | `.select2-container .select2-selection--single` | Высота, граница, скругление, фон — как у `.form-select`. |
| Текст выбранного значения | `.select2-selection__rendered` | Цвет, шрифт, отступы. |
| Стрелка | `.select2-selection__arrow` | Цвет/иконка как у темы (см. `$form-select-indicator` в `_variables.scss`). |
| Выпадающий список | `.select2-container--default .select2-results__option` | Подсветка при наведении, отступы. |
| Поле поиска | `.select2-search--dropdown .select2-search__field` | Как `.form-control`: граница, padding, border-radius. |
| Контейнер выпадающего списка | `.select2-dropdown` | Граница, тень, скругление в стиле карточек темы. |

Ограничивать стили контекстом, чтобы не задеть другие страницы (чекout и т.д.): например, только внутри `.woocommerce-EditAddressForm` или `.woocommerce-address-fields`.

---

## 3. Откуда брать значения (SCSS)

В теме уже заданы переменные для нативного select в `src/assets/scss/_variables.scss`:

- Граница: `$form-select-border-color`, `$form-select-border-radius`
- Отступы: `$form-select-padding-y`, `$form-select-padding-x`
- Шрифт: `$form-select-font-size`, `$form-select-color`, `$form-label-color`
- Фокус: `$form-select-focus-border-color`, `$form-select-focus-box-shadow`
- Стрелка: `$form-select-indicator`, `$form-select-indicator-color`, `$form-select-bg-position`

Имеет смысл использовать эти переменные в стилях для Select2, чтобы вид совпадал с `.form-select`.

---

## 4. Файл для стилей

- **Вариант A:** отдельный файл в теме, например `src/assets/scss/theme/_woocommerce-select2.scss`, подключённый в основной сборке после Bootstrap и WooCommerce.
- **Вариант B (реализован):** блок в конце файла `src/assets/scss/theme/_forms.scss` с областью видимости под `.woocommerce-EditAddressForm` и `.woocommerce-address-fields` (только страница edit-address).

Внутри — селекторы под контекст формы редактирования адреса, например:

```scss
.woocommerce-EditAddressForm,
.woocommerce-address-fields {
  .select2-container--default .select2-selection--single {
    height: auto;
    padding: $form-select-padding-y $form-select-padding-x;
    border: 1px solid $form-select-border-color;
    border-radius: $form-select-border-radius;
    // ...
  }
  .select2-search__field {
    border: 1px solid $form-select-border-color;
    border-radius: $form-select-border-radius;
    padding: $form-select-padding-y $form-select-padding-x;
  }
  // и т.д.
}
```

---

## 5. Порядок работ (краткий план)

1. Создать/выбрать SCSS-файл для переопределения Select2 (п. 4).
2. Подключить переменные темы (`_variables.scss` или уже подключённый файл с ними).
3. Стилизовать триггер (поле выбора) — высота, граница, фон, стрелка.
4. Стилизовать выпадающий список и поле поиска — граница, отступы, hover.
5. Проверить на странице edit-address (billing и shipping) и при необходимости сузить селекторы по контексту.
6. Собрать стили (gulp/npm) и проверить в браузере.

После этого Select2 на «Редактировать адрес» будет с поиском и визуально ближе к нативному `.form-select` темы.
