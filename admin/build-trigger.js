jQuery(document).ready(function ($) {
    // Локализация для AJAX
    var gulpAjax = {
        ajax_url: gulpBuildAjax.ajax_url,
        nonce: gulpBuildAjax.nonce
    };

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
});