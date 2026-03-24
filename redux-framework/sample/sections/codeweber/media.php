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
	<h3 style="margin-top: 0;">' . esc_html__( 'Регенерация миниатюр', 'codeweber' ) . '</h3>
	<p class="description">' . esc_html__( 'Перегенерирует все размеры изображений для файлов в медиатеке. Нужно запускать после изменения зарегистрированных размеров.', 'codeweber' ) . '</p>
	<div style="margin: 15px 0;">
		<button id="cw-regen-start" class="button button-primary">'
				. esc_html__( 'Регенерировать миниатюры', 'codeweber' ) .
				'</button>
	</div>
	<div id="cw-regen-progress" style="display:none; margin-top: 15px; max-width: 500px;">
		<div style="background: #ddd; border-radius: 3px; height: 18px; overflow: hidden; margin-bottom: 8px;">
			<div id="cw-regen-bar" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s;"></div>
		</div>
		<p id="cw-regen-label" style="margin: 0; font-size: 13px; color: #555;"></p>
	</div>
	<div id="cw-regen-status" class="notice inline" style="margin-top: 12px; display: none;"></div>
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

	var nonce     = "' . wp_create_nonce( 'cw_media_regen' ) . '";
	var total     = 0;
	var batchSize = 10;
	var allLost   = [];

	function setProgress(done, outOf) {
		var pct = outOf > 0 ? Math.round(done / outOf * 100) : 0;
		$("#cw-regen-bar").css("width", pct + "%");
		$("#cw-regen-label").text(
			"' . esc_js( __( 'Обработано', 'codeweber' ) ) . ' " + done + " ' . esc_js( __( 'из', 'codeweber' ) ) . ' " + outOf + " (" + pct + "%)"
		);
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

	function showStatus(msg, type) {
		var $s = $("#cw-regen-status");
		$s.removeClass("notice-success notice-error notice-info notice-warning");
		$s.addClass("notice-" + type).html("<p>" + msg + "</p>").show();
	}

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
					$("#cw-regen-start").prop("disabled", false);
					return;
				}
				var next = r.data.next_offset;
				setProgress(Math.min(next, total), total);

				if (r.data.lost && r.data.lost.length > 0) {
					allLost = allLost.concat(r.data.lost);
				}

				if (r.data.done) {
					$("#cw-regen-start").prop("disabled", false);
					var msg = "' . esc_js( __( 'Готово! Все миниатюры успешно регенерированы.', 'codeweber' ) ) . '";
					if (allLost.length > 0) {
						msg += " ' . esc_js( __( 'Потерянных файлов:', 'codeweber' ) ) . ' " + allLost.length + ".";
					}
					showStatus(msg, allLost.length > 0 ? "warning" : "success");
					renderLostReport();
				} else {
					runBatch(next);
				}
			},
			error: function() {
				showStatus("' . esc_js( __( 'Ошибка AJAX-запроса. Попробуйте ещё раз.', 'codeweber' ) ) . '", "error");
				$("#cw-regen-start").prop("disabled", false);
			}
		});
	}

	$("#cw-regen-start").on("click", function(e) {
		e.preventDefault();
		if (!confirm("' . esc_js( __( 'Регенерировать все миниатюры? Это может занять некоторое время.', 'codeweber' ) ) . '")) {
			return;
		}

		$(this).prop("disabled", true);
		allLost = [];
		$("#cw-regen-status").hide();
		$("#cw-regen-progress").hide();
		$("#cw-regen-lost").hide();
		showStatus("' . esc_js( __( 'Подсчёт изображений...', 'codeweber' ) ) . '", "info");

		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: { action: "cw_media_regen_count", nonce: nonce },
			success: function(r) {
				if (!r.success) {
					showStatus(r.data.message, "error");
					$("#cw-regen-start").prop("disabled", false);
					return;
				}
				total = r.data.total;
				if (total === 0) {
					showStatus("' . esc_js( __( 'Изображений не найдено.', 'codeweber' ) ) . '", "info");
					$("#cw-regen-start").prop("disabled", false);
					return;
				}
				$("#cw-regen-progress").show();
				setProgress(0, total);
				showStatus("' . esc_js( __( 'Обработка...', 'codeweber' ) ) . '", "info");
				runBatch(0);
			},
			error: function() {
				showStatus("' . esc_js( __( 'Ошибка AJAX-запроса. Попробуйте ещё раз.', 'codeweber' ) ) . '", "error");
				$("#cw-regen-start").prop("disabled", false);
			}
		});
	});
})(jQuery);
</script>',
			],
		],
	]
);
