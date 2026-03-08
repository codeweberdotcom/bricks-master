# Документация темы Codeweber

Добро пожаловать в документацию темы Codeweber. Эта документация содержит описание функций, API, шаблонов и инструкций по использованию различных компонентов темы.

## 📁 Структура документации

Документация организована по категориям для удобной навигации:

```
doc/
├── Shortcodes/              # Документация по шорткодам (19 файлов)
│   ├── SHORTCODE_ADDRESS.md
│   ├── SHORTCODE_AJAX_SEARCH_FORM.md
│   ├── SHORTCODE_BREADCRUMBS.md
│   ├── SHORTCODE_CODEWEBER_FORM.md
│   ├── SHORTCODE_CW_BLOG_POSTS_SLIDER.md
│   ├── SHORTCODE_FAQ_ACCORDION.md
│   ├── SHORTCODE_GET_CONTACT.md
│   ├── SHORTCODE_GETTHEMEBUTTON.md
│   ├── SHORTCODE_GETTHEMEFORM.md
│   ├── SHORTCODE_HTML_BLOCK.md
│   ├── SHORTCODE_MENU_COLLAPSE.md
│   ├── SHORTCODE_MENU_LIST.md
│   ├── SHORTCODE_PDN_SECTIONS.md
│   ├── SHORTCODE_REDUX_OPTION.md
│   ├── SHORTCODE_SITE_DOMAIN.md
│   ├── SHORTCODE_SITE_DOMAIN_LINK.md
│   ├── SHORTCODE_SOCIAL_LINKS.md
│   ├── SHORTCODE_UNIVERSAL_TITLE.md
│   └── menu-collapse-shortcode-php-examples.md
│
├── Functions/               # Документация по функциям
│   ├── FUNCTION_CODEWEBER_NAV.md
│   ├── FUNCTION_SOCIAL_LINKS.md
│   ├── FUNCTION_CODEWEBER_SINGLE_SOCIAL_LINKS.md
│   ├── FUNCTION_STAFF_SOCIAL_LINKS.md
│   └── FUNCTION_VACANCY_SOCIAL_LINKS.md
│
├── Integrations/            # Документация по интеграциям
│   ├── dadata-integration.md
│   ├── dadata-integration-plan.md
│   └── woocommerce-template-migration.md
│
├── Other/                   # Сборка и инструменты
│   └── GULP.md
│
├── ajax/                    # AJAX функциональность
│   ├── AJAX_FILTER.md
│   └── AJAX_DOWNLOAD_IMPLEMENTATION.md
│
├── modules/                 # Документация по модулям
│   ├── CPT_CREATION.md
│   ├── SIDEBARS.md
│   ├── ARCHIVE_TEMPLATES.md
│   ├── METAFIELDS.md
│   ├── SINGLE_TEMPLATES.md
│   ├── REDUX_CPT_OPTIONS.md
│   └── YANDEX_MAPS.md
│
├── images/                  # Работа с изображениями
│   └── IMAGE_CREATION_GUIDE.md
│
├── install/                 # Установка и настройка
│   └── GHOSTSCRIPT_INSTALL.md
│
├── ARCHIVE_AND_CARD_TEMPLATES.md
├── NAV_WALKER_USAGE.md                 # Руководство: WP_Bootstrap_Navwalker и CodeWeber_Menu_Collapse_Walker
└── README.md
```

## Шорткоды

Документация по всем шорткодам темы:

| Шорткод | Описание |
|---------|----------|
| [address](Shortcodes/SHORTCODE_ADDRESS.md) | Адрес из Redux |
| [ajax_search_form](Shortcodes/SHORTCODE_AJAX_SEARCH_FORM.md) | Форма AJAX-поиска |
| [breadcrumbs](Shortcodes/SHORTCODE_BREADCRUMBS.md) | Хлебные крошки |
| [codeweber_form](Shortcodes/SHORTCODE_CODEWEBER_FORM.md) | Формы (стили для CF7) |
| [cw_blog_posts_slider](Shortcodes/SHORTCODE_CW_BLOG_POSTS_SLIDER.md) | Слайдер постов блога |
| [faq_accordion](Shortcodes/SHORTCODE_FAQ_ACCORDION.md) | FAQ-аккордеон |
| [get_contact](Shortcodes/SHORTCODE_GET_CONTACT.md) | Контактные данные |
| [getthemebutton](Shortcodes/SHORTCODE_GETTHEMEBUTTON.md) | Стиль кнопки для форм |
| [getthemeform](Shortcodes/SHORTCODE_GETTHEMEFORM.md) | Стиль полей для форм |
| [html_block](Shortcodes/SHORTCODE_HTML_BLOCK.md) | Вывод HTML-блока из Redux |
| [menu_collapse](Shortcodes/SHORTCODE_MENU_COLLAPSE.md) | Меню с Bootstrap Collapse |
| [menu_list](Shortcodes/SHORTCODE_MENU_LIST.md) | Список пунктов меню |
| [pdn_sections](Shortcodes/SHORTCODE_PDN_SECTIONS.md) | Секции PDN |
| [redux_option](Shortcodes/SHORTCODE_REDUX_OPTION.md) | Вывод опции Redux |
| [site_domain](Shortcodes/SHORTCODE_SITE_DOMAIN.md) | Домен сайта |
| [site_domain_link](Shortcodes/SHORTCODE_SITE_DOMAIN_LINK.md) | Ссылка на главную |
| [social_links](Shortcodes/SHORTCODE_SOCIAL_LINKS.md) | Социальные иконки |
| [universal_title](Shortcodes/SHORTCODE_UNIVERSAL_TITLE.md) | Универсальный заголовок |
| [menu_collapse — примеры PHP](Shortcodes/menu-collapse-shortcode-php-examples.md) | Примеры вызова из PHP |

## Функции

Документация по основным функциям темы:

### Социальные сети

- **[codeweber_nav()](Functions/FUNCTION_CODEWEBER_NAV.md)** - Навигация из таксономии или CPT (collapse-вёрстка)
- **[social_links()](Functions/FUNCTION_SOCIAL_LINKS.md)** - Основная функция вывода социальных иконок
- **[codeweber_single_social_links()](Functions/FUNCTION_CODEWEBER_SINGLE_SOCIAL_LINKS.md)** - Для single-страниц
- **[staff_social_links()](Functions/FUNCTION_STAFF_SOCIAL_LINKS.md)** - Для сотрудников
- **[vacancy_social_links()](Functions/FUNCTION_VACANCY_SOCIAL_LINKS.md)** - Для вакансий

## 🚀 Быстрый старт

### Шорткоды

- **[social_links](Shortcodes/SHORTCODE_SOCIAL_LINKS.md)** - Вывод социальных иконок с различными стилями
- **[menu_collapse](Shortcodes/SHORTCODE_MENU_COLLAPSE.md)** - Меню с Bootstrap Collapse
- **[cw_blog_posts_slider](Shortcodes/SHORTCODE_CW_BLOG_POSTS_SLIDER.md)** - Слайдер постов блога

### Функции

- **[codeweber_nav()](Functions/FUNCTION_CODEWEBER_NAV.md)** - Навигация из таксономии или CPT (collapse-вёрстка)
- **[social_links()](Functions/FUNCTION_SOCIAL_LINKS.md)** - Основная функция вывода социальных иконок
- **[codeweber_single_social_links()](Functions/FUNCTION_CODEWEBER_SINGLE_SOCIAL_LINKS.md)** - Обёртка для single-страниц
- **[staff_social_links()](Functions/FUNCTION_STAFF_SOCIAL_LINKS.md)** - Для сотрудников (из метаполей)
- **[vacancy_social_links()](Functions/FUNCTION_VACANCY_SOCIAL_LINKS.md)** - Для вакансий

### Модули и разработка

- **[Создание новых CPT](modules/CPT_CREATION.md)** - Полное руководство по созданию Custom Post Types
- **[Добавление сайдбаров](modules/SIDEBARS.md)** - Как добавлять новые сайдбары в тему
- **[Архивные страницы](modules/ARCHIVE_TEMPLATES.md)** - Создание архивных страниц и шаблонов (пример staff)
- **[Архивы и карточки: логика шаблонов](ARCHIVE_AND_CARD_TEMPLATES.md)** - Разделение архив/карточка, использование в AJAX для всех типов записей
- **[Метаполя](modules/METAFIELDS.md)** - Как и куда добавлять метаполя к single записям
- **[Single шаблоны](modules/SINGLE_TEMPLATES.md)** - Создание шаблонов Single записей на примере staff
- **[Redux настройки CPT](modules/REDUX_CPT_OPTIONS.md)** - Управление CPT через Redux Framework (шаблоны, сайдбары, заголовки)
- **[Yandex Maps](modules/YANDEX_MAPS.md)** - Интеграция Яндекс карт с автоматическим спиннером загрузки
- **[Использование Nav Walker](NAV_WALKER_USAGE.md)** — руководство по WP_Bootstrap_Navwalker и CodeWeber_Menu_Collapse_Walker: вызов wp_nav_menu(), параметры, примеры

### AJAX функциональность

- **[Универсальный AJAX фильтр](ajax/AJAX_FILTER.md)** - Подробное руководство по использованию AJAX фильтрации для вакансий, статей, товаров и других типов контента
- [AJAX загрузка файлов](ajax/AJAX_DOWNLOAD_IMPLEMENTATION.md) - Реализация AJAX загрузки документов

### Изображения

- **[Руководство по созданию изображений](images/IMAGE_CREATION_GUIDE.md)** - Подробное руководство по системе изображений для записей

### Установка

- [Установка Ghostscript](install/GHOSTSCRIPT_INSTALL.md) - Инструкции по установке Ghostscript для работы с PDF

### Сборка и разработка

- **[Gulp руководство](Other/GULP.md)** - Подробное руководство по системе сборки Gulp

## 📚 Основные разделы

### Модули разработки

Тема Codeweber предоставляет полную систему для создания и настройки Custom Post Types.

**Основные возможности:**
- Создание новых типов записей (CPT)
- Настройка архивных и single страниц
- Добавление метаполей и сайдбаров
- Гибкая система шаблонов

**Документация:**
- [Создание CPT](modules/CPT_CREATION.md) - Полное руководство по созданию Custom Post Types
- [Архивные шаблоны](modules/ARCHIVE_TEMPLATES.md) - Создание архивных страниц
- [Single шаблоны](modules/SINGLE_TEMPLATES.md) - Создание шаблонов для отдельных записей
- [Метаполя](modules/METAFIELDS.md) - Добавление кастомных полей
- [Сайдбары](modules/SIDEBARS.md) - Регистрация и использование сайдбаров
- [Redux настройки CPT](modules/REDUX_CPT_OPTIONS.md) - Полное руководство по настройкам CPT через Redux
- [Yandex Maps](modules/YANDEX_MAPS.md) - Полное руководство по интеграции Яндекс карт

### AJAX функции

Тема Codeweber включает мощную систему AJAX для создания интерактивных элементов без перезагрузки страницы.

**Основные возможности:**
- Универсальный фильтр контента
- AJAX загрузка файлов
- Динамическое обновление контента

**Документация:**
- [Универсальный AJAX фильтр](ajax/AJAX_FILTER.md) - Полное руководство
- [AJAX загрузка](ajax/AJAX_DOWNLOAD_IMPLEMENTATION.md)

### Работа с изображениями

Тема автоматически обрабатывает изображения разных размеров для различных типов контента.

**Особенности:**
- Автоматическая генерация размеров
- Оптимизация для разных устройств
- Поддержка различных форматов
- Динамическая фильтрация по типам записей

**Документация:**
- [Руководство по созданию изображений](images/IMAGE_CREATION_GUIDE.md) - Полное руководство по системе изображений

## 🔧 Разработка

### Структура темы

```
codeweber/
├── functions/           # PHP функции темы
│   ├── ajax-filter.php # AJAX фильтр
│   ├── cpt/           # Custom Post Types
│   └── ...
├── templates/          # Шаблоны
│   ├── archives/      # Архивы
│   └── singles/       # Отдельные записи
├── src/               # Исходные файлы
│   └── assets/
│       ├── js/        # JavaScript
│       └── scss/      # Стили
└── dist/              # Скомпилированные файлы
```

### Сборка проекта

Тема использует Gulp для сборки:

```bash
cd wp-content/themes/codeweber
npm install
npm run build
```

**Подробная документация:** [Gulp руководство](Other/GULP.md)

### JavaScript модули

- `ajax-filter.js` - Универсальный AJAX фильтр
- `ajax-download.js` - Загрузка файлов
- `restapi.js` - REST API клиент
- `theme.js` - Основные функции темы

### CSS структура

- `style.scss` - Основные стили
- `colors/` - Цветовые схемы
- `fonts/` - Шрифты

## 📖 Примеры использования

### Использование AJAX фильтра

```html
<form class="codeweber-filter-form" 
      data-post-type="vacancies" 
      data-container=".results">
  <select name="category" data-filter-name="category">
    <option value="">Все</option>
  </select>
</form>
<div class="results"><!-- Результаты --></div>
```

Подробнее: [AJAX фильтр](ajax/AJAX_FILTER.md)

## 🐛 Отладка

### Включение debug режима

В `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Проверка ошибок

Логи находятся в:
- PHP ошибки: `wp-content/debug.log`
- JavaScript ошибки: Консоль браузера (F12)

## 📝 Контрибуция и правила структуры

При добавлении новой функциональности:

1. Создайте соответствующий файл документации в нужной категории:
   - **Shortcodes/** — один файл на шорткод, имя `SHORTCODE_<NAME>.md`
   - **Functions/** — функции темы, имя `FUNCTION_<NAME>.md`
   - **modules/** — CPT, шаблоны, сайдбары, метаполя, интеграции с сервисами
   - **ajax/** — всё, что связано с AJAX
   - **Integrations/** — сторонние сервисы (платежи, карты, CRM и т.п.)
   - **images/** — работа с медиа и размерами изображений
   - **install/** — пошаговая установка софта (например, Ghostscript)
   - **Other/** — сборка, Gulp, прочие инструменты
2. Обновите этот README.md (структуру и при необходимости раздел «Шорткоды» / «Функции»).
3. Ссылки на другие документы указывайте относительными путями (например `../modules/CPT_CREATION.md`).

## 📞 Поддержка

Для вопросов и предложений обращайтесь к команде разработки.

## 📄 Лицензия

Документация является частью темы Codeweber и следует лицензии темы.

---

**Последнее обновление:** 2024-12-13

