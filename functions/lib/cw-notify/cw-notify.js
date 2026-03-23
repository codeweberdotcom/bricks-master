/* global cwNotifyConfig, bootstrap */
/**
 * CWNotify — универсальный менеджер уведомлений темы CodeWeber.
 *
 * Использует Bootstrap Toast:
 *   <div class="toast cw-notify-item text-bg-{type}">
 *     <div class="toast-body d-flex align-items-center gap-2">
 *       <i class="uil uil-{icon} fs-18 flex-shrink-0"></i>
 *       <span class="me-auto">Сообщение</span>
 *       <button class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
 *     </div>
 *   </div>
 *
 * API:
 *   CWNotify.show(message, { type, position, delay, event })
 *   CWNotify.isEnabled(event)
 */
(function () {
	'use strict';

	var config = (typeof cwNotifyConfig !== 'undefined') ? cwNotifyConfig : {};

	var DEFAULTS = {
		enabled:  config.enabled  !== undefined ? config.enabled  : true,
		position: config.position || 'bottom-end',
		delay:    config.delay    !== undefined ? parseInt(config.delay, 10) : 3000,
		events:   config.events   || {},
	};

	// Иконки по типу
	var ICONS = {
		success: 'uil-check-circle',
		danger:  'uil-times-circle',
		warning: 'uil-exclamation-triangle',
		info:    'uil-exclamation-circle',
		primary: 'uil-star',
	};

	// CSS-классы позиции контейнера
	var POSITIONS = {
		'bottom-end':   'bottom-0 end-0',
		'bottom-start': 'bottom-0 start-0',
		'top-end':      'top-0 end-0',
		'top-start':    'top-0 start-0',
	};

	var CWNotify = {

		/**
		 * Показать уведомление.
		 *
		 * @param {string} message  Текст сообщения.
		 * @param {object} options  { type, position, delay, event }
		 */
		show: function (message, options) {
			options = options || {};

			var type     = options.type     || 'success';
			var position = options.position || DEFAULTS.position;
			var delay    = options.delay    !== undefined ? options.delay : DEFAULTS.delay;
			var event    = options.event    || null;

			if (!DEFAULTS.enabled) return;
			if (event && DEFAULTS.events[event] === false) return;

			var icon     = ICONS[type] || ICONS.info;
			var posClass = POSITIONS[position] || POSITIONS['bottom-end'];

			// Получить или создать контейнер для нужной позиции
			var containerId = 'cw-notify-container--' + position;
			var container   = document.getElementById(containerId);

			if (!container) {
				container = document.createElement('div');
				container.id        = containerId;
				container.className = 'position-fixed ' + posClass + ' p-3 d-flex flex-column gap-1 cw-notify-container';
				container.style.zIndex = '9999';
				document.body.appendChild(container);
			}

			// Сборка Bootstrap Toast
			var toastEl = document.createElement('div');
			toastEl.className = 'toast cw-notify-item border-0';
			toastEl.setAttribute('role', 'alert');
			toastEl.setAttribute('aria-live', 'assertive');
			toastEl.setAttribute('aria-atomic', 'true');
			if (delay > 0) {
				toastEl.style.setProperty('--cw-notify-delay', delay + 'ms');
			}

			toastEl.innerHTML =
				'<div class="toast-body d-flex align-items-center gap-2">' +
					'<i class="uil ' + icon + ' fs-18 flex-shrink-0 text-' + type + '"></i>' +
					'<span class="me-auto">' + message + '</span>' +
					'<button type="button" class="btn-close flex-shrink-0" data-bs-dismiss="toast" aria-label="Close"></button>' +
				'</div>';

			// bottom-позиции: prepend — новый тост сверху стека, старые не прыгают
			// top-позиции: append — новый тост снизу стека, старые не прыгают
			if (position.indexOf('bottom') === 0) {
				container.prepend(toastEl);
			} else {
				container.appendChild(toastEl);
			}

			// Bootstrap Toast API — автоскрытие и анимация встроены
			var bsToast = new bootstrap.Toast(toastEl, {
				autohide:  delay > 0,
				delay:     delay > 0 ? delay : 5000,
				animation: true,
			});

			toastEl.addEventListener('hidden.bs.toast', function () {
				toastEl.remove();
			});

			bsToast.show();

			return toastEl;
		},

		/**
		 * Проверить, включены ли уведомления для события.
		 *
		 * @param  {string} event  Ключ события (wishlist, cart, form и т.д.)
		 * @return {boolean}
		 */
		isEnabled: function (event) {
			if (!DEFAULTS.enabled) return false;
			if (event && DEFAULTS.events[event] === false) return false;
			return true;
		},
	};

	// Экспортируем глобально
	window.CWNotify = CWNotify;

})();
