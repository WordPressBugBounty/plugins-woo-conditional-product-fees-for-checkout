<?php

/**
 * Plugin Name: Extra Fees Plugin for WooCommerce
 * Plugin URI:          https://www.thedotstore.com/woocommerce-conditional-product-fees-checkout/
 * Description:         With this plugin, you can create and manage complex fee rules in WooCommerce store without the help of a developer.
 * Version:             4.1.1
 * Author:              theDotstore
 * Author URI:          https://www.thedotstore.com/
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         woocommerce-conditional-product-fees-for-checkout
 * Domain Path:         /languages
 * Requires Plugins:    woocommerce
 *
 * WC requires at least:4.5
 * WP tested up to:     6.6.2
 * WC tested up to:     9.3.3
 * Requires PHP:        7.2
 * Requires at least:   5.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'wcpffc_fs' ) ) {
    wcpffc_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'wcpffc_fs' ) ) {
        // Create a helper function for easy SDK access.
        function wcpffc_fs() {
            global $wcpffc_fs;
            if ( !isset( $wcpffc_fs ) ) {
                // Activate multisite network integration.
                if ( !defined( 'WP_FS__PRODUCT_3390_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_3390_MULTISITE', true );
                }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $wcpffc_fs = fs_dynamic_init( array(
                    'id'              => '3390',
                    'slug'            => 'woocommerce-conditional-product-fees-for-checkout',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_d202bec45f41a5ae6b41399bde03f',
                    'is_premium'      => false,
                    'premium_suffix'  => 'Premium',
                    'has_addons'      => false,
                    'has_paid_plans'  => true,
                    'trial'           => array(
                        'days'               => 14,
                        'is_require_payment' => true,
                    ),
                    'has_affiliation' => 'selected',
                    'menu'            => array(
                        'slug'       => 'wcpfc-pro-list',
                        'first-path' => 'admin.php?page=wcpfc-pro-list&send-wizard-data=true',
                        'contact'    => false,
                        'support'    => false,
                        'network'    => true,
                    ),
                    'is_live'         => true,
                    'navigation'      => 'menu',
                ) );
            }
            return $wcpffc_fs;
        }

        // Init Freemius.
        wcpffc_fs();
        // Signal that SDK was initiated.
        do_action( 'wcpffc_fs_loaded' );
        wcpffc_fs()->get_upgrade_url();
    }
}
// Define plugin basename constant
if ( !defined( 'WCPFC_PRO_PLUGIN_BASENAME' ) ) {
    define( 'WCPFC_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
/**
 * Hide freemius account tab
 *
 * @since    3.9.3
 */
if ( !function_exists( 'wcpfc_hide_account_tab' ) ) {
    function wcpfc_hide_account_tab() {
        return true;
    }

    wcpffc_fs()->add_filter( 'hide_account_tabs', 'wcpfc_hide_account_tab' );
}
/**
 * Include plugin header on freemius account page
 *
 * @since    1.0.0
 */
if ( !function_exists( 'wcpfc_load_plugin_header_after_account' ) ) {
    function wcpfc_load_plugin_header_after_account() {
        require_once plugin_dir_path( __FILE__ ) . 'admin/partials/header/plugin-header.php';
        ?>
        </div>
        </div>
        </div>
        </div>
        <?php 
    }

    wcpffc_fs()->add_action( 'after_account_details', 'wcpfc_load_plugin_header_after_account' );
}
/**
 * Hide billing and payments details from freemius account page
 *
 * @since    3.9.3
 */
if ( !function_exists( 'wcpfc_hide_billing_and_payments_info' ) ) {
    function wcpfc_hide_billing_and_payments_info() {
        return true;
    }

    wcpffc_fs()->add_action( 'hide_billing_and_payments_info', 'wcpfc_hide_billing_and_payments_info' );
}
/**
 * Hide powerd by popup from freemius account page
 *
 * @since    3.9.3
 */
if ( !function_exists( 'wcpfc_hide_freemius_powered_by' ) ) {
    function wcpfc_hide_freemius_powered_by() {
        return true;
    }

    wcpffc_fs()->add_action( 'hide_freemius_powered_by', 'wcpfc_hide_freemius_powered_by' );
}
/**
 * Start plugin setup wizard before license activation screen
 *
 * @since    3.9.3
 */
if ( !function_exists( 'wcpfc_load_plugin_setup_wizard_connect_before' ) ) {
    function wcpfc_load_plugin_setup_wizard_connect_before() {
        require_once plugin_dir_path( __FILE__ ) . 'admin/partials/dots-plugin-setup-wizard.php';
        ?>
        <div class="tab-panel" id="step5">
            <div class="ds-wizard-wrap">
                <div class="ds-wizard-content">
                    <h2 class="cta-title"><?php 
        echo esc_html__( 'Activate Plugin', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></h2>
                </div>
        <?php 
    }

    wcpffc_fs()->add_action( 'connect/before', 'wcpfc_load_plugin_setup_wizard_connect_before' );
}
/**
 * End plugin setup wizard after license activation screen
 *
 * @since    3.9.3
 */
if ( !function_exists( 'wcpfc_load_plugin_setup_wizard_connect_after' ) ) {
    function wcpfc_load_plugin_setup_wizard_connect_after() {
        ?>
        </div>
        </div>
        </div>
        </div>
        <?php 
    }

    wcpffc_fs()->add_action( 'connect/after', 'wcpfc_load_plugin_setup_wizard_connect_after' );
}
/**
 * The core plugin include constant file for set constant.
 */
require plugin_dir_path( __FILE__ ) . 'constant.php';
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-conditional-product-fees-for-checkout-activator.php
 */
add_action( 'plugins_loaded', 'wcpfc_initialize_plugin', 11 );
/**
 * Check Initialize plugin in case of WooCommerce plugin is missing.
 *
 * @since    1.0.0
 */
if ( !function_exists( 'wcpfc_initialize_plugin' ) ) {
    function wcpfc_initialize_plugin() {
        $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
        if ( is_multisite() ) {
            $network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
            $active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
            $active_plugins = array_unique( $active_plugins );
            if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', $active_plugins ), true ) ) {
                add_action( 'admin_notices', 'wcpfc_plugin_admin_notice_required_plugin' );
            }
        } else {
            if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', $active_plugins ), true ) ) {
                add_action( 'admin_notices', 'wcpfc_plugin_admin_notice_required_plugin' );
            }
        }
    }

}
if ( !function_exists( 'wcpfc_pro_activation' ) ) {
    function wcpfc_pro_activation() {
        set_transient( 'wcpfc-admin-notice', true );
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-conditional-product-fees-for-checkout-activator.php';
        Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Activator::activate();
    }

}
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-conditional-product-fees-for-checkout-deactivator.php
 */
if ( !function_exists( 'wcpfc_pro_deactivation' ) ) {
    function wcpfc_pro_deactivation() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-conditional-product-fees-for-checkout-deactivator.php';
        Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Deactivator::deactivate();
    }

}
register_activation_hook( __FILE__, 'wcpfc_pro_activation' );
register_deactivation_hook( __FILE__, 'wcpfc_pro_deactivation' );
add_action( 'admin_init', 'wcpfc_deactivate_plugin' );
if ( !function_exists( 'wcpfc_deactivate_plugin' ) ) {
    function wcpfc_deactivate_plugin() {
        $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
        if ( is_multisite() ) {
            $network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
            $active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
            $active_plugins = array_unique( $active_plugins );
            if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', $active_plugins ), true ) ) {
                if ( wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code() ) {
                    deactivate_plugins( '/woocommerce-conditional-product-fees-for-checkout-premium/woocommerce-conditional-product-fees-for-checkout.php', true );
                } else {
                    deactivate_plugins( '/woo-conditional-product-fees-for-checkout/woocommerce-conditional-product-fees-for-checkout.php', true );
                    //WordPress ORG name
                    deactivate_plugins( '/woocommerce-conditional-product-fees-for-checkout-premium/woocommerce-conditional-product-fees-for-checkout.php', true );
                    //Freemius name
                }
            }
        } else {
            if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', $active_plugins ), true ) ) {
                if ( wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code() ) {
                    deactivate_plugins( '/woocommerce-conditional-product-fees-for-checkout-premium/woocommerce-conditional-product-fees-for-checkout.php', true );
                } else {
                    deactivate_plugins( '/woo-conditional-product-fees-for-checkout/woocommerce-conditional-product-fees-for-checkout.php', true );
                    //WordPress ORG name
                    deactivate_plugins( '/woocommerce-conditional-product-fees-for-checkout-premium/woocommerce-conditional-product-fees-for-checkout.php', true );
                    //Freemius name
                }
            }
        }
    }

}
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-conditional-product-fees-for-checkout.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
if ( !function_exists( 'wcpfc_pro_activation_run' ) ) {
    function wcpfc_pro_activation_run() {
        $plugin = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro();
        $plugin->run();
    }

    wcpfc_pro_activation_run();
}
if ( !function_exists( 'wcpfc_pro_path' ) ) {
    function wcpfc_pro_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

}
/**
 * Show admin notice in case of WooCommerce plugin is missing.
 *
 * @since    1.0.0
 */
if ( !function_exists( 'wcpfc_plugin_admin_notice_required_plugin' ) ) {
    function wcpfc_plugin_admin_notice_required_plugin() {
        $vpe_plugin = esc_html__( 'WooCommerce Extra Fees Plugin', 'woocommerce-conditional-product-fees-for-checkout' );
        $wc_plugin = esc_html__( 'WooCommerce', 'woocommerce-conditional-product-fees-for-checkout' );
        ?>
        <div class="error">
            <p><?php 
        echo sprintf( esc_html__( '%1$s requires %2$s to be installed & activated!', 'woocommerce-conditional-product-fees-for-checkout' ), '<strong>' . esc_html( $vpe_plugin ) . '</strong>', '<a href="' . esc_url( 'https://wordpress.org/plugins/woocommerce/' ) . '" target="_blank"><strong>' . esc_html( $wc_plugin ) . '</strong></a>' );
        ?></p>
        </div>
        <?php 
    }

}
/**
 * Plugin compability with WooCommerce HPOS
 *
 * @since 3.9.4
 */
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
    }
} );