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
			array(
				'id'      => 'demo-faq-controls',
				'type'    => 'raw',
				'content' => '
					<div class="demo-controls" style="margin: 20px 0;">
						<h3>' . esc_html__('Demo FAQ', 'codeweber') . '</h3>
						<p class="description">' . esc_html__('Создайте demo записи для CPT FAQ с категориями', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-faq" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Создать Demo FAQ', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-faq" class="button button-secondary">
								' . esc_html__('Удалить Demo FAQ', 'codeweber') . '
							</button>
						</div>
						<div id="cw-demo-faq-status" class="demo-status" style="margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px; display: none;"></div>
					</div>
					<script>
					(function($) {
						"use strict";

						var createNonce = "' . wp_create_nonce('cw_demo_create_faq') . '";
						var deleteNonce = "' . wp_create_nonce('cw_demo_delete_faq') . '";

						function showStatus(message, type) {
							var $status = $("#cw-demo-faq-status");
							$status.removeClass("notice-success notice-error");
							$status.addClass("notice-" + (type || "info"));
							$status.html("<p>" + message + "</p>").show();
						}

						function setButtonsState(disabled) {
							$("#cw-demo-create-faq, #cw-demo-delete-faq").prop("disabled", disabled);
						}

						$("#cw-demo-create-faq").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Создать demo записи FAQ? Это может занять некоторое время.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Создание записей...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_create_faq",
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

						$("#cw-demo-delete-faq").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Удалить все demo записи FAQ? Это действие нельзя отменить.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Удаление записей...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_delete_faq",
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
			array(
				'id'      => 'demo-testimonials-controls',
				'type'    => 'raw',
				'content' => '
					<div class="demo-controls" style="margin: 20px 0;">
						<h3>' . esc_html__('Demo Testimonials', 'codeweber') . '</h3>
						<p class="description">' . esc_html__('Создайте 15 demo записей для CPT Testimonials с аватарками из папки avatars', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-testimonials" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Создать Demo Testimonials', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-testimonials" class="button button-secondary">
								' . esc_html__('Удалить Demo Testimonials', 'codeweber') . '
							</button>
						</div>
						<div id="cw-demo-testimonials-status" class="demo-status" style="margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px; display: none;"></div>
					</div>
					<script>
					(function($) {
						"use strict";

						var createNonce = "' . wp_create_nonce('cw_demo_create_testimonials') . '";
						var deleteNonce = "' . wp_create_nonce('cw_demo_delete_testimonials') . '";

						function showStatus(message, type) {
							var $status = $("#cw-demo-testimonials-status");
							$status.removeClass("notice-success notice-error");
							$status.addClass("notice-" + (type || "info"));
							$status.html("<p>" + message + "</p>").show();
						}

						function setButtonsState(disabled) {
							$("#cw-demo-create-testimonials, #cw-demo-delete-testimonials").prop("disabled", disabled);
						}

						$("#cw-demo-create-testimonials").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Создать 15 demo записей testimonials? Это может занять некоторое время.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Создание записей...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_create_testimonials",
									nonce: createNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . ':</strong><ul>";
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

						$("#cw-demo-delete-testimonials").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Удалить все demo записи testimonials? Это действие нельзя отменить.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Удаление записей...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_delete_testimonials",
									nonce: deleteNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . ':</strong><ul>";
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
			array(
				'id'      => 'demo-staff-controls',
				'type'    => 'raw',
				'content' => '
					<div class="demo-controls" style="margin: 20px 0;">
						<h3>' . esc_html__('Demo Staff', 'codeweber') . '</h3>
						<p class="description">' . esc_html__('Создайте demo записи для CPT Staff с изображениями из папки avatars и отделами', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-staff" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Создать Demo Staff', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-staff" class="button button-secondary">
								' . esc_html__('Удалить Demo Staff', 'codeweber') . '
							</button>
						</div>
						<div id="cw-demo-staff-status" class="demo-status" style="margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px; display: none;"></div>
					</div>
					<script>
					(function($) {
						"use strict";

						var createNonce = "' . wp_create_nonce('cw_demo_create_staff') . '";
						var deleteNonce = "' . wp_create_nonce('cw_demo_delete_staff') . '";

						function showStatus(message, type) {
							var $status = $("#cw-demo-staff-status");
							$status.removeClass("notice-success notice-error");
							$status.addClass("notice-" + (type || "info"));
							$status.html("<p>" + message + "</p>").show();
						}

						function setButtonsState(disabled) {
							$("#cw-demo-create-staff, #cw-demo-delete-staff").prop("disabled", disabled);
						}

						$("#cw-demo-create-staff").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Создать demo записи staff? Это может занять некоторое время.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Создание записей...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_create_staff",
									nonce: createNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . ':</strong><ul>";
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

						$("#cw-demo-delete-staff").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Удалить все demo записи staff? Это действие нельзя отменить.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Удаление записей...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_delete_staff",
									nonce: deleteNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . ':</strong><ul>";
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
			array(
				'id'      => 'demo-vacancies-controls',
				'type'    => 'raw',
				'content' => '
					<div class="demo-controls" style="margin: 20px 0;">
						<h3>' . esc_html__('Demo Vacancies', 'codeweber') . '</h3>
						<p class="description">' . esc_html__('Создайте demo записи для CPT Vacancies с изображениями из папки photos', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-vacancies" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Создать Demo Vacancies', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-vacancies" class="button button-secondary">
								' . esc_html__('Удалить Demo Vacancies', 'codeweber') . '
							</button>
						</div>
						<div id="cw-demo-vacancies-status" class="demo-status" style="margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px; display: none;"></div>
					</div>
					<script>
					(function($) {
						"use strict";

						var createNonce = "' . wp_create_nonce('cw_demo_create_vacancies') . '";
						var deleteNonce = "' . wp_create_nonce('cw_demo_delete_vacancies') . '";

						function showStatus(message, type) {
							var $status = $("#cw-demo-vacancies-status");
							$status.removeClass("notice-success notice-error");
							$status.addClass("notice-" + (type || "info"));
							$status.html("<p>" + message + "</p>").show();
						}

						function setButtonsState(disabled) {
							$("#cw-demo-create-vacancies, #cw-demo-delete-vacancies").prop("disabled", disabled);
						}

						$("#cw-demo-create-vacancies").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Создать demo записи vacancies? Это может занять некоторое время.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Создание записей...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_create_vacancies",
									nonce: createNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . ':</strong><ul>";
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

						$("#cw-demo-delete-vacancies").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Удалить все demo записи vacancies? Это действие нельзя отменить.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Удаление записей...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_delete_vacancies",
									nonce: deleteNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . ':</strong><ul>";
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
			array(
				'id'      => 'demo-forms-controls',
				'type'    => 'raw',
				'content' => '
					<div class="demo-controls" style="margin: 20px 0;">
						<h3>' . esc_html__('Demo Forms', 'codeweber') . '</h3>
						<p class="description">' . esc_html__('Создайте demo формы (Testimonial и Newsletter) для CPT Forms', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-forms" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Создать Demo формы', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-forms" class="button button-secondary">
								' . esc_html__('Удалить Demo формы', 'codeweber') . '
							</button>
						</div>
						<div id="cw-demo-forms-status" class="demo-status" style="margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px; display: none;"></div>
					</div>
					<script>
					(function($) {
						"use strict";

						var createNonce = "' . wp_create_nonce('cw_demo_create_forms') . '";
						var deleteNonce = "' . wp_create_nonce('cw_demo_delete_forms') . '";

						function showStatus(message, type) {
							var $status = $("#cw-demo-forms-status");
							$status.removeClass("notice-success notice-error");
							$status.addClass("notice-" + (type || "info"));
							$status.html("<p>" + message + "</p>").show();
						}

						function setButtonsState(disabled) {
							$("#cw-demo-create-forms, #cw-demo-delete-forms").prop("disabled", disabled);
						}

						$("#cw-demo-create-forms").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Создать demo формы? Это может занять некоторое время.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Создание форм...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_create_forms",
									nonce: createNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . ':</strong><ul>";
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

						$("#cw-demo-delete-forms").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Удалить все demo формы? Это действие нельзя отменить.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Удаление форм...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_delete_forms",
									nonce: deleteNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . ':</strong><ul>";
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
			array(
				'id'      => 'demo-cf7-forms-controls',
				'type'    => 'raw',
				'content' => '
					<div class="demo-controls" style="margin: 20px 0;">
						<h3>' . esc_html__('CF7 Forms', 'codeweber') . '</h3>
						<p class="description">' . esc_html__('Создайте demo формы Contact Form 7: "Форма обратной связи" и "Заказать звонок"', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-cf7-forms" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Создать CF7 формы', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-cf7-forms" class="button button-secondary">
								' . esc_html__('Удалить CF7 формы', 'codeweber') . '
							</button>
						</div>
						<div id="cw-demo-cf7-forms-status" class="demo-status" style="margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px; display: none;"></div>
					</div>
					<script>
					(function($) {
						"use strict";

						var createNonce = "' . wp_create_nonce('cw_demo_create_cf7_forms') . '";
						var deleteNonce = "' . wp_create_nonce('cw_demo_delete_cf7_forms') . '";

						function showStatus(message, type) {
							var $status = $("#cw-demo-cf7-forms-status");
							$status.removeClass("notice-success notice-error");
							$status.addClass("notice-" + (type || "info"));
							$status.html("<p>" + message + "</p>").show();
						}

						function setButtonsState(disabled) {
							$("#cw-demo-create-cf7-forms, #cw-demo-delete-cf7-forms").prop("disabled", disabled);
						}

						$("#cw-demo-create-cf7-forms").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Создать demo формы CF7? Это может занять некоторое время.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Создание форм...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_create_cf7_forms",
									nonce: createNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . ':</strong><ul>";
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

						$("#cw-demo-delete-cf7-forms").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Удалить все demo формы CF7? Это действие нельзя отменить.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Удаление форм...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_delete_cf7_forms",
									nonce: deleteNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . ':</strong><ul>";
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
			array(
				'id'      => 'demo-offices-controls',
				'type'    => 'raw',
				'content' => '
					<div class="demo-controls" style="margin: 20px 0;">
						<h3>' . esc_html__('Demo Offices', 'codeweber') . '</h3>
						<p class="description">' . esc_html__('Создайте demo записи для CPT Offices с городами России и адресами Москвы с координатами для Яндекс карт', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-offices" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Создать Demo Offices', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-offices" class="button button-secondary">
								' . esc_html__('Удалить Demo Offices', 'codeweber') . '
							</button>
						</div>
						<div id="cw-demo-offices-status" class="demo-status" style="margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px; display: none;"></div>
					</div>
					<script>
					(function($) {
						"use strict";

						var createNonce = "' . wp_create_nonce('cw_demo_create_offices') . '";
						var deleteNonce = "' . wp_create_nonce('cw_demo_delete_offices') . '";

						function showStatus(message, type) {
							var $status = $("#cw-demo-offices-status");
							$status.removeClass("notice-success notice-error");
							$status.addClass("notice-" + (type || "info"));
							$status.html("<p>" + message + "</p>").show();
						}

						function setButtonsState(disabled) {
							$("#cw-demo-create-offices, #cw-demo-delete-offices").prop("disabled", disabled);
						}

						$("#cw-demo-create-offices").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Создать demo записи офисов? Это может занять некоторое время.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Создание записей...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_create_offices",
									nonce: createNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . ':</strong><ul>";
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

						$("#cw-demo-delete-offices").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Удалить все demo записи офисов? Это действие нельзя отменить.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Удаление записей...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_delete_offices",
									nonce: deleteNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . ':</strong><ul>";
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
			array(
				'id'      => 'demo-footer-controls',
				'type'    => 'raw',
				'content' => '
					<div class="demo-controls" style="margin: 20px 0;">
						<h3>' . esc_html__('Demo Footer', 'codeweber') . '</h3>
						<p class="description">' . esc_html__('Создайте demo записи для CPT Footer: Footer_01 (футер с 4 колонками), Footer_02 (CTA + футер с 4 колонками), Footer_03 (CTA card + футер с 4 колонками, Redux Demo)', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-footers" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Создать Demo Footer', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-footers" class="button button-secondary">
								' . esc_html__('Удалить Demo Footer', 'codeweber') . '
							</button>
						</div>
						<div id="cw-demo-footers-status" class="demo-status" style="margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px; display: none;"></div>
					</div>
					<script>
					(function($) {
						"use strict";

						var createNonce = "' . wp_create_nonce('cw_demo_create_footers') . '";
						var deleteNonce = "' . wp_create_nonce('cw_demo_delete_footers') . '";

						function showStatus(message, type) {
							var $status = $("#cw-demo-footers-status");
							$status.removeClass("notice-success notice-error");
							$status.addClass("notice-" + (type || "info"));
							$status.html("<p>" + message + "</p>").show();
						}

						function setButtonsState(disabled) {
							$("#cw-demo-create-footers, #cw-demo-delete-footers").prop("disabled", disabled);
						}

						$("#cw-demo-create-footers").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Создать demo футеры (Footer_01, Footer_02, Footer_03)? Это может занять некоторое время.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Создание записей...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_create_footers",
									nonce: createNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . '</strong><ul>";
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

						$("#cw-demo-delete-footers").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Удалить все demo футеры? Это действие нельзя отменить.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Удаление записей...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_delete_footers",
									nonce: deleteNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Ошибки:', 'codeweber')) . ':</strong><ul>";
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

