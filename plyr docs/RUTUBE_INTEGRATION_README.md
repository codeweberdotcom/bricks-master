# Интеграция Rutube с Plyr

Эта интеграция добавляет поддержку видео с платформы Rutube в медиаплеер Plyr.

## Возможности

- Полная интеграция с API Rutube
- Поддержка всех стандартных элементов управления Plyr
- Обработка событий плеера через postMessage
- Поддержка дополнительных параметров Rutube (цветовая схема, время начала воспроизведения и т.д.)
- Автоматическое определение провайдера по URL

## Использование

### Способ 1: Использование div с data-атрибутами

```html
<div id="player" data-plyr-provider="rutube" data-plyr-embed-id="VIDEO_ID"></div>

<script>
  const player = new Plyr('#player');
</script>
```

### Способ 2: Использование iframe напрямую

```html
<iframe src="https://rutube.ru/play/embed/VIDEO_ID" width="640" height="360" frameborder="0" allow="clipboard-write; autoplay" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>

<script>
  const player = new Plyr('iframe');
</script>
```

### Способ 3: Программное создание

```javascript
const player = new Plyr('#player', {
  controls: ['play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'fullscreen'],
  rutube: {
    customControls: true,
    skinColor: '7cb342', // Опционально: цвет элементов управления
    getPlayOptions: 'pg_rating,is_adult,duration,title' // Опционально: получить информацию о видео
  }
});
```

## Параметры конфигурации

### rutube.skinColor
Цвет элементов управления плеера в 16-ричном формате (без #).

```javascript
rutube: {
  skinColor: '7cb342' // Зеленый цвет
}
```

### rutube.getPlayOptions
Параметры для получения дополнительной информации о видео.

```javascript
rutube: {
  getPlayOptions: 'pg_rating,is_adult,duration,title'
}
```

Доступные параметры:
- `pg_rating` - возрастной рейтинг
- `is_adult` - флаг adult-контента
- `duration` - длительность видео
- `title` - название видео
- И другие параметры из API Rutube

### rutube.autoplay
Включить автоплей для видео.

```javascript
rutube: {
  autoplay: true // Автоплей включен
}
```

### rutube.autoUnmute
Автоматически включить звук после загрузки страницы (необходимо для автоплея в большинстве браузеров).

```javascript
rutube: {
  autoUnmute: true, // Включить автовключение звука
  autoUnmuteDelay: 2000 // Задержка перед включением звука (мс)
}
```

### Пример полной конфигурации с автоплеем:

```javascript
const player = new Plyr('#player', {
  controls: ['play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'fullscreen'],
  autoplay: true,
  muted: true, // Начинать без звука для автоплея
  rutube: {
    customControls: true,
    autoplay: true,
    autoUnmute: true,
    autoUnmuteDelay: 2000, // Включить звук через 2 секунды
    skinColor: 'ff6b35',
    getPlayOptions: 'pg_rating,is_adult,duration,title'
  }
});
```

## Дополнительные параметры URL

При создании iframe можно добавить следующие параметры:

- `t=30` - начать воспроизведение с 30 секунды
- `skinColor=7cb342` - цвет элементов управления
- `getPlayOptions=pg_rating,is_adult,duration,title` - получить информацию о видео

Пример:
```html
<iframe src="https://rutube.ru/play/embed/VIDEO_ID?t=30&skinColor=7cb342&getPlayOptions=pg_rating,is_adult,duration,title"></iframe>
```

## События

Интеграция поддерживает все стандартные события Plyr плюс события Rutube:

```javascript
player.on('ready', () => {
  console.log('Плеер готов');
});

player.on('play', () => {
  console.log('Воспроизведение начато');
});

player.on('pause', () => {
  console.log('Воспроизведение приостановлено');
});

player.on('ended', () => {
  console.log('Воспроизведение завершено');
});

// События от Rutube
player.on('statechange', (event) => {
  console.log('Состояние плеера изменено:', event.detail.code);
});
```

## Методы управления

```javascript
// Воспроизведение
player.play();

// Пауза
player.pause();

// Остановка
player.stop();

// Перемотка к времени (в секундах)
player.currentTime = 30;

// Установка громкости (0-1)
player.volume = 0.8;

// Отключение/включение звука
player.muted = true;
```

## Автоподстройка размера

Для адаптивного размера используйте следующую структуру:

```html
<div style="height:60vw; max-width: 1066px; max-height: 600px; min-height: 240px;">
  <iframe width="100%" height="100%" src="https://rutube.ru/play/embed/VIDEO_ID" frameborder="0" allow="clipboard-write" allowfullscreen>
  </iframe>
</div>
```

## Отслеживание прогресса видео

Интеграция автоматически отслеживает прогресс воспроизведения видео:

### Автоматическое обновление времени
- **Периодический запрос** текущего времени каждую секунду во время воспроизведения
- **Обновление ползунка прогресса** в реальном времени
- **Отображение прошедшего времени** в элементах управления

### Получение длительности видео
- **Автоматический запрос** длительности при готовности плеера
- **Корректное отображение** общего времени видео
- **Расчет прогресса** в процентах

### События прогресса
```javascript
player.on('timeupdate', () => {
  console.log('Current time:', player.currentTime);
});

player.on('durationchange', () => {
  console.log('Duration:', player.duration);
});
```

## Автоплей и автовключение звука

### Особенности автоплея в браузерах

Большинство современных браузеров блокируют автоплей видео со звуком по умолчанию. Для успешного автоплея рекомендуется:

1. **Начинать с выключенного звука** (`muted: true`)
2. **Использовать автовключение звука** с задержкой (`autoUnmute: true`)
3. **Указать автоплей в параметрах** iframe
4. **Обеспечить пользовательское взаимодействие** для активации звука

### Важное замечание о браузерной безопасности

Начиная с 2020 года, браузеры требуют **пользовательского взаимодействия** для воспроизведения видео со звуком. Интеграция автоматически создает невидимую кнопку для обработки пользовательских кликов.

### Пример с автоплеем:

```javascript
const player = new Plyr('#player', {
  autoplay: true,
  muted: true, // Обязательно для автоплея в большинстве браузеров
  rutube: {
    autoplay: true,
    autoUnmute: true,
    autoUnmuteDelay: 3000 // Включить звук через 3 секунды после взаимодействия
  }
});
```

### Рекомендации для продакшена:

1. **Сообщите пользователям** о необходимости кликнуть для активации звука
2. **Используйте задержку** 2-4 секунды для автовключения
3. **Предоставьте альтернативу** - кнопку ручного включения звука

### Прямой iframe с автоплеем:

```html
<iframe src="https://rutube.ru/play/embed/VIDEO_ID?autoplay=1&t=10&skinColor=4a90e2"
        allow="clipboard-write; autoplay"
        width="640" height="360">
</iframe>
```

## 🔧 Поиск и устранение проблем

### Проблема: Звук не включается автоматически

**Решение:**
1. **Кликните на странице** - браузеры требуют пользовательского взаимодействия для активации звука
2. **Увеличьте задержку** `autoUnmuteDelay` до 4-5 секунд:
   ```javascript
   rutube: {
     autoUnmuteDelay: 5000 // 5 секунд
   }
   ```
3. **Проверьте консоль браузера** - там будут подробные логи процесса
4. **Используйте ручное управление** как запасной вариант

### Проблема: Видео не отображается

**Решение:**
1. Убедитесь что ID видео корректный
2. Проверьте что элемент существует в DOM перед инициализацией Plyr
3. Используйте локальные файлы сборки (`dist/plyr.min.js`), а не CDN

### Проблема: Автоплей не работает

**Решение:**
1. Установите `muted: true` при инициализации
2. Добавьте `autoplay: true` в конфигурацию
3. Убедитесь что пользователь взаимодействовал со страницей

### Проблема: Ползунок прогресса не двигается

**Решение:**
1. Интеграция автоматически запрашивает время воспроизведения каждую секунду
2. Убедитесь что видео загружается и начинает воспроизведение
3. Проверьте консоль на сообщения `⏱️ Current time updated:`
4. Если ползунок не двигается - попробуйте перезагрузить страницу

### Проблема: Не отображается длительность видео

**Решение:**
1. Интеграция автоматически запрашивает длительность при готовности плеера
2. Проверьте консоль на сообщения `📏 Video duration:`
3. Если длительность не отображается - проверьте что видео доступно

### Проблема: Ошибки в консоли

**Решение:**
1. Проверьте что все файлы (`plyr.min.js`, `plyr.css`) загружаются корректно
2. Убедитесь что элемент плеера существует перед инициализацией
3. Проверьте консоль на ошибки JavaScript

### Ручное включение звука:

```javascript
// Добавить кнопку для ручного включения
player.on('ready', () => {
  // Создать кнопку "Включить звук"
  const unmuteBtn = document.createElement('button');
  unmuteBtn.textContent = '🔊 Включить звук';
  unmuteBtn.onclick = () => {
    player.unMute();
    player.setVolume(1);
  };
  // Добавить кнопку в интерфейс
});
```

## Безопасность

Интеграция проверяет origin сообщений для безопасности. Только сообщения от доменов `rutube.ru` обрабатываются.

## Совместимость

- Современные браузеры с поддержкой postMessage API
- ES6+ для использования классов и модулей
- Работает с существующими элементами управления Plyr

## Быстрый старт

### Минимальный пример:

```html
<div id="player" data-plyr-provider="rutube" data-plyr-embed-id="VIDEO_ID"></div>

<script src="dist/plyr.min.js"></script>
<script>
  const player = new Plyr('#player');
</script>
```

### Пример с автоплеем:

```html
<div id="player" data-plyr-provider="rutube" data-plyr-embed-id="VIDEO_ID"></div>

<script>
  const player = new Plyr('#player', {
    autoplay: true,
    muted: true,
    rutube: {
      autoplay: true,
      autoUnmute: true,
      autoUnmuteDelay: 3000
    }
  });

  // Обязательно: пользователь должен кликнуть для активации звука
  console.log('👆 Пожалуйста, кликните на странице для включения звука');
</script>
```

## Пример демо

Смотрите файлы:
- `rutube-demo.html` - три примера использования
- `test-rutube.html` - улучшенный тест-интерфейс с мониторингом
