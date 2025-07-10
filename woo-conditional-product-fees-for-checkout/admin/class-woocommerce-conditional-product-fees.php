<?php 
/**
 * WooCommerce Conditional Fee details
 *
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce_Conditional_Product_Fees.
 */
#[\AllowDynamicProperties]
class Woocommerce_Conditional_Product_Fees {

    /** @var int fee (post) unique ID */
	protected $id = 0;

	/** @var \WP_Post fee post object */
	protected $post;

	/** @var string fee name (post title) */
	protected $name = '';

	/** @var string fee (post) slug */
	protected $slug = '';

    /** @var string fee type post meta key name */
	protected $fee_type_meta = 'fee_settings_select_fee_type';
    protected $fees_on_cart_total_meta = 'fees_on_cart_total';
    protected $fee_settings_product_cost_meta = 'fee_settings_product_cost';
    protected $fee_chk_qty_price_meta = 'fee_chk_qty_price';
    protected $fee_per_qty_meta = 'fee_per_qty';
    protected $extra_product_cost_meta = 'extra_product_cost';
    protected $is_allow_custom_weight_base_meta = 'is_allow_custom_weight_base';
    protected $sm_custom_weight_base_cost_meta = 'sm_custom_weight_base_cost';
    protected $sm_custom_weight_base_per_each_meta = 'sm_custom_weight_base_per_each';
    protected $sm_custom_weight_base_over_meta = 'sm_custom_weight_base_over';
    protected $wcpfc_tooltip_description_meta = 'wcpfc_tooltip_description';
    protected $fee_settings_select_taxable_meta = 'fee_settings_select_taxable';
    protected $fee_settings_select_optional_meta = 'fee_settings_select_optional';
    protected $fee_settings_optional_type_meta = 'fee_settings_optional_type';
    protected $default_optional_checked_meta = 'default_optional_checked';
	protected $optional_fees_in_cart_meta = 'optional_fees_in_cart';
    protected $fee_settings_optional_description_meta = 'fee_settings_optional_description';
    protected $first_order_for_user_meta = 'first_order_for_user';
    protected $fee_settings_recurring_meta = 'fee_settings_recurring';
    protected $fee_show_on_checkout_only_meta = 'fee_show_on_checkout_only';
    protected $ds_select_day_of_week_meta = 'ds_select_day_of_week';
    protected $fee_settings_start_date_meta = 'fee_settings_start_date';
    protected $fee_settings_end_date_meta = 'fee_settings_end_date';
    protected $ds_time_from_meta = 'ds_time_from';
    protected $ds_time_to_meta = 'ds_time_to';
    protected $product_fees_metabox_meta = 'product_fees_metabox';
    protected $cost_rule_match_meta = 'cost_rule_match';
	protected $ap_rule_status_meta = 'ap_rule_status';
	protected $cost_on_product_status_meta = 'cost_on_product_status';
	protected $cost_on_category_status_meta = 'cost_on_category_status';
	protected $cost_on_product_subtotal_status_meta = 'cost_on_product_subtotal_status';

	protected $cost_on_product_weight_status_meta = 'cost_on_product_weight_status';
	protected $cost_on_category_subtotal_status_meta = 'cost_on_category_subtotal_status';
	protected $cost_on_category_weight_status_meta = 'cost_on_category_weight_status';
	protected $cost_on_total_cart_qty_status_meta = 'cost_on_total_cart_qty_status';
	protected $cost_on_total_cart_weight_status_meta = 'cost_on_total_cart_weight_status';
	protected $cost_on_total_cart_subtotal_status_meta = 'cost_on_total_cart_subtotal_status';
	protected $cost_on_shipping_class_subtotal_status_meta = 'cost_on_shipping_class_subtotal_status';

	protected $sm_metabox_ap_product_meta = 'sm_metabox_ap_product';
	protected $sm_metabox_ap_product_subtotal_meta = 'sm_metabox_ap_product_subtotal';
	protected $sm_metabox_ap_category_meta = 'sm_metabox_ap_category';
	protected $sm_metabox_ap_product_weight_meta = 'sm_metabox_ap_product_weight';
	protected $sm_metabox_ap_category_subtotal_meta = 'sm_metabox_ap_category_subtotal';
	protected $sm_metabox_ap_category_weight_meta = 'sm_metabox_ap_category_weight';
	protected $sm_metabox_ap_total_cart_qty_meta = 'sm_metabox_ap_total_cart_qty';
	protected $sm_metabox_ap_total_cart_weight_meta = 'sm_metabox_ap_total_cart_weight';
	protected $sm_metabox_ap_total_cart_subtotal_meta = 'sm_metabox_ap_total_cart_subtotal';
	protected $sm_metabox_ap_shipping_class_subtotal_meta = 'sm_metabox_ap_shipping_class_subtotal';
	

    /**
	 * Fee constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int|\WP_Post|\Woocommerce_Conditional_Product_Fees $data the post or fee ID, object
	 */
	public function __construct( $data ) {

		if ( is_numeric( $data ) ) {
			$post       = get_post( (int) $data );
			$this->post = $post instanceof \WP_Post ? $post : null;
		} elseif ( is_object( $data ) ) {
			$this->post = $data;
		}

		if ( $this->post instanceof \WP_Post ) {

			// set post type data
			$this->id       = (int) $this->post->ID;
			$this->name     = $this->post->post_title;
			$this->slug     = $this->post->post_name;
			$this->status   = $this->post->post_status;
		}
	}

    /**
	 * Get the fee ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

    /**
	 * Get the fee name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the fee slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

    /**
	 * Get the fee status.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

    /**
	 * Get fee type.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_fee_type() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->fee_type_meta, true ) : 'fixed';
	}

	/**
	 * Whether the fee has a type.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fee_type() {

		$phone = trim( $this->get_fee_type() );

		return ! empty( $phone );
	}

	/**
	 * Set fee type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fee_type a phone number string
	 * @return bool
	 */
	public function set_fee_type( $fee_type ) {

		$success = false;

		if ( $this->id > 0 && is_string( $fee_type ) ) {
			$success = update_post_meta( $this->id, $this->fee_type_meta, trim( $fee_type ) );
		}

		return (bool) $success;
	}

	/**
	 * Delete the fee type.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function delete_fee_type() {
		return $this->id > 0 && delete_post_meta( $this->id, $this->fee_type_meta );
	}

    /**
	 * Get fee type.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_fees_on_cart_total() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->fees_on_cart_total_meta, true ) : 'no';
	}

    /**
	 * Set fee type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fees_on_cart_total a phone number string
	 * @return bool
	 */
	public function set_fees_on_cart_total( $fees_on_cart_total ) {

		$success = false;

		if ( $this->id > 0 && is_string( $fees_on_cart_total ) ) {
			$success = update_post_meta( $this->id, $this->fees_on_cart_total_meta, trim( $fees_on_cart_total ) );
		}

		return (bool) $success;
	}


    /**
	 * Get fee type.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_fee_settings_product_cost() {

		return (float) $this->id > 0 ? get_post_meta( $this->id, $this->fee_settings_product_cost_meta, true ) : '';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fee_settings_product_cost() {

		$fee_cost = trim( $this->get_fee_settings_product_cost() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fee_settings_product_cost a phone number string
	 * @return bool
	 */
	public function set_fee_settings_product_cost( $fee_settings_product_cost ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->fee_settings_product_cost_meta, trim( $fee_settings_product_cost ) );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_fee_chk_qty_price() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->fee_chk_qty_price_meta, true ) : '';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fee_chk_qty_price() {

		$fee_cost = trim( $this->get_fee_chk_qty_price() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fee_chk_qty_price a phone number string
	 * @return bool
	 */
	public function set_fee_chk_qty_price( $fee_chk_qty_price ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->fee_chk_qty_price_meta, trim( $fee_chk_qty_price ) );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_fee_per_qty() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->fee_per_qty_meta, true ) : '';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fee_per_qty() {

		$fee_cost = trim( $this->get_fee_per_qty() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fee_per_qty a phone number string
	 * @return bool
	 */
	public function set_fee_per_qty( $fee_per_qty ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->fee_per_qty_meta, trim( $fee_per_qty ) );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_extra_product_cost() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->extra_product_cost_meta, true ) : '';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_extra_product_cost() {

		$fee_cost = trim( $this->get_extra_product_cost() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $extra_product_cost a phone number string
	 * @return bool
	 */
	public function set_extra_product_cost( $extra_product_cost ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->extra_product_cost_meta, trim( $extra_product_cost ) );
		}

		return (bool) $success;
	}


    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_is_allow_custom_weight_base() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->is_allow_custom_weight_base_meta, true ) : 'no';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_is_allow_custom_weight_base() {

		$fee_cost = trim( $this->get_is_allow_custom_weight_base() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $is_allow_custom_weight_base a phone number string
	 * @return bool
	 */
	public function set_is_allow_custom_weight_base( $is_allow_custom_weight_base ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->is_allow_custom_weight_base_meta, trim( $is_allow_custom_weight_base ) );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_sm_custom_weight_base_cost() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->sm_custom_weight_base_cost_meta, true ) : '';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_sm_custom_weight_base_cost() {

		$fee_cost = trim( $this->get_sm_custom_weight_base_cost() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $sm_custom_weight_base_cost a phone number string
	 * @return bool
	 */
	public function set_sm_custom_weight_base_cost( $sm_custom_weight_base_cost ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->sm_custom_weight_base_cost_meta, trim( $sm_custom_weight_base_cost ) );
		}

		return (bool) $success;
	}

    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_sm_custom_weight_base_per_each() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->sm_custom_weight_base_per_each_meta, true ) : '';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_sm_custom_weight_base_per_each() {

		$fee_cost = trim( $this->get_sm_custom_weight_base_per_each() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $sm_custom_weight_base_per_each a phone number string
	 * @return bool
	 */
	public function set_sm_custom_weight_base_per_each( $sm_custom_weight_base_per_each ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->sm_custom_weight_base_per_each_meta, trim( $sm_custom_weight_base_per_each ) );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_sm_custom_weight_base_over() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->sm_custom_weight_base_over_meta, true ) : '';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_sm_custom_weight_base_over() {

		$fee_cost = trim( $this->get_sm_custom_weight_base_over() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $sm_custom_weight_base_over a phone number string
	 * @return bool
	 */
	public function set_sm_custom_weight_base_over( $sm_custom_weight_base_over ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->sm_custom_weight_base_over_meta, trim( $sm_custom_weight_base_over ) );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_wcpfc_tooltip_description() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->wcpfc_tooltip_description_meta, true ) : '';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_wcpfc_tooltip_description() {

		$fee_cost = trim( $this->get_wcpfc_tooltip_description() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $wcpfc_tooltip_description a phone number string
	 * @return bool
	 */
	public function set_wcpfc_tooltip_description( $wcpfc_tooltip_description ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->wcpfc_tooltip_description_meta, trim( $wcpfc_tooltip_description ) );
		}

		return (bool) $success;
	}


    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_fee_settings_select_taxable() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->fee_settings_select_taxable_meta, true ) : 'no';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fee_settings_select_taxable() {

		$fee_cost = trim( $this->get_fee_settings_select_taxable() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fee_settings_select_taxable a phone number string
	 * @return bool
	 */
	public function set_fee_settings_select_taxable( $fee_settings_select_taxable ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->fee_settings_select_taxable_meta, trim( $fee_settings_select_taxable ) );
		}

		return (bool) $success;
	}

    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_fee_settings_select_optional() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->fee_settings_select_optional_meta, true ) : 'no';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fee_settings_select_optional() {

		$fee_cost = trim( $this->get_fee_settings_select_optional() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fee_settings_select_optional a phone number string
	 * @return bool
	 */
	public function set_fee_settings_select_optional( $fee_settings_select_optional ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->fee_settings_select_optional_meta, trim( $fee_settings_select_optional ) );
		}

		return (bool) $success;
	}

    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_fee_settings_optional_type() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->fee_settings_optional_type_meta, true ) : '';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fee_settings_optional_type() {

		$fee_cost = trim( $this->get_fee_settings_optional_type() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fee_settings_optional_type a phone number string
	 * @return bool
	 */
	public function set_fee_settings_optional_type( $fee_settings_optional_type ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->fee_settings_optional_type_meta, trim( $fee_settings_optional_type ) );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_default_optional_checked() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->default_optional_checked_meta, true ) : '';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_default_optional_checked() {

		$fee_cost = trim( $this->get_default_optional_checked() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $default_optional_checked a phone number string
	 * @return bool
	 */
	public function set_default_optional_checked( $default_optional_checked ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->default_optional_checked_meta, trim( $default_optional_checked ) );
		}

		return (bool) $success;
	}

	/**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_optional_fees_in_cart() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->optional_fees_in_cart_meta, true ) : '';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_optional_fees_in_cart() {

		$fee_cost = trim( $this->get_optional_fees_in_cart() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $optional_fees_in_cart a phone number string
	 * @return bool
	 */
	public function set_optional_fees_in_cart( $optional_fees_in_cart ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->optional_fees_in_cart_meta, trim( $optional_fees_in_cart ) );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_fee_settings_optional_description() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->fee_settings_optional_description_meta, true ) : 'no';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fee_settings_optional_description() {

		$fee_cost = trim( $this->get_fee_settings_optional_description() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fee_settings_optional_description a phone number string
	 * @return bool
	 */
	public function set_fee_settings_optional_description( $fee_settings_optional_description ) {

		$success = false;

		if ( $this->id > 0 && ! empty( $fee_settings_optional_description ) ) {
			$success = update_post_meta( $this->id, $this->fee_settings_optional_description_meta, trim( $fee_settings_optional_description ) );
		}

		return (bool) $success;
	}

    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_first_order_for_user() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->first_order_for_user_meta, true ) : 'no';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_first_order_for_user() {

		$fee_cost = trim( $this->get_first_order_for_user() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $first_order_for_user a phone number string
	 * @return bool
	 */
	public function set_first_order_for_user( $first_order_for_user ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->first_order_for_user_meta, trim( $first_order_for_user ) );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_fee_settings_recurring() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->fee_settings_recurring_meta, true ) : 'no';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fee_settings_recurring() {

		$fee_cost = trim( $this->get_fee_settings_recurring() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fee_settings_recurring a phone number string
	 * @return bool
	 */
	public function set_fee_settings_recurring( $fee_settings_recurring ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->fee_settings_recurring_meta, trim( $fee_settings_recurring ) );
		}

		return (bool) $success;
	}

    
     /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_fee_show_on_checkout_only() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->fee_show_on_checkout_only_meta, true ) : 'no';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fee_show_on_checkout_only() {

		$fee_cost = trim( $this->get_fee_show_on_checkout_only() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fee_show_on_checkout_only a phone number string
	 * @return bool
	 */
	public function set_fee_show_on_checkout_only( $fee_show_on_checkout_only ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->fee_show_on_checkout_only_meta, trim( $fee_show_on_checkout_only ) );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_ds_select_day_of_week() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->ds_select_day_of_week_meta, true ) : array();
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_ds_select_day_of_week() {

		$fee_cost = trim( $this->get_ds_select_day_of_week() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ds_select_day_of_week a phone number string
	 * @return bool
	 */
	public function set_ds_select_day_of_week( $ds_select_day_of_week ) {

		$success = false;

		if ( $this->id > 0 && is_array( $ds_select_day_of_week ) ) {
			$success = update_post_meta( $this->id, $this->ds_select_day_of_week_meta, array_map( 'sanitize_text_field', $ds_select_day_of_week ) );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_fee_settings_start_date( $format = false ) {

        $start_date = $this->id > 0 ? get_post_meta( $this->id, $this->fee_settings_start_date_meta, true ) : '';

        if( $format && $start_date ) {
            $start_date = gmdate( get_option( 'date_format' ), $start_date );
        }

		return $start_date;
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fee_settings_start_date() {

		$fee_cost = trim( $this->get_fee_settings_start_date() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fee_settings_start_date a phone number string
	 * @return bool
	 */
	public function set_fee_settings_start_date( $fee_settings_start_date ) {

		$success = false;

        if( ! is_numeric( $fee_settings_start_date ) && ! is_null( $fee_settings_start_date ) ) {
            $fee_settings_start_date = strtotime( $fee_settings_start_date );
        }

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->fee_settings_start_date_meta, $fee_settings_start_date );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_fee_settings_end_date( $format = false ) {

		$end_date = $this->id > 0 ? get_post_meta( $this->id, $this->fee_settings_end_date_meta, true ) : '';

        if( $format && $end_date ) {
            $end_date = gmdate( get_option( 'date_format' ), $end_date );
        }

        return $end_date;
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_fee_settings_end_date() {

		$fee_cost = trim( $this->get_fee_settings_end_date() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fee_settings_end_date a phone number string
	 * @return bool
	 */
	public function set_fee_settings_end_date( $fee_settings_end_date ) {

		$success = false;

        if( ! is_numeric( $fee_settings_end_date ) && ! is_null( $fee_settings_end_date ) ) {
            $fee_settings_end_date = strtotime( $fee_settings_end_date );
        }

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->fee_settings_end_date_meta, $fee_settings_end_date );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_ds_time_from( $format = false ) {

		$time_from = $this->id > 0 ? get_post_meta( $this->id, $this->ds_time_from_meta, true ) : '';

        if( $format && $time_from ) {
            $time_from = gmdate( get_option( 'time_format' ), strtotime( $time_from ) );
        }

        return $time_from;
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_ds_time_from() {

		$fee_cost = trim( $this->get_ds_time_from() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ds_time_from a phone number string
	 * @return bool
	 */
	public function set_ds_time_from( $ds_time_from ) {

		$success = false;

        if( ! is_numeric( $ds_time_from ) && ! is_null( $ds_time_from ) ) {
            $ds_time_from = strtotime( $ds_time_from );
        }

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->ds_time_from_meta, $ds_time_from );
		}

		return (bool) $success;
	}
    

    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_ds_time_to( $format = false ) {

		$time_to = $this->id > 0 ? get_post_meta( $this->id, $this->ds_time_to_meta, true ) : '';

        if( $format && $time_to ) {
            $time_to = gmdate( get_option( 'time_format' ), strtotime( $time_to ) );
        }
        return $time_to;
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_ds_time_to() {

		$fee_cost = trim( $this->get_ds_time_to() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ds_time_to a phone number string
	 * @return bool
	 */
	public function set_ds_time_to( $ds_time_to ) {

		$success = false;

        if( ! is_numeric( $ds_time_to ) && ! is_null( $ds_time_to ) ) {
            $ds_time_to = strtotime( $ds_time_to );
        }

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->ds_time_to_meta, $ds_time_to );
		}

		return (bool) $success;
	}

    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_product_fees_metabox() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->product_fees_metabox_meta, true ) : '';
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_product_fees_metabox() {

		$fee_cost = trim( $this->get_product_fees_metabox() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param array $product_fees_metabox a conditional rule array
	 * @return bool
	 */
	public function set_product_fees_metabox( $product_fees_metabox ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->product_fees_metabox_meta, $product_fees_metabox );
		}

		return (bool) $success;
	}

    
    /**
     * Get fee type.
     *
     * @since 1.0.0
     *
     * @return string\array
     */
	public function get_cost_rule_match( $key = '' ) {

        $cost_rule_match = $this->id > 0 ? get_post_meta( $this->id, $this->cost_rule_match_meta, true ) : array();

        if ( ! empty( $cost_rule_match ) ) {

            if ( is_serialized( $cost_rule_match ) ) {
                $cost_rule_match = maybe_unserialize( $cost_rule_match );
            }
            if( !empty( $key ) ) {
                if ( array_key_exists( $key, $cost_rule_match ) ) {
                    $cost_rule_match = $cost_rule_match[$key];
                } else {
                    $cost_rule_match = 'all';
                }
            }
        }

        return $cost_rule_match;
	}

    /**
	 * Whether the fee_cost has value or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_cost_rule_match() {

		$fee_cost = trim( $this->get_cost_rule_match() );

		return ! empty( $fee_cost );
	}

    /**
	 * Set fee cost.
	 *
	 * @since 1.0.0
	 *
	 * @param string $cost_rule_match a phone number string
	 * @return bool
	 */
	public function set_cost_rule_match( $cost_rule_match ) {

		$success = false;

		if ( $this->id > 0 ) {
			$success = update_post_meta( $this->id, $this->cost_rule_match_meta, maybe_serialize( $cost_rule_match ) );
		}

		return (bool) $success;
	}

	/**
     * Get advanced pricing rule status.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_ap_rule_status() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->ap_rule_status_meta, true ) : '';
	}

	/**
     * Get cost on product status.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_cost_on_product_status() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->cost_on_product_status_meta, true ) : '';
	}

	/**
     * Get cost on product subtotal status.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_cost_on_product_subtotal_status() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->cost_on_product_subtotal_status_meta, true ) : '';
	}

	/**
     * Get cost on category status.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_cost_on_category_status() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->cost_on_category_status_meta, true ) : '';
	}

	/**
     * Get cost on product weight status.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_cost_on_product_weight_status() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->cost_on_product_weight_status_meta, true ) : '';
	}

	/**
     * Get cost on category subtotal status
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_cost_on_category_subtotal_status() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->cost_on_category_subtotal_status_meta, true ) : '';
	}

	/**
     * Get cost on category weight status meta
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_cost_on_category_weight_status() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->cost_on_category_weight_status_meta, true ) : '';
	}

	/**
     * Get cost on category status.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_cost_on_total_cart_qty_status() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->cost_on_total_cart_qty_status_meta, true ) : '';
	}

	/**
     * Get cost on category status.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_cost_on_total_cart_weight_status() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->cost_on_total_cart_weight_status_meta, true ) : '';
	}

	/**
     * Get cost on category status.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_cost_on_total_cart_subtotal_status() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->cost_on_total_cart_subtotal_status_meta, true ) : '';
	}

	/**
     * Get cost on category status.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_cost_on_shipping_class_subtotal_status() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->cost_on_shipping_class_subtotal_status_meta, true ) : '';
	}


	/**
     * Get sm metabox ap product.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_sm_metabox_ap_product() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->sm_metabox_ap_product_meta, true ) : '';
	}

	/**
     * Get sm metabox ap product subtotal.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_sm_metabox_ap_product_subtotal() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->sm_metabox_ap_product_subtotal_meta, true ) : '';
	}

	/**
     * Get sm metabox ap product subtotal.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_sm_metabox_ap_category() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->sm_metabox_ap_category_meta, true ) : '';
	}

	/**
     * Get sm metabox ap product subtotal.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_sm_metabox_ap_product_weight() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->sm_metabox_ap_product_weight_meta, true ) : '';
	}

	/**
     * Get sm metabox ap product subtotal.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_sm_metabox_ap_category_subtotal() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->sm_metabox_ap_category_subtotal_meta, true ) : '';
	}

	/**
     * Get sm metabox ap product subtotal.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_sm_metabox_ap_category_weight() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->sm_metabox_ap_category_weight_meta, true ) : '';
	}

	/**
     * Get sm metabox ap product subtotal.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_sm_metabox_ap_total_cart_qty() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->sm_metabox_ap_total_cart_qty_meta, true ) : '';
	}

	/**
     * Get sm metabox ap product subtotal.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_sm_metabox_ap_total_cart_weight() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->sm_metabox_ap_total_cart_weight_meta, true ) : '';
	}

	/**
     * Get sm metabox ap product subtotal.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_sm_metabox_ap_total_cart_subtotal() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->sm_metabox_ap_total_cart_subtotal_meta, true ) : '';
	}

	/**
     * Get sm metabox ap product subtotal.
     *
     * @since 1.0.0
     *
     * @return string
     */
	public function get_sm_metabox_ap_shipping_class_subtotal() {

		return $this->id > 0 ? get_post_meta( $this->id, $this->sm_metabox_ap_shipping_class_subtotal_meta, true ) : '';
	}
}


