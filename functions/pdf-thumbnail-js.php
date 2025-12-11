<?php
/**
 * PDF Thumbnail Generator - JavaScript Solution
 * 
 * Использует PDF.js для генерации превью PDF в браузере
 * Не требует установки Ghostscript или Imagick на сервер
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Подключает PDF.js и скрипты для генерации превью (только в админке)
 */
function codeweber_enqueue_pdf_thumbnail_scripts($hook) {
	// Загружаем только на страницах редактирования документов
	global $post_type;
	
	if ($hook !== 'post.php' && $hook !== 'post-new.php') {
		return;
	}
	
	if ($post_type !== 'documents') {
		return;
	}
	
	// PDF.js из CDN
	wp_enqueue_script(
		'pdfjs',
		'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js',
		[],
		'3.11.174',
		true
	);
	
	// Наш скрипт для генерации превью
	wp_enqueue_script(
		'codeweber-pdf-thumbnail',
		get_template_directory_uri() . '/functions/js/pdf-thumbnail.js',
		['pdfjs', 'jquery'],
		'1.0.0',
		true
	);
	
	wp_localize_script('codeweber-pdf-thumbnail', 'codeweberPdfThumbnail', [
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('pdf_thumbnail_nonce'),
	]);
}
add_action('admin_enqueue_scripts', 'codeweber_enqueue_pdf_thumbnail_scripts');

/**
 * AJAX обработчик для сохранения превью PDF
 */
function codeweber_save_pdf_thumbnail_ajax() {
	check_ajax_referer('pdf_thumbnail_nonce', 'nonce');
	
	if (!current_user_can('upload_files')) {
		wp_send_json_error(['message' => 'Недостаточно прав']);
	}
	
	$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
	$image_data = isset($_POST['image_data']) ? $_POST['image_data'] : '';
	
	if (!$post_id || !$image_data) {
		wp_send_json_error(['message' => 'Недостаточно данных']);
	}
	
	// Декодируем base64 изображение
	$image_data = str_replace('data:image/jpeg;base64,', '', $image_data);
	$image_data = str_replace('data:image/png;base64,', '', $image_data);
	$image_data = base64_decode($image_data);
	
	if (!$image_data) {
		wp_send_json_error(['message' => 'Ошибка декодирования изображения']);
	}
	
	// Сохраняем изображение
	$upload_dir = wp_upload_dir();
	$filename = 'pdf-thumbnail-' . $post_id . '-' . time() . '.jpg';
	$file_path = $upload_dir['path'] . '/' . $filename;
	$file_url = $upload_dir['url'] . '/' . $filename;
	
	// Сохраняем файл
	file_put_contents($file_path, $image_data);
	
	// Создаем вложение
	$file_type = wp_check_filetype($filename, null);
	$attachment = [
		'guid' => $file_url,
		'post_mime_type' => $file_type['type'],
		'post_title' => get_the_title($post_id) . ' - PDF Preview',
		'post_content' => '',
		'post_status' => 'inherit',
	];
	
	$attachment_id = wp_insert_attachment($attachment, $file_path, $post_id);
	
	if (is_wp_error($attachment_id)) {
		wp_send_json_error(['message' => 'Ошибка создания вложения']);
	}
	
	// Генерируем метаданные
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	$attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
	wp_update_attachment_metadata($attachment_id, $attachment_data);
	
	// Удаляем старое превью PDF, если есть
	$old_thumbnail_id = get_post_thumbnail_id($post_id);
	if ($old_thumbnail_id) {
		// Проверяем, что это превью PDF (содержит "PDF Preview" в названии)
		$old_attachment = get_post($old_thumbnail_id);
		if ($old_attachment && strpos($old_attachment->post_title, 'PDF Preview') !== false) {
			wp_delete_attachment($old_thumbnail_id, true);
		}
	}
	
	// Устанавливаем как featured image
	$result = set_post_thumbnail($post_id, $attachment_id);
	
	if (!$result) {
		wp_send_json_error(['message' => 'Ошибка установки featured image']);
	}
	
	wp_send_json_success([
		'attachment_id' => $attachment_id,
		'url' => wp_get_attachment_image_url($attachment_id, 'medium'),
		'thumbnail_id' => $attachment_id,
		'message' => 'Превью успешно создано и установлено как featured image',
	]);
}
add_action('wp_ajax_codeweber_save_pdf_thumbnail', 'codeweber_save_pdf_thumbnail_ajax');

/**
 * Добавляет скрипт для автоматической генерации превью при загрузке PDF
 */
function codeweber_add_pdf_thumbnail_auto_generate() {
	global $post_type;
	
	if ($post_type !== 'documents') {
		return;
	}
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		// Проверяем наличие PDF.js
		if (typeof pdfjsLib === 'undefined') {
			console.error('PDF.js не загружен');
			return;
		}
		
		// Настраиваем PDF.js worker
		pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
		
		// Функция для установки превью (определяем до использования)
		function codeweber_set_pdf_thumbnail(postId, imageData) {
			$.ajax({
				url: codeweberPdfThumbnail.ajaxurl,
				type: 'POST',
				data: {
					action: 'codeweber_save_pdf_thumbnail',
					nonce: codeweberPdfThumbnail.nonce,
					post_id: postId,
					image_data: imageData
				},
				success: function(response) {
					if (response.success) {
						$('.pdf-thumbnail-status').html('✅ Превью успешно создано и установлено как featured image!');
						// Обновляем превью в интерфейсе
						if (response.data && response.data.url) {
							// Обновляем или создаем превью в блоке Featured Image
							var $featuredImageDiv = $('#postimagediv');
							var $existingImg = $featuredImageDiv.find('img');
							
							if ($existingImg.length) {
								$existingImg.attr('src', response.data.url);
								$existingImg.show();
							} else {
								// Создаем изображение, если его нет
								$featuredImageDiv.find('.inside').prepend(
									'<div class="wp-post-image-wrapper">' +
									'<img src="' + response.data.url + '" style="max-width: 100%; height: auto;" />' +
									'</div>'
								);
							}
							
							// Обновляем скрытое поле с ID вложения
							if (response.data.attachment_id) {
								$('#_thumbnail_id').val(response.data.attachment_id);
							}
							
							// Обновляем текст кнопки
							$featuredImageDiv.find('.remove-post-thumbnail').show();
							var $setBtn = $featuredImageDiv.find('.set-post-thumbnail');
							if ($setBtn.length) {
								$setBtn.text('Заменить превью');
							}
						}
					} else {
						$('.pdf-thumbnail-status').html('❌ Ошибка: ' + (response.data && response.data.message ? response.data.message : 'Неизвестная ошибка'));
					}
				},
				error: function(xhr, status, error) {
					console.error('AJAX Error:', error);
					$('.pdf-thumbnail-status').html('❌ Ошибка отправки данных на сервер: ' + error);
				}
			});
		}
		
		// Обработчик изменения файла
		$('#document_file').on('change', function(e) {
			var file = this.files[0];
			
			if (!file || file.type !== 'application/pdf') {
				return;
			}
			
			// Показываем индикатор загрузки
			var $metaBox = $(this).closest('.document-file-upload');
			// Удаляем предыдущий статус, если есть
			$metaBox.find('.pdf-thumbnail-status').remove();
			$metaBox.append('<p class="pdf-thumbnail-status">Генерация превью...</p>');
			
			var fileReader = new FileReader();
			fileReader.onload = function(event) {
				var typedArray = new Uint8Array(event.target.result);
				
				// Загружаем PDF
				pdfjsLib.getDocument({data: typedArray}).promise.then(function(pdf) {
					// Получаем первую страницу
					return pdf.getPage(1);
				}).then(function(page) {
					// Настройки рендеринга
					var viewport = page.getViewport({scale: 2.0}); // Увеличиваем для качества
					var canvas = document.createElement('canvas');
					var context = canvas.getContext('2d');
					
					canvas.height = viewport.height;
					canvas.width = viewport.width;
					
					// Рендерим страницу
					var renderContext = {
						canvasContext: context,
						viewport: viewport
					};
					
					return page.render(renderContext).promise.then(function() {
						// Конвертируем canvas в изображение
						var imageData = canvas.toDataURL('image/jpeg', 0.9);
						
						// Получаем ID поста (может быть 0 для новых постов)
						var postId = $('#post_ID').val();
						
						// Если пост еще не сохранен, сохраняем превью временно и установим после сохранения
						if (!postId || postId === '0' || postId === '') {
							// Сохраняем изображение в localStorage для установки после сохранения поста
							localStorage.setItem('codeweber_pdf_thumbnail_pending', imageData);
							$('.pdf-thumbnail-status').html('⚠️ Сохраните пост, чтобы установить превью. Превью будет установлено автоматически.');
							
							// Добавляем обработчик сохранения поста
							$(document).on('click', '#publish, #save-post', function() {
								setTimeout(function() {
									var newPostId = $('#post_ID').val();
									if (newPostId && newPostId !== '0') {
										var pendingImage = localStorage.getItem('codeweber_pdf_thumbnail_pending');
										if (pendingImage) {
											codeweber_set_pdf_thumbnail(newPostId, pendingImage);
											localStorage.removeItem('codeweber_pdf_thumbnail_pending');
										}
									}
								}, 1000);
							});
							return;
						}
						
						// Отправляем на сервер
						codeweber_set_pdf_thumbnail(postId, imageData);
					});
				}).catch(function(error) {
					console.error('PDF Error:', error);
					$('.pdf-thumbnail-status').html('❌ Ошибка обработки PDF: ' + error.message);
				});
			};
			
			fileReader.readAsArrayBuffer(file);
		});
	});
	</script>
	<style>
	.pdf-thumbnail-status {
		margin-top: 10px;
		padding: 8px;
		background: #f0f0f0;
		border-left: 4px solid #2271b1;
	}
	</style>
	<?php
}
add_action('admin_footer', 'codeweber_add_pdf_thumbnail_auto_generate');

