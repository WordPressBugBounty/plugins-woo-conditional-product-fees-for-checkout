<?php
/**
 * WordPress Abilities API: best-fit fee suggestions.
 *
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers suggest + create-draft abilities.
 */
class WCPFC_Bestfit_Abilities {

	const ABILITY_SUGGEST = 'wcpfc/suggest-best-fit-fees';
	const ABILITY_DRAFTS  = 'wcpfc/create-best-fit-drafts';
	const ABILITY_SCAN    = 'wcpfc/get-best-fit-scan-metrics';

	/**
	 * Load suggestion engine classes when an ability runs.
	 */
	private static function load_engine_classes() {
		static $loaded = false;
		if ( $loaded ) {
			return;
		}
		$base = plugin_dir_path( dirname( __FILE__ ) );
		require_once $base . 'includes/class-wcpfc-store-analytics.php';
		require_once $base . 'includes/class-wcpfc-existing-fees-registry.php';
		require_once $base . 'includes/class-wcpfc-fee-suggestion-engine.php';
		require_once $base . 'includes/class-wcpfc-bestfit-draft-creator.php';
		$loaded = true;
	}

	/**
	 * Boot.
	 */
	public static function init() {
		$instance = new self();
		add_action( 'wp_abilities_api_init', array( $instance, 'register_abilities' ) );
	}

	/**
	 * Register abilities.
	 */
	public function register_abilities() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			self::ABILITY_SCAN,
			array(
				'label'               => __( 'Best-fit scan preview', 'woocommerce-conditional-product-fees-for-checkout' ),
				'description'         => __( 'Returns store counts used by the best-fit loading sequence.', 'woocommerce-conditional-product-fees-for-checkout' ),
				'category'            => 'site',
				'execute_callback'    => array( $this, 'execute_scan_preview' ),
				'permission_callback' => array( $this, 'permission_check' ),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'has_orders'     => array( 'type' => 'boolean' ),
						'period_days'    => array( 'type' => 'integer' ),
						'period_months'  => array( 'type' => 'integer' ),
						'order_count'    => array( 'type' => 'integer' ),
						'category_count' => array( 'type' => 'integer' ),
						'product_count'  => array( 'type' => 'integer' ),
					),
				),
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

		wp_register_ability(
			self::ABILITY_SUGGEST,
			array(
				'label'               => __( 'Suggest best-fit fees', 'woocommerce-conditional-product-fees-for-checkout' ),
				'description'         => __( 'Analyzes store data and returns up to five recommended extra fees.', 'woocommerce-conditional-product-fees-for-checkout' ),
				'category'            => 'site',
				'execute_callback'    => array( $this, 'execute_suggest' ),
				'permission_callback' => array( $this, 'permission_check' ),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'suggestions' => array( 'type' => 'array' ),
						'metrics'     => array( 'type' => 'object' ),
						'source'      => array( 'type' => 'string' ),
						'store_count' => array( 'type' => 'integer' ),
						'ai_count'    => array( 'type' => 'integer' ),
						'ai_available'=> array( 'type' => 'boolean' ),
						'ai_cached'   => array( 'type' => 'boolean' ),
					),
				),
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

		wp_register_ability(
			self::ABILITY_DRAFTS,
			array(
				'label'               => __( 'Create best-fit draft fees', 'woocommerce-conditional-product-fees-for-checkout' ),
				'description'         => __( 'Creates draft fee rules from best-fit suggestions.', 'woocommerce-conditional-product-fees-for-checkout' ),
				'category'            => 'site',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'suggestions' => array(
							'type'        => 'array',
							'description' => __( 'Fee suggestions to create as drafts.', 'woocommerce-conditional-product-fees-for-checkout' ),
						),
					),
					'required'   => array( 'suggestions' ),
				),
				'execute_callback'    => array( $this, 'execute_create_drafts' ),
				'permission_callback' => array( $this, 'permission_check' ),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'created' => array( 'type' => 'array' ),
						'errors'  => array( 'type' => 'array' ),
					),
				),
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => false,
					),
					'show_in_rest' => true,
				),
			)
		);
	}

	/**
	 * @param mixed $input Input.
	 * @return bool
	 */
	public function permission_check( $input = null ) {
		unset( $input );

		return current_user_can( 'manage_options' ) && function_exists( 'wcpfc_can_use_best_fit' ) && wcpfc_can_use_best_fit();
	}

	/**
	 * @param mixed $input Input.
	 * @return array<string, int|bool>|WP_Error
	 */
	public function execute_scan_preview( $input = null ) {
		unset( $input );

		self::load_engine_classes();

		if ( ! class_exists( 'WooCommerce' ) ) {
			return new WP_Error(
				'woocommerce_inactive',
				__( 'WooCommerce is required.', 'woocommerce-conditional-product-fees-for-checkout' ),
				array( 'status' => 400 )
			);
		}

		return WCPFC_Store_Analytics::get_scan_preview();
	}

	/**
	 * @param mixed $input Input.
	 * @return array<string, mixed>
	 */
	public function execute_suggest( $input = null ) {
		unset( $input );

		self::load_engine_classes();

		if ( ! class_exists( 'WooCommerce' ) ) {
			return new WP_Error(
				'woocommerce_inactive',
				__( 'WooCommerce is required.', 'woocommerce-conditional-product-fees-for-checkout' ),
				array( 'status' => 400 )
			);
		}

		return WCPFC_Fee_Suggestion_Engine::get_suggestions();
	}

	/**
	 * @param mixed $input Input.
	 * @return array<string, mixed>|WP_Error
	 */
	public function execute_create_drafts( $input = null ) {
		self::load_engine_classes();

		$input = is_array( $input ) ? $input : array();
		$suggestions = $input['suggestions'] ?? array();

		if ( empty( $suggestions ) || ! is_array( $suggestions ) ) {
			return new WP_Error(
				'missing_suggestions',
				__( 'No suggestions provided.', 'woocommerce-conditional-product-fees-for-checkout' ),
				array( 'status' => 400 )
			);
		}

		$suggestions = array_slice( $suggestions, 0, WCPFC_Fee_Suggestion_Engine::SUGGESTION_LIMIT );

		return WCPFC_Bestfit_Draft_Creator::create_drafts( $suggestions );
	}
}
