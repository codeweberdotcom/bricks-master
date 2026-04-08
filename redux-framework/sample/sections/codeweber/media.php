<?php
/**
 * Redux Framework — Раздел инструментов медиафайлов
 *
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

Redux::set_section(
	$opt_name,
	[
		'title'  => esc_html__( 'Медиа', 'codeweber' ),
		'id'     => 'media-tools',
		'desc'   => esc_html__( 'Инструменты для работы с медиафайлами', 'codeweber' ),
		'icon'   => 'el el-picture',
		'fields' => [
			[
				'id'      => 'media-regenerate-thumbnails',
				'type'    => 'raw',
				'content' => '
<div class="cw-media-regen" style="margin: 20px 0;">
	<h3 style="margin-top: 0;">
		' . esc_html__( 'Регенерация миниатюр', 'codeweber' ) . '
		<span class="dashicons dashicons-info" style="font-size:18px;vertical-align:middle;margin-left:6px;cursor:help;color:#72777c;"
		      title="' . esc_attr__( "Что делает регенерация:\n• Перегенерирует все зарегистрированные размеры изображений для каждого файла в медиатеке.\n• Оригинальный файл НЕ удаляется и НЕ изменяется.\n• Размеры генерируются с учётом типа записи (CPT): для товаров WooCommerce — одни размеры, для событий — другие.\n• Старые миниатюры перезаписываются новыми.\n• Файлы без родительской записи генерируют все размеры без ограничений.\n• Потерянные вложения (файл удалён с диска) — не регенерируются, выводятся в отдельном списке.", 'codeweber' ) . '"></span>
	</h3>
	<p class="description">' . esc_html__( 'Перегенерирует все размеры изображений для файлов в медиатеке. Нужно запускать после изменения зарегистрированных размеров.', 'codeweber' ) . '</p>
	<div style="margin: 15px 0; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
		<button id="cw-regen-start" class="button button-primary">'
				. esc_html__( 'Регенерировать миниатюры', 'codeweber' ) .
				'</button>
		<button id="cw-regen-resume" class="button button-primary" style="display:none;">'
				. esc_html__( 'Продолжить', 'codeweber' ) .
				'</button>
		<button id="cw-regen-restart" class="button button-secondary" style="display:none;">'
				. esc_html__( 'Начать сначала', 'codeweber' ) .
				'</button>
	</div>
	<div id="cw-regen-progress" style="display:none; margin-top: 15px; max-width: 500px;">
		<div style="background: #ddd; border-radius: 3px; height: 18px; overflow: hidden; margin-bottom: 8px;">
			<div id="cw-regen-bar" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s;"></div>
		</div>
		<p id="cw-regen-label" style="margin: 0; font-size: 13px; color: #555;"></p>
	</div>
	<div id="cw-regen-status" class="notice inline" style="margin-top: 12px; display: none;"></div>
	<div id="cw-regen-log-wrap" style="display:none; margin-top: 20px;">
		<div style="display:flex; align-items:center; gap:10px; margin-bottom:8px; flex-wrap:wrap;">
			<h4 style="margin:0;">' . esc_html__( 'Журнал обработки', 'codeweber' ) . ' <span id="cw-log-count" style="font-weight:normal; color:#72777c;"></span></h4>
			<input type="search" id="cw-log-search" placeholder="' . esc_attr__( 'Поиск по имени файла...', 'codeweber' ) . '" style="flex:1; max-width:300px; padding:4px 8px; font-size:13px; border:1px solid #ddd; border-radius:3px;">
		</div>
		<div id="cw-regen-log-list" style="max-height:300px; overflow-y:auto; border:1px solid #ddd; border-radius:3px; background:#fafafa; font-family:monospace; font-size:12px; padding:0;"></div>
	</div>

	<div id="cw-regen-lost" style="display:none; margin-top: 20px;">
		<h4 style="color:#b32d2e; margin-bottom: 8px;">' . esc_html__( 'Потерянные файлы', 'codeweber' ) . ' <span id="cw-lost-count"></span></h4>
		<p class="description" style="margin-bottom: 10px;">' . esc_html__( 'Записи в базе данных есть, но файлы на диске отсутствуют.', 'codeweber' ) . '</p>
		<table class="widefat striped" style="max-width: 900px;">
			<thead>
				<tr>
					<th style="width:40px;">#</th>
					<th>' . esc_html__( 'Файл', 'codeweber' ) . '</th>
					<th>' . esc_html__( 'Запись', 'codeweber' ) . '</th>
					<th style="width:140px;">' . esc_html__( 'Действия', 'codeweber' ) . '</th>
				</tr>
			</thead>
			<tbody id="cw-regen-lost-tbody"></tbody>
		</table>
		<div style="margin-top: 10px;">
			<button id="cw-delete-all-lost" class="button button-secondary" style="color:#b32d2e; border-color:#b32d2e;">
				' . esc_html__( 'Удалить все потерянные из БД', 'codeweber' ) . '
			</button>
			<span id="cw-delete-status" style="margin-left: 10px; font-size: 13px;"></span>
		</div>
	</div>
</div>
<script>
(function($) {
	"use strict";

	var nonce      = "' . wp_create_nonce( 'cw_media_regen' ) . '";
	var total      = 0;
	var batchSize  = 10;
	var allLost    = [];
	var allLog     = [];
	var running    = false;
	var SS_KEY     = "cw_media_regen_state";

	// ── sessionStorage helpers ──────────────────────────────────────────
	function saveState(offset, done) {
		try {
			sessionStorage.setItem(SS_KEY, JSON.stringify({
				offset:  offset,
				total:   total,
				allLost: allLost,
				done:    done || false
			}));
		} catch(e) {}
	}

	function loadState() {
		try {
			var raw = sessionStorage.getItem(SS_KEY);
			return raw ? JSON.parse(raw) : null;
		} catch(e) { return null; }
	}

	function clearState() {
		try { sessionStorage.removeItem(SS_KEY); } catch(e) {}
	}

	// ── UI helpers ──────────────────────────────────────────────────────
	function setProgress(done, outOf) {
		var pct = outOf > 0 ? Math.round(done / outOf * 100) : 0;
		$("#cw-regen-bar").css("width", pct + "%");
		$("#cw-regen-label").text(
			"' . esc_js( __( 'Обработано', 'codeweber' ) ) . ' " + done + " ' . esc_js( __( 'из', 'codeweber' ) ) . ' " + outOf + " (" + pct + "%)"
		);
	}

	function showStatus(msg, type) {
		var $s = $("#cw-regen-status");
		$s.removeClass("notice-success notice-error notice-info notice-warning");
		$s.addClass("notice-" + type).html("<p>" + msg + "</p>").show();
	}

	function setRunning(isRunning) {
		running = isRunning;
		$("#cw-regen-start").prop("disabled", isRunning);
		$("#cw-regen-resume").prop("disabled", isRunning);
	}

	// ── Log helpers ─────────────────────────────────────────────────────
	function renderLogEntry(item) {
		var icon  = item.ok ? "✓" : (item.error ? "✗" : "⚠");
		var color = item.ok ? "#2e7d32" : "#b32d2e";
		var sizes = item.sizes && item.sizes.length
			? item.sizes.join(", ")
			: "' . esc_js( __( 'нет размеров', 'codeweber' ) ) . '";
		var detail = item.ok
			? "<span style=\"color:#72777c\"> → " + sizes + "</span>"
			: "<span style=\"color:#b32d2e\"> — " + (item.error || "' . esc_js( __( 'файл не найден', 'codeweber' ) ) . '") + "</span>";
		var cpt = item.parent_type && item.parent_type !== "default"
			? " <span style=\"background:#e8f0fe;color:#1a56db;border-radius:2px;padding:0 4px;font-size:11px;\">" + item.parent_type + "</span>"
			: "";
		return "<div class=\"cw-log-row\" data-filename=\"" + item.filename + "\" style=\"padding:4px 10px; border-bottom:1px solid #eee; display:flex; gap:6px; align-items:baseline;\">" +
			"<span style=\"color:" + color + "; flex-shrink:0;\">" + icon + "</span>" +
			"<span style=\"color:#888; flex-shrink:0;\">[" + item.ext + "]</span>" +
			"<span style=\"flex-shrink:0; font-weight:500;\">" + item.filename + "</span>" +
			cpt + detail +
			"</div>";
	}

	function appendLog(entries) {
		if (!entries || !entries.length) return;
		allLog = allLog.concat(entries);
		var $list = $("#cw-regen-log-list");
		var html = "";
		for (var i = entries.length - 1; i >= 0; i--) {
			html += renderLogEntry(entries[i]);
		}
		$list.prepend(html);
		$("#cw-log-count").text("(" + allLog.length + ")");
		$("#cw-regen-log-wrap").show();
	}

	function renderFullLog(entries) {
		allLog = entries || [];
		var $list = $("#cw-regen-log-list").empty();
		var html = "";
		for (var i = allLog.length - 1; i >= 0; i--) {
			html += renderLogEntry(allLog[i]);
		}
		$list.html(html);
		$("#cw-log-count").text("(" + allLog.length + ")");
		if (allLog.length > 0) {
			$("#cw-regen-log-wrap").show();
		}
	}

	// Поиск по имени файла
	$("#cw-log-search").on("input", function() {
		var q = $(this).val().toLowerCase().trim();
		$("#cw-regen-log-list .cw-log-row").each(function() {
			var fn = ($(this).data("filename") || "").toLowerCase();
			$(this).toggle(!q || fn.indexOf(q) !== -1);
		});
	});

	// Загрузить лог с сервера при открытии страницы
	function fetchStoredLog() {
		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: { action: "cw_media_regen_get_log", nonce: nonce },
			success: function(r) {
				if (r.success && r.data.log && r.data.log.length) {
					renderFullLog(r.data.log);
				}
			}
		});
	}

	function updateLostCount() {
		var remaining = $("#cw-regen-lost-tbody tr").length;
		if (remaining > 0) {
			$("#cw-lost-count").text("(" + remaining + ")");
		} else {
			$("#cw-regen-lost").hide();
		}
	}

	function renderLostReport() {
		if (allLost.length === 0) return;
		var $tbody = $("#cw-regen-lost-tbody").empty();
		$.each(allLost, function(i, item) {
			var postLink = item.parent_url
				? "<a href=\"" + item.parent_url + "\" target=\"_blank\">" + $("<span>").text(item.parent_title).html() + "</a>"
				: $("<span>").text(item.parent_title).html();
			var editLink = item.edit_url
				? "<a href=\"" + item.edit_url + "\" target=\"_blank\">' . esc_js( __( 'Изменить', 'codeweber' ) ) . '</a> "
				: "";
			var delBtn = "<button class=\"button button-small cw-del-one\" data-id=\"" + item.attachment_id + "\" style=\"color:#b32d2e;\">' . esc_js( __( 'Удалить', 'codeweber' ) ) . '</button>";
			$tbody.append(
				"<tr id=\"cw-lost-row-" + item.attachment_id + "\">" +
				"<td>" + (i + 1) + "</td>" +
				"<td><code>" + $("<span>").text(item.filename).html() + "</code></td>" +
				"<td>" + postLink + "</td>" +
				"<td>" + editLink + delBtn + "</td>" +
				"</tr>"
			);
		});
		updateLostCount();
		$("#cw-regen-lost").show();
	}

	function onFinished() {
		setRunning(false);
		saveState(total, true);
		$("#cw-regen-resume").hide();
		$("#cw-regen-restart").show();
		var msg = "' . esc_js( __( 'Готово! Все миниатюры успешно регенерированы.', 'codeweber' ) ) . '";
		if (allLost.length > 0) {
			msg += " ' . esc_js( __( 'Потерянных файлов:', 'codeweber' ) ) . ' " + allLost.length + ".";
		}
		showStatus(msg, allLost.length > 0 ? "warning" : "success");
		renderLostReport();
	}

	// ── AJAX ────────────────────────────────────────────────────────────
	function runBatch(offset) {
		$.ajax({
			url: ajaxurl,
			type: "POST",
			timeout: 180000,
			data: {
				action: "cw_media_regen_batch",
				nonce:  nonce,
				offset: offset,
				limit:  batchSize,
				total:  total
			},
			success: function(r) {
				if (!r.success) {
					showStatus(r.data.message, "error");
					saveState(offset, false);
					setRunning(false);
					$("#cw-regen-resume").show();
					$("#cw-regen-restart").show();
					return;
				}
				var next = r.data.next_offset;
				setProgress(Math.min(next, total), total);

				if (r.data.lost && r.data.lost.length > 0) {
					allLost = allLost.concat(r.data.lost);
				}

				if (r.data.log && r.data.log.length > 0) {
					appendLog(r.data.log);
				}

				if (r.data.done) {
					onFinished();
				} else {
					saveState(next, false);
					runBatch(next);
				}
			},
			error: function() {
				showStatus("' . esc_js( __( 'Ошибка AJAX-запроса. Попробуйте ещё раз.', 'codeweber' ) ) . '", "error");
				saveState(offset, false);
				setRunning(false);
				$("#cw-regen-resume").show();
				$("#cw-regen-restart").show();
			}
		});
	}

	function startFromOffset(offset) {
		setRunning(true);
		$("#cw-regen-resume").hide();
		$("#cw-regen-restart").show();
		$("#cw-regen-progress").show();
		setProgress(offset, total);
		showStatus("' . esc_js( __( 'Обработка...', 'codeweber' ) ) . '", "info");
		runBatch(offset);
	}

	function fetchTotalAndStart(offset) {
		showStatus("' . esc_js( __( 'Подсчёт изображений...', 'codeweber' ) ) . '", "info");
		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: { action: "cw_media_regen_count", nonce: nonce },
			success: function(r) {
				if (!r.success) {
					showStatus(r.data.message, "error");
					setRunning(false);
					return;
				}
				total = r.data.total;
				if (total === 0) {
					showStatus("' . esc_js( __( 'Изображений не найдено.', 'codeweber' ) ) . '", "info");
					setRunning(false);
					return;
				}
				startFromOffset(offset);
			},
			error: function() {
				showStatus("' . esc_js( __( 'Ошибка AJAX-запроса. Попробуйте ещё раз.', 'codeweber' ) ) . '", "error");
				setRunning(false);
			}
		});
	}

	// ── Кнопки ─────────────────────────────────────────────────────────
	$("#cw-regen-start").on("click", function(e) {
		e.preventDefault();
		if (!confirm("' . esc_js( __( 'Регенерировать все миниатюры? Это может занять некоторое время.', 'codeweber' ) ) . '")) return;
		clearState();
		allLost = [];
		total   = 0;
		$("#cw-regen-status").hide();
		$("#cw-regen-lost").hide();
		fetchTotalAndStart(0);
	});

	$("#cw-regen-resume").on("click", function(e) {
		e.preventDefault();
		var state = loadState();
		if (!state) return;
		allLost = state.allLost || [];
		total   = state.total  || 0;
		if (total > 0) {
			renderLostReport();
			startFromOffset(state.offset);
		} else {
			fetchTotalAndStart(0);
		}
	});

	$("#cw-regen-restart").on("click", function(e) {
		e.preventDefault();
		if (!confirm("' . esc_js( __( 'Начать регенерацию сначала? Весь прогресс будет сброшен.', 'codeweber' ) ) . '")) return;
		clearState();
		allLost = [];
		allLog  = [];
		total   = 0;
		$("#cw-regen-status").hide();
		$("#cw-regen-progress").hide();
		$("#cw-regen-lost").hide();
		$("#cw-regen-lost-tbody").empty();
		$("#cw-regen-log-list").empty();
		$("#cw-regen-log-wrap").hide();
		$(this).hide();
		$("#cw-regen-resume").hide();
		fetchTotalAndStart(0);
	});

	// ── Удаление потерянных ─────────────────────────────────────────────
	function deleteLost(ids, $btn, onSuccess) {
		$btn.prop("disabled", true);
		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: { action: "cw_media_delete_lost", nonce: nonce, ids: ids },
			success: function(r) {
				$btn.prop("disabled", false);
				if (!r.success) {
					$("#cw-delete-status").text(r.data.message).css("color", "#b32d2e");
					return;
				}
				onSuccess(r.data.deleted);
				$("#cw-delete-status").text("' . esc_js( __( 'Удалено:', 'codeweber' ) ) . ' " + r.data.deleted).css("color", "#2e7d32");
			},
			error: function() {
				$btn.prop("disabled", false);
				$("#cw-delete-status").text("' . esc_js( __( 'Ошибка AJAX-запроса.', 'codeweber' ) ) . '").css("color", "#b32d2e");
			}
		});
	}

	$(document).on("click", ".cw-del-one", function() {
		var $btn = $(this);
		var id   = parseInt($btn.data("id"), 10);
		if (!confirm("' . esc_js( __( 'Удалить эту запись из базы данных?', 'codeweber' ) ) . '")) return;
		deleteLost([id], $btn, function() {
			$("#cw-lost-row-" + id).remove();
			allLost = allLost.filter(function(item) { return item.attachment_id !== id; });
			updateLostCount();
		});
	});

	$("#cw-delete-all-lost").on("click", function() {
		if (!confirm("' . esc_js( __( 'Удалить все потерянные записи из базы данных? Это действие необратимо.', 'codeweber' ) ) . '")) return;
		var ids = allLost.map(function(item) { return item.attachment_id; });
		deleteLost(ids, $(this), function() {
			$("#cw-regen-lost-tbody tr").remove();
			allLost = [];
			updateLostCount();
		});
	});

	// ── Восстановление состояния при загрузке страницы ──────────────────
	$(function() {
		fetchStoredLog();
		var state = loadState();
		if (!state) return;
		total   = state.total  || 0;
		allLost = state.allLost || [];
		if (state.done) {
			// Процесс завершён — показать статистику
			$("#cw-regen-progress").show();
			setProgress(total, total);
			var msg = "' . esc_js( __( 'Готово! Все миниатюры успешно регенерированы.', 'codeweber' ) ) . '";
			if (allLost.length > 0) {
				msg += " ' . esc_js( __( 'Потерянных файлов:', 'codeweber' ) ) . ' " + allLost.length + ".";
			}
			showStatus(msg, allLost.length > 0 ? "warning" : "success");
			renderLostReport();
			$("#cw-regen-restart").show();
		} else if (state.offset > 0) {
			// Незавершённый процесс — предложить продолжить
			$("#cw-regen-progress").show();
			setProgress(state.offset, total);
			showStatus(
				"' . esc_js( __( 'Прерванная регенерация. Обработано:', 'codeweber' ) ) . ' " + state.offset + " ' . esc_js( __( 'из', 'codeweber' ) ) . ' " + total + ". ' . esc_js( __( 'Нажмите «Продолжить».', 'codeweber' ) ) . '",
				"warning"
			);
			renderLostReport();
			$("#cw-regen-resume").show();
			$("#cw-regen-restart").show();
		}
	});

})(jQuery);
</script>',
			],
		],
	]
);
