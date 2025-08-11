jQuery(function ($) {
  // Регистрация плагина превью для FilePond
  $.fn.filepond.registerPlugin(FilePondPluginImagePreview);

  // Инициализируем FilePond на нужном input
  // Найдём input с Redux media по id
  const input = $('input[name="redux[custom_filepond_upload]"]');

  if (input.length) {
    // Превращаем input в FilePond
    input.filepond({
      server: {
        process: {
          url: filepond_ajax.ajax_url + "?action=filepond_upload",
          method: "POST",
          headers: {
            "X-WP-Nonce": filepond_ajax.nonce,
          },
          onload: function (response) {
            let res = JSON.parse(response);
            if (res.success) {
              // Обновляем value скрытого input с ID файла
              input.val(res.data.id).trigger("change");

              // Можно добавить превью рядом, если хочешь
              // Или обновить Redux UI, если нужно
            } else {
              alert(res.data.message);
            }
          },
        },
      },
      allowMultiple: false,
      instantUpload: true,
      acceptedFileTypes: ["image/*", "application/pdf"], // Настрой по необходимости
    });
  }
});
