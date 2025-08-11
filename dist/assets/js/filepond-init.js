jQuery(document).ready(function ($) {
  // Инициализация FilePond для всех полей с классом .filepond-field
  $(".filepond-field").each(function () {
    var $input = $(this);
    var $container = $('<div class="filepond-container"></div>');

    $input.after($container);
    $input.hide();

    // Создаем input type=file для FilePond
    var $fileInput = $('<input type="file">');
    $container.append($fileInput);

    // Инициализация FilePond
    $fileInput.filepond({
      allowMultiple: false,
      server: {
        url: filepond_vars.ajax_url,
        process: {
          url: "?action=filepond_upload",
          method: "POST",
          headers: {
            "X-WP-Nonce": filepond_vars.nonce,
          },
          onload: function (response) {
            var data = JSON.parse(response);
            if (data.success) {
              // Сохраняем ID вложения в скрытое поле Redux
              $input.val(data.data.id).trigger("change");
              return data.data.id;
            }
            return null;
          },
          onerror: function (response) {
            console.error("Upload error:", response);
          },
        },
        revert: {
          url: "?action=filepond_upload",
          method: "DELETE",
          headers: {
            "X-WP-Nonce": filepond_vars.nonce,
          },
          onload: function (response) {
            // Очищаем поле при удалении файла
            $input.val("").trigger("change");
          },
        },
      },
    });

    // Если есть сохраненное значение, загружаем превью
    if ($input.val()) {
      $.ajax({
        url: filepond_vars.ajax_url,
        method: "POST",
        data: {
          action: "wp_get_attachment_url",
          id: $input.val(),
          nonce: filepond_vars.nonce,
        },
        success: function (response) {
          if (response.success) {
            var pond = $fileInput[0].filepond;
            pond.addFile(response.data).then(function (file) {
              pond.setOptions({
                fileMetadata: {
                  id: $input.val(),
                },
              });
            });
          }
        },
      });
    }
  });
});
