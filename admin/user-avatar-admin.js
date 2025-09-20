jQuery(document).ready(function ($) {
  // Можно добавить предпросмотр изображения перед загрузкой
  $("#custom_avatar").on("change", function (e) {
    var file = e.target.files[0];
    if (file) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $("#custom_avatar").before(
          '<img src="' +
            e.target.result +
            '" style="width: 150px; height: 150px; object-fit: cover; margin-bottom: 10px; border-radius: 50%; border: 3px solid #f0f0f0;" />'
        );
      };
      reader.readAsDataURL(file);
    }
  });
});
