/* global cwQuickView, bootstrap */
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
	 * Загружаем контент товара через AJAX и показываем модал.
	 */
	function loadProduct(productId, triggerBtn) {
		var modal  = getModal();
		var body   = document.getElementById('cw-quick-view-body');
		if (!modal || !body) return;

		// Показываем модал со спиннером
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

					// Активируем carousel thumbnail-кнопки
					initThumbSync(body);

					// Инициализируем форму вариаций
					initVariationForm(body);

					// Сообщаем WooCommerce о новом контенте
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
	 * Синхронизируем активный класс миниатюр с Bootstrap Carousel.
	 */
	function initThumbSync(container) {
		var carousel = container.querySelector('.cw-qv-carousel');
		if (!carousel) return;

		carousel.addEventListener('slide.bs.carousel', function (e) {
			var thumbs = container.querySelectorAll('.cw-qv-thumb');
			thumbs.forEach(function (t, i) {
				t.classList.toggle('active', i === e.to);
			});
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
