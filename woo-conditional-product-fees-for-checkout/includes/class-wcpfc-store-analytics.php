<?php
/**
 * Collects WooCommerce store metrics for fee suggestions.
 *
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store analytics helper (last 90 days).
 */
class WCPFC_Store_Analytics {

	const DAYS = 90;

	/**
	 * Gather metrics for suggestions.
	 *
	 * @return array<string, mixed>
	 */
	public static function collect() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array(
				'has_orders' => false,
				'error'      => 'woocommerce_inactive',
			);
		}

		$date_after = gmdate( 'Y-m-d H:i:s', strtotime( '-' . self::DAYS . ' days' ) );
		$statuses   = array_merge( wc_get_is_paid_statuses(), array( 'processing', 'completed' ) );
		$statuses   = array_unique( $statuses );

		$orders = wc_get_orders(
			array(
				'limit'        => 500,
				'status'       => $statuses,
				'date_created' => '>' . $date_after,
				'return'       => 'objects',
			)
		);

		$order_count = is_array( $orders ) ? count( $orders ) : 0;

		if ( $order_count > 0 ) {
			return self::analyze_orders( $orders, $order_count, $date_after );
		}

		return self::analyze_catalog();
	}

	/**
	 * @param WC_Order[] $orders       Orders.
	 * @param int        $order_count  Count.
	 * @param string     $date_after   Period start.
	 * @return array<string, mixed>
	 */
	private static function analyze_orders( $orders, $order_count, $date_after ) {
		$countries       = array();
		$payments        = array();
		$products        = array();
		$categories      = array();
		$totals          = array();
		$subtotals       = array();

		foreach ( $orders as $order ) {
			if ( ! $order instanceof WC_Order ) {
				continue;
			}

			$total    = (float) $order->get_total();
			$subtotal = (float) $order->get_subtotal();
			if ( $subtotal <= 0 ) {
				$subtotal = $total;
			}
			$totals[]    = $total;
			$subtotals[] = $subtotal;

			$country = $order->get_billing_country();
			if ( $country ) {
				$countries[ $country ] = ( $countries[ $country ] ?? 0 ) + 1;
			}

			$payment = $order->get_payment_method();
			if ( $payment ) {
				$payments[ $payment ] = ( $payments[ $payment ] ?? 0 ) + 1;
			}

			$order_product_ids = array();
			foreach ( $order->get_items( 'line_item' ) as $item ) {
				if ( ! $item instanceof WC_Order_Item_Product ) {
					continue;
				}
				$product_id = (int) $item->get_product_id();
				if ( $product_id <= 0 ) {
					continue;
				}
				$order_product_ids[] = $product_id;
				if ( ! isset( $products[ $product_id ] ) ) {
					$products[ $product_id ] = array(
						'id'          => $product_id,
						'name'        => $item->get_name(),
						'qty'         => 0,
						'revenue'     => 0.0,
						'order_count' => 0,
					);
				}
				$products[ $product_id ]['qty']     += (int) $item->get_quantity();
				$products[ $product_id ]['revenue'] += (float) $item->get_total();

				$terms = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'all' ) );
				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
					foreach ( $terms as $term ) {
						if ( ! isset( $categories[ $term->term_id ] ) ) {
							$categories[ $term->term_id ] = array(
								'id'          => (int) $term->term_id,
								'name'        => $term->name,
								'slug'        => $term->slug,
								'qty'         => 0,
								'order_count' => 0,
							);
						}
						$categories[ $term->term_id ]['qty'] += (int) $item->get_quantity();
					}
				}
			}

			$order_product_ids = array_unique( $order_product_ids );
			foreach ( $order_product_ids as $pid ) {
				$products[ $pid ]['order_count'] = ( $products[ $pid ]['order_count'] ?? 0 ) + 1;
				$terms = wp_get_post_terms( $pid, 'product_cat', array( 'fields' => 'ids' ) );
				if ( ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term_id ) {
						if ( isset( $categories[ $term_id ] ) ) {
							$categories[ $term_id ]['order_count'] = ( $categories[ $term_id ]['order_count'] ?? 0 ) + 1;
						}
					}
				}
			}
		}

		arsort( $countries );
		arsort( $payments );
		usort(
			$products,
			static function ( $a, $b ) {
				return $b['revenue'] <=> $a['revenue'];
			}
		);
		usort(
			$categories,
			static function ( $a, $b ) {
				return $b['qty'] <=> $a['qty'];
			}
		);

		$aov                   = ! empty( $totals ) ? array_sum( $totals ) / count( $totals ) : 0;
		$small_order_threshold = $aov > 0 ? max( 10, round( $aov * 0.45, 0 ) ) : 15;
		$high_order_threshold  = $aov > 0 ? round( $aov * 1.35, 0 ) : 100;
		$orders_below          = 0;
		$orders_above_high     = 0;

		foreach ( $subtotals as $sub ) {
			if ( $sub < $small_order_threshold ) {
				++$orders_below;
			}
			if ( $sub > $high_order_threshold ) {
				++$orders_above_high;
			}
		}

		$monthly_orders = max( 1, (int) round( $order_count / 3 ) );

		return array(
			'has_orders'              => true,
			'period_days'             => self::DAYS,
			'period_start'            => $date_after,
			'order_count'             => $order_count,
			'monthly_orders'          => $monthly_orders,
			'aov'                     => round( $aov, 2 ),
			'small_order_threshold'   => $small_order_threshold,
			'orders_below_threshold'  => $orders_below,
			'orders_below_threshold_pct' => $order_count > 0 ? (int) round( ( $orders_below / $order_count ) * 100 ) : 0,
			'high_order_threshold'    => $high_order_threshold,
			'orders_above_high'       => $orders_above_high,
			'orders_above_high_pct'   => $order_count > 0 ? (int) round( ( $orders_above_high / $order_count ) * 100 ) : 0,
			'currency'                => get_woocommerce_currency(),
			'currency_symbol'         => wp_specialchars_decode( get_woocommerce_currency_symbol(), ENT_QUOTES ),
			'top_countries'           => self::format_top( $countries, 5, 'code', $order_count ),
			'top_payments'            => self::format_payment_top( $payments, 5, $order_count ),
			'top_products'            => self::add_share_to_items( array_slice( array_values( $products ), 0, 5 ), $order_count, 'order_count' ),
			'top_categories'          => self::add_share_to_items( array_slice( array_values( $categories ), 0, 5 ), $order_count, 'order_count' ),
			'payment_gateways'        => self::available_gateways(),
			'published_products'      => (int) wp_count_posts( 'product' )->publish,
		);
	}

	/**
	 * Catalog + gateways when no orders exist.
	 *
	 * @return array<string, mixed>
	 */
	private static function analyze_catalog() {
		$products = wc_get_products(
			array(
				'status' => 'publish',
				'limit'  => 20,
				'orderby'=> 'popularity',
				'order'  => 'DESC',
			)
		);

		$catalog = array();
		foreach ( $products as $product ) {
			if ( ! $product instanceof WC_Product ) {
				continue;
			}
			$catalog[] = array(
				'id'    => $product->get_id(),
				'name'  => $product->get_name(),
				'price' => (float) $product->get_price(),
			);
		}

		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'number'     => 5,
				'orderby'    => 'count',
				'order'      => 'DESC',
			)
		);

		$cat_list = array();
		if ( ! is_wp_error( $categories ) ) {
			foreach ( $categories as $term ) {
				$cat_list[] = array(
					'id'    => (int) $term->term_id,
					'name'  => $term->name,
					'slug'  => $term->slug,
					'count' => (int) $term->count,
				);
			}
		}

		return array(
			'has_orders'         => false,
			'period_days'        => self::DAYS,
			'order_count'        => 0,
			'monthly_orders'     => max( 20, min( 300, (int) wp_count_posts( 'product' )->publish * 2 ) ),
			'aov'                => 0,
			'small_order_threshold' => 15,
			'orders_below_threshold' => 0,
			'orders_below_threshold_pct' => 0,
			'currency'           => get_woocommerce_currency(),
			'currency_symbol'    => wp_specialchars_decode( get_woocommerce_currency_symbol(), ENT_QUOTES ),
			'top_countries'      => array(),
			'top_payments'       => array(),
			'top_products'       => $catalog,
			'top_categories'     => $cat_list,
			'payment_gateways'   => self::available_gateways(),
			'published_products' => (int) wp_count_posts( 'product' )->publish,
		);
	}

	/**
	 * @param array<int, array<string, mixed>> $items Items.
	 * @param int                              $total  Total orders.
	 * @param string                           $field  Count field.
	 * @return array<int, array<string, mixed>>
	 */
	private static function add_share_to_items( $items, $total, $field = 'order_count' ) {
		foreach ( $items as $key => $item ) {
			$count = (int) ( $item[ $field ] ?? 0 );
			$items[ $key ]['share_pct'] = $total > 0 ? (int) round( ( $count / $total ) * 100 ) : 0;
		}
		return $items;
	}

	/**
	 * @param array<string, int> $data Raw counts.
	 * @param int                $limit Limit.
	 * @param string             $label_key Label key.
	 * @return array<int, array<string, mixed>>
	 */
	private static function format_top( $data, $limit, $label_key = 'name', $order_total = 0 ) {
		$out = array();
		$i   = 0;
		foreach ( $data as $key => $count ) {
			if ( $i >= $limit ) {
				break;
			}
			$label = $key;
			if ( 'code' === $label_key && function_exists( 'WC' ) && WC()->countries ) {
				$label = WC()->countries->countries[ $key ] ?? $key;
			}
			$out[] = array(
				'code'      => $key,
				'label'     => $label,
				'count'     => (int) $count,
				'share_pct' => $order_total > 0 ? (int) round( ( $count / $order_total ) * 100 ) : 0,
			);
			++$i;
		}
		return $out;
	}

	/**
	 * @param array<string, int> $payments Payment counts.
	 * @param int                $limit    Limit.
	 * @param int                $order_total Total orders.
	 * @return array<int, array<string, mixed>>
	 */
	private static function format_payment_top( $payments, $limit, $order_total = 0 ) {
		$gateways = self::available_gateways();
		$map      = array();
		foreach ( $gateways as $gw ) {
			$map[ $gw['id'] ] = $gw['title'];
		}

		$out = array();
		$i   = 0;
		foreach ( $payments as $id => $count ) {
			if ( $i >= $limit ) {
				break;
			}
			$out[] = array(
				'id'        => $id,
				'title'     => $map[ $id ] ?? $id,
				'count'     => (int) $count,
				'share_pct' => $order_total > 0 ? (int) round( ( $count / $order_total ) * 100 ) : 0,
			);
			++$i;
		}
		return $out;
	}

	/**
	 * @return array<int, array<string, string>>
	 */
	public static function available_gateways() {
		if ( ! function_exists( 'WC' ) ) {
			return array();
		}
		$gateways = WC()->payment_gateways() ? WC()->payment_gateways()->payment_gateways() : array();
		$list     = array();
		foreach ( $gateways as $gateway ) {
			if ( ! $gateway instanceof WC_Payment_Gateway ) {
				continue;
			}
			$list[] = array(
				'id'    => $gateway->id,
				'title' => $gateway->get_title(),
			);
		}
		return $list;
	}

	/**
	 * Lightweight metrics for the best-fit loading sequence.
	 *
	 * @return array<string, int|bool>
	 */
	public static function get_scan_preview() {
		$metrics = self::collect();

		$category_count = 0;
		if ( function_exists( 'wp_count_terms' ) ) {
			$term_count = wp_count_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => true,
				)
			);
			if ( ! is_wp_error( $term_count ) ) {
				$category_count = (int) $term_count;
			}
		}

		if ( $category_count <= 0 && ! empty( $metrics['top_categories'] ) && is_array( $metrics['top_categories'] ) ) {
			$category_count = count( $metrics['top_categories'] );
		}

		$period_days = isset( $metrics['period_days'] ) ? (int) $metrics['period_days'] : self::DAYS;
		$months      = max( 1, (int) round( $period_days / 30 ) );

		return array(
			'has_orders'     => ! empty( $metrics['has_orders'] ),
			'period_days'    => $period_days,
			'period_months'  => $months,
			'order_count'    => isset( $metrics['order_count'] ) ? (int) $metrics['order_count'] : 0,
			'category_count' => $category_count,
			'product_count'  => isset( $metrics['published_products'] ) ? (int) $metrics['published_products'] : 0,
		);
	}
}
