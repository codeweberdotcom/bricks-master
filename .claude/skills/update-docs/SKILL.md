---
name: update-docs
description: Обновить документацию doc_claude/ после изменений в коде темы
argument-hint: [путь к изменённому файлу — опционально]
---

Обнови документацию `doc_claude/` в соответствии с изменениями в коде.

Таблица соответствий файлов и документации:

| Изменённый файл | Документ для обновления |
|----------------|------------------------|
| `functions/cpt/cpt-*.php` | `doc_claude/cpt/CPT_CATALOG.md` |
| `functions/fetch/` | `doc_claude/api/AJAX_FETCH_SYSTEM.md` |
| `functions/integrations/cf7.php` | `doc_claude/forms/CF7_INTEGRATION.md` |
| `functions/integrations/codeweber-forms/` | `doc_claude/forms/CODEWEBER_FORMS.md` |
| `functions/integrations/newsletter-*` | `doc_claude/integrations/NEWSLETTER.md` |
| `functions/integrations/dadata/` | `doc_claude/integrations/DADATA.md` |
| `functions/integrations/smsru/` | `doc_claude/integrations/SMSRU.md` |
| `functions/integrations/matomo*` | `doc_claude/integrations/MATOMO.md` |
| `functions/integrations/personal-data-v2/` | `doc_claude/integrations/PERSONAL_DATA_V2.md` |
| `functions/admin/` | `doc_claude/settings/ADMIN_PANELS.md` |
| `functions/enqueues.php` | `doc_claude/development/BUILD_SYSTEM.md` |
| `functions.php` | `doc_claude/architecture/FILE_LOADING_ORDER.md` |
| REST endpoints | `doc_claude/api/REST_API_REFERENCE.md` |
| Новые хуки (apply_filters / do_action) | `doc_claude/api/HOOKS_REFERENCE.md` |
| `redux-framework/` | `doc_claude/settings/REDUX_OPTIONS.md` |
| `templates/` | `doc_claude/templates/` |

Шаги:
1. Если передан аргумент `$ARGUMENTS` — работай с указанным файлом
2. Иначе — запусти `git diff --name-only` и определи изменённые файлы
3. По таблице выше найди соответствующие документы
4. Прочитай изменённый код и соответствующий документ
5. Обнови документ: исправь устаревшее, добавь новое, сохрани формат
