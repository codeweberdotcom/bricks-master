# Панели администратора

## Карта страниц в WordPress Admin

```
Внешний вид
└── Параметры темы (Redux Framework)   ← Главный центр настроек темы
    ├── General
    ├── Header Settings
    ├── Footer Settings
    ├── Blog / Archive
    ├── Single Post
    ├── Colors
    ├── Typography
    ├── Integrations
    └── ...другие разделы

Подписки на рассылку                   ← Newsletter-модуль
├── Подписчики
├── Настройки
└── Импорт/экспорт

Документация темы                       ← Собственная вкладка помощи
(вкладка в верхнем меню Help)

Инструменты
├── Экспорт персональных данных        ← WordPress Privacy + Personal Data V2
└── Удаление персональных данных       ← WordPress Privacy + Personal Data V2

Инструменты
└── PD Providers Test                  ← (только при WP_DEBUG=true)
```

---

## Redux Framework — основная страница настроек

**Путь:** Внешний вид → Параметры темы
**Ключ опции:** `redux_demo`
**Файл конфигурации:** `redux-framework/sample/theme-config.php` + `redux-framework/theme-settings/theme-settings.php`

Читать настройки:
```php
// Через обёртку (предпочтительно)
$value = Codeweber_Options::get('setting_key');

// Напрямую через Redux
global $opt_name;
$value = Redux::get_option($opt_name, 'setting_key', 'default');
```

Redux инициализируется на `after_setup_theme` с приоритетом 30. До этого момента `Redux::get_option()` вернёт null.

---

## Newsletter: страницы в Admin

Файлы: `functions/integrations/newsletter-subscription/admin/`

| Страница | Класс | Slug | Что делает |
|---------|-------|------|-----------|
| Список подписчиков | `NewsletterSubscriptionAdmin` | `newsletter-subscriptions` | Таблица подписчиков с поиском и фильтром по статусу |
| Настройки | `NewsletterSubscriptionSettings` | `newsletter-settings` | Тема письма, шаблон, тексты страницы отписки |
| Импорт/экспорт | `NewsletterSubscriptionImportExport` | `newsletter-import-export` | CSV-импорт и экспорт базы подписчиков |

Все страницы добавляются как submenu к корневому пункту «Подписки на рассылку» (`newsletter-subscriptions`).

---

## Admin Menu: кастомные пункты меню

### Настройки пунктов навигации (`admin_menu.php`)

Добавляет дополнительные поля в редактор пунктов меню (`Внешний вид → Меню`):

| Поле | Meta-ключ | Описание |
|------|----------|---------|
| Smooth Scroll | `_custom_menu_checkbox` | Включить плавную прокрутку для этого пункта |
| Mega Menu | `_mega_menu` | Отобразить sub-меню как мега-меню |

### Профиль пользователя (`admin_user_profil.php`)

Добавляет дополнительные поля на страницу профиля пользователя (`Пользователи → Профиль`).

### Медиабиблиотека (`admin_media.php`)

Дополнения к медиабиблиотеке.

### Gutenberg (`admin-gutenberg.php`)

Настройки редактора Gutenberg.

### Privacy (`admin_privacy.php`)

Дополнения к разделу конфиденциальности.

---

## CF7: панели в редакторе формы

При активном Contact Form 7 добавляются вкладки в редактор форм (`Контактные формы → редактировать`):

| Вкладка | Файл | Описание |
|---------|------|---------|
| Form Type | `cf7.php` | Тип формы → `data-form-type` атрибут |
| Consents | `cf7-consents-panel.php` | Привязка документов согласий к acceptance-полям |

---

## Страница документации темы

**Файл:** `functions/documentation.php`

Добавляет вкладку **"Документация"** в стандартную панель помощи WordPress (кнопка Help в правом верхнем углу). Описывает возможности темы для авторов контента.

---

## Personal Data V2: тестовая страница

**Файл:** `functions/integrations/personal-data-v2/test-providers.php`
**Условие загрузки:** только при `WP_DEBUG=true`

Создаёт страницу в меню **Инструменты → PD Providers Test**. Показывает список зарегистрированных GDPR-провайдеров и позволяет проверить наличие данных по email.

Для деактивации: убрать `define('WP_DEBUG', true)` из `wp-config.php` или закомментировать строку в `functions.php`:
```php
// if (defined('WP_DEBUG') && WP_DEBUG) {
//     require_once .../test-providers.php;
// }
```

---

## Быстрый доступ к ключевым страницам

| Задача | URL в Admin |
|--------|------------|
| Настройки темы | `wp-admin/themes.php?page=redux_demo` |
| Список подписчиков newsletter | `wp-admin/admin.php?page=newsletter-subscriptions` |
| Настройки newsletter | `wp-admin/admin.php?page=newsletter-settings` |
| Экспорт персональных данных | `wp-admin/tools.php?page=export_personal_data` |
| Удаление персональных данных | `wp-admin/tools.php?page=remove_personal_data` |
| Тест GDPR-провайдеров | `wp-admin/tools.php?page=pd-providers-test` |
