(function ($) {
	'use strict';

	$(document).on('click', '#cws3-test-connection', function () {
		var $btn = $(this);
		var $res = $('#cws3-test-result');
		$btn.prop('disabled', true);
		$res.removeClass('ok err').text(cws3.i18n.testing);

		$.post(cws3.ajaxUrl, {
			action: 'cws3_test_connection',
			nonce: cws3.nonce
		}).done(function (resp) {
			if (resp.success) {
				$res.addClass('ok').text(cws3.i18n.test_ok + ' ' + (resp.data && resp.data.message ? resp.data.message : ''));
			} else {
				$res.addClass('err').text(cws3.i18n.test_fail + (resp.data && resp.data.message ? resp.data.message : 'Unknown error'));
			}
		}).fail(function (xhr) {
			$res.addClass('err').text(cws3.i18n.test_fail + xhr.statusText);
		}).always(function () {
			$btn.prop('disabled', false);
		});
	});

	$(document).on('click', '#cws3-clear-errors', function () {
		if (!window.confirm(cws3.i18n.confirm_clear)) { return; }
		$.post(cws3.ajaxUrl, { action: 'cws3_clear_errors', nonce: cws3.nonce }).done(function () { window.location.reload(); });
	});

	$(document).on('click', '#cws3-clear-logs', function () {
		if (!window.confirm(cws3.i18n.confirm_clear)) { return; }
		$.post(cws3.ajaxUrl, { action: 'cws3_clear_logs', nonce: cws3.nonce }).done(function () { window.location.reload(); });
	});

	// ---------- Tools: batch jobs ----------
	var pollers = {};

	function startJob($section, dryRun) {
		var type = $section.data('job-type');
		$.post(cws3.ajaxUrl, {
			action: 'cws3_start_job',
			nonce: cws3.nonce,
			type: type,
			dry_run: dryRun ? 1 : 0
		}).done(function (resp) {
			if (!resp.success) {
				alert((resp.data && resp.data.message) || cws3.i18n.failed_to_start);
				return;
			}
			var jobId = resp.data.job_id;
			$section.data('job-id', jobId);
			$section.find('.cws3-progress').show();
			toggleButtons($section, 'running');
			poll($section, jobId);
		});
	}

	function toggleButtons($section, state) {
		$section.find('.cws3-start').prop('disabled', state === 'running' || state === 'paused');
		$section.find('.cws3-pause').prop('disabled', state !== 'running');
		$section.find('.cws3-resume').prop('disabled', state !== 'paused');
		$section.find('.cws3-cancel').prop('disabled', state === 'idle');
	}

	function poll($section, jobId) {
		stopPoll($section);
		pollers[jobId] = setInterval(function () {
			$.get(cws3.ajaxUrl, { action: 'cws3_job_status', nonce: cws3.nonce, job_id: jobId }).done(function (resp) {
				if (!resp.success) { return; }
				renderStatus($section, resp.data);
				if (['completed', 'cancelled', 'failed'].indexOf(resp.data.status) !== -1) {
					stopPoll($section);
					toggleButtons($section, 'idle');
				}
				if (resp.data.status === 'paused') { toggleButtons($section, 'paused'); }
			});
		}, 2000);
	}

	function stopPoll($section) {
		var jobId = $section.data('job-id');
		if (jobId && pollers[jobId]) { clearInterval(pollers[jobId]); delete pollers[jobId]; }
	}

	function renderStatus($section, data) {
		var pct = data.total > 0 ? Math.min(100, Math.round((data.processed / data.total) * 100)) : 0;
		$section.find('.cws3-progress-fill').css('width', pct + '%');
		var statusLabel = cws3.i18n[data.status] || data.status;
		var text = statusLabel + ': ' + data.processed + ' / ' + data.total
			+ (data.failed ? ' (' + data.failed + ' ' + cws3.i18n.failed_count + ')' : '')
			+ (data.dry_run ? ' ' + cws3.i18n.dry_run_label : '');
		if (data.error) { text += ' — ' + data.error; }
		$section.find('.cws3-progress-text').text(text);
	}

	function control($section, action) {
		var jobId = $section.data('job-id');
		if (!jobId) { return; }
		$.post(cws3.ajaxUrl, { action: 'cws3_control_job', nonce: cws3.nonce, job_id: jobId, control: action }).done(function () {
			if (action === 'cancel') { stopPoll($section); toggleButtons($section, 'idle'); }
			if (action === 'resume') { poll($section, jobId); }
		});
	}

	$(document).on('click', '.cws3-section .cws3-start', function () {
		var $section = $(this).closest('.cws3-section');
		var dryRun   = $(this).data('dry-run') === 1;
		if (!dryRun && $section.data('confirm') === 1) {
			if (!window.confirm(cws3.i18n.confirm_wipe)) {
				return;
			}
		}
		startJob($section, dryRun);
	});
	$(document).on('click', '.cws3-section .cws3-pause', function () { control($(this).closest('.cws3-section'), 'pause'); });
	$(document).on('click', '.cws3-section .cws3-resume', function () { control($(this).closest('.cws3-section'), 'resume'); });
	$(document).on('click', '.cws3-section .cws3-cancel', function () {
		if (!window.confirm(cws3.i18n.confirm_cancel)) { return; }
		control($(this).closest('.cws3-section'), 'cancel');
	});

	// ---------- Attachment metabox + row actions ----------
	function runAttachmentAction(attachmentId, op, $result, $badgeTarget) {
		$result.removeClass('ok err').text(cws3.i18n.working);
		$.post(cws3.ajaxUrl, { action: 'cws3_attachment_action', nonce: cws3.nonce, attachment_id: attachmentId, op: op })
			.done(function (resp) {
				if (resp.success) {
					$result.addClass('ok').text(cws3.i18n.done);
					if (resp.data && resp.data.badge && $badgeTarget && $badgeTarget.length) {
						$badgeTarget.html(resp.data.badge);
					}
				} else {
					$result.addClass('err').text(cws3.i18n.error + ': ' + ((resp.data && resp.data.message) || ''));
				}
			}).fail(function (xhr) {
				$result.addClass('err').text(cws3.i18n.error + ': ' + xhr.statusText);
			});
	}

	$(document).on('click', '.cws3-metabox-action', function () {
		var $btn = $(this);
		var $box = $btn.closest('.cws3-metabox');
		var $res = $box.find('.cws3-metabox-result');
		runAttachmentAction($box.data('attachment-id'), $btn.data('op'), $res, null);
	});

	$(document).on('click', '.cws3-row-action', function (e) {
		e.preventDefault();
		var $link   = $(this);
		var id      = $link.data('id');
		var op      = $link.data('action');
		var $cell   = $link.closest('tr').find('td.column-cws3_storage');
		var $result = $link.next('.cws3-row-result');
		if (!$result.length) {
			$result = $('<span class="cws3-row-result" style="margin-left:6px;"></span>');
			$link.after($result);
		}
		runAttachmentAction(id, op, $result, $cell);
	});
})(jQuery);
