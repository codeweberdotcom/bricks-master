# CodeWeber Forms Translations

## Файлы переводов

- `codeweber-forms-ru_RU.po` - Файл переводов на русский язык
- `codeweber-forms-ru_RU.mo` - Скомпилированный файл переводов (создается автоматически)

## Компиляция переводов

### Способ 1: Использование Loco Translate (Рекомендуется)

1. Установите плагин **Loco Translate** (если еще не установлен)
2. Перейдите в **Loco Translate → Themes → Codeweber**
3. Найдите модуль **codeweber-forms**
4. Откройте файл переводов `ru_RU`
5. Loco Translate автоматически скомпилирует `.mo` файл при сохранении

### Способ 2: Использование Poedit

1. Откройте файл `codeweber-forms-ru_RU.po` в программе Poedit
2. Нажмите **Сохранить** - Poedit автоматически создаст `.mo` файл

### Способ 3: Использование Node.js (если установлен gettext-parser)

```bash
cd wp-content/themes/codeweber/functions/integrations/codeweber-forms/languages
npm install gettext-parser
node compile-translations.js
```

### Способ 4: Использование msgfmt (Linux/Mac)

```bash
cd wp-content/themes/codeweber/functions/integrations/codeweber-forms/languages
msgfmt codeweber-forms-ru_RU.po -o codeweber-forms-ru_RU.mo
```

## Добавление новых переводов

1. Отредактируйте файл `codeweber-forms-ru_RU.po`
2. Добавьте новые строки в формате:
   ```
   #: path/to/file.php:123
   msgid "English text"
   msgstr "Русский текст"
   ```
3. Скомпилируйте переводы одним из способов выше

## Использование в коде

Все строки в модуле должны использовать функцию `__()` или `_e()`:

```php
__('Text to translate', 'codeweber-forms');
_e('Text to translate', 'codeweber-forms');
```

## Поддерживаемые языки

- Русский (ru_RU) - включен
- Английский (en_US) - по умолчанию (не требует перевода)


