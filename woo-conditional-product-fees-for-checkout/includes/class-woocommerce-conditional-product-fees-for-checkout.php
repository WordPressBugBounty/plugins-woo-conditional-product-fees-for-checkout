<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 * @package    Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @subpackage Woocommerce_Conditional_Product_Fees_For_Checkout_Pro/includes
 * @author     Multidots <inquiry@multidots.in>
 */
if ( !class_exists( 'Woocommerce_Conditional_Product_Fees_For_Checkout_Pro' ) ) {
    class Woocommerce_Conditional_Product_Fees_For_Checkout_Pro {
        /**
         * The loader that's responsible for maintaining and registering all hooks that power
         * the plugin.
         *
         * @since    1.0.0
         * @access   protected
         * @var      Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Loader $loader Maintains and registers all hooks for the plugin.
         */
        protected $loader;

        /**
         * The unique identifier of this plugin.
         *
         * @since    1.0.0
         * @access   protected
         * @var      string $plugin_name The string used to uniquely identify this plugin.
         */
        protected $plugin_name;

        /**
         * The current version of the plugin.
         *
         * @since    1.0.0
         * @access   protected
         * @var      string $version The current version of the plugin.
         */
        protected $version;

        const WCPFC_VERSION = WCPFC_PRO_PLUGIN_VERSION;

        /** 
         * The current instance of the plugin
         * 
         * @since   1.0.0
         * @access  protected
         * @var     \Woocommerce_Conditional_Product_Fees_For_Checkout_Pro single instance of this plugin 
         */
        protected static $instance;

        /**
         * Define the core functionality of the plugin.
         *
         * Set the plugin name and the plugin version that can be used throughout the plugin.
         * Load the dependencies, define the locale, and set the hooks for the admin area and
         * the public-facing side of the site.
         *
         * @since    1.0.0
         */
        public function __construct() {
            $this->plugin_name = 'woocommerce-conditional-product-fees-for-checkout';
            $this->version = WCPFC_PRO_PLUGIN_VERSION;
            $this->load_dependencies();
            $this->set_locale();
            $this->define_admin_hooks();
            $this->define_public_hooks();
            $prefix = ( is_network_admin() ? 'network_admin_' : '' );
            add_filter(
                "{$prefix}plugin_action_links_" . WCPFC_PRO_PLUGIN_BASENAME,
                array($this, 'plugin_action_links'),
                10,
                4
            );
            add_filter(
                'plugin_row_meta',
                array($this, 'plugin_row_meta_action_links'),
                20,
                3
            );
        }

        /**
         * Load the required dependencies for this plugin.
         *
         * Include the following files that make up the plugin:
         *
         * - Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Loader. Orchestrates the hooks of the plugin.
         * - Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_i18n. Defines internationalization functionality.
         * - Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Admin. Defines all hooks for the admin area.
         * - Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Public. Defines all hooks for the public side of the site.
         *
         * Create an instance of the loader which will be used to register the hooks
         * with WordPress.
         *
         * @since    1.0.0
         * @access   private
         */
        private function load_dependencies() {
            /**
             * The class responsible for orchestrating the actions and filters of the
             * core plugin.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-conditional-product-fees-for-checkout-loader.php';
            /**
             * The class responsible for defining internationalization functionality
             * of the plugin.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-conditional-product-fees-for-checkout-i18n.php';
            /**
             * The class responsible for defining all actions that occur in the admin area.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-conditional-product-fees-for-checkout-admin.php';
            /**
             * The class responsible for defining all actions that occur in the public-facing
             * side of the site.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommerce-conditional-product-fees-for-checkout-public.php';
            $this->loader = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Loader();
            /**
             * The class responsible for defining all custom command for use in WP-CLI
             * of the site.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-conditional-product-fees-for-checkout-cli-commands.php';
            if ( class_exists( 'WP_CLI' ) ) {
                WP_CLI::add_command( 'dotstore', 'DS_Command_Line' );
            }
        }

        /**
         * Define the locale for this plugin for internationalization.
         *
         * Uses the Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_i18n class in order to set the domain and to register the hook
         * with WordPress.
         *
         * @since    1.0.0
         * @access   private
         */
        private function set_locale() {
            $plugin_i18n = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_i18n();
            $plugin_i18n->set_domain( $this->get_plugin_name() );
            $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
        }

        /**
         * Register all of the hooks related to the admin area functionality
         * of the plugin.
         *
         * @since    1.0.0
         * @access   private
         */
        private function define_admin_hooks() {
            $page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $plugin_admin = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Admin($this->get_plugin_name(), $this->get_version());
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'wcpfc_admin_enqueue_styles' );
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'wcpfc_admin_enqueue_scripts' );
            $this->loader->add_action( 'admin_menu', $plugin_admin, 'wcpfc_admin_menu_pages' );
            $this->loader->add_action( 'admin_head', $plugin_admin, 'wcpfc_dot_store_icon_css' );
            $this->loader->add_action( 'admin_notices', $plugin_admin, 'wcpfc_pro_notifications' );
            $this->loader->add_action( 'wp_ajax_wcpfc_pro_product_fees_conditions_values_ajax', $plugin_admin, 'wcpfc_pro_product_fees_conditions_values_ajax' );
            $this->loader->add_action( 'wp_ajax_nopriv_wcpfc_pro_product_fees_conditions_values_ajax', $plugin_admin, 'wcpfc_pro_product_fees_conditions_values_ajax' );
            $this->loader->add_action( 'wp_ajax_wcpfc_pro_product_fees_conditions_values_product_ajax', $plugin_admin, 'wcpfc_pro_product_fees_conditions_values_product_ajax' );
            $this->loader->add_action( 'wp_ajax_nopriv_wcpfc_pro_product_fees_conditions_values_product_ajax', $plugin_admin, 'wcpfc_pro_product_fees_conditions_values_product_ajax' );
            $this->loader->add_action( 'wp_ajax_wcpfc_pro_product_fees_conditions_sorting', $plugin_admin, 'wcpfc_pro_conditional_fee_sorting' );
            $this->loader->add_action( 'trashed_post', $plugin_admin, 'wcpfc_clear_fee_cache' );
            $this->loader->add_action( 'admin_head', $plugin_admin, 'wcpfc_pro_remove_admin_submenus' );
            $this->loader->add_action( 'admin_init', $plugin_admin, 'wcpfc_pro_welcome_conditional_fee_screen_do_activation_redirect' );
            if ( !empty( $page ) && false !== strpos( $page, 'wcpfc' ) ) {
                $this->loader->add_filter( 'admin_footer_text', $plugin_admin, 'wcpfc_pro_admin_footer_review' );
            }
            $this->loader->add_action( 'wp_ajax_wcpfc_pro_change_status_from_list_section', $plugin_admin, 'wcpfc_pro_change_status_from_list_section' );
            $this->loader->add_action( 'wp_ajax_wcpfc_pro_product_fees_conditions_varible_values_product_ajax', $plugin_admin, 'wcpfc_pro_product_fees_conditions_varible_values_product_ajax' );
            $this->loader->add_action( 'wp_ajax_wcpfc_pro_simple_and_variation_product_list_ajax', $plugin_admin, 'wcpfc_pro_simple_and_variation_product_list_ajax' );
            // Add custom fee button in add order items
            $this->loader->add_action( 'woocommerce_order_item_add_line_buttons', $plugin_admin, 'wcpfc_add_custom_fee_button_in_add_order_items' );
            $this->loader->add_filter(
                'hidden_columns',
                $plugin_admin,
                'wcpfc_default_hidden_columns',
                10,
                2
            );
            $this->loader->add_filter(
                'set-screen-option',
                $plugin_admin,
                'wcpfc_set_screen_options',
                10,
                3
            );
            $this->loader->add_action( 'wp_ajax_wcpfc_plugin_setup_wizard_submit', $plugin_admin, 'wcpfc_plugin_setup_wizard_submit' );
            $this->loader->add_action( 'admin_init', $plugin_admin, 'wcpfc_send_wizard_data_after_plugin_activation' );
            $this->loader->add_action( 'wp_ajax_wcpfc_pro_product_fees_conditions_values_user_ajax', $plugin_admin, 'wcpfc_pro_product_fees_conditions_values_user_ajax' );
            //From 3.9.2 WPML changes hook
            $this->loader->add_filter(
                'wpml_link_to_translation',
                $plugin_admin,
                'wcpfc_wpml_translation_plugin_link',
                21,
                4
            );
            $this->loader->add_action(
                'icl_pro_translation_saved',
                $plugin_admin,
                'wcpfc_wpml_transiention_action',
                10,
                3
            );
            $this->loader->add_action(
                'icl_pro_translation_completed',
                $plugin_admin,
                'wcpfc_wpml_transiention_action',
                10,
                3
            );
            $this->loader->add_filter(
                'wpml_admin_language_switcher_items',
                $plugin_admin,
                'wcpfc_admin_language_switcher_items',
                10,
                1
            );
            if ( !(wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code()) ) {
                $this->loader->add_action(
                    'admin_init',
                    $plugin_admin,
                    'wcpfc_set_upgrade_to_pro_limit',
                    10
                );
            }
        }

        /**
         * Register all of the hooks related to the public-facing functionality
         * of the plugin.
         *
         * @since    1.0.0
         * @access   private
         */
        private function define_public_hooks() {
            $plugin_public = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Public($this->get_plugin_name(), $this->get_version());
            $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'wcpfc_public_enqueue_styles' );
            $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'wcpfc_public_enqueue_scripts' );
            $this->loader->add_action( 'woocommerce_cart_calculate_fees', $plugin_public, 'wcpfc_pro_conditional_fee_add_to_cart' );
            $this->loader->add_action(
                'woocommerce_checkout_create_order',
                $plugin_public,
                'wcpfc_add_fee_details_with_order_for_track',
                20,
                1
            );
            $this->loader->add_filter(
                'woocommerce_locate_template',
                $plugin_public,
                'wcpfc_wc_locate_template_product_fees_conditions',
                1,
                3
            );
        }

        /**
         * Return the plugin action links.  This will only be called if the plugin
         * is active.
         *
         * @param array $actions associative array of action names to anchor tags
         *
         * @return array associative array of plugin action links
         * @since 1.0.0
         */
        public function plugin_action_links( $actions ) {
            $custom_actions = array(
                'configure' => sprintf( '<a href="%s">%s</a>', esc_url( add_query_arg( array(
                    'page' => 'wcpfc-pro-list',
                ), admin_url( 'admin.php' ) ) ), __( 'Settings', 'woocommerce-conditional-product-fees-for-checkout' ) ),
                'docs'      => sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( 'https://docs.thedotstore.com/category/191-premium-plugin-settings' ), __( 'Docs', 'woocommerce-conditional-product-fees-for-checkout' ) ),
                'support'   => sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( 'https://www.thedotstore.com/support' ), __( 'Support', 'woocommerce-conditional-product-fees-for-checkout' ) ),
            );
            // add the links to the front of the actions list
            return array_merge( $custom_actions, $actions );
        }

        /**
         * Add review stars in plugin row meta
         *
         * @since 1.0.0
         */
        public function plugin_row_meta_action_links( $plugin_meta, $plugin_file, $plugin_data ) {
            if ( isset( $plugin_data['TextDomain'] ) && $plugin_data['TextDomain'] !== 'woocommerce-conditional-product-fees-for-checkout' ) {
                return $plugin_meta;
            }
            $url = '';
            $url = esc_url( 'https://wordpress.org/plugins/woo-conditional-product-fees-for-checkout/#reviews' );
            $plugin_meta[] = sprintf( '<a href="%s" target="_blank" style="color:#f5bb00;">%s</a>', $url, esc_html( '★★★★★' ) );
            return $plugin_meta;
        }

        /**
         * Run the loader to execute all of the hooks with WordPress.
         *
         * @since    1.0.0
         */
        public function run() {
            $this->loader->run();
        }

        /**
         * The name of the plugin used to uniquely identify it within the context of
         * WordPress and to define internationalization functionality.
         *
         * @return    string    The name of the plugin.
         * @since     1.0.0
         */
        public function get_plugin_name() {
            return $this->plugin_name;
        }

        /**
         * The reference to the class that orchestrates the hooks with the plugin.
         *
         * @return    Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Loader    Orchestrates the hooks of the plugin.
         * @since     1.0.0
         */
        public function get_loader() {
            return $this->loader;
        }

        /**
         * Retrieve the version number of the plugin.
         *
         * @return    string    The version number of the plugin.
         * @since     1.0.0
         */
        public function get_version() {
            return $this->version;
        }

        /**
         * Allowed html tags used for wp_kses function
         *
         * @param array add custom tags
         *
         * @return array
         * @since     1.0.0
         *
         */
        public static function allowed_html_tags() {
            $allowed_tags = array(
                'a'        => array(
                    'href'         => array(),
                    'title'        => array(),
                    'class'        => array(),
                    'target'       => array(),
                    'data-tooltip' => array(),
                ),
                'ul'       => array(
                    'class' => array(),
                ),
                'li'       => array(
                    'class' => array(),
                ),
                'div'      => array(
                    'class' => array(),
                    'id'    => array(),
                ),
                'select'   => array(
                    'rel-id'   => array(),
                    'id'       => array(),
                    'name'     => array(),
                    'class'    => array(),
                    'multiple' => array(),
                    'style'    => array(),
                ),
                'input'    => array(
                    'id'         => array(),
                    'value'      => array(),
                    'name'       => array(),
                    'class'      => array(),
                    'type'       => array(),
                    'data-index' => array(),
                ),
                'textarea' => array(
                    'id'    => array(),
                    'name'  => array(),
                    'class' => array(),
                ),
                'option'   => array(
                    'id'       => array(),
                    'selected' => array(),
                    'name'     => array(),
                    'value'    => array(),
                ),
                'br'       => array(),
                'p'        => array(),
                'b'        => array(
                    'style' => array(),
                ),
                'em'       => array(),
                'strong'   => array(),
                'i'        => array(
                    'class' => array(),
                ),
                'span'     => array(
                    'class' => array(),
                ),
                'small'    => array(
                    'class' => array(),
                ),
                'label'    => array(
                    'class' => array(),
                    'id'    => array(),
                    'for'   => array(),
                ),
            );
            return $allowed_tags;
        }

        /**
         * Include template with arguments
         *
         * @param string $__template
         * @param array  $__variables
         * 
         * @since   1.0.0
         * 
         * @link https://woocommerce.github.io/code-reference/files/woocommerce-includes-wc-core-functions.html#source-view.340
         */
        public function include_template( $__template, array $__variables = [] ) {
            $template_file = WCPFC_PLUGIN_BASE_DIR . $__template;
            if ( file_exists( $template_file ) ) {
                extract( $__variables );
                // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
                include $template_file;
            }
        }

        /**
         * Check woocommerce page has block cart and checkout
         * 
         * @param string $isBlockPage
         * 
         * @return boolean
         * 
         * @since 1.0.0
         */
        public function is_wc_has_block( $isBlockPage = '' ) {
            $isBlockCart = WC_Blocks_Utils::has_block_in_page( wc_get_page_id( 'cart' ), 'woocommerce/cart' );
            $isBlockCheckout = WC_Blocks_Utils::has_block_in_page( wc_get_page_id( 'checkout' ), 'woocommerce/checkout' );
            if ( empty( $isBlockPage ) ) {
                return $isBlockCart || $isBlockCheckout;
            }
            if ( 'cart' === $isBlockPage ) {
                return $isBlockCart;
            }
            if ( 'checkout' === $isBlockPage ) {
                return $isBlockCheckout;
            }
            return false;
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
         * @return \Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
         */
        public static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

    }

}
/**
 * Returns the One True Instance of Advance Extra Fee WooCommerce class object.
 *
 * @since 1.0.0
 *
 * @return \Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 */
function wcpfc_pro() {
    return \Woocommerce_Conditional_Product_Fees_For_Checkout_Pro::instance();
}
