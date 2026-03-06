## План перевода документации `dist/docs/index.html` на русский язык

### 1. Область перевода

- Перевести все человекочитаемые тексты в файле `dist/docs/index.html`:
  - Заголовки: `Get Started`, `Overview`, `File Structure`, `Installation`, `Gulp Commands`, `Quick Installation Video` и промо-блок внизу страницы.
  - Все параграфы и элементы списков в основном контенте.
  - Подписи и названия пунктов меню в шапке, боковых колонках (`Usage`, `Styleguide`, `Elements`, `On this page`) и нижнем CTA-блоке.
  - Тексты кнопок: `Contact`, `Contact Form`, `Discussions Page`, `Order a Website` и т.п.
- Не переводить:
  - URL и домены, CSS/JS классы, атрибуты, имена файлов и папок.
  - Команды терминала и названия технологий: `npm install`, `gulp serve`, `gulp build:dist`, `Node.js`, `Gulp`, `SASS`, `Bootstrap 5` и т.д.

### 2. Стиль и терминология

- Использовать официальный деловой стиль, форма обращения — «вы».
- Сохранять точный технический смысл, при этом делать текст естественным для русскоязычной аудитории.
- Базовые соответствия:
  - `Get Started` → «Начало работы».
  - `File Structure` → «Структура файлов».
  - `Installation` → «Установка».
  - `Gulp Commands` → «Команды Gulp».
  - `Quick Installation Video` → «Быстрое видео по установке».
  - `Usage` → «Использование», `Styleguide` → «Руководство по стилю», `Elements` → «Элементы».

### 3. Перевод основного контента

- Раздел `Overview`:
  - Перевести описания назначения шаблона и структуры документации.
  - Перевести тексты в карточке с напоминанием об условиях поддержки.
- Раздел `File Structure`:
  - Перевести описания папок `dev`, `dist`, `src`, подпапок `src/assets/js`, `src/assets/css`, `src/assets/scss`.
  - Оставить имена папок и файлов в `<code>` без изменений, переводить только сопроводительный текст.
- Раздел `Installation`:
  - Перевести предупреждающий алерт и список шагов установки.
  - Подчеркнуть, что шаги по Node.js/Gulp/SASS являются опциональными.
- Раздел `Gulp Commands`:
  - Перевести описания команд `gulp`, `gulp serve`, `gulp build:dist`, `gulp build:dev`, оставить сами команды как есть.
  - Перевести пояснение про остановку сервера (`Control + C`).
- Раздел `Quick Installation Video`:
  - Перевести описание того, что демонстрирует видео, списком.

### 4. Перевод навигации и боковых блоков

- Верхнее меню:
  - Перевести основные разделы: `Pages`, `Projects`, `Blog`, `Blocks`, `Documentation`, `Contact`.
  - Перевести подпункты: `Services`, `About`, `Shop`, `Career`, `Utility`, `Pricing`, `One Page`, а также варианты (`Services I/II`, `About I/II`, и т.д.) с сохранением нумерации.
- Левый сайдбар:
  - `Usage` → «Использование», элементы списка (`Get Started`, `Forms`, `FAQ`, `Changelog`, `Credits`) перевести.
  - `Styleguide` → «Руководство по стилю», пункты (`Colors`, `Fonts`, `SVG Icons`, `Font Icons`, `Illustrations`, `Backgrounds`, `Misc`) перевести.
  - `Elements` → «Элементы», перевести названия всех элементов (`Accordion`, `Alerts`, `Animations`, `Avatars`, и т.д.).
- Правый сайдбар:
  - `On this page` → «На этой странице».
  - Перевести названия пунктов (`Overview`, `File Structure`, `Installation`, `Gulp Commands`, `Quick Video`).
- Нижний промо-блок:
  - Перевести слоган и лид-абзац с сохранением маркетингового смысла.
  - Кнопку `Order a Website` перевести как «Заказать сайт» или аналогичный лаконичный вариант.

### 5. Мета-информация и язык документа

- Перевести `meta description` на русский, сохранив описание преимуществ шаблона.
- При необходимости скорректировать `meta keywords`, добавив русские ключи, сохраняя английские.
- В корневом теге `<html>` заменить `lang="en"` на `lang="ru"` для корректной идентификации языка страницы.

### 6. Перевод всех страниц документации из сайдбара

- Для всех ссылок из левого сайдбара документации выполнить аналогичный перевод содержимого:
  - Раздел **«Использование»**:
    - `src/docs/index.html` ↔ `dist/docs/index.html` (уже учтено текущим планом).
    - `src/docs/forms.html` ↔ `dist/docs/forms.html`.
    - `src/docs/faq.html` ↔ `dist/docs/faq.html`.
    - `src/docs/changelog.html` ↔ `dist/docs/changelog.html`.
    - `src/docs/credits.html` ↔ `dist/docs/credits.html`.
  - Раздел **«Руководство по стилю»**:
    - `src/docs/styleguide/colors.html` ↔ `dist/docs/styleguide/colors.html`.
    - `src/docs/styleguide/fonts.html` ↔ `dist/docs/styleguide/fonts.html`.
    - `src/docs/styleguide/icons-svg.html` ↔ `dist/docs/styleguide/icons-svg.html`.
    - `src/docs/styleguide/icons-font.html` ↔ `dist/docs/styleguide/icons-font.html`.
    - `src/docs/styleguide/illustrations.html` ↔ `dist/docs/styleguide/illustrations.html`.
    - `src/docs/styleguide/backgrounds.html` ↔ `dist/docs/styleguide/backgrounds.html`.
    - `src/docs/styleguide/misc.html` ↔ `dist/docs/styleguide/misc.html`.
  - Раздел **«Элементы»**:
    - Все файлы `src/docs/elements/*.html` ↔ соответствующие `dist/docs/elements/*.html` (accordion, alerts, animations, avatars, background, badges, buttons, card, carousel, dividers, form-elements, image-hover, image-mask, lightbox, player, modal, pagination, progressbar, shadows, shapes, tables, tabs, text-animations, text-highlight, tiles, tooltips-popovers, typography).
- Для каждой пары `src`/`dist`:
  - Перевести все тексты заголовков, описаний, списков, вспомогательных пояснений, оставляя команды, имена файлов и классы без изменений.
  - Сохранить структуру HTML и классы, менять только текстовые узлы.

### 7. Синхронизация `src` и `dist`

- Аналогично перевести исходный файл `src/docs/index.html`, чтобы изменения не потерялись при будущей сборке.
- Продублировать переведённый текст в `dist/docs/index.html`, чтобы текущая собранная версия документации сразу отображалась на русском без запуска Gulp.
- Сборку фронтенда не запускать; при необходимости пересборку выполнит руководитель.

### 8. Финальная проверка

- Открыть `dist/docs/index.html` в браузере.
- Проверить:
  - Корректное отображение всех переводов и отсутствие смешения языков в одном элементе.
  - Работу всех якорных ссылок (`#snippet-1` … `#snippet-5`) и навигации.
  - Отсутствие поломок вёрстки после замены текстов.
