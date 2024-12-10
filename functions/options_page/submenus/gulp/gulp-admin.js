document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("gulp-form");
  const output = document.getElementById("gulp-output");

  form.addEventListener("submit", function (e) {
    e.preventDefault(); // Отменяем стандартное поведение формы

    // Отображаем сообщение "Выполняется сборка..."
    output.innerHTML = "<p>Выполняется сборка...</p>";

    const formData = new FormData(form);

    // Добавляем nonce в данные формы
    formData.append("gulp_nonce", ajax_object.nonce);
    formData.append("action", "run_gulp_task");

    // Отправляем запрос на сервер через fetch
    fetch(ajax_object.ajaxurl, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        console.log(data); // Для проверки структуры данных

        if (data.success) {
          const terminalOutput = data.data.terminal_output.replace(
            /\n/g,
            "<br>"
          );
          output.innerHTML = `
            <div class="alert alert-success alert-icon" role="alert">
              <i class="uil uil-check-circle"></i> Сборка завершена успешно!
            </div>
            <pre style="background: #000; color: #bfbfbf; padding: 20px;">${terminalOutput}</pre>`;
        } else {
          const errorOutput =
            data.data?.terminal_output || "Нет данных терминала";
          output.innerHTML = `
            <p>Ошибка: ${data.data?.message || "Неизвестная ошибка"}</p>
            <pre style="background: #000; color: #bfbfbf; padding: 20px;">${errorOutput}</pre>`;
        }
      })
      .catch((error) => {
        output.innerHTML = `<p>Произошла ошибка: ${error.message}</p>`;
      });
  });
});
