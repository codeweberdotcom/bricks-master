(function ($) {
	"use strict";

	$(document).ready(function () {
		// Функция запуска Gulp
		window.run_gulp_task = function () {
			// Получаем значение команды из текстового поля
			var gulpCommand = $("#redux-gulp_command").val();

			// Показать сообщение с командой (для теста)
			alert("Запуск команды: " + gulpCommand);

			// Отправляем AJAX-запрос на сервер
			$.ajax({
				url: ajaxurl, // WordPress глобальная переменная для AJAX
				method: "POST",
				data: {
					action: "run_gulp",
					command: gulpCommand,
				},
				success: function (response) {
					if (response.success) {
						alert("Результат: " + response.data);
					} else {
						alert("Ошибка: " + response.data);
					}
				},
				error: function () {
					alert("Ошибка запроса!");
				},
			});
		};
	});
})(jQuery);

