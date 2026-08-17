/**
 * Smart Fee Recommendations – Abilities API + modal UI.
 */
( function ( $ ) {
	'use strict';

	var state = {
		suggestions: [],
		metrics: {},
		revenueSummary: {},
		draftsAdded: 0,
	};

	var loadingState = {
		timer: null,
	};

	var SCAN_MAX_VISIBLE = 4;
	var SCAN_STEP_DURATION = 1450;

	function runAbility( abilityName, method, data ) {
		if ( 'undefined' === typeof wp || ! wp.apiFetch ) {
			return Promise.reject( new Error( 'wp.apiFetch unavailable' ) );
		}
		var options = {
			path: '/wp-abilities/v1/abilities/' + abilityName + '/run',
			method: method || 'GET',
		};
		if ( data && 'GET' !== options.method ) {
			options.data = { input: data };
		}
		return wp.apiFetch( options );
	}

	function escapeHtml( text ) {
		return $( '<div>' ).text( text || '' ).html();
	}

	function stripHtml( html ) {
		return $( '<div>' ).html( html || '' ).text();
	}

	function plainText( value ) {
		if ( null === value || undefined === value ) {
			return '';
		}
		return stripHtml( String( value ) ).replace( /\s+/g, ' ' ).trim();
	}

	function getCurrencySymbol() {
		return plainText( state.metrics.currency_symbol ) || '$';
	}

	function safeText( value ) {
		return escapeHtml( plainText( value ) );
	}

	function recalculateRevenueSummary() {
		var monthly = 0;
		state.suggestions.forEach( function ( item ) {
			monthly += parseFloat( item.revenue_monthly, 10 ) || 0;
		} );
		state.revenueSummary = {
			monthly_total: monthly,
			three_month_total: monthly * 3,
		};
	}

	function renderRevenueTotals() {
		var $box = $( '.wcpfc-bestfit-revenue-totals' );
		if ( ! $box.length ) {
			return;
		}
		if ( ! state.suggestions.length ) {
			$box.hide();
			return;
		}
		recalculateRevenueSummary();
		var monthly = state.revenueSummary.monthly_total || 0;
		var three = state.revenueSummary.three_month_total || 0;
		var monthlyText = formatMoney( monthly );
		var threeText = formatMoney( three );

		if ( state.suggestions.length === state.initialCount && state.revenueSummary.monthly_formatted ) {
			monthlyText = stripHtml( state.revenueSummary.monthly_formatted );
			threeText = stripHtml( state.revenueSummary.three_month_formatted || '' ) || formatMoney( three );
		}

		$box.show();
		$box.find( '.wcpfc-bestfit-revenue-monthly-value' ).text( '~' + monthlyText );
		$box.find( '.wcpfc-bestfit-revenue-three-value' ).text( '~' + threeText );

		var $btn = $( '#wcpfc_bestfit_draft_all' );
		var $status = $( '.wcpfc-bestfit-draft-all-status' );
		$status.hide().removeClass( 'is-success is-error' ).empty();
		$btn.prop( 'disabled', false ).removeClass( 'is-loading is-success' );
		$btn.find( '.wcpfc-bestfit-draft-all-label' ).text( wcpfcBestfit.i18n.draftAllFee || 'Draft all fee' );
		$btn.find( '.wcpfc-bestfit-btn-spinner' ).remove();
	}

	function formatMoney( amount ) {
		var symbol = getCurrencySymbol();
		return symbol + Number( amount ).toLocaleString( undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 } );
	}

	function formatAmountBadge( item ) {
		var feesLabel = wcpfcBestfit.i18n.feesLabel || 'Fees';
		var amount = parseFloat( item.amount, 10 );
		var displayAmount = isNaN( amount ) ? plainText( item.amount ) : String( Math.round( amount ) );

		if ( item.fee_type === 'percentage' ) {
			return displayAmount + '% ' + feesLabel;
		}
		var symbol = getCurrencySymbol();
		return symbol + displayAmount + ' ' + feesLabel;
	}

	function getConfidenceDotClass( confidence ) {
		var score = parseInt( confidence, 10 ) || 0;
		return score >= 75 ? 'wcpfc-bestfit-card-dot--strong' : 'wcpfc-bestfit-card-dot--moderate';
	}

	function formatConditionLabel( condition ) {
		if ( ! condition ) {
			return '';
		}

		var label = plainText( condition.label || '' );
		var key = plainText( condition.condition || '' );
		var operator = condition.operator || 'is_equal_to';
		var values = condition.values;
		var valueText = '';

		if ( Array.isArray( values ) ) {
			valueText = values.map( function ( value ) {
				return plainText( value );
			} ).filter( Boolean ).join( ', ' );
		} else if ( null !== values && undefined !== values ) {
			valueText = plainText( values );
		}

		if ( label && valueText && label.toLowerCase().indexOf( valueText.toLowerCase() ) !== -1 ) {
			return label;
		}
		if ( label && /[<>!=]=?/.test( label ) ) {
			return label;
		}
		if ( label && /\b(is|below|above|at least|at most)\b/i.test( label ) && valueText ) {
			return label;
		}

		var names = {
			cart_total: wcpfcBestfit.i18n.conditionCartSubtotal,
			quantity: wcpfcBestfit.i18n.conditionCartQuantity,
			product_qty: wcpfcBestfit.i18n.conditionProductQuantity,
			country: wcpfcBestfit.i18n.conditionCountry,
			product: wcpfcBestfit.i18n.conditionProduct,
			category: wcpfcBestfit.i18n.conditionCategory,
			payment: wcpfcBestfit.i18n.conditionPayment,
		};
		var operators = {
			less_then: '<',
			greater_then: '>',
			greater_equal_to: '>=',
			less_equal_to: '<=',
			is_equal_to: '=',
			not_in: '!=',
		};
		var conditionName = names[ key ] || label || key;

		if ( 'country' === key || 'product' === key || 'category' === key || 'payment' === key ) {
			if ( 'not_in' === operator ) {
				return conditionName + ' ' + wcpfcBestfit.i18n.conditionIsNot + ' ' + valueText;
			}
			return conditionName + ' ' + wcpfcBestfit.i18n.conditionIs + ' ' + valueText;
		}

		if ( valueText ) {
			return conditionName + ' ' + ( operators[ operator ] || operator ) + ' ' + valueText;
		}

		return label || conditionName;
	}

	function buildWhenLine( item ) {
		var conditions = item.conditions || [];
		var whenText = wcpfcBestfit.i18n.defaultWhen;
		if ( conditions.length ) {
			whenText = conditions.map( function ( condition ) {
				return formatConditionLabel( condition );
			} ).filter( Boolean ).join( '; ' );
		}
		var matchType = ( item.match_type || 'all' ).toLowerCase();
		var matchLabel = 'any' === matchType ? wcpfcBestfit.i18n.matchAny : wcpfcBestfit.i18n.matchAll;
		return wcpfcBestfit.i18n.whenLabel + ' ' + whenText + ' | ' + wcpfcBestfit.i18n.match + ': ' + matchLabel;
	}

	function formatUpliftRange( item ) {
		var three = parseFloat( item.revenue_3_month, 10 ) || 0;
		if ( three <= 0 ) {
			return '';
		}
		var low = Math.round( three * 0.85 );
		var high = Math.round( three * 1.15 );
		return wcpfcBestfit.i18n.estUplift + ' ' + formatMoney( low ) + ' – ' + formatMoney( high );
	}

	function buildMoreInfoHtml( item ) {
		var confidence = parseInt( item.confidence, 10 ) || 75;
		var monthlyFormatted = item.revenue_monthly_formatted ? plainText( item.revenue_monthly_formatted ) : formatMoney( item.revenue_monthly );
		var html = '';

		html += '<p><strong>' + escapeHtml( wcpfcBestfit.i18n.confidence ) + ':</strong> ' + confidence + '%</p>';

		if ( item.reason ) {
			html += '<p><strong>' + escapeHtml( wcpfcBestfit.i18n.reason ) + ':</strong> ' + safeText( item.reason ) + '</p>';
		}
		if ( item.revenue_breakdown ) {
			var breakdown = plainText( item.revenue_breakdown );
			var label = plainText( wcpfcBestfit.i18n.additionalRevenue ).toLowerCase();
			if ( breakdown.toLowerCase().indexOf( label ) === 0 ) {
				html += '<p>' + escapeHtml( breakdown ) + '</p>';
			} else {
				html += '<p><strong>' + escapeHtml( wcpfcBestfit.i18n.additionalRevenue ) + ':</strong> ' + escapeHtml( breakdown ) + '</p>';
			}
		}
		if ( monthlyFormatted ) {
			html += '<p><strong>' + escapeHtml( wcpfcBestfit.i18n.revenueMonthlyLabel ) + '</strong> ~' + escapeHtml( monthlyFormatted ) + '</p>';
		}

		return html;
	}

	function buildPoweredByBadge( item ) {
		var poweredBy = item.powered_by || ( 'ai' === item.suggestion_type ? 'ai' : 'store_data' );
		if ( 'ai' === poweredBy ) {
			return '<span class="wcpfc-bestfit-powered-badge wcpfc-bestfit-powered-badge--ai">' +
				escapeHtml( wcpfcBestfit.i18n.badgePoweredByAI ) +
			'</span>';
		}
		return '<span class="wcpfc-bestfit-powered-badge wcpfc-bestfit-powered-badge--store">' +
			escapeHtml( wcpfcBestfit.i18n.badgeBasedOnStoreData ) +
		'</span>';
	}

	function buildSummaryHtml() {
		var orderCount = state.metrics.order_count || 0;
		var storeCount = state.storeCount || 0;
		var aiCount = state.aiAvailable ? ( state.aiCount || 0 ) : 0;
		var cachedNote = state.aiAvailable && state.aiCached ? ' ' + escapeHtml( wcpfcBestfit.i18n.aiCachedNote ) : '';
		var ordersLabel = '<strong>' + escapeHtml( ( wcpfcBestfit.i18n.ordersCount || '%d orders' ).replace( '%d', orderCount ) ) + '</strong>';
		var daysLabel = '<strong>' + escapeHtml( wcpfcBestfit.i18n.daysPeriod || '90 days' ) + '</strong>';

		if ( storeCount > 0 && aiCount > 0 ) {
			if ( orderCount > 0 ) {
				return escapeHtml( wcpfcBestfit.i18n.summaryMixed )
					.replace( '%1$d', storeCount )
					.replace( '%2$d', aiCount )
					.replace( '%3$s', ordersLabel )
					.replace( '%4$s', daysLabel ) + cachedNote;
			}
			return escapeHtml( wcpfcBestfit.i18n.summaryMixedNoOrders )
				.replace( '%1$d', storeCount )
				.replace( '%2$d', aiCount ) + cachedNote;
		}

		if ( storeCount > 0 ) {
			return orderCount > 0
				? escapeHtml( wcpfcBestfit.i18n.summaryStoreOnly )
					.replace( '%1$d', storeCount )
					.replace( '%2$s', ordersLabel )
					.replace( '%3$s', daysLabel )
				: escapeHtml( wcpfcBestfit.i18n.summaryStoreOnlyNoOrders ).replace( '%1$d', storeCount );
		}

		return escapeHtml( wcpfcBestfit.i18n.summaryEmpty );
	}

	function blinkSummaryNote() {
		var $summary = $( '.wcpfc-bestfit-summary' );
		if ( ! $summary.length ) {
			return;
		}
		$summary.removeClass( 'is-blinking' );
		// Force reflow so the animation can replay.
		void $summary[ 0 ].offsetWidth;
		$summary.addClass( 'is-blinking' );
		setTimeout( function () {
			$summary.removeClass( 'is-blinking' );
		}, 900 );
	}

	var MIN_ACTION_FEEDBACK_MS = 1500;
	var CARD_REMOVE_DELAY_MS = 800;

	function delay( ms ) {
		return new Promise( function ( resolve ) {
			setTimeout( resolve, ms );
		} );
	}

	function waitForMinFeedback( startedAt ) {
		var elapsed = Date.now() - startedAt;
		var remaining = Math.max( 0, MIN_ACTION_FEEDBACK_MS - elapsed );
		return delay( remaining );
	}

	function resetCardActions( $card, $addBtn, originalText ) {
		$card.removeClass( 'is-processing' );
		$addBtn.removeClass( 'is-adding is-added' ).text( originalText );
		$card.find( '.wcpfc-bestfit-skip-fee' ).show();
		$card.find( 'button, .wcpfc-bestfit-more-toggle' ).prop( 'disabled', false );
	}

	function setAddButtonAdding( $addBtn ) {
		$addBtn
			.addClass( 'is-adding' )
			.html(
				'<span class="wcpfc-bestfit-btn-spinner" aria-hidden="true"></span>' +
				escapeHtml( wcpfcBestfit.i18n.addingFee )
			);
	}

	function setSkipButtonSkipping( $skipBtn ) {
		$skipBtn
			.addClass( 'is-skipping' )
			.html(
				'<span class="wcpfc-bestfit-btn-spinner" aria-hidden="true"></span>' +
				escapeHtml( wcpfcBestfit.i18n.skippingFee )
			);
	}

	function removeCardFromList( $card, item ) {
		setTimeout( function () {
			$card.slideUp( 280, function () {
				var removeIndex = state.suggestions.indexOf( item );
				if ( removeIndex > -1 ) {
					state.suggestions.splice( removeIndex, 1 );
				}
				renderSuggestions();
			} );
		}, CARD_REMOVE_DELAY_MS );
	}

	function renderSuggestions( options ) {
		var opts = options || {};
		var $content = $( '.wcpfc-bestfit-content' );
		var $list = $( '.wcpfc-bestfit-suggestions' );
		var $summary = $( '.wcpfc-bestfit-summary' );

		$summary.html( buildSummaryHtml() );

		$list.empty();

		if ( ! state.suggestions.length ) {
			$content.addClass( 'is-empty' ).css( 'display', 'flex' );
			renderRevenueTotals();
			if ( opts.blinkSummary ) {
				requestAnimationFrame( function () {
					blinkSummaryNote();
				} );
			}
			return;
		}

		$content.removeClass( 'is-empty' ).show();

		state.suggestions.forEach( function ( item, index ) {
			var amountBadge = formatAmountBadge( item );
			var whenLine = buildWhenLine( item );
			var upliftLine = formatUpliftRange( item );
			var moreInfoHtml = buildMoreInfoHtml( item );
			var poweredByBadge = buildPoweredByBadge( item );
			var confidence = parseInt( item.confidence, 10 ) || 75;
			var dotClass = getConfidenceDotClass( confidence );
			var howItHelps = item.how_it_helps ? safeText( item.how_it_helps ) : '';

			var $card = $( '<div class="wcpfc-bestfit-card" data-index="' + index + '"></div>' );

			$card.append(
				'<div class="wcpfc-bestfit-card-top">' +
					'<h4 class="wcpfc-bestfit-card-heading">' +
						'<span class="wcpfc-bestfit-card-dot ' + dotClass + '" aria-hidden="true" title="' + confidence + '%"></span>' +
						'<span class="wcpfc-bestfit-card-title">' + safeText( item.title ) + '</span>' +
						poweredByBadge +
					'</h4>' +
					'<span class="wcpfc-bestfit-amount-badge">' + safeText( amountBadge ) + '</span>' +
				'</div>'
			);

			$card.append(
				'<p class="wcpfc-bestfit-when">' +
					'<span class="dashicons dashicons-filter" aria-hidden="true"></span>' +
					'<span>' + safeText( whenLine ) + '</span>' +
				'</p>'
			);

			if ( howItHelps ) {
				$card.append(
					'<p class="wcpfc-bestfit-how-it-helps">' + howItHelps + '</p>'
				);
			}

			if ( upliftLine ) {
				$card.append(
					'<div class="wcpfc-bestfit-uplift">' +
						'<span class="dashicons dashicons-chart-line" aria-hidden="true"></span>' +
						'<span>' + safeText( upliftLine ) + '</span>' +
					'</div>'
				);
			}

			if ( moreInfoHtml ) {
				$card.append(
					'<div class="wcpfc-bestfit-more">' +
						'<button type="button" class="wcpfc-bestfit-more-toggle" aria-expanded="false">' +
							escapeHtml( wcpfcBestfit.i18n.moreInfo ) +
							' <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>' +
						'</button>' +
						'<div class="wcpfc-bestfit-more-panel">' + moreInfoHtml + '</div>' +
					'</div>'
				);
			}

			$card.append(
				'<div class="wcpfc-bestfit-card-actions">' +
					'<button type="button" class="button wcpfc-bestfit-add-fee" data-index="' + index + '">' + escapeHtml( wcpfcBestfit.i18n.addFeeShort ) + '</button>' +
					'<button type="button" class="button wcpfc-bestfit-skip-fee" data-index="' + index + '">' + escapeHtml( wcpfcBestfit.i18n.skipShort ) + '</button>' +
				'</div>'
			);

			$list.append( $card );
		} );

		renderRevenueTotals();
		$content.show();
		if ( opts.blinkSummary ) {
			requestAnimationFrame( function () {
				blinkSummaryNote();
			} );
		}
	}

	function setPayload( payload ) {
		state.aiAvailable = !! payload.ai_available;
		state.suggestions = ( payload.suggestions || [] ).filter( function ( item ) {
			if ( state.aiAvailable ) {
				return true;
			}
			return 'ai' !== item.powered_by && 'ai' !== item.suggestion_type;
		} );
		state.metrics = payload.metrics || {};
		state.source = payload.source || 'rules';
		state.storeCount = payload.store_count || 0;
		state.aiCount = state.aiAvailable ? ( payload.ai_count || 0 ) : 0;
		state.aiCached = state.aiAvailable && !! payload.ai_cached;
		state.revenueSummary = payload.revenue_summary || {};
		state.initialCount = state.suggestions.length;
		renderSuggestions( { blinkSummary: true } );
	}

	function replaceCount( template, count ) {
		return String( template || '' ).replace( '%d', count );
	}

	function buildScanSteps( preview ) {
		var i18n = wcpfcBestfit.i18n;
		var steps = [];
		var months = preview.period_months || 3;
		var orders = preview.order_count || 0;
		var categories = preview.category_count || 0;
		var products = preview.product_count || 0;

		if ( preview.has_orders && orders > 0 ) {
			steps.push( replaceCount( i18n.scanReadingOrders, months ) );
			steps.push( replaceCount( i18n.scanOrders, orders ) );
		} else {
			steps.push( i18n.scanReadingCatalog );
			if ( orders > 0 ) {
				steps.push( replaceCount( i18n.scanOrders, orders ) );
			}
		}

		if ( categories > 0 ) {
			steps.push( replaceCount( i18n.scanCategories, categories ) );
		}
		if ( products > 0 ) {
			steps.push( replaceCount( i18n.scanProducts, products ) );
		}

		steps.push( i18n.scanPatterns );
		steps.push( i18n.scanScoring );
		steps.push( i18n.scanCompetitor );
		steps.push( i18n.scanProjection );
		steps.push( i18n.scanFinalize );

		return steps;
	}

	function updateScanStats( preview ) {
		var i18n = wcpfcBestfit.i18n;
		var parts = [];
		var orders = preview.order_count || 0;
		var products = preview.product_count || 0;
		var categories = preview.category_count || 0;

		if ( orders > 0 ) {
			parts.push( replaceCount( i18n.scanStatOrders, orders ) );
		}
		if ( products > 0 ) {
			parts.push( replaceCount( i18n.scanStatProducts, products ) );
		}
		if ( categories > 0 ) {
			parts.push( replaceCount( i18n.scanStatCategories, categories ) );
		}

		$( '.wcpfc-bestfit-scan-stats' ).text( parts.join( ' · ' ) );
	}

	function updateScanStatusPill( label ) {
		var text = label || wcpfcBestfit.i18n.scanStatusDefault;
		$( '.wcpfc-bestfit-scan-status-text' ).text( text );
	}

	function clearScanTimer() {
		if ( loadingState.timer ) {
			clearTimeout( loadingState.timer );
			loadingState.timer = null;
		}
	}

	function updateScanProgress( current, total, label ) {
		var pct = total > 0 ? Math.min( 100, Math.round( ( current / total ) * 100 ) ) : 0;
		$( '.wcpfc-bestfit-scan-bar-fill' ).css( 'width', pct + '%' );
		$( '.wcpfc-bestfit-scan-bar-percent' ).text( pct + '%' );
		$( '.wcpfc-bestfit-scan-bar' ).attr( 'aria-valuenow', pct );
		if ( label ) {
			updateScanStatusPill( label );
		}
	}

	function hideScanProgress() {
		updateScanProgress( 1, 1, wcpfcBestfit.i18n.scanFinalize );
	}

	function trimScanSteps( $list ) {
		var $items = $list.children();
		if ( $items.length <= SCAN_MAX_VISIBLE ) {
			return;
		}
		var $first = $items.first();
		$first.addClass( 'is-exiting' );
		setTimeout( function () {
			$first.remove();
		}, 380 );
	}

	function resetScanUI( preview ) {
		clearScanTimer();
		var $scan = $( '.wcpfc-bestfit-scan' );
		$scan.attr( 'aria-busy', 'true' );
		$( '.wcpfc-bestfit-scan-steps' ).empty();
		$( '.wcpfc-bestfit-scan-progress' ).removeClass( 'is-hidden' );
		updateScanStats( preview || {} );
		updateScanProgress( 0, 1, wcpfcBestfit.i18n.scanStatusDefault );
	}

	function renderScanStep( $list, label, status ) {
		var $step = $( '<li class="wcpfc-bestfit-scan-step"></li>' );
		$step.append(
			'<span class="wcpfc-bestfit-scan-icon" aria-hidden="true"></span>' +
			'<span class="wcpfc-bestfit-scan-label"></span>'
		);
		$step.find( '.wcpfc-bestfit-scan-label' ).text( label );

		if ( 'active' === status ) {
			$step.addClass( 'is-active' );
		} else if ( 'done' === status ) {
			$step.addClass( 'is-done' );
		}

		$list.append( $step );
		requestAnimationFrame( function () {
			$step.addClass( 'is-visible' );
		} );

		trimScanSteps( $list );

		return $step;
	}

	function runLoadingSequence( preview ) {
		return new Promise( function ( resolve ) {
			var steps = buildScanSteps( preview || {} );
			var totalSteps = steps.length;
			var $list = $( '.wcpfc-bestfit-scan-steps' );
			var index = 0;
			var $activeStep = null;

			function markPreviousDone() {
				if ( $activeStep && $activeStep.length ) {
					$activeStep.removeClass( 'is-active' ).addClass( 'is-done' );
				}
			}

			function advance() {
				markPreviousDone();

				if ( index >= steps.length ) {
					updateScanProgress( totalSteps, totalSteps, steps[ totalSteps - 1 ] || wcpfcBestfit.i18n.scanFinalize );
					$( '.wcpfc-bestfit-scan' ).attr( 'aria-busy', 'false' );
					resolve();
					return;
				}

				updateScanProgress( index, totalSteps, steps[ index ] );
				$activeStep = renderScanStep( $list, steps[ index ], 'active' );
				index += 1;
				loadingState.timer = setTimeout( advance, SCAN_STEP_DURATION );
			}

			advance();
		} );
	}

	function fetchSuggestions() {
		var suggestPayload = null;
		var animationDone = false;
		var failed = false;

		function tryFinish() {
			if ( failed || ! suggestPayload || ! animationDone ) {
				return;
			}
			clearScanTimer();
			hideScanProgress();
			$( '.wcpfc-bestfit-loading' ).hide();
			setPayload( suggestPayload );
		}

		function startAnimation( preview ) {
			resetScanUI( preview );
			return runLoadingSequence( preview ).then( function () {
				animationDone = true;
				tryFinish();
			} );
		}

		runAbility( wcpfcBestfit.abilities.scan, 'GET' )
			.then( function ( preview ) {
				return startAnimation( preview );
			} )
			.catch( function () {
				return startAnimation( {
					has_orders: true,
					period_months: 3,
					order_count: 0,
					category_count: 0,
					product_count: 0,
				} );
			} );

		runAbility( wcpfcBestfit.abilities.suggest, 'GET' )
			.then( function ( payload ) {
				suggestPayload = payload;
				tryFinish();
			} )
			.catch( function () {
				failed = true;
				clearScanTimer();
				hideScanProgress();
				$( '.wcpfc-bestfit-loading' ).hide();
				$( '.wcpfc-bestfit-error' ).show().text( wcpfcBestfit.i18n.error );
			} );
	}

	function openFreemiusCheckout() {
		if ( typeof window.wcpfcOpenFreemiusCheckout === 'function' ) {
			window.wcpfcOpenFreemiusCheckout( $( '.upgrade-to-pro-discount-code' ).val() || '' );
			return true;
		}

		openPremiumUpgrade();
		return false;
	}

	function focusBestfitDialog( $modal, focusSelector ) {
		setTimeout( function () {
			if ( ! $modal.length || ! $modal.data( 'ui-dialog' ) ) {
				return;
			}

			var $widget = $modal.dialog( 'widget' );
			if ( $widget.length ) {
				$widget.attr( 'tabindex', -1 ).focus();
			}

			if ( focusSelector ) {
				var $target = $modal.find( focusSelector ).add( $widget.find( focusSelector ) ).filter( ':visible' ).first();
				if ( $target.length ) {
					$target.trigger( 'focus' );
				}
			}
		}, 0 );
	}

	function openFreeUpgradeModal() {
		var $modal = $( '#wcpfc-bestfit-free-modal' );
		if ( ! $modal.length ) {
			openPremiumUpgrade();
			return;
		}

		if ( ! $modal.data( 'ui-dialog' ) ) {
			$modal.dialog( {
				modal: true,
				dialogClass: 'wcpfc-bestfit-dialog wcpfc-bestfit-free-dialog',
				width: 780,
				maxWidth: 780,
				closeOnEscape: true,
				autoFocus: false,
				open: function () {
					$( '.ui-widget-overlay' ).last().addClass( 'wcpfc-bestfit-overlay' );
					focusBestfitDialog( $modal );
					$modal.find( '.wcpfc-bestfit-free-upgrade' ).blur();
				},
			} );
		} else {
			$modal.dialog( 'open' );
			focusBestfitDialog( $modal );
			$modal.find( '.wcpfc-bestfit-free-upgrade' ).blur();
		}
	}

	function closeFreeUpgradeModal() {
		var $modal = $( '#wcpfc-bestfit-free-modal' );
		if ( $modal.length && $modal.data( 'ui-dialog' ) ) {
			$modal.dialog( 'close' );
		}
	}

	function openPremiumUpgrade() {
		$( 'body' ).addClass( 'wcpfc-modal-visible' );
	}

	function openModal() {
		var $modal = $( '#wcpfc-bestfit-modal' );
		if ( ! $modal.length ) {
			return;
		}

		state.draftsAdded = 0;
		resetScanUI();
		$( '.wcpfc-bestfit-loading' ).show();
		$( '.wcpfc-bestfit-content' ).hide();
		$( '.wcpfc-bestfit-error' ).hide().empty();

		if ( ! $modal.data( 'ui-dialog' ) ) {
			$modal.dialog( {
				modal: true,
				dialogClass: 'wcpfc-bestfit-dialog wcpfc-bestfit-ai-dialog',
				width: 780,
				maxHeight: $( window ).height() * 0.9,
				closeOnEscape: true,
				autoFocus: true,
				open: function () {
					$( '.ui-widget-overlay' ).last().addClass( 'wcpfc-bestfit-overlay' );
					focusBestfitDialog( $modal, '.ui-dialog-titlebar-close' );
					fetchSuggestions();
				},
				close: function () {
					clearScanTimer();
					if ( state.draftsAdded > 0 ) {
						window.location.reload();
					}
				},
			} );
		} else {
			resetScanUI();
			$( '.wcpfc-bestfit-loading' ).show();
			$( '.wcpfc-bestfit-content' ).hide();
			$( '.wcpfc-bestfit-error' ).hide().empty();
			$modal.dialog( 'open' );
			focusBestfitDialog( $modal, '.ui-dialog-titlebar-close' );
			fetchSuggestions();
		}
	}

	function addSingleDraft( index ) {
		var item = state.suggestions[ index ];
		if ( ! item ) {
			return Promise.resolve();
		}

		var $card = $( '.wcpfc-bestfit-card[data-index="' + index + '"]' );
		if ( $card.hasClass( 'is-processing' ) ) {
			return Promise.resolve();
		}

		var $addBtn = $card.find( '.wcpfc-bestfit-add-fee' );
		var originalText = $addBtn.data( 'label' ) || wcpfcBestfit.i18n.addFeeShort;
		$addBtn.data( 'label', originalText );

		$card.addClass( 'is-processing' );
		$card.find( 'button, .wcpfc-bestfit-more-toggle' ).prop( 'disabled', true );
		setAddButtonAdding( $addBtn );

		var startedAt = Date.now();

		return runAbility( wcpfcBestfit.abilities.drafts, 'POST', { suggestions: [ item ] } )
			.then( function ( result ) {
				return waitForMinFeedback( startedAt ).then( function () {
					return result;
				} );
			} )
			.then( function ( result ) {
				if ( result.created && result.created.length ) {
					state.draftsAdded += 1;
					$addBtn.removeClass( 'is-adding' ).addClass( 'is-added' ).text( wcpfcBestfit.i18n.addedFee );
					$card.find( '.wcpfc-bestfit-skip-fee' ).hide();
					removeCardFromList( $card, item );
				} else {
					resetCardActions( $card, $addBtn, originalText );
					window.alert( wcpfcBestfit.i18n.draftSingleError );
				}
			} )
			.catch( function () {
				resetCardActions( $card, $addBtn, originalText );
				window.alert( wcpfcBestfit.i18n.draftSingleError );
			} );
	}

	function skipSuggestion( index ) {
		var $card = $( '.wcpfc-bestfit-card[data-index="' + index + '"]' );
		if ( $card.hasClass( 'is-processing' ) ) {
			return;
		}

		var item = state.suggestions[ index ];
		if ( ! item ) {
			return;
		}

		var $skipBtn = $card.find( '.wcpfc-bestfit-skip-fee' );
		var $addBtn = $card.find( '.wcpfc-bestfit-add-fee' );

		$card.addClass( 'is-processing' );
		$card.find( 'button, .wcpfc-bestfit-more-toggle' ).prop( 'disabled', true );
		$addBtn.hide();
		setSkipButtonSkipping( $skipBtn );

		delay( MIN_ACTION_FEEDBACK_MS ).then( function () {
			$skipBtn.removeClass( 'is-skipping' ).addClass( 'is-skipped' ).text( wcpfcBestfit.i18n.skippedFee );
			removeCardFromList( $card, item );
		} );
	}

	function draftAllRemaining() {
		if ( ! state.suggestions.length ) {
			return;
		}
		if ( ! window.confirm( wcpfcBestfit.i18n.confirmDraftAll ) ) {
			return;
		}

		var $btn = $( '#wcpfc_bestfit_draft_all' );
		var $label = $btn.find( '.wcpfc-bestfit-draft-all-label' );
		var $status = $( '.wcpfc-bestfit-draft-all-status' );
		var toCreate = state.suggestions.slice();
		var originalLabel = $label.text() || wcpfcBestfit.i18n.draftAllFee || 'Draft all fee';

		$btn.prop( 'disabled', true ).addClass( 'is-loading' ).removeClass( 'is-success' );
		$btn.find( '.wcpfc-bestfit-btn-spinner' ).remove();
		$btn.prepend( '<span class="wcpfc-bestfit-btn-spinner" aria-hidden="true"></span>' );
		$label.text( wcpfcBestfit.i18n.draftingAllFees || 'Creating drafts…' );
		$status.hide().removeClass( 'is-success is-error' ).empty();

		var startedAt = Date.now();
		var minWait = MIN_ACTION_FEEDBACK_MS;

		runAbility( wcpfcBestfit.abilities.drafts, 'POST', { suggestions: toCreate } )
			.then( function ( result ) {
				var created = result.created || [];
				var wait = Math.max( 0, minWait - ( Date.now() - startedAt ) );

				return delay( wait ).then( function () {
					if ( created.length ) {
						state.draftsAdded += created.length;
						state.suggestions = [];
						$btn.removeClass( 'is-loading' ).addClass( 'is-success' );
						$btn.find( '.wcpfc-bestfit-btn-spinner' ).remove();
						$label.text( wcpfcBestfit.i18n.draftsCreatedSuccess || 'Drafts created' );
						$status
							.addClass( 'is-success' )
							.text(
								( wcpfcBestfit.i18n.draftsCreated || '%d draft fees created.' ).replace( '%d', created.length )
							)
							.show();

						return delay( 1200 ).then( function () {
							window.location.reload();
						} );
					}

					$btn.prop( 'disabled', false ).removeClass( 'is-loading is-success' );
					$btn.find( '.wcpfc-bestfit-btn-spinner' ).remove();
					$label.text( originalLabel );
					$status.addClass( 'is-error' ).text( wcpfcBestfit.i18n.draftsError ).show();
				} );
			} )
			.catch( function () {
				var wait = Math.max( 0, minWait - ( Date.now() - startedAt ) );
				return delay( wait ).then( function () {
					$btn.prop( 'disabled', false ).removeClass( 'is-loading is-success' );
					$btn.find( '.wcpfc-bestfit-btn-spinner' ).remove();
					$label.text( originalLabel );
					$status.addClass( 'is-error' ).text( wcpfcBestfit.i18n.draftsError ).show();
				} );
			} );
	}

	$( document ).on( 'click', '#wcpfc_find_best_fits_btn', function ( e ) {
		e.preventDefault();
		if ( wcpfcBestfit.canUseBestFit ) {
			openModal();
		} else {
			openFreeUpgradeModal();
		}
	} );

	$( document ).on( 'click', '#wcpfc-bestfit-free-modal .wcpfc-bestfit-free-upgrade', function ( e ) {
		e.preventDefault();
		e.stopImmediatePropagation();
		closeFreeUpgradeModal();
		openFreemiusCheckout();
	} );

	$( document ).on( 'click', '.wcpfc-bestfit-add-fee', function () {
		addSingleDraft( parseInt( $( this ).data( 'index' ), 10 ) );
	} );

	$( document ).on( 'click', '.wcpfc-bestfit-skip-fee', function () {
		skipSuggestion( parseInt( $( this ).data( 'index' ), 10 ) );
	} );

	$( document ).on( 'click', '#wcpfc_bestfit_draft_all', function () {
		draftAllRemaining();
	} );

	$( document ).on( 'click', '.wcpfc-bestfit-more-toggle', function ( e ) {
		e.preventDefault();
		var $wrap = $( this ).closest( '.wcpfc-bestfit-more' );
		var isOpen = $wrap.hasClass( 'is-open' );
		$wrap.toggleClass( 'is-open', ! isOpen );
		$( this ).attr( 'aria-expanded', isOpen ? 'false' : 'true' );
	} );

	$( document ).on( 'click', '.wcpfc-bestfit-tag-remove', function ( e ) {
		e.preventDefault();
		e.stopPropagation();

		var $btn = $( this );
		var feeId = parseInt( $btn.data( 'fee-id' ), 10 );
		var $tag = $btn.closest( '.wcpfc-bestfit-tag' );

		if ( ! feeId || $btn.prop( 'disabled' ) ) {
			return;
		}

		$btn.prop( 'disabled', true );

		$.post( wcpfcBestfit.ajaxurl, {
			action: 'wcpfc_remove_bestfit_tag',
			security: wcpfcBestfit.removeTagNonce,
			fee_id: feeId,
		} )
			.done( function ( response ) {
				if ( response && response.success ) {
					$tag.remove();
					return;
				}
				window.alert( wcpfcBestfit.i18n.removeTagError );
				$btn.prop( 'disabled', false );
			} )
			.fail( function () {
				window.alert( wcpfcBestfit.i18n.removeTagError );
				$btn.prop( 'disabled', false );
			} );
	} );
}( jQuery ) );
