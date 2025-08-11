jQuery(document).ready(function ($) {
  $(".redux-multi-plupload-uploader").each(function () {
    var $field = $(this);
    var $container = $('<div class="redux-multi-plupload-container"></div>');
    var $previewArea = $('<div class="redux-multi-preview-area d-flex"></div>');
    var $uploadBtn = $(
      '<button type="button btn btn-primary" class="button">Select or Upload Images</button>'
    );

    $field.after($container);
    $container.append($previewArea).append($uploadBtn);
    $field.hide();

    // Загрузка существующих изображений
    if ($field.val()) {
      var imageIds = $field.val().split(",");
      imageIds.forEach(function (id) {
        if (id) {
          loadImagePreview(id);
        }
      });
    }

    // Инициализация Plupload
    var uploader = new plupload.Uploader({
      runtimes: "html5",
      browse_button: $uploadBtn[0],
      drop_element: $container[0],
      url: ajaxurl,
      multi_selection: true,
      filters: {
        mime_types: [{ title: "Image files", extensions: "jpg,jpeg,gif,png" }],
        max_file_size: "10mb",
        prevent_duplicates: true,
      },
      multipart_params: {
        action: "redux_multi_handle_upload",
        _ajax_nonce: redux_plupload_vars.nonce,
      },
    });

    uploader.init();

    uploader.bind("FilesAdded", function (up, files) {
      uploader.start();
    });

    uploader.bind("FileUploaded", function (up, file, response) {
      var res = JSON.parse(response.response);
      if (res.success) {
        addImageToGallery(res.data.id, res.data.url);
      }
    });

    // Функция загрузки превью
    function loadImagePreview(id) {
      $.ajax({
        url: ajaxurl,
        method: "POST",
        data: {
          action: "redux_get_attachment",
          id: id,
        },
        success: function (response) {
          if (response.success) {
            $previewArea.append(createPreviewItem(id, response.data.url));
          }
        },
      });
    }

    // Функция создания элемента превью
    function createPreviewItem(id, url) {
      return $(
        '<div class="redux-preview-item" data-id="' +
          id +
          '">' +
          '<img src="' +
          url +
          '">' +
          '<span type="button" style="font-size: 25px" class="remove-image">&times;</span>' +
          "</div>"
      );
    }

    // Функция добавления изображения в галерею
    function addImageToGallery(id, url) {
      var currentIds = $field.val() ? $field.val().split(",") : [];
      if (!currentIds.includes(id.toString())) {
        currentIds.push(id);
        $field.val(currentIds.join(",")).trigger("change");
        $previewArea.append(createPreviewItem(id, url));
      }
    }

    // Удаление изображения
    $container.on("click", ".remove-image", function () {
      var $item = $(this).parent();
      var id = $item.data("id");
      var currentIds = $field.val().split(",");

      currentIds = currentIds.filter(function (item) {
        return item !== id.toString();
      });

      $field.val(currentIds.join(",")).trigger("change");
      $item.remove();
    });

    // Сортировка перетаскиванием
    $previewArea.sortable({
      update: function () {
        var newOrder = [];
        $previewArea.find(".redux-preview-item").each(function () {
          newOrder.push($(this).data("id"));
        });
        $field.val(newOrder.join(",")).trigger("change");
      },
    });
  });
});
