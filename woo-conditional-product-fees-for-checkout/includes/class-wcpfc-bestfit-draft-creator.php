<?php
/**
 * Creates draft fee rules from best-fit suggestions.
 *
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Draft fee creator for Bestfit suggestions.
 */
class WCPFC_Bestfit_Draft_Creator {

	const META_FLAG = '_wcpfc_bestfit_generated';
	const POST_TYPE = 'wc_conditional_fee';

	/**
	 * Create draft fees from suggestions.
	 *
	 * @param array<int, array<string, mixed>> $suggestions Suggestions.
	 * @return array{created: array<int, array<string, mixed>>, errors: array<int, string>}
	 */
	public static function create_drafts( $suggestions ) {
		$created = array();
		$errors  = array();

		if ( ! is_array( $suggestions ) ) {
			return array(
				'created' => array(),
				'errors'  => array( __( 'Invalid suggestions payload.', 'woocommerce-conditional-product-fees-for-checkout' ) ),
			);
		}

		delete_transient( 'get_all_fees' );
		if ( class_exists( 'WCPFC_Existing_Fees_Registry' ) ) {
			WCPFC_Existing_Fees_Registry::flush_cache();
		}

		$menu_order = self::next_menu_order();

		foreach ( $suggestions as $index => $suggestion ) {
			$result = self::create_single_draft( $suggestion, $menu_order );
			if ( is_wp_error( $result ) ) {
				$errors[] = $result->get_error_message();
				continue;
			}
			$created[] = $result;
			++$menu_order;
		}

		return array(
			'created' => $created,
			'errors'  => $errors,
		);
	}

	/**
	 * @param array<string, mixed> $suggestion Suggestion.
	 * @param int                  $menu_order Menu order.
	 * @return array<string, mixed>|WP_Error
	 */
	private static function create_single_draft( $suggestion, $menu_order ) {
		$title = isset( $suggestion['title'] ) ? sanitize_text_field( $suggestion['title'] ) : '';
		if ( '' === $title ) {
			return new WP_Error( 'invalid_title', __( 'Missing fee title.', 'woocommerce-conditional-product-fees-for-checkout' ) );
		}

		$post_id = wp_insert_post(
			array(
				'post_title'  => wp_strip_all_tags( $title ),
				'post_status' => 'draft',
				'post_type'   => self::POST_TYPE,
				'menu_order'  => $menu_order,
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return new WP_Error( 'insert_failed', __( 'Could not create draft fee.', 'woocommerce-conditional-product-fees-for-checkout' ) );
		}

		$fee_type = in_array( $suggestion['fee_type'] ?? '', array( 'fixed', 'percentage' ), true ) ? $suggestion['fee_type'] : 'fixed';
		$amount   = isset( $suggestion['amount'] ) ? sanitize_text_field( (string) $suggestion['amount'] ) : '0';
		$match    = in_array( $suggestion['match_type'] ?? '', array( 'all', 'any' ), true ) ? $suggestion['match_type'] : 'all';

		update_post_meta( $post_id, 'fee_settings_status', 'off' );
		update_post_meta( $post_id, 'fee_settings_product_cost', $amount );
		update_post_meta( $post_id, 'fee_settings_select_fee_type', $fee_type );
		update_post_meta( $post_id, 'fee_settings_select_taxable', ! empty( $suggestion['taxable'] ) && 'on' === $suggestion['taxable'] ? 'on' : '' );
		update_post_meta( $post_id, 'fee_settings_select_optional', ! empty( $suggestion['optional'] ) ? 'on' : '' );
		update_post_meta( $post_id, 'ap_rule_status', 'off' );
		update_post_meta( $post_id, 'cost_rule_match', maybe_serialize( array( 'general_rule_match' => $match ) ) );
		update_post_meta( $post_id, 'product_fees_metabox', self::build_metabox( $suggestion['conditions'] ?? array() ) );
		update_post_meta( $post_id, self::META_FLAG, '1' );
		update_post_meta( $post_id, 'fee_chk_qty_price', 'off' );

		$statuses = array(
			'cost_on_product_status',
			'cost_on_product_weight_status',
			'cost_on_product_subtotal_status',
			'cost_on_category_status',
			'cost_on_category_weight_status',
			'cost_on_category_subtotal_status',
			'cost_on_total_cart_qty_status',
			'cost_on_total_cart_weight_status',
			'cost_on_total_cart_subtotal_status',
			'cost_on_shipping_class_subtotal_status',
		);
		foreach ( $statuses as $key ) {
			update_post_meta( $post_id, $key, 'off' );
		}

		return array(
			'id'    => (int) $post_id,
			'title' => $title,
			'edit_url' => add_query_arg(
				array(
					'page'   => 'wcpfc-pro-list',
					'action' => 'edit',
					'id'     => $post_id,
				),
				admin_url( 'admin.php' )
			),
		);
	}

	/**
	 * @param array<int, array<string, mixed>> $conditions Conditions.
	 * @return array<int, array<string, mixed>>
	 */
	private static function build_metabox( $conditions ) {
		$rows = array();
		foreach ( (array) $conditions as $row ) {
			if ( empty( $row['condition'] ) ) {
				continue;
			}
			$values = $row['values'] ?? array();
			$rows[] = array(
				'product_fees_conditions_condition' => sanitize_key( $row['condition'] ),
				'product_fees_conditions_is'        => ! empty( $row['operator'] ) ? $row['operator'] : 'is_equal_to',
				'product_fees_conditions_values'    => $values,
			);
		}
		if ( empty( $rows ) ) {
			$rows[] = array(
				'product_fees_conditions_condition' => 'quantity',
				'product_fees_conditions_is'        => 'greater_equal_to',
				'product_fees_conditions_values'    => '1',
			);
		}
		return $rows;
	}

	/**
	 * @return int
	 */
	private static function next_menu_order() {
		$query = new WP_Query(
			array(
				'post_type'              => self::POST_TYPE,
				'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'posts_per_page'         => 1,
				'orderby'                => 'menu_order',
				'order'                  => 'DESC',
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( empty( $query->posts[0] ) ) {
			return 1;
		}

		$max = (int) get_post_field( 'menu_order', $query->posts[0] );
		return $max + 1;
	}
}
