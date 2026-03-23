# Changelog

Все значимые изменения темы CodeWeber будут задокументированы в этом файле.

---

## [Unreleased]

### Added

- **CPT Events (Мероприятия)**: полный модуль мероприятий — регистрация CPT `events` с таксономиями `event_category` и `event_format`, мета-поля дат (начало/конец события, открытие/закрытие приёма заявок), местоположение, адрес, организатор, цена, внешняя ссылка, количество мест
- **CPT Event Registrations (Заявки)**: приватный CPT `event_registrations` с кастомными статусами (Новая/Подтверждена/Отменена/Ожидает оплаты), admin-колонки, фильтр по мероприятию, массовые действия, счётчик-бейдж в меню
- **Event Registration REST API**: `POST codeweber/v1/events/register` — запись на мероприятие с honeypot+nonce, проверкой статуса, дубликатов и уведомлением на email; `GET codeweber/v1/events/calendar` — фид FullCalendar
- **archive-events.php**: архив мероприятий с двойным видом (FullCalendar v6 / Bootstrap-таблица), переключатель с сохранением в localStorage, фильтр по категориям
- **single-events.php**: страница мероприятия — галерея (Bootstrap carousel + GLightbox), видео (YouTube/Vimeo/Rutube/VK Video/загрузка), форма записи с envelope-анимацией, счётчик мест с progress-bar, кнопка «Поделиться»
- **Event Gallery Metabox**: FilePond + SortableJS массовая загрузка фото (паттерн Projects)
- **Events Settings Page**: страница настроек в меню мероприятий — показ мест, текст кнопки, success-сообщение, email уведомлений
- **`codeweber_events_get_registration_status()`**: центральный PHP-хелпер статуса записи с логикой дат и мест
- **`codeweber_events_get_video_glightbox()`**: хелпер определения типа видео (YouTube/Vimeo/Rutube/VK/файл) с GLightbox-данными
- **`src/assets/scss/theme/_events.scss`**: стили для архива, карточек, single-страницы, формы, галереи, счётчика мест
- **Переводы**: добавлены ~120 строк русского перевода для всего модуля мероприятий

---

## [1.0.5] - 2025-04-26

### Added

- **Floating Social Widget**: плавающая кнопка соцсетей с анимацией и позиционированием
- **Share Buttons**: стили и JS для кнопок «Поделиться» на страницах
- **Social Links Shortcode**: шорткод `[social_links]` с документацией и примерами
- **Divider Text**: компонент разделителя с текстом и гибким выравниванием
- **CodeWeber Forms**: типы форм «обратный звонок» и «резюме» с улучшенной обработкой ID
- **Цвета Dewalt и Frost**: новые цвета в палитру темы с Bootstrap-утилитами
- **Custom Post Header**: выбор header per page через Redux Metabox
- **Accordion стили**: кастомные стили для аккордеонов Bootstrap
- **WooCommerce**: интеграция My Account, шаблон `form-edit-address.php`, русские регионы
- **DaData Address**: JS-автоподсказки для адресов в WooCommerce через DaData API
- **Menu Collapse Walker**: навигация с collapse-поведением для вертикальных меню
- **CodeWeber Nav**: модуль `codeweber-nav.php` для расширенного управления навигацией
- **Vacancies CPT**: улучшения — демо-данные EN/RU, шаблон `singles/vacancies/default.php`
- **Кнопки соцсетей**: явные стили для social media кнопок в `_buttons.scss`
- **HTML Demo-шаблоны**: 404, about, vertical-menu и другие страницы в `dist/docs/`

### Changed

- **Yandex Maps**: рефакторинг — динамическая загрузка API, удаление legacy-проверок
- **Page.php**: упрощение рендеринга контента (the_content + wp_link_pages)
- **Nav Walker**: улучшения Bootstrap navwalker — поддержка dropdown
- **Dropdown стили**: расширенные стили для dropdown-меню
- **Breadcrumbs**: улучшенная логика хлебных крошек
- **Email Templates**: рефакторинг шаблонов email в CodeWeber Forms
- **Переводы**: обновление POT/PO/MO файлов, добавление русских переводов
- **Card стили**: обновление `_card.scss` для улучшенного отображения
- **Forms стили**: упрощение `_forms.scss`

### Fixed

- **Layout**: исправление класса контента и стилей share-кнопок для адаптивности
- **CF7**: исправление классов Contact Form 7
- **Redux**: исправление настроек Gulp в панели Redux

### Security

- **Санитизация**: исправления безопасности в PHP-файлах (по результатам аудита)

---

## [1.0.4] - 2025-04-26

### Changed

- Тестовые обновления версии и настройки GitHub

---

## [1.0.3] - 2025-04-26

### Changed

- Обновления версии

---

## [1.0.2] - 2025-04-26

### Changed

- Тестовая сборка

---

## [1.0.1] - 2025-04-26

### Fixed

- **Header**: исправление бага в шапке
- **Address**: исправление отображения адреса

### Added

- **TGM Plugin Activation**: интеграция обязательных плагинов

---

## [1.0.0] - 2025-04-25

### Added

- Первый релиз темы CodeWeber
- Header + PageHeader + Topbar компоненты
- Базовая структура темы на Bootstrap 5

---

## Формат

Этот changelog следует принципам [Keep a Changelog](https://keepachangelog.com/ru/1.0.0/),
и проект придерживается [Semantic Versioning](https://semver.org/lang/ru/).
