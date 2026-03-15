---
name: init
description: Инициализировать child тему CodeWeber — скопировать скиллы из parent и настроить git
---

Инициализируй эту дочернюю тему CodeWeber.

**Запускается из директории child темы** (текущая рабочая директория = корень child темы).

`_user-variables.scss` и `CLAUDE.md` уже созданы при генерации темы через Redux.
Задача `/init` — скопировать скиллы из parent и настроить git.

---

## Шаги

### 1. Определить parent тему

Прочитай `./style.css`. Извлеки строку `Template:` — это `PARENT_SLUG` (например, `codeweber`).
Папка parent: `../PARENT_SLUG/`

Если parent не найден (`../PARENT_SLUG/` не существует) — предупреди, но продолжай.

---

### 2. Скопировать скиллы из parent

Прочитай и запиши следующие файлы (создай директории если нужно):

| Источник | Цель |
|----------|------|
| `../PARENT_SLUG/.claude/skills/design-extract/SKILL.md` | `.claude/skills/design-extract/SKILL.md` |
| `../PARENT_SLUG/.claude/skills/build/SKILL.md` | `.claude/skills/build/SKILL.md` |
| `../PARENT_SLUG/.claude/skills/commit/SKILL.md` | `.claude/skills/commit/SKILL.md` |
| `../PARENT_SLUG/.claude/skills/done/SKILL.md` | `.claude/skills/done/SKILL.md` |
| `../PARENT_SLUG/.claude/skills/update-docs/SKILL.md` | `.claude/skills/update-docs/SKILL.md` |
| `../PARENT_SLUG/.claude/skills/changelog/SKILL.md` | `.claude/skills/changelog/SKILL.md` |

Скилл `init` уже есть — не трогай.

---

### 3. Git

Проверь есть ли `.git` в текущей директории:

- **Нет** → `git init`, добавь все файлы кроме `node_modules/`, `dist/`, `*.log`
  Коммит: `init: child theme setup`
- **Есть** → добавь только что скопированные скиллы, коммит:
  `init: copy skills from parent`

---

### 4. Отчёт

- Список скопированных скиллов
- Статус git
- Следующий шаг: активировать тему в WordPress → запустить `/build`
