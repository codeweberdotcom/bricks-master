/* global cwWishlist, CWNotify */
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
				if (typeof CWNotify !== 'undefined') {
					CWNotify.show(cwWishlist.i18n.loginNotice, { type: 'warning', event: 'wishlist' });
				}
				window.location.href = cwWishlist.loginUrl;
				return;
			}

			var productId = $btn.data('product-id');
			if (!productId) { return; }

			var feedback     = cwWishlist.feedbackType || 'spinner';
			var showSpinner  = (feedback === 'spinner' || feedback === 'both');
			var showCardSpinner = (feedback === 'card' || feedback === 'card-toast');
			var showToast    = (feedback === 'toast' || feedback === 'both' || feedback === 'card-toast');

			// Найти карточку товара для card-спиннера
			var $card = null;
			if (showCardSpinner) {
				$card = $btn.closest('li.product, .product, li');
				if ($card.length) {
					$card.css('position', 'relative');
					$card.append('<div class="cw-card-spinner spinner spinner-overlay"></div>');
				} else {
					// Нет карточки (напр. страница товара) — fallback на спиннер кнопки
					showCardSpinner = false;
					$btn.addClass('cw-wishlist-btn--loading').prop('disabled', true);
				}
			}

			if (showSpinner) {
				$btn.addClass('cw-wishlist-btn--loading').prop('disabled', true);
			}

			$.ajax({
				url: cwWishlist.ajaxUrl,
				method: 'POST',
				data: {
					action:     'cw_add_to_wishlist',
					nonce:      cwWishlist.nonce,
					product_id: productId,
				},
				success: function (response) {
					if (response.success) {
						CWWishlist.markAdded($btn);
						CWWishlist.updateCountWidget(response.data.count);

						if (showToast && typeof CWNotify !== 'undefined') {
							CWNotify.show(cwWishlist.i18n.added, { type: 'success', event: 'wishlist' });
						}
					}
				},
				complete: function () {
					if (showCardSpinner && $card && $card.length) {
						$card.find('.cw-card-spinner').addClass('done');
						setTimeout(function () {
							$card.find('.cw-card-spinner').remove();
						}, 300);
					}
					if (showSpinner) {
						$btn.removeClass('cw-wishlist-btn--loading').prop('disabled', false);
					}
				},
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

						if (typeof CWNotify !== 'undefined') {
							CWNotify.show(cwWishlist.i18n.removed, { type: 'info', event: 'wishlist' });
						}
					}
				},
				complete: function () {
					$btn.removeClass('cw-wishlist-btn--loading').prop('disabled', false);
				},
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
