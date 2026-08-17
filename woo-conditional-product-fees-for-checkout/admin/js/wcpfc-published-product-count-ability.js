/**
 * Global Settings: run wcpfc/count-published-products via WordPress Abilities API.
 */
( function ( $ ) {
	'use strict';

	/**
	 * Execute the ability through the Abilities REST API (readonly = GET).
	 *
	 * @return {Promise<{count: number}>}
	 */
	function wcpfcRunPublishedProductCountAbility() {
		if ( 'undefined' === typeof wp || ! wp.apiFetch ) {
			return Promise.reject( new Error( 'wp.apiFetch is not available.' ) );
		}

		var abilityName = wcpfcProductCount.ability;

		if ( ! abilityName ) {
			return Promise.reject( new Error( 'Ability name is not configured.' ) );
		}

		return wp.apiFetch( {
			path: '/wp-abilities/v1/abilities/' + abilityName + '/run',
			method: 'GET',
		} );
	}

	/**
	 * @param {unknown} response REST response body.
	 * @return {number|null}
	 */
	function wcpfcParseProductCount( response ) {
		if ( 'number' === typeof response ) {
			return response;
		}

		if ( response && 'object' === typeof response ) {
			if ( 'undefined' !== typeof response.count ) {
				return parseInt( response.count, 10 );
			}
			if ( 'undefined' !== typeof response.result && response.result ) {
				return wcpfcParseProductCount( response.result );
			}
		}

		return null;
	}

	/**
	 * Persist count via admin AJAX.
	 *
	 * @param {number} count Published product count.
	 * @return {Promise}
	 */
	function wcpfcSavePublishedProductCount( count ) {
		return $.ajax( {
			type: 'POST',
			url: wcpfcProductCount.ajaxurl,
			data: {
				action: 'wcpfc_pro_save_published_product_count',
				security: wcpfcProductCount.nonce,
				wcpfc_published_product_count: count,
			},
		} );
	}

	$( document ).on( 'click', '#wcpfc_count_published_products_btn', function () {
		var $button = $( this );
		var $field = $( '#wcpfc_published_product_count' );
		var $spinner = $( '.wcpfc-published-product-count-spinner' );

		if ( ! $field.length ) {
			return;
		}

		$button.prop( 'disabled', true );
		$spinner.addClass( 'is-active' );

		wcpfcRunPublishedProductCountAbility()
			.then( function ( response ) {
				var count = wcpfcParseProductCount( response );

				if ( null === count || isNaN( count ) ) {
					throw new Error( 'Invalid ability response.' );
				}

				$field.val( count );

				return wcpfcSavePublishedProductCount( count );
			} )
			.then( function ( saveResponse ) {
				if ( saveResponse && saveResponse.success ) {
					var $notice = $( '<div></div>' )
						.addClass( 'notice notice-success is-dismissible' )
						.append( $( '<p></p>' ).text( wcpfcProductCount.savedMessage ) );
					$( '#dotsstoremain .wp-header-end' ).after( $notice );
					setTimeout( function () {
						$notice.remove();
					}, 3000 );
				}
			} )
			.catch( function () {
				window.alert( wcpfcProductCount.errorMessage );
			} )
			.finally( function () {
				$button.prop( 'disabled', false );
				$spinner.removeClass( 'is-active' );
			} );
	} );
}( jQuery ) );
