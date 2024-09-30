<?php

/**
 * Woocommerce Conditional Product Fees Conditional Rules validation and checks for frontend side.
 *
 * @version 1.0.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( class_exists( 'Woocommerce_Conditional_Product_Fees_Conditional_Rules', false ) ) {
    return new Woocommerce_Conditional_Product_Fees_Conditional_Rules(0);
}
/**
 * Woocommerce_Conditional_Product_Fees_Conditional_Rules.
 */
#[\AllowDynamicProperties]
class Woocommerce_Conditional_Product_Fees_Conditional_Rules {
    /** @var int ID of the corresponding fee */
    private $conditional_fee;

    /** @var int advance fee (post) unique ID */
    protected $id = 0;

    /** @var string cost_rule_match */
    private $cost_rule_match = '';

    /** @var array product_fees_metabox */
    private $product_fees_metabox = array();

    /** @var array county specific variable */
    private $country_array = array();

    /** @var array state specific variable */
    private $state_array = array();

    /** @var array city specific variable */
    private $city_array = array();

    /** @var array postcode specific variable */
    private $postcode_array = array();

    /** @var array zone specific variable */
    private $zone_array = array();

    /** @var array product specific variable */
    private $product_array = array();

    /** @var array product specific variable */
    private $variable_product_array = array();

    /** @var array category specific variable */
    private $category_array = array();

    /** @var array tag specific variable */
    private $tag_array = array();

    /** @var array product_qty specific variable */
    private $product_qty_array = array();

    /** @var array user specific variable */
    private $user_array = array();

    /** @var array user_role specific variable */
    private $user_role_array = array();

    /** @var array cart_total specific variable */
    private $cart_total_array = array();

    /** @var array total_spent_order specific variable */
    private $total_spent_order_array = array();

    /** @var array spent_order_count specific variable */
    private $spent_order_count_array = array();

    /** @var array last_spent_order specific variable */
    private $last_spent_order_array = array();

    /** @var array cart_totalafter specific variable */
    private $cart_totalafter_array = array();

    /** @var array cart_productspecific specific variable */
    private $cart_productspecific_array = array();

    /** @var array cart total excluding tax specific variable */
    private $cart_total_excluding_tax_array = array();

    /** @var array cart row total specific variable */
    private $cart_rowtotal_array = array();

    /** @var array quantity specific variable */
    private $quantity_array = array();

    /** @var array weight specific variable */
    private $weight_array = array();

    /** @var array coupon specific variable */
    private $coupon_array = array();

    /** @var array shipping_class specific variable */
    private $shipping_class_array = array();

    /** @var array payment specific variable */
    private $payment_array = array();

    /** @var array shipping_method specific variable */
    private $shipping_method_array = array();

    /** @var array product_attribute specific variable */
    private $product_attribute_array = array();

    /**
     * Fee constructor.
     *
     * @since 1.0.0
     *
     * @param int|\WP_Post|\Woocommerce_Conditional_Product_Fees $data the post or advance fee ID, object
     */
    public function __construct( $data ) {
        if ( empty( $data ) ) {
            return;
        }
        $this->conditional_fee = new \Woocommerce_Conditional_Product_Fees($data);
        if ( $this->conditional_fee instanceof \Woocommerce_Conditional_Product_Fees ) {
            // set post type data
            $this->id = (int) $this->conditional_fee->get_id();
            $this->cost_rule_match = $this->conditional_fee->get_cost_rule_match( 'general_rule_match' );
            $product_fees_metabox = $this->conditional_fee->get_product_fees_metabox();
            // parse the product fees metabox
            $this->parse_product_fees_metabox( $product_fees_metabox );
        }
    }

    /**
     * Parse the product fees metabox.
     * 
     * @param array $product_fees_metabox
     * 
     * @since 1.0.0
     */
    public function parse_product_fees_metabox( $product_fees_metabox ) {
        $this->product_fees_metabox = $product_fees_metabox;
        if ( !empty( $product_fees_metabox ) ) {
            foreach ( $product_fees_metabox as $key => $value ) {
                // Location specific
                if ( array_search( 'country', $value, true ) ) {
                    $this->country_array[$key] = $value;
                }
                if ( array_search( 'city', $value, true ) ) {
                    $this->city_array[$key] = $value;
                }
                // Product specific
                if ( array_search( 'product', $value, true ) ) {
                    $this->product_array[$key] = $value;
                }
                if ( array_search( 'variableproduct', $value, true ) ) {
                    $this->variable_product_array[$key] = $value;
                }
                if ( array_search( 'category', $value, true ) ) {
                    $this->category_array[$key] = $value;
                }
                if ( array_search( 'tag', $value, true ) ) {
                    $this->tag_array[$key] = $value;
                }
                if ( array_search( 'product_qty', $value, true ) ) {
                    $this->product_qty_array[$key] = $value;
                }
                // User specific
                if ( array_search( 'user', $value, true ) ) {
                    $this->user_array[$key] = $value;
                }
                // Cart specific
                if ( array_search( 'cart_total', $value, true ) ) {
                    $this->cart_total_array[$key] = $value;
                }
                if ( array_search( 'quantity', $value, true ) ) {
                    $this->quantity_array[$key] = $value;
                }
                // Product attribute specific
                foreach ( wc_get_attribute_taxonomies() as $attribute ) {
                    $att_name = wc_attribute_taxonomy_name( $attribute->attribute_name );
                    if ( array_search( $att_name, $value, true ) ) {
                        // If user add same attribute multiple time then merge the values
                        if ( array_key_exists( $att_name, $this->product_attribute_array ) ) {
                            $this->product_attribute_array[$att_name]['product_fees_conditions_values'] = array_merge( $this->product_attribute_array[$att_name]['product_fees_conditions_values'], $value['product_fees_conditions_values'] );
                        } else {
                            $this->product_attribute_array[$att_name] = $value;
                        }
                    }
                }
            }
        }
    }

    /**
     * Get the fee rule match value.
     * 
     * @since 1.0.0
     */
    public function get_rule_match() {
        return $this->cost_rule_match;
    }

    /**
     * Get product id and variation id from cart
     *
     * @return array $cart_array
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_get_cart() {
        $cart_array = WC()->cart->get_cart();
        return $cart_array;
    }

    /**
     * Check all conditional rule validation and return the result.
     * 
     * @return boolen
     * 
     * @since 1.0.0
     */
    public function is_fee_passed_conditional_rule_validation( $fees_id ) {
        global $sitepress;
        if ( is_admin() ) {
            return true;
        }
        // Check if product fees metabox is empty then fee will apply to all products
        if ( empty( $this->product_fees_metabox ) ) {
            return true;
        }
        $is_passed = array();
        $admin_object = new \Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Admin('', '');
        $default_lang = $admin_object->wcpfc_pro_get_default_langugae_with_sitpress();
        $cart_product_ids_array = wcpfc_pro_public()->wcpfc_pro_get_prd_var_id( $sitepress, $default_lang );
        $cart_array = wcpfc_pro_public()->wcpfc_pro_get_cart();
        $wc_curr_version = wcpfc_pro_public()->wcpfc_pro_get_woo_version_number();
        $get_condition_array = get_post_meta( $fees_id, 'product_fees_metabox', true );
        if ( wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code() ) {
            $variation_cart_products_array = wcpfc_pro_public()->wcpfc_pro_get_var_name__premium_only( $sitepress, $default_lang );
        }
        //Check if is country exist
        if ( isset( $this->country_array ) && !empty( $this->country_array ) && is_array( $this->country_array ) ) {
            $country_passed = wcpfc_pro_public()->wcpfc_pro_match_country_rules( $this->country_array, $this->cost_rule_match );
            if ( 'yes' === $country_passed ) {
                $is_passed['has_fee_based_on_country'] = true;
            } else {
                $is_passed['has_fee_based_on_country'] = false;
            }
        }
        // Check if is city exist
        if ( isset( $this->city_array ) && !empty( $this->city_array ) && is_array( $this->city_array ) ) {
            $city_passed = wcpfc_pro_public()->wcpfc_pro_match_city_rules( $this->city_array, $this->cost_rule_match );
            if ( 'yes' === $city_passed ) {
                $is_passed['has_fee_based_on_city'] = true;
            } else {
                $is_passed['has_fee_based_on_city'] = false;
            }
        }
        // Check if is product exist
        if ( isset( $this->product_array ) && !empty( $this->product_array ) && is_array( $this->product_array ) ) {
            $product_passed = wcpfc_pro_public()->wcpfc_pro_match_simple_products_rule( $cart_product_ids_array, $this->product_array, $this->cost_rule_match );
            if ( 'yes' === $product_passed ) {
                $is_passed['has_fee_based_on_product'] = true;
            } else {
                $is_passed['has_fee_based_on_product'] = false;
            }
        }
        // Check if is variable product exist
        if ( isset( $this->variable_product_array ) && !empty( $this->variable_product_array ) && is_array( $this->variable_product_array ) ) {
            $variable_product_passed = wcpfc_pro_public()->wcpfc_pro_match_variable_products_rule( $cart_product_ids_array, $this->variable_product_array, $this->cost_rule_match );
            if ( 'yes' === $variable_product_passed ) {
                $is_passed['has_fee_based_on_variable_prd'] = true;
            } else {
                $is_passed['has_fee_based_on_variable_prd'] = false;
            }
        }
        // Check if is category exist
        if ( isset( $this->category_array ) && !empty( $this->category_array ) && is_array( $this->category_array ) ) {
            $category_passed = wcpfc_pro_public()->wcpfc_pro_match_category_rule( $cart_product_ids_array, $this->category_array, $this->cost_rule_match );
            if ( 'yes' === $category_passed ) {
                $is_passed['has_fee_based_on_category'] = true;
            } else {
                $is_passed['has_fee_based_on_category'] = false;
            }
        }
        // Check if is tag exist
        if ( isset( $this->tag_array ) && !empty( $this->tag_array ) && is_array( $this->tag_array ) ) {
            $tag_passed = wcpfc_pro_public()->wcpfc_pro_match_tag_rule( $cart_product_ids_array, $this->tag_array, $this->cost_rule_match );
            if ( 'yes' === $tag_passed ) {
                $is_passed['has_fee_based_on_tag'] = true;
            } else {
                $is_passed['has_fee_based_on_tag'] = false;
            }
        }
        // Check if is product qty exist
        if ( isset( $this->product_qty_array ) && !empty( $this->product_qty_array ) && is_array( $this->product_qty_array ) ) {
            $product_qty_passed = wcpfc_pro_public()->wcpfc_pro_match_product_qty_rule(
                $fees_id,
                $cart_array,
                $this->product_qty_array,
                $this->cost_rule_match,
                $sitepress,
                $default_lang
            );
            if ( 'yes' === $product_qty_passed ) {
                $is_passed['has_fee_based_on_product_qty'] = true;
            } else {
                $is_passed['has_fee_based_on_product_qty'] = false;
            }
        }
        // Check if is user exist
        if ( isset( $this->user_array ) && !empty( $this->user_array ) && is_array( $this->user_array ) ) {
            $user_passed = wcpfc_pro_public()->wcpfc_pro_match_user_rule( $this->user_array, $this->cost_rule_match );
            if ( 'yes' === $user_passed ) {
                $is_passed['has_fee_based_on_user'] = true;
            } else {
                $is_passed['has_fee_based_on_user'] = false;
            }
        }
        // Check if is cart total (Before Discount) exist
        if ( isset( $this->cart_total_array ) && !empty( $this->cart_total_array ) && is_array( $this->cart_total_array ) ) {
            $cart_total_passed = wcpfc_pro_public()->wcpfc_pro_match_cart_subtotal_before_discount_rule( $wc_curr_version, $this->cart_total_array, $this->cost_rule_match );
            if ( 'yes' === $cart_total_passed ) {
                $is_passed['has_fee_based_on_cart_total'] = true;
            } else {
                $is_passed['has_fee_based_on_cart_total'] = false;
            }
        }
        // Check if is quantity exist
        if ( isset( $this->quantity_array ) && !empty( $this->quantity_array ) && is_array( $this->quantity_array ) ) {
            $quantity_passed = wcpfc_pro_public()->wcpfc_pro_match_cart_total_cart_qty_rule( $cart_array, $this->quantity_array, $this->cost_rule_match );
            if ( 'yes' === $quantity_passed ) {
                $is_passed['has_fee_based_on_quantity'] = true;
            } else {
                $is_passed['has_fee_based_on_quantity'] = false;
            }
        }
        if ( !empty( $is_passed ) && is_array( $is_passed ) ) {
            return ( 'any' === $this->get_rule_match() ? in_array( true, $is_passed, true ) : !in_array( false, $is_passed, true ) );
        }
    }

    /**
     * Check cost rule match for product or category.
     * 
     * @param int    $fees_id      The fee ID.
     * @param string $rule_type    The type of rule to check ('product' or 'category').
     * 
     * @return string The cost rule match ('any' if not found).
     * 
     * @since 1.0.0
     */
    public function cost_on_rule_match( $fees_id, $rule_type ) {
        $conditional_fee = new \Woocommerce_Conditional_Product_Fees($fees_id);
        $cost_rule_match = $conditional_fee->get_cost_rule_match();
        // Determine the key based on the rule type
        $rule_key = 'cost_on_' . $rule_type . '_rule_match';
        // Check if the rule exists, otherwise default to 'any'
        if ( array_key_exists( $rule_key, $cost_rule_match ) ) {
            $cost_on_rule_match = $cost_rule_match[$rule_key];
        } else {
            $cost_on_rule_match = 'any';
        }
        return $cost_on_rule_match;
    }

    /**
     * Check all advanced pricing rule validation and return the result.
     * 
     * @return boolen
     * 
     * @since 1.0.0
     */
    public function is_fee_passed_advanced_pricing_rule_validation( $fees_id ) {
        global $sitepress;
        $conditional_fee = new \Woocommerce_Conditional_Product_Fees($fees_id);
        $fee_cost = 0;
        return $fee_cost;
    }

    /**
     * Find unique id based on given array
     *
     * @param array  $is_passed
     * @param string $has_fee_based
     * @param string $general_rule_match
     *
     * @return string $main_is_passed
     * 
     * @since    1.0.0
     * 
     * @internal
     */
    public function wcpfc_pro_check_all_passed_general_rule( $is_passed, $has_fee_based, $general_rule_match ) {
        $main_is_passed = false;
        $flag = array();
        if ( !empty( $is_passed ) ) {
            foreach ( $is_passed as $key => $is_passed_value ) {
                if ( true === $is_passed_value[$has_fee_based] ) {
                    $flag[$key] = true;
                } else {
                    $flag[$key] = false;
                }
            }
            $main_is_passed = ( 'any' === $general_rule_match ? in_array( true, $flag, true ) : !in_array( false, $flag, true ) );
        }
        return $main_is_passed;
    }

}
