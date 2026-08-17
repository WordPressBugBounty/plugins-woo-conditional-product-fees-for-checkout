<?php
/**
 * Generates Smart Fee Recommendations (rules + optional AI).
 *
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fee suggestion engine.
 */
class WCPFC_Fee_Suggestion_Engine {

	const STORE_SUGGESTION_LIMIT = 5;
	const AI_SUGGESTION_LIMIT    = 3;
	const SUGGESTION_LIMIT       = 8;
	const AI_CACHE_PREFIX        = 'wcpfc_bestfit_ai_';

	/**
	 * Build suggestions from store metrics.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_suggestions() {
		$metrics = WCPFC_Store_Analytics::collect();

		if ( isset( $metrics['error'] ) ) {
			return array(
				'suggestions'     => array(),
				'metrics'         => $metrics,
				'source'          => 'none',
				'store_count'     => 0,
				'ai_count'        => 0,
				'ai_cached'       => false,
				'revenue_summary' => self::empty_revenue_summary(),
			);
		}

		$existing_fees     = WCPFC_Existing_Fees_Registry::collect();
		$rule_based        = self::build_rule_suggestions( $metrics );
		$store_suggestions = WCPFC_Existing_Fees_Registry::select_unique_suggestions(
			self::tag_powered_by( $rule_based, 'store_data' ),
			$existing_fees,
			self::STORE_SUGGESTION_LIMIT
		);

		$ai_available   = self::is_ai_available();
		$ai_context     = array_merge(
			$existing_fees,
			array_map( array( 'WCPFC_Existing_Fees_Registry', 'normalize_suggestion' ), $store_suggestions )
		);
		$ai_result      = self::get_ai_research_suggestions( $metrics, $store_suggestions, $ai_available, $ai_context );
		$ai_suggestions = $ai_result['suggestions'];
		$suggestions     = array_merge( $store_suggestions, $ai_suggestions );
		$suggestions     = self::ensure_insights( $suggestions, $metrics );

		return array(
			'suggestions'     => $suggestions,
			'metrics'         => $metrics,
			'source'          => self::resolve_source( $store_suggestions, $ai_suggestions ),
			'store_count'     => count( $store_suggestions ),
			'ai_count'        => count( $ai_suggestions ),
			'ai_available'    => $ai_available,
			'ai_cached'       => $ai_available && ! empty( $ai_result['cached'] ),
			'revenue_summary' => self::build_revenue_summary( $suggestions, $metrics ),
		);
	}

	/**
	 * Whether WordPress AI connectors are available for Best Fit research.
	 *
	 * @return bool
	 */
	public static function is_ai_available() {
		$available = function_exists( 'wp_supports_ai' )
			&& wp_supports_ai()
			&& function_exists( 'wp_ai_client_prompt' )
			&& ( ! function_exists( 'wcpfc_has_ai_provider_configured' ) || wcpfc_has_ai_provider_configured() );

		return (bool) apply_filters( 'wcpfc_bestfit_ai_available', $available );
	}

	/**
	 * @param array<int, array<string, mixed>> $store_suggestions Store suggestions.
	 * @param array<int, array<string, mixed>> $ai_suggestions    AI suggestions.
	 * @return string
	 */
	private static function resolve_source( $store_suggestions, $ai_suggestions ) {
		$has_store = ! empty( $store_suggestions );
		$has_ai    = ! empty( $ai_suggestions );

		if ( $has_store && $has_ai ) {
			return 'mixed';
		}
		if ( $has_ai ) {
			return 'hybrid';
		}
		if ( $has_store ) {
			return 'rules';
		}

		return 'none';
	}

	/**
	 * @param array<int, array<string, mixed>> $suggestions Suggestions.
	 * @param string                           $powered_by  Badge source key.
	 * @return array<int, array<string, mixed>>
	 */
	private static function tag_powered_by( $suggestions, $powered_by ) {
		foreach ( $suggestions as $index => $suggestion ) {
			$suggestions[ $index ]['powered_by'] = $powered_by;
		}

		return $suggestions;
	}

	/**
	 * Daily cache key for AI research suggestions.
	 *
	 * @return string
	 */
	private static function get_ai_cache_key() {
		return self::AI_CACHE_PREFIX . get_current_blog_id() . '_' . gmdate( 'Y-m-d' );
	}

	/**
	 * Fetch AI research fees (cached once per site per day).
	 *
	 * @param array<string, mixed>             $metrics           Metrics.
	 * @param array<int, array<string, mixed>> $store_suggestions Existing store fees.
	 * @param bool                             $ai_available      Whether AI connectors are active.
	 * @param array<int, array<string, mixed>> $dedupe_context    Existing + selected store fees.
	 * @return array{suggestions: array<int, array<string, mixed>>, cached: bool}
	 */
	private static function get_ai_research_suggestions( $metrics, $store_suggestions, $ai_available = true, $dedupe_context = array() ) {
		if ( ! $ai_available ) {
			self::clear_ai_cache();

			return array(
				'suggestions' => array(),
				'cached'      => false,
			);
		}

		$cache_key   = self::get_ai_cache_key();
		$use_cache   = (bool) apply_filters( 'wcpfc_bestfit_use_ai_cache', true );
		$clear_cache = (bool) apply_filters( 'wcpfc_bestfit_clear_ai_cache', false );

		if ( $clear_cache ) {
			self::clear_ai_cache();
		}

		$tagged     = array();
		$from_cache = false;

		if ( $use_cache && ! $clear_cache ) {
			$cached = get_transient( $cache_key );

			if ( is_array( $cached ) && ! empty( $cached ) ) {
				$tagged     = self::tag_powered_by( $cached, 'ai' );
				$from_cache = true;
			}
		}

		if ( empty( $tagged ) ) {
			$generated = self::generate_ai_research_suggestions( $metrics, $store_suggestions, $dedupe_context );
			$tagged    = self::tag_powered_by( $generated, 'ai' );

			if ( $use_cache && ! empty( $tagged ) ) {
				set_transient( $cache_key, $tagged, DAY_IN_SECONDS );
			}
		}

		$unique = WCPFC_Existing_Fees_Registry::select_unique_suggestions(
			$tagged,
			$dedupe_context,
			self::AI_SUGGESTION_LIMIT
		);

		return array(
			'suggestions' => $unique,
			'cached'      => $from_cache,
		);
	}

	/**
	 * Delete cached AI research suggestions for the current site.
	 *
	 * @return bool
	 */
	public static function clear_ai_cache() {
		$blog_id = get_current_blog_id();

		for ( $day = 0; $day < 2; $day++ ) {
			$date = gmdate( 'Y-m-d', strtotime( '-' . $day . ' days' ) );
			delete_transient( self::AI_CACHE_PREFIX . $blog_id . '_' . $date );
		}

		return true;
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function empty_revenue_summary() {
		return array(
			'monthly_total'       => 0,
			'three_month_total'   => 0,
			'monthly_formatted'   => wc_price( 0 ),
			'three_month_formatted' => wc_price( 0 ),
		);
	}

	/**
	 * @param array<int, array<string, mixed>> $suggestions Suggestions.
	 * @param array<string, mixed>             $metrics     Metrics.
	 * @return array<string, mixed>
	 */
	private static function build_revenue_summary( $suggestions, $metrics ) {
		$monthly = 0.0;
		foreach ( $suggestions as $s ) {
			$monthly += (float) ( $s['revenue_monthly'] ?? 0 );
		}
		$three = $monthly * 3;

		return array(
			'monthly_total'         => round( $monthly, 2 ),
			'three_month_total'     => round( $three, 2 ),
			'monthly_formatted'     => wc_price( $monthly ),
			'three_month_formatted' => wc_price( $three ),
			'monthly_orders'        => (int) ( $metrics['monthly_orders'] ?? 0 ),
		);
	}

	/**
	 * Rule-based suggestions from metrics.
	 *
	 * @param array<string, mixed> $metrics Metrics.
	 * @return array<int, array<string, mixed>>
	 */
	private static function build_rule_suggestions( $metrics ) {
		$suggestions = array();
		$currency    = $metrics['currency'] ?? get_woocommerce_currency();
		$symbol      = wp_specialchars_decode( (string) ( $metrics['currency_symbol'] ?? get_woocommerce_currency_symbol() ), ENT_QUOTES );
		$aov         = (float) ( $metrics['aov'] ?? 0 );
		$has_orders  = ! empty( $metrics['has_orders'] );
		$monthly     = max( 1, (int) ( $metrics['monthly_orders'] ?? 1 ) );

		if ( $has_orders && $aov > 0 ) {
			$threshold = (int) ( $metrics['small_order_threshold'] ?? max( 10, round( $aov * 0.45 ) ) );
			$pct       = (int) ( $metrics['orders_below_threshold_pct'] ?? 0 );
			$below     = (int) ( $metrics['orders_below_threshold'] ?? 0 );
			$amount    = self::suggest_fixed_amount( max( 2.5, $aov * 0.02 ), 2 );
			$qualifying_monthly = max( 1, (int) round( ( $below / max( 1, (int) $metrics['order_count'] ) ) * $monthly ) );
			$fee_revenue = $qualifying_monthly * (float) $amount;
			$confidence  = min( 98, max( 72, 68 + $pct / 2 ) );

			$suggestions[] = self::make_suggestion(
				array(
					'title'            => __( 'Small Order Handling Fee', 'woocommerce-conditional-product-fees-for-checkout' ),
					'amount'           => $amount,
					'fee_type'         => 'fixed',
					'suggestion_type'  => 'small_order',
					'confidence'       => $confidence,
					'reason'           => sprintf(
						/* translators: 1: percent, 2: currency symbol, 3: threshold amount */
						__( '%1$d%% of orders in the last 90 days had a cart subtotal below %2$s%3$s. Low-value purchases often reduce profitability because packaging, payment processing, and fulfillment costs stay similar regardless of order size.', 'woocommerce-conditional-product-fees-for-checkout' ),
						$pct,
						$symbol,
						number_format_i18n( $threshold, 0 )
					),
					'how_it_helps'     => __( 'Low-value orders frequently erode margin after shipping labels, pick-pack labor, and gateway fees. A targeted small-order fee recovers handling costs without affecting larger, healthier baskets.', 'woocommerce-conditional-product-fees-for-checkout' ),
					'revenue_breakdown'=> sprintf(
						/* translators: 1: monthly orders, 2: percent, 3: qualifying orders, 4: fee amount, 5: monthly revenue */
						__( 'Additional revenue: %1$s orders/month × %2$d%% below threshold ≈ %3$s qualifying orders × %4$s%5$s fee ≈ %6$s/month', 'woocommerce-conditional-product-fees-for-checkout' ),
						number_format_i18n( $monthly ),
						$pct,
						number_format_i18n( $qualifying_monthly ),
						$symbol,
						number_format_i18n( (float) $amount, 2 ),
						self::format_plain_price( $fee_revenue, $symbol )
					),
					'revenue_monthly'  => $fee_revenue,
					'qualifying_orders'=> $qualifying_monthly,
					'conditions'       => array(
						self::condition_row( 'cart_total', 'less_then', (string) $threshold, sprintf( __( 'Cart subtotal below %1$s%2$s', 'woocommerce-conditional-product-fees-for-checkout' ), $symbol, number_format_i18n( $threshold, 0 ) ) ),
					),
				)
			);
		}

		if ( $has_orders && ! empty( $metrics['top_countries'] ) ) {
			foreach ( array_slice( (array) $metrics['top_countries'], 0, 5 ) as $country ) {
				$share       = (int) ( $country['share_pct'] ?? 0 );
				$amount      = self::suggest_fixed_amount( max( 3, $aov * 0.03 ), 2 );
				$qualifying  = max( 1, (int) round( $monthly * ( $share / 100 ) ) );
				$fee_revenue = $qualifying * (float) $amount;
				$confidence  = min( 96, max( 75, 70 + $share / 2 ) );

				$suggestions[] = self::make_suggestion(
					array(
						'title'           => sprintf(
							/* translators: %s: country name */
							__( 'International Handling – %s', 'woocommerce-conditional-product-fees-for-checkout' ),
							$country['label']
						),
						'amount'          => $amount,
						'fee_type'        => 'fixed',
						'suggestion_type' => 'country',
						'confidence'      => $confidence,
						'reason'          => sprintf(
							/* translators: 1: country, 2: percent, 3: order count */
							__( '%1$s represents %2$d%% of your orders (%3$s orders in 90 days). Cross-border or regional orders often carry higher shipping, customs documentation, or carrier surcharges that standard product pricing does not cover.', 'woocommerce-conditional-product-fees-for-checkout' ),
							$country['label'],
							$share,
							number_format_i18n( (int) $country['count'] )
						),
						'how_it_helps'    => __( 'A country-specific fee aligns checkout charges with real delivery cost differences and reduces subsidizing distant customers from domestic margin.', 'woocommerce-conditional-product-fees-for-checkout' ),
						'revenue_breakdown' => sprintf(
							__( '%1$s orders/month from %2$s (%3$d%%) × %4$s%5$s ≈ %6$s/month', 'woocommerce-conditional-product-fees-for-checkout' ),
							number_format_i18n( $monthly ),
							$country['label'],
							$share,
							$symbol,
							number_format_i18n( (float) $amount, 2 ),
							self::format_plain_price( $fee_revenue, $symbol )
						),
						'revenue_monthly' => $fee_revenue,
						'qualifying_orders' => $qualifying,
						'conditions'      => array(
							self::condition_row( 'country', 'is_equal_to', array( $country['code'] ), sprintf( __( 'Country is %s', 'woocommerce-conditional-product-fees-for-checkout' ), $country['label'] ) ),
						),
					)
				);
			}
		}

		if ( ! empty( $metrics['top_products'] ) ) {
			foreach ( array_slice( (array) $metrics['top_products'], 0, 5 ) as $product ) {
				$pid         = (int) ( $product['id'] ?? 0 );
				$share       = (int) ( $product['share_pct'] ?? ( $has_orders ? 25 : 10 ) );
				$amount      = self::suggest_fixed_amount( max( 2, $aov > 0 ? $aov * 0.025 : 5 ), 2 );
				$qualifying  = max( 1, (int) round( $monthly * ( $share / 100 ) ) );
				$fee_revenue = $qualifying * (float) $amount;
				$confidence  = min( 94, max( 70, 65 + $share / 2 ) );

				if ( $pid <= 0 ) {
					continue;
				}

				$suggestions[] = self::make_suggestion(
					array(
						'title'           => sprintf(
							/* translators: %s: product name */
							__( 'Best-Seller Handling – %s', 'woocommerce-conditional-product-fees-for-checkout' ),
							$product['name'] ?? __( 'Top product', 'woocommerce-conditional-product-fees-for-checkout' )
						),
						'amount'          => $amount,
						'fee_type'        => 'fixed',
						'suggestion_type' => 'product',
						'confidence'      => $confidence,
						'reason'          => sprintf(
							/* translators: 1: product name, 2: percent, 3: revenue */
							__( '“%1$s” appears in %2$d%% of recent orders and generated %3$s in line-item revenue. High-velocity SKUs increase pick frequency, packaging wear, and stock-out risk—costs that a flat catalog price rarely reflects.', 'woocommerce-conditional-product-fees-for-checkout' ),
							$product['name'] ?? '',
							$share,
							self::format_plain_price( (float) ( $product['revenue'] ?? 0 ), $symbol )
						),
						'how_it_helps'    => __( 'Product-specific fees let you protect margin on hero SKUs while keeping other items competitively priced.', 'woocommerce-conditional-product-fees-for-checkout' ),
						'revenue_breakdown' => sprintf(
							__( '%1$s orders/month with this product (%2$d%%) × %3$s%4$s ≈ %5$s/month', 'woocommerce-conditional-product-fees-for-checkout' ),
							number_format_i18n( $monthly ),
							$share,
							$symbol,
							number_format_i18n( (float) $amount, 2 ),
							self::format_plain_price( $fee_revenue, $symbol )
						),
						'revenue_monthly' => $fee_revenue,
						'qualifying_orders' => $qualifying,
						'conditions'      => array(
							self::condition_row( 'product', 'is_equal_to', array( (string) $pid ), sprintf( __( 'Cart contains product: %s', 'woocommerce-conditional-product-fees-for-checkout' ), $product['name'] ?? $pid ) ),
						),
					)
				);
			}
		}

		if ( ! empty( $metrics['top_payments'] ) ) {
			foreach ( array_slice( (array) $metrics['top_payments'], 0, 5 ) as $payment ) {
				$share       = (int) ( $payment['share_pct'] ?? 0 );
				$amount      = self::suggest_fixed_amount( max( 1.5, $aov > 0 ? $aov * 0.015 : 3 ), 2 );
				$qualifying  = max( 1, (int) round( $monthly * ( $share / 100 ) ) );
				$fee_revenue = $qualifying * (float) $amount;
				$confidence  = min( 92, max( 68, 62 + $share / 2 ) );

				$suggestions[] = self::make_suggestion(
					array(
						'title'           => sprintf(
							/* translators: %s: payment method */
							__( 'Payment Method Fee – %s', 'woocommerce-conditional-product-fees-for-checkout' ),
							$payment['title']
						),
						'amount'          => $amount,
						'fee_type'        => 'fixed',
						'suggestion_type' => 'payment',
						'confidence'      => $confidence,
						'reason'          => sprintf(
							/* translators: 1: payment title, 2: percent, 3: count */
							__( '%1$s is used on %2$d%% of completed orders (%3$s in 90 days). Some gateways charge higher interchange, FX, or manual reconciliation effort that your product margin may not absorb.', 'woocommerce-conditional-product-fees-for-checkout' ),
							$payment['title'],
							$share,
							number_format_i18n( (int) $payment['count'] )
						),
						'how_it_helps'    => __( 'Passing through a modest payment-method surcharge keeps net revenue closer to true cost-to-serve per checkout choice.', 'woocommerce-conditional-product-fees-for-checkout' ),
						'revenue_breakdown' => sprintf(
							__( '%1$s %2$s checkouts/month × %3$s%4$s ≈ %5$s/month', 'woocommerce-conditional-product-fees-for-checkout' ),
							number_format_i18n( $qualifying ),
							$payment['title'],
							$symbol,
							number_format_i18n( (float) $amount, 2 ),
							self::format_plain_price( $fee_revenue, $symbol )
						),
						'revenue_monthly' => $fee_revenue,
						'qualifying_orders' => $qualifying,
						'conditions'      => array(
							self::condition_row( 'payment', 'is_equal_to', array( $payment['id'] ), sprintf( __( 'Payment method is %s', 'woocommerce-conditional-product-fees-for-checkout' ), $payment['title'] ) ),
						),
					)
				);
			}
		} elseif ( ! empty( $metrics['payment_gateways'] ) ) {
			foreach ( array_slice( (array) $metrics['payment_gateways'], 0, 3 ) as $gateway ) {
				$amount     = '2.50';
				$qualifying = max( 1, (int) round( $monthly * 0.4 ) );
				$suggestions[] = self::make_suggestion(
					array(
						'title'           => sprintf(
							__( 'Payment Setup Fee – %s', 'woocommerce-conditional-product-fees-for-checkout' ),
							$gateway['title']
						),
						'amount'          => $amount,
						'fee_type'        => 'fixed',
						'suggestion_type' => 'payment',
						'confidence'      => 62,
						'reason'          => __( 'No completed orders in the last 90 days. This recommendation is based on your enabled payment gateway and published catalog—adjust amounts once live order data is available.', 'woocommerce-conditional-product-fees-for-checkout' ),
						'how_it_helps'    => __( 'Prepares a payment-method fee template so you can recover processing costs from day one of sales.', 'woocommerce-conditional-product-fees-for-checkout' ),
						'revenue_breakdown' => sprintf(
							__( 'Estimated %1$s checkouts/month × %2$s%3$s ≈ %4$s/month (projected)', 'woocommerce-conditional-product-fees-for-checkout' ),
							number_format_i18n( $qualifying ),
							$symbol,
							$amount,
							self::format_plain_price( $qualifying * (float) $amount, $symbol )
						),
						'revenue_monthly' => $qualifying * (float) $amount,
						'qualifying_orders' => $qualifying,
						'conditions'      => array(
							self::condition_row( 'payment', 'is_equal_to', array( $gateway['id'] ), sprintf( __( 'Payment method is %s', 'woocommerce-conditional-product-fees-for-checkout' ), $gateway['title'] ) ),
						),
					)
				);
			}
		}

		if ( ! empty( $metrics['top_categories'] ) ) {
			foreach ( array_slice( (array) $metrics['top_categories'], 0, 5 ) as $category ) {
				$cat_id      = (int) ( $category['id'] ?? 0 );
				$share       = (int) ( $category['share_pct'] ?? 20 );
				$pct_amount  = (string) round( min( 5, max( 1.5, 2.5 ) ), 2 );
				$qualifying  = max( 1, (int) round( $monthly * ( $share / 100 ) ) );
				$avg_basket  = $aov > 0 ? $aov : 25;
				$fee_revenue = $qualifying * $avg_basket * ( (float) $pct_amount / 100 );

				if ( $cat_id <= 0 ) {
					continue;
				}

				$suggestions[] = self::make_suggestion(
					array(
						'title'           => sprintf(
							__( 'Category Service Fee – %s', 'woocommerce-conditional-product-fees-for-checkout' ),
							$category['name']
						),
						'amount'          => $pct_amount,
						'fee_type'        => 'percentage',
						'suggestion_type' => 'category',
						'confidence'      => min( 90, max( 65, 60 + $share / 2 ) ),
						'reason'          => sprintf(
							__( 'The “%1$s” category appears in %2$d%% of orders. Category-level surcharges work well when certain product lines need extra packaging, compliance checks, or supplier handling.', 'woocommerce-conditional-product-fees-for-checkout' ),
							$category['name'],
							$share
						),
						'how_it_helps'    => __( 'A percentage fee scales with basket value so high-ticket category orders contribute proportionally more revenue.', 'woocommerce-conditional-product-fees-for-checkout' ),
						'revenue_breakdown' => sprintf(
							__( '%1$s category orders/month × %2$s%% avg fee on ~%3$s AOV ≈ %4$s/month', 'woocommerce-conditional-product-fees-for-checkout' ),
							number_format_i18n( $qualifying ),
							$pct_amount,
							self::format_plain_price( $aov > 0 ? $aov : 25, $symbol ),
							self::format_plain_price( $fee_revenue, $symbol )
						),
						'revenue_monthly' => $fee_revenue,
						'qualifying_orders' => $qualifying,
						'conditions'      => array(
							self::condition_row( 'category', 'is_equal_to', array( (string) $cat_id ), sprintf( __( 'Cart contains category: %s', 'woocommerce-conditional-product-fees-for-checkout' ), $category['name'] ) ),
						),
					)
				);
			}
		}

		if ( $has_orders && $aov > 0 ) {
			$high        = (int) ( $metrics['high_order_threshold'] ?? round( $aov * 1.35, 0 ) );
			$pct         = (int) ( $metrics['orders_above_high_pct'] ?? 15 );
			$pct_amount  = (string) round( min( 4, max( 2, 2.5 ) ), 2 );
			$qualifying  = max( 1, (int) round( $monthly * ( $pct / 100 ) ) );
			$fee_revenue = $qualifying * $high * ( (float) $pct_amount / 100 );

			$suggestions[] = self::make_suggestion(
				array(
					'title'           => __( 'Premium Order Handling', 'woocommerce-conditional-product-fees-for-checkout' ),
					'amount'          => $pct_amount,
					'fee_type'        => 'percentage',
					'suggestion_type' => 'premium_order',
					'confidence'      => min( 88, max( 70, 65 + $pct / 3 ) ),
					'reason'          => sprintf(
						__( '%1$d%% of orders exceed %2$s%3$s. High-value baskets often justify white-glove packing, insurance, or priority fulfillment that you can monetize as an optional premium service.', 'woocommerce-conditional-product-fees-for-checkout' ),
						$pct,
						$symbol,
						number_format_i18n( $high, 0 )
					),
					'how_it_helps'    => __( 'Turns elevated service expectations on large orders into incremental revenue instead of silent operational cost.', 'woocommerce-conditional-product-fees-for-checkout' ),
					'revenue_breakdown' => sprintf(
						__( '%1$s premium orders/month × %2$s%% on ~%3$s%4$s baskets ≈ %5$s/month', 'woocommerce-conditional-product-fees-for-checkout' ),
						number_format_i18n( $qualifying ),
						$pct_amount,
						$symbol,
						number_format_i18n( $high, 0 ),
						self::format_plain_price( $fee_revenue, $symbol )
					),
					'revenue_monthly' => $fee_revenue,
					'qualifying_orders' => $qualifying,
					'conditions'      => array(
						self::condition_row( 'cart_total', 'greater_then', (string) $high, sprintf( __( 'Cart subtotal above %1$s%2$s', 'woocommerce-conditional-product-fees-for-checkout' ), $symbol, number_format_i18n( $high, 0 ) ) ),
					),
				)
			);
		}

		if ( empty( $suggestions ) && ! empty( $metrics['top_products'][0] ) ) {
			$qualifying = max( 10, (int) round( $monthly * 0.5 ) );
			$suggestions[] = self::make_suggestion(
				array(
					'title'           => __( 'Catalog Handling Fee', 'woocommerce-conditional-product-fees-for-checkout' ),
					'amount'          => '4.99',
					'fee_type'        => 'fixed',
					'suggestion_type' => 'catalog',
					'confidence'      => 58,
					'reason'          => __( 'Limited sales history available. This starter fee is based on your published catalog size and typical handling costs for new stores.', 'woocommerce-conditional-product-fees-for-checkout' ),
					'how_it_helps'    => __( 'Provides a baseline checkout fee you can refine once real order patterns emerge.', 'woocommerce-conditional-product-fees-for-checkout' ),
					'revenue_breakdown' => sprintf(
						__( 'Estimated %1$s orders/month × %2$s%3$s ≈ %4$s/month (projected)', 'woocommerce-conditional-product-fees-for-checkout' ),
						number_format_i18n( $qualifying ),
						$symbol,
						'4.99',
						self::format_plain_price( $qualifying * 4.99, $symbol )
					),
					'revenue_monthly' => $qualifying * 4.99,
					'qualifying_orders' => $qualifying,
					'conditions'      => array(
						self::condition_row( 'quantity', 'greater_equal_to', '1', __( 'Cart quantity at least 1', 'woocommerce-conditional-product-fees-for-checkout' ) ),
					),
				)
			);
		}

		return $suggestions;
	}

	/**
	 * Fill missing insight fields (e.g. after AI).
	 *
	 * @param array<int, array<string, mixed>> $suggestions Suggestions.
	 * @param array<string, mixed>             $metrics     Metrics.
	 * @return array<int, array<string, mixed>>
	 */
	private static function ensure_insights( $suggestions, $metrics ) {
		foreach ( $suggestions as $i => $s ) {
			if ( empty( $s['confidence'] ) ) {
				$suggestions[ $i ]['confidence'] = 75;
			}
			if ( empty( $s['reason'] ) && ! empty( $s['rationale'] ) ) {
				$suggestions[ $i ]['reason'] = $s['rationale'];
			}
			if ( empty( $s['how_it_helps'] ) ) {
				$suggestions[ $i ]['how_it_helps'] = __( 'This fee aligns checkout pricing with your store’s order patterns to recover cost-to-serve and improve net margin.', 'woocommerce-conditional-product-fees-for-checkout' );
			}
			if ( empty( $s['revenue_monthly'] ) ) {
				$monthly = max( 1, (int) ( $metrics['monthly_orders'] ?? 1 ) );
				$amount  = (float) ( $s['amount'] ?? 0 );
				if ( 'percentage' === ( $s['fee_type'] ?? 'fixed' ) ) {
					$aov = (float) ( $metrics['aov'] ?? 25 );
					$suggestions[ $i ]['revenue_monthly'] = $monthly * 0.2 * $aov * ( $amount / 100 );
				} else {
					$suggestions[ $i ]['revenue_monthly'] = $monthly * 0.2 * $amount;
				}
			}
			if ( empty( $s['revenue_breakdown'] ) ) {
				$monthly_rev = (float) $suggestions[ $i ]['revenue_monthly'];
				$suggestions[ $i ]['revenue_breakdown'] = sprintf(
					__( 'Estimated additional revenue ≈ %1$s/month', 'woocommerce-conditional-product-fees-for-checkout' ),
					self::format_plain_price( $monthly_rev, $metrics['currency_symbol'] ?? '' )
				);
			}
			if ( ! empty( $suggestions[ $i ]['revenue_breakdown'] ) ) {
				$suggestions[ $i ]['revenue_breakdown'] = self::plain_text( $suggestions[ $i ]['revenue_breakdown'] );
			}
			if ( ! empty( $suggestions[ $i ]['reason'] ) ) {
				$suggestions[ $i ]['reason'] = self::plain_text( $suggestions[ $i ]['reason'] );
			}
			if ( ! empty( $suggestions[ $i ]['conditions'] ) && is_array( $suggestions[ $i ]['conditions'] ) ) {
				foreach ( $suggestions[ $i ]['conditions'] as $ci => $condition ) {
					if ( 'ai' === ( $s['powered_by'] ?? '' ) || self::should_rebuild_condition_label( $condition ) ) {
						$suggestions[ $i ]['conditions'][ $ci ]['label'] = self::format_condition_label(
							(string) ( $condition['condition'] ?? '' ),
							(string) ( $condition['operator'] ?? 'is_equal_to' ),
							$condition['values'] ?? '',
							$metrics
						);
					} elseif ( ! empty( $condition['label'] ) ) {
						$suggestions[ $i ]['conditions'][ $ci ]['label'] = self::plain_text( $condition['label'] );
					}
				}
			}
			$suggestions[ $i ]['revenue_3_month'] = round( (float) $suggestions[ $i ]['revenue_monthly'] * 3, 2 );
			$suggestions[ $i ]['revenue_3_month_formatted'] = wc_price( $suggestions[ $i ]['revenue_3_month'] );
			$suggestions[ $i ]['revenue_monthly_formatted'] = wc_price( $suggestions[ $i ]['revenue_monthly'] );
			if ( empty( $s['copy_text'] ) ) {
				$suggestions[ $i ]['copy_text'] = self::format_copy_text( $suggestions[ $i ] );
			}
		}
		return $suggestions;
	}

	/**
	 * Generate AI competitor/market research fees (not cached here).
	 *
	 * @param array<string, mixed>             $metrics           Metrics.
	 * @param array<int, array<string, mixed>> $store_suggestions Store-based fees already selected.
	 * @param array<int, array<string, mixed>> $dedupe_context    Existing created fees + selected store fees.
	 * @return array<int, array<string, mixed>>
	 */
	private static function generate_ai_research_suggestions( $metrics, $store_suggestions, $dedupe_context = array() ) {
		if ( ! self::is_ai_available() ) {
			return array();
		}

		$store_titles = array_values(
			array_filter(
				array_map(
					static function ( $item ) {
						return isset( $item['title'] ) ? (string) $item['title'] : '';
					},
					$store_suggestions
				)
			)
		);

		$existing_created = WCPFC_Existing_Fees_Registry::summarize_for_prompt(
			array_filter(
				(array) $dedupe_context,
				static function ( $fee ) {
					return ! empty( $fee['id'] );
				}
			)
		);

		$prompt = 'You are a WooCommerce revenue strategist and competitive pricing analyst. '
			. 'Using the store metrics JSON below, research typical competitor checkout fees and current ecommerce market practices for this store profile. '
			. 'Return EXACTLY 3 NEW extra-fee recommendations as a JSON array (no markdown, no commentary). '
			. 'These 3 fees must be different from existing_created_fees and existing_fees—do not duplicate titles, fee types, or condition logic. '
			. 'If a fee logic already exists in the store, propose a different condition, audience, or use case. '
			. 'Focus on innovative, practical fees that help grow revenue (e.g. sustainability, rush handling, membership tiers, peak-season surcharges, B2B invoicing, gift wrapping, compliance, loyalty recovery) grounded in the store data. '
			. 'Each condition label must be a complete human-readable rule (e.g. "Cart quantity >= 21", "Cart subtotal < $50", "Payment method is PayPal")—never only the field name. '
			. 'Schema per item: {"title":"string","fee_type":"fixed|percentage","amount":"string","match_type":"all","confidence":85,"reason":"detailed paragraph citing market/competitor patterns and store data","how_it_helps":"paragraph","revenue_breakdown":"calculation text","revenue_monthly":1000,"conditions":[{"condition":"country|product|category|payment|cart_total|quantity","operator":"is_equal_to|less_then|greater_then|greater_equal_to","values":[""],"label":"string"}]}. '
			. 'Use real product/category/country/payment IDs from metrics when applicable. confidence is 55-98. '
			. 'Store metrics: '
			. wp_json_encode( $metrics )
			. ' Existing created fees in store (do not duplicate): '
			. wp_json_encode( $existing_created )
			. ' New store-metrics suggestions for this run (do not duplicate): '
			. wp_json_encode( $store_titles );

		$response = wp_ai_client_prompt( $prompt )->using_temperature( 0.4 )->generate_text(); // @phpstan-ignore-line

		if ( is_wp_error( $response ) || empty( $response ) ) {
			return array();
		}

		$parsed = self::parse_ai_json( $response );
		if ( empty( $parsed ) ) {
			return array();
		}

		$normalized = array();
		foreach ( $parsed as $item ) {
			$norm = self::normalize_suggestion( $item, $metrics );
			if ( $norm ) {
				$normalized[] = $norm;
			}
		}

		return $normalized;
	}

	/**
	 * @param string $text AI response.
	 * @return array<int, array<string, mixed>>
	 */
	private static function parse_ai_json( $text ) {
		$text = trim( $text );
		if ( preg_match( '/\[[\s\S]*\]/', $text, $matches ) ) {
			$text = $matches[0];
		}
		$data = json_decode( $text, true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * @param array<string, mixed> $item    Raw item.
	 * @param array<string, mixed> $metrics Store metrics.
	 * @return array<string, mixed>|null
	 */
	private static function normalize_suggestion( $item, $metrics = array() ) {
		if ( empty( $item['title'] ) || empty( $item['amount'] ) ) {
			return null;
		}
		$conditions = array();
		if ( ! empty( $item['conditions'] ) && is_array( $item['conditions'] ) ) {
			foreach ( $item['conditions'] as $row ) {
				if ( empty( $row['condition'] ) ) {
					continue;
				}
				$values = $row['values'] ?? array();
				if ( ! is_array( $values ) ) {
					$values = array( (string) $values );
				}
				$conditions[] = self::condition_row(
					sanitize_key( $row['condition'] ),
					! empty( $row['operator'] ) ? $row['operator'] : 'is_equal_to',
					$values,
					! empty( $row['label'] ) ? $row['label'] : '',
					$metrics
				);
			}
		}
		if ( empty( $conditions ) ) {
			return null;
		}

		return self::make_suggestion(
			array(
				'title'             => sanitize_text_field( $item['title'] ),
				'amount'            => (string) $item['amount'],
				'fee_type'          => in_array( $item['fee_type'] ?? '', array( 'fixed', 'percentage' ), true ) ? $item['fee_type'] : 'fixed',
				'match_type'        => ! empty( $item['match_type'] ) ? sanitize_key( $item['match_type'] ) : 'all',
				'optional'          => ! empty( $item['optional'] ),
				'taxable'           => ! empty( $item['taxable'] ) && 'on' === $item['taxable'] ? 'on' : 'off',
				'confidence'        => min( 98, max( 55, (int) ( $item['confidence'] ?? 75 ) ) ),
				'reason'            => ! empty( $item['reason'] ) ? sanitize_textarea_field( $item['reason'] ) : ( ! empty( $item['rationale'] ) ? sanitize_textarea_field( $item['rationale'] ) : '' ),
				'how_it_helps'      => ! empty( $item['how_it_helps'] ) ? sanitize_textarea_field( $item['how_it_helps'] ) : '',
				'revenue_breakdown' => ! empty( $item['revenue_breakdown'] ) ? sanitize_textarea_field( $item['revenue_breakdown'] ) : '',
				'revenue_monthly'   => isset( $item['revenue_monthly'] ) ? (float) $item['revenue_monthly'] : 0,
				'conditions'        => $conditions,
				'suggestion_type'   => 'ai',
				'powered_by'        => 'ai',
			)
		);
	}

	/**
	 * @param array<string, mixed> $args Suggestion args.
	 * @return array<string, mixed>
	 */
	private static function make_suggestion( $args ) {
		$defaults = array(
			'title'           => '',
			'amount'          => '0',
			'fee_type'        => 'fixed',
			'match_type'      => 'all',
			'optional'        => false,
			'taxable'         => 'off',
			'confidence'      => 75,
			'reason'          => '',
			'how_it_helps'    => '',
			'revenue_breakdown'=> '',
			'revenue_monthly' => 0,
			'qualifying_orders' => 0,
			'conditions'      => array(),
			'suggestion_type' => 'custom',
			'powered_by'      => 'store_data',
		);
		$args = wp_parse_args( $args, $defaults );

		$monthly = (float) $args['revenue_monthly'];
		$three   = round( $monthly * 3, 2 );

		$suggestion = array(
			'id'                        => wp_generate_uuid4(),
			'title'                     => $args['title'],
			'fee_type'                  => $args['fee_type'],
			'amount'                    => (string) $args['amount'],
			'match_type'                => $args['match_type'],
			'optional'                  => (bool) $args['optional'],
			'taxable'                   => $args['taxable'],
			'confidence'                => (int) $args['confidence'],
			'reason'                    => $args['reason'],
			'how_it_helps'              => $args['how_it_helps'],
			'rationale'                 => $args['reason'],
			'revenue_breakdown'         => $args['revenue_breakdown'],
			'revenue_monthly'           => round( $monthly, 2 ),
			'revenue_3_month'           => $three,
			'revenue_monthly_formatted' => wc_price( $monthly ),
			'revenue_3_month_formatted' => wc_price( $three ),
			'qualifying_orders'         => (int) $args['qualifying_orders'],
			'conditions'                => $args['conditions'],
			'suggestion_type'           => $args['suggestion_type'],
			'powered_by'                => in_array( $args['powered_by'] ?? '', array( 'ai', 'store_data' ), true ) ? $args['powered_by'] : 'store_data',
		);

		$suggestion['copy_text'] = self::format_copy_text( $suggestion );

		return $suggestion;
	}

	/**
	 * @param string               $condition Condition key.
	 * @param string               $operator  Operator.
	 * @param mixed                $values    Values.
	 * @param string               $label     Label.
	 * @param array<string, mixed> $metrics   Store metrics.
	 * @return array<string, mixed>
	 */
	private static function condition_row( $condition, $operator, $values, $label = '', $metrics = array() ) {
		if ( in_array( $condition, array( 'cart_total', 'quantity', 'product_qty' ), true ) ) {
			$stored_values = is_array( $values ) ? (string) ( $values[0] ?? '' ) : (string) $values;
		} else {
			$stored_values = array_map( 'strval', (array) $values );
		}

		$label = trim( (string) $label );
		if ( '' === $label || self::should_rebuild_condition_label(
			array(
				'condition' => $condition,
				'operator'  => $operator,
				'values'    => $stored_values,
				'label'     => $label,
			)
		) ) {
			$label = self::format_condition_label( $condition, $operator, $stored_values, $metrics );
		}

		return array(
			'condition' => $condition,
			'operator'  => $operator,
			'values'    => $stored_values,
			'label'     => $label,
		);
	}

	/**
	 * @param array<string, mixed> $row Condition row.
	 * @return bool
	 */
	private static function should_rebuild_condition_label( $row ) {
		$label = trim( (string) ( $row['label'] ?? '' ) );
		if ( '' === $label ) {
			return true;
		}

		$condition = (string) ( $row['condition'] ?? '' );
		$values    = $row['values'] ?? '';
		$value_parts = array();

		if ( is_array( $values ) ) {
			$value_parts = array_filter( array_map( 'strval', $values ), 'strlen' );
		} elseif ( '' !== (string) $values ) {
			$value_parts = array( (string) $values );
		}

		if ( empty( $value_parts ) ) {
			return false;
		}

		foreach ( $value_parts as $part ) {
			if ( false !== stripos( $label, $part ) ) {
				return false;
			}
		}

		$generic_names = array(
			self::get_condition_display_name( $condition ),
			ucwords( str_replace( '_', ' ', $condition ) ),
			$condition,
		);

		foreach ( $generic_names as $generic ) {
			if ( '' !== $generic && strcasecmp( $label, $generic ) === 0 ) {
				return true;
			}
		}

		return ! preg_match( '/[<>!=]=?|≥|≤/', $label ) && ! preg_match( '/\b(is|equals|below|above|at least|at most)\b/i', $label );
	}

	/**
	 * @param string               $condition Condition key.
	 * @param string               $operator  Operator.
	 * @param mixed                $values    Values.
	 * @param array<string, mixed> $metrics   Store metrics.
	 * @return string
	 */
	private static function format_condition_label( $condition, $operator, $values, $metrics = array() ) {
		$value_text      = self::format_condition_values( $condition, $values, $metrics );
		$condition_label = self::get_condition_display_name( $condition );

		if ( in_array( $condition, array( 'country', 'product', 'category', 'payment' ), true ) ) {
			if ( 'not_in' === $operator ) {
				return sprintf(
					/* translators: 1: condition label, 2: value */
					__( '%1$s is not %2$s', 'woocommerce-conditional-product-fees-for-checkout' ),
					$condition_label,
					$value_text
				);
			}

			return sprintf(
				/* translators: 1: condition label, 2: value */
				__( '%1$s is %2$s', 'woocommerce-conditional-product-fees-for-checkout' ),
				$condition_label,
				$value_text
			);
		}

		$operator_symbol = self::get_operator_display_symbol( $operator );

		return trim( $condition_label . ' ' . $operator_symbol . ' ' . $value_text );
	}

	/**
	 * @param string $condition Condition key.
	 * @return string
	 */
	private static function get_condition_display_name( $condition ) {
		$labels = array(
			'cart_total'  => __( 'Cart subtotal', 'woocommerce-conditional-product-fees-for-checkout' ),
			'quantity'    => __( 'Cart quantity', 'woocommerce-conditional-product-fees-for-checkout' ),
			'product_qty' => __( 'Product quantity', 'woocommerce-conditional-product-fees-for-checkout' ),
			'country'     => __( 'Country', 'woocommerce-conditional-product-fees-for-checkout' ),
			'product'     => __( 'Product', 'woocommerce-conditional-product-fees-for-checkout' ),
			'category'    => __( 'Category', 'woocommerce-conditional-product-fees-for-checkout' ),
			'payment'     => __( 'Payment method', 'woocommerce-conditional-product-fees-for-checkout' ),
		);

		return $labels[ $condition ] ?? ucwords( str_replace( '_', ' ', $condition ) );
	}

	/**
	 * @param string $operator Operator key.
	 * @return string
	 */
	private static function get_operator_display_symbol( $operator ) {
		$symbols = array(
			'less_then'        => '<',
			'greater_then'     => '>',
			'greater_equal_to' => '>=',
			'less_equal_to'    => '<=',
			'is_equal_to'      => '=',
			'not_in'           => '!=',
		);

		return $symbols[ $operator ] ?? $operator;
	}

	/**
	 * @param string               $condition Condition key.
	 * @param mixed                $values    Values.
	 * @param array<string, mixed> $metrics   Store metrics.
	 * @return string
	 */
	private static function format_condition_values( $condition, $values, $metrics = array() ) {
		if ( is_array( $values ) ) {
			$value_parts = array_filter( array_map( 'strval', $values ), 'strlen' );
		} else {
			$value_parts = '' !== (string) $values ? array( (string) $values ) : array();
		}

		if ( empty( $value_parts ) ) {
			return '';
		}

		if ( 'product' === $condition ) {
			return implode(
				', ',
				array_map(
					static function ( $id ) use ( $metrics ) {
						return self::resolve_product_name( $id, $metrics );
					},
					$value_parts
				)
			);
		}

		if ( 'category' === $condition ) {
			return implode(
				', ',
				array_map(
					static function ( $id ) use ( $metrics ) {
						return self::resolve_category_name( $id, $metrics );
					},
					$value_parts
				)
			);
		}

		if ( 'country' === $condition ) {
			return implode(
				', ',
				array_map(
					static function ( $code ) use ( $metrics ) {
						return self::resolve_country_name( $code, $metrics );
					},
					$value_parts
				)
			);
		}

		if ( 'payment' === $condition ) {
			return implode(
				', ',
				array_map(
					static function ( $id ) use ( $metrics ) {
						return self::resolve_payment_name( $id, $metrics );
					},
					$value_parts
				)
			);
		}

		if ( 'cart_total' === $condition ) {
			$symbol = wp_specialchars_decode( (string) ( $metrics['currency_symbol'] ?? get_woocommerce_currency_symbol() ), ENT_QUOTES );
			return $symbol . number_format_i18n( (float) $value_parts[0], 0 );
		}

		return implode( ', ', $value_parts );
	}

	/**
	 * @param string|int           $id      Product ID.
	 * @param array<string, mixed> $metrics Store metrics.
	 * @return string
	 */
	private static function resolve_product_name( $id, $metrics ) {
		$id = (int) $id;
		foreach ( (array) ( $metrics['top_products'] ?? array() ) as $product ) {
			if ( (int) ( $product['id'] ?? 0 ) === $id ) {
				return (string) ( $product['name'] ?? $id );
			}
		}

		if ( $id > 0 && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $id );
			if ( $product instanceof WC_Product ) {
				return $product->get_name();
			}
		}

		return (string) $id;
	}

	/**
	 * @param string|int           $id      Category ID.
	 * @param array<string, mixed> $metrics Store metrics.
	 * @return string
	 */
	private static function resolve_category_name( $id, $metrics ) {
		$id = (int) $id;
		foreach ( (array) ( $metrics['top_categories'] ?? array() ) as $category ) {
			if ( (int) ( $category['id'] ?? 0 ) === $id ) {
				return (string) ( $category['name'] ?? $id );
			}
		}

		$term = get_term( $id, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term->name;
		}

		return (string) $id;
	}

	/**
	 * @param string               $code    Country code.
	 * @param array<string, mixed> $metrics Store metrics.
	 * @return string
	 */
	private static function resolve_country_name( $code, $metrics ) {
		$code = strtoupper( (string) $code );
		foreach ( (array) ( $metrics['top_countries'] ?? array() ) as $country ) {
			if ( strtoupper( (string) ( $country['code'] ?? '' ) ) === $code ) {
				return (string) ( $country['label'] ?? $code );
			}
		}

		if ( function_exists( 'WC' ) && WC()->countries ) {
			$countries = WC()->countries->get_countries();
			if ( isset( $countries[ $code ] ) ) {
				return (string) $countries[ $code ];
			}
		}

		return $code;
	}

	/**
	 * @param string               $id      Gateway ID.
	 * @param array<string, mixed> $metrics Store metrics.
	 * @return string
	 */
	private static function resolve_payment_name( $id, $metrics ) {
		$id = (string) $id;
		foreach ( (array) ( $metrics['top_payments'] ?? array() ) as $payment ) {
			if ( (string) ( $payment['id'] ?? '' ) === $id ) {
				return (string) ( $payment['title'] ?? $id );
			}
		}

		foreach ( (array) ( $metrics['payment_gateways'] ?? array() ) as $gateway ) {
			if ( (string) ( $gateway['id'] ?? '' ) === $id ) {
				return (string) ( $gateway['title'] ?? $id );
			}
		}

		return $id;
	}

	/**
	 * @param float $value    Value.
	 * @param int   $decimals Decimals.
	 * @return string
	 */
	private static function suggest_fixed_amount( $value, $decimals = 2 ) {
		return (string) round( max( 0.5, $value ), $decimals );
	}

	/**
	 * Plain-text price for API/UI (no WooCommerce HTML wrappers).
	 *
	 * @param float|int $amount  Amount.
	 * @param string    $symbol  Currency symbol.
	 * @return string
	 */
	private static function format_plain_price( $amount, $symbol = '' ) {
		if ( '' === $symbol ) {
			$symbol = wp_specialchars_decode( get_woocommerce_currency_symbol(), ENT_QUOTES );
		}
		return $symbol . number_format_i18n( (float) $amount, 2 );
	}

	/**
	 * Strip HTML and decode entities from stored suggestion copy.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	private static function plain_text( $value ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return '';
		}
		return html_entity_decode( wp_strip_all_tags( $value ), ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * @param array<string, mixed> $suggestion Suggestion.
	 * @return string
	 */
	private static function format_copy_text( $suggestion ) {
		$amount   = $suggestion['amount'] ?? '';
		$fee_type = $suggestion['fee_type'] ?? 'fixed';
		$lines    = array();
		$lines[]  = __( 'Fee title:', 'woocommerce-conditional-product-fees-for-checkout' ) . ' ' . ( $suggestion['title'] ?? '' );
		$lines[]  = __( 'Confidence:', 'woocommerce-conditional-product-fees-for-checkout' ) . ' ' . ( $suggestion['confidence'] ?? '' ) . '%';
		$lines[]  = __( 'Fee type:', 'woocommerce-conditional-product-fees-for-checkout' ) . ' ' . $fee_type;
		$lines[]  = __( 'Amount:', 'woocommerce-conditional-product-fees-for-checkout' ) . ' ' . $amount . ( 'percentage' === $fee_type ? '%' : '' );
		$lines[]  = __( 'Reason:', 'woocommerce-conditional-product-fees-for-checkout' ) . ' ' . ( $suggestion['reason'] ?? '' );
		$lines[]  = __( 'How it helps:', 'woocommerce-conditional-product-fees-for-checkout' ) . ' ' . ( $suggestion['how_it_helps'] ?? '' );
		$lines[]  = __( 'Revenue:', 'woocommerce-conditional-product-fees-for-checkout' ) . ' ' . ( $suggestion['revenue_breakdown'] ?? '' );
		$lines[]  = __( 'Conditions:', 'woocommerce-conditional-product-fees-for-checkout' );
		foreach ( (array) ( $suggestion['conditions'] ?? array() ) as $c ) {
			$lines[] = '• ' . ( $c['label'] ?: $c['condition'] );
		}
		return implode( "\n", $lines );
	}
}
