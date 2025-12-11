/**
 * PDF Thumbnail Generator - JavaScript
 * 
 * Генерирует превью PDF файлов используя PDF.js
 * Работает полностью в браузере, не требует серверных инструментов
 * 
 * @package Codeweber
 */

(function($) {
	'use strict';
	
	// Настраиваем PDF.js worker
	if (typeof pdfjsLib !== 'undefined') {
		pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
	}
	
	/**
	 * Генерирует превью из PDF файла
	 * 
	 * @param {File} file PDF файл
	 * @param {Object} options Опции (scale, format, quality)
	 * @returns {Promise} Promise с base64 изображением
	 */
	function generatePdfThumbnail(file, options) {
		options = options || {};
		var scale = options.scale || 2.0;
		var format = options.format || 'image/jpeg';
		var quality = options.quality || 0.9;
		
		return new Promise(function(resolve, reject) {
			if (typeof pdfjsLib === 'undefined') {
				reject(new Error('PDF.js не загружен'));
				return;
			}
			
			var fileReader = new FileReader();
			
			fileReader.onload = function(event) {
				var typedArray = new Uint8Array(event.target.result);
				
				// Загружаем PDF
				pdfjsLib.getDocument({data: typedArray}).promise
					.then(function(pdf) {
						// Получаем первую страницу
						return pdf.getPage(1);
					})
					.then(function(page) {
						// Настройки рендеринга
						var viewport = page.getViewport({scale: scale});
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
							var imageData = canvas.toDataURL(format, quality);
							resolve(imageData);
						});
					})
					.catch(function(error) {
						reject(error);
					});
			};
			
			fileReader.onerror = function(error) {
				reject(new Error('Ошибка чтения файла: ' + error));
			};
			
			fileReader.readAsArrayBuffer(file);
		});
	}
	
	/**
	 * Сохраняет превью на сервер
	 * 
	 * @param {string} imageData Base64 изображение
	 * @param {number} postId ID поста
	 * @returns {Promise} Promise с результатом
	 */
	function saveThumbnailToServer(imageData, postId) {
		return $.ajax({
			url: codeweberPdfThumbnail.ajaxurl,
			type: 'POST',
			data: {
				action: 'codeweber_save_pdf_thumbnail',
				nonce: codeweberPdfThumbnail.nonce,
				post_id: postId,
				image_data: imageData
			}
		});
	}
	
	// Экспортируем функции для использования
	window.codeweberPdfThumbnailGenerator = {
		generate: generatePdfThumbnail,
		save: saveThumbnailToServer
	};
	
})(jQuery);

