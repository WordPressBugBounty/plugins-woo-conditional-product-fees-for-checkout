<?php

/**
 * Handles plugin global settings
 * 
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @since   3.9.3
 */
// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';
// Function for free plugin content
function wcpfc_free_global_settings_content() {
    $allowed_tooltip_html = wp_kses_allowed_html( 'post' )['span'];
    ?>
    <div class="wcpfc-mastersettings wcpfc-table-tooltip wcpfc-upgrade-pro-to-unlock">
        <div class="mastersettings-title">
            <h2><?php 
    esc_html_e( 'Global Settings', 'woocommerce-conditional-product-fees-for-checkout' );
    ?><div class="wcpfc-pro-label"></div></h2>
        </div>
        <table class="table-mastersettings form-table table-outer" cellpadding="0" cellspacing="0">
            <tbody>
                <tr valign="top" id="enable_coupon_fee">
                    <th class="table-whattodo fr-1">
                        <label for="chk_move_menu_under_wc">
                            <?php 
    esc_html_e( 'Move main menu under WooCommerce > Extra Fees', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>
                            <?php 
    echo wp_kses( wc_help_tip( esc_html__( 'If enabled, the main menu of the plugin will be moved to the "WooCommerce > Extra Fees" section.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
        'span' => $allowed_tooltip_html,
    ) );
    ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" name="chk_move_menu_under_wc" id="chk_move_menu_under_wc" value="">
                    </td>
                </tr>
                <tr valign="top" id="enable_coupon_fee">
                    <th class="table-whattodo fr-1">
                        <label for="chk_enable_coupon_fee">
                            <?php 
    echo sprintf( esc_html__( 'Remove fees once a %d%% discount applies.', 'woocommerce-conditional-product-fees-for-checkout' ), 100 );
    ?>
                            <?php 
    echo wp_kses( wc_help_tip( sprintf( esc_html__( 'When this option is enabled, the fee will be removed once a %d%% discount applies to the cart.', 'woocommerce-conditional-product-fees-for-checkout' ), 100 ) ), array(
        'span' => $allowed_tooltip_html,
    ) );
    ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" name="chk_enable_coupon_fee" id="chk_enable_coupon_fee" value="">
                    </td>
                </tr>
                <tr valign="top" id="enable_custom_fun">
                    <th class="table-whattodo fr-1">
                        <label for="chk_enable_custom_fun">
                            <?php 
    esc_html_e( 'Display all fees in one label', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>
                            <?php 
    echo wp_kses( wc_help_tip( esc_html__( 'When this option is enabled, all fees will be combined into a single fee.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
        'span' => $allowed_tooltip_html,
    ) );
    ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" name="chk_enable_custom_fun" id="chk_enable_custom_fun" value="">
                    </td>
                </tr>
                <tr valign="top" id="enable_all_fee_tax" class="wcpffc_merged_fee_settings">
                    <th class="table-whattodo fr-1">
                        <label for="chk_enable_all_fee_tax">
                            <?php 
    esc_html_e( 'Merge all fee with taxable', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>
                            <?php 
    echo wp_kses( wc_help_tip( esc_html__( 'If enabled, it will make this one merged fee calculated as taxable.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
        'span' => $allowed_tooltip_html,
    ) );
    ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" name="chk_enable_all_fee_tax" id="chk_enable_all_fee_tax" value="">
                    </td>
                </tr>
                <tr valign="top" id="enable_all_fee_tooltip" class="wcpffc_merged_fee_settings">
                    <th class="table-whattodo fr-1">
                        <label for="chk_enable_all_fee_tooltip">
                            <?php 
    esc_html_e( 'Merge all fee tooltip', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>
                            <?php 
    echo wp_kses( wc_help_tip( esc_html__( 'Enable this if you want to add a tooltip to the merged fee label.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
        'span' => $allowed_tooltip_html,
    ) );
    ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" name="chk_enable_all_fee_tooltip" id="chk_enable_all_fee_tooltip" value="">
                    </td>
                </tr>
                <tr valign="top" id="enable_all_fee_tooltip_text" class="wcpffc_merged_fee_settings">
                    <th class="table-whattodo fr-1">
                        <label for="chk_enable_all_fee_tooltip_text">
                            <?php 
    esc_html_e( 'Merge all fee tooltip text', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>
                            <?php 
    echo wp_kses( wc_help_tip( esc_html__( 'Add your own tooltip text that will apply to the merged fee label.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
        'span' => $allowed_tooltip_html,
    ) );
    ?>        
                        </label>
                    </th>
                    <td>
                        <input type="text" name="chk_enable_all_fee_tooltip_text" id="chk_enable_all_fee_tooltip_text" value="" />
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <span class="button-primary" id="save_master_settings" name="save_master_settings">
                            <?php 
    esc_html_e( 'Save Settings', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>
                        </span>
                    </th>
                </tr>
            </tbody>
        </table>
    </div>
    <?php 
}

?>
<div class="wcpfc-section-left">
    <?php 
wcpfc_free_global_settings_content();
?>
</div>
</div>
</div>
</div>
</div>
