/* global cwWishlist, CWNotify */
(function ($) {
	'use strict';

	var CWWishlist = {

		init: function () {
			this.updateCountWidget( cwWishlist.count || 0 );
			this.bindEvents();
			this.initTooltips();
		},

		initTooltips: function () {
			if (typeof bootstrap === 'undefined' || typeof bootstrap.Tooltip === 'undefined') { return; }
			document.querySelectorAll('.cw-wishlist-card [data-bs-toggle="white-tooltip"], .cw-wishlist-card [data-bs-toggle="tooltip"]').forEach(function (el) {
				var existing = bootstrap.Tooltip.getInstance(el);
				if (existing) { existing.dispose(); }
				new bootstrap.Tooltip(el, {
					customClass: el.getAttribute('data-bs-toggle') === 'white-tooltip' ? 'white-tooltip' : '',
					trigger: 'hover',
					placement: el.dataset.bsPlacement || 'left',
				});
			});
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
				if ($btn.hasClass('cw-wishlist-btn--single')) {
					window.location.href = cwWishlist.wishlistUrl;
				} else {
					CWWishlist.handleRemove($btn);
				}
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

			var feedback        = cwWishlist.feedbackType || 'spinner';
			var showSpinner     = (feedback === 'spinner');
			var showCardSpinner = (feedback === 'card');
			var showToast       = (cwWishlist.showToast === 'yes');

			var $card = null;
			if (showCardSpinner) {
				$card = $btn.closest('figure');
				if (!$card.length) { $card = $btn.closest('[id^="product-"]'); }
				if (!$card.length) { $card = $btn.closest('li.product, article.product, .product, li'); }

				if ($card.length) {
					$card.append('<div class="cw-card-spinner spinner spinner-overlay"></div>');
				} else {
					showCardSpinner = false;
					showSpinner = true;
				}
			}

			if (showCardSpinner && $card && $card.length) {
				$btn.prop('disabled', true);
			} else if (showSpinner) {
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
						$card.find('.cw-card-spinner').remove();
					}
					$btn.removeClass('cw-wishlist-btn--loading').prop('disabled', false);
				},
			});
		},

		handleRemove: function ($btn) {
			var productId = $btn.data('product-id');
			if (!productId) { return; }

			// На странице вишлиста (.cw-wishlist-card) — всегда оверлей на figure.
			// В каталоге — уважаем feedbackType.
			var $spinnerHost = $();

			var $wishlistCard = $btn.closest('.cw-wishlist-card');
			if ($wishlistCard.length) {
				$spinnerHost = $wishlistCard.find('figure');
			} else {
				var feedback = cwWishlist.feedbackType || 'spinner';
				if (feedback === 'card' || feedback === 'card-toast') {
					$spinnerHost = $btn.closest('figure');
					if (!$spinnerHost.length) { $spinnerHost = $btn.closest('[id^="product-"]'); }
					if (!$spinnerHost.length) { $spinnerHost = $btn.closest('li.product, article.product, .product, li'); }
				}
			}

			if ($spinnerHost.length) {
				$spinnerHost.append('<div class="cw-card-spinner spinner spinner-overlay"></div>');
				$btn.prop('disabled', true);
			} else {
				$btn.addClass('cw-wishlist-btn--loading').prop('disabled', true);
			}

			$.ajax({
				url: cwWishlist.ajaxUrl,
				method: 'POST',
				data: {
					action:     'cw_remove_from_wishlist',
					nonce:      cwWishlist.nonce,
					product_id: productId,
				},
				success: function (response) {
					if (response.success) {
						CWWishlist.removeCard(productId);
						CWWishlist.updateCountWidget(response.data.count);

						var showToast = (cwWishlist.showToast === 'yes');
						if (showToast && typeof CWNotify !== 'undefined') {
							CWNotify.show(cwWishlist.i18n.removed, { type: 'info', event: 'wishlist' });
						}
					}
				},
				complete: function () {
					$spinnerHost.find('.cw-card-spinner').remove();
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
				$card.fadeOut(200, function () {
					$(this).remove();
					if ($('.cw-wishlist-card').length === 0) {
						CWWishlist.showEmptyState();
					}
				});
			}
			$('.cw-wishlist-btn[data-product-id="' + productId + '"]')
				.removeClass('cw-wishlist-btn--active')
				.attr('href', '#')
				.attr('title', cwWishlist.i18n.add)
				.attr('aria-label', cwWishlist.i18n.add)
				.find('.cw-wishlist-label').text(cwWishlist.i18n.add);
		},

		showEmptyState: function () {
			$('.cw-wishlist-grid').replaceWith(
				'<div class="cw-wishlist-empty">' +
					'<p>' + (cwWishlist.i18n.emptyText || 'Your wishlist is empty.') + '</p>' +
					'<a href="' + (cwWishlist.shopUrl || '/shop/') + '" class="btn btn-primary">' +
						(cwWishlist.i18n.goToShop || 'Go to Shop') +
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
