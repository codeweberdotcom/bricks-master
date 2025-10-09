# Обновление темы для поддержки Rutube и VK Video

## Только для темы Sandbox (HTML)

## Что нужно изменить в вашей теме

Если вы используете Plyr в своей теме, добавьте поддержку новых провайдеров в функцию `theme.plyr()`:

```javascript
/**
 * Plyr
 * Enables media player with support for YouTube, Vimeo, Rutube, and VK Video
 * Requires assets/js/vendor/plyr.js
 */
plyr: () => {
  var players = Plyr.setup(".player", {
    loadSprite: true,
    // Enable support for new providers
    rutube: {
      customControls: true,
      autoplay: false,
      autoUnmute: false,
      autoUnmuteDelay: 1000,
    },
    vkvideo: {
      customControls: true,
      autoplay: false,
      autoUnmute: false,
      autoUnmuteDelay: 1000,
    },
  });

  // Log initialized players for debugging
  console.log('Plyr initialized with support for:', {
    youtube: 'enabled (no changes needed)',
    vimeo: 'enabled (no changes needed)',
    rutube: 'enabled',
    vkvideo: 'enabled',
    totalPlayers: players.length
  });
},
```

### ⚠️ Важно:
- **YouTube и Vimeo** продолжают работать как раньше - **никаких изменений не требуется**
- **Rutube и VK Video** требуют добавления конфигурации в `Plyr.setup()`
- **Не нужно добавлять отдельные скрипты** для каждого плеера

```javascript
/**
 * Plyr
 * Enables media player with support for YouTube, Vimeo, Rutube, and VK Video
 * Requires assets/js/vendor/plyr.js
 */
plyr: () => {
  var players = Plyr.setup(".player", {
    loadSprite: true,
    // Enable support for new providers
    rutube: {
      customControls: true,
      autoplay: false,
      autoUnmute: false,
      autoUnmuteDelay: 1000,
    },
    vkvideo: {
      customControls: true,
      autoplay: false,
      autoUnmute: false,
      autoUnmuteDelay: 1000,
    },
  });

  // Log initialized players for debugging
  console.log('Plyr initialized with support for:', {
    youtube: 'enabled (no changes needed)',
    vimeo: 'enabled (no changes needed)',
    rutube: 'enabled',
    vkvideo: 'enabled',
    totalPlayers: players.length
  });
},
```

## Использование в HTML

### Rutube
```html
<div id="player" data-plyr-provider="rutube" data-plyr-embed-id="VIDEO_ID"></div>
<script>
  const player = new Plyr('#player');
</script>
```

### VK Video
```html
<div id="player" data-plyr-provider="vkvideo" data-plyr-embed-id="oid=-VIDEO_OID&id=VIDEO_ID"></div>
<script>
  const player = new Plyr('#player');
</script>
```

## Важные замечания

### VK Video ограничения:
- **Нет JavaScript API** для управления воспроизведением
- **Элементы управления Plyr не работают** (кроме fullscreen)
- **Используйте нативные элементы управления** VK Video в iframe
- **ID владельца (oid) должен начинаться с минуса**: `oid=-226274878&id=456239033`

### Автоплей:
- **Требует пользовательского взаимодействия** для включения звука
- **Используйте `muted: true`** при инициализации
- **Добавьте `autoUnmute: true`** для автоматического включения звука

## Пример полной конфигурации

```javascript
const player = new Plyr('#player', {
  controls: ['play', 'progress', 'current-time', 'duration', 'mute', 'volume', 'fullscreen'],
  autoplay: true,
  muted: true, // Required for autoplay
  rutube: {
    customControls: true,
    autoplay: true,
    autoUnmute: true,
    autoUnmuteDelay: 3000, // Unmute after 3 seconds
    skinColor: '7cb342'
  }
});
```

## Файлы для обновления

1. **src/js/config/types.js** - добавлены провайдеры rutube и vkvideo
2. **src/js/config/defaults.js** - добавлена конфигурация для новых провайдеров
3. **src/js/plugins/rutube.js** - плагин для Rutube с полным API
4. **src/js/plugins/vkvideo.js** - плагин для VK Video с iframe встраиванием
5. **src/js/media.js** - регистрация новых плагинов
6. **src/js/plyr.js** - геттеры для новых провайдеров

## Демо файлы

- `rutube-demo.html` - демо для Rutube
- `vkvideo-demo.html` - демо для VK Video
- `test-rutube.html` - улучшенный тест-интерфейс

## Сборка

Проект уже пересобран и готов к использованию. Используйте файлы из папки `dist/` для продакшена.
