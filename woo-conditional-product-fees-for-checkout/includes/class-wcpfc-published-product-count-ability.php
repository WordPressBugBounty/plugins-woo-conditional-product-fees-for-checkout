<?php
/**
 * Registers the published product count ability (WordPress Abilities API).
 *
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @since   4.3.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Published product count ability for Global Settings.
 */
class WCPFC_Published_Product_Count_Ability {

	/**
	 * Ability name (namespaced).
	 */
	const ABILITY_NAME = 'wcpfc/count-published-products';

	/**
	 * Option key for the saved count.
	 */
	const OPTION_KEY = 'wcpfc_published_product_count';

	/**
	 * Boot hooks.
	 */
	public static function init() {
		$instance = new self();
		add_action( 'wp_abilities_api_init', array( $instance, 'register_ability' ) );
	}

	/**
	 * Register ability on wp_abilities_api_init.
	 */
	public function register_ability() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			self::ABILITY_NAME,
			array(
				'label'               => __( 'Count published products', 'woocommerce-conditional-product-fees-for-checkout' ),
				'description'         => __( 'Returns the number of published WooCommerce products on this store.', 'woocommerce-conditional-product-fees-for-checkout' ),
				'category'            => 'site',
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'count' => array(
							'type'        => 'integer',
							'description' => __( 'Number of published products.', 'woocommerce-conditional-product-fees-for-checkout' ),
						),
					),
					'required'   => array( 'count' ),
				),
				'execute_callback'    => array( $this, 'execute' ),
				'permission_callback' => array( $this, 'permission_check' ),
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					),
					'show_in_rest' => true,
				),
			)
		);
	}

	/**
	 * Permission check for the ability.
	 *
	 * @param mixed $input Ability input.
	 * @return bool
	 */
	public function permission_check( $input = null ) {
		unset( $input );
		return current_user_can( 'manage_options' );
	}

	/**
	 * Count published WooCommerce products.
	 *
	 * @param mixed $input Ability input.
	 * @return array{count: int}|\WP_Error
	 */
	public function execute( $input = null ) {
		unset( $input );

		if ( ! post_type_exists( 'product' ) ) {
			return new WP_Error(
				'woocommerce_inactive',
				__( 'WooCommerce products are not available.', 'woocommerce-conditional-product-fees-for-checkout' ),
				array( 'status' => 400 )
			);
		}

		$counts    = wp_count_posts( 'product' );
		$published = ( isset( $counts->publish ) ) ? (int) $counts->publish : 0;

		return array(
			'count' => $published,
		);
	}

	/**
	 * Persist count to options table.
	 *
	 * @param int $count Product count.
	 * @return bool
	 */
	public static function save_count( $count ) {
		return update_option( self::OPTION_KEY, max( 0, (int) $count ), false );
	}

	/**
	 * Get saved count from options.
	 *
	 * @return int
	 */
	public static function get_saved_count() {
		$count = get_option( self::OPTION_KEY, '' );
		return ( '' === $count || false === $count ) ? '' : (int) $count;
	}
}
