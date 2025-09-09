jQuery(document).ready(function ($) {
  $("#run-gulp-build").on("click", function (e) {
    e.preventDefault();

    var $btn = $(this);
    var $log = $("#gulp-build-log");

    // Добавляем спиннер
    $btn
      .prop("disabled", true)
      .html(
        '<span class="spinner" style="visibility: visible; float: none; margin: 0 8px 0 0;"></span> Assembly in progress...'
      );

    $.ajax({
      url: gulpBuildAjax.ajax_url,
      type: "POST",
      data: {
        action: "run_gulp_build",
        _ajax_nonce: gulpBuildAjax.nonce,
      },
      success: function (response) {
        // Возвращаем оригинальный текст
        $btn.prop("disabled", false).text("Start building CSS/JS");

        if (response.success) {
          $log.show().text(response.data.output.join("\n"));
        } else {
          $log.show().text("Error::\n" + response.data.output.join("\n"));
        }
      },
      error: function (xhr, status, error) {
        $btn.prop("disabled", false).text("Start building CSS/JS");
        $log.show().text("Request error: " + error);
      },
    });
  });
});
