<?php
/**
 * Provide HTML for Optional Fee on checkout page
 *
 * @link       https://www.thedotstore.com/
 * @since      1.0.0
 *
 * @package    Woocommerce_Conditional_Product_Fees
 * @subpackage Woocommerce_Conditional_Product_Fees/public/partials
 */

defined( 'ABSPATH' ) || exit;

// Check if block is active on checkout or cart page
$is_checkout_has_block = ( 'checkout' === $page || 'cart' === $page ) ? wcpfc_pro()->is_wc_has_block( $page ) : false; //phpcs:ignore

// Ensure WooCommerce session is available and retrieve fees IDs
$fees_ids = ( class_exists( 'WooCommerce' ) && WC()->session ) ? WC()->session->get( 'fees_ids' ) : array();

// If $fees_ids is not an array, initialize it as an empty array
if ( ! is_array( $fees_ids ) ) { $fees_ids = array(); }

if ( isset($optional_fee_data) && !empty($optional_fee_data) ) {

    $section_title = apply_filters( 'wcpfc_optional_fee_text', esc_html__( 'Optional fee(s)', 'woocommerce-conditional-product-fees-for-checkout' ) );

    if( $is_checkout_has_block ) {
        // Block checkout HTML structure
        ?>
        <div class="block-cart-checkout-wrapper">
        
        <?php
        $display_title = true;
        foreach( $optional_fee_data as $optional_fee ) { //phpcs:ignore

            $getOptionalFeesCartPage = get_post_meta( $optional_fee['fee_id'], 'optional_fees_in_cart', true );
            $fee_show_on_checkout_only	= get_post_meta( $optional_fee['fee_id'], 'fee_show_on_checkout_only', true ) ? get_post_meta( $optional_fee['fee_id'], 'fee_show_on_checkout_only', true ) : '';

            if ( is_checkout() || ( is_cart() && empty($fee_show_on_checkout_only) ) ){ ?>
            <?php if ( $display_title ) { ?>
            <h2 class="wc-block-components-title wc-block-components-checkout-optional_fee__title" aria-hidden="true"><?php echo esc_html( $section_title ); ?></h2>
            <?php $display_title = false; } ?>
            <div class="wp-block-woocommerce-checkout-optional-fee-block">
                <div class="wp-block-woocommerce-checkout-order-summary-shipping-block wc-block-components-totals-wrapper">
                    <div class="wc-block-components-totals-shipping">
                        <div class="wc-block-components-totals-item">
                        <?php if( 'checkbox' === $optional_fee['fee_checked_type'] || empty( $optional_fee['fee_checked_type'] ) ) { ?>
                            <input type="checkbox" class="input-checkbox" name="wcpfc_optional_fee[]" <?php checked( 'on', $optional_fee['fee_checked'] ); ?> id="fees_<?php echo esc_attr( $optional_fee['fee_id'] ); ?>" value="<?php echo esc_attr( $optional_fee['fee_id'] ); ?>" />
                        <?php } elseif( 'dropdown' === $optional_fee['fee_checked_type'] ) { ?>
                            <?php 
                            // Set the selected value based on the condition
                            $selected_value = ( 'on' === $optional_fee['fee_checked'] ) ? 'yes' : 'no';
                            $option_values = apply_filters( 'wcpfc_optional_fee_dropdown_array_value', array( 
                                'yes' => esc_html__( 'Yes', 'woocommerce-conditional-product-fees-for-checkout' ), 
                                'no' => esc_html__( 'No', 'woocommerce-conditional-product-fees-for-checkout' )
                            ) ); 
                            ?>
                            <select class="input-dropdown" data-value="<?php echo esc_attr( $optional_fee['fee_id'] ); ?>">
                                <?php foreach( $option_values as $option_value => $option_text ){ ?>
                                    <option value="<?php echo esc_attr($option_value); ?>" <?php selected( $selected_value, $option_value ); ?>><?php echo esc_html( $option_text ); ?></option>
                                <?php } ?>
                            </select>
                        <?php } elseif( 'radio-button' === $optional_fee['fee_checked_type'] ) { ?>
                            <?php 
                            // Set the selected radio value based on the condition
                            $radio_checked_value = ( 'on' === $optional_fee['fee_checked'] ) ? 'yes' : 'no';
                            $radio_values = apply_filters( 'wcpfc_optional_fee_radio_array_value', array( 
                                'yes' => esc_html__( 'Yes', 'woocommerce-conditional-product-fees-for-checkout' ), 
                                'no' => esc_html__( 'No', 'woocommerce-conditional-product-fees-for-checkout' )
                            ) ); 
                            ?>
                            <?php foreach( $radio_values as $radio_value => $radio_text ){ 
                                // Set the data-value for 'yes', leave it empty for 'no'
                                $data_value = ( $radio_value === 'yes' ) ? esc_attr( $optional_fee['fee_id'] ) : ''; ?>
                                <label>
                                    <input type="radio" class="input-radio" name="wcpfc_optional_fee_<?php echo esc_attr( $optional_fee['fee_id'] ); ?>" value="<?php echo esc_attr( $radio_value ); ?>" <?php checked( $radio_checked_value, $radio_value); ?> data-value="<?php echo esc_attr( $data_value ); ?>" />
                                    <?php echo esc_html( $radio_text ); ?>
                                </label>
                            <?php } ?>
                        <?php } ?>

                            <span class="wc-block-components-totals-item__label"><?php echo esc_html( $optional_fee['fee_title'] ); ?></span>
                            <div class="wc-block-components-totals-item__value"><strong><?php echo wp_kses_post(wc_price( $optional_fee['fee_cost']) ); ?></strong></div>
                            <?php if( $optional_fee['fee_description'] ) { ?>
                                <div class="wc-block-components-totals-item__description">
                                    <span class="wc-block-components-shipping-address"><?php echo esc_html( $optional_fee['fee_description'] ); ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="optional_fee_in_checkout_only" id="optional_fee_in_checkout_only" value="<?php echo esc_attr( $getOptionalFeesCartPage); ?>" />
            </div>
            <?php
            }
        } ?>
        </div> <?php
    } else {
        // Classic checkout HTML structure
        ?>
        <div class="optional_fee_container">
            
            <table class="shop_table"><?php 
                $display_title = true;
                foreach( $optional_fee_data as $optional_fee ) { //phpcs:ignore

                    $getOptionalFeesCartPage = get_post_meta( $optional_fee['fee_id'], 'optional_fees_in_cart', true );

                    $fee_show_on_checkout_only	= get_post_meta( $optional_fee['fee_id'], 'fee_show_on_checkout_only', true ) ? get_post_meta( $optional_fee['fee_id'], 'fee_show_on_checkout_only', true ) : '';

					if ( is_checkout() || ( is_cart() && empty($fee_show_on_checkout_only) ) ){ ?>
                        <?php if ( $display_title ) { ?>
                        <h3 id="optional_fee_heading"><?php echo esc_html( $section_title ); ?></h3>
                        <?php $display_title = false; } ?>
                        <tr class="optional_row"> <?php 
                            if ( 'checkbox' === $optional_fee['fee_checked_type'] || empty( $optional_fee['fee_checked_type'] ) ) { 
                                if ( isset( $getOptionalFeesCartPage ) && $getOptionalFeesCartPage !== 'on') {
                                    // For the checkout page, check if the fee is marked as "on".
                                    $is_checked = checked( 'on', $optional_fee['fee_checked'], false );
                                } else {
                                    // For AJAX requests, check if the fee ID is in the selected fees; otherwise, fallback to the default "checked" state.
                                    $is_checked = wcpfc_pro_public()->is_ajax_request() 
                                        ? ( in_array( (int) $optional_fee['fee_id'], array_map( 'intval', $fees_ids ), true ) ? 'checked="checked"' : '' )
                                        : checked( 'on', $optional_fee['fee_checked'], false );
                                } ?>
                                <th class="checbox_row">
                                    <input type="checkbox" class="input-checkbox" name="wef_fees_id_array_<?php echo esc_attr( $optional_fee['fee_id'] ); ?>[]" <?php echo esc_html($is_checked); ?> id="fees_<?php echo esc_attr( $optional_fee['fee_id'] ); ?>" value="<?php echo esc_attr( $optional_fee['fee_id'] ); ?>" />
                                    <label class="checkbox"><span class="title_fee"><?php echo esc_html( $optional_fee['fee_title'] ); ?></span></label>
                                    <?php if ( !empty( $optional_fee['fee_description'] ) ){ ?>
                                        <p class="optional_fee_description"><?php echo esc_html( $optional_fee['fee_description'] ); ?></p>
                                    <?php } ?>
                                </th>
                                <td>
                                    <p class="optional_fee_cost"><?php echo wp_kses_post(wc_price( $optional_fee['fee_cost']) ); ?></p>
                                </td>
                            <?php
                            } elseif ( 'dropdown' === $optional_fee['fee_checked_type'] ) {

                                if ( isset( $getOptionalFeesCartPage ) && $getOptionalFeesCartPage !== 'on') {
                                    // For the checkout page, determine if the fee is checked or not.
                                    $selected_value = !empty( $optional_fee['fee_checked'] ) ? 'yes' : 'no';
                                } else {
                                    // For AJAX requests, determine if the fee ID is in the selected fees or use the default fee_checked value.
                                    $selected_value = wcpfc_pro_public()->is_ajax_request() 
                                        ? ( in_array( (int) $optional_fee['fee_id'], array_map( 'intval', $fees_ids ), true ) ? 'yes' : 'no' ) 
                                        : ( !empty( $optional_fee['fee_checked'] ) ? 'yes' : 'no' );
                                }
                                

                                $option_values = apply_filters( 'wcpfc_optional_fee_dropdown_array_value', array( 
                                    'yes' => esc_html__( 'Yes', 'woocommerce-conditional-product-fees-for-checkout' ), 
                                    'no' => esc_html__( 'No', 'woocommerce-conditional-product-fees-for-checkout' )
                                ) ); ?>
                                <th class="dropdown_row">
                                    <select class="input-dropdown" data-value="<?php echo esc_attr( $optional_fee['fee_id'] ); ?>">
                                        <?php foreach ( $option_values as $option_value => $option_text ) { ?>
                                            <option value="<?php echo esc_attr($option_value); ?>" <?php selected( $selected_value, $option_value ); ?>><?php echo esc_html($option_text); ?></option>
                                        <?php } ?>
                                    </select>
                                    <label class="checkbox"><span class="title_fee"><?php echo esc_html( $optional_fee['fee_title'] ); ?></span></label>
                                    <?php if ( !empty( $optional_fee['fee_description'] ) ){ ?>
                                        <p class="optional_fee_description"><?php echo esc_html( $optional_fee['fee_description'] ); ?></p>
                                    <?php } ?>
                                </th>
                                <td>
                                    <p class="optional_fee_cost"><?php echo wp_kses_post( wc_price( $optional_fee['fee_cost'] ) ); ?></p>
                                </td> <?php 
                            } elseif ( 'radio-button' === $optional_fee['fee_checked_type'] ) { 
                                if ( isset( $getOptionalFeesCartPage ) && $getOptionalFeesCartPage !== 'on') {
                                    // Determine if the radio button should be checked on the checkout page.
                                    $radio_checked_value = !empty( $optional_fee['fee_checked'] ) ? 'yes' : 'no';
                                } else {
                                    // Handle AJAX requests or fallback to default radio checked value.
                                    $radio_checked_value = wcpfc_pro_public()->is_ajax_request() 
                                        ? ( in_array( (int) $optional_fee['fee_id'], array_map( 'intval', $fees_ids ), true ) ? 'yes' : 'no' ) 
                                        : ( !empty( $optional_fee['fee_checked'] ) ? 'yes' : 'no' );
                                }
                                // Example values for radio buttons (yes/no options)
                                $radio_values = apply_filters( 'wcpfc_optional_fee_radio_array_value', array(
                                    'yes' => esc_html__( 'Yes', 'woocommerce-conditional-product-fees-for-checkout' ), 
                                    'no' => esc_html__( 'No', 'woocommerce-conditional-product-fees-for-checkout' )
                                ) ); ?>
                                <th class="radio_row">
                                    <?php foreach( $radio_values as $radio_value => $radio_text ){ 
                                        $radio_fee_value = $radio_value === 'yes' ? $optional_fee['fee_id'] : '';?>
                                        <input type="radio" class="input-radio" 
                                            name="wef_fees_id_array_<?php echo esc_attr( $optional_fee['fee_id'] ); ?>[]" 
                                            id="fees_<?php echo esc_attr($optional_fee['fee_id'] . '_' . $radio_value); ?>" 
                                            value="<?php echo esc_attr($radio_fee_value); ?>" 
                                            <?php checked( $radio_checked_value, $radio_value ); ?> />
                                        <label for="fees_<?php echo esc_attr($optional_fee['fee_id'] . '_' . $radio_value); ?>"><?php echo esc_html($radio_text); ?></label>
                                    <?php } ?>
                                    <label class="checkbox"><span class="title_fee"><?php echo esc_html( $optional_fee['fee_title'] ); ?></span></label>
                                    <?php if ( !empty( $optional_fee['fee_description'] ) ){ ?>
                                        <p class="optional_fee_description"><?php echo esc_html( $optional_fee['fee_description'] ); ?></p>
                                    <?php } ?>
                                </th>
                                <td>
                                    <p class="optional_fee_cost"><?php echo wp_kses_post( wc_price( $optional_fee['fee_cost'] ) ); ?></p>
                                </td>
                            <?php } ?>
                        </tr><?php 
                    } ?>
                    <?php
                }?></table>
        </div>
        <?php
    }
}
