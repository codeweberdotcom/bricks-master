/**
 * Event Registration Form Handler
 *
 * По паттерну testimonial-form.js.
 * Отправка через REST API codeweber/v1/events/register.
 *
 * @package Codeweber
 */

(function () {
	'use strict';

	/**
	 * Заменяет контент модального окна на шаблон с конвертом (success state).
	 */
	function replaceModalContentWithEnvelope(form, message) {
		var modal        = form.closest('.modal');
		var modalContent = modal ? modal.querySelector('.modal-body') : null;

		if (!modal || !modalContent) return;

		var apiRoot  = (typeof wpApiSettings !== 'undefined') ? wpApiSettings.root : '/wp-json/';
		var apiNonce = (typeof wpApiSettings !== 'undefined') ? wpApiSettings.nonce : '';

		if (!apiNonce) {
			var nonceMeta = document.querySelector('meta[name="wp-api-nonce"]');
			if (nonceMeta) apiNonce = nonceMeta.getAttribute('content');
		}

		fetch(apiRoot + 'codeweber/v1/success-message-template?message=' + encodeURIComponent(message) + '&icon_type=svg', {
			method: 'GET',
			headers: { 'X-WP-Nonce': apiNonce, 'Content-Type': 'application/json' }
		})
		.then(function (r) { return r.json(); })
		.then(function (data) {
			if (data.success && data.html) {
				modalContent.innerHTML = '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' + data.html;
				setTimeout(function () {
					var bsModal = bootstrap.Modal.getInstance(modal);
					if (bsModal) bsModal.hide();
				}, 2000);
			} else {
				setTimeout(function () {
					var bsModal = bootstrap.Modal.getInstance(modal);
					if (bsModal) bsModal.hide();
				}, 500);
			}
		})
		.catch(function () {
			setTimeout(function () {
				var bsModal = bootstrap.Modal.getInstance(modal);
				if (bsModal) bsModal.hide();
			}, 500);
		});
	}

	/**
	 * Show inline message (fallback when no modal).
	 */
	function showMessage(container, message, type) {
		if (!container) return;
		var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
		container.className = 'event-reg-form-messages alert ' + alertClass;
		container.innerHTML = '<p class="mb-0">' + message + '</p>';
		container.style.display = 'block';
		container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
	}

	/**
	 * Update seats counter on the page after successful registration.
	 */
	function updateSeatsCounter(eventId) {
		var counter = document.querySelector('[data-event-seats-counter="' + eventId + '"]');
		if (!counter) return;
		var taken = parseInt(counter.getAttribute('data-seats-taken') || '0', 10) + 1;
		var max   = parseInt(counter.getAttribute('data-seats-max') || '0', 10);
		counter.setAttribute('data-seats-taken', taken);

		var takenEl = counter.querySelector('.event-seats-taken');
		var leftEl  = counter.querySelector('.event-seats-left');
		var bar     = counter.querySelector('.event-seats-bar');

		if (takenEl) takenEl.textContent = taken;
		if (max > 0) {
			var left = Math.max(0, max - taken);
			if (leftEl) leftEl.textContent = left;
			if (bar) {
				var pct = Math.min(100, Math.round((taken / max) * 100));
				bar.style.width = pct + '%';
				bar.setAttribute('aria-valuenow', pct);
			}
		}
	}

	/**
	 * Initialize a single event registration form.
	 */
	function initEventRegForm(form) {
		if (form.dataset.initialized === 'true') return;
		form.dataset.initialized = 'true';

		var submitBtn       = form.querySelector('[type="submit"]');
		var formMessages    = form.querySelector('.event-reg-form-messages');
		var originalBtnText = submitBtn ? (submitBtn.textContent || submitBtn.innerText) : '';

		form.addEventListener('submit', function (e) {
			e.preventDefault();

			if (!form.checkValidity()) {
				form.classList.add('was-validated');
				var firstInvalid = form.querySelector(':invalid');
				if (firstInvalid) firstInvalid.focus();
				return;
			}
			form.classList.remove('was-validated');

			if (formMessages) {
				formMessages.innerHTML = '';
				formMessages.className = 'event-reg-form-messages';
			}

			if (submitBtn) {
				submitBtn.disabled = true;
				var loadingText = submitBtn.dataset.loadingText || '...';
				submitBtn.textContent = loadingText;
			}

			var formData  = new FormData(form);
			var eventId   = form.getAttribute('data-event-id') || formData.get('event_id');
			var restUrl   = (typeof codeweberEventReg !== 'undefined') ? codeweberEventReg.restUrl : '/wp-json/codeweber/v1/events/register';
			var restNonce = (typeof codeweberEventReg !== 'undefined') ? codeweberEventReg.nonce : '';

			var payload = {
				event_id: parseInt(eventId, 10),
				name:     formData.get('reg_name')    || '',
				email:    formData.get('reg_email')   || '',
				phone:    formData.get('reg_phone')   || '',
				message:  formData.get('reg_message') || '',
				nonce:    formData.get('event_reg_nonce') || restNonce,
				honeypot: formData.get('event_reg_honeypot') || '',
			};

			fetch(restUrl, {
				method:  'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': restNonce },
				body:    JSON.stringify(payload),
			})
			.then(function (r) {
				if (!r.ok) {
					return r.json().then(function (err) { throw new Error(err.message || 'Error'); });
				}
				return r.json();
			})
			.then(function (data) {
				if (data.success) {
					form.reset();
					form.classList.remove('was-validated');

					var successMsg = data.message || '';

					replaceModalContentWithEnvelope(form, successMsg);

					var modal = form.closest('.modal');
					if (!modal && formMessages) {
						showMessage(formMessages, successMsg, 'success');
					}

					updateSeatsCounter(eventId);

					form.dispatchEvent(new CustomEvent('codeweberEventRegSubmitted', {
						detail: { eventId: eventId, registrationId: data.registration_id }
					}));
				} else {
					showMessage(formMessages, data.message || 'Error', 'error');
				}
			})
			.catch(function (err) {
				showMessage(formMessages, err.message || 'Network error', 'error');
			})
			.finally(function () {
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.textContent = originalBtnText;
				}
			});
		});
	}

	/**
	 * Init all forms on page.
	 */
	function initAllForms() {
		document.querySelectorAll('.event-registration-form').forEach(initEventRegForm);
	}

	window.initEventRegForm = initEventRegForm;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAllForms);
	} else {
		initAllForms();
	}

	// Reinit when modal opens
	document.addEventListener('shown.bs.modal', function (e) {
		if (e.target.querySelector('.event-registration-form')) {
			setTimeout(initAllForms, 100);
		}
	});

})();
