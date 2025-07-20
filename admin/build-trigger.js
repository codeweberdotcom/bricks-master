jQuery(document).ready(function ($) {
  $("#run-gulp-build").on("click", function (e) {
    e.preventDefault();

    var $btn = $(this);
    var $log = $("#gulp-build-log");

    $btn.prop("disabled", true).text("Собираем…");

    $.ajax({
      url: gulpBuildAjax.ajax_url,
      type: "POST",
      data: {
        action: "run_gulp_build",
        _ajax_nonce: gulpBuildAjax.nonce,
      },
      success: function (response) {
        $btn.prop("disabled", false).text("Собрать CSS и JS");

        if (response.success) {
          $log.show().text(response.data.output.join("\n"));
        } else {
          $log.show().text("Ошибка:\n" + response.data.output.join("\n"));
        }
      },
      error: function (xhr, status, error) {
        $btn.prop("disabled", false).text("Собрать CSS и JS");
        $log.show().text("Ошибка запроса: " + error);
      },
    });
  });
});
