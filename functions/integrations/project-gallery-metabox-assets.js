/**
 * Project Gallery metabox: FilePond (upload) + SortableJS (reorder grid).
 *
 * @package Codeweber
 */
(function () {
	'use strict';

	var grid = document.getElementById('project-gallery-sortable-grid');
	var input = document.getElementById('project_gallery_ids');
	var filepondInput = document.getElementById('project_gallery_filepond');
	var settings = typeof codeweberProjectGallery !== 'undefined' ? codeweberProjectGallery : {};

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

	function updateOrderFromGrid() {
		if (!grid) return;
		var items = grid.querySelectorAll('.project-gallery-item');
		var ids = [];
		items.forEach(function (el) {
			var id = el.getAttribute('data-id');
			if (id) ids.push(id);
		});
		setIds(ids);
	}

	// SortableJS on grid
	if (grid && typeof Sortable !== 'undefined') {
		Sortable.create(grid, {
			animation: 150,
			onEnd: function () {
				updateOrderFromGrid();
			}
		});
	}

	// Remove buttons on existing items
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
	}

	// FilePond (only when post already exists so we have post_id for attachment parent)
	if (filepondInput && typeof FilePond !== 'undefined' && settings.postId && Number(settings.postId) > 0) {
		var thumbCache = {};
		var uploadUrl = (settings.uploadUrl || '').replace(/&amp;/g, '&');
		console.log('[Project Gallery] Init:', {
			uploadUrl: uploadUrl,
			postId: settings.postId,
			nonceLength: (settings.nonce || '').length
		});

		var i18n = settings.i18n || {};
		var config = {
			allowMultiple: true,
			credits: false,
			acceptedFileTypes: ['image/*'],
			labelIdle: i18n.labelIdle || 'Drag & Drop your files or <span class="filepond--label-action">browse</span>',
			labelFileProcessingComplete: i18n.uploadComplete || 'Upload complete',
			labelTapToCancel: i18n.tapToCancel || 'Tap to cancel',
			labelTapToUndo: i18n.tapToUndo || 'Tap to undo',
			server: {
				process: {
					url: uploadUrl,
					method: 'POST',
					withCredentials: true,
					name: 'file',
					ondata: function (formData) {
						formData.append('action', 'codeweber_project_gallery_upload');
						formData.append('nonce', settings.nonce || '');
						formData.append('post_id', String(settings.postId || ''));
						console.log('[Project Gallery] Request formData: action, nonce, post_id=', settings.postId);
						return formData;
					},
					onload: function (response) {
						console.log('[Project Gallery] onload raw response:', typeof response, response);
						try {
							var data = typeof response === 'string' ? JSON.parse(response) : response;
							console.log('[Project Gallery] onload parsed:', data);
							if (data && data.data) {
								var id = data.data.file_id || (data.data.file && data.data.file.id);
								if (id) {
									if (data.data.thumbnail_url) {
										thumbCache[String(id)] = data.data.thumbnail_url;
									}
									console.log('[Project Gallery] Success, file_id:', id);
									return id;
								}
							}
							console.warn('[Project Gallery] onload: no file_id in response');
							return response;
						} catch (e) {
							console.error('[Project Gallery] onload parse error:', e, 'response:', response);
							return response;
						}
					},
					onerror: function (response) {
						console.error('[Project Gallery] onerror raw response:', typeof response, response);
						try {
							var data = typeof response === 'string' ? JSON.parse(response) : response;
							var msg = (data && data.data && data.data.message) ? data.data.message : (response || (i18n.uploadFailed || 'Upload failed'));
							console.error('[Project Gallery] onerror parsed:', data, 'message:', msg);
							return msg;
						} catch (e) {
							console.error('[Project Gallery] onerror parse error:', e, 'response:', response);
							return response || (i18n.uploadFailed || 'Upload failed');
						}
					}
				}
			}
		};

		var pond = FilePond.create(filepondInput, config);

		pond.on('addfile', function (err, file) {
			if (err) {
				console.error('[Project Gallery] addfile error:', err);
				return;
			}
			console.log('[Project Gallery] addfile:', file.filename);
			file.setMetadata('post_id', settings.postId);
		});

		pond.on('processfile', function (err, file) {
			if (err) {
				console.error('[Project Gallery] processfile error:', err, 'file:', file && file.filename);
				return;
			}
			var serverId = file.serverId;
			console.log('[Project Gallery] processfile done, serverId:', serverId);
			if (serverId && grid) {
				var imgUrl = thumbCache[String(serverId)] || '';
				if (!imgUrl && file.getFileEncodeDataURL) {
					imgUrl = file.getFileEncodeDataURL() || '';
				}
				addItemToGrid(serverId, imgUrl);
				delete thumbCache[String(serverId)];
			}
		});

		pond.on('processfileerror', function (file, error) {
			console.error('[Project Gallery] processfileerror:', file && file.filename, error);
		});

		pond.on('processfiles', function () {
			pond.removeFiles();
		});
	} else {
		console.log('[Project Gallery] FilePond not inited:', {
			hasInput: !!filepondInput,
			hasFilePond: typeof FilePond !== 'undefined',
			postId: settings.postId
		});
	}
})();
