(function ($) {
	'use strict';

	$(document).ready(function () {
		if (typeof wc_add_to_cart_params === 'undefined') {
			return;
		}
		initWooCommerceIntegration();
	});

	function initWooCommerceIntegration() {
		/* ---------------- Cart count refresh ---------------- */
		$(document.body).on('added_to_cart removed_from_cart', refreshCart);
		$(document.body).on('wc_fragments_refreshed wc_fragments_loaded', refreshCart);

		refreshCart();              // initial refresh
		initWishlistIntegration();   // optional wishlist
	}

	function refreshCart() {
		$.post(
			wp_mnb_wc.ajax_url,
			{
				action: 'wp_mnb_get_cart_count',
				nonce: wp_mnb_wc.nonce
			},
			function (response) {
				if (!response.success) {
					return;
				}
				const $count = $('#wp-mnb-cart-count');
				$count.text(response.data.count);

				// Bouncy feedback
				$count.addClass('wp-mnb-bounce');
				setTimeout(() => $count.removeClass('wp-mnb-bounce'), 300);
			}
		);
	}

	/* ---------------- Wishlist integration (YITH / TI) ---------------- */
	function initWishlistIntegration() {
		if (typeof yith_wcwl_l10n === 'undefined' && typeof ti_wishlist_vars === 'undefined') {
			return; // no wishlist plugin detected
		}

		$(document.body).on('added_to_wishlist removed_from_wishlist', function () {
			$('.wp-mnb-wishlist-count').load(
				location.href + ' .wp-mnb-wishlist-count>*',
				function () {
					$(this).addClass('wp-mnb-bounce');
					setTimeout(() => $(this).removeClass('wp-mnb-bounce'), 300);
				}
			);
		});
	}
})(jQuery);
