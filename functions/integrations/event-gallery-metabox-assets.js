/**
 * Event Gallery metabox: FilePond (upload) + SortableJS (reorder grid).
 * По паттерну project-gallery-metabox-assets.js
 *
 * @package Codeweber
 */
(function () {
	'use strict';

	var grid        = document.getElementById('event-gallery-sortable-grid');
	var input       = document.getElementById('event_gallery_ids');
	var filepondInput = document.getElementById('event_gallery_filepond');
	var settings    = typeof codeweberEventGallery !== 'undefined' ? codeweberEventGallery : {};

	function getIds() {
		if (!input || !input.value) return [];
		return input.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
	}

	function setIds(ids) {
		if (input) input.value = ids.join(',');
	}

	function addId(id) {
		var ids = getIds();
		if (ids.indexOf(String(id)) !== -1) return;
		ids.push(String(id));
		setIds(ids);
	}

	function removeId(id) {
		var ids = getIds().filter(function (i) { return i !== String(id); });
		setIds(ids);
	}

	function addItemToGrid(attachmentId, imageUrl) {
		if (!grid) return;
		var item = document.createElement('div');
		item.className = 'project-gallery-item';
		item.setAttribute('data-id', attachmentId);
		var removeLabel = (settings.i18n && settings.i18n.remove) ? settings.i18n.remove : 'Remove';
		item.innerHTML = '<img src="' + (imageUrl || '') + '" alt="">' +
			'<button type="button" class="project-gallery-remove" aria-label="' + removeLabel.replace(/"/g, '&quot;') + '">&times;</button>';
		grid.appendChild(item);
		addId(attachmentId);
		item.querySelector('.project-gallery-remove').addEventListener('click', function () {
			item.remove();
			removeId(attachmentId);
		});
	}

	// Attach remove handlers to existing items
	if (grid) {
		grid.querySelectorAll('.project-gallery-remove').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var item = btn.closest('.project-gallery-item');
				if (item) {
					removeId(item.getAttribute('data-id'));
					item.remove();
				}
			});
		});

		// SortableJS
		if (typeof Sortable !== 'undefined') {
			Sortable.create(grid, {
				animation: 150,
				onEnd: function () {
					var ids = [];
					grid.querySelectorAll('.project-gallery-item').forEach(function (el) {
						ids.push(el.getAttribute('data-id'));
					});
					setIds(ids);
				}
			});
		}
	}

	// FilePond
	if (filepondInput && typeof FilePond !== 'undefined') {
		var pond = FilePond.create(filepondInput, {
			allowMultiple:    true,
			instantUpload:    true,
			labelIdle:        (settings.i18n && settings.i18n.labelIdle) || 'Drag & Drop or <span class="filepond--label-action">Browse</span>',
			server: {
				process: {
					url:    settings.uploadUrl || '',
					method: 'POST',
					headers: {},
					withCredentials: false,
					ondata: function (formData) {
						formData.append('nonce',   settings.nonce || '');
						formData.append('post_id', settings.postId || 0);
						return formData;
					},
					onload: function (response) {
						try {
							var data = JSON.parse(response);
							if (data.success && data.data && data.data.file_id) {
								addItemToGrid(data.data.file_id, data.data.thumbnail_url || '');
								return data.data.file_id;
							}
						} catch (e) {}
						return response;
					},
					onerror: function (response) {
						try {
							var data = JSON.parse(response);
							return data.data && data.data.message ? data.data.message : (settings.i18n && settings.i18n.uploadFailed) || 'Upload failed';
						} catch (e) {
							return (settings.i18n && settings.i18n.uploadFailed) || 'Upload failed';
						}
					}
				},
				revert: null,
				restore: null,
				load: null,
				fetch: null
			}
		});
	}
})();
