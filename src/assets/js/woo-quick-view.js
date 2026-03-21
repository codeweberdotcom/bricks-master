/* global cwQuickView, bootstrap, Swiper */
(function () {
	'use strict';

	/**
	 * Находим или создаём Bootstrap Modal для quick view.
	 */
	function getModal() {
		var el = document.getElementById('cw-quick-view-modal');
		if (!el) return null;
		return bootstrap.Modal.getOrCreateInstance(el);
	}

	/**
	 * Инициализируем WooCommerce-форму вариаций внутри модала.
	 */
	function initVariationForm(container) {
		if (typeof jQuery === 'undefined') return;
		var $form = jQuery(container).find('.variations_form');
		if (!$form.length) return;
		$form.wc_variation_form();
		jQuery(document.body).trigger('cw_init_swatches', [$form]);
		$form.find('.variations select:eq(0)').trigger('change');
	}

	/**
	 * Инициализируем Swiper галерею — повторяет логику theme.swiperSlider()
	 * но только для переданного контейнера.
	 */
	function initSwiper(container) {
		var slider1 = container.querySelector('.swiper-container');
		if (!slider1) return;

		var swiperEl = slider1.querySelector('.swiper');
		if (!swiperEl) return;

		// Создаём контролы навигации (как в theme.swiperSlider)
		var controls = document.createElement('div');
		controls.className = 'swiper-controls';
		var pagi = document.createElement('div');
		pagi.className = 'swiper-pagination';
		var navi = document.createElement('div');
		navi.className = 'swiper-navigation';
		var prev = document.createElement('div');
		prev.className = 'swiper-button swiper-button-prev';
		var next = document.createElement('div');
		next.className = 'swiper-button swiper-button-next';
		navi.appendChild(prev);
		navi.appendChild(next);
		controls.appendChild(navi);
		controls.appendChild(pagi);
		slider1.appendChild(controls);

		new Swiper(swiperEl, {
			loop: false,
			slidesPerView: 1,
			spaceBetween: Number(slider1.getAttribute('data-margin') || 0),
			grabCursor: true,
			navigation: {
				prevEl: prev,
				nextEl: next,
			},
			pagination: {
				el: pagi,
				clickable: true,
			},
			on: {
				beforeInit: function () {
					if (slider1.getAttribute('data-nav') !== 'true') navi.remove();
					if (slider1.getAttribute('data-dots') !== 'true') pagi.remove();
					if (slider1.getAttribute('data-nav') !== 'true' && slider1.getAttribute('data-dots') !== 'true') controls.remove();
				},
				init: function () {
					this.update();
				},
			},
		});
	}

	/**
	 * Загружаем контент товара через AJAX и показываем модал.
	 */
	function loadProduct(productId, triggerBtn) {
		var modal  = getModal();
		var body   = document.getElementById('cw-quick-view-body');
		if (!modal || !body) return;

		body.innerHTML =
			'<div class="row g-0">' +

				// Левая колонка: изображение (квадрат)
				'<div class="col-md-6 bg-light">' +
					'<div class="cw-skeleton-block w-100" style="aspect-ratio:1/1;min-height:280px;border-radius:0;"></div>' +
				'</div>' +

				// Правая колонка: summary — p-4 p-lg-5 как в шаблоне
				'<div class="col-md-6 p-4 p-lg-5">' +

					// Заголовок (2 строки)
					'<div class="cw-skeleton-block mb-2" style="height:1.5em;width:80%"></div>' +
					'<div class="cw-skeleton-block mb-4" style="height:1.5em;width:55%"></div>' +

					// Рейтинг: 5 звёзд + текст
					'<div class="d-flex gap-1 align-items-center mb-3">' +
						'<div class="cw-skeleton-block" style="height:.95em;width:.95em;flex-shrink:0;"></div>' +
						'<div class="cw-skeleton-block" style="height:.95em;width:.95em;flex-shrink:0;"></div>' +
						'<div class="cw-skeleton-block" style="height:.95em;width:.95em;flex-shrink:0;"></div>' +
						'<div class="cw-skeleton-block" style="height:.95em;width:.95em;flex-shrink:0;"></div>' +
						'<div class="cw-skeleton-block" style="height:.95em;width:.95em;flex-shrink:0;"></div>' +
						'<div class="cw-skeleton-block ms-2" style="height:.8em;width:55px;"></div>' +
					'</div>' +

					// Цена
					'<div class="cw-skeleton-block mb-4" style="height:1.8em;width:28%"></div>' +

					// Атрибут 1 (label + controls)
					'<div class="cw-skeleton-block mb-2" style="height:.8em;width:30%"></div>' +
					'<div class="d-flex gap-2 mb-3">' +
						'<div class="cw-skeleton-block" style="height:2em;width:2em;flex-shrink:0;"></div>' +
						'<div class="cw-skeleton-block" style="height:2em;width:2em;flex-shrink:0;"></div>' +
						'<div class="cw-skeleton-block" style="height:2em;width:2em;flex-shrink:0;"></div>' +
						'<div class="cw-skeleton-block" style="height:2em;width:2em;flex-shrink:0;"></div>' +
					'</div>' +

					// Атрибут 2 (label + dropdown)
					'<div class="cw-skeleton-block mb-2" style="height:.8em;width:25%"></div>' +
					'<div class="cw-skeleton-block mb-4" style="height:2.2em;width:55%"></div>' +

					// Кнопка «В корзину» (qty + button)
					'<div class="d-flex gap-2 mb-4">' +
						'<div class="cw-skeleton-block" style="height:2.6em;width:65px;flex-shrink:0;"></div>' +
						'<div class="cw-skeleton-block" style="height:2.6em;width:150px;flex-shrink:0;"></div>' +
					'</div>' +

					// Категории и метки
					'<div class="cw-skeleton-block mb-2" style="height:.75em;width:60%"></div>' +
					'<div class="cw-skeleton-block mb-3" style="height:.75em;width:45%"></div>' +

					// Ссылка «Подробнее»
					'<div class="cw-skeleton-block" style="height:1.8em;width:40%"></div>' +

				'</div>' +
			'</div>';

		modal.show();

		if (triggerBtn) triggerBtn.classList.add('cw-qv-loading');

		fetch(cwQuickView.ajaxUrl + '?action=' + cwQuickView.action + '&product_id=' + encodeURIComponent(productId), {
			method: 'GET',
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
		})
			.then(function (response) {
				if (!response.ok) throw new Error('Network error');
				return response.json();
			})
			.then(function (data) {
				if (data && data.success && data.data) {
					body.innerHTML = data.data;

					initSwiper(body);
					initVariationForm(body);

					if (typeof jQuery !== 'undefined') {
						jQuery(document.body).trigger('wc_fragment_refresh');
					}
				} else {
					body.innerHTML = '<p class="text-center p-5 text-muted">' + cwQuickView.i18n.error + '</p>';
				}
			})
			.catch(function () {
				body.innerHTML = '<p class="text-center p-5 text-muted">' + cwQuickView.i18n.error + '</p>';
			})
			.finally(function () {
				if (triggerBtn) triggerBtn.classList.remove('cw-qv-loading');
			});
	}

	/**
	 * Основной обработчик кликов — делегируем на document.
	 */
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.item-view[data-product-id]');
		if (!btn) return;

		e.preventDefault();
		loadProduct(btn.dataset.productId, btn);
	});

})();
