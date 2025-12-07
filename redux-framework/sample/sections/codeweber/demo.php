<?php
/**
 * Redux Framework Demo Section
 * 
 * @package Codeweber
 */

defined( 'ABSPATH' ) || exit;

Redux::set_section(
	$opt_name,
	array(
		'title'            => esc_html__("Demo", "codeweber"),
		'id'               => 'demo',
		'desc'             => esc_html__("Demo section for testing", "codeweber"),
		'customizer_width' => '300px',
		'icon'             => 'el el-star',
		'fields'           => array(
			array(
				'id'      => 'demo-clients-controls',
				'type'    => 'raw',
				'content' => '
					<div class="demo-controls" style="margin: 20px 0;">
						<h3>' . esc_html__('Demo Clients', 'codeweber') . '</h3>
						<p class="description">' . esc_html__('Создайте demo записи для CPT Clients с изображениями из папки brands', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-clients" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Создать Demo Clients', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-clients" class="button button-secondary">
								' . esc_html__('Удалить Demo Clients', 'codeweber') . '
							</button>
						</div>
						<div id="cw-demo-status" class="demo-status" style="margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px; display: none;"></div>
					</div>
					<script>
					(function($) {
						"use strict";
						
						var createNonce = "' . wp_create_nonce('cw_demo_create_clients') . '";
						var deleteNonce = "' . wp_create_nonce('cw_demo_delete_clients') . '";
						
						function showStatus(message, type) {
							var $status = $("#cw-demo-status");
							$status.removeClass("notice-success notice-error");
							$status.addClass("notice-" + (type || "info"));
							$status.html("<p>" + message + "</p>").show();
						}
						
						function setButtonsState(disabled) {
							$("#cw-demo-create-clients, #cw-demo-delete-clients").prop("disabled", disabled);
						}
						
						$("#cw-demo-create-clients").on("click", function(e) {
							e.preventDefault();
							
							if (!confirm("' . esc_js(__('Создать demo записи клиентов? Это может занять некоторое время.', 'codeweber')) . '")) {
								return;
							}
							
							setButtonsState(true);
							showStatus("' . esc_js(__('Создание записей...', 'codeweber')) . '", "info");
							
							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_create_clients",
									nonce: createNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>Ошибки:</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('Произошла ошибка', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('Ошибка AJAX запроса', 'codeweber')) . '", "error");
								}
							});
						});
						
						$("#cw-demo-delete-clients").on("click", function(e) {
							e.preventDefault();
							
							if (!confirm("' . esc_js(__('Удалить все demo записи клиентов? Это действие нельзя отменить.', 'codeweber')) . '")) {
								return;
							}
							
							setButtonsState(true);
							showStatus("' . esc_js(__('Удаление записей...', 'codeweber')) . '", "info");
							
							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_delete_clients",
									nonce: deleteNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>Ошибки:</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('Произошла ошибка', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('Ошибка AJAX запроса', 'codeweber')) . '", "error");
								}
							});
						});
					})(jQuery);
					</script>
				',
			),
		),
	)
);

