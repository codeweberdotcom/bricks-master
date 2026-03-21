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
		$form.find('.variations select:eq(0)').trigger('change');
	}

	/**
	 * Инициализируем Swiper с миниатюрами — повторяет логику theme.swiperSlider()
	 * но только для переданного контейнера.
	 */
	function initSwiper(container) {
		var slider1 = container.querySelector('.swiper-container');
		if (!slider1) return;

		var swiperEl   = slider1.querySelector('.swiper:not(.swiper-thumbs)');
		var swiperThEl = slider1.querySelector('.swiper-thumbs');
		if (!swiperEl) return;

		// Создаём контролы навигации (как в theme.swiperSlider)
		var controls = document.createElement('div');
		controls.className = 'swiper-controls';
		var navi = document.createElement('div');
		navi.className = 'swiper-navigation';
		var prev = document.createElement('div');
		prev.className = 'swiper-button swiper-button-prev';
		var next = document.createElement('div');
		next.className = 'swiper-button swiper-button-next';
		navi.appendChild(prev);
		navi.appendChild(next);
		controls.appendChild(navi);
		slider1.appendChild(controls);

		var thumbsSwiper = null;

		if (swiperThEl && slider1.getAttribute('data-thumbs') === 'true') {
			thumbsSwiper = new Swiper(swiperThEl, {
				slidesPerView: 5,
				spaceBetween: 10,
				loop: false,
				threshold: 2,
				slideToClickedSlide: true,
			});

			// Оборачиваем основной swiper в swiper-main, контролы переносим туда
			var swiperMain = document.createElement('div');
			swiperMain.className = 'swiper-main';
			swiperEl.parentNode.insertBefore(swiperMain, swiperEl);
			swiperMain.appendChild(swiperEl);
			slider1.removeChild(controls);
			swiperMain.appendChild(controls);
		}

		new Swiper(swiperEl, {
			loop: false,
			slidesPerView: 1,
			spaceBetween: Number(slider1.getAttribute('data-margin') || 10),
			grabCursor: true,
			navigation: {
				prevEl: prev,
				nextEl: next,
			},
			thumbs: {
				swiper: thumbsSwiper,
			},
			on: {
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
