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

	// Обновить .badge-cart в шапке (WC fragment обновляет автоматически,
	// но при нашем AJAX для single product — обновляем сразу из ответа)
	function updateBadge(count) {
		document.querySelectorAll('.badge-cart').forEach(function (el) {
			el.textContent = count > 0 ? count : '';
			el.style.display = count > 0 ? '' : 'none';
		});
	}

	// Заменить содержимое offcanvas-корзины
	function updateCartHtml(html) {
		var inner = document.querySelector('.cw-offcanvas-cart-inner');
		if (inner && html) {
			inner.outerHTML = html;
		}
	}

	// ── Архив: встроенный WC AJAX ─────────────────────────────────────────────
	// wc-add-to-cart.js обрабатывает .ajax_add_to_cart, после успеха триггерит
	// added_to_cart. WC fragments обновляют .cw-offcanvas-cart-inner и .badge-cart
	// автоматически через wc-cart-fragments.js.

	$(document.body).on('added_to_cart', function () {
		openCart();
	});

	// ── Single product: перехват form.cart ───────────────────────────────────

	$(document).on('submit', 'form.cart', function (e) {
		var $form = $(this);
		var $btn  = $form.find('.single_add_to_cart_button');

		// Пропустить если кнопки нет или вариация не выбрана
		if (!$btn.length || $btn.hasClass('disabled') || $btn.prop('disabled')) {
			return;
		}

		e.preventDefault();

		$btn.addClass('loading').prop('disabled', true);

		var data = $form.serialize();

		// serialize() не включает кнопки-submit — добавляем product_id вручную
		var productId = $form.find('[name="add-to-cart"]').val() || $btn.val();
		if (productId) {
			data += '&add-to-cart=' + encodeURIComponent(productId);
		}

		data += '&action=' + encodeURIComponent(cwCartOffcanvas.action);
		data += '&nonce='  + encodeURIComponent(cwCartOffcanvas.nonce);

		$.post(cwCartOffcanvas.ajaxUrl, data)
			.done(function (response) {
				if (response.success) {
					// Обновить HTML корзины напрямую из ответа (без второго AJAX-запроса)
					updateCartHtml(response.data.cart_html);
					updateBadge(response.data.cart_count);

					// Сообщить WC об изменении корзины (обновит мини-корзину в виджете и пр.)
					$(document.body).trigger('wc_fragment_refresh');

					openCart();

				} else {
					var msg = (response.data && response.data.message)
						? response.data.message
						: cwCartOffcanvas.i18n.error;

					var $noticesWrap = $form.closest('.product').find('.woocommerce-notices-wrapper');
					if ($noticesWrap.length) {
						$noticesWrap.html(
							'<ul class="woocommerce-error" role="alert"><li>' +
							$('<div>').text(msg).html() +
							'</li></ul>'
						);
					} else {
						window.alert(msg);
					}
				}
			})
			.fail(function () {
				window.alert(cwCartOffcanvas.i18n.error);
			})
			.always(function () {
				$btn.removeClass('loading').prop('disabled', false);
			});
	});

	// ── Удаление товара из корзины (AJAX) ────────────────────────────────────

	$(document).on('click', '.cw-cart-remove', function (e) {
		e.preventDefault();

		var $item = $(this).closest('.shopping-cart-item');
		var href  = $(this).attr('href');

		if (!href || href === '#') return;

		$item.css({ opacity: '0.4', pointerEvents: 'none' });

		// WC обрабатывает ?remove_item=KEY&_wpnonce=NONCE при GET-запросе
		fetch(href, {
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
			redirect: 'follow',
		})
			.then(function () {
				// WC fragments обновят .cw-offcanvas-cart-inner и .badge-cart
				$(document.body).trigger('wc_fragment_refresh');
			})
			.catch(function () {
				$item.css({ opacity: '', pointerEvents: '' });
			});
	});

}(jQuery));
