/**
 * Shared Freemius overlay checkout for Upgrade Now buttons.
 */
( function ( window, $ ) {
	'use strict';

	var CHECKOUT_SCRIPT = 'https://checkout.freemius.com/js/v1/checkout.global.js';
	var DEFAULTS = {
		plugin_id: '3390',
		plan_id: '5474',
		public_key: 'pk_9edf804dccd14eabfd00ff503acaf',
		image: 'https://www.thedotstore.com/wp-content/uploads/sites/1417/2023/09/WooCommerce-Extra-Fees-Banner-New.png',
		hide_coupon: true,
		show_reviews: true,
		show_refund_badge: true,
		always_show_renewals_amount: true,
	};

	function getCheckoutSources() {
		var sources = [];

		if ( window.__FSCheckoutGlobalInternal__ && window.__FSCheckoutGlobalInternal__.Checkout ) {
			sources.push( window.__FSCheckoutGlobalInternal__.Checkout );
		}

		if ( window.FS && window.FS.Checkout ) {
			sources.push( window.FS.Checkout );
		}

		return sources;
	}

	function hasCheckoutApi() {
		return getCheckoutSources().length > 0;
	}

	function createHandler( config ) {
		var sources = getCheckoutSources();
		var i;
		var Checkout;
		var handler;

		for ( i = 0; i < sources.length; i++ ) {
			Checkout = sources[ i ];

			if ( Checkout && typeof Checkout.configure === 'function' ) {
				try {
					handler = Checkout.configure( config );
					if ( handler && typeof handler.open === 'function' ) {
						return handler;
					}
				} catch ( e ) {
					handler = null;
				}
			}

			if ( typeof Checkout === 'function' ) {
				try {
					handler = new Checkout( config );
					if ( handler && typeof handler.open === 'function' ) {
						return handler;
					}
				} catch ( e ) {
					handler = null;
				}
			}
		}

		return null;
	}

	function openHostedCheckout( config ) {
		var params = [
			'plugin_id=' + encodeURIComponent( config.plugin_id ),
			'plan_id=' + encodeURIComponent( config.plan_id ),
			'public_key=' + encodeURIComponent( config.public_key ),
		];

		if ( config.coupon ) {
			params.push( 'coupon=' + encodeURIComponent( config.coupon ) );
		}

		window.open( 'https://checkout.freemius.com/?' + params.join( '&' ), '_blank' );
	}

	function loadCheckoutScript( callback ) {
		var existing = document.querySelector( 'script[src*="checkout.freemius.com"]' );
		var script;

		if ( hasCheckoutApi() ) {
			callback();
			return;
		}

		if ( existing ) {
			existing.addEventListener( 'load', callback );
			existing.addEventListener( 'error', callback );
			if ( 'complete' === document.readyState ) {
				callback();
			}
			return;
		}

		script = document.createElement( 'script' );
		script.src = CHECKOUT_SCRIPT;
		script.async = true;
		script.onload = callback;
		script.onerror = callback;
		document.head.appendChild( script );
	}

	function openCheckout( couponCode ) {
		var config = $.extend( {}, DEFAULTS, {
			coupon: couponCode || $( '.upgrade-to-pro-discount-code' ).val() || '',
		} );
		var licenses = $( 'input[name="licence"]:checked' ).val();
		var opened = false;

		function tryOpen() {
			var handler;

			if ( opened ) {
				return true;
			}

			handler = createHandler( config );

			if ( handler && typeof handler.open === 'function' ) {
				opened = true;
				handler.open( {
					name: 'WooCommerce Extra Fees Plugin',
					subtitle: 'You\u2019re a step closer to our Pro features',
					licenses: licenses,
					purchaseCompleted: function () {},
					success: function () {},
				} );
				return true;
			}

			opened = true;
			openHostedCheckout( config );
			return false;
		}

		if ( hasCheckoutApi() ) {
			return tryOpen();
		}

		loadCheckoutScript( tryOpen );
		return true;
	}

	window.wcpfcOpenFreemiusCheckout = openCheckout;
}( window, jQuery ) );
