<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @subpackage Woocommerce_Conditional_Product_Fees_For_Checkout_Pro/public
 * @author     Multidots <inquiry@multidots.in>
 */
class Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Public {
    private static $admin_object = null;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Extra fee cost
     *
     * @since    3.9.5
     * @access   private
     */
    private $fee_cost;

    /**
     * This is Optional Fees at Checkout meta key.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $wcpfc_fee_revenue   This is Optional Fees at checkout meta key.
     */
    private $wcpfc_fee_revenue = '_wcpfc_fee_revenue';

    /** 
     * The current instance of the plugin
     * 
     * @since   1.0.0
     * @access  protected
     * @var     \Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Public single instance of this plugin 
     */
    protected static $instance;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version     The version of this plugin.
     *
     * @since    1.0.0
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        self::$admin_object = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Admin('', '');
        $this->include_modules();
    }

    /**
     * Include public classes and objects.
     *
     * @since 1.0.0
     */
    private function include_modules() {
        // Fees public edit screens
        require_once plugin_dir_path( dirname( __FILE__ ) ) . '/public/class-woocommerce-product-fees-conditional-rules.php';
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function wcpfc_public_enqueue_styles() {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/woocommerce-conditional-product-fees-for-checkout-public.css',
            array(),
            $this->version,
            'all'
        );
        if ( is_cart() || is_checkout() ) {
            wp_enqueue_style(
                $this->plugin_name . 'font-awesome',
                WCPFC_PRO_PLUGIN_URL . 'admin/css/font-awesome.min.css',
                array(),
                $this->version,
                'all'
            );
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function wcpfc_public_enqueue_scripts() {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'js/woocommerce-conditional-product-fees-for-checkout-public.js',
            array('jquery'),
            $this->version,
            false
        );
    }

    /**
     * Override WooCommerce file in our plugin
     *
     * @param string $template
     * @param string $template_name
     * @param mixed  $template_path
     *
     * @return string $template
     * @since    1.0.0
     *
     *
     */
    public function wcpfc_wc_locate_template_product_fees_conditions( $template, $template_name, $template_path ) {
        global $woocommerce;
        $_template = $template;
        if ( !$template_path ) {
            $template_path = $woocommerce->template_url;
        }
        $plugin_path = wcpfc_pro_path() . '/woocommerce/';
        $template = locate_template( array($template_path . $template_name, $template_name) );
        // Modification: Get the template from this plugin, if it exists
        if ( !$template && file_exists( $plugin_path . $template_name ) ) {
            $template = $plugin_path . $template_name;
        }
        if ( !$template ) {
            $template = $_template;
        }
        // Return what we found
        return $template;
    }

    /**
     * Filter data
     *
     * @param string $string
     *
     * @since    3.9.3
     *
     */
    public function wcpfc_filter_sanitize_string( $string ) {
        $str = preg_replace( '/\\x00|<[^>]*>?/', '', $string );
        return str_replace( ["'", '"'], ['&#39;', '&#34;'], $str );
    }

    /**
     * Display a table of advanced product pricing fees based on a given array of product IDs or category IDs.
     *
     * This function takes an array of fee details, checks if the current product matches 
     * any product ID or belongs to any specified category, and displays the fees in a 
     * table format with corresponding minimum and maximum quantities and fee amounts.
     *
     * @param array $fee_array Array containing fee details for products and categories.
     */
    public function display_advanced_product_pricing_fees( $fee_array, $collect_only = false ) {
        // Use a static variable to hold the rows across multiple calls
        static $rows = [];
        // Get the current product ID
        $current_product_id = get_the_ID();
        $currency_symbol = ( get_woocommerce_currency_symbol() ?: '' );
        // Loop through each fee structure in the array
        foreach ( $fee_array as $fee ) {
            // Loop through each product ID in the 'ap_fees_products' array
            if ( isset( $fee['ap_fees_products'] ) ) {
                foreach ( $fee['ap_fees_products'] as $fee_product_id ) {
                    // Get the product object
                    $product = wc_get_product( $fee_product_id );
                    if ( $product ) {
                        // Check if the product is a variation and get the parent product ID
                        if ( $product->is_type( 'variation' ) ) {
                            $parent_product_id = $product->get_parent_id();
                        } else {
                            $parent_product_id = $fee_product_id;
                            // Simple product, no parent
                        }
                        // Check if the current product ID matches the parent product ID
                        if ( (int) $parent_product_id === (int) $current_product_id ) {
                            // Format each row with product details
                            $min_qty = $fee['ap_fees_ap_prd_min_qty'];
                            $max_qty = ( $fee['ap_fees_ap_prd_max_qty'] ? $fee['ap_fees_ap_prd_max_qty'] : '' );
                            $fee_amount = $fee['ap_fees_ap_price_product'];
                            $rows[] = sprintf(
                                '<tr><td>%s</td><td>%s</td><td>%s%s</td></tr>',
                                esc_html( $min_qty ),
                                esc_html( $max_qty ),
                                esc_html( $currency_symbol ),
                                esc_html( $fee_amount )
                            );
                        }
                    }
                }
            }
            // Check if the current product belongs to any of the specified categories
            if ( isset( $fee['ap_fees_categories'] ) && has_term( $fee['ap_fees_categories'], 'product_cat', $current_product_id ) ) {
                $min_qty = $fee['ap_fees_ap_cat_min_qty'];
                $max_qty = ( $fee['ap_fees_ap_cat_max_qty'] ? $fee['ap_fees_ap_cat_max_qty'] : '' );
                $fee_amount = $fee['ap_fees_ap_price_category'];
                // Format each row with category details
                $rows[] = sprintf(
                    '<tr><td>%s</td><td>%s</td><td>%s%s</td></tr>',
                    esc_html( $min_qty ),
                    esc_html( $max_qty ),
                    esc_html( $currency_symbol ),
                    esc_html( $fee_amount )
                );
            }
        }
        // If we are collecting rows only, do not render the table yet
        if ( $collect_only ) {
            return;
        }
        // Display the table if rows were added
        if ( !empty( $rows ) ) {
            echo '<table class="product-fees-table">';
            echo '<tr><th>' . esc_html__( 'Min Quantity', 'woocommerce-conditional-product-fees-for-checkout' ) . '</th>';
            echo '<th>' . esc_html__( 'Max Quantity', 'woocommerce-conditional-product-fees-for-checkout' ) . '</th>';
            echo '<th>' . esc_html__( 'Fee Amount', 'woocommerce-conditional-product-fees-for-checkout' ) . '</th></tr>';
            // Escape each row before output
            foreach ( $rows as $row ) {
                echo wp_kses_post( $row );
            }
            echo '</table>';
            // Reset the static rows after displaying the table
            $rows = [];
        }
    }

    /**
     * Add fees in cart based on rule
     *
     * @since    1.0.0
     *
     * @uses     Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Admin::wcpfc_pro_get_default_langugae_with_sitpress()
     * @uses     wcpfc_pro_get_woo_version_number()
     * @uses     WC_Cart::get_cart()
     * @uses     wcpfc_pro_fees_per_qty_on_ap_rules_off()
     * @uses     wcpfc_pro_cart_subtotal_before_discount_cost()
     * @uses     wcpfc_pro_cart_subtotal_after_discount_cost()
     * @uses     wcpfc_pro_match_country_rules()
     * @uses     wcpfc_pro_match_city_rules()
     * @uses     wcpfc_pro_match_state_rules__premium_only()
     * @uses     wcpfc_pro_match_postcode_rules__premium_only()
     * @uses     wcpfc_pro_match_zone_rules__premium_only()
     * @uses     wcpfc_pro_match_variable_products_rule()
     * @uses     wcpfc_pro_match_simple_products_rule()
     * @uses     wcpfc_pro_match_category_rule()
     * @uses     wcpfc_pro_match_tag_rule()
     * @uses 	 wcpfc_pro_match_product_qty_rule()
     * @uses     wcpfc_pro_match_user_rule()
     * @uses     wcpfc_pro_match_user_role_rule__premium_only()
     * @uses     wcpfc_pro_match_coupon_rule__premium_only()
     * @uses     wcpfc_pro_match_cart_subtotal_before_discount_rule()
     * @uses     wcpfc_pro_match_cart_subtotal_after_discount_rule__premium_only()
     * @uses	 wcpfc_pro_match_cart_subtotal_specific_product_rule__premium_only()
     * @uses     wcpfc_pro_match_cart_total_cart_qty_rule()
     * @uses     wcpfc_pro_match_cart_total_weight_rule__premium_only()
     * @uses     wcpfc_pro_match_shipping_class_rule__premium_only()
     * @uses     wcpfc_pro_match_payment_gateway_rule__premium_only()
     * @uses     wcpfc_pro_match_shipping_method_rule__premium_only()
     * @uses     wcpfc_pro_match_product_per_qty__premium_only()
     * @uses     wcpfc_pro_match_category_per_qty__premium_only()
     * @uses     wcpfc_pro_match_total_cart_qty__premium_only()
     * @uses     wcpfc_pro_match_product_per_weight__premium_only()
     * @uses     wcpfc_pro_match_category_per_weight__premium_only()
     * @uses     wcpfc_pro_match_total_cart_weight__premium_only()
     * @uses     wcpfc_pro_calculate_advance_pricing_rule_fees()
     *
     */
    public function wcpfc_pro_conditional_fee_add_to_cart() {
        global $woocommerce_wpml, $sitepress, $woocommerce;
        $wcpfc_checkout_data = filter_input(
            INPUT_POST,
            'post_data',
            FILTER_CALLBACK,
            array(
                'options' => array($this, 'wcpfc_filter_sanitize_string'),
            )
        );
        if ( isset( $wcpfc_checkout_data ) ) {
            parse_str( $wcpfc_checkout_data, $post_data );
        } else {
            $post_data = filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        }
        $default_lang = self::$admin_object->wcpfc_pro_get_default_langugae_with_sitpress();
        $get_all_fees = get_transient( 'get_all_fees' );
        if ( false === $get_all_fees ) {
            $fees_args = array(
                'post_type'        => 'wc_conditional_fee',
                'post_status'      => 'publish',
                'posts_per_page'   => -1,
                'suppress_filters' => false,
                'fields'           => 'ids',
                'order'            => 'DESC',
                'orderby'          => 'ID',
            );
            $get_all_fees_query = new WP_Query($fees_args);
            $get_all_fees = $get_all_fees_query->get_posts();
            set_transient( 'get_all_fees', $get_all_fees );
        }
        $wc_curr_version = $this->wcpfc_pro_get_woo_version_number();
        $cart_array = $this->wcpfc_pro_get_cart();
        $cart_main_product_ids_array = $this->wcpfc_pro_get_main_prd_id( $sitepress, $default_lang );
        $cart_product_ids_array = $this->wcpfc_pro_get_prd_var_id( $sitepress, $default_lang );
        if ( wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code() ) {
            $variation_cart_products_array = $this->wcpfc_pro_get_var_name__premium_only( $sitepress, $default_lang );
        }
        $cart_sub_total = WC()->cart->cart_contents_total;
        $total_fee = 0;
        $chk_enable_custom_fun = get_option( 'chk_enable_custom_fun' );
        $getFeesOptional = '';
        $general_rule_match = '';
        $total_cart_qty_n_combination = array();
        // Query start for tax calculation based on the selected class from products
        $items = $woocommerce->cart->get_cart();
        // Get the cart items
        $item_tax_class = array();
        if ( !empty( $items ) && is_array( $items ) ) {
            foreach ( $items as $item ) {
                $product = $item['data'];
                // Get the product object
                $item_tax_class[] = $product->get_tax_class();
                // Get the tax class
            }
        }
        $items_tax_classes = ( !empty( $item_tax_class ) && is_array( $item_tax_class ) ? $item_tax_class : array() );
        $new_item_tax_class = array();
        if ( empty( $items_tax_classes ) ) {
            $new_item_tax_class = array();
        } else {
            $new_item_tax_class = array_unique( $items_tax_classes );
        }
        $all_wc_tax_class = array();
        $wc_tax_classes = WC_Tax::get_tax_rate_classes();
        if ( !empty( $wc_tax_classes ) && is_array( $wc_tax_classes ) ) {
            foreach ( $wc_tax_classes as $wc_tax_class ) {
                $all_wc_tax_class[] = $wc_tax_class->slug;
            }
        }
        $final_item_tax_class = '';
        if ( in_array( '', $new_item_tax_class, true ) ) {
            $final_item_tax_class = '';
        } else {
            if ( !empty( $new_item_tax_class ) && is_array( $new_item_tax_class ) ) {
                foreach ( $new_item_tax_class as $new_item_tax ) {
                    if ( in_array( $new_item_tax, $all_wc_tax_class, true ) ) {
                        $final_item_tax_class = $new_item_tax;
                    }
                }
            }
        }
        // Query end for tax calculation based on the selected class from products
        if ( isset( $get_all_fees ) && !empty( $get_all_fees ) ) {
            foreach ( $get_all_fees as $fees ) {
                if ( !empty( $sitepress ) ) {
                    $fees_id = apply_filters(
                        'wpml_object_id',
                        $fees,
                        'wc_conditional_fee',
                        true,
                        $default_lang
                    );
                } else {
                    $fees_id = $fees;
                }
                $optional_fee_array = ( isset( $post_data['wef_fees_id_array_' . $fees_id] ) && !empty( $post_data['wef_fees_id_array_' . $fees_id] ) ? array_map( 'intval', $post_data['wef_fees_id_array_' . $fees_id] ) : array() );
                if ( !empty( $sitepress ) ) {
                    if ( version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
                        $language_information = apply_filters( 'wpml_post_language_details', null, $fees_id );
                    } else {
                        $language_information = wpml_get_language_information( $fees_id );
                    }
                    if ( is_array( $language_information ) && isset( $language_information['language_code'] ) ) {
                        $post_id_language_code = $language_information['language_code'];
                    } else {
                        $post_id_language_code = $default_lang;
                    }
                } else {
                    $post_id_language_code = $default_lang;
                }
                if ( $post_id_language_code === $default_lang ) {
                    $is_passed = array();
                    $final_is_passed_general_rule = array();
                    $new_is_passed = array();
                    $final_passed = array();
                    $cart_based_qty = 0;
                    $cart_based_weight = 0;
                    $apply_rule_for_optional = false;
                    $display_optional_fee_on_checkout = 'on';
                    if ( in_array( $fees_id, $optional_fee_array, true ) ) {
                        $apply_rule_for_optional = true;
                    }
                    if ( isset( $cart_array ) && !empty( $cart_array ) ) {
                        foreach ( $cart_array as $woo_cart_item_for_qty ) {
                            $product_id = $woo_cart_item_for_qty['product_id'];
                            $product_type = WC_Product_Factory::get_product_type( $product_id );
                            if ( "bundle" === $product_type ) {
                                continue;
                            }
                            if ( !empty( $woo_cart_item_for_qty['data']->get_weight() ) ) {
                                $cart_based_weight += $woo_cart_item_for_qty['quantity'] * $woo_cart_item_for_qty['data']->get_weight();
                            }
                            $cart_based_qty += $woo_cart_item_for_qty['quantity'];
                        }
                    }
                    $fee_title = get_the_title( $fees_id );
                    $title = ( !empty( $fee_title ) ? __( $fee_title, 'woocommerce-conditional-product-fees-for-checkout' ) : __( 'Fee', 'woocommerce-conditional-product-fees-for-checkout' ) );
                    $getFeesCostOriginal = get_post_meta( $fees_id, 'fee_settings_product_cost', true );
                    $getFeeType = get_post_meta( $fees_id, 'fee_settings_select_fee_type', true );
                    if ( isset( $woocommerce_wpml ) && !empty( $woocommerce_wpml->multi_currency ) ) {
                        if ( isset( $getFeeType ) && !empty( $getFeeType ) && 'fixed' === $getFeeType ) {
                            $getFeesCost = $woocommerce_wpml->multi_currency->prices->convert_price_amount( $getFeesCostOriginal );
                        } else {
                            $getFeesCost = $getFeesCostOriginal;
                        }
                    } else {
                        if ( 'both' === $getFeeType ) {
                            $getFeesCost = get_post_meta( $fees_id, 'fee_settings_product_cost', true );
                        } else {
                            if ( class_exists( 'WOOMULTI_CURRENCY_Data' ) && 'percentage' !== $getFeeType ) {
                                $getFeesCostOriginal = $this->wcpfc_pro_convert_currency( $getFeesCostOriginal );
                            }
                            $getFeesCost = $getFeesCostOriginal;
                        }
                    }
                    $getFeetaxable = get_post_meta( $fees_id, 'fee_settings_select_taxable', true );
                    $getFeeStartDate = get_post_meta( $fees_id, 'fee_settings_start_date', true );
                    $getFeeEndDate = get_post_meta( $fees_id, 'fee_settings_end_date', true );
                    $getFeeStartTime = get_post_meta( $fees_id, 'ds_time_from', true );
                    $getFeeEndTime = get_post_meta( $fees_id, 'ds_time_to', true );
                    $getFeeStatus = get_post_meta( $fees_id, 'fee_settings_status', true );
                    if ( isset( $getFeeStatus ) && 'off' === $getFeeStatus ) {
                        continue;
                    }
                    $fees_cost = $getFeesCost;
                    $get_condition_array = get_post_meta( $fees_id, 'product_fees_metabox', true );
                    $general_rule_match = 'all';
                    $fees_on_cart_total = get_post_meta( $fees_id, 'fees_on_cart_total', true );
                    if ( 'on' === $fees_on_cart_total ) {
                        $cart_sub_total = $this->wcpfc_cart_total();
                    }
                    if ( isset( $getFeeType ) && !empty( $getFeeType ) && $getFeeType === 'percentage' ) {
                        $fees_cost = $cart_sub_total * $getFeesCost / 100;
                    } else {
                        if ( isset( $getFeeType ) && !empty( $getFeeType ) && $getFeeType === 'both' && strpos( $getFeesCost, '+' ) !== false ) {
                            $newamount = explode( '+', $getFeesCost );
                            if ( is_numeric( $newamount[0] ) && is_numeric( $newamount[1] ) ) {
                                $peramount = $cart_sub_total * $newamount[0] / 100;
                                if ( class_exists( 'WOOMULTI_CURRENCY_Data' ) ) {
                                    $newamount[1] = $this->wcpfc_pro_convert_currency( $newamount[1] );
                                }
                                $fees_cost = $peramount + $newamount[1];
                            }
                        } else {
                            $fees_cost = $getFeesCost;
                        }
                    }
                    if ( isset( $get_condition_array ) && !empty( $get_condition_array ) ) {
                        $country_array = array();
                        $city_array = array();
                        $product_array = array();
                        $tag_array = array();
                        $user_array = array();
                        $cart_total_array = array();
                        $quantity_array = array();
                        $variableproduct_array = array();
                        $category_array = array();
                        $product_qty_array = array();
                        foreach ( $get_condition_array as $key => $value ) {
                            if ( array_search( 'country', $value, true ) ) {
                                $country_array[$key] = $value;
                            }
                            if ( array_search( 'city', $value, true ) ) {
                                $city_array[$key] = $value;
                            }
                            if ( array_search( 'product', $value, true ) ) {
                                $product_array[$key] = $value;
                            }
                            if ( array_search( 'variableproduct', $value, true ) ) {
                                $variableproduct_array[$key] = $value;
                            }
                            if ( array_search( 'category', $value, true ) ) {
                                $category_array[$key] = $value;
                            }
                            if ( array_search( 'tag', $value, true ) ) {
                                $tag_array[$key] = $value;
                            }
                            if ( array_search( 'product_qty', $value, true ) ) {
                                $product_qty_array[$key] = $value;
                            }
                            if ( array_search( 'user', $value, true ) ) {
                                $user_array[$key] = $value;
                            }
                            if ( array_search( 'cart_total', $value, true ) ) {
                                $cart_total_array[$key] = $value;
                            }
                            if ( array_search( 'quantity', $value, true ) ) {
                                $quantity_array[$key] = $value;
                            }
                            //Check if is country exist
                            if ( isset( $country_array ) && !empty( $country_array ) && is_array( $country_array ) && !empty( $cart_array ) ) {
                                $country_passed = $this->wcpfc_pro_match_country_rules( $country_array, $general_rule_match );
                                if ( 'yes' === $country_passed ) {
                                    $is_passed['has_fee_based_on_country'] = 'yes';
                                } else {
                                    $is_passed['has_fee_based_on_country'] = 'no';
                                }
                            }
                            //Check if is city exist
                            if ( isset( $city_array ) && !empty( $city_array ) && is_array( $city_array ) && !empty( $cart_array ) ) {
                                $city_passed = $this->wcpfc_pro_match_city_rules( $city_array, $general_rule_match );
                                if ( 'yes' === $city_passed ) {
                                    $is_passed['has_fee_based_on_city'] = 'yes';
                                } else {
                                    $is_passed['has_fee_based_on_city'] = 'no';
                                }
                            }
                            //Check if is product exist
                            if ( isset( $product_array ) && !empty( $product_array ) && is_array( $product_array ) && !empty( $cart_product_ids_array ) ) {
                                $product_passed = $this->wcpfc_pro_match_simple_products_rule( $cart_product_ids_array, $product_array, $general_rule_match );
                                if ( 'yes' === $product_passed ) {
                                    $is_passed['has_fee_based_on_product'] = 'yes';
                                } else {
                                    $is_passed['has_fee_based_on_product'] = 'no';
                                }
                            }
                            //Check if is variable product exist
                            if ( isset( $variableproduct_array ) && !empty( $variableproduct_array ) && is_array( $variableproduct_array ) && !empty( $cart_product_ids_array ) ) {
                                $variable_prd_passed = $this->wcpfc_pro_match_variable_products_rule( $cart_product_ids_array, $variableproduct_array, $general_rule_match );
                                if ( 'yes' === $variable_prd_passed ) {
                                    $is_passed['has_fee_based_on_variable_prd'] = 'yes';
                                } else {
                                    $is_passed['has_fee_based_on_variable_prd'] = 'no';
                                }
                            }
                            //Check if is Category exist
                            if ( isset( $category_array ) && !empty( $category_array ) && is_array( $category_array ) && !empty( $cart_main_product_ids_array ) ) {
                                $category_passed = $this->wcpfc_pro_match_category_rule( $cart_main_product_ids_array, $category_array, $general_rule_match );
                                if ( 'yes' === $category_passed ) {
                                    $is_passed['has_fee_based_on_category'] = 'yes';
                                } else {
                                    $is_passed['has_fee_based_on_category'] = 'no';
                                }
                            }
                            //Check if is tag exist
                            if ( isset( $tag_array ) && !empty( $tag_array ) && is_array( $tag_array ) && !empty( $cart_main_product_ids_array ) ) {
                                $tag_passed = $this->wcpfc_pro_match_tag_rule( $cart_main_product_ids_array, $tag_array, $general_rule_match );
                                if ( 'yes' === $tag_passed ) {
                                    $is_passed['has_fee_based_on_tag'] = 'yes';
                                } else {
                                    $is_passed['has_fee_based_on_tag'] = 'no';
                                }
                            }
                            //Check if product quantity exist
                            if ( isset( $product_qty_array ) && !empty( $product_qty_array ) && is_array( $product_qty_array ) && !empty( $cart_product_ids_array ) ) {
                                $product_qty_passed = $this->wcpfc_pro_match_product_qty_rule(
                                    $fees_id,
                                    $cart_array,
                                    $product_qty_array,
                                    $general_rule_match,
                                    $sitepress,
                                    $default_lang
                                );
                                if ( 'yes' === $product_qty_passed ) {
                                    $is_passed['has_fee_based_on_product_qty'] = 'yes';
                                } else {
                                    $is_passed['has_fee_based_on_product_qty'] = 'no';
                                }
                            }
                            //Check if is user exist
                            if ( isset( $user_array ) && !empty( $user_array ) && is_array( $user_array ) && !empty( $cart_array ) ) {
                                $user_passed = $this->wcpfc_pro_match_user_rule( $user_array, $general_rule_match );
                                if ( 'yes' === $user_passed ) {
                                    $is_passed['has_fee_based_on_user'] = 'yes';
                                } else {
                                    $is_passed['has_fee_based_on_user'] = 'no';
                                }
                            }
                            //Check if is Cart Subtotal (Before Discount) exist
                            if ( isset( $cart_total_array ) && !empty( $cart_total_array ) && is_array( $cart_total_array ) && !empty( $cart_array ) ) {
                                $cart_total_before_passed = $this->wcpfc_pro_match_cart_subtotal_before_discount_rule( $wc_curr_version, $cart_total_array, $general_rule_match );
                                if ( 'yes' === $cart_total_before_passed ) {
                                    $is_passed['has_fee_based_on_cart_total_before'] = 'yes';
                                } else {
                                    $is_passed['has_fee_based_on_cart_total_before'] = 'no';
                                }
                            }
                            //Check if is quantity exist
                            if ( isset( $quantity_array ) && !empty( $quantity_array ) && is_array( $quantity_array ) && !empty( $cart_array ) ) {
                                $quantity_passed = $this->wcpfc_pro_match_cart_total_cart_qty_rule( $cart_array, $quantity_array, $general_rule_match );
                                if ( 'yes' === $quantity_passed ) {
                                    $is_passed['has_fee_based_on_quantity'] = 'yes';
                                } else {
                                    $is_passed['has_fee_based_on_quantity'] = 'no';
                                }
                            }
                        }
                        if ( isset( $is_passed ) && !empty( $is_passed ) && is_array( $is_passed ) ) {
                            $fnispassed = array();
                            foreach ( $is_passed as $val ) {
                                if ( '' !== $val ) {
                                    $fnispassed[] = $val;
                                }
                            }
                            if ( 'all' === $general_rule_match ) {
                                if ( in_array( 'no', $fnispassed, true ) ) {
                                    $final_is_passed_general_rule['passed'] = 'no';
                                } else {
                                    $final_is_passed_general_rule['passed'] = 'yes';
                                }
                            } else {
                                if ( in_array( 'yes', $fnispassed, true ) ) {
                                    $final_is_passed_general_rule['passed'] = 'yes';
                                } else {
                                    $final_is_passed_general_rule['passed'] = 'no';
                                }
                            }
                        }
                    }
                    if ( empty( $final_is_passed_general_rule ) || '' === $final_is_passed_general_rule || null === $final_is_passed_general_rule ) {
                        $new_is_passed['passed'] = 'no';
                    } else {
                        if ( !empty( $final_is_passed_general_rule ) && in_array( 'no', $final_is_passed_general_rule, true ) ) {
                            $new_is_passed['passed'] = 'no';
                        } else {
                            if ( empty( $final_is_passed_general_rule ) && in_array( '', $final_is_passed_general_rule, true ) ) {
                                $new_is_passed['passed'] = 'no';
                            } else {
                                if ( !empty( $final_is_passed_general_rule ) && in_array( 'yes', $final_is_passed_general_rule, true ) ) {
                                    $new_is_passed['passed'] = 'yes';
                                }
                            }
                        }
                    }
                    if ( in_array( 'no', $new_is_passed, true ) ) {
                        $final_passed['passed'] = 'no';
                    } else {
                        $final_passed['passed'] = 'yes';
                    }
                    if ( isset( $final_passed ) && !empty( $final_passed ) && is_array( $final_passed ) ) {
                        if ( !in_array( 'no', $final_passed, true ) ) {
                            $texable = ( isset( $getFeetaxable ) && !empty( $getFeetaxable ) && 'yes' === $getFeetaxable ? true : false );
                            $currentDate = strtotime( gmdate( 'd-m-Y' ) );
                            $feeStartDate = ( isset( $getFeeStartDate ) && '' !== $getFeeStartDate ? strtotime( $getFeeStartDate ) : '' );
                            $feeEndDate = ( isset( $getFeeEndDate ) && '' !== $getFeeEndDate ? strtotime( $getFeeEndDate ) : '' );
                            /*Check for time*/
                            $local_nowtimestamp = current_time( 'timestamp' );
                            $feeStartTime = ( isset( $getFeeStartTime ) && !empty( $getFeeStartTime ) ? strtotime( $getFeeStartTime ) : '' );
                            $feeEndTime = ( isset( $getFeeEndTime ) && !empty( $getFeeEndTime ) ? strtotime( $getFeeEndTime ) : '' );
                            // Calculate N combination fees on Total Cart Quantity
                            $cart_qty_n_combination = get_post_meta( $fees_id, 'cost_on_total_cart_qty_n_combination', true );
                            if ( !empty( $cart_qty_n_combination ) && 'on' === $cart_qty_n_combination ) {
                                $nc_total_qty = WC()->cart->get_cart_contents_count();
                                $fee_for_single_container = 0;
                                $fee_for_double_containers = 0;
                                if ( isset( $total_cart_qty_n_combination['n_combination'] ) && !empty( $total_cart_qty_n_combination['n_combination'] ) ) {
                                    $fee_for_single_container = $total_cart_qty_n_combination['n_combination'][0]['has_fee_based_on_tcq_price'][0];
                                    $fee_for_double_containers = $total_cart_qty_n_combination['n_combination'][1]['has_fee_based_on_tcq_price'][1];
                                }
                                $nc_total_fee = 0;
                                if ( $nc_total_qty === 1 ) {
                                    $nc_total_fee = $fee_for_single_container;
                                } else {
                                    if ( $nc_total_qty === 2 ) {
                                        $nc_total_fee = $fee_for_double_containers;
                                    } else {
                                        $full_double_container_sets = intdiv( $nc_total_qty, 2 );
                                        // Number of full sets of 2 containers
                                        $remaining_single_containers = $nc_total_qty % 2;
                                        // Remaining single container
                                        $nc_total_fee = $full_double_container_sets * $fee_for_double_containers + $remaining_single_containers * $fee_for_single_container;
                                    }
                                }
                                $fees_cost = $nc_total_fee;
                            }
                            $fees_cost = $this->wcpfc_pro_price_format( $fees_cost );
                            $fee_show_on_checkout_only = ( get_post_meta( $fees_id, 'fee_show_on_checkout_only', true ) ? get_post_meta( $fees_id, 'fee_show_on_checkout_only', true ) : '' );
                            $today = strtolower( gmdate( "D" ) );
                            $ds_select_day_of_week = ( get_post_meta( $fees_id, 'ds_select_day_of_week', true ) ? get_post_meta( $fees_id, 'ds_select_day_of_week', true ) : array() );
                            if ( ($currentDate >= $feeStartDate || '' === $feeStartDate) && ($currentDate <= $feeEndDate || '' === $feeEndDate) && ($local_nowtimestamp >= $feeStartTime || '' === $feeStartTime) && ($local_nowtimestamp <= $feeEndTime || '' === $feeEndTime) && (in_array( $today, $ds_select_day_of_week, true ) || empty( $ds_select_day_of_week )) ) {
                                if ( '' !== $fees_cost ) {
                                    $chk_enable_coupon_fee = get_option( 'chk_enable_coupon_fee' );
                                    if ( 'on' === $chk_enable_coupon_fee ) {
                                        if ( $wc_curr_version >= 3.0 ) {
                                            $cart_coupon = WC()->cart->get_coupons();
                                            $get_cart_subtotal = $woocommerce->cart->get_cart_subtotal();
                                        } else {
                                            $cart_coupon = ( isset( $woocommerce->cart->coupons ) && !empty( $woocommerce->cart->coupons ) ? $woocommerce->cart->coupons : array() );
                                            $get_cart_subtotal = $woocommerce->cart->get_cart_subtotal();
                                        }
                                        if ( !empty( $cart_coupon ) && is_array( $cart_coupon ) ) {
                                            foreach ( $cart_coupon as $coupon ) {
                                                $coupon_type = $coupon->get_discount_type();
                                                $coupon_amount = intval( $coupon->get_amount() );
                                                $cart_subtotal_amount = $this->wcpfc_pro_remove_currency_symbol( $get_cart_subtotal );
                                                if ( 'percent' === $coupon_type && !(100 === $coupon_amount) || 'percent' !== $coupon_type && (floatval( $cart_subtotal_amount ) !== floatval( $coupon->get_amount() ) && floatval( $cart_subtotal_amount ) > floatval( $coupon->get_amount() )) ) {
                                                    $merge_fee_flag = apply_filters( 'merge_fee_flag', true, $fees_id );
                                                    if ( !empty( $chk_enable_custom_fun ) && 'on' === $chk_enable_custom_fun && true === $merge_fee_flag ) {
                                                        if ( 'yes' !== $getFeesOptional || $apply_rule_for_optional ) {
                                                            $total_fee = $total_fee + $fees_cost;
                                                        }
                                                    } else {
                                                        if ( 'yes' !== $getFeesOptional || $apply_rule_for_optional ) {
                                                            if ( is_checkout() || empty( $fee_show_on_checkout_only ) ) {
                                                                $woocommerce->cart->add_fee(
                                                                    $title,
                                                                    $fees_cost,
                                                                    $texable,
                                                                    apply_filters( 'wcpfc_tax_class', $final_item_tax_class, $fees )
                                                                );
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            /** @var add the total fee value $total_fee */
                                            if ( !empty( $chk_enable_custom_fun ) && 'on' === $chk_enable_custom_fun ) {
                                                $merge_fee_flag = apply_filters( 'merge_fee_flag', true, $fees_id );
                                                if ( true === $merge_fee_flag ) {
                                                    if ( 'yes' !== $getFeesOptional || $apply_rule_for_optional ) {
                                                        $total_fee = $total_fee + $fees_cost;
                                                    }
                                                } else {
                                                    if ( 'yes' !== $getFeesOptional || $apply_rule_for_optional ) {
                                                        if ( is_checkout() || empty( $fee_show_on_checkout_only ) ) {
                                                            $woocommerce->cart->add_fee(
                                                                $title,
                                                                $fees_cost,
                                                                $texable,
                                                                apply_filters( 'wcpfc_tax_class', $final_item_tax_class, $fees )
                                                            );
                                                        }
                                                    }
                                                }
                                            } else {
                                                if ( 'yes' !== $getFeesOptional || $apply_rule_for_optional ) {
                                                    if ( is_checkout() || empty( $fee_show_on_checkout_only ) ) {
                                                        $woocommerce->cart->add_fee(
                                                            $title,
                                                            $fees_cost,
                                                            $texable,
                                                            apply_filters( 'wcpfc_tax_class', $final_item_tax_class, $fees )
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        /** @var add the total fee value $total_fee */
                                        if ( !empty( $chk_enable_custom_fun ) && 'on' === $chk_enable_custom_fun ) {
                                            $merge_fee_flag = apply_filters( 'merge_fee_flag', true, $fees_id );
                                            if ( true === $merge_fee_flag ) {
                                                if ( 'yes' !== $getFeesOptional || $apply_rule_for_optional ) {
                                                    $total_fee = $total_fee + $fees_cost;
                                                }
                                            } else {
                                                if ( 'yes' !== $getFeesOptional || $apply_rule_for_optional ) {
                                                    if ( is_checkout() || empty( $fee_show_on_checkout_only ) ) {
                                                        $woocommerce->cart->add_fee(
                                                            $title,
                                                            $fees_cost,
                                                            $texable,
                                                            apply_filters( 'wcpfc_tax_class', $final_item_tax_class, $fees )
                                                        );
                                                    }
                                                }
                                            }
                                        } else {
                                            if ( 'yes' !== $getFeesOptional || $apply_rule_for_optional ) {
                                                if ( is_checkout() || empty( $fee_show_on_checkout_only ) ) {
                                                    $woocommerce->cart->add_fee(
                                                        $title,
                                                        $fees_cost,
                                                        $texable,
                                                        apply_filters( 'wcpfc_tax_class', $final_item_tax_class, $fees )
                                                    );
                                                } else {
                                                    if ( !is_cart() && !empty( $fee_show_on_checkout_only ) ) {
                                                        $woocommerce->cart->add_fee(
                                                            $title,
                                                            $fees_cost,
                                                            $texable,
                                                            apply_filters( 'wcpfc_tax_class', $final_item_tax_class, $fees )
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $display_optional_fee_on_checkout = 'on';
                        } else {
                            $display_optional_fee_on_checkout = 'off';
                        }
                        update_post_meta( $fees_id, '_wcpfc_display_optional_fee_on_checkout', $display_optional_fee_on_checkout );
                    }
                }
            }
            /**
             * Add one time fee with total applied fees count
             */
            if ( !empty( $chk_enable_custom_fun ) && 'on' === $chk_enable_custom_fun ) {
                if ( isset( $total_fee ) && 0 < $total_fee ) {
                    $chk_enable_all_fee_tax = ( 'on' === get_option( 'chk_enable_all_fee_tax' ) && !empty( get_option( 'chk_enable_all_fee_tax' ) ) ? true : false );
                    $fee_title = apply_filters( 'wcpfc_all_fee_title', 'Fees' );
                    // Fetch the tax class type for the merged fee
                    $taxClassType = get_option( 'merge_fee_settings_taxable_type' );
                    $woocommerce->cart->add_fee(
                        wp_kses_post( $fee_title, 'woocommerce-conditional-product-fees-for-checkout' ),
                        $total_fee,
                        $chk_enable_all_fee_tax,
                        apply_filters( 'wcpfc_tax_class', $final_item_tax_class, -1 )
                    );
                    //-1 for combined fees id
                }
            }
        }
    }

    /**
     * Store fees revenue data for tracking and anylysis
     *
     * @return boolean true
     * @since 3.7.0
     *
     */
    public function wcpfc_add_fee_details_with_order_for_track( $order ) {
        if ( !empty( $order->get_fees() ) ) {
            $extra_fee_arr = array();
            foreach ( $order->get_fees() as $fee_detail ) {
                $fees_id = ( !empty( $fee_detail->legacy_fee->id ) ? $fee_detail->legacy_fee->id : 0 );
                $fee_amount = 0;
                if ( $fees_id > 0 ) {
                    $fee_revenue = ( get_post_meta( $fees_id, '_wcpfc_fee_revenue', true ) ? get_post_meta( $fees_id, '_wcpfc_fee_revenue', true ) : 0 );
                    $fee_amount = ( !empty( $fee_detail->legacy_fee->total ) ? $fee_detail->legacy_fee->total : 0 );
                    if ( !empty( $fee_detail->legacy_fee->taxable ) && $fee_detail->legacy_fee->taxable ) {
                        $fee_amount += ( $fee_detail->legacy_fee->tax > 0 ? $fee_detail->legacy_fee->tax : 0 );
                    }
                    $fee_revenue += $fee_amount;
                    if ( $fee_revenue > 0 ) {
                        update_post_meta( $fees_id, '_wcpfc_fee_revenue', $fee_revenue );
                    }
                    array_push( $extra_fee_arr, $fee_detail->legacy_fee );
                }
            }
            if ( !empty( $extra_fee_arr ) ) {
                $order->update_meta_data( '_wcpfc_fee_summary', $extra_fee_arr );
            }
        }
    }

    // Function to get all variation IDs for a product
    public function wcpfc_get_all_variation_ids( $product_id ) {
        // Ensure the product exists and is a variable product
        $product = wc_get_product( $product_id );
        if ( $product && $product->is_type( 'variable' ) ) {
            // Get all variations of the product
            $variation_ids = $product->get_children();
            return $variation_ids;
        }
        return array();
        // Return empty array if product is not variable
    }

    public function wcpfc_pro_get_woo_version_number() {
        // If get_plugins() isn't available, require it
        if ( !function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        // Create the plugins folder and file variables
        $plugin_folder = get_plugins( '/' . 'woocommerce' );
        $plugin_file = 'woocommerce.php';
        // If the plugin version number is set, return it
        if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
            return $plugin_folder[$plugin_file]['Version'];
        } else {
            return null;
        }
    }

    /**
     * Get product id and variation id from cart
     *
     * @return array $cart_array
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_get_cart() {
        // Ensure WooCommerce is initialized and the cart object is available
        if ( !function_exists( 'WC' ) || !WC()->cart ) {
            return array();
            // Return an empty array or handle the error as needed
        }
        $cart_array = WC()->cart->get_cart();
        return $cart_array;
    }

    /**
     * Get product id and variation id from cart
     *
     * @param string $sitepress
     * @param string $default_lang
     *
     * @return array $cart_main_product_ids_array
     * @uses  wcpfc_pro_get_cart();
     *
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_get_main_prd_id( $sitepress, $default_lang ) {
        $cart_array = $this->wcpfc_pro_get_cart();
        $cart_main_product_ids_array = array();
        if ( isset( $cart_array ) && !empty( $cart_array ) ) {
            foreach ( $cart_array as $woo_cart_item ) {
                $product_id = ( $woo_cart_item['product_id'] ? $woo_cart_item['product_id'] : 0 );
                settype( $product_id, 'integer' );
                if ( !empty( $sitepress ) ) {
                    $cart_main_product_ids_array[] = apply_filters(
                        'wpml_object_id',
                        $product_id,
                        'product',
                        true,
                        $default_lang
                    );
                } else {
                    $cart_main_product_ids_array[] = $product_id;
                }
            }
        }
        return $cart_main_product_ids_array;
    }

    /**
     * Get product id and variation id from cart
     *
     * @param string $sitepress
     * @param string $default_lang
     *
     * @return array $cart_product_ids_array
     * @uses  wcpfc_pro_get_cart();
     *
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_get_prd_var_id( $sitepress, $default_lang ) {
        $cart_array = $this->wcpfc_pro_get_cart();
        $cart_product_ids_array = array();
        if ( isset( $cart_array ) && !empty( $cart_array ) ) {
            foreach ( $cart_array as $woo_cart_item ) {
                $product_id = ( isset( $woo_cart_item['variation_id'] ) && !empty( $woo_cart_item['variation_id'] ) && $woo_cart_item['variation_id'] > 0 ? $woo_cart_item['variation_id'] : $woo_cart_item['product_id'] );
                settype( $product_id, 'integer' );
                if ( !empty( $sitepress ) ) {
                    $cart_product_ids_array[] = apply_filters(
                        'wpml_object_id',
                        $product_id,
                        'product',
                        true,
                        $default_lang
                    );
                } else {
                    $cart_product_ids_array[] = $product_id;
                }
            }
        }
        return $cart_product_ids_array;
    }

    /**
     * Count qty for product based and cart based when apply per qty option is on. This rule will apply when advance pricing rule will disable
     *
     * @param int    $fees_id
     * @param array  $cart_array
     * @param int    $products_based_qty
     * @param float  $products_based_subtotal
     * @param string $sitepress
     * @param string $default_lang
     *
     * @return array $products_based_qty, $products_based_subtotal
     * @since 1.3.3
     *
     * @uses  get_post_meta()
     * @uses  get_post()
     * @uses  get_terms()
     *
     */
    public function wcpfc_pro_fees_per_qty_on_ap_rules_off(
        $fees_id,
        $cart_array,
        $products_based_qty,
        $products_based_subtotal,
        $sitepress,
        $default_lang
    ) {
        $productFeesArray = get_post_meta( $fees_id, 'product_fees_metabox', true );
        $all_rule_check = array();
        if ( !empty( $productFeesArray ) ) {
            foreach ( $productFeesArray as $condition ) {
                if ( !empty( $condition['product_fees_conditions_values'] ) && is_array( $condition['product_fees_conditions_values'] ) ) {
                    $condition['product_fees_conditions_values'] = array_map( 'intval', $condition['product_fees_conditions_values'] );
                } else {
                    $condition['product_fees_conditions_values'] = '';
                }
                if ( array_search( 'product', $condition, true ) ) {
                    $site_product_id = '';
                    $cart_final_products_array = array();
                    // Product Condition Start
                    if ( 'is_equal_to' === $condition['product_fees_conditions_is'] ) {
                        if ( !empty( $condition['product_fees_conditions_values'] ) ) {
                            foreach ( $cart_array as $value ) {
                                if ( !empty( $value['variation_id'] ) && 0 !== $value['variation_id'] ) {
                                    $product_id_lan = $value['variation_id'];
                                } else {
                                    $product_id_lan = $value['product_id'];
                                }
                                $_product = wc_get_product( $product_id_lan );
                                $line_item_subtotal = (float) $value['line_subtotal'] + (float) $value['line_subtotal_tax'];
                                if ( !empty( $sitepress ) ) {
                                    $site_product_id = apply_filters(
                                        'wpml_object_id',
                                        $product_id_lan,
                                        'product',
                                        true,
                                        $default_lang
                                    );
                                } else {
                                    $site_product_id = $product_id_lan;
                                }
                                if ( false === strpos( $_product->get_type(), 'bundle' ) ) {
                                    if ( in_array( $site_product_id, $condition['product_fees_conditions_values'], true ) ) {
                                        $prod_qty = ( $value['quantity'] ? $value['quantity'] : 0 );
                                        if ( array_key_exists( $site_product_id, $cart_final_products_array ) ) {
                                            $product_data_explode = explode( "||", $cart_final_products_array[$site_product_id] );
                                            $cart_product_qty = json_decode( $product_data_explode[0] );
                                            $prod_qty += $cart_product_qty;
                                        }
                                        $cart_final_products_array[$site_product_id] = $prod_qty . "||" . $line_item_subtotal;
                                    }
                                } else {
                                    if ( false !== strpos( $_product->get_type(), 'bundle' ) ) {
                                        $prod_qty = 0;
                                        $cart_final_products_array[$site_product_id] = $prod_qty . "||" . $line_item_subtotal;
                                    }
                                }
                            }
                        }
                    } elseif ( 'not_in' === $condition['product_fees_conditions_is'] ) {
                        if ( !empty( $condition['product_fees_conditions_values'] ) ) {
                            foreach ( $cart_array as $value ) {
                                if ( !empty( $value['variation_id'] ) && 0 !== $value['variation_id'] ) {
                                    $product_id_lan = $value['variation_id'];
                                } else {
                                    $product_id_lan = $value['product_id'];
                                }
                                $_product = wc_get_product( $product_id_lan );
                                $line_item_subtotal = (float) $value['line_subtotal'] + (float) $value['line_subtotal_tax'];
                                if ( !empty( $sitepress ) ) {
                                    $site_product_id = apply_filters(
                                        'wpml_object_id',
                                        $product_id_lan,
                                        'product',
                                        true,
                                        $default_lang
                                    );
                                } else {
                                    $site_product_id = $product_id_lan;
                                }
                                if ( false === strpos( $_product->get_type(), 'bundle' ) ) {
                                    if ( !in_array( $site_product_id, $condition['product_fees_conditions_values'], true ) ) {
                                        $prod_qty = ( $value['quantity'] ? $value['quantity'] : 0 );
                                        if ( array_key_exists( $site_product_id, $cart_final_products_array ) ) {
                                            $product_data_explode = explode( "||", $cart_final_products_array[$site_product_id] );
                                            $cart_product_qty = json_decode( $product_data_explode[0] );
                                            $prod_qty += $cart_product_qty;
                                        }
                                        $cart_final_products_array[$product_id_lan] = $prod_qty . "||" . $line_item_subtotal;
                                    }
                                } else {
                                    if ( false !== strpos( $_product->get_type(), 'bundle' ) ) {
                                        $prod_qty = 0;
                                        $cart_final_products_array[$site_product_id] = $prod_qty . "||" . $line_item_subtotal;
                                    }
                                }
                            }
                        }
                    }
                    if ( isset( $cart_final_products_array ) && !empty( $cart_final_products_array ) ) {
                        foreach ( $cart_final_products_array as $prd_id => $cart_item ) {
                            $cart_item_explode = explode( "||", $cart_item );
                            $all_rule_check[$prd_id]['qty'] = $cart_item_explode[0];
                            $all_rule_check[$prd_id]['subtotal'] = $cart_item_explode[1];
                        }
                    }
                    // Product Condition End
                }
                if ( array_search( 'variableproduct', $condition, true ) ) {
                    $site_product_id = '';
                    $cart_final_var_products_array = array();
                    // Variable Product Condition Start
                    if ( 'is_equal_to' === $condition['product_fees_conditions_is'] ) {
                        if ( !empty( $condition['product_fees_conditions_values'] ) ) {
                            foreach ( $cart_array as $value ) {
                                if ( !empty( $value['variation_id'] ) && 0 !== $value['variation_id'] ) {
                                    $product_id_lan = $value['variation_id'];
                                } else {
                                    $product_id_lan = $value['product_id'];
                                }
                                $_product = wc_get_product( $product_id_lan );
                                $line_item_subtotal = (float) $value['line_subtotal'] + (float) $value['line_subtotal_tax'];
                                if ( !empty( $sitepress ) ) {
                                    $site_product_id = apply_filters(
                                        'wpml_object_id',
                                        $product_id_lan,
                                        'product',
                                        true,
                                        $default_lang
                                    );
                                } else {
                                    $site_product_id = $product_id_lan;
                                }
                                if ( false === strpos( $_product->get_type(), 'bundle' ) ) {
                                    if ( in_array( $site_product_id, $condition['product_fees_conditions_values'], true ) ) {
                                        $prod_qty = ( $value['quantity'] ? $value['quantity'] : 0 );
                                        $cart_final_var_products_array[] = $prod_qty . "||" . $line_item_subtotal;
                                    }
                                } else {
                                    if ( false !== strpos( $_product->get_type(), 'bundle' ) ) {
                                        $prod_qty = 0;
                                        $cart_final_var_products_array[] = $prod_qty . "||" . $line_item_subtotal;
                                    }
                                }
                            }
                        }
                    } elseif ( 'not_in' === $condition['product_fees_conditions_is'] ) {
                        if ( !empty( $condition['product_fees_conditions_values'] ) ) {
                            foreach ( $cart_array as $value ) {
                                if ( !empty( $value['variation_id'] ) && 0 !== $value['variation_id'] ) {
                                    $product_id_lan = $value['variation_id'];
                                } else {
                                    $product_id_lan = $value['product_id'];
                                }
                                $_product = wc_get_product( $product_id_lan );
                                $line_item_subtotal = (float) $value['line_subtotal'] + (float) $value['line_subtotal_tax'];
                                if ( !empty( $sitepress ) ) {
                                    $site_product_id = apply_filters(
                                        'wpml_object_id',
                                        $product_id_lan,
                                        'product',
                                        true,
                                        $default_lang
                                    );
                                } else {
                                    $site_product_id = $product_id_lan;
                                }
                                if ( false === strpos( $_product->get_type(), 'bundle' ) ) {
                                    if ( !in_array( $site_product_id, $condition['product_fees_conditions_values'], true ) ) {
                                        $prod_qty = ( $value['quantity'] ? $value['quantity'] : 0 );
                                        $cart_final_var_products_array[] = $prod_qty . "||" . $line_item_subtotal;
                                    }
                                } else {
                                    if ( false !== strpos( $_product->get_type(), 'bundle' ) ) {
                                        $prod_qty = 0;
                                        $cart_final_var_products_array[] = $prod_qty . "||" . $line_item_subtotal;
                                    }
                                }
                            }
                        }
                    }
                    if ( isset( $cart_final_var_products_array ) && !empty( $cart_final_var_products_array ) ) {
                        foreach ( $cart_final_var_products_array as $prd_id => $cart_item ) {
                            $cart_item_explode = explode( "||", $cart_item );
                            $all_rule_check[$prd_id]['qty'] = $cart_item_explode[0];
                            $all_rule_check[$prd_id]['subtotal'] = $cart_item_explode[1];
                        }
                    }
                    // Variable Product Condition End
                }
                if ( array_search( 'category', $condition, true ) ) {
                    $final_cart_products_cats_ids = array();
                    $cart_final_cat_products_array = array();
                    $all_cats = get_terms( array(
                        'taxonomy' => 'product_cat',
                        'fields'   => 'ids',
                    ) );
                    if ( 'is_equal_to' === $condition['product_fees_conditions_is'] ) {
                        if ( !empty( $condition['product_fees_conditions_values'] ) ) {
                            foreach ( $condition['product_fees_conditions_values'] as $category_id ) {
                                settype( $category_id, 'integer' );
                                $final_cart_products_cats_ids[] = $category_id;
                            }
                        }
                    } elseif ( 'not_in' === $condition['product_fees_conditions_is'] ) {
                        if ( !empty( $condition['product_fees_conditions_values'] ) ) {
                            $final_cart_products_cats_ids = array_diff( $all_cats, $condition['product_fees_conditions_values'] );
                        }
                    }
                    $final_cart_products_cats_ids = array_map( 'intval', $final_cart_products_cats_ids );
                    $terms = array();
                    $cart_value_array = array();
                    foreach ( $cart_array as $value ) {
                        if ( !empty( $value['variation_id'] ) && 0 !== $value['variation_id'] ) {
                            $product_id = $value['variation_id'];
                        } else {
                            $product_id = $value['product_id'];
                        }
                        $_product = wc_get_product( $product_id );
                        $line_item_subtotal = (float) $value['line_subtotal'] + (float) $value['line_subtotal_tax'];
                        $cart_value_array[] = $value;
                        $term_ids = wp_get_post_terms( $value['product_id'], 'product_cat', array(
                            'fields' => 'ids',
                        ) );
                        if ( !empty( $term_ids ) ) {
                            foreach ( $term_ids as $term_id ) {
                                $prod_qty = ( $value['quantity'] ? $value['quantity'] : 0 );
                                if ( false !== strpos( $_product->get_type(), 'bundle' ) ) {
                                    $prod_qty = 0;
                                }
                                $product_id = ( $value['variation_id'] ? $value['variation_id'] : $product_id );
                                if ( 'is_equal_to' === $condition['product_fees_conditions_is'] ) {
                                    if ( in_array( $term_id, $final_cart_products_cats_ids, true ) ) {
                                        if ( array_key_exists( $product_id, $terms ) && array_key_exists( $term_id, $terms[$product_id] ) ) {
                                            $term_data_explode = explode( "||", $terms[$product_id][$term_id] );
                                            $cart_term_qty = json_decode( $term_data_explode[0] );
                                            $prod_qty += $cart_term_qty;
                                        }
                                        $terms[$product_id][$term_id] = $prod_qty . "||" . $line_item_subtotal;
                                    }
                                }
                            }
                        }
                    }
                    if ( isset( $terms ) && !empty( $terms ) ) {
                        foreach ( $terms as $cart_product_key => $main_term_data ) {
                            foreach ( $main_term_data as $cart_term_id => $term_data ) {
                                $term_data_explode = explode( "||", $term_data );
                                $cart_term_qty = json_decode( $term_data_explode[0] );
                                $cart_term_subtotal = json_decode( $term_data_explode[1] );
                                if ( in_array( $cart_term_id, $final_cart_products_cats_ids, true ) ) {
                                    $cart_final_cat_products_array[$cart_product_key][$cart_term_id] = $cart_term_qty . "||" . $cart_term_subtotal;
                                }
                            }
                        }
                    }
                    if ( isset( $cart_final_cat_products_array ) && !empty( $cart_final_cat_products_array ) ) {
                        foreach ( $cart_final_cat_products_array as $prd_id => $main_cart_item ) {
                            foreach ( $main_cart_item as $term_id => $cart_item ) {
                                $cart_item_explode = explode( "||", $cart_item );
                                $all_rule_check[$prd_id]['qty'] = $cart_item_explode[0];
                                $all_rule_check[$prd_id]['subtotal'] = $cart_item_explode[1];
                            }
                        }
                    }
                }
                if ( array_search( 'tag', $condition, true ) ) {
                    // Tag Condition Start
                    $final_cart_products_tag_ids = array();
                    $cart_final_tag_products_array = array();
                    $all_tags = get_terms( array(
                        'taxonomy' => 'product_tag',
                        'fields'   => 'ids',
                    ) );
                    if ( 'is_equal_to' === $condition['product_fees_conditions_is'] ) {
                        if ( !empty( $condition['product_fees_conditions_values'] ) ) {
                            foreach ( $condition['product_fees_conditions_values'] as $tag_id ) {
                                $final_cart_products_tag_ids[] = $tag_id;
                            }
                        }
                    } elseif ( 'not_in' === $condition['product_fees_conditions_is'] ) {
                        if ( !empty( $condition['product_fees_conditions_values'] ) ) {
                            $final_cart_products_tag_ids = array_diff( $all_tags, $condition['product_fees_conditions_values'] );
                        }
                    }
                    $final_cart_products_tag_ids = array_map( 'intval', $final_cart_products_tag_ids );
                    $tags = array();
                    $cart_value_array = array();
                    foreach ( $cart_array as $value ) {
                        if ( !empty( $value['variation_id'] ) && 0 !== $value['variation_id'] ) {
                            $product_id = $value['variation_id'];
                        } else {
                            $product_id = $value['product_id'];
                        }
                        $_product = wc_get_product( $product_id );
                        $line_item_subtotal = (float) $value['line_subtotal'] + (float) $value['line_subtotal_tax'];
                        $cart_value_array[] = $value;
                        $tag_ids = wp_get_post_terms( $value['product_id'], 'product_tag', array(
                            'fields' => 'ids',
                        ) );
                        foreach ( $tag_ids as $tag_id ) {
                            $prod_qty = ( $value['quantity'] ? $value['quantity'] : 0 );
                            if ( false !== strpos( $_product->get_type(), 'bundle' ) ) {
                                $prod_qty = 0;
                            }
                            $product_id = ( $value['variation_id'] ? $value['variation_id'] : $product_id );
                            if ( in_array( $tag_id, $final_cart_products_tag_ids, true ) ) {
                                if ( array_key_exists( $product_id, $tags ) && array_key_exists( $tag_id, $tags[$product_id] ) ) {
                                    $term_data_explode = explode( "||", $tags[$product_id][$tag_id] );
                                    $cart_term_qty = json_decode( $term_data_explode[0] );
                                    $prod_qty += $cart_term_qty;
                                }
                                $tags[$product_id][$tag_id] = $prod_qty . "||" . $line_item_subtotal;
                            }
                        }
                    }
                    if ( isset( $tags ) && !empty( $tags ) ) {
                        foreach ( $tags as $cart_product_key => $main_tag_data ) {
                            foreach ( $main_tag_data as $cart_tag_id => $tag_data ) {
                                $tag_data_explode = explode( "||", $tag_data );
                                $cart_tag_qty = json_decode( $tag_data_explode[0] );
                                $cart_tag_subtotal = json_decode( $tag_data_explode[1] );
                                if ( !empty( $final_cart_products_tag_ids ) ) {
                                    if ( in_array( $cart_tag_id, $final_cart_products_tag_ids, true ) ) {
                                        $cart_final_tag_products_array[$cart_product_key][$cart_tag_id] = $cart_tag_qty . "||" . $cart_tag_subtotal;
                                    }
                                }
                            }
                        }
                    }
                    if ( !empty( $cart_final_tag_products_array ) ) {
                        foreach ( $cart_final_tag_products_array as $prd_id => $main_cart_item ) {
                            foreach ( $main_cart_item as $term_id => $cart_item ) {
                                $cart_item_explode = explode( "||", $cart_item );
                                $all_rule_check[$prd_id]['qty'] = $cart_item_explode[0];
                                $all_rule_check[$prd_id]['subtotal'] = $cart_item_explode[1];
                            }
                        }
                    }
                }
            }
        }
        if ( isset( $all_rule_check ) && !empty( $all_rule_check ) ) {
            foreach ( $all_rule_check as $cart_item ) {
                $products_based_qty += ( isset( $cart_item['qty'] ) ? $cart_item['qty'] : 0 );
                $products_based_subtotal += ( isset( $cart_item['subtotal'] ) ? $cart_item['subtotal'] : 0 );
            }
        }
        if ( 0 === $products_based_qty ) {
            $products_based_qty = 1;
        }
        return array($products_based_qty, $products_based_subtotal);
    }

    /**
     * Match country rules
     *
     * @param array  $country_array
     * @param string $general_rule_match
     *
     * @return string $main_is_passed
     *
     * @since    1.3.3
     *
     * @uses     WC_Customer::get_shipping_country()
     *
     */
    public function wcpfc_pro_match_country_rules( $country_array, $general_rule_match ) {
        if ( !WC()->customer ) {
            return '';
            // Return an empty string or handle the error as needed
        }
        $selected_country = WC()->customer->get_shipping_country();
        $is_passed = array();
        foreach ( $country_array as $key => $country ) {
            if ( 'is_equal_to' === $country['product_fees_conditions_is'] ) {
                if ( !empty( $country['product_fees_conditions_values'] ) ) {
                    if ( in_array( $selected_country, $country['product_fees_conditions_values'], true ) ) {
                        $is_passed[$key]['has_fee_based_on_country'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_country'] = 'no';
                    }
                }
                if ( empty( $country['product_fees_conditions_values'] ) ) {
                    $is_passed[$key]['has_fee_based_on_country'] = 'yes';
                }
            }
            if ( 'not_in' === $country['product_fees_conditions_is'] ) {
                if ( !empty( $country['product_fees_conditions_values'] ) ) {
                    if ( in_array( $selected_country, $country['product_fees_conditions_values'], true ) || in_array( 'all', $country['product_fees_conditions_values'], true ) ) {
                        $is_passed[$key]['has_fee_based_on_country'] = 'no';
                    } else {
                        $is_passed[$key]['has_fee_based_on_country'] = 'yes';
                    }
                }
            }
        }
        $main_is_passed = $this->wcpfc_pro_check_all_passed_general_rule( $is_passed, 'has_fee_based_on_country', $general_rule_match );
        return $main_is_passed;
    }

    /**
     * Match city rules
     *
     * @param array  $city_array
     * @param string $general_rule_match
     *
     * @return string $main_is_passed
     *
     * @since    1.3.3
     *
     * @uses     WC_Customer::get_shipping_city()
     *
     */
    public function wcpfc_pro_match_city_rules( $city_array, $general_rule_match ) {
        $selected_city = WC()->customer->get_shipping_city();
        $is_passed = array();
        foreach ( $city_array as $key => $city ) {
            if ( !empty( $city['product_fees_conditions_values'] ) ) {
                $citystr = str_replace( PHP_EOL, "<br/>", $city['product_fees_conditions_values'] );
                $city_val_array = explode( '<br/>', $citystr );
                $city_val_array = array_map( 'trim', $city_val_array );
                if ( 'is_equal_to' === $city['product_fees_conditions_is'] ) {
                    if ( in_array( $selected_city, $city_val_array, true ) ) {
                        $is_passed[$key]['has_fee_based_on_city'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_city'] = 'no';
                    }
                }
                if ( 'not_in' === $city['product_fees_conditions_is'] ) {
                    if ( in_array( $selected_city, $city_val_array, true ) ) {
                        $is_passed[$key]['has_fee_based_on_city'] = 'no';
                    } else {
                        $is_passed[$key]['has_fee_based_on_city'] = 'yes';
                    }
                }
            }
        }
        $main_is_passed = $this->wcpfc_pro_check_all_passed_general_rule( $is_passed, 'has_fee_based_on_city', $general_rule_match );
        return $main_is_passed;
    }

    /**
     * Find unique id based on given array
     *
     * @param array  $is_passed
     * @param string $has_fee_based
     * @param string $general_rule_match
     *
     * @return string $main_is_passed
     * @since    3.6
     *
     */
    public function wcpfc_pro_check_all_passed_general_rule( $is_passed, $has_fee_based, $general_rule_match ) {
        $main_is_passed = 'no';
        $flag = array();
        if ( !empty( $is_passed ) ) {
            foreach ( $is_passed as $key => $is_passed_value ) {
                if ( 'yes' === $is_passed_value[$has_fee_based] ) {
                    $flag[$key] = true;
                } else {
                    $flag[$key] = false;
                }
            }
            if ( 'any' === $general_rule_match ) {
                if ( in_array( true, $flag, true ) ) {
                    $main_is_passed = 'yes';
                } else {
                    $main_is_passed = 'no';
                }
            } else {
                if ( in_array( false, $flag, true ) ) {
                    $main_is_passed = 'no';
                } else {
                    $main_is_passed = 'yes';
                }
            }
        }
        return $main_is_passed;
    }

    /**
     * Match simple products rules
     *
     * @param array  $cart_product_ids_array
     * @param array  $product_array
     * @param string $general_rule_match
     *
     * @return string $main_is_passed
     *
     * @since    1.3.3
     *
     */
    public function wcpfc_pro_match_simple_products_rule( $cart_product_ids_array, $product_array, $general_rule_match ) {
        $is_passed = array();
        foreach ( $product_array as $key => $product ) {
            if ( 'is_equal_to' === $product['product_fees_conditions_is'] ) {
                if ( !empty( $product['product_fees_conditions_values'] ) ) {
                    foreach ( $product['product_fees_conditions_values'] as $product_id ) {
                        settype( $product_id, 'integer' );
                        if ( in_array( $product_id, $cart_product_ids_array, true ) ) {
                            $is_passed[$key]['has_fee_based_on_product'] = 'yes';
                            break;
                        } else {
                            $is_passed[$key]['has_fee_based_on_product'] = 'no';
                        }
                    }
                }
            }
            if ( 'not_in' === $product['product_fees_conditions_is'] ) {
                if ( !empty( $product['product_fees_conditions_values'] ) ) {
                    foreach ( $product['product_fees_conditions_values'] as $product_id ) {
                        settype( $product_id, 'integer' );
                        if ( in_array( $product_id, $cart_product_ids_array, true ) ) {
                            $is_passed[$key]['has_fee_based_on_product'] = 'no';
                            break;
                        } else {
                            $is_passed[$key]['has_fee_based_on_product'] = 'yes';
                        }
                    }
                }
            }
        }
        $main_is_passed = $this->wcpfc_pro_check_all_passed_general_rule( $is_passed, 'has_fee_based_on_product', $general_rule_match );
        return $main_is_passed;
    }

    /**
     * Match tag rules
     *
     * @param array  $cart_product_ids_array
     * @param array  $tag_array
     * @param string $general_rule_match
     *
     * @return string $main_is_passed
     *
     * @uses     wp_get_post_terms()
     * @uses     wcpfc_pro_array_flatten()
     *
     * @since    1.3.3
     *
     */
    public function wcpfc_pro_match_tag_rule( $cart_product_ids_array, $tag_array, $general_rule_match ) {
        $tagid = array();
        $is_passed = array();
        foreach ( $cart_product_ids_array as $product ) {
            $cart_product_tag = wp_get_post_terms( $product, 'product_tag', array(
                'fields' => 'ids',
            ) );
            if ( isset( $cart_product_tag ) && !empty( $cart_product_tag ) && is_array( $cart_product_tag ) ) {
                $tagid[] = $cart_product_tag;
            }
        }
        $get_tag_all = array_unique( $this->wcpfc_pro_array_flatten( $tagid ) );
        foreach ( $tag_array as $key => $tag ) {
            if ( 'is_equal_to' === $tag['product_fees_conditions_is'] ) {
                if ( !empty( $tag['product_fees_conditions_values'] ) ) {
                    foreach ( $tag['product_fees_conditions_values'] as $tag_id ) {
                        settype( $tag_id, 'integer' );
                        if ( in_array( $tag_id, $get_tag_all, true ) ) {
                            $is_passed[$key]['has_fee_based_on_tag'] = 'yes';
                            break;
                        } else {
                            $is_passed[$key]['has_fee_based_on_tag'] = 'no';
                        }
                    }
                }
            }
            if ( 'not_in' === $tag['product_fees_conditions_is'] ) {
                if ( !empty( $tag['product_fees_conditions_values'] ) ) {
                    foreach ( $tag['product_fees_conditions_values'] as $tag_id ) {
                        settype( $tag_id, 'integer' );
                        if ( in_array( $tag_id, $get_tag_all, true ) ) {
                            $is_passed[$key]['has_fee_based_on_tag'] = 'no';
                            break;
                        } else {
                            $is_passed[$key]['has_fee_based_on_tag'] = 'yes';
                        }
                    }
                }
            }
        }
        $main_is_passed = $this->wcpfc_pro_check_all_passed_general_rule( $is_passed, 'has_fee_based_on_tag', $general_rule_match );
        return $main_is_passed;
    }

    /**
     * Find unique id based on given array
     *
     * @param array $array
     *
     * @return array $result if $array is empty it will return false otherwise return array as $result
     * @since    1.0.0
     *
     */
    public function wcpfc_pro_array_flatten( $array ) {
        if ( !is_array( $array ) ) {
            return false;
        }
        $result = array();
        foreach ( $array as $key => $value ) {
            if ( is_array( $value ) ) {
                $result = array_merge( $result, $this->wcpfc_pro_array_flatten( $value ) );
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Match user rules
     *
     * @param array  $user_array
     * @param string $general_rule_match
     *
     * @return string $main_is_passed
     *
     * @uses     get_current_user_id()
     *
     * @since    1.3.3
     *
     * @uses     is_user_logged_in()
     */
    public function wcpfc_pro_match_user_rule( $user_array, $general_rule_match ) {
        $current_user_id = get_current_user_id();
        $is_passed = array();
        foreach ( $user_array as $key => $user ) {
            $user['product_fees_conditions_values'] = array_map( 'intval', $user['product_fees_conditions_values'] );
            if ( 'is_equal_to' === $user['product_fees_conditions_is'] ) {
                if ( in_array( $current_user_id, $user['product_fees_conditions_values'], true ) ) {
                    $is_passed[$key]['has_fee_based_on_user'] = 'yes';
                } else {
                    $is_passed[$key]['has_fee_based_on_user'] = 'no';
                }
            }
            if ( 'not_in' === $user['product_fees_conditions_is'] ) {
                if ( in_array( $current_user_id, $user['product_fees_conditions_values'], true ) ) {
                    $is_passed[$key]['has_fee_based_on_user'] = 'no';
                } else {
                    $is_passed[$key]['has_fee_based_on_user'] = 'yes';
                }
            }
        }
        $main_is_passed = $this->wcpfc_pro_check_all_passed_general_rule( $is_passed, 'has_fee_based_on_user', $general_rule_match );
        return $main_is_passed;
    }

    public function wcpfc_pro_match_cart_subtotal_before_discount_rule( $wc_curr_version, $cart_total_array, $general_rule_match ) {
        global $woocommerce, $woocommerce_wpml;
        if ( $wc_curr_version >= 3.0 ) {
            $total = $this->wcpfc_pro_get_cart_subtotal();
        } else {
            $total = $woocommerce->cart->subtotal;
        }
        if ( isset( $woocommerce_wpml ) && !empty( $woocommerce_wpml->multi_currency ) ) {
            $new_total = $woocommerce_wpml->multi_currency->prices->unconvert_price_amount( $total );
        } else {
            $new_total = $total;
        }
        settype( $new_total, 'float' );
        $is_passed = array();
        foreach ( $cart_total_array as $key => $cart_total ) {
            settype( $cart_total['product_fees_conditions_values'], 'float' );
            if ( class_exists( 'WOOMULTI_CURRENCY_Data' ) ) {
                $cart_total['product_fees_conditions_values'] = $this->wcpfc_pro_convert_currency( $cart_total['product_fees_conditions_values'] );
            }
            if ( 'is_equal_to' === $cart_total['product_fees_conditions_is'] ) {
                if ( $cart_total['product_fees_conditions_values'] >= 0 || !empty( $cart_total['product_fees_conditions_values'] ) ) {
                    if ( $cart_total['product_fees_conditions_values'] === $new_total ) {
                        $is_passed[$key]['has_fee_based_on_cart_total'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_cart_total'] = 'no';
                    }
                }
            }
            if ( 'less_equal_to' === $cart_total['product_fees_conditions_is'] ) {
                if ( $cart_total['product_fees_conditions_values'] >= 0 || !empty( $cart_total['product_fees_conditions_values'] ) ) {
                    if ( $cart_total['product_fees_conditions_values'] >= $new_total ) {
                        $is_passed[$key]['has_fee_based_on_cart_total'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_cart_total'] = 'no';
                    }
                }
            }
            if ( 'less_then' === $cart_total['product_fees_conditions_is'] ) {
                if ( $cart_total['product_fees_conditions_values'] >= 0 || !empty( $cart_total['product_fees_conditions_values'] ) ) {
                    if ( $cart_total['product_fees_conditions_values'] > $new_total ) {
                        $is_passed[$key]['has_fee_based_on_cart_total'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_cart_total'] = 'no';
                    }
                }
            }
            if ( 'greater_equal_to' === $cart_total['product_fees_conditions_is'] ) {
                if ( $cart_total['product_fees_conditions_values'] >= 0 || !empty( $cart_total['product_fees_conditions_values'] ) ) {
                    if ( $cart_total['product_fees_conditions_values'] <= $new_total ) {
                        $is_passed[$key]['has_fee_based_on_cart_total'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_cart_total'] = 'no';
                    }
                }
            }
            if ( 'greater_then' === $cart_total['product_fees_conditions_is'] ) {
                $cart_total['product_fees_conditions_values'];
                if ( $cart_total['product_fees_conditions_values'] >= 0 || !empty( $cart_total['product_fees_conditions_values'] ) ) {
                    if ( $cart_total['product_fees_conditions_values'] < $new_total ) {
                        $is_passed[$key]['has_fee_based_on_cart_total'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_cart_total'] = 'no';
                    }
                }
            }
            if ( 'not_in' === $cart_total['product_fees_conditions_is'] ) {
                if ( $cart_total['product_fees_conditions_values'] >= 0 || !empty( $cart_total['product_fees_conditions_values'] ) ) {
                    if ( $new_total === $cart_total['product_fees_conditions_values'] ) {
                        $is_passed[$key]['has_fee_based_on_cart_total'] = 'no';
                    } else {
                        $is_passed[$key]['has_fee_based_on_cart_total'] = 'yes';
                    }
                }
            }
        }
        $main_is_passed = $this->wcpfc_pro_check_all_passed_general_rule( $is_passed, 'has_fee_based_on_cart_total', $general_rule_match );
        return $main_is_passed;
    }

    /**
     * get cart subtotal
     *
     * @return float $cart_subtotal
     * @since  1.5.2
     *
     */
    public function wcpfc_pro_get_cart_subtotal() {
        $get_customer = WC()->cart->get_customer();
        $get_customer_vat_exempt = WC()->customer->get_is_vat_exempt();
        $tax_display_cart = WC()->cart->get_tax_price_display_mode();
        $wc_prices_include_tax = wc_prices_include_tax();
        $tax_enable = wc_tax_enabled();
        $cart_subtotal = 0;
        if ( true === $tax_enable ) {
            if ( true === $wc_prices_include_tax ) {
                if ( 'incl' === $tax_display_cart && !($get_customer && $get_customer_vat_exempt) ) {
                    $cart_subtotal += WC()->cart->get_subtotal() + WC()->cart->get_subtotal_tax();
                } else {
                    $cart_subtotal += WC()->cart->get_subtotal();
                }
            } else {
                if ( 'incl' === $tax_display_cart && !($get_customer && $get_customer_vat_exempt) ) {
                    $cart_subtotal += WC()->cart->get_subtotal() + WC()->cart->get_subtotal_tax();
                } else {
                    $cart_subtotal += WC()->cart->get_subtotal();
                }
            }
        } else {
            $cart_subtotal += WC()->cart->get_subtotal();
        }
        return $cart_subtotal;
    }

    /**
     * Get the product count in each row of the cart.
     *
     * @return array An associative array with product IDs as keys and their quantities as values.
     */
    function get_cart_row_totals() {
        if ( !function_exists( 'WC' ) || !WC()->cart ) {
            return array();
            // Return an empty array or handle the error as needed
        }
        $cart_items = WC()->cart->get_cart();
        $product_counts = array();
        foreach ( $cart_items as $cart_item ) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];
            // Store the product ID and its quantity
            $product_counts[$product_id] = $quantity;
        }
        return $product_counts;
    }

    /**
     * Match rule based on total cart quantity
     *
     * @param array $quantity_array
     *
     * @return array $is_passed
     * @since    1.3.3
     *
     * @uses     WC_Cart::get_cart()
     *
     */
    public function wcpfc_pro_match_cart_total_cart_qty_rule( $cart_array, $quantity_array, $general_rule_match ) {
        $quantity_total = 0;
        foreach ( $cart_array as $woo_cart_item ) {
            $quantity_total += $woo_cart_item['quantity'];
        }
        $is_passed = array();
        foreach ( $quantity_array as $key => $quantity ) {
            settype( $quantity['product_fees_conditions_values'], 'integer' );
            if ( 'is_equal_to' === $quantity['product_fees_conditions_is'] ) {
                if ( !empty( $quantity['product_fees_conditions_values'] ) ) {
                    if ( $quantity_total === $quantity['product_fees_conditions_values'] ) {
                        $is_passed[$key]['has_fee_based_on_quantity'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_quantity'] = 'no';
                    }
                }
            }
            if ( 'less_equal_to' === $quantity['product_fees_conditions_is'] ) {
                if ( !empty( $quantity['product_fees_conditions_values'] ) ) {
                    if ( $quantity['product_fees_conditions_values'] >= $quantity_total ) {
                        $is_passed[$key]['has_fee_based_on_quantity'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_quantity'] = 'no';
                    }
                }
            }
            if ( 'less_then' === $quantity['product_fees_conditions_is'] ) {
                if ( !empty( $quantity['product_fees_conditions_values'] ) ) {
                    if ( $quantity['product_fees_conditions_values'] > $quantity_total ) {
                        $is_passed[$key]['has_fee_based_on_quantity'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_quantity'] = 'no';
                    }
                }
            }
            if ( 'greater_equal_to' === $quantity['product_fees_conditions_is'] ) {
                if ( !empty( $quantity['product_fees_conditions_values'] ) ) {
                    if ( $quantity['product_fees_conditions_values'] <= $quantity_total ) {
                        $is_passed[$key]['has_fee_based_on_quantity'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_quantity'] = 'no';
                    }
                }
            }
            if ( 'greater_then' === $quantity['product_fees_conditions_is'] ) {
                if ( !empty( $quantity['product_fees_conditions_values'] ) ) {
                    if ( $quantity['product_fees_conditions_values'] < $quantity_total ) {
                        $is_passed[$key]['has_fee_based_on_quantity'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_quantity'] = 'no';
                    }
                }
            }
            if ( 'not_in' === $quantity['product_fees_conditions_is'] ) {
                if ( !empty( $quantity['product_fees_conditions_values'] ) ) {
                    if ( $quantity_total === $quantity['product_fees_conditions_values'] ) {
                        $is_passed[$key]['has_fee_based_on_quantity'] = 'no';
                    } else {
                        $is_passed[$key]['has_fee_based_on_quantity'] = 'yes';
                    }
                }
            }
        }
        $main_is_passed = $this->wcpfc_pro_check_all_passed_general_rule( $is_passed, 'has_fee_based_on_quantity', $general_rule_match );
        return $main_is_passed;
    }

    /**
     * Find a matching zone for a given package.
     *
     * @param array $available_zone_id_array
     *
     * @return int $return_zone_id
     * @uses   WC_Customer::get_shipping_state()
     * @uses   WC_Customer::get_shipping_postcode()
     * @uses   wc_postcode_location_matcher()
     *
     * @since  3.0.0
     *
     * @uses   WC_Customer::get_shipping_country()
     */
    public function wcpfc_pro_check_zone_available( $available_zone_id_array ) {
        $return_zone_id = '';
        $country = strtoupper( wc_clean( WC()->customer->get_shipping_country() ) );
        $state = strtoupper( wc_clean( WC()->customer->get_shipping_state() ) );
        $postcode = wc_normalize_postcode( wc_clean( WC()->customer->get_shipping_postcode() ) );
        $state_flag = false;
        $flag = false;
        $postcode_locations = array();
        $zone_array = array();
        foreach ( $available_zone_id_array as $zone_id ) {
            $zone_by_id = WC_Shipping_Zones::get_zone_by( 'zone_id', $zone_id );
            $zones = array();
            if ( isset( $zone_by_id ) && !empty( $zone_by_id ) ) {
                $zones = $zone_by_id->get_zone_locations();
            }
            if ( !empty( $zones ) ) {
                foreach ( $zones as $zone_location ) {
                    if ( 'country' === $zone_location->type || 'state' === $zone_location->type ) {
                        $zone_array[$zone_id][$zone_location->type][] = $zone_location->code;
                    }
                    $location = new stdClass();
                    if ( 'postcode' === $zone_location->type ) {
                        $location->zone_id = $zone_id;
                        $location->location_code = $zone_location->code;
                        if ( false !== strpos( $location->location_code, '...' ) ) {
                            $postcode_locations_ex = explode( '...', $location->location_code );
                            $start_index = $postcode_locations_ex[0];
                            $end_index = $postcode_locations_ex[1];
                            if ( $start_index < $end_index ) {
                                $total_count = $end_index - $start_index;
                                $new_index = $start_index;
                                for ($i = 0; $i <= $total_count; $i++) {
                                    $desh_location = new stdClass();
                                    if ( 0 === $i ) {
                                        $new_index = $start_index;
                                    } elseif ( $total_count === $i ) {
                                        $new_index = $end_index;
                                    } else {
                                        $new_index += 1;
                                    }
                                    $desh_location->zone_id = $zone_id;
                                    settype( $new_index, 'string' );
                                    $desh_location->location_code = $new_index;
                                    $postcode_locations[$zone_id][$i] = $desh_location;
                                }
                            }
                        } else {
                            $postcode_locations[$zone_id][] = $location;
                        }
                    }
                }
            }
        }
        if ( !empty( $zone_array ) ) {
            foreach ( $zone_array as $zone_id => $zone_location_detail ) {
                foreach ( $zone_location_detail as $zone_location_type => $zone_location_code ) {
                    if ( 'country' === $zone_location_type ) {
                        if ( $postcode_locations ) {
                            foreach ( $postcode_locations as $post_zone_id => $postcode_location_detail ) {
                                if ( $zone_id === $post_zone_id ) {
                                    if ( in_array( $country, $zone_location_code, true ) ) {
                                        $flag = 1;
                                    }
                                } else {
                                    if ( in_array( $country, $zone_location_code, true ) ) {
                                        $return_zone_id = $zone_id;
                                    }
                                }
                            }
                        } else {
                            if ( in_array( $country, $zone_location_code, true ) ) {
                                $return_zone_id = $zone_id;
                            }
                        }
                    }
                    if ( 'state' === $zone_location_type ) {
                        $state_array = array();
                        foreach ( $zone_location_code as $subzone_location_code ) {
                            if ( false !== strpos( $subzone_location_code, ':' ) ) {
                                $sub_zone_location_code_explode = explode( ':', $subzone_location_code );
                            }
                            $state_array[] = $sub_zone_location_code_explode[1];
                            if ( !$postcode_locations ) {
                                if ( in_array( $state, $state_array, true ) ) {
                                    $return_zone_id = $zone_id;
                                    $state_flag = true;
                                }
                            } else {
                                if ( in_array( $state, $state_array, true ) ) {
                                    $flag = 1;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if ( $postcode_locations ) {
                $flag = 1;
            }
        }
        if ( true === $state_flag || 1 === $flag ) {
            if ( $postcode_locations ) {
                foreach ( $postcode_locations as $post_zone_id => $postcode_location_detail ) {
                    $matches = wc_postcode_location_matcher(
                        $postcode,
                        $postcode_location_detail,
                        'zone_id',
                        'location_code',
                        $country
                    );
                    $matches_count = count( $matches );
                    if ( 0 !== $matches_count ) {
                        $matches_array_key = array_keys( $matches );
                        $return_zone_id = $matches_array_key[0];
                    } else {
                        $return_zone_id = '';
                    }
                }
            }
        }
        return $return_zone_id;
    }

    /**
     * Match variable products rules
     *
     * @param array $cart_product_ids_array
     * @param array $variableproduct_array
     * * @param string $general_rule_match
     *
     * @return string $main_is_passed
     *
     * @since    1.3.3
     *
     */
    public function wcpfc_pro_match_variable_products_rule( $cart_product_ids_array, $variableproduct_array, $general_rule_match ) {
        $is_passed = array();
        $passed_product = array();
        foreach ( $variableproduct_array as $key => $product ) {
            if ( 'is_equal_to' === $product['product_fees_conditions_is'] ) {
                if ( !empty( $product['product_fees_conditions_values'] ) ) {
                    foreach ( $product['product_fees_conditions_values'] as $product_id ) {
                        settype( $product_id, 'integer' );
                        $passed_product[] = $product_id;
                        if ( in_array( $product_id, $cart_product_ids_array, true ) ) {
                            $is_passed[$key]['has_fee_based_on_product'] = 'yes';
                            break;
                        } else {
                            $is_passed[$key]['has_fee_based_on_product'] = 'no';
                        }
                    }
                }
            }
            if ( 'not_in' === $product['product_fees_conditions_is'] ) {
                if ( !empty( $product['product_fees_conditions_values'] ) ) {
                    foreach ( $product['product_fees_conditions_values'] as $product_id ) {
                        settype( $product_id, 'integer' );
                        if ( in_array( $product_id, $cart_product_ids_array, true ) ) {
                            $is_passed[$key]['has_fee_based_on_product'] = 'no';
                            break;
                        } else {
                            $is_passed[$key]['has_fee_based_on_product'] = 'yes';
                        }
                    }
                }
            }
        }
        $main_is_passed = $this->wcpfc_pro_check_all_passed_general_rule( $is_passed, 'has_fee_based_on_product', $general_rule_match );
        return $main_is_passed;
    }

    /**
     * Match category rules
     *
     * @param array  $cart_product_ids_array
     * @param array  $category_array
     * @param string $general_rule_match
     *
     * @return string $main_is_passed
     * @uses     wcpfc_pro_array_flatten()
     *
     * @since    1.3.3
     *
     * @uses     wp_get_post_terms()
     */
    public function wcpfc_pro_match_category_rule( $cart_product_ids_array, $category_array, $general_rule_match ) {
        $is_passed = array();
        $cart_category_id_array = array();
        foreach ( $cart_product_ids_array as $product ) {
            $cart_product_category = wp_get_post_terms( $product, 'product_cat', array(
                'fields' => 'ids',
            ) );
            if ( isset( $cart_product_category ) && !empty( $cart_product_category ) && is_array( $cart_product_category ) ) {
                $cart_category_id_array[] = $cart_product_category;
            }
        }
        $get_cat_all = array_unique( $this->wcpfc_pro_array_flatten( $cart_category_id_array ) );
        foreach ( $category_array as $key => $category ) {
            if ( 'is_equal_to' === $category['product_fees_conditions_is'] ) {
                if ( !empty( $category['product_fees_conditions_values'] ) ) {
                    foreach ( $category['product_fees_conditions_values'] as $category_id ) {
                        settype( $category_id, 'integer' );
                        if ( in_array( $category_id, $get_cat_all, true ) ) {
                            $is_passed[$key]['has_fee_based_on_category'] = 'yes';
                            break;
                        } else {
                            $is_passed[$key]['has_fee_based_on_category'] = 'no';
                        }
                    }
                }
            }
            if ( 'not_in' === $category['product_fees_conditions_is'] ) {
                if ( !empty( $category['product_fees_conditions_values'] ) ) {
                    foreach ( $category['product_fees_conditions_values'] as $category_id ) {
                        settype( $category_id, 'integer' );
                        if ( in_array( $category_id, $get_cat_all, true ) ) {
                            $is_passed[$key]['has_fee_based_on_category'] = 'no';
                            break;
                        } else {
                            $is_passed[$key]['has_fee_based_on_category'] = 'yes';
                        }
                    }
                }
            }
        }
        $main_is_passed = $this->wcpfc_pro_check_all_passed_general_rule( $is_passed, 'has_fee_based_on_category', $general_rule_match );
        return $main_is_passed;
    }

    /**
     * Match specific product quantity rules
     *
     * @param int    $shipping_method_id_val
     * @param array  $cart_array
     * @param array  $product_qty_array
     * @param string $general_rule_match
     *
     * @param string $default_lang
     *
     * @return string $main_is_passed
     * @since    3.4
     *
     */
    public function wcpfc_pro_match_product_qty_rule(
        $fees_id,
        $cart_array,
        $product_qty_array,
        $general_rule_match,
        $sitepress,
        $default_lang
    ) {
        $products_based_qty = 0;
        $products_based_qty = $this->wcpfc_pro_fees_per_qty_on_ap_rules_off(
            $fees_id,
            $cart_array,
            $products_based_qty,
            0,
            $sitepress,
            $default_lang
        );
        $main_is_passed = $this->wcpfc_pro_match_product_based_qty_rule( $products_based_qty[0], $product_qty_array, $general_rule_match );
        return $main_is_passed;
    }

    /**
     * Match rule based on product qty
     *
     * @param array  $cart_array
     * @param array  $quantity_array
     * @param string $general_rule_match
     *
     * @return string $main_is_passed
     * @since    3.4
     *
     * @uses     WC_Cart::get_cart()
     *
     */
    public function wcpfc_pro_match_product_based_qty_rule( $product_qty, $quantity_array, $general_rule_match ) {
        $quantity_total = 0;
        if ( 0 < $product_qty ) {
            $quantity_total = $product_qty;
        }
        $is_passed = array();
        foreach ( $quantity_array as $key => $quantity ) {
            settype( $quantity['product_fees_conditions_values'], 'integer' );
            if ( 'is_equal_to' === $quantity['product_fees_conditions_is'] ) {
                if ( !empty( $quantity['product_fees_conditions_values'] ) ) {
                    if ( $quantity_total === $quantity['product_fees_conditions_values'] ) {
                        $is_passed[$key]['has_fee_based_on_product_qty'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_product_qty'] = 'no';
                    }
                }
            }
            if ( 'less_equal_to' === $quantity['product_fees_conditions_is'] ) {
                if ( !empty( $quantity['product_fees_conditions_values'] ) ) {
                    if ( $quantity['product_fees_conditions_values'] >= $quantity_total ) {
                        $is_passed[$key]['has_fee_based_on_product_qty'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_product_qty'] = 'no';
                    }
                }
            }
            if ( 'less_then' === $quantity['product_fees_conditions_is'] ) {
                if ( !empty( $quantity['product_fees_conditions_values'] ) ) {
                    if ( $quantity['product_fees_conditions_values'] > $quantity_total ) {
                        $is_passed[$key]['has_fee_based_on_product_qty'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_product_qty'] = 'no';
                    }
                }
            }
            if ( 'greater_equal_to' === $quantity['product_fees_conditions_is'] ) {
                if ( !empty( $quantity['product_fees_conditions_values'] ) ) {
                    if ( $quantity['product_fees_conditions_values'] <= $quantity_total ) {
                        $is_passed[$key]['has_fee_based_on_product_qty'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_product_qty'] = 'no';
                    }
                }
            }
            if ( 'greater_then' === $quantity['product_fees_conditions_is'] ) {
                if ( !empty( $quantity['product_fees_conditions_values'] ) ) {
                    if ( $quantity['product_fees_conditions_values'] < $quantity_total ) {
                        $is_passed[$key]['has_fee_based_on_product_qty'] = 'yes';
                    } else {
                        $is_passed[$key]['has_fee_based_on_product_qty'] = 'no';
                    }
                }
            }
            if ( 'not_in' === $quantity['product_fees_conditions_is'] ) {
                if ( !empty( $quantity['product_fees_conditions_values'] ) ) {
                    if ( $quantity_total === $quantity['product_fees_conditions_values'] ) {
                        $is_passed[$key]['has_fee_based_on_product_qty'] = 'no';
                    } else {
                        $is_passed[$key]['has_fee_based_on_product_qty'] = 'yes';
                    }
                }
            }
        }
        /**
         * Filter for matched all passed rules.
         *
         * @since  3.8
         *
         * @author jb
         */
        $main_is_passed = $this->wcpfc_pro_check_all_passed_general_rule( apply_filters(
            'wcpfc_pro_match_product_based_qty_rule_ft',
            $is_passed,
            $product_qty,
            $quantity_array,
            'has_fee_based_on_product_qty',
            $general_rule_match
        ), 'has_fee_based_on_product_qty', $general_rule_match );
        return $main_is_passed;
    }

    /**
     * Remove WooCommerce currency symbol
     *
     * @param float $price
     *
     * @return float $new_price2
     * @since  1.0.0
     *
     * @uses   get_woocommerce_currency_symbol()
     *
     */
    public function wcpfc_pro_remove_currency_symbol( $price ) {
        $args = array(
            'decimal_separator'  => wc_get_price_decimal_separator(),
            'thousand_separator' => wc_get_price_thousand_separator(),
        );
        $wc_currency_symbol = get_woocommerce_currency_symbol();
        $cleanText = wp_strip_all_tags( $price );
        $new_price = str_replace( $wc_currency_symbol, '', $cleanText );
        $tnew_price = str_replace( $args['thousand_separator'], '', $new_price );
        $dnew_price = str_replace( $args['decimal_separator'], '.', $tnew_price );
        $new_price2 = preg_replace( '/[^.\\d]/', '', $dnew_price );
        return $new_price2;
    }

    /**
     * Price format
     *
     * @param string $price
     *
     * @return string $price
     * @since  1.3.3
     *
     */
    public function wcpfc_pro_price_format( $price ) {
        $price = floatval( $price );
        return $price;
    }

    /**
     * Get applied fees on frontside
     *
     * @return array|object $fees
     * @since  1.3.3
     *
     */
    public function wcpfc_pro_get_applied_fees() {
        // Check if WooCommerce cart is initialized
        if ( !WC()->cart ) {
            return array();
            // Return an empty array if the cart is not initialized
        }
        $fees = WC()->cart->get_fees();
        $fee_names = wp_list_pluck( $fees, 'name' );
        // Use wp_list_pluck to extract names from fees.
        $args = array(
            'post_type'      => 'wc_conditional_fee',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'post_title__in' => $fee_names,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        );
        $query = new WP_Query($args);
        $fee_posts = array();
        if ( $query->have_posts() ) {
            $fee_posts = $query->posts;
        }
        foreach ( $fees as &$fee ) {
            foreach ( $fee_posts as $fee_post ) {
                if ( $fee->name === $fee_post->post_title ) {
                    $fee->menu_order = $fee_post->menu_order;
                    break;
                    // Break the inner loop when a match is found
                }
            }
        }
        uasort( $fees, array($this, 'wcpfc_pro_sorting_fees') );
        return $fees;
    }

    /**
     * Sorting fees on front side
     *
     * @param object $a
     * @param object $b
     *
     * @return int
     * @since  1.3.3
     *
     */
    public function wcpfc_pro_sorting_fees( $a, $b ) {
        if ( isset( $a->menu_order ) && isset( $b->menu_order ) ) {
            return ( $a->menu_order < $b->menu_order ? -1 : 1 );
        }
    }

    /**
     * Cart total with tax and shipping cost
     *
     * @return number $cart_final_total.
     *
     * @since  3.8
     *
     * @author sj
     */
    public function wcpfc_cart_total() {
        if ( !WC()->cart ) {
            return 0;
        }
        $cart_final_total = 0;
        $total_tax = 0;
        $total_shipping = 0;
        $cart_subtotal = WC()->cart->get_cart_contents_total();
        foreach ( WC()->cart->get_tax_totals() as $taxy ) {
            $total_tax += $taxy->amount;
        }
        // Loop through shipping packages from WC_Session (They can be multiple in some cases)
        foreach ( WC()->cart->get_shipping_packages() as $package_id => $package ) {
            // Check if a shipping for the current package exist
            if ( WC()->session->__isset( 'shipping_for_package_' . $package_id ) ) {
                // Loop through shipping rates for the current package
                foreach ( WC()->session->get( 'shipping_for_package_' . $package_id )['rates'] as $shipping_rate_id => $shipping_rate ) {
                    if ( in_array( $shipping_rate_id, WC()->session->get( 'chosen_shipping_methods' ), true ) ) {
                        $shipping_rate = WC()->session->get( 'shipping_for_package_' . $package_id )['rates'][$shipping_rate_id];
                        $total_shipping += $shipping_rate->get_cost();
                        // The cost without tax
                    }
                }
            }
        }
        $cart_final_total = $cart_subtotal + $total_tax + $total_shipping;
        return $cart_final_total;
    }

    /**
     * Sanitize string
     *
     * @param mixed $string
     *
     * @return string $result
     * @since 1.0.0
     *
     */
    public function wcpfc_fee_string_sanitize( $string ) {
        $result = preg_replace( "/[^ A-Za-z0-9_=.*()+\\-\\[\\]\\/]+/", '', html_entity_decode( $string, ENT_QUOTES ) );
        return $result;
    }

    public function wcpfc_pro_convert_currency( $amount ) {
        $multiCurrencySettings = ( class_exists( 'WOOMULTI_CURRENCY_Data' ) ? WOOMULTI_CURRENCY_Data::get_ins() : null );
        if ( $multiCurrencySettings ) {
            $currentCurrency = $multiCurrencySettings->get_current_currency();
            if ( $currentCurrency ) {
                $wmcCurrencies = $multiCurrencySettings->get_list_currencies();
                $currentCurrencyRate = floatval( $wmcCurrencies[$currentCurrency]['rate'] );
                $amount *= $currentCurrencyRate;
            }
        }
        return $amount;
    }

    public function wcpfc_pro_get_all_fees( $args = array() ) {
        $fees_args = wp_parse_args( $args, array(
            'post_type'        => 'wc_conditional_fee',
            'post_status'      => 'publish',
            'posts_per_page'   => -1,
            'suppress_filters' => false,
            'fields'           => 'ids',
            'order'            => 'DESC',
            'orderby'          => 'ID',
        ) );
        $wcpfc_pro_get_all_fees_query = new WP_Query($fees_args);
        $wcpfc_pro_get_all_fees = $wcpfc_pro_get_all_fees_query->get_posts();
        return $wcpfc_pro_get_all_fees;
    }

    /**
     * Apply fee for first order
     * 
     * @param int $fees_id
     * 
     * @since 1.0.0
     */
    public function wcpfc_pro_apply_fee_for_first_order( $fees_id ) {
        if ( empty( $fees_id ) ) {
            return false;
        }
        $conditional_fee = new \Woocommerce_Conditional_Product_Fees($fees_id);
        if ( empty( $conditional_fee ) ) {
            return false;
        }
        $getFirstOrderForUser = $conditional_fee->get_first_order_for_user();
        $firstOrderForUser = ( !empty( $getFirstOrderForUser ) && 'on' === $getFirstOrderForUser ? true : false );
        if ( $firstOrderForUser && is_user_logged_in() ) {
            $current_user_id = get_current_user_id();
            $check_for_user = $this->wcpfc_check_first_order_for_user__premium_only( $current_user_id );
            if ( !$check_for_user ) {
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    // Function to return custom allowed HTML
    public function wcpfc_get_allowed_html() {
        // Get default allowed HTML
        $allowed_html = wp_kses_allowed_html( 'post' );
        // Add select, option, input (checkbox/radio) to the allowed HTML
        $allowed_html['select'] = array(
            'name'       => array(),
            'id'         => array(),
            'class'      => array(),
            'data-value' => array(),
        );
        $allowed_html['option'] = array(
            'value'      => array(),
            'selected'   => array(),
            'data-value' => array(),
        );
        $allowed_html['input'] = array(
            'type'       => array(),
            'name'       => array(),
            'id'         => array(),
            'value'      => array(),
            'checked'    => array(),
            'class'      => array(),
            'data-value' => array(),
        );
        return $allowed_html;
    }

    /* Ajax request check */
    public function is_ajax_request() {
        return isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest';
        //phpcs:ignore
    }

    /**
     * Gets the main Woocommerce Conditional Product Fees For Checkout Pro instance.
     *
     * Ensures only one instance loaded at one time.
     *
     * @see \wcpfc_pro()
     *
     * @since 1.0.0
     *
     * @return \Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Public
     */
    public static function instance( $plugin_name, $version ) {
        if ( null === self::$instance ) {
            self::$instance = new self($plugin_name, $version);
        }
        return self::$instance;
    }

}

/**
 * Returns the One True Instance of Woocommerce Conditional Product Fees For Checkout Pro Public class object.
 *
 * @since 1.0.0
 *
 * @return \Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Public
 */
function wcpfc_pro_public() {
    return \Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Public::instance( '', '' );
}

/** Show the fees once trial coupon code apply on subscription product */
add_filter( 'wcs_remove_fees_from_initial_cart', 'wcs_remove_fees_from_initial_cart_custom' );
function wcs_remove_fees_from_initial_cart_custom() {
    return false;
}
