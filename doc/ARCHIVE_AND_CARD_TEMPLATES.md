# Логика шаблонов архивов и карточек для всех типов записей

## Общая схема

В теме используется **двухуровневая структура**:

1. **Шаблоны архивов** (`templates/archives/{post_type}/`) — отвечают за **сетку и цикл**: обёртка (row, grid), колонки и вызов карточек в цикле `while (have_posts())`.
2. **Шаблоны карточек** — только **разметка одной записи** (карточка, элемент списка), без обёрток сетки (без `<div class="row">`, без `<div class="col-...">`).

Так архивная страница и AJAX-фильтр используют одни и те же шаблоны: на архиве — основной запрос WordPress, при AJAX — подменённый глобальный `$wp_query`.

---

## Расположение файлов

| Тип | Путь | Содержимое |
|-----|------|------------|
| Главный архив страницы | `archive-{post_type}.php` (корень темы) | Header, сайдбары, выбор шаблона из Redux, вызов `get_template_part('templates/archives/...')`, пагинация. |
| Шаблон варианта архива | `templates/archives/{post_type}/{template}_1.php`, `..._2.php`, … | Сетка (row/col или grid) + цикл по постам + вызов карточки. |
| Карточка записи | `templates/post-cards/{post_type}/...` или внутри `templates/archives/...` | Только разметка одной карточки (без row/col). |

Для части CPT карточки лежат прямо в `templates/archives/{post_type}/` (например staff, offices, faq, testimonials). Для вакансий карточки вынесены в `templates/post-cards/vacancies/`.

---

## Вакансии (vacancies)

### Файлы

- **Архив страницы:** `archive-vacancies.php`
- **Варианты архива:**  
  - `templates/archives/vacancies/vacancies_1.php` — список с фильтрами, группировка по типу.  
  - `templates/archives/vacancies/vacancies_2.php` — сетка карточек (row + col + цикл).  
  - `templates/archives/vacancies/vacancies_3.php` — сетка карточек в другом стиле (row + col + цикл).
- **Карточки (только разметка карточки, без col):**  
  - `templates/post-cards/vacancies/grid-card.php`  
  - `templates/post-cards/vacancies/style3-card.php`  
  - `templates/post-cards/vacancies/list-item.php` (для vacancies_1)

### Логика

- **vacancies_1** — особый вариант: сам шаблон содержит форму фильтра, группировку по типам и вызов `list-item`. Сетку в нём не трогаем.
- **vacancies_2 и vacancies_3:**  
  - В **шаблоне архива** (vacancies_2.php / vacancies_3.php):  
    - обёртка `<div class="row g-3 mb-5">`,  
    - цикл `while (have_posts())`,  
    - для каждого поста: `<div class="col-md-6 col-lg-4">` + `get_template_part('.../grid-card')` или `style3-card` + `</div>`.  
  - В **карточках** (`grid-card.php`, `style3-card.php`) — только разметка карточки (например `<a class="card">` или `<div class="card">`), **без** колонки.

В `archive-vacancies.php` для вариантов 2 и 3 вызывается один раз:

```php
get_template_part("templates/archives/vacancies/{$templateloop}");
```

Цикл и сетка — внутри этого шаблона, дублирования row/loop в `archive-vacancies.php` нет.

### AJAX

При фильтрации для `vacancies_2` и `vacancies_3` в `functions/ajax-filter.php`:

1. Формируется `WP_Query` с фильтрами.
2. Сохраняется текущий `$GLOBALS['wp_query']`.
3. Подменяется: `$GLOBALS['wp_query'] = $query`.
4. Вызывается тот же шаблон архива: `get_template_part('templates/archives/vacancies/vacancies_2')` или `vacancies_3`.
5. Восстанавливается прежний `$GLOBALS['wp_query']`.

Таким образом, разметка при AJAX совпадает с разметкой на архивной странице; дублировать сетку в PHP не нужно.

---

## Остальные типы записей (staff, offices, faq, testimonials, …)

- **Архив страницы:** `archive-{post_type}.php` — получает из Redux `archive_template_select_{post_type}`, формирует путь к шаблону `templates/archives/{post_type}/{template}.php`.
- **Шаблоны в `templates/archives/{post_type}/`** устроены по-разному:
  - Либо один файл = и сетка, и карточка (всё в одном шаблоне, часто с `.item`/`.col` внутри шаблона).
  - Либо шаблон архива делает row/loop и подключает карточку через `get_template_part` (аналогично vacancies_2/3).

Рекомендация при добавлении новых вариантов архива: **сетку и цикл держать в шаблоне архива**, а разметку одной записи — в отдельном файле карточки (или внутри того же файла, но без дублирования row/col в главном archive-*.php).

---

## Создание нового варианта архива (по аналогии с vacancies_2)

1. **Карточка** в `templates/post-cards/{post_type}/{style}-card.php` (например `grid-card.php`, `style3-card.php`): только разметка одной записи (без `<div class="row">`, без `<div class="col-...">`).
2. **Шаблон архива** в `templates/archives/{post_type}/{template}.php`:
   - проверка `have_posts()`;
   - обёртка сетки (например `<div class="row g-3 mb-5">`);
   - цикл `while (have_posts()) : the_post();`;
   - колонка + `get_template_part('templates/post-cards/.../grid-card')` (или другой шаблон карточки) + закрытие колонки;
   - закрытие обёртки.
3. В **archive-{post_type}.php** для этого варианта — только один вызов `get_template_part("templates/archives/{post_type}/{template}")` без своего цикла и row.
4. Если есть **AJAX-фильтр** — в обработчике подменить `$wp_query` и вызвать тот же `get_template_part('templates/archives/...')`, затем восстановить запрос и вызвать `wp_reset_postdata()` (один раз после вывода).

---

## Кратко

- **Карточки** — только разметка одной записи.  
- **Шаблоны архивов** — сетка + цикл + вызов карточек.  
- **archive-{post_type}.php** — выбор шаблона и один вызов шаблона архива (без дублирования цикла/сетки).  
- **AJAX** — тот же шаблон архива при подменённом `$wp_query`.

Связанные документы: [ARCHIVE_TEMPLATES.md](modules/ARCHIVE_TEMPLATES.md), [ajax/AJAX_FILTER.md](ajax/AJAX_FILTER.md).
