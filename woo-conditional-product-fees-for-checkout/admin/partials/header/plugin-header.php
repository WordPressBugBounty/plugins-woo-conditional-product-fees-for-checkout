<?php

/**
 * Handles plugin header
 * 
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @since   1.0.0
 */
// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
global $wcpffc_fs;
$version_label = '';
$plugin_slug = '';
$version_label = __( 'Free', 'woocommerce-conditional-product-fees-for-checkout' );
$plugin_slug = 'basic_extra_fee';
$plugin_name = __( 'WooCommerce Extra Fees', 'woocommerce-conditional-product-fees-for-checkout' );
$plugin_version = 'v' . WCPFC_PRO_PLUGIN_VERSION;
$current_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$whsm_pro_dashboard = ( isset( $current_page ) && 'wcpfc-pro-dashboard' === $current_page ? 'active' : '' );
$whsm_free_dashboard = ( isset( $current_page ) && 'wcpfc-upgrade-dashboard' === $current_page ? 'active' : '' );
$wcpfc_rules_list = ( isset( $current_page ) && 'wcpfc-pro-list' === $current_page ? 'active' : '' );
$wcpfc_settings_menu = ( isset( $current_page ) && ('wcpfc-pro-import-export' === $current_page || 'wcpfc-global-settings' === $current_page) ? 'active' : '' );
$wcpfc_get_started = ( isset( $current_page ) && 'wcpfc-pro-get-started' === $current_page ? 'active' : '' );
$wcpfc_import_export = ( isset( $current_page ) && 'wcpfc-pro-import-export' === $current_page ? 'active' : '' );
$wcpfc_global_settings = ( isset( $current_page ) && 'wcpfc-global-settings' === $current_page ? 'active' : '' );
$wcpfc_account_page = ( isset( $current_page ) && 'wcpfc-pro-list-account' === $current_page ? 'active' : '' );
$whsm_display_submenu = ( !empty( $wcpfc_settings_menu ) && 'active' === $wcpfc_settings_menu ? 'display:inline-block' : 'display:none' );
$wcpfc_admin_object = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Admin('', '');
?>
<div id="dotsstoremain">
    <div class="all-pad">
        <?php 
$wcpfc_admin_object->wcpfc_get_promotional_bar( $plugin_slug );
?>
        <div class="dotstore_plugin_page_loader"></div>
        <header class="dots-header">
            <div class="dots-plugin-details">
                <div class="dots-header-left">
                    <div class="dots-logo-main">
                        <img src="<?php 
echo esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/wc-conditional-product-fees.png' );
?>">
                    </div>
                    <div class="plugin-name">
                        <div class="title"><?php 
esc_html_e( $plugin_name, 'woocommerce-conditional-product-fees-for-checkout' );
?></div>
                    </div>
                    <span class="version-label <?php 
echo esc_attr( $plugin_slug );
?>"><?php 
esc_html_e( $version_label, 'woocommerce-conditional-product-fees-for-checkout' );
?></span>
                    <span class="version-number"><?php 
echo esc_html_e( $plugin_version, 'woocommerce-conditional-product-fees-for-checkout' );
?></span>
                </div>
                <div class="dots-header-right">
                    <div class="button-dots">
                        <a target="_blank" href="<?php 
echo esc_url( 'http://www.thedotstore.com/support/' );
?>"><?php 
esc_html_e( 'Support', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
                    </div>
                    <div class="button-dots">
                        <a target="_blank" href="<?php 
echo esc_url( 'https://www.thedotstore.com/feature-requests/' );
?>"><?php 
esc_html_e( 'Suggest', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
                    </div>
                    <?php 
$plugin_help_url = 'https://docs.thedotstore.com/category/191-premium-plugin-settings';
if ( strpos( current_filter(), 'fs_connect' ) !== false ) {
    $plugin_help_url = 'https://docs.thedotstore.com/article/62-how-to-installing-and-activating-an-thedotstore-plugin';
}
?>
                	<div class="button-dots <?php 
echo ( wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code() ? '' : 'last-link-button' );
?>">
                        <a target="_blank" href="<?php 
echo esc_url( $plugin_help_url );
?>"><?php 
esc_html_e( 'Help', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
                    </div>
                    <div class="button-dots">
                        <?php 
?>
                            <a class="dots-upgrade-btn" target="_blank" href="javascript:void(0);"><?php 
esc_html_e( 'Upgrade Now', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
                            <?php 
?>
                    </div>
                </div>
            </div>
            <div class="dots-bottom-menu-main">
                <div class="dots-menu-main">
                    <nav>
                        <ul>
                            <li>
                                <a class="dotstore_plugin <?php 
echo esc_attr( $whsm_pro_dashboard );
?>" href="<?php 
echo esc_url( add_query_arg( array(
    'page' => 'wcpfc-pro-dashboard',
), admin_url( 'admin.php' ) ) );
?>"><?php 
esc_html_e( 'Dashboard', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
                            </li>
                            <li>
                                <a class="dotstore_plugin <?php 
echo esc_attr( $wcpfc_rules_list );
?>" href="<?php 
echo esc_url( add_query_arg( array(
    'page' => 'wcpfc-pro-list',
), admin_url( 'admin.php' ) ) );
?>"><?php 
esc_html_e( 'Manage Fees', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
                            </li>
                            <li>
                                <a class="dotstore_plugin <?php 
echo esc_attr( $wcpfc_settings_menu );
?>" href="<?php 
echo esc_url( add_query_arg( array(
    'page' => 'wcpfc-global-settings',
), admin_url( 'admin.php' ) ) );
?>"><?php 
esc_html_e( 'Settings', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
                            </li>
                            <?php 
if ( wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code() ) {
    ?>
                                <li>
                                    <a class="dotstore_plugin <?php 
    echo esc_attr( $wcpfc_account_page );
    ?>" href="<?php 
    echo esc_url( $wcpffc_fs->get_account_url() );
    ?>"><?php 
    esc_html_e( 'License', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></a>
                                </li>
                                <?php 
} else {
    ?>
                                <li>
                                    <a class="dotstore_plugin dots_get_premium <?php 
    echo esc_attr( $whsm_free_dashboard );
    ?>" href="<?php 
    echo esc_url( add_query_arg( array(
        'page' => 'wcpfc-upgrade-dashboard',
    ), admin_url( 'admin.php' ) ) );
    ?>"><?php 
    esc_html_e( 'Get Premium', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></a>
                                </li>
                                <?php 
}
?>
                        </ul>
                    </nav>
                </div>
                <div class="dots-getting-started">
                    <a href="<?php 
echo esc_url( add_query_arg( array(
    'page' => 'wcpfc-pro-get-started',
), admin_url( 'admin.php' ) ) );
?>" class="<?php 
echo esc_attr( $wcpfc_get_started );
?>"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12 4.75a7.25 7.25 0 100 14.5 7.25 7.25 0 000-14.5zM3.25 12a8.75 8.75 0 1117.5 0 8.75 8.75 0 01-17.5 0zM12 8.75a1.5 1.5 0 01.167 2.99c-.465.052-.917.44-.917 1.01V14h1.5v-.845A3 3 0 109 10.25h1.5a1.5 1.5 0 011.5-1.5zM11.25 15v1.5h1.5V15h-1.5z" fill="#a0a0a0"></path></svg></a>
                </div>
            </div>
        </header>
        <!-- Upgrade to pro popup -->
        <?php 
if ( !(wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code()) ) {
    require_once WCPFC_PRO_PLUGIN_DIR_PATH . 'admin/partials/dots-upgrade-popup.php';
}
?>
        <div class="dots-settings-inner-main">
            <div class="dots-settings-left-side">
                <div class="dotstore-submenu-items" style="<?php 
echo esc_attr( $whsm_display_submenu );
?>">
                    <ul>
                        <li><a class="<?php 
echo esc_attr( $wcpfc_global_settings );
?>" href="<?php 
echo esc_url( add_query_arg( array(
    'page' => 'wcpfc-global-settings',
), admin_url( 'admin.php' ) ) );
?>"><?php 
esc_html_e( 'Global Settings', 'woocommerce-conditional-product-fees-for-checkout' );
?></a></li>
                        <li><a class="<?php 
echo esc_attr( $wcpfc_import_export );
?>" href="<?php 
echo esc_url( add_query_arg( array(
    'page' => 'wcpfc-pro-import-export',
), admin_url( 'admin.php' ) ) );
?>"><?php 
esc_html_e( 'Import / Export', 'woocommerce-conditional-product-fees-for-checkout' );
?></a></li>
                    </ul>
                </div>
                <hr class="wp-header-end" />
                