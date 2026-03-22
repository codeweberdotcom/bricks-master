/* global cwCartOffcanvas, bootstrap */
(function ($) {
	'use strict';

	// ── Helpers ───────────────────────────────────────────────────────────────

	function getOffcanvas() {
		var el = document.getElementById('offcanvas-cart');
		if (!el) return null;
		return bootstrap.Offcanvas.getOrCreateInstance(el);
	}

	function openCart() {
		var oc = getOffcanvas();
		if (oc) oc.show();
	}

	function updateBadge(count) {
		document.querySelectorAll('.badge-cart').forEach(function (el) {
			el.textContent = count > 0 ? count : '';
			el.style.display = count > 0 ? '' : 'none';
		});
	}

	function updateCartHtml(html) {
		var inner = document.querySelector('.cw-offcanvas-cart-inner');
		if (!inner || !html) return;
		var tmp = document.createElement('div');
		tmp.innerHTML = html;
		var newInner = tmp.querySelector('.cw-offcanvas-cart-inner');
		if (newInner) {
			inner.innerHTML = newInner.innerHTML;
			inner.className  = newInner.className;
		}
	}

	function setCartLoading(loading) {
		var inner = document.querySelector('.cw-offcanvas-cart-inner');
		if (!inner) return;
		if (loading) {
			inner.classList.add('cw-cart-loading');
		} else {
			inner.classList.remove('cw-cart-loading');
		}
	}

	// ── Архив: свой AJAX (перехват клика на .ajax_add_to_cart) ───────────────

	$(document).on('click', '.ajax_add_to_cart', function (e) {
		// Single product: форма form.cart обрабатывает сабмит сама
		if ($(this).closest('form.cart').length) return;

		e.preventDefault();

		var $btn       = $(this);
		var productId  = $btn.data('product_id');
		var quantity   = $btn.data('quantity') || 1;

		if (!productId || $btn.hasClass('loading')) return;

		$btn.addClass('loading');
		openCart();
		setCartLoading(true);

		$.post(cwCartOffcanvas.ajaxUrl, {
			action:     cwCartOffcanvas.action,
			nonce:      cwCartOffcanvas.nonce,
			'add-to-cart': productId,
			quantity:   quantity,
		})
			.done(function (response) {
				if (response.success) {
					updateCartHtml(response.data.cart_html);
					updateBadge(response.data.cart_count);
					$(document.body).trigger('wc_fragment_refresh');
				} else {
					setCartLoading(false);
					getOffcanvas() && getOffcanvas().hide();
				}
			})
			.fail(function () {
				setCartLoading(false);
				getOffcanvas() && getOffcanvas().hide();
			})
			.always(function () {
				$btn.removeClass('loading');
			});
	});

	// ── Single product: перехват form.cart ───────────────────────────────────

	$(document).on('submit', 'form.cart', function (e) {
		var $form = $(this);
		var $btn  = $form.find('.single_add_to_cart_button');

		if (!$btn.length || $btn.hasClass('disabled') || $btn.prop('disabled')) {
			return;
		}

		e.preventDefault();

		// ── 1. Мгновенно: блокируем кнопку, открываем offcanvas с текущим содержимым
		$btn.addClass('loading').prop('disabled', true);
		openCart();
		setCartLoading(true);

		// ── 2. Параллельно: AJAX добавляет товар
		var data = $form.serialize();

		var productId = $form.find('[name="add-to-cart"]').val() || $btn.val();
		if (productId) {
			data += '&add-to-cart=' + encodeURIComponent(productId);
		}
		data += '&action=' + encodeURIComponent(cwCartOffcanvas.action);
		data += '&nonce='  + encodeURIComponent(cwCartOffcanvas.nonce);

		$.post(cwCartOffcanvas.ajaxUrl, data)
			.done(function (response) {
				if (response.success) {
					// ── 3. Заменяем скелетон реальным HTML
					updateCartHtml(response.data.cart_html);
					updateBadge(response.data.cart_count);
					$(document.body).trigger('wc_fragment_refresh');
				} else {
					// Ошибка — убираем индикатор, закрываем offcanvas
					setCartLoading(false);
					getOffcanvas() && getOffcanvas().hide();
					$(document.body).trigger('wc_fragment_refresh');

					var msg = (response.data && response.data.message)
						? response.data.message
						: cwCartOffcanvas.i18n.error;

					var $noticesWrap = $form.closest('.product').find('.woocommerce-notices-wrapper');
					if ($noticesWrap.length) {
						$noticesWrap.html(
							'<ul class="woocommerce-error" role="alert"><li>' +
							$('<div>').text(msg).html() + '</li></ul>'
						);
					} else {
						window.alert(msg);
					}
				}
			})
			.fail(function () {
				setCartLoading(false);
				getOffcanvas() && getOffcanvas().hide();
				$(document.body).trigger('wc_fragment_refresh');
				window.alert(cwCartOffcanvas.i18n.error);
			})
			.always(function () {
				$btn.removeClass('loading').prop('disabled', false);
			});
	});

	// ── Удаление товара из корзины ────────────────────────────────────────────

	$(document).on('click', '.cw-cart-remove', function (e) {
		e.preventDefault();

		var $item = $(this).closest('.shopping-cart-item');
		var key   = $(this).data('cart-item-key');

		if (!key) return;

		$item.addClass('cw-item-removing');

		$.post(cwCartOffcanvas.ajaxUrl, {
			action:        'cw_remove_from_cart',
			nonce:         cwCartOffcanvas.nonce,
			cart_item_key: key,
		})
			.done(function (response) {
				if (response.success) {
					updateCartHtml(response.data.cart_html);
					updateBadge(response.data.cart_count);
					$(document.body).trigger('wc_fragment_refresh');
				} else {
					$item.removeClass('cw-item-removing');
				}
			})
			.fail(function () {
				$item.removeClass('cw-item-removing');
			});
	});

}(jQuery));
