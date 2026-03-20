/* global cwNotifyConfig */
/**
 * CWNotify — универсальный менеджер уведомлений темы CodeWeber.
 *
 * Использует разметку алертов темы:
 *   <div class="alert alert-{type} alert-icon alert-dismissible fade show">
 *     <i class="uil uil-{icon}"></i> Сообщение
 *     <button class="btn-close" data-bs-dismiss="alert"></button>
 *   </div>
 *
 * API:
 *   CWNotify.show(message, { type, position, delay, event })
 *   CWNotify.isEnabled(event)
 */
(function ($) {
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

	// animate.css класс появления по позиции (используем встроенные анимации темы)
	var ENTER_ANIMATIONS = {
		'bottom-end':   'animate__slideInUp',
		'bottom-start': 'animate__slideInUp',
		'top-end':      'animate__slideInDown',
		'top-start':    'animate__slideInDown',
	};

	var CWNotify = {

		/**
		 * Показать уведомление.
		 *
		 * @param {string} message  Текст сообщения.
		 * @param {object} options  { type, position, delay, event }
		 */
		show: function (message, options) {
			options = $.extend({}, options);

			var type     = options.type     || 'success';
			var position = options.position || DEFAULTS.position;
			var delay    = options.delay    !== undefined ? options.delay : DEFAULTS.delay;
			var event    = options.event    || null;

			// Глобальное отключение
			if (!DEFAULTS.enabled) {
				return;
			}

			// Отключение по типу события
			if (event && DEFAULTS.events[event] === false) {
				return;
			}

			var icon     = ICONS[type]     || ICONS.info;
			var posClass = POSITIONS[position] || POSITIONS['bottom-end'];

			// Получить или создать контейнер для нужной позиции
			var containerId = 'cw-notify-container--' + position;
			var $container  = $('#' + containerId);

			if (!$container.length) {
				$container = $('<div>', {
					id:    containerId,
					class: 'position-fixed ' + posClass + ' p-3 d-flex flex-column gap-1 cw-notify-container',
					css:   { zIndex: 9999 },
				});
				$('body').append($container);
			}

			// Анимация появления из animate.css (встроена в тему)
			var enterAnim = ENTER_ANIMATIONS[position] || ENTER_ANIMATIONS['bottom-end'];

			// Сборка алерта
			var delayStyle = delay > 0 ? ' style="--cw-notify-delay:' + delay + 'ms"' : '';
			var $alert = $(
				'<div class="alert alert-' + type + ' alert-icon alert-dismissible fade show cw-notify-item animate__animated ' + enterAnim + '"' + delayStyle + ' role="alert">' +
					'<i class="uil ' + icon + '"></i> ' + message +
					'<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
				'</div>'
			);

			// bottom-позиции: prepend — новый тост сверху стека, старые не прыгают
			// top-позиции: append — новый тост снизу стека, старые не прыгают
			if (position.indexOf('bottom') === 0) {
				$container.prepend($alert);
			} else {
				$container.append($alert);
			}

			// Авто-скрытие
			if (delay > 0) {
				setTimeout(function () {
					$alert.removeClass('show');
					setTimeout(function () {
						$alert.remove();
					}, 200);
				}, delay);
			}

			return $alert;
		},

		/**
		 * Проверить, включены ли уведомления для события.
		 *
		 * @param  {string} event  Ключ события (wishlist, cart, form и т.д.)
		 * @return {boolean}
		 */
		isEnabled: function (event) {
			if (!DEFAULTS.enabled) {
				return false;
			}
			if (event && DEFAULTS.events[event] === false) {
				return false;
			}
			return true;
		},
	};

	// Экспортируем глобально
	window.CWNotify = CWNotify;

})(jQuery);
