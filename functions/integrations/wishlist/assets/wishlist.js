/* global cwWishlist, bootstrap */
(function ($) {
	'use strict';

	var CWWishlist = {

		init: function () {
			this.updateCountWidget( cwWishlist.count || 0 );
			this.bindEvents();
		},

		bindEvents: function () {
			$(document).on('click', '.cw-wishlist-btn', function (e) {
				e.preventDefault();
				CWWishlist.handleToggle($(this));
			});

			$(document).on('click', '.cw-wishlist-remove', function (e) {
				e.preventDefault();
				CWWishlist.handleRemove($(this));
			});
		},

		handleToggle: function ($btn) {
			if ($btn.hasClass('cw-wishlist-btn--active')) {
				window.location.href = cwWishlist.wishlistUrl;
				return;
			}

			if (cwWishlist.isLoggedIn !== 'yes' && cwWishlist.guestsAllowed !== 'yes') {
				alert(cwWishlist.i18n.loginNotice);
				window.location.href = cwWishlist.loginUrl;
				return;
			}

			var productId = $btn.data('product-id');
			if (!productId) { return; }

			var feedback = cwWishlist.feedbackType || 'spinner';
			var showSpinner = (feedback === 'spinner' || feedback === 'both');

			if (showSpinner) {
				$btn.addClass('cw-wishlist-btn--loading').prop('disabled', true);
			}

			$.ajax({
				url: cwWishlist.ajaxUrl,
				method: 'POST',
				data: {
					action: 'cw_add_to_wishlist',
					nonce: cwWishlist.nonce,
					product_id: productId,
				},
				success: function (response) {
					if (response.success) {
						CWWishlist.markAdded($btn);
						CWWishlist.updateCountWidget(response.data.count);

						var showToast = (feedback === 'toast' || feedback === 'both');
						if (showToast) {
							CWWishlist.showToast(cwWishlist.i18n.added, 'success');
						}
					}
				},
				complete: function () {
					if (showSpinner) {
						$btn.removeClass('cw-wishlist-btn--loading').prop('disabled', false);
					}
				}
			});
		},

		handleRemove: function ($btn) {
			var productId = $btn.data('product-id');
			if (!productId) { return; }

			$btn.addClass('cw-wishlist-btn--loading').prop('disabled', true);

			$.ajax({
				url: cwWishlist.ajaxUrl,
				method: 'POST',
				data: {
					action: 'cw_remove_from_wishlist',
					nonce: cwWishlist.nonce,
					product_id: productId,
				},
				success: function (response) {
					if (response.success) {
						CWWishlist.removeCard(productId);
						CWWishlist.updateCountWidget(response.data.count);
					}
				},
				complete: function () {
					$btn.removeClass('cw-wishlist-btn--loading').prop('disabled', false);
				}
			});
		},

		markAdded: function ($btn) {
			$btn.addClass('cw-wishlist-btn--active');
			$btn.attr('href', cwWishlist.wishlistUrl);
			$btn.attr('title', cwWishlist.i18n.added);
			$btn.attr('aria-label', cwWishlist.i18n.added);
			$btn.find('.cw-wishlist-label').text(cwWishlist.i18n.added);
		},

		removeCard: function (productId) {
			var $card = $('.cw-wishlist-card[data-product-id="' + productId + '"]');
			if ($card.length) {
				$card.closest('.col').fadeOut(200, function () {
					$(this).remove();
					if ($('.cw-wishlist-card').length === 0) {
						CWWishlist.showEmptyState();
					}
				});
			}
			$('.cw-wishlist-btn[data-product-id="' + productId + '"]').removeClass('cw-wishlist-btn--active');
		},

		showEmptyState: function () {
			$('.cw-wishlist-grid').replaceWith(
				'<div class="cw-wishlist-empty">' +
					'<p>' + (cwWishlist.i18n.emptyText || 'В избранном пока ничего нет.') + '</p>' +
					'<a href="' + (cwWishlist.shopUrl || '/shop/') + '" class="btn btn-primary">' +
						(cwWishlist.i18n.goToShop || 'Перейти в каталог') +
					'</a>' +
				'</div>'
			);
		},

		/**
		 * Bootstrap 5 toast.
		 *
		 * @param {string} message
		 * @param {string} type  'success' | 'danger' | 'info'
		 */
		showToast: function (message, type) {
			type = type || 'success';

			var $container = $('#cw-toast-container');
			if (!$container.length) {
				$container = $('<div id="cw-toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index:9999;"></div>');
				$('body').append($container);
			}

			var id = 'cw-toast-' + Date.now();
			var bgClass = type === 'success' ? 'text-bg-success' : (type === 'danger' ? 'text-bg-danger' : 'text-bg-secondary');

			var $toast = $(
				'<div id="' + id + '" class="toast align-items-center ' + bgClass + ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
					'<div class="d-flex">' +
						'<div class="toast-body">' + message + '</div>' +
						'<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
					'</div>' +
				'</div>'
			);

			$container.append($toast);

			if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
				var toast = new bootstrap.Toast($toast[0], { delay: 2500, autohide: true });
				toast.show();
				$toast[0].addEventListener('hidden.bs.toast', function () {
					$toast.remove();
				});
			} else {
				// Fallback без Bootstrap JS
				$toast.addClass('show');
				setTimeout(function () { $toast.remove(); }, 2500);
			}
		},

		updateCountWidget: function (count) {
			$('.cw-wishlist-widget__count').text(count);
			$('.cw-wishlist-widget__count').toggle(count > 0);
		},
	};

	$(document).ready(function () {
		if (typeof cwWishlist !== 'undefined') {
			CWWishlist.init();
		}
	});

})(jQuery);
