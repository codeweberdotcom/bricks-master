jQuery(document).ready(function($) {
	$('#generate-staff-qrcode-btn').on('click', function(e) {
		e.preventDefault();
		
		var $button = $(this);
		var $spinner = $('#qrcode-generating-spinner');
		var $message = $('#qrcode-message');
		var $container = $('#staff-qrcode-container');
		var postId = $button.data('post-id');
		var nonce = $button.data('nonce');
		
		// Показываем спиннер
		$spinner.css('visibility', 'visible');
		$button.prop('disabled', true);
		$message.hide();
		
		// AJAX запрос
		$.ajax({
			url: staffQrcode.ajax_url,
			type: 'POST',
			data: {
				action: 'generate_staff_qrcode',
				post_id: postId,
				nonce: nonce
			},
			success: function(response) {
				$spinner.css('visibility', 'hidden');
				$button.prop('disabled', false);
				
				if (response.success) {
					// Обновляем изображение QR кода
					var qrcodeUrl = response.data.qrcode_url;
					var qrcodeId = response.data.qrcode_id;
					
					// Обновляем или создаем изображение
					var $img = $('#staff-qrcode-image');
					if ($img.length) {
						$img.attr('src', qrcodeUrl);
					} else {
						$container.html(
							'<div style="margin-bottom: 10px;">' +
							'<img id="staff-qrcode-image" src="' + qrcodeUrl + '" alt="QR Code" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; background: #fff;">' +
							'</div>' +
							'<p style="margin: 5px 0;">' +
							'<strong>QR Code ID:</strong> ' +
							'<input type="hidden" id="staff_qrcode_id" name="staff_qrcode_id" value="' + qrcodeId + '">' +
							qrcodeId +
							'</p>'
						);
					}
					
					// Обновляем скрытое поле
					$('#staff_qrcode_id').val(qrcodeId);
					
					// Показываем сообщение об успехе
					$message.text(staffQrcode.success).css('color', '#46b450').show();
				} else {
					// Показываем сообщение об ошибке
					var errorMsg = response.data && response.data.message ? response.data.message : staffQrcode.error;
					$message.text(errorMsg).css('color', '#dc3232').show();
				}
			},
			error: function() {
				$spinner.css('visibility', 'hidden');
				$button.prop('disabled', false);
				$message.text(staffQrcode.error).css('color', '#dc3232').show();
			}
		});
	});
	
	// Обработчик удаления QR кода
	$(document).on('click', '#delete-staff-qrcode-btn', function(e) {
		e.preventDefault();
		
		if (!confirm('Вы уверены, что хотите удалить QR код?')) {
			return;
		}
		
		var $button = $(this);
		var $spinner = $('#qrcode-generating-spinner');
		var $message = $('#qrcode-message');
		var $container = $('#staff-qrcode-container');
		var postId = $button.data('post-id');
		var nonce = $button.data('nonce');
		
		// Показываем спиннер
		$spinner.css('visibility', 'visible');
		$button.prop('disabled', true);
		$('#generate-staff-qrcode-btn').prop('disabled', true);
		$message.hide();
		
		// AJAX запрос
		$.ajax({
			url: staffQrcode.ajax_url,
			type: 'POST',
			data: {
				action: 'delete_staff_qrcode',
				post_id: postId,
				nonce: nonce
			},
			success: function(response) {
				$spinner.css('visibility', 'hidden');
				$button.prop('disabled', false);
				$('#generate-staff-qrcode-btn').prop('disabled', false);
				
				if (response.success) {
					// Удаляем изображение и информацию о QR коде
					$container.html('<p style="color: #666; font-style: italic;">QR код не сгенерирован</p>');
					$('#staff_qrcode_id').val('');
					
					// Скрываем кнопку удаления
					$button.hide();
					
					// Показываем сообщение об успехе
					$message.text(response.data.message || staffQrcode.delete_success).css('color', '#46b450').show();
				} else {
					// Показываем сообщение об ошибке
					var errorMsg = response.data && response.data.message ? response.data.message : staffQrcode.delete_error;
					$message.text(errorMsg).css('color', '#dc3232').show();
				}
			},
			error: function() {
				$spinner.css('visibility', 'hidden');
				$button.prop('disabled', false);
				$('#generate-staff-qrcode-btn').prop('disabled', false);
				$message.text(staffQrcode.delete_error).css('color', '#dc3232').show();
			}
		});
	});
});

