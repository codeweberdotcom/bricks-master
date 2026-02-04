jQuery(document).ready(function ($) {
    console.log('[Load User Variables] 1. Document ready, script loaded');

    // Локализация для AJAX
    var gulpAjax = {
        ajax_url: typeof gulpBuildAjax !== 'undefined' ? gulpBuildAjax.ajax_url : '',
        nonce: typeof gulpBuildAjax !== 'undefined' ? gulpBuildAjax.nonce : ''
    };
    console.log('[Load User Variables] 2. gulpBuildAjax check:', {
        exists: typeof gulpBuildAjax !== 'undefined',
        ajax_url: gulpAjax.ajax_url,
        load_user_vars_nonce: typeof gulpBuildAjax !== 'undefined' ? gulpBuildAjax.load_user_vars_nonce : 'N/A'
    });

    // Общая функция для обработки кнопок
    function handleGulpButton(buttonId, actionName, buttonText, successMessage) {
        $(buttonId).on('click', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var $log = $('#gulp-build-log');

            // Сохраняем оригинальный HTML кнопки
            var originalHtml = $btn.html();

            // Добавляем спиннер
            $btn
                .prop('disabled', true)
                .html('<span class="spinner" style="visibility: visible; margin: 0 8px 0 0;"></span> Processing...');

            // Очищаем лог
            $log.hide().empty();

            // Показываем лог с сообщением о начале
            $log.removeClass('error-log success-log')
                .addClass('processing-log')
                .show()
                .html('Starting ' + buttonText + '...<br>');

            $.ajax({
                url: gulpAjax.ajax_url,
                type: 'POST',
                data: {
                    action: actionName,
                    _ajax_nonce: gulpAjax.nonce
                },
                success: function (response) {
                    // Возвращаем оригинальный текст
                    $btn.prop('disabled', false).html(originalHtml);

                    if (response.success) {
                        $log.removeClass('error-log processing-log')
                            .addClass('success-log')
                            .html('<strong>' + successMessage + '</strong><br><br>' + 
                                  response.data.output.join('<br>'));
                    } else {
                        $log.removeClass('success-log processing-log')
                            .addClass('error-log')
                            .html('<strong>Error in ' + buttonText + '</strong><br><br>' + 
                                  response.data.output.join('<br>'));
                    }
                },
                error: function (xhr, status, error) {
                    $btn.prop('disabled', false).html(originalHtml);
                    $log.removeClass('success-log processing-log')
                        .addClass('error-log')
                        .html('<strong>Request error</strong><br><br>' + 
                              'Error: ' + error + '<br>' + 
                              'Status: ' + status);
                },
                timeout: 300000 // 5 минут таймаут для длительных процессов
            });
        });
    }

    // Инициализируем кнопки
    handleGulpButton('#run-gulp-dev', 'run_gulp_dev', 'DEV build', 'DEV build completed successfully!');
    handleGulpButton('#run-gulp-dist', 'run_gulp_dist', 'PROD build', 'PROD build completed successfully!');
    handleGulpButton('#run-gulp-css', 'run_gulp_css', 'CSS build', 'CSS build completed successfully!');
    handleGulpButton('#run-gulp-js', 'run_gulp_js', 'JS build', 'JS build completed successfully!');

    // Старая кнопка (для обратной совместимости)
    $("#run-gulp-build").on("click", function (e) {
        e.preventDefault();
        $("#run-gulp-dist").click(); // Перенаправляем на новую кнопку PROD
    });

    // Кнопка очистки лога
    $(document).on('click', '#clear-gulp-log', function(e) {
        e.preventDefault();
        $('#gulp-build-log').hide().empty();
    });

    // Кнопка загрузки _user-variables.scss в поле Custom SCSS
    $(document).on('click', '#load-user-variables-btn', function(e) {
        console.log('[Load User Variables] 3. CLICK detected on #load-user-variables-btn');
        e.preventDefault();

        var $btn = $(this);
        var originalHtml = $btn.html();
        console.log('[Load User Variables] 4. Button state saved, disabling...');

        if (typeof gulpBuildAjax === 'undefined') {
            console.error('[Load User Variables] ERROR: gulpBuildAjax is undefined! Script may not be properly enqueued on this page.');
            return;
        }
        if (!gulpBuildAjax.load_user_vars_nonce) {
            console.error('[Load User Variables] ERROR: gulpBuildAjax.load_user_vars_nonce is missing!');
            return;
        }

        $btn.prop('disabled', true).html('<span class="spinner" style="visibility: visible; margin: 0 4px 0 0;"></span> Loading...');

        var ajaxData = {
            action: 'load_user_variables',
            _ajax_nonce: gulpBuildAjax.load_user_vars_nonce
        };
        console.log('[Load User Variables] 5. Starting AJAX request:', {
            url: gulpBuildAjax.ajax_url,
            data: ajaxData
        });

        $.ajax({
            url: gulpBuildAjax.ajax_url,
            type: 'POST',
            data: ajaxData,
            beforeSend: function() {
                console.log('[Load User Variables] 6. AJAX beforeSend - request sent');
            },
            success: function(response) {
                console.log('[Load User Variables] 7. AJAX success - response received:', response);

                $btn.prop('disabled', false).html(originalHtml);

                if (response.success && response.data && response.data.content !== undefined) {
                    var content = response.data.content;
                    console.log('[Load User Variables] 8. Content received, length:', content ? content.length : 0);

                    // 1. Заполняем _user-variables (opt-gulp-sass-variation)
                    var $textarea = $('#opt-gulp-sass-variation-textarea');
                    console.log('[Load User Variables] 9. Textarea found:', $textarea.length, 'element(s)');

                    $textarea.val(content);
                    console.log('[Load User Variables] 10. Textarea value set');

                    var editorId = 'opt-gulp-sass-variation-editor';
                    if (typeof ace !== 'undefined' && ace.edit) {
                        try {
                            var editor = ace.edit(editorId);
                            if (editor && editor.getSession()) {
                                editor.getSession().setValue(content);
                                console.log('[Load User Variables] 11. ACE editor value set successfully');
                            } else {
                                console.warn('[Load User Variables] 11. ACE editor or session not available');
                            }
                        } catch (err) {
                            console.error('[Load User Variables] 11. ACE editor error:', err);
                        }
                    } else {
                        console.warn('[Load User Variables] 11. ACE not loaded - typeof ace:', typeof ace);
                    }

                    // 2. Заполняем активный шрифт (opt-font-variation) и fonts_combanation
                    if (response.data.font_content !== undefined && response.data.font_filename !== undefined) {
                        var fontContent = response.data.font_content;
                        var fontFilename = response.data.font_filename;
                        console.log('[Load User Variables] 12. Font content received, filename:', fontFilename);

                        var $fontTextarea = $('#opt-font-variation-textarea');
                        if ($fontTextarea.length) {
                            $fontTextarea.val(fontContent);
                            var fontEditorId = 'opt-font-variation-editor';
                            if (typeof ace !== 'undefined' && ace.edit) {
                                try {
                                    var fontEditor = ace.edit(fontEditorId);
                                    if (fontEditor && fontEditor.getSession()) {
                                        fontEditor.getSession().setValue(fontContent);
                                        console.log('[Load User Variables] 13. Font ACE editor value set');
                                    }
                                } catch (err) {
                                    console.error('[Load User Variables] 13. Font ACE editor error:', err);
                                }
                            }
                        }

                        var $fontsCombanation = $('#fonts_combanation').length ? $('#fonts_combanation') : $('input[name*="fonts_combanation"]');
                        if ($fontsCombanation.length) {
                            $fontsCombanation.val(fontFilename);
                            $fontsCombanation.trigger('change');
                            console.log('[Load User Variables] 14. fonts_combanation set to:', fontFilename);
                        }
                    }
                } else {
                    console.warn('[Load User Variables] 8. Unexpected response structure:', response);
                    alert(response.data && response.data.message ? response.data.message : 'Error loading file.');
                }
            },
            error: function(xhr, status, error) {
                console.error('[Load User Variables] 7. AJAX error:', {
                    xhr: xhr,
                    status: status,
                    error: error,
                    responseText: xhr && xhr.responseText ? xhr.responseText.substring(0, 500) : 'N/A',
                    responseJSON: xhr && xhr.responseJSON ? xhr.responseJSON : 'N/A'
                });

                $btn.prop('disabled', false).html(originalHtml);
                alert('Request error: ' + (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : error));
            },
            complete: function() {
                console.log('[Load User Variables] 12. AJAX complete (success or error)');
            }
        });
    });

    console.log('[Load User Variables] Handler for #load-user-variables-btn registered (delegated)');
    console.log('[Load User Variables] Button exists on load:', $('#load-user-variables-btn').length > 0);
});