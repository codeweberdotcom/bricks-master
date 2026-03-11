---
name: security-check
description: Проверить PHP-файл темы CodeWeber на уязвимости безопасности
argument-hint: <путь к файлу>
---

Проверь файл `$ARGUMENTS` на уязвимости безопасности.

Сначала прочитай: `doc_claude/security/SECURITY_CHECKLIST.md`

Затем проверь файл по следующим пунктам:

**AJAX-обработчики:**
- [ ] `check_ajax_referer()` или `verify_nonce()` в начале функции
- [ ] `current_user_can()` для проверки прав
- [ ] Оба хука: `wp_ajax_` и `wp_ajax_nopriv_` (или только один — намеренно)

**Входные данные:**
- [ ] `sanitize_text_field()` / `absint()` / `sanitize_email()` для всех `$_POST`, `$_GET`, `$_REQUEST`
- [ ] `wp_unslash()` перед sanitize
- [ ] Нет прямого использования `$_POST['key']` без обработки

**Вывод данных:**
- [ ] `esc_html()` / `esc_attr()` / `esc_url()` при выводе в HTML
- [ ] `wp_kses()` для HTML-контента
- [ ] Нет `echo $_GET[...]` или подобного

**SQL:**
- [ ] `$wpdb->prepare()` для всех запросов с переменными
- [ ] Нет конкатенации переменных в SQL-строках

**Файлы:**
- [ ] Нет `file_put_contents()` / `file_get_contents()` с пользовательскими путями
- [ ] Нет `include`/`require` с переменными из запроса

**REST API:**
- [ ] `permission_callback` не равен `__return_true` для мутирующих endpoints
- [ ] Входные параметры sanitize через `sanitize_callback` в `register_rest_route()`

Выведи результат в виде таблицы: пункт / статус (OK / ПРОБЛЕМА / Н/Д) / комментарий.
Если найдены проблемы — покажи конкретные строки и предложи исправление.
