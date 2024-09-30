<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * define constant variabes
 * define admin side constant
 *
 * @param null
 *
 * @author Multidots
 * @since  1.0.0
 */
// define constant for plugin
if ( !defined( 'WCPFC_PRO_PLUGIN_VERSION' ) ) {
    define( 'WCPFC_PRO_PLUGIN_VERSION', '4.1.1' );
}
if ( !defined( 'WCPFC_PRO_PLUGIN_URL' ) ) {
    define( 'WCPFC_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( !defined( 'WCPFC_PLUGIN_DIR' ) ) {
    define( 'WCPFC_PLUGIN_DIR', dirname( __FILE__ ) );
}
if ( !defined( 'WCPFC_PRO_PLUGIN_DIR_PATH' ) ) {
    define( 'WCPFC_PRO_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
}
if ( !defined( 'WCPFC_PRO_SLUG' ) ) {
    define( 'WCPFC_PRO_SLUG', 'woocommerce-conditional-product-fees-for-checkout' );
}
if ( !defined( 'WCPFC_PRO_PREMIUM_VERSION' ) ) {
    if ( !defined( 'WCPFC_PRO_PREMIUM_VERSION' ) ) {
        define( 'WCPFC_PRO_PREMIUM_VERSION', 'Free Version ' );
    }
}
if ( !defined( 'WCPFC_PRO_PLUGIN_NAME' ) ) {
    define( 'WCPFC_PRO_PLUGIN_NAME', 'WooCommerce Extra Fees Plugin' );
}
if ( !defined( 'WCPFC_STORE_URL' ) ) {
    define( 'WCPFC_STORE_URL', 'https://www.thedotstore.com/' );
}
/**
 * The function is used to dynamically generate the base path of the directory containing the main plugin file.
 */
if ( !defined( 'WCPFC_PLUGIN_BASE_DIR' ) ) {
    define( 'WCPFC_PLUGIN_BASE_DIR', plugin_dir_path( __FILE__ ) );
}