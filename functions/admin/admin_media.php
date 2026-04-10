<?php

/**
 * Добавляет новую колонку "Image Sizes" в таблицу медиафайлов в админке WordPress.
 * Эта колонка выводит список всех размеров изображения (thumbnail, medium, large и т.д.),
 * которые были созданы для каждого вложения (attachment).
 *
 * Фильтр `manage_upload_columns` расширяет набор колонок в медиабиблиотеке.
 *
 * Хук `manage_media_custom_column` выводит содержимое для колонки "Image Sizes",
 * получая метаданные вложения и отображая размеры с их шириной и высотой.
 *
 * Если у вложения нет дополнительных размеров, выводится текст "No sizes".
 *
 * Размеры выводятся с переносом строк (тег <br>) для удобного чтения.
 *
 * @param array $columns Массив колонок таблицы медиафайлов.
 * @return array Массив колонок с добавленной "Image Sizes".
 */
add_filter('manage_upload_columns', function ($columns) {
   $columns['image_sizes'] = __('Image Sizes', 'codeweber');
   return $columns;
});

/**
 * Заполняет колонку "Image Sizes" в таблице медиафайлов.
 *
 * @param string $column_name Имя текущей колонки.
 * @param int    $post_id     ID вложения (attachment).
 */
add_action('manage_media_custom_column', function ($column_name, $post_id) {
   if ($column_name === 'image_sizes') {
      $meta = wp_get_attachment_metadata($post_id);
      if (!$meta || empty($meta['sizes'])) {
         echo __('No sizes', 'codeweber');
         return;
      }

      $sizes = $meta['sizes'];
      $output = [];

      foreach ($sizes as $size_name => $size_info) {
         if (isset($size_info['width'], $size_info['height'])) {
            $output[] = sprintf(
               '%s (%d×%d)',
               esc_html($size_name),
               intval($size_info['width']),
               intval($size_info['height'])
            );
         } else {
            $output[] = esc_html($size_name);
         }
      }

      echo implode('<br>', $output);
   }
}, 10, 2);

/**
 * Кнопка «Регенерировать» в строке списка медиатеки (list view).
 */
add_filter( 'media_row_actions', function ( $actions, $post ) {
	if ( ! wp_attachment_is_image( $post->ID ) ) {
		return $actions;
	}
	$actions['cw_regen'] = sprintf(
		'<a href="#" class="cw-regen-single" data-id="%d">%s</a>',
		$post->ID,
		esc_html__( 'Regenerate', 'codeweber' )
	);
	return $actions;
}, 10, 2 );

/**
 * Кнопка «Регенерировать» в диалоге редактирования вложения (grid modal + edit page).
 */
add_filter( 'attachment_fields_to_edit', function ( $form_fields, $post ) {
	if ( ! wp_attachment_is_image( $post->ID ) ) {
		return $form_fields;
	}
	$form_fields['cw_regen'] = [
		'label' => '',
		'input' => 'html',
		'html'  => sprintf(
			'<button type="button" class="button cw-regen-single" data-id="%d" style="margin-bottom:4px;">%s</button>'
			. '<div class="cw-regen-result" id="cw-regen-result-%d" style="margin-top:6px;font-size:12px;"></div>',
			$post->ID,
			esc_html__( 'Regenerate thumbnails', 'codeweber' ),
			$post->ID
		),
	];
	return $form_fields;
}, 10, 2 );

/**
 * Enqueue inline JS + nonce для кнопок регенерации на странице медиатеки.
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, [ 'upload.php', 'post.php' ], true ) ) {
		return;
	}
	$nonce    = wp_create_nonce( 'cw_media_regen' );
	$nonce_js = wp_json_encode( $nonce );
	$js       = '(function(){
	var nonce = ' . $nonce_js . ';
	var ajaxUrl = window.ajaxurl || "/wp-admin/admin-ajax.php";

	function doRegen(id, btn, resultEl) {
		btn.disabled = true;
		var origText = btn.textContent;
		btn.textContent = "\u23F3 ...";
		if (resultEl) { resultEl.innerHTML = ""; }

		var data = new URLSearchParams();
		data.append("action", "cw_media_regen_single");
		data.append("nonce", nonce);
		data.append("attachment_id", id);

		fetch(ajaxUrl, { method: "POST", body: data })
			.then(function(r){ return r.json(); })
			.then(function(res){
				btn.textContent = origText;
				btn.disabled = false;
				if (!resultEl) return;
				if (res.success) {
					var sizes = res.data.sizes || [];
					var html = "<span style=\"color:#3c763d;\">\u2713 " + sizes.length + " \u0440\u0430\u0437\u043c\u0435\u0440\u043e\u0432:</span><br>";
					html += "<span style=\"color:#555;\">" + sizes.join(", ") + "</span>";
					resultEl.innerHTML = html;
				} else {
					var msg = res.data && res.data.message ? res.data.message : "\u041e\u0448\u0438\u0431\u043a\u0430";
					resultEl.innerHTML = "<span style=\"color:#c0392b;\">\u2717 " + msg + "</span>";
				}
			})
			.catch(function(){
				btn.textContent = origText;
				btn.disabled = false;
				if (resultEl) resultEl.innerHTML = "<span style=\"color:#c0392b;\">\u2717 \u041e\u0448\u0438\u0431\u043a\u0430 \u0437\u0430\u043f\u0440\u043e\u0441\u0430</span>";
			});
	}

	document.addEventListener("click", function(e){
		var btn = e.target.closest(".cw-regen-single");
		if (!btn) return;
		e.preventDefault();
		var id = parseInt(btn.dataset.id, 10);
		if (!id) return;
		var resultEl = document.getElementById("cw-regen-result-" + id);
		if (!resultEl) {
			resultEl = btn.nextElementSibling;
			if (!resultEl || !resultEl.classList.contains("cw-regen-inline-result")) {
				resultEl = document.createElement("span");
				resultEl.className = "cw-regen-inline-result";
				resultEl.style.marginLeft = "8px";
				resultEl.style.fontSize = "12px";
				btn.parentNode.insertBefore(resultEl, btn.nextSibling);
			}
		}
		doRegen(id, btn, resultEl);
	});
})();';
	// jquery всегда загружен в WP Admin — используем один хендл, чтобы не дублировать listener
	wp_add_inline_script( 'jquery', $js );
} );
