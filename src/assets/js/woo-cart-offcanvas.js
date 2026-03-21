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

	// Обновить счётчик товаров в шапке (.badge-cart)
	function updateCartCount(count) {
		document.querySelectorAll('.badge-cart').forEach(function (el) {
			el.textContent = count > 0 ? count : '';
			el.style.display = count > 0 ? '' : 'none';
		});
	}

	// ── Архив: встроенный WC AJAX ─────────────────────────────────────────────
	// wc-add-to-cart.js обрабатывает .ajax_add_to_cart и после успеха
	// триггерит added_to_cart. WC также обновляет зарегистрированные fragments,
	// включая .cw-offcanvas-cart-inner.

	$(document.body).on('added_to_cart', function (e, fragments, cartHash, $button) {
		openCart();

		// Обновить счётчик из фрагментов (если WC передаёт cart_count)
		if (fragments && fragments['div.widget_shopping_cart_content']) {
			// Счётчик обновляется через fragment .cw-offcanvas-cart-inner
		}
	});

	// ── Single product: перехват form.cart ───────────────────────────────────

	$(document).on('submit', 'form.cart', function (e) {
		var $form = $(this);
		var $btn  = $form.find('.single_add_to_cart_button');

		// Пропустить если нет кнопки WC или кнопка недоступна (напр. вариация не выбрана)
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
					// Открыть offcanvas сразу
					openCart();

					// Обновить счётчик в шапке
					if (response.data && response.data.cart_count !== undefined) {
						updateCartCount(response.data.cart_count);
					}

					// Запросить обновление всех WC fragments (включая .cw-offcanvas-cart-inner)
					$(document.body).trigger('wc_fragment_refresh');

				} else {
					var msg = (response.data && response.data.message)
						? response.data.message
						: cwCartOffcanvas.i18n.error;
					// Показываем ошибку рядом с кнопкой, если есть блок WC notices
					var $noticesWrap = $form.closest('.product').find('.woocommerce-notices-wrapper');
					if ($noticesWrap.length) {
						$noticesWrap.html(
							'<ul class="woocommerce-error" role="alert"><li>' +
							$('<div>').text(msg).html() +
							'</li></ul>'
						);
					} else {
						// fallback
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
	// WC remove URL — GET-запрос ?remove_item=KEY&_wpnonce=NONCE.
	// Делаем fetch, затем обновляем fragments.

	$(document).on('click', '.cw-cart-remove', function (e) {
		e.preventDefault();

		var $item = $(this).closest('.shopping-cart-item');
		var href  = $(this).attr('href');

		if (!href || href === '#') return;

		$item.css({ opacity: '0.4', pointerEvents: 'none' });

		fetch(href, {
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
			redirect: 'follow',
		})
			.then(function () {
				$(document.body).trigger('wc_fragment_refresh');
			})
			.catch(function () {
				// Восстановить если ошибка
				$item.css({ opacity: '', pointerEvents: '' });
			});
	});

	// ── Обновить счётчик после WC fragment refresh ────────────────────────────

	$(document.body).on('wc_fragments_refreshed', function () {
		// .cw-offcanvas-cart-inner уже обновлён WC автоматически.
		// Обновляем .badge-cart из скрытого элемента WC (если он есть на странице).
		var $count = $('span.count'); // стандартный WC виджет корзины
		if ($count.length) {
			var raw = parseInt($count.first().text(), 10) || 0;
			updateCartCount(raw);
		}
	});

}(jQuery));
