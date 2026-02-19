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
						<p class="description">' . esc_html__('Create demo entries for CPT Clients with images from the brands folder', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-clients" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Create Demo Clients', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-clients" class="button button-secondary">
								' . esc_html__('Delete Demo Clients', 'codeweber') . '
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

							if (!confirm("' . esc_js(__('Create demo client entries? This may take a while.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Creating entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>" + "' . esc_js(__('Errors:', 'codeweber')) . '" + "</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
								}
							});
						});

						$("#cw-demo-delete-clients").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Delete all demo client entries? This action cannot be undone.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Deleting entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>" + "' . esc_js(__('Errors:', 'codeweber')) . '" + "</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
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
						<p class="description">' . esc_html__('Create demo entries for CPT FAQ with categories', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-faq" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Create Demo FAQ', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-faq" class="button button-secondary">
								' . esc_html__('Delete Demo FAQ', 'codeweber') . '
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

							if (!confirm("' . esc_js(__('Create demo FAQ entries? This may take a while.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Creating entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>" + "' . esc_js(__('Errors:', 'codeweber')) . '" + "</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
								}
							});
						});

						$("#cw-demo-delete-faq").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Delete all demo FAQ entries? This action cannot be undone.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Deleting entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>" + "' . esc_js(__('Errors:', 'codeweber')) . '" + "</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
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
						<p class="description">' . esc_html__('Create 15 demo entries for CPT Testimonials with avatars from the avatars folder', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-testimonials" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Create Demo Testimonials', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-testimonials" class="button button-secondary">
								' . esc_html__('Delete Demo Testimonials', 'codeweber') . '
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

							if (!confirm("' . esc_js(__('Create 15 demo testimonial entries? This may take a while.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Creating entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
								}
							});
						});

						$("#cw-demo-delete-testimonials").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Delete all demo testimonial entries? This action cannot be undone.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Deleting entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
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
						<p class="description">' . esc_html__('Create demo entries for CPT Staff with images from the avatars folder and departments', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-staff" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Create Demo Staff', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-staff" class="button button-secondary">
								' . esc_html__('Delete Demo Staff', 'codeweber') . '
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

							if (!confirm("' . esc_js(__('Create demo staff entries? This may take a while.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Creating entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
								}
							});
						});

						$("#cw-demo-delete-staff").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Delete all demo staff entries? This action cannot be undone.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Deleting entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
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
						<p class="description">' . esc_html__('Create demo entries for CPT Vacancies with images from the photos folder', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-vacancies" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Create Demo Vacancies', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-vacancies" class="button button-secondary">
								' . esc_html__('Delete Demo Vacancies', 'codeweber') . '
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

							if (!confirm("' . esc_js(__('Create demo vacancy entries? This may take a while.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Creating entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
								}
							});
						});

						$("#cw-demo-delete-vacancies").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Delete all demo vacancy entries? This action cannot be undone.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Deleting entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
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
						<p class="description">' . esc_html__('Create demo forms (Testimonial and Newsletter) for CPT Forms', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-forms" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Create Demo Forms', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-forms" class="button button-secondary">
								' . esc_html__('Delete Demo Forms', 'codeweber') . '
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

							if (!confirm("' . esc_js(__('Create demo forms? This may take a while.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Creating forms...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
								}
							});
						});

						$("#cw-demo-delete-forms").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Delete all demo forms? This action cannot be undone.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Deleting forms...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
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
						<p class="description">' . esc_html__('Create demo Contact Form 7 forms: "Feedback form" and "Callback request"', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-cf7-forms" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Create CF7 Forms', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-cf7-forms" class="button button-secondary">
								' . esc_html__('Delete CF7 Forms', 'codeweber') . '
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

							if (!confirm("' . esc_js(__('Create demo CF7 forms? This may take a while.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Creating forms...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
								}
							});
						});

						$("#cw-demo-delete-cf7-forms").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Delete all demo CF7 forms? This action cannot be undone.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Deleting forms...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
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
						<p class="description">' . esc_html__('Create demo entries for CPT Offices with Russian cities and Moscow addresses with Yandex Maps coordinates', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-offices" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Create Demo Offices', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-offices" class="button button-secondary">
								' . esc_html__('Delete Demo Offices', 'codeweber') . '
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

							if (!confirm("' . esc_js(__('Create demo office entries? This may take a while.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Creating entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
								}
							});
						});

						$("#cw-demo-delete-offices").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Delete all demo office entries? This action cannot be undone.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Deleting entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
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
						<p class="description">' . esc_html__('Create demo entries for CPT Footer: Footer_01 (4-column footer), Footer_02 (CTA + 4-column footer), Footer_03 (CTA card + 4-column footer, Redux Demo)', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-footers" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Create Demo Footer', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-footers" class="button button-secondary">
								' . esc_html__('Delete Demo Footer', 'codeweber') . '
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

							if (!confirm("' . esc_js(__('Create demo footers (Footer_01, Footer_02, Footer_03)? This may take a while.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Creating entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . '</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
								}
							});
						});

						$("#cw-demo-delete-footers").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Delete all demo footers? This action cannot be undone.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Deleting entries...', 'codeweber')) . '", "info");

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
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
								}
							});
						});
					})(jQuery);
					</script>
				',
			),
			array(
				'id'      => 'demo-header-controls',
				'type'    => 'raw',
				'content' => '
					<div class="demo-controls" style="margin: 20px 0;">
						<h3>' . esc_html__('Demo Header', 'codeweber') . '</h3>
						<p class="description">' . esc_html__('Create demo entries for CPT Header: Header_01, Header_01 - Dark, Header_01 - Light, Header_02–08 (Navbar), Header_09 (Top Header + Navbar)', 'codeweber') . '</p>
						<div style="margin: 15px 0;">
							<button id="cw-demo-create-headers" class="button button-primary" style="margin-right: 10px;">
								' . esc_html__('Create Demo Header', 'codeweber') . '
							</button>
							<button id="cw-demo-delete-headers" class="button button-secondary">
								' . esc_html__('Delete Demo Header', 'codeweber') . '
							</button>
						</div>
						<div id="cw-demo-headers-status" class="demo-status" style="margin-top: 10px; padding: 10px; background: #f0f0f0; border-radius: 4px; display: none;"></div>
					</div>
					<script>
					(function($) {
						"use strict";

						var createNonce = "' . wp_create_nonce('cw_demo_create_headers') . '";
						var deleteNonce = "' . wp_create_nonce('cw_demo_delete_headers') . '";

						function showStatus(message, type) {
							var $status = $("#cw-demo-headers-status");
							$status.removeClass("notice-success notice-error");
							$status.addClass("notice-" + (type || "info"));
							$status.html("<p>" + message + "</p>").show();
						}

						function setButtonsState(disabled) {
							$("#cw-demo-create-headers, #cw-demo-delete-headers").prop("disabled", disabled);
						}

						$("#cw-demo-create-headers").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Create demo headers (Header_01, Header_01 - Dark, Header_01 - Light, Header_02 … Header_09)? This may take a while.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Creating entries...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_create_headers",
									nonce: createNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . '</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
								}
							});
						});

						$("#cw-demo-delete-headers").on("click", function(e) {
							e.preventDefault();

							if (!confirm("' . esc_js(__('Delete all demo headers? This action cannot be undone.', 'codeweber')) . '")) {
								return;
							}

							setButtonsState(true);
							showStatus("' . esc_js(__('Deleting entries...', 'codeweber')) . '", "info");

							$.ajax({
								url: ajaxurl,
								type: "POST",
								data: {
									action: "cw_demo_delete_headers",
									nonce: deleteNonce
								},
								success: function(response) {
									setButtonsState(false);
									if (response.success) {
										var message = response.data.message;
										if (response.data.errors && response.data.errors.length > 0) {
											message += "<br><strong>' . esc_js(__('Errors:', 'codeweber')) . ':</strong><ul>";
											response.data.errors.forEach(function(error) {
												message += "<li>" + error + "</li>";
											});
											message += "</ul>";
										}
										showStatus(message, "success");
									} else {
										showStatus(response.data.message || "' . esc_js(__('An error occurred', 'codeweber')) . '", "error");
									}
								},
								error: function() {
									setButtonsState(false);
									showStatus("' . esc_js(__('AJAX request error', 'codeweber')) . '", "error");
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

