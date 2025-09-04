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
/** Dotstore Marketing Plugin IDs from Freemius */
if ( !defined( 'WCPFC_PLUGIN_IDS' ) ) {
    define( 'WCPFC_PLUGIN_IDS', array(
        3495 => array(
            'marketing_title'        => esc_html__( 'Display Product Size Guide in Detail Page?', 'woocommerce-conditional-product-fees-for-checkout' ),
            'marketing_tooltip'      => esc_html__( 'This will give you premium plugin on discount!', 'woocommerce-conditional-product-fees-for-checkout' ),
            'marketing_help_url'     => esc_url( admin_url( '/edit.php?post_type=size-chart' ) ),
            'marketing_button_text'  => esc_html__( 'Start Reducing Returns Today', 'woocommerce-conditional-product-fees-for-checkout' ),
            'marketing_plugin_path'  => 'size-chart-for-woocommerce-premium/size-chart-for-woocommerce.php',
            'marketing_coupon_code'  => 82898,
            'marketing_feature_list' => array(esc_html__( 'Reduce returns by eliminating size-related issues.', 'woocommerce-conditional-product-fees-for-checkout' ), esc_html__( 'Minimizes customer confusion about sizes and fittings.', 'woocommerce-conditional-product-fees-for-checkout' ), esc_html__( 'Customers can view size charts easily before making purchases.', 'woocommerce-conditional-product-fees-for-checkout' )),
        ),
        3379 => array(
            'marketing_title'        => esc_html__( 'Advanced Shipping Based on Weight', 'woocommerce-conditional-product-fees-for-checkout' ),
            'marketing_tooltip'      => esc_html__( 'This will give you premium plugin on discount!', 'woocommerce-conditional-product-fees-for-checkout' ),
            'marketing_help_url'     => esc_url( 'https://www.thedotstore.com/docs/docs/woocommerce-conditional-product-fees-for-checkout/' ),
            'marketing_button_text'  => esc_html__( 'Optimize Your Shipping Costs Today', 'woocommerce-conditional-product-fees-for-checkout' ),
            'marketing_plugin_path'  => 'advanced-flat-rate-shipping-for-woocommerce-premium/advanced-flat-rate-shipping-for-woocommerce.php',
            'marketing_coupon_code'  => 82896,
            'marketing_feature_list' => array(esc_html__( 'Charge accurate shipping fees based on product weight.', 'woocommerce-conditional-product-fees-for-checkout' ), esc_html__( 'Prevent overcharging or undercharging for shipping.', 'woocommerce-conditional-product-fees-for-checkout' ), esc_html__( 'Improve transparency and build customer trust at checkout.', 'woocommerce-conditional-product-fees-for-checkout' )),
        ),
        3790 => array(
            'marketing_title'        => esc_html__( 'Want a discount apply?', 'woocommerce-conditional-product-fees-for-checkout' ),
            'marketing_tooltip'      => esc_html__( 'This will give you premium plugin on discount!', 'woocommerce-conditional-product-fees-for-checkout' ),
            'marketing_help_url'     => esc_url( 'https://www.thedotstore.com/docs/docs/woocommerce-conditional-product-fees-for-checkout/' ),
            'marketing_button_text'  => esc_html__( 'Start Increasing Sales with Smart Discounts Today', 'woocommerce-conditional-product-fees-for-checkout' ),
            'marketing_plugin_path'  => 'woocommerce-conditional-discount-rules-for-checkout-premium/woo-conditional-discount-rules-for-checkout.php',
            'marketing_coupon_code'  => 82899,
            'marketing_feature_list' => array(esc_html__( 'Offer percentage, fixed, or BOGO discounts based on conditions.', 'woocommerce-conditional-product-fees-for-checkout' ), esc_html__( 'Encourage bulk purchases with quantity-based discounts.', 'woocommerce-conditional-product-fees-for-checkout' ), esc_html__( 'Automatically apply discounts to streamline the checkout process.', 'woocommerce-conditional-product-fees-for-checkout' )),
        ),
    ) );
}
/** Dotstore Marketing Plugin IDs from Freemius */
if ( !defined( 'WCPFC_OTHER_PLUGIN_IDS' ) ) {
    define( 'WCPFC_OTHER_PLUGIN_IDS', array(
        'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers',
        'woo-extra-flat-rate',
        'hide-shipping-method-for-woocommerce',
        'woo-product-attachment',
        'woo-advanced-product-size-chart',
        'local-pickup-for-woocommerce',
        'woo-conditional-discount-rules-for-checkout',
        'conditional-payments',
        'woo-checkout-for-digital-goods',
        'banner-management-for-woocommerce',
        'min-and-max-quantity-for-woocommerce',
        'woo-ecommerce-tracking-for-google-and-facebook',
        'mass-pagesposts-creator',
        'linked-variation',
        'woo-shipping-display-mode'
    ) );
}