# Документация темы Codeweber

Добро пожаловать в документацию темы Codeweber. Эта документация содержит описание функций, API, шаблонов и инструкций по использованию различных компонентов темы.

## 📁 Структура документации

Документация организована по категориям для удобной навигации:

```
doc/
├── Shortcodes/              # Документация по шорткодам
│   ├── SHORTCODE_SOCIAL_LINKS.md
│   ├── SHORTCODE_MENU_COLLAPSE.md
│   ├── SHORTCODE_CW_BLOG_POSTS_SLIDER.md
│   └── menu-collapse-shortcode-php-examples.md
│
├── Functions/               # Документация по функциям
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
├── Other/                   # Разное
│   └── GULP.md
│
├── Tasks/                   # Задачи и чеклисты
│
├── Analysis/                # Анализ и отчеты
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
└── README.md
```

## Шорткоды

Документация по всем шорткодам темы:

- **[social_links](Shortcodes/SHORTCODE_SOCIAL_LINKS.md)** - Вывод социальных иконок
- **[menu_collapse](Shortcodes/SHORTCODE_MENU_COLLAPSE.md)** - Меню с Bootstrap Collapse

## Функции

Документация по основным функциям темы:

### Социальные сети

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

### AJAX функциональность

- **[Универсальный AJAX фильтр](ajax/AJAX_FILTER.md)** - Подробное руководство по использованию AJAX фильтрации для вакансий, статей, товаров и других типов контента
- [AJAX загрузка файлов](ajax/AJAX_DOWNLOAD_IMPLEMENTATION.md) - Реализация AJAX загрузки документов

### Изображения

- **[Руководство по созданию изображений](images/IMAGE_CREATION_GUIDE.md)** - Подробное руководство по системе изображений для записей

### Установка

- [Установка Ghostscript](install/GHOSTSCRIPT_INSTALL.md) - Инструкции по установке Ghostscript для работы с PDF

### Сборка и разработка

- **[Gulp руководство](GULP.md)** - Подробное руководство по системе сборки Gulp

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

**Подробная документация:** [Gulp руководство](GULP.md)

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

## 📝 Контрибуция

При добавлении новой функциональности:

1. Создайте соответствующий файл документации
2. Разместите в правильной категории
3. Обновите этот README.md
4. Следуйте существующему формату документации

## 📞 Поддержка

Для вопросов и предложений обращайтесь к команде разработки.

## 📄 Лицензия

Документация является частью темы Codeweber и следует лицензии темы.

---

**Последнее обновление:** 2024-12-13

