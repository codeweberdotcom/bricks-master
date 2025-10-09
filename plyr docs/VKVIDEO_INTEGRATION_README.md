# Интеграция VK Video с Plyr

Эта интеграция добавляет поддержку встраивания видео с платформы VK Video в медиаплеер Plyr.

## ⚠️ Важные ограничения

**VK Video не имеет JavaScript API для управления воспроизведением.** Это означает:

- ❌ Нет программного управления воспроизведением (play/pause/stop)
- ❌ Нет контроля громкости через JavaScript
- ❌ Нет отслеживания прогресса видео
- ❌ Нет информации о длительности видео
- ✅ Только встраивание iframe с нативными элементами управления

## Использование

### Единственный рабочий способ: iframe

```html
<iframe src="https://vkvideo.ru/video_ext.php?oid=-226274878&id=456239033&hd=2&autoplay=1"
        width="853" height="480" frameborder="0" allowfullscreen
        allow="autoplay; encrypted-media; fullscreen; picture-in-picture; screen-wake-lock;"
        style="background-color: #000">
</iframe>

<script>
  const player = new Plyr('iframe', {
    controls: ['fullscreen'] // Только fullscreen работает
  });
</script>
```

### Альтернативный способ: через Plyr конструктор

```javascript
const iframe = document.createElement('iframe');
iframe.src = 'https://vkvideo.ru/video_ext.php?oid=-226274878&id=456239033&hd=2&autoplay=1';
iframe.width = '853';
iframe.height = '480';
iframe.frameBorder = '0';
iframe.allow = 'autoplay; encrypted-media; fullscreen; picture-in-picture; screen-wake-lock;';
iframe.allowFullscreen = true;
iframe.style.backgroundColor = '#000';

document.body.appendChild(iframe);

const player = new Plyr(iframe, {
  controls: ['fullscreen']
});
```

## Форматы URL

### Поддерживаемые форматы:

1. **Стандартный URL**: `https://vkvideo.ru/video-226274878_456239033`
2. **Iframe URL**: `https://vkvideo.ru/video_ext.php?oid=-226274878&id=456239033`

### Парсинг ID видео:

- Из URL `video-226274878_456239033` извлекается:
  - `oid`: `226274878`
  - `id`: `456239033`

## Параметры конфигурации

### vkvideo.autoplay
Включить автоплей для видео.

```javascript
vkvideo: {
  autoplay: true // Автоплей включен
}
```

## Дополнительные параметры iframe

При создании iframe можно добавить следующие параметры:

- `hd=2` - качество видео (HD)
- `autoplay=1` - автоплей
- `oid` - ID владельца видео
- `id` - ID видео

Пример:
```html
<iframe src="https://vkvideo.ru/video_ext.php?oid=-226274878&id=456239033&hd=2&autoplay=1"></iframe>
```

## События

Интеграция поддерживает все стандартные события Plyr:

```javascript
player.on('ready', () => {
  console.log('VK Video плеер готов');
});

player.on('play', () => {
  console.log('Воспроизведение начато');
});

player.on('pause', () => {
  console.log('Воспроизведение приостановлено');
});
```

## Методы управления

```javascript
// Воспроизведение
player.play();

// Пауза
player.pause();

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
<div style="height:60vw; max-width: 853px; max-height: 480px; min-height: 240px;">
  <iframe width="100%" height="100%"
    src="https://vkvideo.ru/video_ext.php?oid=-226274878&id=456239033&hd=2"
    frameborder="0" allow="autoplay; encrypted-media; fullscreen" allowfullscreen>
  </iframe>
</div>
```

## Безопасность

Интеграция проверяет origin сообщений для безопасности. Только сообщения от доменов `vkvideo.ru` и `vk.com` обрабатываются.

## Совместимость

- Современные браузеры с поддержкой ES6+
- Работает с существующими элементами управления Plyr
- Поддержка мобильных устройств

## Пример демо

Смотрите файл `vkvideo-demo.html` для полного примера использования.

## Отличия от Rutube

- **URL формат**: VK Video использует `video-oid_id` формат
- **Параметры iframe**: VK Video использует `oid` и `id` параметры
- **API**: VK Video имеет ограниченный postMessage API
- **Автоплей**: Требует пользовательского взаимодействия для активации звука

## Устранение проблем

### Проблема: Видео не загружается
- Проверьте корректность ID видео в URL
- Убедитесь что видео доступно публично

### Проблема: Нет элементов управления
- Убедитесь что установлен `customControls: true`
- Проверьте что Plyr CSS файл загружен

### Проблема: Автоплей не работает
- Установите `muted: true` при инициализации
- Добавьте `autoplay: true` в конфигурацию
- Обеспечьте пользовательское взаимодействие

## Пример полной конфигурации

```javascript
const player = new Plyr('#vkvideo-player', {
  controls: ['play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'fullscreen'],
  autoplay: true,
  muted: true,
  vkvideo: {
    customControls: true,
    autoplay: true
  }
});
