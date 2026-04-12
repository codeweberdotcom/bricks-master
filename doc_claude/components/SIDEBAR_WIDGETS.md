# Sidebar Widgets - Default CPT Widgets

Система программных виджетов для сайдбаров CPT. Виджеты отображаются автоматически при установке темы и скрываются, когда пользователь добавляет свои виджеты в область виджетов.

---

## Архитектура

### Файлы

| Файл | Назначение |
| ---- | ---------- |
| `functions/sidebars.php` | Регистрация sidebar areas + все функции виджетов |
| `functions/sidebars-redux.php` | Динамическая регистрация sidebar areas для CPT через Redux |
| `sidebar-left.php` | Шаблон левого сайдбара |
| `sidebar-right.php` | Шаблон правого сайдбара |

### Хуки

| Хук | Когда срабатывает |
| ---- | ----------------- |
| `codeweber_before_sidebar` | **Всегда** перед виджетами (даже если виджетов нет). Используется для дефолтных виджетов CPT |
| `codeweber_after_sidebar` | **Всегда** после виджетов |

### Логика вывода (sidebar-left.php / sidebar-right.php)

```php
do_action('codeweber_before_sidebar', $post_type);  // всегда
if (is_active_sidebar($post_type)) {
    dynamic_sidebar($post_type);                      // только если пользователь добавил виджеты
}
do_action('codeweber_after_sidebar', $post_type);    // всегда
```

### Логика каждого виджета

```php
function codeweber_sidebar_widget_{cpt}($sidebar_id) {
    if ($sidebar_id !== '{cpt}') return;              // 1. Проверка CPT
    if (!post_type_exists('{cpt}')) return;            // 2. CPT существует
    if (!is_singular('{cpt}')) return;                 // 3. Только single-страницы
    if (is_active_sidebar('{cpt}')) return;            // 4. Пользователь НЕ добавил виджеты
    // ... рендер
}
add_action('codeweber_before_sidebar', 'codeweber_sidebar_widget_{cpt}');
```

---

## Зарегистрированные виджеты

### `codeweber_sidebar_widget_events`

**CPT:** `events`
**Содержимое:**
- Карточка с изображением события
- Бейджи категорий и форматов
- Детали: даты, место, организатор, цена
- Countdown-таймер (до открытия/закрытия регистрации)
- Кнопки "Добавить в календарь" (Apple Calendar, Google Calendar)
- Автор события (аватар, имя, должность)
- Счётчик мест (прогресс-бар, зарегистрировано/осталось)
- Кнопка регистрации (modal / external URL)
- Форма регистрации (form-floating: имя, email, телефон, комментарий)
- Карта Яндекс (если включена)
- JS-скрипт countdown-таймера

### `codeweber_sidebar_widget_vacancies`

**CPT:** `vacancies`
**Содержимое:**
- Карточка с изображением вакансии
- Бейджи типа и статуса (open/closed)
- Детали: локация, график, опыт, образование, зарплата
- Автор (HR-менеджер): аватар, имя, должность
- Кнопка скачивания PDF
- Кнопка отклика (CF7 или CodeWeber Forms через модалку)
- Карта Яндекс (если включена)

### `codeweber_sidebar_widget_legal`

**CPT:** `legal`
**Содержимое:**
- Навигация по всем legal-документам (нумерованный список)
- Текущий документ выделен классом `active`
- Скрытые записи (`_hide_from_archive`) не отображаются

### `codeweber_sidebar_widget_faq`

**CPT:** `faq`
**Содержимое:**
- Навигация по категориям FAQ (scroll-ссылки на якоря)
- Некатегоризированные FAQ в отдельном пункте
- Если нет категорий — ссылка на секцию "All FAQs"

---

## Управление из дочерней темы

### Отключить виджет

```php
remove_action( 'codeweber_before_sidebar', 'codeweber_sidebar_widget_events' );
remove_action( 'codeweber_before_sidebar', 'codeweber_sidebar_widget_vacancies' );
remove_action( 'codeweber_before_sidebar', 'codeweber_sidebar_widget_legal' );
remove_action( 'codeweber_before_sidebar', 'codeweber_sidebar_widget_faq' );
```

### Заменить виджет

```php
remove_action( 'codeweber_before_sidebar', 'codeweber_sidebar_widget_events' );
add_action( 'codeweber_before_sidebar', 'my_custom_events_sidebar' );
function my_custom_events_sidebar( $sidebar_id ) {
    if ( $sidebar_id !== 'events' ) return;
    if ( is_active_sidebar( 'events' ) ) return;
    if ( ! is_singular( 'events' ) ) return;
    // ... свой рендер
}
```

### Добавить виджет для нового CPT

```php
add_action( 'codeweber_before_sidebar', 'horizons_sidebar_widget_partners' );
function horizons_sidebar_widget_partners( $sidebar_id ) {
    if ( $sidebar_id !== 'partners' ) return;
    if ( is_active_sidebar( 'partners' ) ) return;
    if ( ! is_singular( 'partners' ) ) return;
    // ... рендер
}
```

**Важно:** sidebar area для CPT регистрируется автоматически через `sidebars-redux.php`, если CPT включён в Redux (`cpt_switch_{slug}`).

---

## Позиция и видимость сайдбара (Redux)

### Позиция

Управляется через Redux → CPT → **Sidebar Settings** → таб **Single** / **Archive**:

- `sidebar_position_single_{post_type}` — left / none / right (default: right)
- `sidebar_position_archive_{post_type}` — left / none / right (default: right)

Per-post override через метаполя: `custom-page-sidebar-type` + `custom-page-sidebar-position`.

Функция: `get_sidebar_position($opt_name)` в `functions/sidebars.php`.

---

### Breakpoint (с какого экрана виден сайдбар)

Redux → CPT → **Sidebar Settings** → таб **Breakpoint**:

- `sidebar_breakpoint_{post_type}` — always / sm / md / lg / xl (default: xl)

Функция: `get_sidebar_breakpoint($opt_name): string` в `functions/sidebars.php`.

| Значение | Поведение |
| -------- | --------- |
| `always` | Виден всегда, `col-12 col-md-4`, без sticky, порядок через Bootstrap order |
| `sm` | Скрыт до 576px: `col-12 col-sm-4 d-none d-sm-block sticky-sidebar` |
| `md` | Скрыт до 768px: `col-12 col-md-4 d-none d-md-block sticky-sidebar` |
| `lg` | Скрыт до 992px: `col-12 col-lg-4 d-none d-lg-block sticky-sidebar` |
| `xl` | Скрыт до 1200px: `col-12 col-xl-4 d-none d-xl-block sticky-sidebar` (по умолчанию) |

**Режим `always`:**

- `sticky-sidebar` не добавляется
- Правый сайдбар: `order-first order-md-last` (на мобильном — над контентом)
- Левый сайдбар: натуральный HTML-порядок (первый в DOM = первый на экране)

Шаблоны: `sidebar-right.php`, `sidebar-left.php`.

---

### Глобальные отступы контента и сайдбара

Redux → **Theme Style → Grid Gutters**:

- `content_padding_mobile` — py-4 / py-6 / py-8 / **py-10** / py-12 / py-14
- `content_padding_desktop` — py-8 / py-10 / py-12 / **py-14** / py-16 / py-20

Функция-хелпер: `get_content_padding_classes(): string` в `functions/sidebars.php`.

```php
// Возвращает строку вида "py-10 py-md-14"
$padding = get_content_padding_classes();
```

Применяется автоматически в `sidebar-left.php`, `sidebar-right.php`, `single.php` и всех `archive-*.php`.

---

## Связанная документация

- [HOOKS_REFERENCE.md](../api/HOOKS_REFERENCE.md) — полный каталог хуков (секция Sidebar Display)
- [CHILD_THEME_AI_RULES.md](../architecture/CHILD_THEME_AI_RULES.md) — правила работы с дочерней темой
- [REDUX_OPTIONS.md](../settings/REDUX_OPTIONS.md) — настройки Redux
