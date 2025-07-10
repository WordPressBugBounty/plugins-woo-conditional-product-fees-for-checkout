<?php

/**
 * Handles plugin rules settings
 * 
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @since   3.9.3
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';
$wcpfc_admin_object = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Admin('', '');
$wcpfc_object = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro('', '');
$allowed_tooltip_html = wp_kses_allowed_html( 'post' )['span'];
if ( isset( $_REQUEST['action'], $_REQUEST['id'] ) && 'edit' === $_REQUEST['action'] ) {
    $get_wpnonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $get_retrieved_nonce = ( isset( $get_wpnonce ) ? sanitize_text_field( wp_unslash( $get_wpnonce ) ) : '' );
    $request_post_id = sanitize_text_field( $_REQUEST['id'] );
    $getnonce = wp_verify_nonce( $get_retrieved_nonce, 'edit_' . $request_post_id );
    $btnValue = __( 'Update Fee', 'woocommerce-conditional-product-fees-for-checkout' );
    $fee_title = __( get_the_title( $request_post_id ), 'woocommerce-conditional-product-fees-for-checkout' );
    $getFeesCost = __( get_post_meta( $request_post_id, 'fee_settings_product_cost', true ), 'woocommerce-conditional-product-fees-for-checkout' );
    $getFeesType = __( get_post_meta( $request_post_id, 'fee_settings_select_fee_type', true ), 'woocommerce-conditional-product-fees-for-checkout' );
    $wcpfc_tooltip_desc = __( get_post_meta( $request_post_id, 'fee_settings_tooltip_desc', true ), 'woocommerce-conditional-product-fees-for-checkout' );
    $wcpfc_price_message_on_cart = __( get_post_meta( $request_post_id, 'fee_settings_price_message_on_cart', true ), 'woocommerce-conditional-product-fees-for-checkout' );
    $getFeesStartDate = get_post_meta( $request_post_id, 'fee_settings_start_date', true );
    $getFeesEndDate = get_post_meta( $request_post_id, 'fee_settings_end_date', true );
    $getFeesTaxable = __( get_post_meta( $request_post_id, 'fee_settings_select_taxable', true ), 'woocommerce-conditional-product-fees-for-checkout' );
    $getFeesTaxableType = __( get_post_meta( $request_post_id, 'fee_settings_taxable_type', true ), 'woocommerce-conditional-product-fees-for-checkout' );
    $displayFeesSingleProduct = __( get_post_meta( $request_post_id, 'display_fees_in_product_page', true ), 'woocommerce-conditional-product-fees-for-checkout' );
    $ds_time_from = get_post_meta( $request_post_id, 'ds_time_from', true );
    $ds_time_to = get_post_meta( $request_post_id, 'ds_time_to', true );
    $fee_show_on_checkout_only = get_post_meta( $request_post_id, 'fee_show_on_checkout_only', true );
    $fees_on_cart_total = get_post_meta( $request_post_id, 'fees_on_cart_total', true );
    $ds_select_day_of_week = get_post_meta( $request_post_id, 'ds_select_day_of_week', true );
    if ( is_serialized( $ds_select_day_of_week ) ) {
        $ds_select_day_of_week = maybe_unserialize( $ds_select_day_of_week );
    } else {
        $ds_select_day_of_week = $ds_select_day_of_week;
    }
    $getFeesStatus = get_post_status( $request_post_id );
    $productFeesArray = get_post_meta( $request_post_id, 'product_fees_metabox', true );
    if ( is_serialized( $productFeesArray ) ) {
        $productFeesArray = maybe_unserialize( $productFeesArray );
    } else {
        $productFeesArray = $productFeesArray;
    }
} else {
    $request_post_id = '';
    $btnValue = __( 'Save Fee', 'woocommerce-conditional-product-fees-for-checkout' );
    $fee_title = '';
    $getFeesCost = '';
    $getFeesType = '';
    $wcpfc_tooltip_desc = '';
    $wcpfc_price_message_on_cart = '';
    $getFeesStartDate = '';
    $getFeesEndDate = '';
    $displayFeesSingleProduct = '';
    $getFeesTaxable = '';
    $getFeesTaxableType = '';
    $getFeesOptional = '';
    $getFeesOptionalType = '';
    $getFeesOptionalDetails = '';
    $getFeesStatus = '';
    $ds_time_from = "";
    $ds_time_to = "";
    $fee_show_on_checkout_only = "";
    $fees_on_cart_total = "";
    $ds_select_day_of_week = "";
    $productFeesArray = array();
}
$sm_status = ( !empty( $getFeesStatus ) && 'publish' === $getFeesStatus || empty( $getFeesStatus ) ? 'checked' : '' );
$get_weight_unit = get_option( 'woocommerce_weight_unit' );
$get_weight_unit = ( isset( $get_weight_unit ) && !empty( $get_weight_unit ) ? $get_weight_unit : 'kg' );
$weight_unit = '(' . $get_weight_unit . ')';
?>
<div class="wcpfc-plugin-modal-main">
	<div class="wcpfc-plugin-modal-outer">
		<div class="wcpfc-plugin-modal-inner">
			<div class="wcpfc-plugin-modal-wrap">
				<div class="wcpfc-plugin-modal-header">
					<span class="dashicons dashicons-no-alt modal-close-btn"></span>
				</div>
				<div class="wcpfc-plugin-modal-body">
					<h3 class="wcpfc-modal-title"><?php 
esc_html_e( 'Oops! It looks like you have entered a negative value in the price box.', 'woocommerce-conditional-product-fees-for-checkout' );
?></h3>
					<p><?php 
echo wp_kses( __( 'Are you perhaps looking to offer a discount to your customers instead? Our "<strong>Discount Plugin</strong>" can help you easily and best way to set up and manage discounts for your products or services. It\'s a quick and easy solution that can save you time and hassle.', 'woocommerce-conditional-product-fees-for-checkout' ), array(
    'strong' => array(),
) );
?></p>
					<p><?php 
esc_html_e( 'Simply click the link below to learn more about our Discount Plugin and how it can benefit your business.', 'woocommerce-conditional-product-fees-for-checkout' );
?></p>
				</div>
				<div class="wcpfc-plugin-modal-footer">
					<a class="wcpfc-modal-more-btn" href="<?php 
echo esc_url( 'https://www.thedotstore.com/woocommerce-conditional-discount-rules-for-checkout/' );
?>" target="_blank"><?php 
esc_html_e( 'Learn More', 'woocommerce-conditional-product-fees-for-checkout' );
?></a>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="wcpfc-section-left">
	<div class="wcpfc-main-table res-cl wcpfc-add-rule-page">
		<?php 
wp_nonce_field( 'wcpfc_pro_product_fees_conditions_values_ajax_action', 'wcpfc_pro_product_fees_conditions_values_ajax' );
?>
		<h2><?php 
esc_html_e( 'Fee Configuration', 'woocommerce-conditional-product-fees-for-checkout' );
?></h2>
		<form method="POST" name="feefrm" action="">
			<?php 
wp_nonce_field( 'wcpfc_pro_fees_conditions_save_action', 'wcpfc_pro_fees_conditions_save' );
?>
			<input type="hidden" name="post_type" value="wc_conditional_fee">
			<input type="hidden" name="fee_post_id" value="<?php 
echo esc_attr( $request_post_id );
?>">
			<div class="wcpfc-rule-general-settings wcpfc-table-tooltip">
				<table class="form-table table-outer product-fee-table">
					<tbody>
						<tr valign="top">
							<th class="titledesc" scope="row">
		                        <label for="onoffswitch">
		                        	<?php 
esc_html_e( 'Status', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                        	<?php 
echo wp_kses( wc_help_tip( esc_html__( 'Enable or Disable this fee using this button (This fee will be visible to customers only if it is enabled).', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
	                        	</label>
							</th>
							<td class="forminp">
								<label class="switch">
									<input type="checkbox" name="fee_settings_status"
									       value="on" <?php 
echo esc_attr( $sm_status );
?>>
									<div class="slider round"></div>
								</label>
							</td>
						</tr>

						<tr valign="top">
							<th class="titledesc" scope="row">
		                        <label for="fee_settings_product_fee_title">
		                            <?php 
esc_html_e( 'Fee Title', 'woocommerce-conditional-product-fees-for-checkout' );
?>
									<span class="required-star">*</span>
									<?php 
echo wp_kses( wc_help_tip( esc_html__( 'This name will be visible to the customer at the time of checkout. This should convey the purpose of the fee you are applying to the order. For example "Deposit Fee", "Night Charges", "Insurance Fee", etc.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                        </label>
		                    </th>
							<td class="forminp">
								<input type="text" name="fee_settings_product_fee_title" class="text-class" id="fee_settings_product_fee_title" value="<?php 
echo ( isset( $fee_title ) ? esc_attr( $fee_title ) : '' );
?>" required="1" placeholder="<?php 
esc_attr_e( 'Enter fee title', 'woocommerce-conditional-product-fees-for-checkout' );
?>">
							</td>
						</tr>
						<tr valign="top">
							<th class="titledesc" scope="row">
								<label for="fee_settings_select_fee_type">
		                            <?php 
esc_html_e( 'Fee Type', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                            <?php 
echo wp_kses( wc_help_tip( esc_html__( 'You can apply extra fees as a fixed or percentage price.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                        </label>
							</th>
							<td class="forminp">
								<select name="fee_settings_select_fee_type" id="fee_settings_select_fee_type" class="">
									<option value="fixed" <?php 
echo ( isset( $getFeesType ) && 'fixed' === $getFeesType ? 'selected="selected"' : '' );
?>><?php 
esc_html_e( 'Fixed', 'woocommerce-conditional-product-fees-for-checkout' );
?></option>
									<option value="percentage" <?php 
echo ( isset( $getFeesType ) && 'percentage' === $getFeesType ? 'selected="selected"' : '' );
?>><?php 
esc_html_e( 'Percentage', 'woocommerce-conditional-product-fees-for-checkout' );
?></option>
									<option value="both" <?php 
echo ( isset( $getFeesType ) && 'both' === $getFeesType ? 'selected="selected"' : '' );
?>><?php 
esc_html_e( 'Percentage + Fee', 'woocommerce-conditional-product-fees-for-checkout' );
?></option>
								</select>
								<span class="fees_on_cart_total_wrap">
									<input type="checkbox" name="fees_on_cart_total" id="fees_on_cart_total" value="on" <?php 
checked( $fees_on_cart_total, 'on' );
?>/>
									<label for="fees_on_cart_total"><strong><?php 
esc_html_e( 'Apply fee on cart total', 'woocommerce-conditional-product-fees-for-checkout' );
?></strong></label>
								</span>
							</td>
						</tr>
						<tr valign="top">
							<th class="titledesc" scope="row">
		                        <label for="fee_settings_product_cost">
		                            <?php 
esc_html_e( 'Fee Amount', 'woocommerce-conditional-product-fees-for-checkout' );
?>
									<span class="required-star">*</span>
									<?php 
echo wp_kses( wc_help_tip( esc_html__( 'You can add a fixed/percentage fee based on the selection above.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                        </label>
		                    </th>
							<td class="forminp">
								<div class="product_cost_left_div">
									<?php 
if ( isset( $getFeesType ) && 'percentage' === $getFeesType ) {
    ?>
										<input type="number" name="fee_settings_product_cost" required="1" class="text-class" id="fee_settings_product_cost" value="<?php 
    echo ( isset( $getFeesCost ) ? esc_attr( $getFeesCost ) : '' );
    ?>" placeholder="%" autocomplete="off" step="0.01" />
									<?php 
} else {
    ?>
										<input type="text" name="fee_settings_product_cost" required="1" class="text-class" id="fee_settings_product_cost" value="<?php 
    echo ( isset( $getFeesCost ) ? esc_attr( $getFeesCost ) : '' );
    ?>" placeholder="<?php 
    echo esc_attr( get_woocommerce_currency_symbol() );
    ?>" autocomplete="off" />
									<?php 
}
?>
								</div>
								<?php 
?>
								<a href="javascript:void(0);" class="wcpffc_chk_advanced_settings">Advance settings</a>
							</td>
						</tr>
						<tr valign="top" class="wcpffc_advanced_setting_section">
							<th class="titledesc" scope="row">
		                        <label for="fee_settings_start_date">
		                            <?php 
esc_html_e( 'Start Date', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                            <?php 
echo wp_kses( wc_help_tip( esc_html__( 'Select the Start Date on which you want to enable the fee.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                        </label>
							</th>
							<td class="forminp">
								<input type="text" name="fee_settings_start_date" class="text-class" id="fee_settings_start_date" value="<?php 
echo ( isset( $getFeesStartDate ) ? esc_attr( $getFeesStartDate ) : '' );
?>" placeholder="<?php 
esc_attr_e( 'Select start date', 'woocommerce-conditional-product-fees-for-checkout' );
?>">
							</td>
						</tr>
						<tr valign="top" class="wcpffc_advanced_setting_section">
							<th class="titledesc" scope="row">
		                        <label for="fee_settings_end_date">
		                            <?php 
esc_html_e( 'End Date', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                            <?php 
echo wp_kses( wc_help_tip( esc_html__( 'Select the End Date on which you want to disable the fee.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                        </label>
							</th>
							<td class="forminp">
								<input type="text" name="fee_settings_end_date" class="text-class" id="fee_settings_end_date" value="<?php 
echo ( isset( $getFeesEndDate ) ? esc_attr( $getFeesEndDate ) : '' );
?>" placeholder="<?php 
esc_attr_e( 'Select end date', 'woocommerce-conditional-product-fees-for-checkout' );
?>">
							</td>
						</tr>
						<tr valign="top" class="wcpffc_advanced_setting_section">
							<th class="titledesc" scope="row">
								<label for="ds_select_day_of_week">
		                            <?php 
esc_html_e( 'Day Of The Week', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                            <?php 
$html = sprintf(
    '%s<a href=%s target="_blank">%s</a>',
    esc_html__( 'Select the days on which you want to enable fees on your website. This rule will match with the current day which is set by WordPress ', 'woocommerce-conditional-product-fees-for-checkout' ),
    esc_url( admin_url( 'options-general.php' ) ),
    esc_html__( 'Timezone', 'woocommerce-conditional-product-fees-for-checkout' )
);
echo wp_kses( wc_help_tip( wp_kses_post( $html ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                        </label>
							</th>
							<td class="forminp">
								<?php 
$select_day_week_array = array(
    'sun' => esc_html__( 'Sunday', 'woocommerce-conditional-product-fees-for-checkout' ),
    'mon' => esc_html__( 'Monday', 'woocommerce-conditional-product-fees-for-checkout' ),
    'tue' => esc_html__( 'Tuesday', 'woocommerce-conditional-product-fees-for-checkout' ),
    'wed' => esc_html__( 'Wednesday', 'woocommerce-conditional-product-fees-for-checkout' ),
    'thu' => esc_html__( 'Thursday', 'woocommerce-conditional-product-fees-for-checkout' ),
    'fri' => esc_html__( 'Friday', 'woocommerce-conditional-product-fees-for-checkout' ),
    'sat' => esc_html__( 'Saturday', 'woocommerce-conditional-product-fees-for-checkout' ),
);
?>
								<select name="ds_select_day_of_week[]" id="ds_select_day_of_week" class="ds_select_day_of_week wcpfc_select" multiple="multiple" placeholder='<?php 
echo esc_attr( 'Select day of the Week', 'woocommerce-conditional-product-fees-for-checkout' );
?>'>
									<?php 
foreach ( $select_day_week_array as $value => $name ) {
    ?>
										<option value="<?php 
    echo esc_attr( $value );
    ?>" <?php 
    echo ( !empty( $ds_select_day_of_week ) && in_array( $value, $ds_select_day_of_week, true ) ? 'selected="selected"' : '' );
    ?>><?php 
    echo esc_html( $name );
    ?></option>
		                            <?php 
}
?>
								</select>
							</td>
						</tr>
						<tr valign="top" class="wcpffc_advanced_setting_section">
							<th class="titledesc" scope="row">
								<label for="fee_show_on_checkout_only">
		                            <?php 
esc_html_e( 'Time', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                            <?php 
$html = sprintf(
    '%s<a href=%s target="_blank">%s</a>',
    esc_html__( 'Select the time at which you want to enable fees on your website. This rule will match with the current day which is set by WordPress ', 'woocommerce-conditional-product-fees-for-checkout' ),
    esc_url( admin_url( 'options-general.php' ) ),
    esc_html__( 'Timezone', 'woocommerce-conditional-product-fees-for-checkout' )
);
echo wp_kses( wc_help_tip( wp_kses_post( $html ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                        </label>
							</th>
							<td class="forminp">
		                        <div class="ds_time_wrap">
		                            <span class="ds_time_from"><?php 
esc_html_e( 'From:', 'woocommerce-conditional-product-fees-for-checkout' );
?></span>
		                            <input type="text" name="ds_time_from" class="text-class" id="ds_time_from" value="<?php 
echo esc_attr( $ds_time_from );
?>" placeholder='<?php 
echo esc_attr( 'Select start time', 'woocommerce-conditional-product-fees-for-checkout' );
?>' autocomplete="off">
		                        </div>
		                        <div class="ds_time_wrap">
		                            <span class="ds_time_to"><?php 
esc_html_e( 'To:', 'woocommerce-conditional-product-fees-for-checkout' );
?></span>
		                            <input type="text" name="ds_time_to" class="text-class" id="ds_time_to" value="<?php 
echo esc_attr( $ds_time_to );
?>" placeholder='<?php 
echo esc_attr( 'Select end time', 'woocommerce-conditional-product-fees-for-checkout' );
?>' autocomplete="off">
		                        </div>
								<a href="javascript:void(0)" class="ds_reset_time"></a>
							</td>
						</tr>
						<?php 
?>
							<tr valign="top" class="wcpffc_advanced_setting_section">
								<th class="titledesc" scope="row">
	                                <label for="first_order_for_user">
	                                    <?php 
esc_html_e( 'User\'s First Order', 'woocommerce-conditional-product-fees-for-checkout' );
?>
	                                    <span class="wcpfc-pro-label"></span>
	                                    <?php 
echo wp_kses( wc_help_tip( esc_html__( 'Apply the fee for the user\'s first order only.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
	                                </label>
								</th>
								<td class="forminp">
									<input type="checkbox" name="first_order_for_user" id="first_order_for_user" class="chk_qty_price_class" value="" disabled>
								</td>
							</tr>
							<?php 
if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
    ?>
								<tr valign="top" class="wcpffc_advanced_setting_section">
			                        <th class="titledesc" scope="row">
			                            <label for="fee_settings_recurring">
			                                <?php 
    esc_html_e( 'Set fee as recurring payment', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>
			                                <span class="wcpfc-pro-label"></span>
			                                <?php 
    echo sprintf(
        wp_kses( wc_help_tip( esc_html__( 'Once selected it will allow fees on recurring payments as well. %1$s %2$s %3$sNote: %4$sThis option only works with subscription products.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
            'span'   => $allowed_tooltip_html,
            'strong' => array(),
            'br'     => array(),
        ) ),
        '</br>',
        '</br>',
        '<strong>',
        '</strong>'
    );
    ?>
			                            </label>
			                        </th>
			                        <td class="forminp">
			                            <input type="checkbox" name="fee_settings_recurring" id="fee_settings_recurring" class="fee_settings_recurring" value="" disabled>
			                        </td>
			                    </tr>
			                    <?php 
}
?>
						<tr valign="top" class="wcpffc_advanced_setting_section">
							<th class="titledesc" scope="row">
								<label for="fee_show_on_checkout_only">
		                            <?php 
esc_html_e( 'Showcase Fee On Checkout Only', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                            <?php 
echo wp_kses( wc_help_tip( esc_html__( 'This option will show added fees on checkout page only.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                        </label>
							</th>
							<td class="forminp">
								<input type="checkbox" name="fee_show_on_checkout_only" id="fee_show_on_checkout_only" class="fee_show_on_checkout_only" value="on" <?php 
checked( $fee_show_on_checkout_only, 'on' );
?>>
							</td>
						</tr>
						<?php 
?>
							<tr valign="top" class="wcpffc_advanced_setting_section">
								<th class="titledesc" scope="row">
									<label for="display_fees_in_product_page">
										<?php 
esc_html_e( 'Display Fees in Product Page', 'woocommerce-conditional-product-fees-for-checkout' );
?>
										<span class="wcpfc-pro-label"></span>
										<?php 
echo wp_kses( wc_help_tip( esc_html__( 'If "Yes" is selected, this fee will be displayed in the product page.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
									</label>
								</th>
								<td class="forminp wcpfc-radio-section">
									<label>
										<input name="display_fees_in_product_page" class="display_fees_in_product_page" type="radio" value="" disabled>
										<?php 
esc_html_e( 'Yes', 'woocommerce-conditional-product-fees-for-checkout' );
?>
									</label>
									<label>
										<input name="display_fees_in_product_page" class="display_fees_in_product_page" type="radio" value="" disabled>
										<?php 
esc_html_e( 'No', 'woocommerce-conditional-product-fees-for-checkout' );
?>
									</label>
								</td>
							</tr>
							<tr valign="top">
								<th class="titledesc" scope="row">
									<label for="sm_custom_weight_base">
	                                    <?php 
esc_html_e( 'Each Weight Rule', 'woocommerce-conditional-product-fees-for-checkout' );
?>
	                                    <span class="wcpfc-pro-label"></span>
	                                    <?php 
echo wp_kses( wc_help_tip( esc_html__( 'Enable/Disable additional rules per weight on the cart page.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
	                                </label>
								</th>
								<td class="forminp">
									<input type="checkbox" name="is_allow_custom_weight_base" id="is_allow_custom_weight_base" class="is_allow_custom_weight_base" value="">
								</td>
							</tr>
							<tr valign="top" class="depend_of_custom_weight_base">
		                        <th class="titledesc" scope="row">
		                            <label for="sm_custom_weight_base_cost">
		                                <?php 
esc_html_e( 'Weight - Rate', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                                <span class="wcpfc-pro-label"></span>
		                                <?php 
echo wp_kses( wc_help_tip( esc_html__( 'Set the amount of the fee which you want to apply per weight.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                            </label>
		                        </th>
		                        <td class="forminp">
		                            <input type="text" name="sm_custom_weight_base_cost" class="text-class" id="sm_custom_weight_base_cost" value="" placeholder="<?php 
echo esc_attr( get_woocommerce_currency_symbol() );
?>" disabled>
		                        </td>
		                    </tr>
		                    <tr valign="top" class="depend_of_custom_weight_base">
		                        <th class="titledesc" scope="row">
		                            <label for="sm_custom_weight_base_per_each">
		                                <?php 
esc_html_e( 'Weight - Per Each', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                                <span class="wcpfc-pro-label"></span>
		                                <?php 
echo wp_kses( wc_help_tip( esc_html__( 'Set per each weight you want to apply the additional fee.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                            </label>
		                        </th>
		                        <td class="forminp">
		                            <input type="text" name="sm_custom_weight_base_per_each" class="text-class" id="sm_custom_weight_base_per_each" value="" placeholder="<?php 
esc_attr_e( $get_weight_unit, 'woocommerce-conditional-product-fees-for-checkout' );
?>" disabled>
		                        </td>
		                    </tr>
		                    <tr valign="top" class="depend_of_custom_weight_base">
		                        <th class="titledesc" scope="row">
		                            <label for="sm_custom_weight_base_over">
		                                <?php 
esc_html_e( 'Weight - Over', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                                <span class="wcpfc-pro-label"></span>
		                                <?php 
echo wp_kses( wc_help_tip( esc_html__( 'The weight-based fee will apply in front when the cart weight reach over the configured here.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                            </label>
		                        </th>
		                        <td class="forminp">
		                            <input type="text" name="sm_custom_weight_base_over" class="text-class" id="sm_custom_weight_base_over" value="" placeholder="<?php 
esc_attr_e( $get_weight_unit, 'woocommerce-conditional-product-fees-for-checkout' );
?>" disabled>
		                        </td>
		                    </tr>
							<tr valign="top">
								<th class="titledesc" scope="row">
									<label for="wcpfc_tooltip_desc">
										<?php 
esc_html_e( 'Tooltip Description', 'woocommerce-conditional-product-fees-for-checkout' );
?>
										<span class="wcpfc-pro-label"></span>
										<?php 
echo wp_kses( wc_help_tip( esc_html__( 'As a tooltip, provide short information for this fee to your customers.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
									</label>
								</th>
								<td class="forminp">
	                            	<textarea name="wcpfc_tooltip_desc" rows="3" cols="70" id="wcpfc_tooltip_desc" placeholder="<?php 
esc_attr_e( 'Enter tooltip short description', 'woocommerce-conditional-product-fees-for-checkout' );
?>" maxlength="<?php 
echo esc_attr( apply_filters( 'wcpfc_set_fee_tooltip_maxlength', 25 ) );
?>" disabled></textarea>
								</td>
							</tr>
							<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="wcpfc_price_message_on_cart">
											<?php 
esc_html_e( 'Price Message on Cart', 'woocommerce-conditional-product-fees-for-checkout' );
?>
											<span class="wcpfc-pro-label"></span>
											<?php 
echo wp_kses( wc_help_tip( esc_html__( 'Message to show on the cart page regarding the extra fees added.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
										</label>
									</th>
									<td class="forminp">
		                            	<textarea name="wcpfc_price_message_on_cart" rows="3" cols="70" id="wcpfc_price_message_on_cart" placeholder="<?php 
esc_attr_e( 'Enter price message on cart', 'woocommerce-conditional-product-fees-for-checkout' );
?>" maxlength="<?php 
echo esc_attr( apply_filters( 'wcpfc_set_fee_tooltip_maxlength', 25 ) );
?>" disabled><?php 
echo esc_textarea( $wcpfc_price_message_on_cart );
?></textarea>
									</td>
								</tr>
							<?php 
?>
						<tr valign="top">
							<th class="titledesc" scope="row">
								<label for="fee_settings_select_taxable">
									<?php 
esc_html_e( 'Is Amount Taxable ?', 'woocommerce-conditional-product-fees-for-checkout' );
?>
									<?php 
echo wp_kses( wc_help_tip( esc_html__( 'If "Yes" is selected, this fee will be calculated as taxable.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
								</label>
							</th>
							<td class="forminp wcpfc-radio-section">
								<label>
									<input name="fee_settings_select_taxable" class="fee_settings_select_taxable" type="radio" value="yes" <?php 
checked( $getFeesTaxable, 'yes' );
?>>
									<?php 
esc_html_e( 'Yes', 'woocommerce-conditional-product-fees-for-checkout' );
?>
								</label>
								<label>
									<input name="fee_settings_select_taxable" class="fee_settings_select_taxable" type="radio" value="no" <?php 
( empty( $getFeesTaxable ) ? checked( $getFeesTaxable, '' ) : checked( $getFeesTaxable, 'no' ) );
?>>
									<?php 
esc_html_e( 'No', 'woocommerce-conditional-product-fees-for-checkout' );
?>
								</label>
							</td>
						</tr>
						<?php 
?>
							<tr valign="top" class="enable_taxable_checked">
								<th class="titledesc" scope="row">
									<label for="fee_settings_taxable_type">
										<?php 
esc_html_e( 'Tax Class', 'woocommerce-conditional-product-fees-for-checkout' );
?>
										<span class="wcpfc-pro-label"></span>
										<?php 
echo wp_kses( wc_help_tip( esc_html__( 'Select the Tax Class.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
									</label>
								</th>
								<td class="forminp">
									<select name="fee_settings_taxable_type" id="fee_settings_taxable_type" disabled>
										<option value=""><?php 
esc_html_e( 'Standard', 'woocommerce-conditional-product-fees-for-checkout' );
?></option>
									</select>
								</td>
							</tr>
		                    <tr valign="top">
		                        <th class="titledesc" scope="row">
		                            <label for="fee_settings_select_optional">
		                            	<?php 
esc_html_e( 'Is Fee Optional ?', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                            	<span class="wcpfc-pro-label"></span>
		                            	<?php 
echo sprintf(
    wp_kses( wc_help_tip( esc_html__( 'This will not apply fee by default. %1$s %2$s %3$sNote: %4$sOnce you select this optional fee to "Yes", It will always show the fee on checkout page as optional.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
        'span'   => $allowed_tooltip_html,
        'strong' => array(),
        'br'     => array(),
    ) ),
    '</br>',
    '</br>',
    '<strong>',
    '</strong>'
);
?>
		                            </label>
		                        </th>
		                        <td class="forminp wcpfc-radio-section">
		                        	<label>
										<input name="fee_settings_select_optional" type="radio" class="fee_settings_select_optional" value="yes">
										<?php 
esc_html_e( 'Yes', 'woocommerce-conditional-product-fees-for-checkout' );
?>
									</label>
									<label>
										<input name="fee_settings_select_optional" type="radio" class="fee_settings_select_optional" value="no" checked>
										<?php 
esc_html_e( 'No', 'woocommerce-conditional-product-fees-for-checkout' );
?>
									</label>
		                        </td>
		                    </tr>
		                    <tr valign="top" class="enable_optional_checked">
		                        <th class="titledesc" scope="row">
		                            <label for="fee_settings_optional_type">
		                                <?php 
esc_html_e( 'Optional Fee Selection Type', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                                <span class="wcpfc-pro-label"></span>
		                                <?php 
echo wp_kses( wc_help_tip( esc_html__( 'Select the display type of the optional fee.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                            </label>
		                        </th>
		                        <td class="forminp">
		                            <select name="fee_settings_optional_type" id="fee_settings_optional_type" disabled>
		                                <option value="checkbox"><?php 
esc_html_e( 'Checkbox', 'woocommerce-conditional-product-fees-for-checkout' );
?></option>
		                                <option value="dropdown"><?php 
esc_html_e( 'Dropdown', 'woocommerce-conditional-product-fees-for-checkout' );
?></option>
										<option value="radio-button"><?php 
esc_html_e( 'Radio Button', 'woocommerce-conditional-product-fees-for-checkout' );
?></option>
		                            </select>
		                        </td>
		                    </tr>
		                    <tr valign="top" class="enable_optional_checked">
		                        <th class="titledesc" scope="row">
		                            <label for="default_optional_checked">
		                                <?php 
esc_html_e( 'Default Optional Fee Checked', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                                <span class="wcpfc-pro-label"></span>
		                                <?php 
echo wp_kses( wc_help_tip( esc_html__( 'If enabled, the optional fee will be checked/active by default on the checkout page.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                            </label>
		                        </th>
		                        <td class="forminp">
		                            <input type="checkbox" name="default_optional_checked" id="default_optional_checked" class="chk_qty_price_class" value="" disabled>
		                        </td>
		                    </tr>
		                    <tr valign="top" class="enable_optional_checked">
		                        <th class="titledesc" scope="row">
		                            <label for="optional_fee_title">
		                                <?php 
esc_html_e( 'Optional Fee Section Title', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                                <span class="wcpfc-pro-label"></span>
		                                <?php 
echo sprintf(
    wp_kses( wc_help_tip( esc_html__( 'Set the optional fee section title that will display before the payment section on the checkout page. %1$s %2$s %3$sNote: %4$sWe are removing this field from upcoming update. From now you can change it by use of  "wcpfc_optional_fee_text" filter hook.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
        'span'   => $allowed_tooltip_html,
        'strong' => array(),
        'br'     => array(),
    ) ),
    '</br>',
    '</br>',
    '<strong>',
    '</strong>'
);
?>
		                            </label>
		                        </th>
		                        <td class="forminp">
		                        	<?php 
$optional_fees_title = ( isset( $optional_fee_title ) && !empty( $optional_fee_title ) ? $optional_fee_title : 'Optional fee(s)' );
?>
		                            <input type="text" name="optional_fee_title" id="optional_fee_title" class="text-class" value="<?php 
echo esc_attr( $optional_fees_title );
?>" readonly disabled />
		                        </td>
		                    </tr>
		                    <tr valign="top" class="enable_optional_checked">
		                        <th class="titledesc" scope="row">
		                            <label for="fee_settings_optional_description">
		                                <?php 
esc_html_e( 'Optional Fee Description', 'woocommerce-conditional-product-fees-for-checkout' );
?>
		                                <span class="wcpfc-pro-label"></span>
		                                <?php 
echo wp_kses( wc_help_tip( esc_html__( 'Add a detailed note or description about the optional fee.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
		                            </label>
		                        </th>
		                        <td class="forminp">
		                            <textarea name="fee_settings_optional_description" id="fee_settings_optional_description" rows="3" cols="70" maxlength="150" placeholder="<?php 
esc_attr_e( 'Enter optional fee description', 'woocommerce-conditional-product-fees-for-checkout' );
?>" disabled></textarea>
		                        </td>
		                    </tr>
							<tr valign="top" class="enable_optional_checked">
			                        <th class="titledesc" scope="row">
			                            <label for="optional_fees_in_cart">
			                                <?php 
esc_html_e( 'Enable Optional Fees in Cart Page', 'woocommerce-conditional-product-fees-for-checkout' );
?>
			                                <span class="wcpfc-pro-label"></span>
			                                <?php 
echo wp_kses( wc_help_tip( esc_html__( 'If enabled, the optional fees will show case in cart page as well.', 'woocommerce-conditional-product-fees-for-checkout' ) ), array(
    'span' => $allowed_tooltip_html,
) );
?>
			                            </label>
			                        </th>
			                        <td class="forminp">
			                            <input type="checkbox" name="optional_fees_in_cart" id="optional_fees_in_cart" class="optional_fees_in_cart" value="" disabled>
			                        </td>
			                    </tr>
		                    <?php 
?>
					</tbody>
				</table>
			</div>
            <div class="fees-rules">
                <div class="sub-title">
                    <h2 class="ap-title"><?php 
esc_html_e( 'Conditional Fee Rule', 'woocommerce-conditional-product-fees-for-checkout' );
?></h2>
                    <div class="tap">
                        <a id="fee-add-field" class="button" href="javascript:;">
                            <?php 
esc_html_e( '+ Add Rule', 'woocommerce-conditional-product-fees-for-checkout' );
?>
                        </a>
                    </div>
                    <?php 
?>
                </div>
                <div class="tap">
                    <table id="tbl-product-fee" class="tbl_product_fee table-outer tap-cas form-table product-fee-table">
                        <tbody>
                        <?php 
$attribute_taxonomies_name = wc_get_attribute_taxonomy_names();
if ( isset( $productFeesArray ) && !empty( $productFeesArray ) ) {
    $i = 2;
    foreach ( $productFeesArray as $key => $productfees ) {
        $fees_conditions = ( isset( $productfees['product_fees_conditions_condition'] ) ? $productfees['product_fees_conditions_condition'] : '' );
        $condition_is = ( isset( $productfees['product_fees_conditions_is'] ) ? $productfees['product_fees_conditions_is'] : '' );
        $condtion_value = ( isset( $productfees['product_fees_conditions_values'] ) ? $productfees['product_fees_conditions_values'] : array() );
        ?>
                                <tr id="row_<?php 
        echo esc_attr( $i );
        ?>" valign="top">
                                    <td class="titledesc th_product_fees_conditions_condition" scope="row">
                                        <select rel-id="<?php 
        echo esc_attr( $i );
        ?>"
                                                id="product_fees_conditions_condition_<?php 
        echo esc_attr( $i );
        ?>"
                                                name="fees[product_fees_conditions_condition][]"
                                                id="product_fees_conditions_condition"
                                                class="product_fees_conditions_condition">
                                            <optgroup label="<?php 
        esc_attr_e( 'Location Specific', 'woocommerce-conditional-product-fees-for-checkout' );
        ?>">
                                                <option value="country" <?php 
        echo ( 'country' === $fees_conditions ? 'selected' : '' );
        ?>><?php 
        esc_html_e( 'Country', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                <option value="city" <?php 
        echo ( 'city' === $fees_conditions ? 'selected' : '' );
        ?>><?php 
        esc_html_e( 'City', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                <?php 
        ?>
                                                    <option value="state_disabled"><?php 
        esc_html_e( 'State 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                    <option value="postcode_disabled"><?php 
        esc_html_e( 'Postcode 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                    <option value="zone_disabled"><?php 
        esc_html_e( 'Zone 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                    <?php 
        ?>
                                            </optgroup>
                                            <optgroup label="<?php 
        esc_attr_e( 'Product Specific', 'woocommerce-conditional-product-fees-for-checkout' );
        ?>">
                                                <option value="product" <?php 
        echo ( 'product' === $fees_conditions ? 'selected' : '' );
        ?>><?php 
        esc_html_e( 'Cart contains product', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                <option value="variableproduct" <?php 
        echo ( 'variableproduct' === $fees_conditions ? 'selected' : '' );
        ?>><?php 
        esc_html_e( 'Cart contains variable product', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                <?php 
        ?>
                                                	<option value="<?php 
        echo esc_attr( ( 'category' === $fees_conditions ? 'category' : 'category_disabled' ) );
        ?>" <?php 
        echo ( 'category' === $fees_conditions ? 'selected' : '' );
        ?>><?php 
        esc_html_e( 'Cart contains category\'s product 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                	<?php 
        ?>
                                                <option value="tag" <?php 
        echo ( 'tag' === $fees_conditions ? 'selected' : '' );
        ?>><?php 
        esc_html_e( 'Cart contains tag\'s product', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                <option value="product_qty" <?php 
        echo ( 'product_qty' === $fees_conditions ? 'selected' : '' );
        ?>><?php 
        esc_html_e( 'Cart contains product\'s quantity', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                            </optgroup>
                                            <optgroup label="<?php 
        esc_attr_e( 'Attribute Specific', 'woocommerce-conditional-product-fees-for-checkout' );
        ?>">
                                            	<?php 
        if ( wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code() ) {
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            foreach ( $attribute_taxonomies as $attribute ) {
                $att_label = $attribute->attribute_label;
                $att_name = wc_attribute_taxonomy_name( $attribute->attribute_name );
                ?>
                                                        <option value="<?php 
                echo esc_attr( $att_name );
                ?>" <?php 
                echo ( $att_name === $fees_conditions ? 'selected' : '' );
                ?>><?php 
                echo esc_html( $att_label );
                ?></option>
                                                        <?php 
            }
        } else {
            ?>
                                                    <option value="attribute_list_disabled"><?php 
            esc_html_e( 'Color 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
            ?></option>
                                                    <?php 
        }
        ?>
                                            </optgroup>
                                            <optgroup label="<?php 
        esc_attr_e( 'User Specific', 'woocommerce-conditional-product-fees-for-checkout' );
        ?>">
                                                <option value="user" <?php 
        echo ( 'user' === $fees_conditions ? 'selected' : '' );
        ?>><?php 
        esc_html_e( 'User', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                <?php 
        ?>
                                                    <option value="user_role_disabled"><?php 
        esc_html_e( 'User Role 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                    <?php 
        ?>
                                            </optgroup>
												<?php 
        ?>
													<optgroup label="<?php 
        esc_attr_e( 'Purchase History', 'woocommerce-conditional-product-fees-for-checkout' );
        ?>">
														<option value="total_spent_order_disabled"><?php 
        esc_html_e( 'Total order spent (all time)', 'woocommerce-conditional-product-fees-for-checkout' );
        echo esc_html( ' 🔒' );
        ?></option>
														<option value="spent_order_count_disabled"><?php 
        esc_html_e( 'Number of orders (all time)', 'woocommerce-conditional-product-fees-for-checkout' );
        echo esc_html( ' 🔒' );
        ?></option>
														<option value="last_spent_order_disabled"><?php 
        esc_html_e( 'Last order spent', 'woocommerce-conditional-product-fees-for-checkout' );
        echo esc_html( ' 🔒' );
        ?></option>
													</optgroup><?php 
        ?>
                                            <optgroup label="<?php 
        esc_attr_e( 'Cart Specific', 'woocommerce-conditional-product-fees-for-checkout' );
        ?>">
                                                <?php 
        $currency_symbol = get_woocommerce_currency_symbol();
        $currency_symbol = ( !empty( $currency_symbol ) ? '(' . $currency_symbol . ')' : '' );
        ?>
                                                <option value="cart_total" <?php 
        echo ( 'cart_total' === $fees_conditions ? 'selected' : '' );
        ?>><?php 
        esc_html_e( 'Cart Subtotal (Before Discount) ', 'woocommerce-conditional-product-fees-for-checkout' );
        echo esc_html( $currency_symbol );
        ?></option>
                                                <?php 
        ?>
                                                    <option value="cart_totalafter_disabled"><?php 
        esc_html_e( 'Cart Subtotal (After Discount) ', 'woocommerce-conditional-product-fees-for-checkout' );
        echo esc_html( $currency_symbol );
        echo esc_html( ' 🔒' );
        ?></option>
                                                    <option value="cart_specificproduct_disabled"><?php 
        esc_html_e( 'Cart Subtotal (Specific products) ', 'woocommerce-conditional-product-fees-for-checkout' );
        echo esc_html( $currency_symbol );
        echo esc_html( ' 🔒' );
        ?></option>
													<option value="cart_totalexclude_tax_disabled"><?php 
        esc_html_e( 'Cart Subtotal (Ex. Taxes) ', 'woocommerce-conditional-product-fees-for-checkout' );
        echo esc_html( $currency_symbol );
        echo esc_html( ' 🔒' );
        ?></option>
													<option value="cart_rowtotal_disabled"><?php 
        esc_html_e( 'Row Total in Cart ', 'woocommerce-conditional-product-fees-for-checkout' );
        echo esc_html( ' 🔒' );
        ?></option>
                                                    <?php 
        ?>
                                                <option value="quantity" <?php 
        echo ( 'quantity' === $fees_conditions ? 'selected' : '' );
        ?>><?php 
        esc_html_e( 'Quantity', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                <?php 
        ?>
                                                    <option value="weight_disabled"><?php 
        esc_html_e( 'Weight ', 'woocommerce-conditional-product-fees-for-checkout' );
        echo esc_html( $weight_unit );
        echo esc_html( ' 🔒' );
        ?></option>
                                                    <option value="coupon_disabled"><?php 
        esc_html_e( 'Coupon 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                    <option value="shipping_class_disabled"><?php 
        esc_html_e( 'Shipping Class 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                    <?php 
        ?>
                                            </optgroup>
                                            <optgroup label="<?php 
        esc_attr_e( 'Payment Specific', 'woocommerce-conditional-product-fees-for-checkout' );
        ?>">
	                                        	<?php 
        ?>
                                                	<option value="payment_disabled"><?php 
        esc_html_e( 'Payment Gateway 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                	<?php 
        ?>
                                        	</optgroup>
                                        	<optgroup label="<?php 
        esc_attr_e( 'Shipping Specific', 'woocommerce-conditional-product-fees-for-checkout' );
        ?>">
                                        		<?php 
        ?>
                                                    <option value="shipping_method_disabled"><?php 
        esc_html_e( 'Shipping Method 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                    <?php 
        ?>
                                    		</optgroup>
                                        </select>
                                    </td>
                                    <td class="select_condition_for_in_notin">
                                        <?php 
        if ( 'cart_total' === $fees_conditions || 'cart_totalafter' === $fees_conditions || 'cart_specificproduct' === $fees_conditions || 'cart_totalexclude_tax' === $fees_conditions || 'cart_rowtotal' === $fees_conditions || 'quantity' === $fees_conditions || 'weight' === $fees_conditions || 'product_qty' === $fees_conditions || 'total_spent_order' === $fees_conditions || 'spent_order_count' === $fees_conditions || 'last_spent_order' === $fees_conditions ) {
            ?>
                                            <select name="fees[product_fees_conditions_is][]" class="product_fees_conditions_is_<?php 
            echo esc_attr( $i );
            ?>">
                                                <option value="is_equal_to" <?php 
            echo ( 'is_equal_to' === $condition_is ? 'selected' : '' );
            ?>><?php 
            esc_html_e( 'Equal to ( = )', 'woocommerce-conditional-product-fees-for-checkout' );
            ?></option>
                                                <option value="less_equal_to" <?php 
            echo ( 'less_equal_to' === $condition_is ? 'selected' : '' );
            ?>><?php 
            esc_html_e( 'Less or Equal to ( <= )', 'woocommerce-conditional-product-fees-for-checkout' );
            ?></option>
                                                <option value="less_then" <?php 
            echo ( 'less_then' === $condition_is ? 'selected' : '' );
            ?>><?php 
            esc_html_e( 'Less than ( < )', 'woocommerce-conditional-product-fees-for-checkout' );
            ?></option>
                                                <option value="greater_equal_to" <?php 
            echo ( 'greater_equal_to' === $condition_is ? 'selected' : '' );
            ?>><?php 
            esc_html_e( 'Greater or Equal to ( >= )', 'woocommerce-conditional-product-fees-for-checkout' );
            ?></option>
                                                <option value="greater_then" <?php 
            echo ( 'greater_then' === $condition_is ? 'selected' : '' );
            ?>><?php 
            esc_html_e( 'Greater than ( > )', 'woocommerce-conditional-product-fees-for-checkout' );
            ?></option>
                                                <option value="not_in" <?php 
            echo ( 'not_in' === $condition_is ? 'selected' : '' );
            ?>><?php 
            esc_html_e( 'Not Equal to ( != )', 'woocommerce-conditional-product-fees-for-checkout' );
            ?></option>
                                            </select>
                                        <?php 
        } else {
            ?>
                                            <select name="fees[product_fees_conditions_is][]" class="product_fees_conditions_is_<?php 
            echo esc_attr( $i );
            ?>">
                                                <option value="is_equal_to" <?php 
            echo ( 'is_equal_to' === $condition_is ? 'selected' : '' );
            ?>><?php 
            esc_html_e( 'Equal to ( = )', 'woocommerce-conditional-product-fees-for-checkout' );
            ?></option>
                                                <option value="not_in" <?php 
            echo ( 'not_in' === $condition_is ? 'selected' : '' );
            ?>><?php 
            esc_html_e( 'Not Equal to ( != )', 'woocommerce-conditional-product-fees-for-checkout' );
            ?> </option>
                                            </select>
                                        <?php 
        }
        ?>
                                    </td>
                                    <td class="condition-value" id="column_<?php 
        echo esc_attr( $i );
        ?>" colspan="2">
                                        <?php 
        $html = '';
        if ( 'country' === $fees_conditions ) {
            $html .= $wcpfc_admin_object->wcpfc_pro_get_country_list( $i, $condtion_value );
        } elseif ( 'city' === $fees_conditions ) {
            $html .= '<textarea name = "fees[product_fees_conditions_values][value_' . $i . ']">' . $condtion_value . '</textarea>';
            $html .= sprintf( wp_kses( __( '<p><b style="color: red;">Note: </b> Please enter each city name in a new line.', 'woocommerce-conditional-product-fees-for-checkout' ), array(
                'p' => array(),
                'b' => array(
                    'style' => array(),
                ),
            ) ) );
        } elseif ( 'product' === $fees_conditions ) {
            $html .= $wcpfc_admin_object->wcpfc_pro_get_product_list( $i, $condtion_value, 'edit' );
        } elseif ( 'variableproduct' === $fees_conditions ) {
            $html .= $wcpfc_admin_object->wcpfc_pro_get_varible_product_list( $i, $condtion_value, 'edit' );
        } elseif ( 'category' === $fees_conditions ) {
            $html .= $wcpfc_admin_object->wcpfc_pro_get_category_list( $i, $condtion_value );
        } elseif ( 'tag' === $fees_conditions ) {
            $html .= $wcpfc_admin_object->wcpfc_pro_get_tag_list( $i, $condtion_value );
        } elseif ( 'product_qty' === $fees_conditions ) {
            $html .= '<input type = "text" name = "fees[product_fees_conditions_values][value_' . $i . ']" id = "product_fees_conditions_values" class = "product_fees_conditions_values qty-class" value = "' . $condtion_value . '">';
        } elseif ( 'user' === $fees_conditions ) {
            $html .= $wcpfc_admin_object->wcpfc_pro_get_user_list( $i, $condtion_value, 'edit' );
        } elseif ( 'cart_total' === $fees_conditions ) {
            $html .= '<input type = "text" name = "fees[product_fees_conditions_values][value_' . $i . ']" id = "product_fees_conditions_values" class = "product_fees_conditions_values price-class" value = "' . $condtion_value . '">';
        } elseif ( 'quantity' === $fees_conditions ) {
            $html .= '<input type = "text" name = "fees[product_fees_conditions_values][value_' . $i . ']" id = "product_fees_conditions_values" class = "product_fees_conditions_values qty-class" value = "' . $condtion_value . '">';
        }
        echo wp_kses( $html, Woocommerce_Conditional_Product_Fees_For_Checkout_Pro::allowed_html_tags() );
        ?>
                                        <input type="hidden" name="condition_key[<?php 
        echo 'value_' . esc_attr( $i );
        ?>]" value="">
                                    </td>
                                    <td>
                                        <a id="fee-delete-field" rel-id="<?php 
        echo esc_attr( $i );
        ?>" class="delete-row" href="javascript:;" title="Delete"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php 
        $i++;
    }
} else {
    $i = 1;
    ?>
                            <tr id="row_1" valign="top">
                                <td class="titledesc th_product_fees_conditions_condition" scope="row">
                                    <select rel-id="1" id="product_fees_conditions_condition_1"
                                            name="fees[product_fees_conditions_condition][]"
                                            id="product_fees_conditions_condition"
                                            class="product_fees_conditions_condition">
                                        <optgroup label="<?php 
    esc_attr_e( 'Location Specific', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>">
                                            <option value="country"><?php 
    esc_html_e( 'Country', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                            <option value="city"><?php 
    esc_html_e( 'City', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                            <?php 
    ?>
                                            	<option value="state_disabled"><?php 
    esc_html_e( 'State 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                                <option value="postcode_disabled"><?php 
    esc_html_e( 'Postcode 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                                <option value="zone_disabled"><?php 
    esc_html_e( 'Zone 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                            	<?php 
    ?>
                                        </optgroup>
                                        <optgroup label="<?php 
    esc_attr_e( 'Product Specific', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>">
                                            <option value="product"><?php 
    esc_html_e( 'Cart contains product', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                            <option value="variableproduct"><?php 
    esc_html_e( 'Cart contains variable product', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                            <?php 
    ?>
                                            	<option value="category_disabled"><?php 
    esc_html_e( 'Cart contains category\'s product 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                            	<?php 
    ?>
                                            <option value="tag"><?php 
    esc_html_e( 'Cart contains tag\'s product', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                            <option value="product_qty"><?php 
    esc_html_e( 'Cart contains product\'s quantity', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                        </optgroup>
                                        <optgroup label="<?php 
    esc_attr_e( 'Attribute Specific', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>">
                                        	<?php 
    if ( wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code() ) {
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        foreach ( $attribute_taxonomies as $attribute ) {
            $att_label = $attribute->attribute_label;
            $att_name = wc_attribute_taxonomy_name( $attribute->attribute_name );
            ?>
                                                    <option value="<?php 
            echo esc_attr( $att_name );
            ?>"><?php 
            echo esc_html( $att_label );
            ?></option>
                                                    <?php 
        }
    } else {
        ?>
                                                <option value="attribute_list_disabled"><?php 
        esc_html_e( 'Color 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
        ?></option>
                                                <?php 
    }
    ?>
                                        </optgroup>
                                        <optgroup label="<?php 
    esc_attr_e( 'User Specific', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>">
                                            <option value="user"><?php 
    esc_html_e( 'User', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                            <?php 
    ?>
                                                <option value="user_role_disabled"><?php 
    esc_html_e( 'User Role 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                                <?php 
    ?>
                                        </optgroup>
											<?php 
    ?>
                                        <optgroup label="<?php 
    esc_attr_e( 'Cart Specific', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>">
                                            <?php 
    $currency_symbol = get_woocommerce_currency_symbol();
    $currency_symbol = ( !empty( $currency_symbol ) ? '(' . $currency_symbol . ')' : '' );
    ?>
                                            <option value="cart_total"><?php 
    esc_html_e( 'Cart Subtotal (Before Discount) ', 'woocommerce-conditional-product-fees-for-checkout' );
    echo esc_html( $currency_symbol );
    ?></option>
                                            <?php 
    ?>
                                                <option value="cart_totalafter_disabled"><?php 
    esc_html_e( 'Cart Subtotal (After Discount) ', 'woocommerce-conditional-product-fees-for-checkout' );
    echo esc_html( $currency_symbol );
    echo esc_html( ' 🔒' );
    ?></option>
                                                <option value="cart_specificproduct_disabled"><?php 
    esc_html_e( 'Cart Subtotal (Specific products) ', 'woocommerce-conditional-product-fees-for-checkout' );
    echo esc_html( $currency_symbol );
    echo esc_html( ' 🔒' );
    ?></option>
												<option value="cart_totalexclude_tax_disabled"><?php 
    esc_html_e( 'Cart Subtotal (Ex. Taxes) ', 'woocommerce-conditional-product-fees-for-checkout' );
    echo esc_html( $currency_symbol );
    echo esc_html( ' 🔒' );
    ?></option>
												<option value="cart_rowtotal_disabled"><?php 
    esc_html_e( 'Row Total in Cart ', 'woocommerce-conditional-product-fees-for-checkout' );
    echo esc_html( ' 🔒' );
    ?></option>
                                                <?php 
    ?>
                                            <option value="quantity"><?php 
    esc_html_e( 'Quantity', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                            <?php 
    ?>
                                                <option value="weight_disabled"><?php 
    esc_html_e( 'Weight ', 'woocommerce-conditional-product-fees-for-checkout' );
    echo esc_html( $weight_unit );
    echo esc_html( ' 🔒' );
    ?></option>
                                                <option value="coupon_disabled"><?php 
    esc_html_e( 'Coupon 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                                <option value="shipping_class_disabled"><?php 
    esc_html_e( 'Shipping Class 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                                <?php 
    ?>
                                        </optgroup>
                                        <optgroup label="<?php 
    esc_attr_e( 'Payment Specific', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>">
                                        	<?php 
    ?>
                                            	<option value="payment_disabled"><?php 
    esc_html_e( 'Payment Gateway 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                            	<?php 
    ?>
                                    	</optgroup>
                                    	<optgroup label="<?php 
    esc_attr_e( 'Shipping Specific', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>">
	                                        <?php 
    ?>
                                                <option value="shipping_method_disabled"><?php 
    esc_html_e( 'Shipping Method 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                                <?php 
    ?>
                                        </optgroup>
                                    </select>
                                </td>
                                <td class="select_condition_for_in_notin">
                                    <select name="fees[product_fees_conditions_is][]" class="product_fees_conditions_is product_fees_conditions_is_1">
                                        <option value="is_equal_to"><?php 
    esc_html_e( 'Equal to ( = )', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                        <option value="not_in"><?php 
    esc_html_e( 'Not Equal to ( != )', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></option>
                                    </select>
                                </td>
                                <td id="column_1" class="condition-value" colspan="2">
                                    <?php 
    echo wp_kses( $wcpfc_admin_object->wcpfc_pro_get_country_list( 1 ), Woocommerce_Conditional_Product_Fees_For_Checkout_Pro::allowed_html_tags() );
    ?>
                                    <input type="hidden" name="condition_key[value_1][]" value="">
                                </td>
                                <td>
	                                <a id="fee-delete-field" rel-id="<?php 
    echo esc_attr( $i );
    ?>" class="delete-row" href="javascript:;" title="Delete"><i class="fa fa-trash"></i></a>
	                            </td>
                            </tr>
                        <?php 
}
?>
                        </tbody>
                    </table>
                    <input type="hidden" name="total_row" id="total_row" value="<?php 
echo esc_attr( $i );
?>">
                </div>
            </div>
			
			<?php 
?>
			<p class="submit"><input type="submit" name="submitFee" class="button button-primary" value="<?php 
echo esc_attr( $btnValue );
?>"></p>
		</form>
	</div>
</div>
</div>
</div>
</div>
</div>
