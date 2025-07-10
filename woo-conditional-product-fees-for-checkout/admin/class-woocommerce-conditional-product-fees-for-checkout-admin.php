<?php

// phpcs:ignore
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.multidots.com
 * @package    Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @subpackage Woocommerce_Conditional_Product_Fees_For_Checkout_Pro/admin
 * @since      1.0.0
 * @author     Multidots <inquiry@multidots.in>
 */
class Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Admin {
    const wcpfc_post_type = 'wc_conditional_fee';

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
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name
     * @param string $version
     *
     * @since    1.0.0
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        require_once plugin_dir_path( dirname( __FILE__ ) ) . '/admin/class-woocommerce-conditional-product-fees.php';
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function wcpfc_admin_enqueue_styles( $hook ) {
        if ( strpos( $hook, '_page_wcpf' ) !== false ) {
            wp_enqueue_style(
                $this->plugin_name . 'select2-min',
                plugin_dir_url( __FILE__ ) . 'css/select2.min.css',
                array(),
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . '-jquery-ui-css',
                plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . '-timepicker-min-css',
                plugin_dir_url( __FILE__ ) . 'css/jquery.timepicker.min.css',
                $this->version,
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . 'font-awesome',
                plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . 'main-style',
                plugin_dir_url( __FILE__ ) . 'css/style.css',
                array(),
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . 'media-css',
                plugin_dir_url( __FILE__ ) . 'css/media.css',
                array(),
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . 'plugin-new-style',
                plugin_dir_url( __FILE__ ) . 'css/plugin-new-style.css',
                array(),
                'all'
            );
            if ( !(wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code()) ) {
                wp_enqueue_style(
                    $this->plugin_name . 'upgrade-dashboard-style',
                    plugin_dir_url( __FILE__ ) . 'css/upgrade-dashboard.css',
                    array(),
                    'all'
                );
            }
            wp_enqueue_style(
                $this->plugin_name . 'plugin-setup-wizard',
                plugin_dir_url( __FILE__ ) . 'css/plugin-setup-wizard.css',
                array(),
                'all'
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function wcpfc_admin_enqueue_scripts( $hook ) {
        wp_enqueue_style( 'wp-jquery-ui-dialog' );
        wp_enqueue_script( 'jquery-ui-accordion' );
        if ( strpos( $hook, '_page_wcpf' ) !== false ) {
            wp_enqueue_script(
                $this->plugin_name . '-select2-full-min',
                plugin_dir_url( __FILE__ ) . 'js/select2.full.min.js',
                array('jquery', 'jquery-ui-datepicker'),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name . '-chart-js',
                plugin_dir_url( __FILE__ ) . 'js/chart.js',
                array('jquery'),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name . 'freemius_pro',
                'https://checkout.freemius.com/checkout.min.js',
                array('jquery'),
                $this->version,
                true
            );
            wp_enqueue_script(
                $this->plugin_name . '-help-scout-beacon-js',
                plugin_dir_url( __FILE__ ) . 'js/help-scout-beacon.js',
                array('jquery'),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'js/woocommerce-conditional-product-fees-for-checkout-admin.js',
                array(
                    'jquery',
                    'jquery-ui-dialog',
                    'jquery-ui-accordion',
                    'jquery-ui-sortable',
                    'select2'
                ),
                $this->version,
                false
            );
            wp_enqueue_script( 'jquery-tiptip' );
            wp_enqueue_script( 'jquery-blockui' );
            wp_enqueue_script(
                $this->plugin_name . '-tablesorter-js',
                plugin_dir_url( __FILE__ ) . 'js/jquery.tablesorter.js',
                array('jquery'),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name . '-timepicker-js',
                plugin_dir_url( __FILE__ ) . 'js/jquery.timepicker.js',
                array('jquery'),
                $this->version,
                false
            );
            $weight_unit = get_option( 'woocommerce_weight_unit' );
            $weight_unit = ( !empty( $weight_unit ) ? '(' . $weight_unit . ')' : '' );
            wp_localize_script( $this->plugin_name, 'coditional_vars', array(
                'ajaxurl'                                  => admin_url( 'admin-ajax.php' ),
                'ajax_icon'                                => esc_url( plugin_dir_url( __FILE__ ) . '/images/ajax-loader.gif' ),
                'plugin_url'                               => plugin_dir_url( __FILE__ ),
                'dsm_ajax_nonce'                           => wp_create_nonce( 'dsm_nonce' ),
                'disable_fees_ajax_nonce'                  => wp_create_nonce( 'disable_fees_nonce' ),
                'setup_wizard_ajax_nonce'                  => wp_create_nonce( 'wizard_ajax_nonce' ),
                'select2_ajax_nonce'                       => wp_create_nonce( 'select2_data_nonce' ),
                'country'                                  => esc_html__( 'Country', 'woocommerce-conditional-product-fees-for-checkout' ),
                'city'                                     => esc_html__( 'City', 'woocommerce-conditional-product-fees-for-checkout' ),
                'state_disabled'                           => esc_html__( 'State 🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'city_disabled'                            => esc_html__( 'City 🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'postcode_disabled'                        => esc_html__( 'Postcode 🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'zone_disabled'                            => esc_html__( 'Zone 🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'cart_contains_product'                    => esc_html__( 'Cart contains product', 'woocommerce-conditional-product-fees-for-checkout' ),
                'cart_contains_variable_product'           => esc_html__( 'Cart contains variable product', 'woocommerce-conditional-product-fees-for-checkout' ),
                'cart_category_product_disabled'           => esc_html__( 'Cart contains category\'s product 🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'cart_contains_tag_product'                => esc_html__( 'Cart contains tag\'s product', 'woocommerce-conditional-product-fees-for-checkout' ),
                'cart_contains_product_qty'                => esc_html__( 'Cart contains product\'s quantity', 'woocommerce-conditional-product-fees-for-checkout' ),
                'product_qty_msg'                          => esc_html__( 'This rule will only work if you have selected any one Product Specific option. ', 'woocommerce-conditional-product-fees-for-checkout' ),
                'city_msg'                                 => esc_html__( 'Please enter each city name in a new line.', 'woocommerce-conditional-product-fees-for-checkout' ),
                'postcode_msg'                             => esc_html__( 'Please enter each postcode/zip code in a new line.', 'woocommerce-conditional-product-fees-for-checkout' ),
                'user'                                     => esc_html__( 'User', 'woocommerce-conditional-product-fees-for-checkout' ),
                'user_role_disabled'                       => esc_html__( 'User Role 🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'cart_subtotal_before_discount'            => esc_html__( 'Cart Subtotal (Before Discount)', 'woocommerce-conditional-product-fees-for-checkout' ),
                'cart_subtotal_after_discount_disabled'    => esc_html__( 'Cart Subtotal (After Discount) 🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'cart_subtotal_specific_products_disabled' => esc_html__( 'Cart Subtotal (Specific products) 🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'quantity'                                 => esc_html__( 'Quantity', 'woocommerce-conditional-product-fees-for-checkout' ),
                'weight_disabled'                          => esc_html__( 'Weight', 'woocommerce-conditional-product-fees-for-checkout' ) . ' ' . esc_html( $weight_unit ) . ' ' . esc_html__( '🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'coupon_disabled'                          => esc_html__( 'Coupon 🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'shipping_class_disabled'                  => esc_html__( 'Shipping Class 🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'payment_gateway_disabled'                 => esc_html__( 'Payment Gateway 🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'shipping_method_disabled'                 => esc_html__( 'Shipping Method 🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'equal_to'                                 => esc_html__( 'Equal to ( = )', 'woocommerce-conditional-product-fees-for-checkout' ),
                'not_equal_to'                             => esc_html__( 'Not Equal to ( != )', 'woocommerce-conditional-product-fees-for-checkout' ),
                'less_or_equal_to'                         => esc_html__( 'Less or Equal to ( <= )', 'woocommerce-conditional-product-fees-for-checkout' ),
                'less_than'                                => esc_html__( 'Less then ( < )', 'woocommerce-conditional-product-fees-for-checkout' ),
                'greater_or_equal_to'                      => esc_html__( 'Greater or Equal to ( >= )', 'woocommerce-conditional-product-fees-for-checkout' ),
                'greater_than'                             => esc_html__( 'Greater then ( > )', 'woocommerce-conditional-product-fees-for-checkout' ),
                'delete'                                   => esc_html__( 'Delete', 'woocommerce-conditional-product-fees-for-checkout' ),
                'location_specific'                        => esc_html__( 'Location Specific', 'woocommerce-conditional-product-fees-for-checkout' ),
                'product_specific'                         => esc_html__( 'Product Specific', 'woocommerce-conditional-product-fees-for-checkout' ),
                'attribute_specific'                       => esc_html__( 'Attribute Specific', 'woocommerce-conditional-product-fees-for-checkout' ),
                'shipping_specific'                        => esc_html__( 'Shipping Specific', 'woocommerce-conditional-product-fees-for-checkout' ),
                'user_specific'                            => esc_html__( 'User Specific', 'woocommerce-conditional-product-fees-for-checkout' ),
                'cart_specific'                            => esc_html__( 'Cart Specific', 'woocommerce-conditional-product-fees-for-checkout' ),
                'payment_specific'                         => esc_html__( 'Payment Specific', 'woocommerce-conditional-product-fees-for-checkout' ),
                'attribute_list_disabled'                  => esc_html__( 'Color 🔒', 'woocommerce-conditional-product-fees-for-checkout' ),
                'warning_msg1'                             => sprintf( __( '<p><b style="color: red;">Note: </b>If entered price is more than total fee price than Message looks like: <b>Fee Name: Curreny Symbole like($) -60.00 Price </b> and if fee minus price is more than total price than it will set Total Price to Zero(0).</p>', 'woocommerce-conditional-product-fees-for-checkout' ) ),
                'note'                                     => esc_html__( 'Note: ', 'woocommerce-conditional-product-fees-for-checkout' ),
                'cart_contains_product_msg'                => esc_html__( 'Please make sure that when you add rules in Advanced Fees Price Rules > Cost on Product Section It contains in above selected product list, otherwise it may not apply proper fees. For more detail please view our documentation. ', 'woocommerce-conditional-product-fees-for-checkout' ),
                'cart_contains_category_msg'               => esc_html__( 'Please make sure that when you add rules in Advanced Fees Price Rules > Cost on Category Section It contains in above selected category list, otherwise it may not apply proper fees. For more detail please view our documentation. ', 'woocommerce-conditional-product-fees-for-checkout' ),
                'cart_subtotal_after_discount_msg'         => esc_html__( 'This rule will apply when you would apply coupon in front side. ', 'woocommerce-conditional-product-fees-for-checkout' ),
                'cart_subtotal_specific_products_msg'      => esc_html__( 'This rule will apply when you would add cart contain product. ', 'woocommerce-conditional-product-fees-for-checkout' ),
                'click_here'                               => esc_html__( 'Click Here', 'woocommerce-conditional-product-fees-for-checkout' ),
                'doc_url'                                  => esc_url( "https://docs.thedotstore.com/category/191-premium-plugin-settings" ),
                'product_doc_url'                          => esc_url( 'https://docs.thedotstore.com/article/198-how-to-add-rules-based-on-simple-product-and-variable-products' ),
                'category_doc_url'                         => esc_url( 'https://docs.thedotstore.com/article/199-how-to-add-category-based-fees' ),
                'product_qty_doc_url'                      => esc_url( 'https://docs.thedotstore.com/article/726-product-specific-fee-rules' ),
                'currency_symbol'                          => esc_attr( get_woocommerce_currency_symbol() ),
                'dpb_api_url'                              => WCPFC_STORE_URL,
                'select_product'                           => esc_html__( 'Select a product', 'woocommerce-conditional-product-fees-for-checkout' ),
                'select_category'                          => esc_html__( 'Select a category', 'woocommerce-conditional-product-fees-for-checkout' ),
                'select_days'                              => esc_html__( 'Select day of the week', 'woocommerce-conditional-product-fees-for-checkout' ),
                'select_country'                           => esc_html__( 'Select a country', 'woocommerce-conditional-product-fees-for-checkout' ),
                'select_tag'                               => esc_html__( 'Select a product tag', 'woocommerce-conditional-product-fees-for-checkout' ),
                'select_user'                              => esc_html__( 'Select a user', 'woocommerce-conditional-product-fees-for-checkout' ),
                'select_float_number'                      => esc_html__( '0.00', 'woocommerce-conditional-product-fees-for-checkout' ),
                'select_integer_number'                    => esc_html__( '10', 'woocommerce-conditional-product-fees-for-checkout' ),
                'select_city'                              => esc_html__( "City 1\nCity 2", 'woocommerce-conditional-product-fees-for-checkout' ),
            ) );
        }
    }

    /**
     * Register Admin menu pages.
     *
     * @since    1.0.0
     */
    public function wcpfc_admin_menu_pages() {
        $chk_move_menu_under_wc = get_option( 'chk_move_menu_under_wc' );
        $parent_menu = 'dots_store';
        $main_menu_title = __( 'WooCommerce Extra Fees', 'woocommerce-conditional-product-fees-for-checkout' );
        if ( 'on' === $chk_move_menu_under_wc ) {
            $parent_menu = 'woocommerce';
            $main_menu_title = __( 'Extra Fees', 'woocommerce-conditional-product-fees-for-checkout' );
        } else {
            if ( empty( $GLOBALS['admin_page_hooks']['dots_store'] ) ) {
                add_menu_page(
                    'Dotstore Plugins',
                    'Dotstore Plugins',
                    'null',
                    'dots_store',
                    array($this, 'wcpfc-pro-list'),
                    'dashicons-marker',
                    25
                );
            }
        }
        $get_hook = add_submenu_page(
            $parent_menu,
            $main_menu_title,
            __( $main_menu_title, 'woocommerce-conditional-product-fees-for-checkout' ),
            'manage_options',
            'wcpfc-pro-list',
            array($this, 'wcpfc_pro_fee_list_page')
        );
        add_submenu_page(
            $parent_menu,
            'Get Started',
            'Get Started',
            'manage_options',
            'wcpfc-pro-get-started',
            array($this, 'wcpfc_pro_get_started_page')
        );
        add_submenu_page(
            $parent_menu,
            'Add New',
            'Add New',
            'manage_options',
            'wcpfc-pro-add-new',
            array($this, 'wcpfc_pro_add_new_fee_page')
        );
        add_submenu_page(
            $parent_menu,
            'Edit Fee',
            'Edit Fee',
            'manage_options',
            'wcpfc-pro-edit-fee',
            array($this, 'wcpfc_pro_edit_fee_page')
        );
        add_submenu_page(
            $parent_menu,
            'Import Export Fee',
            'Import Export Fee',
            'manage_options',
            'wcpfc-pro-import-export',
            array($this, 'wcpfc_pro_import_export_fee_page')
        );
        add_submenu_page(
            $parent_menu,
            'Dashboard',
            'Dashboard',
            'manage_options',
            'wcpfc-pro-dashboard',
            array($this, 'wcpfc_pro_dashboard_page')
        );
        add_submenu_page(
            $parent_menu,
            'Global Settings',
            'Global Settings',
            'manage_options',
            'wcpfc-global-settings',
            array($this, 'wcpfc_pro_global_settings_page')
        );
        if ( !(wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code()) ) {
            add_submenu_page(
                $parent_menu,
                'Get Premium',
                'Get Premium',
                'manage_options',
                'wcpfc-upgrade-dashboard',
                array($this, 'wcpfc_free_user_upgrade_page')
            );
        }
        // inlcude screen options
        add_action( "load-{$get_hook}", array($this, "wcpfc_screen_options") );
    }

    /**
     * Add custom css for dotstore icon in admin area
     *
     * @since  3.9.3
     *
     */
    public function wcpfc_dot_store_icon_css() {
        echo '<style>
	    .toplevel_page_dots_store .dashicons-marker::after{content:"";border:3px solid;position:absolute;top:14px;left:15px;border-radius:50%;opacity: 0.6;}
	    li.toplevel_page_dots_store:hover .dashicons-marker::after,li.toplevel_page_dots_store.current .dashicons-marker::after{opacity: 1;}
	    @media only screen and (max-width: 960px){
	    	.toplevel_page_dots_store .dashicons-marker::after{left:14px;}
	    }
	  	</style>';
    }

    /**
     * Register Admin fee list page output.
     *
     * @since    1.0.0
     */
    public function wcpfc_pro_fee_list_page() {
        require_once plugin_dir_path( __FILE__ ) . '/partials/wcpfc_pro_list-page.php';
        $wcpfc_rule_lising_obj = new WCPFC_Rule_Listing_Page();
        $wcpfc_rule_lising_obj->wcpfc_sj_output();
    }

    /**
     * Register Admin add new fee condition page output.
     *
     * @since    1.0.0
     */
    public function wcpfc_pro_add_new_fee_page() {
        require_once plugin_dir_path( __FILE__ ) . '/partials/wcpfc-pro-add-new-page.php';
    }

    /**
     * Register Admin edit fee condition page output.
     *
     * @since    1.0.0
     */
    public function wcpfc_pro_edit_fee_page() {
        require_once plugin_dir_path( __FILE__ ) . '/partials/wcpfc-pro-add-new-page.php';
    }

    /**
     * Register Admin get started page output.
     *
     */
    public function wcpfc_pro_get_started_page() {
        require_once plugin_dir_path( __FILE__ ) . '/partials/wcpfc-pro-get-started-page.php';
    }

    /**
     * Premium version info page
     *
     */
    public function wcpfc_free_user_upgrade_page() {
        require_once plugin_dir_path( __FILE__ ) . '/partials/dots-upgrade-dashboard.php';
    }

    /**
     * Import Export Setting page
     *
     */
    public function wcpfc_pro_import_export_fee_page() {
        require_once plugin_dir_path( __FILE__ ) . '/partials/wcpfc-import-export-setting.php';
    }

    /**
     * Dashboard page
     *
     */
    public function wcpfc_pro_dashboard_page() {
        require_once plugin_dir_path( __FILE__ ) . '/partials/wcpfc-dashboard-setting.php';
    }

    /**
     * Global settings page
     *
     */
    public function wcpfc_pro_global_settings_page() {
        require_once plugin_dir_path( __FILE__ ) . '/partials/wcpfc-global-settings-page.php';
    }

    /**
     * Screen option for fee listing page
     *
     * @since    3.9.3
     */
    public function wcpfc_screen_options() {
        $per_page = ( get_option( 'chk_fees_per_page' ) ? get_option( 'chk_fees_per_page' ) : 10 );
        $args = array(
            'label'   => esc_html( 'Number of fees per page', 'woocommerce-conditional-product-fees-for-checkout' ),
            'default' => $per_page,
            'option'  => 'chk_fees_per_page',
        );
        add_screen_option( 'per_page', $args );
        if ( !class_exists( 'WC_Conditional_product_Fees_Table' ) ) {
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/list-tables/class-wc-conditional-product-fees-table.php';
        }
        $list_table_obj = new WC_Conditional_product_Fees_Table();
        $list_table_obj->_column_headers = $list_table_obj->get_column_info();
    }

    /**
     * Specify the columns we wish to hide by default.
     *
     * @param array     $hidden Columns set to be hidden.
     * @param WP_Screen $screen Screen object.
     * @since 3.9.3
     * 
     * @return array
     */
    public function wcpfc_default_hidden_columns( $hidden, WP_Screen $screen ) {
        if ( false === $hidden && !empty( $screen->id ) && strpos( $screen->id, '_page_wcpfc-pro-list' ) !== false ) {
            settype( $hidden, 'array' );
            $hidden = array_merge( $hidden, array('date') );
        }
        return $hidden;
    }

    /**
     * Add screen option for per page
     *
     * @param bool   $status
     * @param string $option
     * @param int    $value
     *
     * @return int $value
     * @since 3.9.3
     *
     */
    public function wcpfc_set_screen_options( $status, $option, $value ) {
        $wcpfc_screens = array('chk_fees_per_page');
        if ( 'chk_fees_per_page' === $option ) {
            $value = ( !empty( $value ) && $value > 0 ? $value : 1 );
        }
        if ( in_array( $option, $wcpfc_screens, true ) ) {
            return $value;
        }
        return $status;
    }

    /**
     * It will display notification message
     *
     * @since 1.0.0
     */
    public function wcpfc_pro_notifications() {
        $page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS );
        $success = filter_input( INPUT_GET, 'success', FILTER_SANITIZE_SPECIAL_CHARS );
        $delete = filter_input( INPUT_GET, 'delete', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if ( isset( $page, $success ) && $page === ' wcpfc-pro-list' && $success === 'true' ) {
            ?>
			<div class="updated notice is-dismissible">
				<p><?php 
            esc_html_e( 'Fee rule has been successfully saved.', 'woocommerce-conditional-product-fees-for-checkout' );
            ?></p>
			</div>
			<?php 
        } else {
            if ( isset( $page, $delete ) && $page === 'wcpfc-pro-list' && $delete === 'true' ) {
                ?>
			<div class="updated notice is-dismissible">
				<p><?php 
                esc_html_e( 'Fee rule has been successfully deleted.', 'woocommerce-conditional-product-fees-for-checkout' );
                ?></p>
			</div>
			<?php 
            }
        }
    }

    /**
     * Display rule Like: country list, state list, zone list, city, postcode, product, category etc.
     *
     * @since 1.0.0
     */
    public function wcpfc_pro_product_fees_conditions_values_ajax() {
        $html = '';
        if ( check_ajax_referer( 'wcpfc_pro_product_fees_conditions_values_ajax_action', 'wcpfc_pro_product_fees_conditions_values_ajax' ) ) {
            $get_condition = filter_input( INPUT_GET, 'condition', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $get_count = filter_input( INPUT_GET, 'count', FILTER_SANITIZE_NUMBER_INT );
            $posts_per_page = filter_input( INPUT_GET, 'posts_per_page', FILTER_VALIDATE_INT );
            $offset = filter_input( INPUT_GET, 'offset', FILTER_VALIDATE_INT );
            $condition = ( isset( $get_condition ) ? sanitize_text_field( $get_condition ) : '' );
            $count = ( isset( $get_count ) ? sanitize_text_field( $get_count ) : '' );
            $posts_per_page = ( isset( $posts_per_page ) ? sanitize_text_field( $posts_per_page ) : '' );
            $offset = ( isset( $offset ) ? sanitize_text_field( $offset ) : '' );
            $html = '';
            if ( 'country' === $condition ) {
                $html .= wp_json_encode( $this->wcpfc_pro_get_country_list( $count, [], true ) );
            } elseif ( 'city' === $condition ) {
                $html .= 'textarea';
            } elseif ( 'product' === $condition ) {
                $html .= wp_json_encode( $this->wcpfc_pro_get_product_list(
                    $count,
                    [],
                    '',
                    true
                ) );
            } elseif ( 'variableproduct' === $condition ) {
                $html .= wp_json_encode( $this->wcpfc_pro_get_varible_product_list(
                    $count,
                    [],
                    '',
                    true
                ) );
            } elseif ( 'category' === $condition ) {
                $html .= wp_json_encode( $this->wcpfc_pro_get_category_list( $count, [], true ) );
            } elseif ( 'tag' === $condition ) {
                $html .= wp_json_encode( $this->wcpfc_pro_get_tag_list( $count, [], true ) );
            } elseif ( 'product_qty' === $condition ) {
                $html .= 'input';
            } elseif ( 'user' === $condition ) {
                $html .= wp_json_encode( $this->wcpfc_pro_get_user_list(
                    $count,
                    [],
                    '',
                    true
                ) );
            } elseif ( 'cart_total' === $condition ) {
                $html .= 'input';
            } elseif ( 'quantity' === $condition ) {
                $html .= 'input';
            }
        }
        echo wp_kses( $html, Woocommerce_Conditional_Product_Fees_For_Checkout_Pro::allowed_html_tags() );
        wp_die();
        // this is required to terminate immediately and return a proper response
    }

    /**
     * Function for select country list
     *
     * @param string $count
     * @param array  $selected
     * @param bool   $json
     *
     * @return string or array $html
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_get_country_list( $count = '', $selected = array(), $json = false ) {
        $countries_obj = new WC_Countries();
        $getCountries = $countries_obj->__get( 'countries' );
        if ( $json ) {
            return $this->wcpfc_pro_convert_array_to_json( $getCountries );
        }
        $html = '<select name="fees[product_fees_conditions_values][value_' . esc_attr( $count ) . '][]" class="wcpfc_select product_fees_conditions_values multiselect2 product_fees_conditions_values_country multiselect2_country" multiple="multiple">';
        if ( isset( $getCountries ) && !empty( $getCountries ) ) {
            foreach ( $getCountries as $code => $country ) {
                $selectedVal = ( is_array( $selected ) && !empty( $selected ) && in_array( $code, $selected, true ) ? 'selected=selected' : '' );
                $html .= '<option value="' . esc_attr( $code ) . '" ' . esc_attr( $selectedVal ) . '>' . esc_html( $country ) . '</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Function for select category list
     *
     * @param string $count
     * @param array  $selected
     * @param string $action
     * @param bool   $json
     *
     * @return string or array $html
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_get_product_list(
        $count = '',
        $selected = array(),
        $action = '',
        $json = false
    ) {
        global $sitepress;
        $default_lang = $this->wcpfc_pro_get_default_langugae_with_sitpress();
        $post_in = '';
        if ( 'edit' === $action ) {
            $post_in = $selected;
            $posts_per_page = -1;
        } else {
            $post_in = '';
            $posts_per_page = 10;
        }
        $product_args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'post__in'       => $post_in,
            'posts_per_page' => $posts_per_page,
        );
        $get_all_products = new WP_Query($product_args);
        $html = '<select id="product-filter-' . esc_attr( $count ) . '" rel-id="' . esc_attr( $count ) . '" name="fees[product_fees_conditions_values][value_' . esc_attr( $count ) . '][]" class="wcpfc_select product_fees_conditions_values multiselect2 product_fees_conditions_values_product" multiple="multiple">';
        if ( isset( $get_all_products->posts ) && !empty( $get_all_products->posts ) ) {
            foreach ( $get_all_products->posts as $get_all_product ) {
                $_product = wc_get_product( $get_all_product->ID );
                if ( $_product->is_type( 'simple' ) ) {
                    if ( !empty( $sitepress ) ) {
                        $new_product_id = apply_filters(
                            'wpml_object_id',
                            $get_all_product->ID,
                            'product',
                            true,
                            $default_lang
                        );
                    } else {
                        $new_product_id = $get_all_product->ID;
                    }
                    $selected = array_map( 'intval', $selected );
                    $selectedVal = ( is_array( $selected ) && !empty( $selected ) && in_array( $new_product_id, $selected, true ) ? 'selected=selected' : '' );
                    if ( $selectedVal !== '' ) {
                        $html .= '<option value="' . esc_attr( $new_product_id ) . '" ' . esc_attr( $selectedVal ) . '>' . '#' . esc_html( $new_product_id ) . ' - ' . esc_html( get_the_title( $new_product_id ) ) . '</option>';
                    }
                }
            }
        }
        $html .= '</select>';
        if ( $json ) {
            return [];
        }
        return $html;
    }

    /**
     * Function for select product variable list
     *
     * @param string $count
     * @param array  $selected
     * @param string $action
     * @param bool   $json
     *
     * @return string or array $html
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_get_varible_product_list(
        $count = '',
        $selected = array(),
        $action = '',
        $json = false
    ) {
        global $sitepress;
        $default_lang = $this->wcpfc_pro_get_default_langugae_with_sitpress();
        if ( 'edit' === $action ) {
            $post_in = $selected;
            $get_varible_product_list_count = -1;
        } else {
            $post_in = '';
            $get_varible_product_list_count = 10;
        }
        $product_args = array(
            'post_type'      => 'product_variation',
            'post_status'    => 'publish',
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'posts_per_page' => $get_varible_product_list_count,
            'post__in'       => $post_in,
        );
        $get_all_products = new WP_Query($product_args);
        $html = '<select id="var-product-filter-' . esc_attr( $count ) . '" rel-id="' . esc_attr( $count ) . '" name="fees[product_fees_conditions_values][value_' . esc_attr( $count ) . '][]" class="wcpfc_select product_fees_conditions_values multiselect2 product_fees_conditions_values_var_product" multiple="multiple">';
        if ( isset( $get_all_products->posts ) && !empty( $get_all_products->posts ) ) {
            foreach ( $get_all_products->posts as $get_all_product ) {
                if ( !empty( $sitepress ) ) {
                    $new_product_id = apply_filters(
                        'wpml_object_id',
                        $get_all_product->ID,
                        'product',
                        true,
                        $default_lang
                    );
                } else {
                    $new_product_id = $get_all_product->ID;
                }
                $selected = array_map( 'intval', $selected );
                $selectedVal = ( is_array( $selected ) && !empty( $selected ) && in_array( $new_product_id, $selected, true ) ? 'selected=selected' : '' );
                if ( '' !== $selectedVal ) {
                    $html .= '<option value="' . esc_attr( $new_product_id ) . '" ' . esc_attr( $selectedVal ) . '>' . '#' . esc_html( $new_product_id ) . ' - ' . esc_html( get_the_title( $new_product_id ) ) . '</option>';
                }
            }
        }
        $html .= '</select>';
        if ( $json ) {
            return [];
        }
        return $html;
    }

    /**
     * Function for select cat list
     *
     * @param string $count
     * @param array  $selected
     * @param bool   $json
     *
     * @return string or array $html
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_get_category_list( $count = '', $selected = array(), $json = false ) {
        global $sitepress;
        $default_lang = $this->wcpfc_pro_get_default_langugae_with_sitpress();
        $filter_categories = [];
        $args = array(
            'taxonomy'     => 'product_cat',
            'orderby'      => 'name',
            'hierarchical' => true,
            'hide_empty'   => false,
        );
        $get_all_categories = get_terms( 'product_cat', $args );
        $html = '<select rel-id="' . esc_attr( $count ) . '" name="fees[product_fees_conditions_values][value_' . esc_attr( $count ) . '][]" class="wcpfc_select product_fees_conditions_values multiselect2" multiple="multiple">';
        if ( isset( $get_all_categories ) && !empty( $get_all_categories ) ) {
            foreach ( $get_all_categories as $get_all_category ) {
                if ( $get_all_category ) {
                    if ( !empty( $sitepress ) ) {
                        $new_cat_id = apply_filters(
                            'wpml_object_id',
                            $get_all_category->term_id,
                            'product_cat',
                            true,
                            $default_lang
                        );
                    } else {
                        $new_cat_id = $get_all_category->term_id;
                    }
                    $selected = array_map( 'intval', $selected );
                    $selectedVal = ( is_array( $selected ) && !empty( $selected ) && in_array( $new_cat_id, $selected, true ) ? 'selected=selected' : '' );
                    $category = get_term_by( 'id', $new_cat_id, 'product_cat' );
                    $parent_category = get_term_by( 'id', $category->parent, 'product_cat' );
                    if ( $category->parent > 0 ) {
                        $html .= '<option value=' . esc_attr( $category->term_id ) . ' ' . esc_attr( $selectedVal ) . '>' . '#' . esc_html( $parent_category->name ) . '->' . esc_html( $category->name ) . '</option>';
                        $filter_categories[$category->term_id] = '#' . $parent_category->name . '->' . $category->name;
                    } else {
                        $html .= '<option value=' . esc_attr( $category->term_id ) . ' ' . esc_attr( $selectedVal ) . '>' . esc_html( $category->name ) . '</option>';
                        $filter_categories[$category->term_id] = $category->name;
                    }
                }
            }
        }
        $html .= '</select>';
        if ( $json ) {
            return $this->wcpfc_pro_convert_array_to_json( $filter_categories );
        }
        return $html;
    }

    /**
     * Function for select tag list
     *
     * @param string $count
     * @param array  $selected
     * @param bool   $json
     *
     * @return string or array $html
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_get_tag_list( $count = '', $selected = array(), $json = false ) {
        global $sitepress;
        $default_lang = $this->wcpfc_pro_get_default_langugae_with_sitpress();
        $filter_tags = [];
        $args = array(
            'taxonomy'     => 'product_cat',
            'orderby'      => 'name',
            'hierarchical' => true,
            'hide_empty'   => false,
        );
        $get_all_tags = get_terms( 'product_tag', $args );
        $html = '<select rel-id="' . esc_attr( $count ) . '" name="fees[product_fees_conditions_values][value_' . esc_attr( $count ) . '][]" class="wcpfc_select product_fees_conditions_values multiselect2" multiple="multiple">';
        if ( isset( $get_all_tags ) && !empty( $get_all_tags ) ) {
            foreach ( $get_all_tags as $get_all_tag ) {
                if ( $get_all_tag ) {
                    if ( !empty( $sitepress ) ) {
                        $new_tag_id = apply_filters(
                            'wpml_object_id',
                            $get_all_tag->term_id,
                            'product_tag',
                            true,
                            $default_lang
                        );
                    } else {
                        $new_tag_id = $get_all_tag->term_id;
                    }
                    $selected = array_map( 'intval', $selected );
                    $selectedVal = ( is_array( $selected ) && !empty( $selected ) && in_array( $new_tag_id, $selected, true ) ? 'selected=selected' : '' );
                    $tag = get_term_by( 'id', $new_tag_id, 'product_tag' );
                    $html .= '<option value="' . esc_attr( $tag->term_id ) . '" ' . esc_attr( $selectedVal ) . '>' . esc_html( $tag->name ) . '</option>';
                    $filter_tags[$tag->term_id] = $tag->name;
                }
            }
        }
        $html .= '</select>';
        if ( $json ) {
            return $this->wcpfc_pro_convert_array_to_json( $filter_tags );
        }
        return $html;
    }

    /**
     * When create fees based on user then all users will display using ajax
     *
     * @since 3.9.3
     *
     */
    public function wcpfc_pro_product_fees_conditions_values_user_ajax() {
        // Security check
        check_ajax_referer( 'select2_data_nonce', 'security' );
        // Get users
        $json = true;
        $filter_user_list = [];
        $request_value = filter_input( INPUT_GET, 'value', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $posts_per_page = filter_input( INPUT_GET, 'posts_per_page', FILTER_SANITIZE_NUMBER_INT );
        $_page = filter_input( INPUT_GET, '_page', FILTER_SANITIZE_NUMBER_INT );
        $post_value = ( isset( $request_value ) ? sanitize_text_field( $request_value ) : '' );
        $users_args = array(
            'number'         => $posts_per_page,
            'offset'         => ($_page - 1) * $posts_per_page,
            'search'         => '*' . $post_value . '*',
            'search_columns' => array('user_login'),
            'orderby'        => 'user_login',
            'order'          => 'ASC',
        );
        $get_all_users = get_users( $users_args );
        $html = '';
        if ( isset( $get_all_users ) && !empty( $get_all_users ) ) {
            foreach ( $get_all_users as $get_all_user ) {
                $html .= '<option value="' . esc_attr( $get_all_user->data->ID ) . '">' . esc_html( $get_all_user->data->user_login ) . '</option>';
                $filter_user_list[] = array($get_all_user->data->ID, $get_all_user->data->user_login);
            }
        }
        if ( $json ) {
            echo wp_json_encode( $filter_user_list );
            wp_die();
        }
        echo wp_kses( $html, Woocommerce_Conditional_Product_Fees_For_Checkout_Pro::allowed_html_tags() );
        wp_die();
    }

    /**
     * Function for select user list
     *
     * @param string $count
     * @param array  $selected
     * @param bool   $json
     *
     * @return string or array $html
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_get_user_list(
        $count = '',
        $selected = array(),
        $action = '',
        $json = false
    ) {
        $filter_users = [];
        $user_in = '';
        if ( 'edit' === $action ) {
            $user_in = $selected;
            $posts_per_page = -1;
        } else {
            $user_in = '';
            $posts_per_page = 10;
        }
        $get_users = array(
            'include' => $user_in,
            'number'  => $posts_per_page,
        );
        $get_all_users = get_users( $get_users );
        $html = '<select rel-id="' . esc_attr( $count ) . '" name="fees[product_fees_conditions_values][value_' . esc_attr( $count ) . '][]" class="wcpfc_select product_fees_conditions_values multiselect2 product_fees_conditions_values_user" multiple="multiple">';
        if ( isset( $get_all_users ) && !empty( $get_all_users ) ) {
            foreach ( $get_all_users as $get_all_user ) {
                $selected = array_map( 'intval', $selected );
                $selectedVal = ( is_array( $selected ) && !empty( $selected ) && in_array( (int) $get_all_user->data->ID, $selected, true ) ? 'selected=selected' : '' );
                $html .= '<option value="' . esc_attr( $get_all_user->data->ID ) . '" ' . esc_attr( $selectedVal ) . '>' . esc_html( $get_all_user->data->user_login ) . '</option>';
                $filter_users[$get_all_user->data->ID] = $get_all_user->data->user_login;
            }
        }
        $html .= '</select>';
        if ( $json ) {
            return $this->wcpfc_pro_convert_array_to_json( $filter_users );
        }
        return $html;
    }

    /**
     * Function for multiple delete fees
     *
     * @since 1.0.0
     */
    public function wcpfc_reset_fee_cache() {
        check_ajax_referer( 'dsm_nonce', 'nonce' );
        $html = esc_html__( 'Somethng went wrong!', 'woocommerce-conditional-product-fees-for-checkout' );
        delete_transient( 'get_all_fees' );
        if ( delete_transient( 'get_top_ten_fees' ) && delete_transient( 'get_all_dashboard_fees' ) && delete_transient( 'get_total_revenue' ) && delete_transient( 'get_total_yearly_revenue' ) && delete_transient( 'get_total_last_month_revenue' ) && delete_transient( 'get_total_this_month_revenue' ) && delete_transient( 'get_total_yesterday_revenue' ) && delete_transient( 'get_total_today_revenue' ) ) {
            $html = esc_html__( 'Fees data has been updated successfully.', 'woocommerce-conditional-product-fees-for-checkout' );
        }
        echo esc_html( $html );
        wp_die();
    }

    /**
     * Function for reset transient after fee delete
     *
     * @since 3.7.0
     */
    public function wcpfc_clear_fee_cache( $post_id ) {
        if ( self::wcpfc_post_type === get_post_type( $post_id ) ) {
            delete_transient( 'get_top_ten_fees' );
            delete_transient( 'get_all_fees' );
            delete_transient( 'get_all_dashboard_fees' );
        }
    }

    /**
     * Function for date wise fee with revenue
     *
     * @since 3.7.0
     */
    public function wcpfc_get_fee_data_from_date_range( $start_date, $end_date, $all = '' ) {
        $default_lang = $this->wcpfc_pro_get_default_langugae_with_sitpress();
        if ( '' === $all && (empty( $start_date ) || empty( $end_date )) ) {
            return 0;
        }
        global $sitepress;
        $filter_arr = array(
            "limit"   => -1,
            "orderby" => "date",
            "return"  => "ids",
            'status'  => array('wc-processing', 'wc-completed'),
        );
        if ( empty( $all ) ) {
            $filter_arr["date_created"] = $start_date . "..." . $end_date;
        }
        $orders = wc_get_orders( $filter_arr );
        $fee_array = array();
        if ( isset( $orders ) && !empty( $orders ) ) {
            foreach ( $orders as $order_id ) {
                $order = wc_get_order( $order_id );
                $order_fees = $order->get_meta( '_wcpfc_fee_summary' );
                if ( !empty( $order_fees ) ) {
                    foreach ( $order_fees as $order_fee ) {
                        $fee_revenue = 0;
                        if ( !empty( $sitepress ) ) {
                            $fee_id = apply_filters(
                                'wpml_object_id',
                                $order_fee->id,
                                'product',
                                true,
                                $default_lang
                            );
                        } else {
                            $fee_id = $order_fee->id;
                        }
                        $fee_obj = get_page_by_path( $fee_id, OBJECT, 'wc_conditional_fee' );
                        // phpcs:ignore
                        if ( !empty( $fee_obj ) && isset( $fee_obj->ID ) && $fee_obj->ID > 0 ) {
                            $fee_id = $fee_obj->ID;
                        }
                        $fee_id = ( !empty( $fee_id ) ? $fee_id : 0 );
                        if ( $fee_id > 0 ) {
                            $fee_amount = ( !empty( $order_fee->total ) ? $order_fee->total : 0 );
                            if ( !empty( $order_fee->taxable ) && $order_fee->taxable ) {
                                $fee_amount += ( $order_fee->tax > 0 ? $order_fee->tax : 0 );
                            }
                            $fee_revenue += $fee_amount;
                            if ( $fee_revenue > 0 && array_key_exists( $fee_id, $fee_array ) ) {
                                $fee_array[$fee_id] += $fee_revenue;
                            } else {
                                $fee_array[$fee_id] = $fee_revenue;
                            }
                        }
                    }
                } else {
                    if ( !empty( $order->get_fees() ) ) {
                        foreach ( $order->get_fees() as $fee_id => $fee ) {
                            $fee_revenue = 0;
                            // Query to fetch fees ids by name
                            $args = array(
                                'post_type'      => 'wc_conditional_fee',
                                'post_status'    => 'publish',
                                'posts_per_page' => 1,
                                'fields'         => 'ids',
                                'title'          => $fee['name'],
                            );
                            $query = new WP_Query($args);
                            $fee_post = '';
                            if ( $query->have_posts() ) {
                                $fee_post = $query->posts[0];
                            }
                            wp_reset_postdata();
                            $fee_id = ( !empty( $fee_post ) ? $fee_post : 0 );
                            if ( !empty( $sitepress ) ) {
                                $fee_id = apply_filters(
                                    'wpml_object_id',
                                    $fee_id,
                                    'product',
                                    true,
                                    $default_lang
                                );
                            }
                            //$fee_id 0 will consider as other custom fees.
                            if ( $fee['line_total'] > 0 ) {
                                $fee_revenue += $fee['line_total'];
                            }
                            if ( $fee['line_tax'] > 0 ) {
                                $fee_revenue += $fee['line_tax'];
                            }
                            if ( $fee_revenue >= 0 && array_key_exists( $fee_id, $fee_array ) ) {
                                $fee_array[$fee_id] += $fee_revenue;
                            } else {
                                $fee_array[$fee_id] = $fee_revenue;
                            }
                        }
                    }
                }
            }
        }
        return $fee_array;
    }

    /**
     * Function color generator in RGB from random number
     *
     * @since 3.7.0
     */
    public function wcpfc_color_generator( $num = 10 ) {
        $hash = md5( 'color' . $num );
        // modify 'color' to get a different palette
        return 'rgb(' . hexdec( substr( $hash, 0, 2 ) ) . ', ' . hexdec( substr( $hash, 2, 2 ) ) . ', ' . hexdec( substr( $hash, 4, 2 ) ) . ')';
    }

    /**
     * Redirect page after plugin activation
     *
     * @uses  wcpfc_pro_register_post_type
     *
     * @since 1.0.0
     */
    public function wcpfc_pro_welcome_conditional_fee_screen_do_activation_redirect() {
        $this->wcpfc_pro_register_post_type();
        // if no activation redirect
        if ( !get_transient( '_welcome_screen_wcpfc_pro_mode_activation_redirect_data' ) ) {
            return;
        }
        // Delete the redirect transient
        delete_transient( '_welcome_screen_wcpfc_pro_mode_activation_redirect_data' );
        // if activating from network, or bulk
        $activate_multi = filter_input( INPUT_GET, 'activate-multi', FILTER_SANITIZE_SPECIAL_CHARS );
        if ( is_network_admin() || isset( $activate_multi ) ) {
            return;
        }
        // Redirect to extra cost welcome  page
        wp_safe_redirect( add_query_arg( array(
            'page' => 'wcpfc-pro-list',
        ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Register post type
     *
     * @since    1.0.0
     */
    public function wcpfc_pro_register_post_type() {
        register_post_type( self::wcpfc_post_type, array(
            'labels' => array(
                'name'          => __( 'Advance Conditional Fees', 'woocommerce-conditional-product-fees-for-checkout' ),
                'singular_name' => __( 'Advance Conditional Fees', 'woocommerce-conditional-product-fees-for-checkout' ),
            ),
        ) );
    }

    /**
     * Remove submenu from admin section
     *
     * @since 1.0.0
     */
    public function wcpfc_pro_remove_admin_submenus() {
        $chk_move_menu_under_wc = get_option( 'chk_move_menu_under_wc' );
        $parent_menu = 'dots_store';
        if ( 'on' === $chk_move_menu_under_wc ) {
            $parent_menu = 'woocommerce';
        } else {
            remove_submenu_page( $parent_menu, $parent_menu );
        }
        remove_submenu_page( $parent_menu, 'wcpfc-pro-add-new' );
        remove_submenu_page( $parent_menu, 'wcpfc-pro-edit-fee' );
        remove_submenu_page( $parent_menu, 'wcpfc-pro-get-started' );
        remove_submenu_page( $parent_menu, 'wcpfc-pro-dashboard' );
        remove_submenu_page( $parent_menu, 'wcpfc-pro-import-export' );
        remove_submenu_page( $parent_menu, 'wcpfc-global-settings' );
        if ( !(wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code()) ) {
            remove_submenu_page( $parent_menu, 'wcpfc-upgrade-dashboard' );
        }
    }

    /**
     * When create fees based on product then all product will display using ajax
     *
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_product_fees_conditions_values_product_ajax() {
        // Security check
        check_ajax_referer( 'select2_data_nonce', 'security' );
        // Get products
        global $sitepress;
        $default_lang = $this->wcpfc_pro_get_default_langugae_with_sitpress();
        $json = true;
        $filter_product_list = [];
        $request_value = filter_input( INPUT_GET, 'value', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $posts_per_page = filter_input( INPUT_GET, 'posts_per_page', FILTER_SANITIZE_NUMBER_INT );
        $_page = filter_input( INPUT_GET, '_page', FILTER_SANITIZE_NUMBER_INT );
        $post_value = ( isset( $request_value ) ? sanitize_text_field( $request_value ) : '' );
        $baselang_product_ids = array();
        function wcpfc_posts_where(  $where, $wp_query  ) {
            global $wpdb;
            $search_term = $wp_query->get( 'search_pro_title' );
            if ( isset( $search_term ) ) {
                $search_term_like = $wpdb->esc_like( $search_term );
                $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $search_term_like ) . '%\'';
            }
            return $where;
        }

        $product_args = array(
            'post_type'        => 'product',
            'posts_per_page'   => $posts_per_page,
            'offset'           => ($_page - 1) * $posts_per_page,
            'search_pro_title' => $post_value,
            'post_status'      => array('publish', 'private'),
            'orderby'          => 'title',
            'order'            => 'ASC',
        );
        add_filter(
            'posts_where',
            'wcpfc_posts_where',
            10,
            2
        );
        $wp_query = new WP_Query($product_args);
        remove_filter(
            'posts_where',
            'wcpfc_posts_where',
            10,
            2
        );
        $get_all_products = $wp_query->posts;
        if ( isset( $get_all_products ) && !empty( $get_all_products ) ) {
            foreach ( $get_all_products as $get_all_product ) {
                $_product = wc_get_product( $get_all_product->ID );
                if ( $_product->is_type( 'simple' ) ) {
                    if ( !empty( $sitepress ) ) {
                        $defaultlang_product_id = apply_filters(
                            'wpml_object_id',
                            $get_all_product->ID,
                            'product',
                            true,
                            $default_lang
                        );
                    } else {
                        $defaultlang_product_id = $get_all_product->ID;
                    }
                    $baselang_product_ids[] = $defaultlang_product_id;
                }
            }
        }
        $html = '';
        if ( isset( $baselang_product_ids ) && !empty( $baselang_product_ids ) ) {
            foreach ( $baselang_product_ids as $baselang_product_id ) {
                $html .= '<option value="' . $baselang_product_id . '">' . '#' . $baselang_product_id . ' - ' . get_the_title( $baselang_product_id ) . '</option>';
                $filter_product_list[] = array($baselang_product_id, '#' . $baselang_product_id . ' - ' . get_the_title( $baselang_product_id ));
            }
        }
        if ( $json ) {
            echo wp_json_encode( $filter_product_list );
            wp_die();
        }
        echo wp_kses( $html, Woocommerce_Conditional_Product_Fees_For_Checkout_Pro::allowed_html_tags() );
        wp_die();
    }

    /**
     * When create fees based on advance pricing rule and add rule based onm product qty then all
     * product will display using ajax
     *
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_simple_and_variation_product_list_ajax() {
        // Security check
        check_ajax_referer( 'select2_data_nonce', 'security' );
        // Get products
        global $sitepress;
        $default_lang = $this->wcpfc_pro_get_default_langugae_with_sitpress();
        $json = true;
        $filter_product_list = [];
        $request_value = filter_input( INPUT_GET, 'value', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $post_value = ( isset( $request_value ) ? sanitize_text_field( $request_value ) : '' );
        $baselang_simple_product_ids = array();
        $baselang_variation_product_ids = array();
        function wcpfc_posts_where(  $where, $wp_query  ) {
            global $wpdb;
            $search_term = $wp_query->get( 'search_pro_title' );
            if ( !empty( $search_term ) ) {
                $search_term_like = $wpdb->esc_like( $search_term );
                $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $search_term_like ) . '%\'';
            }
            return $where;
        }

        $product_args = array(
            'post_type'        => 'product',
            'posts_per_page'   => -1,
            'search_pro_title' => $post_value,
            'post_status'      => 'publish',
            'orderby'          => 'title',
            'order'            => 'ASC',
        );
        add_filter(
            'posts_where',
            'wcpfc_posts_where',
            10,
            2
        );
        $get_wp_query = new WP_Query($product_args);
        remove_filter(
            'posts_where',
            'wcpfc_posts_where',
            10,
            2
        );
        $get_all_products = $get_wp_query->posts;
        if ( isset( $get_all_products ) && !empty( $get_all_products ) ) {
            foreach ( $get_all_products as $get_all_product ) {
                $_product = wc_get_product( $get_all_product->ID );
                if ( $_product->is_type( 'variable' ) ) {
                    $variations = $_product->get_available_variations();
                    foreach ( $variations as $value ) {
                        if ( !empty( $sitepress ) ) {
                            $defaultlang_variation_product_id = apply_filters(
                                'wpml_object_id',
                                $value['variation_id'],
                                'product',
                                true,
                                $default_lang
                            );
                        } else {
                            $defaultlang_variation_product_id = $value['variation_id'];
                        }
                        $baselang_variation_product_ids[] = $defaultlang_variation_product_id;
                    }
                }
                if ( $_product->is_type( 'simple' ) ) {
                    if ( !empty( $sitepress ) ) {
                        $defaultlang_simple_product_id = apply_filters(
                            'wpml_object_id',
                            $get_all_product->ID,
                            'product',
                            true,
                            $default_lang
                        );
                    } else {
                        $defaultlang_simple_product_id = $get_all_product->ID;
                    }
                    $baselang_simple_product_ids[] = $defaultlang_simple_product_id;
                }
            }
        }
        $baselang_product_ids = array_merge( $baselang_variation_product_ids, $baselang_simple_product_ids );
        $html = '';
        if ( isset( $baselang_product_ids ) && !empty( $baselang_product_ids ) ) {
            foreach ( $baselang_product_ids as $baselang_product_id ) {
                $html .= '<option value="' . $baselang_product_id . '">' . '#' . $baselang_product_id . ' - ' . get_the_title( $baselang_product_id ) . '</option>';
                $filter_product_list[] = array($baselang_product_id, '#' . $baselang_product_id . ' - ' . get_the_title( $baselang_product_id ));
            }
        }
        if ( $json ) {
            echo wp_json_encode( $filter_product_list );
            wp_die();
        }
        echo wp_kses( $html, Woocommerce_Conditional_Product_Fees_For_Checkout_Pro::allowed_html_tags() );
        wp_die();
    }

    /**
     * Sorting fess in list section
     *
     * @since 1.0.0
     */
    public function wcpfc_pro_conditional_fee_sorting() {
        check_ajax_referer( 'sorting_conditional_fee_action', 'sorting_conditional_fee' );
        global $wpdb;
        $post_type = self::wcpfc_post_type;
        $getListingArray = filter_input(
            INPUT_POST,
            'listing',
            FILTER_SANITIZE_NUMBER_INT,
            FILTER_REQUIRE_ARRAY
        );
        $listingArray = ( !empty( $getListingArray ) ? array_map( 'intval', wp_unslash( $getListingArray ) ) : array() );
        $results = new WP_Query(array(
            'post_type'      => $post_type,
            'post_status'    => array('publish', 'draft'),
            'fields'         => 'ids',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'posts_per_page' => -1,
        ));
        // Original post IDs
        $objects_ids = array();
        if ( isset( $results->posts ) && !empty( $results->posts ) ) {
            foreach ( $results->posts as $result ) {
                $objects_ids[] = (int) $result;
            }
        }
        // Let's directly replace the content of $objects_ids with the updated order from $listingArray
        if ( !empty( $listingArray ) ) {
            // Ensure that the number of items match between $listingArray and $objects_ids
            if ( count( $listingArray ) === count( $objects_ids ) ) {
                $objects_ids = $listingArray;
                // Replace the entire $objects_ids array with the new order
            } else {
                // If they don't match, only update up to the matching count to avoid index issues
                for ($i = 0; $i < min( count( $listingArray ), count( $objects_ids ) ); $i++) {
                    $objects_ids[$i] = $listingArray[$i];
                    // Update each item in $objects_ids
                }
            }
        }
        // Update the menu_order within the database using $wpdb directly
        if ( isset( $objects_ids ) && !empty( $objects_ids ) ) {
            foreach ( $objects_ids as $menu_order => $id ) {
                $wpdb->update(
                    //phpcs:ignore
                    $wpdb->posts,
                    array(
                        'menu_order' => (int) $menu_order,
                    ),
                    // Set the new menu_order
                    array(
                        'ID' => (int) $id,
                    ),
                    // Where ID matches
                    array('%d'),
                    // Format for the menu_order
                    array('%d')
                );
                // Clean cache
                clean_post_cache( $id );
            }
        }
        wp_send_json_success( array(
            'message' => esc_html__( 'Order of fee rules has been updated successfully.', 'woocommerce-conditional-product-fees-for-checkout' ),
        ) );
    }

    /**
     * Ajax response of product wc product variable
     *
     * @since 1.0.0
     */
    public function wcpfc_pro_product_fees_conditions_varible_values_product_ajax() {
        // Security check
        check_ajax_referer( 'select2_data_nonce', 'security' );
        // Get products
        global $sitepress;
        $default_lang = $this->wcpfc_pro_get_default_langugae_with_sitpress();
        $json = true;
        $filter_variable_product_list = [];
        $request_value = filter_input( INPUT_GET, 'value', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $_page = filter_input( INPUT_GET, '_page', FILTER_SANITIZE_NUMBER_INT );
        $posts_per_page = filter_input( INPUT_GET, 'posts_per_page', FILTER_SANITIZE_NUMBER_INT );
        $post_value = ( isset( $request_value ) ? sanitize_text_field( $request_value ) : '' );
        $baselang_product_ids = array();
        function wcpfc_posts_wheres(  $where, $wp_query  ) {
            global $wpdb;
            $search_term = $wp_query->get( 'search_pro_title' );
            if ( isset( $search_term ) ) {
                $search_term_like = $wpdb->esc_like( $search_term );
                $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $search_term_like ) . '%\'';
            }
            return $where;
        }

        $product_args = array(
            'post_type'        => 'product_variation',
            'posts_per_page'   => $posts_per_page,
            'offset'           => ($_page - 1) * $posts_per_page,
            'search_pro_title' => $post_value,
            'post_status'      => array('publish', 'private'),
            'orderby'          => 'title',
            'order'            => 'ASC',
        );
        add_filter(
            'posts_where',
            'wcpfc_posts_wheres',
            10,
            2
        );
        $get_all_products = new WP_Query($product_args);
        remove_filter(
            'posts_where',
            'wcpfc_posts_wheres',
            10,
            2
        );
        if ( isset( $get_all_products ) && !empty( $get_all_products ) ) {
            foreach ( $get_all_products->posts as $get_all_product ) {
                if ( !empty( $sitepress ) ) {
                    $defaultlang_product_id = apply_filters(
                        'wpml_object_id',
                        $get_all_product->ID,
                        'product',
                        true,
                        $default_lang
                    );
                } else {
                    $defaultlang_product_id = $get_all_product->ID;
                }
                $baselang_product_ids[] = $defaultlang_product_id;
            }
        }
        $html = '';
        if ( isset( $baselang_product_ids ) && !empty( $baselang_product_ids ) ) {
            foreach ( $baselang_product_ids as $baselang_product_id ) {
                $html .= '<option value="' . $baselang_product_id . '">' . '#' . $baselang_product_id . ' - ' . get_the_title( $baselang_product_id ) . '</option>';
                $filter_variable_product_list[] = array($baselang_product_id, '#' . $baselang_product_id . ' - ' . get_the_title( $baselang_product_id ));
            }
        }
        if ( $json ) {
            echo wp_json_encode( $filter_variable_product_list );
            wp_die();
        }
        echo wp_kses( $html, Woocommerce_Conditional_Product_Fees_For_Checkout_Pro::allowed_html_tags() );
        wp_die();
    }

    /**
     * Admin footer review
     *
     * @since 1.0.0
     */
    public function wcpfc_pro_admin_footer_review() {
        $url = '';
        $url = esc_url( 'https://wordpress.org/plugins/woo-conditional-product-fees-for-checkout/#reviews' );
        $html = sprintf( wp_kses( __( '<strong>We need your support</strong> to keep updating and improving the plugin. Please <a href="%1$s" target="_blank">help us by leaving a good review</a> :) Thanks!', 'woocommerce-conditional-product-fees-for-checkout' ), array(
            'strong' => array(),
            'a'      => array(
                'href'   => array(),
                'target' => 'blank',
            ),
        ) ), esc_url( $url ) );
        echo wp_kses_post( $html );
    }

    /**
     * Convert array to json
     *
     * @param array $arr
     *
     * @return array $filter_data
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_convert_array_to_json( $arr ) {
        $filter_data = [];
        foreach ( $arr as $key => $value ) {
            $option = [];
            $option['name'] = $value;
            $option['attributes']['value'] = $key;
            $filter_data[] = $option;
        }
        return $filter_data;
    }

    /**
     * Get product list in advance pricing rules section
     *
     * @param string $count
     * @param array  $selected
     *
     * @return mixed $html
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_get_product_options( $count = '', $selected = array() ) {
        global $sitepress;
        $default_lang = $this->wcpfc_pro_get_default_langugae_with_sitpress();
        $all_selected_product_ids = array();
        if ( !empty( $selected ) && is_array( $selected ) ) {
            foreach ( $selected as $product_id ) {
                $_product = wc_get_product( $product_id );
                if ( 'product_variation' === $_product->post_type ) {
                    $all_selected_product_ids[] = $_product->get_parent_id();
                    //parent_id;
                } else {
                    $all_selected_product_ids[] = $product_id;
                }
            }
        }
        $all_selected_product_count = 900;
        $get_all_products = new WP_Query(array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $all_selected_product_count,
            'post__in'       => $all_selected_product_ids,
        ));
        $baselang_variation_product_ids = array();
        $defaultlang_simple_product_ids = array();
        $html = '';
        if ( isset( $get_all_products->posts ) && !empty( $get_all_products->posts ) ) {
            foreach ( $get_all_products->posts as $get_all_product ) {
                $_product = wc_get_product( $get_all_product->ID );
                if ( $_product->is_type( 'variable' ) ) {
                    $variations = $_product->get_available_variations();
                    if ( isset( $variations ) && !empty( $variations ) ) {
                        foreach ( $variations as $value ) {
                            if ( !empty( $sitepress ) ) {
                                $defaultlang_variation_product_id = apply_filters(
                                    'wpml_object_id',
                                    $value['variation_id'],
                                    'product',
                                    true,
                                    $default_lang
                                );
                            } else {
                                $defaultlang_variation_product_id = $value['variation_id'];
                            }
                            $baselang_variation_product_ids[] = $defaultlang_variation_product_id;
                        }
                    }
                }
                if ( $_product->is_type( 'simple' ) ) {
                    if ( !empty( $sitepress ) ) {
                        $defaultlang_simple_product_id = apply_filters(
                            'wpml_object_id',
                            $get_all_product->ID,
                            'product',
                            true,
                            $default_lang
                        );
                    } else {
                        $defaultlang_simple_product_id = $get_all_product->ID;
                    }
                    $defaultlang_simple_product_ids[] = $defaultlang_simple_product_id;
                }
            }
        }
        $baselang_product_ids = array_merge( $baselang_variation_product_ids, $defaultlang_simple_product_ids );
        if ( isset( $baselang_product_ids ) && !empty( $baselang_product_ids ) ) {
            foreach ( $baselang_product_ids as $baselang_product_id ) {
                $selected = array_map( 'intval', $selected );
                $selectedVal = ( is_array( $selected ) && !empty( $selected ) && in_array( $baselang_product_id, $selected, true ) ? 'selected=selected' : '' );
                if ( '' !== $selectedVal ) {
                    $html .= '<option value="' . $baselang_product_id . '" ' . $selectedVal . '>' . '#' . $baselang_product_id . ' - ' . get_the_title( $baselang_product_id ) . '</option>';
                }
            }
        }
        return $html;
    }

    /**
     * Change fees status in list section
     *
     * @since 1.0.0
     */
    public function wcpfc_pro_change_status_from_list_section() {
        // Security check
        check_ajax_referer( 'disable_fees_nonce', 'security' );
        // Enable & disable rule status
        $get_current_fees_id = filter_input( INPUT_GET, 'current_fees_id', FILTER_SANITIZE_NUMBER_INT );
        $get_current_value = filter_input( INPUT_GET, 'current_value', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if ( !isset( $get_current_fees_id ) ) {
            echo '<strong>' . esc_html__( 'Something went wrong', 'woocommerce-conditional-product-fees-for-checkout' ) . '</strong>';
            wp_die();
        }
        $post_id = ( isset( $get_current_fees_id ) ? absint( $get_current_fees_id ) : '' );
        $current_value = ( isset( $get_current_value ) ? sanitize_text_field( $get_current_value ) : '' );
        if ( 'true' === $current_value ) {
            $post_args = array(
                'ID'          => $post_id,
                'post_status' => 'publish',
                'post_type'   => self::wcpfc_post_type,
            );
            $post_update = wp_update_post( $post_args );
            update_post_meta( $post_id, 'fee_settings_status', 'on' );
        } else {
            $post_args = array(
                'ID'          => $post_id,
                'post_status' => 'draft',
                'post_type'   => self::wcpfc_post_type,
            );
            $post_update = wp_update_post( $post_args );
            update_post_meta( $post_id, 'fee_settings_status', 'off' );
        }
        if ( !empty( $post_update ) ) {
            delete_transient( 'get_top_ten_fees' );
            delete_transient( 'get_all_fees' );
            delete_transient( 'get_all_dashboard_fees' );
        } else {
            echo esc_html__( 'Something went wrong', 'woocommerce-conditional-product-fees-for-checkout' );
        }
        wp_die();
    }

    /**
     * Get default site language
     *
     * @return string $default_lang
     *
     * @since  1.0.0
     *
     */
    public function wcpfc_pro_get_default_langugae_with_sitpress() {
        global $sitepress;
        if ( !empty( $sitepress ) ) {
            $default_lang = $sitepress->get_current_language();
        } else {
            $default_lang = $this->wcpfc_pro_get_current_site_language();
        }
        return $default_lang;
    }

    /**
     * Get current site langugae
     *
     * @return string $default_lang
     * @since 1.0.0
     *
     */
    public function wcpfc_pro_get_current_site_language() {
        $get_site_language = get_bloginfo( "language" );
        if ( false !== strpos( $get_site_language, '-' ) ) {
            $get_site_language_explode = explode( '-', $get_site_language );
            $default_lang = $get_site_language_explode[0];
        } else {
            $default_lang = $get_site_language;
        }
        return $default_lang;
    }

    /**
     * Fetch slug based on id
     *
     * @since    3.6.1
     */
    public function wcpfc_pro_fetch_slug( $id_array, $condition ) {
        $return_array = array();
        if ( isset( $id_array ) && !empty( $id_array ) ) {
            foreach ( $id_array as $key => $ids ) {
                if ( 'product' === $condition || 'variableproduct' === $condition || 'cpp' === $condition ) {
                    $get_posts = get_post( $ids );
                    if ( !empty( $get_posts ) ) {
                        $return_array[] = $get_posts->post_name;
                    }
                } elseif ( 'category' === $condition || 'cpc' === $condition ) {
                    $term = get_term( $ids, 'product_cat' );
                    if ( $term ) {
                        $return_array[] = $term->slug;
                    }
                } elseif ( 'tag' === $condition ) {
                    $tag = get_term( $ids, 'product_tag' );
                    if ( $tag ) {
                        $return_array[] = $tag->slug;
                    }
                } elseif ( 'shipping_class' === $condition ) {
                    $shipping_class = get_term( $key, 'product_shipping_class' );
                    if ( $shipping_class ) {
                        $return_array[$shipping_class->slug] = $ids;
                    }
                } elseif ( 'cpsc' === $condition ) {
                    $return_array[] = $ids;
                } elseif ( 'cpp' === $condition ) {
                    $cpp_posts = get_post( $ids );
                    if ( !empty( $cpp_posts ) ) {
                        $return_array[] = $cpp_posts->post_name;
                    }
                } else {
                    $return_array[] = $ids;
                }
            }
        }
        return $return_array;
    }

    /**
     * Fetch id based on slug
     *
     * @since    3.6.1
     */
    public function wcpfc_pro_fetch_id( $slug_array, $condition ) {
        $return_array = array();
        if ( isset( $slug_array ) && !empty( $slug_array ) ) {
            foreach ( $slug_array as $key => $slugs ) {
                if ( 'product' === $condition ) {
                    $post = get_page_by_path( $slugs, OBJECT, 'product' );
                    // phpcs:ignore
                    $id = $post->ID;
                    $return_array[] = $id;
                } elseif ( 'variableproduct' === $condition ) {
                    $args = array(
                        'post_type' => 'product_variation',
                        'fields'    => 'ids',
                        'name'      => $slugs,
                    );
                    $variable_posts = new WP_Query($args);
                    if ( !empty( $variable_posts->posts ) ) {
                        foreach ( $variable_posts->posts as $val ) {
                            $return_array[] = $val;
                        }
                    }
                } elseif ( 'category' === $condition || 'cpc' === $condition ) {
                    $term = get_term_by( 'slug', $slugs, 'product_cat' );
                    if ( $term ) {
                        $return_array[] = $term->term_id;
                    }
                } elseif ( 'tag' === $condition ) {
                    $term_tag = get_term_by( 'slug', $slugs, 'product_tag' );
                    if ( $term_tag ) {
                        $return_array[] = $term_tag->term_id;
                    }
                } elseif ( 'shipping_class' === $condition || 'cpsc' === $condition ) {
                    $term_tag = get_term_by( 'slug', $key, 'product_shipping_class' );
                    if ( $term_tag ) {
                        $return_array[$term_tag->term_id] = $slugs;
                    }
                } elseif ( 'cpp' === $condition ) {
                    $args = array(
                        'post_type' => array('product_variation', 'product'),
                        'fields'    => 'ids',
                        'name'      => $slugs,
                    );
                    $variable_posts = new WP_Query($args);
                    if ( !empty( $variable_posts->posts ) ) {
                        foreach ( $variable_posts->posts as $val ) {
                            $return_array[] = $val;
                        }
                    }
                } else {
                    $return_array[] = $slugs;
                }
            }
        }
        return $return_array;
    }

    /**
     * Fetch translated IDs based on based language IDs.
     *
     * @since    3.9.2
     * @author   SJ
     */
    public function wcpfc_wpml_translated_id( $slug_array, $condition, $language_code ) {
        global $sitepress;
        $return_array = array();
        if ( isset( $slug_array ) && !empty( $slug_array ) ) {
            foreach ( $slug_array as $slugs ) {
                if ( 'product' === $condition ) {
                    $id = $slugs;
                    if ( !empty( $sitepress ) ) {
                        $id = apply_filters(
                            'wpml_object_id',
                            $slugs,
                            'product',
                            false,
                            $language_code
                        );
                    }
                    $return_array[] = $id;
                } elseif ( 'variableproduct' === $condition ) {
                    $id = $slugs;
                    if ( !empty( $sitepress ) ) {
                        $id = apply_filters(
                            'wpml_object_id',
                            $slugs,
                            'product_variation',
                            false,
                            $language_code
                        );
                    }
                    $return_array[] = $id;
                } elseif ( 'category' === $condition || 'cpc' === $condition ) {
                    $id = $slugs;
                    if ( !empty( $sitepress ) ) {
                        $id = apply_filters(
                            'wpml_object_id',
                            $slugs,
                            'product_cat',
                            false,
                            $language_code
                        );
                    }
                    $return_array[] = $id;
                } elseif ( 'tag' === $condition ) {
                    $id = $slugs;
                    if ( !empty( $sitepress ) ) {
                        $id = apply_filters(
                            'wpml_object_id',
                            $slugs,
                            'product_tag',
                            false,
                            $language_code
                        );
                    }
                    $return_array[] = $id;
                } elseif ( 'shipping_class' === $condition ) {
                    $id = $slugs;
                    if ( !empty( $sitepress ) ) {
                        $id = apply_filters(
                            'wpml_object_id',
                            $slugs,
                            'product_shipping_class',
                            false,
                            $language_code
                        );
                    }
                    $return_array[] = $id;
                } elseif ( 'ap_shipping_class' === $condition ) {
                    $term_tag = get_term_by( 'slug', $slugs, 'product_shipping_class' );
                    $id = $term_tag->term_id;
                    if ( !empty( $sitepress ) ) {
                        $id = apply_filters(
                            'wpml_object_id',
                            $id,
                            'product_shipping_class',
                            false,
                            $language_code
                        );
                    }
                    $term_tag = get_term_by( 'term_id', $id, 'product_shipping_class' );
                    $return_array[] = ( !empty( $term_tag ) && isset( $term_tag->slug ) && !empty( $term_tag->slug ) ? $term_tag->slug : '' );
                } elseif ( strpos( $condition, 'pa_' ) === 0 ) {
                    $term_tag = get_term_by( 'slug', $slugs, $condition );
                    $id = $term_tag->term_id;
                    if ( !empty( $sitepress ) ) {
                        $id = apply_filters(
                            'wpml_object_id',
                            $id,
                            $condition,
                            false,
                            $language_code
                        );
                    }
                    $term_tag = get_term_by( 'term_id', $id, $condition );
                    $return_array[] = ( !empty( $term_tag ) && isset( $term_tag->slug ) && !empty( $term_tag->slug ) ? $term_tag->slug : '' );
                } else {
                    $return_array[] = $slugs;
                }
            }
        }
        return $return_array;
    }

    /**
     * Display message in admin side
     *
     * @param string $message
     * @param string $tab
     *
     * @return bool
     * @since 1.0.0
     *
     */
    public function wcpfc_updated_message( $message, $validation_msg ) {
        if ( empty( $message ) ) {
            return false;
        }
        if ( 'created' === $message ) {
            $updated_message = esc_html__( "Fee rule has been created.", 'woocommerce-conditional-product-fees-for-checkout' );
        } elseif ( 'saved' === $message ) {
            $updated_message = esc_html__( "Fee rule has been updated.", 'woocommerce-conditional-product-fees-for-checkout' );
        } elseif ( 'deleted' === $message ) {
            $updated_message = esc_html__( "Fee rule has been deleted.", 'woocommerce-conditional-product-fees-for-checkout' );
        } elseif ( 'duplicated' === $message ) {
            $updated_message = esc_html__( "Fee rule has been duplicated.", 'woocommerce-conditional-product-fees-for-checkout' );
        } elseif ( 'disabled' === $message ) {
            $updated_message = esc_html__( "Fee rule has been disabled.", 'woocommerce-conditional-product-fees-for-checkout' );
        } elseif ( 'enabled' === $message ) {
            $updated_message = esc_html__( "Fee rule has been enabled.", 'woocommerce-conditional-product-fees-for-checkout' );
        }
        if ( 'failed' === $message ) {
            $failed_messsage = esc_html__( "There was an error with saving data.", 'woocommerce-conditional-product-fees-for-checkout' );
        } elseif ( 'nonce_check' === $message ) {
            $failed_messsage = esc_html__( "There was an error with security check.", 'woocommerce-conditional-product-fees-for-checkout' );
        }
        if ( 'validated' === $message ) {
            $validated_messsage = esc_html( $validation_msg );
        } elseif ( 'exist' === $message ) {
            $validated_messsage = esc_html__( "The fee rule title already exists. Please create a different title.", 'woocommerce-conditional-product-fees-for-checkout' );
        }
        if ( !empty( $updated_message ) ) {
            echo sprintf( '<div id="message" class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $updated_message ) );
            return false;
        }
        if ( !empty( $failed_messsage ) ) {
            echo sprintf( '<div id="message" class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $failed_messsage ) );
            return false;
        }
        if ( !empty( $validated_messsage ) ) {
            echo sprintf( '<div id="message" class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $validated_messsage ) );
            return false;
        }
    }

    /**
     * This function will return our plugin edit base language post link (not wordpress edit post link which cause "not allow to edit" error)
     * 
     * @param string $link
     * @param int    $post_id
     * @param string $lang
     * @param int    $trid
     *
     * @return string
     * @since    3.9.2
     * @author   SJ
     * 
     */
    public function wcpfc_wpml_translation_plugin_link(
        $link,
        $post_id,
        $lang,
        $trid
    ) {
        if ( !is_admin() ) {
            return $link;
        }
        global $wpml_tm_translation_status, $wpml_post_translations, $sitepress;
        $post_translations = $sitepress->post_translations();
        $status = $wpml_tm_translation_status->filter_translation_status( null, $trid, $lang );
        //status 10 means edit translated post
        $correct_id = $wpml_post_translations->element_id_in( $post_id, $lang );
        $source_lang = $post_translations->get_source_lang_code( $correct_id );
        if ( self::wcpfc_post_type === get_post_type( $post_id ) && empty( $source_lang ) ) {
            if ( !in_array( $status, array(0, 2), true ) && $status && $correct_id ) {
                $edit_method_url = add_query_arg( array(
                    'page'   => 'wcpfc-pro-list',
                    'action' => 'edit',
                    'id'     => $correct_id,
                    'lang'   => $lang,
                ), admin_url( 'admin.php' ) );
                $link = wp_nonce_url( $edit_method_url, 'edit_' . $correct_id, '_wpnonce' );
            }
        }
        return $link;
    }

    /**
     * This function will reset transient after create translated post
     * 
     * @param int       $new_post_id
     * @param array     $data_fields
     * @param object    $job
     *
     * @return string
     * @since    3.9.2
     * @author   SJ
     * 
     */
    public function wcpfc_wpml_transiention_action( $new_post_id, $data_fields, $job ) {
        $base_post_id = ( !empty( $job->original_doc_id ) && isset( $job->original_doc_id ) ? $job->original_doc_id : 0 );
        if ( self::wcpfc_post_type === get_post_type( $new_post_id ) ) {
            if ( $base_post_id > 0 ) {
                //Conditional Fee Rule Translated IDs of values
                $wppfc_wmpl_metabox_customize = array();
                $productFeesArray = get_post_meta( $base_post_id, 'product_fees_metabox', true );
                if ( !empty( $productFeesArray ) ) {
                    foreach ( $productFeesArray as $key => $condition_array ) {
                        if ( 'product' === $condition_array['product_fees_conditions_condition'] || 'variableproduct' === $condition_array['product_fees_conditions_condition'] || 'category' === $condition_array['product_fees_conditions_condition'] || 'tag' === $condition_array['product_fees_conditions_condition'] || 'shipping_class' === $condition_array['product_fees_conditions_condition'] || strpos( $condition_array['product_fees_conditions_condition'], 'pa_' ) === 0 ) {
                            $product_fees_conditions_values = $this->wcpfc_wpml_translated_id( $condition_array['product_fees_conditions_values'], $condition_array['product_fees_conditions_condition'], $job->language_code );
                            $wppfc_wmpl_metabox_customize[$key] = array(
                                'product_fees_conditions_condition' => $condition_array['product_fees_conditions_condition'],
                                'product_fees_conditions_is'        => $condition_array['product_fees_conditions_is'],
                                'product_fees_conditions_values'    => $product_fees_conditions_values,
                            );
                        } else {
                            $wppfc_wmpl_metabox_customize[$key] = array(
                                'product_fees_conditions_condition' => $condition_array['product_fees_conditions_condition'],
                                'product_fees_conditions_is'        => $condition_array['product_fees_conditions_is'],
                                'product_fees_conditions_values'    => $condition_array['product_fees_conditions_values'],
                            );
                        }
                    }
                    update_post_meta( $new_post_id, 'product_fees_metabox', $wppfc_wmpl_metabox_customize );
                }
                //Advanced Fee Price Rules translated Ids of Products value
                $wppfc_wmpl_ap_product_customize = array();
                $wppfc_wmpl_ap_product = get_post_meta( $base_post_id, 'sm_metabox_ap_product', true );
                if ( !empty( $wppfc_wmpl_ap_product ) ) {
                    foreach ( $wppfc_wmpl_ap_product as $key => $val ) {
                        $ap_fees_products_values = $this->wcpfc_wpml_translated_id( $val['ap_fees_products'], 'product', $job->language_code );
                        $wppfc_wmpl_ap_product_customize[$key] = array(
                            'ap_fees_products'         => $ap_fees_products_values,
                            'ap_fees_ap_prd_min_qty'   => $val['ap_fees_ap_prd_min_qty'],
                            'ap_fees_ap_prd_max_qty'   => $val['ap_fees_ap_prd_max_qty'],
                            'ap_fees_ap_price_product' => $val['ap_fees_ap_price_product'],
                            'ap_fees_ap_per_product'   => ( isset( $val['ap_fees_ap_per_product'] ) && !empty( $val['ap_fees_ap_per_product'] ) && strpos( $val['ap_fees_ap_price_product'], '%' ) ? $val['ap_fees_ap_per_product'] : 'no' ),
                        );
                    }
                    update_post_meta( $new_post_id, 'sm_metabox_ap_product', $wppfc_wmpl_ap_product_customize );
                }
                //Advanced Fee Price Rules translated IDs of Product Subtotal
                $wppfc_wmpl_ap_product_subtotal_customize = array();
                $wppfc_wmpl_ap_product_subtotal = get_post_meta( $base_post_id, 'sm_metabox_ap_product_subtotal', true );
                if ( !empty( $wppfc_wmpl_ap_product_subtotal ) ) {
                    foreach ( $wppfc_wmpl_ap_product_subtotal as $key => $val ) {
                        $ap_fees_product_subtotal_values = $this->wcpfc_wpml_translated_id( $val['ap_fees_product_subtotal'], 'product', $job->language_code );
                        $wppfc_wmpl_ap_product_subtotal_customize[$key] = array(
                            'ap_fees_product_subtotal'                 => $ap_fees_product_subtotal_values,
                            'ap_fees_ap_product_subtotal_min_subtotal' => $val['ap_fees_ap_product_subtotal_min_subtotal'],
                            'ap_fees_ap_product_subtotal_max_subtotal' => $val['ap_fees_ap_product_subtotal_max_subtotal'],
                            'ap_fees_ap_price_product_subtotal'        => $val['ap_fees_ap_price_product_subtotal'],
                        );
                    }
                    update_post_meta( $new_post_id, 'sm_metabox_ap_product_subtotal', $wppfc_wmpl_ap_product_subtotal_customize );
                }
                //Advanced Fee Price Rules translated IDs of Product Weight
                $wppfc_wmpl_ap_product_weight_customize = array();
                $wppfc_wmpl_ap_product_weight = get_post_meta( $base_post_id, 'sm_metabox_ap_product_weight', true );
                if ( !empty( $wppfc_wmpl_ap_product_weight ) ) {
                    foreach ( $wppfc_wmpl_ap_product_weight as $key => $val ) {
                        $ap_fees_products_weight_values = $this->wcpfc_wpml_translated_id( $val['ap_fees_product_weight'], 'product', $job->language_code );
                        $wppfc_wmpl_ap_product_weight_customize[$key] = array(
                            'ap_fees_product_weight'            => $ap_fees_products_weight_values,
                            'ap_fees_ap_product_weight_min_qty' => $val['ap_fees_ap_product_weight_min_qty'],
                            'ap_fees_ap_product_weight_max_qty' => $val['ap_fees_ap_product_weight_max_qty'],
                            'ap_fees_ap_price_product_weight'   => $val['ap_fees_ap_price_product_weight'],
                        );
                    }
                    update_post_meta( $new_post_id, 'sm_metabox_ap_product_weight', $wppfc_wmpl_ap_product_weight_customize );
                }
                //Advanced Fee Price Rules translated IDs of Category
                $wppfc_wmpl_ap_category_customize = array();
                $wppfc_wmpl_ap_fees_categories = get_post_meta( $base_post_id, 'sm_metabox_ap_category', true );
                if ( !empty( $wppfc_wmpl_ap_fees_categories ) ) {
                    foreach ( $wppfc_wmpl_ap_fees_categories as $key => $val ) {
                        $ap_fees_category_values = $this->wcpfc_wpml_translated_id( $val['ap_fees_categories'], 'category', $job->language_code );
                        $wppfc_wmpl_ap_category_customize[$key] = array(
                            'ap_fees_categories'        => $ap_fees_category_values,
                            'ap_fees_ap_cat_min_qty'    => $val['ap_fees_ap_cat_min_qty'],
                            'ap_fees_ap_cat_max_qty'    => $val['ap_fees_ap_cat_max_qty'],
                            'ap_fees_ap_price_category' => $val['ap_fees_ap_price_category'],
                        );
                    }
                    update_post_meta( $new_post_id, 'sm_metabox_ap_category', $wppfc_wmpl_ap_category_customize );
                }
                //Advanced Fee Price Rules translated IDs of Category Subtotal
                $wppfc_wmpl_ap_category_subtotal_customize = array();
                $wppfc_wmpl_ap_category_subtotal = get_post_meta( $base_post_id, 'sm_metabox_ap_category_subtotal', true );
                if ( !empty( $wppfc_wmpl_ap_category_subtotal ) ) {
                    foreach ( $wppfc_wmpl_ap_category_subtotal as $key => $val ) {
                        $ap_fees_ap_category_subtotal_values = $this->wcpfc_wpml_translated_id( $val['ap_fees_category_subtotal'], 'category', $job->language_code );
                        $wppfc_wmpl_ap_category_subtotal_customize[$key] = array(
                            'ap_fees_category_subtotal'                 => $ap_fees_ap_category_subtotal_values,
                            'ap_fees_ap_category_subtotal_min_subtotal' => $val['ap_fees_ap_category_subtotal_min_subtotal'],
                            'ap_fees_ap_category_subtotal_max_subtotal' => $val['ap_fees_ap_category_subtotal_max_subtotal'],
                            'ap_fees_ap_price_category_subtotal'        => $val['ap_fees_ap_price_category_subtotal'],
                        );
                    }
                    update_post_meta( $new_post_id, 'sm_metabox_ap_category_subtotal', $wppfc_wmpl_ap_category_subtotal_customize );
                }
                //Advanced Fee Rules translated IDs of Category Weight
                $wppfc_wmpl_ap_category_weight_customize = array();
                $wppfc_wmpl_ap_category_weight = get_post_meta( $base_post_id, 'sm_metabox_ap_category_weight', true );
                if ( !empty( $wppfc_wmpl_ap_category_weight ) ) {
                    foreach ( $wppfc_wmpl_ap_category_weight as $key => $val ) {
                        $ap_fees_ap_category_weight_values = $this->wcpfc_wpml_translated_id( $val['ap_fees_categories_weight'], 'category', $job->language_code );
                        $wppfc_wmpl_ap_category_weight_customize[$key] = array(
                            'ap_fees_categories_weight'          => $ap_fees_ap_category_weight_values,
                            'ap_fees_ap_category_weight_min_qty' => $val['ap_fees_ap_category_weight_min_qty'],
                            'ap_fees_ap_category_weight_max_qty' => $val['ap_fees_ap_category_weight_max_qty'],
                            'ap_fees_ap_price_category_weight'   => $val['ap_fees_ap_price_category_weight'],
                        );
                    }
                    update_post_meta( $new_post_id, 'sm_metabox_ap_category_weight', $wppfc_wmpl_ap_category_weight_customize );
                }
                //Advanced Fee Rules translated IDs of Shipping Class Subtotal
                $sm_metabox_ap_shipping_class_subtotal_customize = array();
                $wppfc_wmpl_ap_shipping_class_subtotal = get_post_meta( $base_post_id, 'sm_metabox_ap_shipping_class_subtotal', true );
                if ( !empty( $wppfc_wmpl_ap_shipping_class_subtotal ) ) {
                    foreach ( $wppfc_wmpl_ap_shipping_class_subtotal as $key => $val ) {
                        $ap_fees_ap_shipping_class_subtotal_values = $this->wcpfc_wpml_translated_id( $val['ap_fees_shipping_class_subtotals'], 'ap_shipping_class', $job->language_code );
                        $sm_metabox_ap_shipping_class_subtotal_customize[$key] = array(
                            'ap_fees_shipping_class_subtotals'                => $ap_fees_ap_shipping_class_subtotal_values,
                            'ap_fees_ap_shipping_class_subtotal_min_subtotal' => $val['ap_fees_ap_shipping_class_subtotal_min_subtotal'],
                            'ap_fees_ap_shipping_class_subtotal_max_subtotal' => $val['ap_fees_ap_shipping_class_subtotal_max_subtotal'],
                            'ap_fees_ap_price_shipping_class_subtotal'        => $val['ap_fees_ap_price_shipping_class_subtotal'],
                        );
                    }
                    update_post_meta( $new_post_id, 'sm_metabox_ap_shipping_class_subtotal', $sm_metabox_ap_shipping_class_subtotal_customize );
                }
            }
            delete_transient( 'get_all_fees' );
        }
    }

    /**
     * This function will add our custom post type fee traslatable link on admin language switcher
     * 
     * @param array     $languages_links
     *
     * @return array
     * @since    3.9.2
     * @author   SJ
     * 
     */
    public function wcpfc_admin_language_switcher_items( $languages_links ) {
        global $sitepress;
        $get_wpnonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_retrieved_nonce = ( isset( $get_wpnonce ) ? sanitize_text_field( wp_unslash( $get_wpnonce ) ) : '' );
        $post_id = ( isset( $_GET['id'] ) && !empty( $_GET['id'] ) ? intval( $_GET['id'] ) : 0 );
        if ( $post_id > 0 && self::wcpfc_post_type === get_post_type( $post_id ) && wp_verify_nonce( $get_retrieved_nonce, 'edit_' . $post_id ) ) {
            $post = get_post( $post_id );
            $trid = $sitepress->get_element_trid( $post_id, 'post_' . $post->post_type );
            $translations = $sitepress->get_element_translations( $trid, 'post_' . $post->post_type, true );
            $active_languages = $sitepress->get_active_languages();
            $current_language = $sitepress->get_current_language();
            if ( isset( $active_languages ) && !empty( $active_languages ) ) {
                foreach ( $active_languages as $lang ) {
                    if ( $lang !== $current_language ) {
                        if ( isset( $_SERVER['QUERY_STRING'] ) ) {
                            parse_str( sanitize_text_field( $_SERVER['QUERY_STRING'] ), $query_vars );
                            unset($query_vars['lang'], $query_vars['admin_bar']);
                        } else {
                            $query_vars = array();
                        }
                        if ( isset( $translations[$lang['code']] ) && isset( $translations[$lang['code']]->element_id ) ) {
                            $query_vars['id'] = $translations[$lang['code']]->element_id;
                            unset($query_vars['source_lang']);
                        }
                        $query_vars['lang'] = $lang['code'];
                        $query_vars['admin_bar'] = 1;
                        $edit_method_url = add_query_arg( $query_vars, admin_url( 'admin.php' ) );
                        $link = wp_nonce_url( $edit_method_url, 'edit_' . $query_vars['id'], '_wpnonce' );
                        $languages_links[$lang['code']]['url'] = $link;
                        //Here we can not open WPML advanced popup as they used "post" as post_id parameter and we use "id" as post_id that not satisfy by WPML notice condition
                        //here is condition: WPML_TM_Post_Edit_Notices::display_notices hook not append
                    }
                }
            }
        }
        return $languages_links;
    }

    /**
     * Upgrade to pro fee rules limit
     * 
     * @since 4.1.0
     * 
     */
    public function wcpfc_set_upgrade_to_pro_limit() {
        $current_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $current_action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if ( !empty( $current_page ) && 'wcpfc-pro-list' === $current_page ) {
            $rules_args = array(
                'post_type'      => self::wcpfc_post_type,
                'posts_per_page' => -1,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
                'post_status'    => 'any',
                'fields'         => 'ids',
            );
            $get_all_rules = new WP_Query($rules_args);
            if ( !empty( $get_all_rules->posts ) && is_array( $get_all_rules->posts ) ) {
                update_option( 'wcpfc_limited_allowed_rules', count( $get_all_rules->posts ) );
            } else {
                delete_option( 'wcpfc_limited_allowed_rules' );
            }
            // Add new rule restriction
            $valid_rules = get_option( 'wcpfc_limited_allowed_rules', '' );
            if ( !empty( $current_action ) && ('add' === $current_action || 'duplicate' === $current_action) ) {
                if ( !empty( $valid_rules ) && intval( $valid_rules ) >= 10 ) {
                    delete_transient( 'wcpfc-hide-limit-notice' );
                    wp_safe_redirect( add_query_arg( array(
                        'page' => 'wcpfc-pro-list',
                    ), admin_url( 'admin.php' ) ) );
                    exit;
                }
            }
            if ( !empty( $valid_rules ) && intval( $valid_rules ) >= 10 ) {
                $hide_limit_notice = filter_input( INPUT_GET, 'wcpfc-hide-limit-notice', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $limit_notice_nonce = filter_input( INPUT_GET, '_wcpfc_limit_notice_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                if ( isset( $hide_limit_notice ) && sanitize_text_field( $hide_limit_notice ) === 'wcpfc-hide-limit' && wp_verify_nonce( sanitize_text_field( $limit_notice_nonce ), 'wcpfc_limit_notices_nonce' ) ) {
                    // Set transient for three months
                    set_transient( 'wcpfc-hide-limit-notice', true, 3 * 30 * 24 * 60 * 60 );
                }
                /* Check transient, if available display notice */
                if ( !get_transient( 'wcpfc-hide-limit-notice' ) ) {
                    ?>
					<div id="message" class="notice notice-warning is-dismissible wcpfc-hide-limit-notice">
						<a class="notice-dismiss" href="<?php 
                    echo esc_url( wp_nonce_url( add_query_arg( 'wcpfc-hide-limit-notice', 'wcpfc-hide-limit' ), 'wcpfc_limit_notices_nonce', '_wcpfc_limit_notice_nonce' ) );
                    ?>">
						</a>
						<p><?php 
                    esc_html_e( 'Usage limit reached: 10 fee rules created. Upgrade to unlock unlimited access.', 'woocommerce-conditional-product-fees-for-checkout' );
                    ?></p>
					</div>
					<?php 
                }
            }
        }
    }

    /**
     * Get dynamic promotional bar of plugin
     *
     * @param   String  $plugin_slug  slug of the plugin added in the site option
     * @since    3.9.3
     * 
     * @return  null
     */
    public function wcpfc_get_promotional_bar( $plugin_slug = '' ) {
        $promotional_bar_upi_url = WCPFC_STORE_URL . 'wp-json/dpb-promotional-banner/v2/dpb-promotional-banner?' . wp_rand();
        $promotional_banner_request = wp_remote_get( $promotional_bar_upi_url );
        //phpcs:ignore
        if ( empty( $promotional_banner_request->errors ) ) {
            $promotional_banner_request_body = $promotional_banner_request['body'];
            $promotional_banner_request_body = json_decode( $promotional_banner_request_body, true );
            echo '<div class="dynamicbar_wrapper">';
            if ( !empty( $promotional_banner_request_body ) && is_array( $promotional_banner_request_body ) ) {
                foreach ( $promotional_banner_request_body as $promotional_banner_request_body_data ) {
                    $promotional_banner_id = $promotional_banner_request_body_data['promotional_banner_id'];
                    $promotional_banner_cookie = $promotional_banner_request_body_data['promotional_banner_cookie'];
                    $promotional_banner_image = $promotional_banner_request_body_data['promotional_banner_image'];
                    $promotional_banner_description = $promotional_banner_request_body_data['promotional_banner_description'];
                    $promotional_banner_button_group = $promotional_banner_request_body_data['promotional_banner_button_group'];
                    $dpb_schedule_campaign_type = $promotional_banner_request_body_data['dpb_schedule_campaign_type'];
                    $promotional_banner_target_audience = $promotional_banner_request_body_data['promotional_banner_target_audience'];
                    if ( !empty( $promotional_banner_target_audience ) ) {
                        $plugin_keys = array();
                        if ( is_array( $promotional_banner_target_audience ) ) {
                            foreach ( $promotional_banner_target_audience as $list ) {
                                $plugin_keys[] = $list['value'];
                            }
                        } else {
                            $plugin_keys[] = $promotional_banner_target_audience['value'];
                        }
                        $display_banner_flag = false;
                        if ( in_array( 'all_customers', $plugin_keys, true ) || in_array( $plugin_slug, $plugin_keys, true ) ) {
                            $display_banner_flag = true;
                        }
                    }
                    if ( true === $display_banner_flag ) {
                        if ( 'default' === $dpb_schedule_campaign_type ) {
                            $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            $banner_cookie_visible_once = filter_input( INPUT_COOKIE, 'banner_show_once_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            $flag = false;
                            if ( empty( $banner_cookie_show ) && empty( $banner_cookie_visible_once ) ) {
                                setcookie( 'banner_show_' . $promotional_banner_cookie, 'yes', time() + 86400 * 7 );
                                //phpcs:ignore
                                setcookie( 'banner_show_once_' . $promotional_banner_cookie, 'yes' );
                                //phpcs:ignore
                                $flag = true;
                            }
                            $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            if ( !empty( $banner_cookie_show ) || true === $flag ) {
                                $banner_cookie = filter_input( INPUT_COOKIE, 'banner_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                                $banner_cookie = ( isset( $banner_cookie ) ? $banner_cookie : '' );
                                if ( empty( $banner_cookie ) && 'yes' !== $banner_cookie ) {
                                    ?>
                            	<div class="dpb-popup <?php 
                                    echo ( isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner' );
                                    ?>">
                                    <?php 
                                    if ( !empty( $promotional_banner_image ) ) {
                                        ?>
                                        <img src="<?php 
                                        echo esc_url( $promotional_banner_image );
                                        ?>"/>
                                        <?php 
                                    }
                                    ?>
                                    <div class="dpb-popup-meta">
                                        <p>
                                            <?php 
                                    echo wp_kses_post( str_replace( array('<p>', '</p>'), '', $promotional_banner_description ) );
                                    if ( !empty( $promotional_banner_button_group ) ) {
                                        foreach ( $promotional_banner_button_group as $promotional_banner_button_group_data ) {
                                            ?>
                                                    <a href="<?php 
                                            echo esc_url( $promotional_banner_button_group_data['promotional_banner_button_link'] );
                                            ?>" target="_blank"><?php 
                                            echo esc_html( $promotional_banner_button_group_data['promotional_banner_button_text'] );
                                            ?></a>
                                                    <?php 
                                        }
                                    }
                                    ?>
                                    	</p>
                                    </div>
                                    <a href="javascript:void(0);" data-bar-id="<?php 
                                    echo esc_attr( $promotional_banner_id );
                                    ?>" data-popup-name="<?php 
                                    echo ( isset( $promotional_banner_cookie ) ? esc_attr( $promotional_banner_cookie ) : 'default-banner' );
                                    ?>" class="dpbpop-close"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><path id="Icon_material-close" data-name="Icon material-close" d="M17.5,8.507,16.493,7.5,12.5,11.493,8.507,7.5,7.5,8.507,11.493,12.5,7.5,16.493,8.507,17.5,12.5,13.507,16.493,17.5,17.5,16.493,13.507,12.5Z" transform="translate(-7.5 -7.5)" fill="#acacac"/></svg></a>
                                </div>
                                <?php 
                                }
                            }
                        } else {
                            $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            $banner_cookie_visible_once = filter_input( INPUT_COOKIE, 'banner_show_once_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            $flag = false;
                            if ( empty( $banner_cookie_show ) && empty( $banner_cookie_visible_once ) ) {
                                setcookie( 'banner_show_' . $promotional_banner_cookie, 'yes' );
                                //phpcs:ignore
                                setcookie( 'banner_show_once_' . $promotional_banner_cookie, 'yes' );
                                //phpcs:ignore
                                $flag = true;
                            }
                            $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            if ( !empty( $banner_cookie_show ) || true === $flag ) {
                                $banner_cookie = filter_input( INPUT_COOKIE, 'banner_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                                $banner_cookie = ( isset( $banner_cookie ) ? $banner_cookie : '' );
                                if ( empty( $banner_cookie ) && 'yes' !== $banner_cookie ) {
                                    ?>
                    			<div class="dpb-popup <?php 
                                    echo ( isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner' );
                                    ?>">
                                    <?php 
                                    if ( !empty( $promotional_banner_image ) ) {
                                        ?>
                                            <img src="<?php 
                                        echo esc_url( $promotional_banner_image );
                                        ?>"/>
                                        <?php 
                                    }
                                    ?>
                                    <div class="dpb-popup-meta">
                                        <p>
                                            <?php 
                                    echo wp_kses_post( str_replace( array('<p>', '</p>'), '', $promotional_banner_description ) );
                                    if ( !empty( $promotional_banner_button_group ) ) {
                                        foreach ( $promotional_banner_button_group as $promotional_banner_button_group_data ) {
                                            ?>
                                                    <a href="<?php 
                                            echo esc_url( $promotional_banner_button_group_data['promotional_banner_button_link'] );
                                            ?>" target="_blank"><?php 
                                            echo esc_html( $promotional_banner_button_group_data['promotional_banner_button_text'] );
                                            ?></a>
                                                    <?php 
                                        }
                                    }
                                    ?>
                                        </p>
                                    </div>
                                    <a href="javascript:void(0);" data-bar-id="<?php 
                                    echo esc_attr( $promotional_banner_id );
                                    ?>" data-popup-name="<?php 
                                    echo ( isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner' );
                                    ?>" class="dpbpop-close"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><path id="Icon_material-close" data-name="Icon material-close" d="M17.5,8.507,16.493,7.5,12.5,11.493,8.507,7.5,7.5,8.507,11.493,12.5,7.5,16.493,8.507,17.5,12.5,13.507,16.493,17.5,17.5,16.493,13.507,12.5Z" transform="translate(-7.5 -7.5)" fill="#acacac"/></svg></a>
                                </div>
                                <?php 
                                }
                            }
                        }
                    }
                }
            }
            echo '</div>';
        }
    }

    /**
     * Get and save plugin setup wizard data
     * 
     * @since    3.9.3
     * 
     */
    public function wcpfc_plugin_setup_wizard_submit() {
        check_ajax_referer( 'wizard_ajax_nonce', 'nonce' );
        $survey_list = filter_input( INPUT_GET, 'survey_list', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if ( !empty( $survey_list ) && 'Select One' !== $survey_list ) {
            update_option( 'wcpfc_where_hear_about_us', $survey_list );
        }
        wp_die();
    }

    /**
     * Send setup wizard data to sendinblue
     * 
     * @since    3.9.3
     * 
     */
    public function wcpfc_send_wizard_data_after_plugin_activation() {
        $send_wizard_data = filter_input( INPUT_GET, 'send-wizard-data', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if ( isset( $send_wizard_data ) && !empty( $send_wizard_data ) ) {
            if ( !get_option( 'wcpfc_data_submited_in_sendiblue' ) ) {
                $wcpfc_where_hear = get_option( 'wcpfc_where_hear_about_us' );
                $get_user = wcpffc_fs()->get_user();
                $data_insert_array = array();
                if ( isset( $get_user ) && !empty( $get_user ) ) {
                    $data_insert_array = array(
                        'user_email'              => $get_user->email,
                        'ACQUISITION_SURVEY_LIST' => $wcpfc_where_hear,
                    );
                }
                $feedback_api_url = WCPFC_STORE_URL . 'wp-json/dotstore-sendinblue-data/v2/dotstore-sendinblue-data?' . wp_rand();
                $query_url = $feedback_api_url . '&' . http_build_query( $data_insert_array );
                if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
                    $response = vip_safe_wp_remote_get(
                        $query_url,
                        3,
                        1,
                        20
                    );
                } else {
                    $response = wp_remote_get( $query_url );
                }
                if ( !is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                    update_option( 'wcpfc_data_submited_in_sendiblue', '1' );
                    delete_option( 'wcpfc_where_hear_about_us' );
                }
            }
        }
    }

    /**
     * Display a custom button to add a fee to the WooCommerce order items table.
     *
     * @return void
     */
    public function wcpfc_add_custom_fee_button_in_add_order_items() {
        ?>
			<button type="button" class="button" onclick="window.location.href='<?php 
        echo esc_url( admin_url( 'admin.php?page=wcpfc-upgrade-dashboard' ) );
        ?>'">
				<?php 
        esc_html_e( 'Add Custom Fee 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
        ?>
			</button> <?php 
    }

}
