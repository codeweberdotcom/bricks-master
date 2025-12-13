# Установка Ghostscript для генерации превью PDF

## Зачем нужен Ghostscript?

Ghostscript необходим для автоматической генерации превью (скриншотов) первой страницы PDF файлов при загрузке документов.

## Варианты установки

### Вариант 1: Установка в Laragon (Рекомендуется)

1. **Скачайте Ghostscript:**
   - Перейдите на https://www.ghostscript.com/download/gsdnld.html
   - Скачайте установщик для Windows (например, `gs1000w64.exe`)

2. **Установите Ghostscript:**
   - Запустите установщик
   - Установите в стандартную директорию (например, `C:\Program Files\gs\gs10.00.0\`)
   - **Важно:** При установке выберите опцию "Add Ghostscript to PATH" или добавьте вручную

3. **Проверка установки:**
   - Откройте командную строку (CMD)
   - Выполните: `gswin64c.exe -v`
   - Должна отобразиться версия Ghostscript

### Вариант 2: Портативная версия в теме

1. **Скачайте портативную версию:**
   - Скачайте ZIP архив Ghostscript с официального сайта
   - Или используйте готовую сборку

2. **Распакуйте в тему:**
   ```
   wp-content/themes/codeweber/bin/ghostscript/bin/gswin64c.exe
   ```

3. **Структура должна быть:**
   ```
   wp-content/themes/codeweber/
   └── bin/
       └── ghostscript/
           └── bin/
               ├── gswin64c.exe
               └── gswin32c.exe (опционально)
   ```

### Вариант 3: Через Laragon Menu

1. Откройте Laragon
2. Меню → Tools → Quick add → Ghostscript
3. Если Ghostscript доступен в меню, установите его

## Проверка работы

После установки Ghostscript:

1. Загрузите PDF файл в документ (CPT Documents)
2. Система автоматически создаст превью первой страницы
3. Превью будет установлено как Featured Image документа

## Альтернативы

Если Ghostscript недоступен, система попробует использовать:
1. **Imagick** (PHP расширение) - если установлено
2. **WordPress Image Editor** - если Imagick доступен через WP

## Устранение проблем

### Ghostscript не найден

1. Проверьте, что Ghostscript установлен: `gswin64c.exe -v` в CMD
2. Проверьте PATH: Ghostscript должен быть в системном PATH
3. Для портативной версии: проверьте путь в `wp-content/themes/codeweber/bin/ghostscript/bin/`

### Ошибка "exec() has been disabled"

Если функция `exec()` отключена в PHP:
- В `php.ini` найдите `disable_functions` и убедитесь, что `exec` не в списке
- Или используйте Imagick вместо Ghostscript

### Проверка через WordPress

Создайте временный файл для проверки:

```php
// В functions.php временно добавьте:
add_action('admin_notices', function() {
    if (function_exists('codeweber_detect_ghostscript')) {
        $gs = codeweber_detect_ghostscript();
        echo '<div class="notice notice-info"><p>Ghostscript: ' . ($gs ? $gs : 'Не найден') . '</p></div>';
    }
});
```

## Ссылки

- Официальный сайт: https://www.ghostscript.com/
- Скачать: https://www.ghostscript.com/download/gsdnld.html
- Документация: https://www.ghostscript.com/documentation.html


