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
			'<div class="d-flex align-items-center justify-content-center" style="min-height:320px;">' +
				'<div class="spinner-border text-primary" role="status">' +
					'<span class="visually-hidden">' + cwQuickView.i18n.loading + '</span>' +
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
