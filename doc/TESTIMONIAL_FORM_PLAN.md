# План создания формы отправки отзыва

## Обзор
Создание AJAX формы для отправки отзывов на основе архитектуры плагина codeweber-gutenberg-blocks.

## Архитектура (по образцу плагина)

### 1. REST API Endpoint (PHP)
**Файл:** `wp-content/themes/codeweber/functions/testimonials/testimonial-form-api.php`

- Класс `TestimonialFormAPI` по аналогии с `LoadMoreAPI`
- Метод `register_routes()` - регистрация REST маршрута
- Маршрут: `/wp-json/codeweber/v1/submit-testimonial`
- Метод: POST
- Permission callback: `__return_true` (публичный доступ)
- Callback: `submit_testimonial()` - обработка данных формы

### 2. JavaScript обработчик (Frontend)
**Файл:** `wp-content/themes/codeweber/src/assets/js/testimonial-form.js`

- Обработчик отправки формы
- Использование Fetch API (как в load-more.js)
- Валидация полей на клиенте
- Обработка ответа (успех/ошибка)
- Показ сообщений пользователю
- Сброс формы после успешной отправки

### 3. HTML форма
**Файл:** Модальное окно или встроенная форма в `archive-testimonials.php`

- Поля формы:
  - Текст отзыва (textarea, обязательное)
  - Имя автора (input, обязательное)
  - Email автора (input, обязательное, валидация email)
  - Роль/Должность (input, опциональное)
  - Компания (input, опциональное)
  - Рейтинг (select 1-5, обязательное)
  - Аватар (file upload, опциональное)
- Nonce поле для безопасности
- Кнопка отправки с индикатором загрузки

### 4. Enqueue скриптов
**Файл:** `wp-content/themes/codeweber/functions/enqueues.php`

- Подключение JavaScript файла
- Локализация скрипта (ajaxurl, nonce, переводы)

## Структура файлов

```
wp-content/themes/codeweber/
├── functions/
│   └── testimonials/
│       ├── testimonial-form-api.php    # REST API endpoint
│       └── testimonial-form-handler.php # Обработка данных
├── src/
│   └── assets/
│       └── js/
│           └── testimonial-form.js     # JavaScript обработчик
└── archive-testimonials.php            # HTML форма (модальное окно)
```

## Детальный план реализации

### Этап 1: REST API Endpoint

**Файл:** `testimonial-form-api.php`

```php
<?php
namespace Codeweber\Testimonials;

class TestimonialFormAPI {
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route('codeweber/v1', '/submit-testimonial', [
            'methods' => 'POST',
            'callback' => [$this, 'submit_testimonial'],
            'permission_callback' => '__return_true',
            'args' => [
                'testimonial_text' => ['required' => true, 'sanitize_callback' => 'wp_kses_post'],
                'author_name' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
                'author_email' => ['required' => true, 'sanitize_callback' => 'sanitize_email'],
                'author_role' => ['required' => false, 'sanitize_callback' => 'sanitize_text_field'],
                'company' => ['required' => false, 'sanitize_callback' => 'sanitize_text_field'],
                'rating' => ['required' => true, 'sanitize_callback' => 'absint'],
                'nonce' => ['required' => true, 'sanitize_callback' => 'sanitize_text_field'],
            ]
        ]);
    }

    public function submit_testimonial($request) {
        // 1. Проверка nonce
        // 2. Валидация данных
        // 3. Проверка на спам (reCAPTCHA или honeypot)
        // 4. Создание поста testimonials со статусом 'pending'
        // 5. Сохранение мета-полей
        // 6. Обработка загрузки аватара (если есть)
        // 7. Отправка уведомления администратору (опционально)
        // 8. Возврат ответа
    }
}
```

### Этап 2: JavaScript обработчик

**Файл:** `testimonial-form.js`

```javascript
(function() {
    'use strict';

    function initTestimonialForm() {
        const form = document.getElementById('testimonial-form');
        if (!form) return;

        const submitBtn = form.querySelector('[type="submit"]');
        const originalBtnText = submitBtn.textContent;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Валидация
            if (!validateForm(form)) {
                return;
            }

            // Показ загрузки
            submitBtn.disabled = true;
            submitBtn.textContent = 'Отправка...';

            // Сбор данных формы
            const formData = new FormData(form);
            const data = {
                testimonial_text: formData.get('testimonial_text'),
                author_name: formData.get('author_name'),
                author_email: formData.get('author_email'),
                author_role: formData.get('author_role'),
                company: formData.get('company'),
                rating: formData.get('rating'),
                nonce: formData.get('nonce')
            };

            // AJAX запрос
            fetch(codeweberTestimonialForm.restUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': codeweberTestimonialForm.nonce
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage(data.message);
                    form.reset();
                } else {
                    showErrorMessage(data.message);
                }
            })
            .catch(error => {
                showErrorMessage('Ошибка отправки. Попробуйте позже.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            });
        });
    }

    function validateForm(form) {
        // Валидация полей
        // Возврат true/false
    }

    function showSuccessMessage(message) {
        // Показ сообщения об успехе
    }

    function showErrorMessage(message) {
        // Показ сообщения об ошибке
    }

    // Инициализация
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTestimonialForm);
    } else {
        initTestimonialForm();
    }
})();
```

### Этап 3: HTML форма

**Модальное окно или встроенная форма:**

```html
<form id="testimonial-form" class="testimonial-form">
    <?php wp_nonce_field('submit_testimonial', 'testimonial_nonce'); ?>
    
    <div class="form-group">
        <label for="testimonial_text">Текст отзыва *</label>
        <textarea name="testimonial_text" id="testimonial_text" required></textarea>
    </div>

    <div class="form-group">
        <label for="author_name">Ваше имя *</label>
        <input type="text" name="author_name" id="author_name" required>
    </div>

    <div class="form-group">
        <label for="author_email">Email *</label>
        <input type="email" name="author_email" id="author_email" required>
    </div>

    <div class="form-group">
        <label for="author_role">Должность</label>
        <input type="text" name="author_role" id="author_role">
    </div>

    <div class="form-group">
        <label for="company">Компания</label>
        <input type="text" name="company" id="company">
    </div>

    <div class="form-group">
        <label for="rating">Рейтинг *</label>
        <select name="rating" id="rating" required>
            <option value="">Выберите рейтинг</option>
            <option value="1">1 звезда</option>
            <option value="2">2 звезды</option>
            <option value="3">3 звезды</option>
            <option value="4">4 звезды</option>
            <option value="5">5 звезд</option>
        </select>
    </div>

    <div class="form-group">
        <label for="avatar">Аватар (опционально)</label>
        <input type="file" name="avatar" id="avatar" accept="image/*">
    </div>

    <button type="submit" class="btn btn-primary">Отправить отзыв</button>
</form>
```

## Безопасность

1. **Nonce проверка** - защита от CSRF
2. **Валидация данных** - на сервере и клиенте
3. **Sanitization** - очистка всех входных данных
4. **Rate limiting** - ограничение количества отправок с одного IP
5. **Honeypot поле** - защита от ботов
6. **reCAPTCHA** (опционально) - дополнительная защита

## Обработка файлов (аватар)

- Использование `media_handle_upload()` для загрузки в медиабиблиотеку
- Валидация типа файла (только изображения)
- Ограничение размера файла
- Генерация миниатюр

## Уведомления

- Email администратору о новом отзыве (опционально)
- Email пользователю с подтверждением (опционально)

## Переводы

- Все строки должны быть обернуты в функции перевода
- Добавление переводов в `.po` файл

## Интеграция с кнопкой

- Кнопка `#submit-testimonial-btn` открывает модальное окно с формой
- Или показывает встроенную форму на странице

## Последовательность реализации

1. ✅ Создать REST API endpoint
2. ✅ Создать JavaScript обработчик
3. ✅ Создать HTML форму (модальное окно)
4. ✅ Подключить скрипты в functions.php
5. ✅ Добавить стили для формы
6. ✅ Добавить переводы
7. ✅ Тестирование

