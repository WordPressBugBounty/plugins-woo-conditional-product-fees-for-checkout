<?php
/**
 * Handles free plugin user dashboard
 * 
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @since   3.9.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once( plugin_dir_path( __FILE__ ) . 'header/plugin-header.php' );

// Get product details from Freemius via API
$annual_plugin_price = '';
$monthly_plugin_price = '';
$plugin_details = array(
    'product_id' => 43543,
);

$api_url = add_query_arg(wp_rand(), '', WCPFC_STORE_URL . 'wp-json/dotstore-product-fs-data/v2/dotstore-product-fs-data');
$final_api_url = add_query_arg($plugin_details, $api_url);

if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
    $api_response = vip_safe_wp_remote_get( $final_api_url, 3, 1, 20 );
} else {
    $api_response = wp_remote_get( $final_api_url ); // phpcs:ignore
}

if ( ( !is_wp_error($api_response)) && (200 === wp_remote_retrieve_response_code( $api_response ) ) ) {
	$api_response_body = wp_remote_retrieve_body($api_response);
	$plugin_pricing = json_decode( $api_response_body, true );

	if ( isset( $plugin_pricing ) && ! empty( $plugin_pricing ) ) {
		$first_element = reset( $plugin_pricing );
        if ( ! empty( $first_element['price_data'] ) ) {
            $first_price = reset( $first_element['price_data'] )['annual_price'];
        } else {
            $first_price = "0";
        }

        if( "0" !== $first_price ){
        	$annual_plugin_price = $first_price;
        	$monthly_plugin_price = round( intval( $first_price  ) / 12 );
        }
	}
}

// Set plugin key features content
$plugin_key_features = array(
    array(
        'title' => esc_html__( 'WooCommerce Dynamic Extra Fees', 'woocommerce-conditional-product-fees-for-checkout' ),
        'description' => esc_html__( 'Charge dynamic fee charges based on the product stock, quantity, cart subtotal, special products, etc.', 'woocommerce-conditional-product-fees-for-checkout' ),
        'popup_image' => esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-one-img.jpeg' ),
        'popup_content' => array(
        	esc_html__( 'You have the power to create personalized fee rules tailored to your specific needs. Define rules based on different criteria such as country, city, state, category, coupon code, and quantity range.', 'woocommerce-conditional-product-fees-for-checkout' )
        ),
        'popup_examples' => array(
            esc_html__( 'Tailor your pricing to quantity levels with tiered fees: $10 for 1-3 items, $20 for 4-10 items, and $29 for 10 or more items.', 'woocommerce-conditional-product-fees-for-checkout' ),
            esc_html__( 'Easily apply quantity-based fees to shipping classes: $39 for Bulky items, $21 for Lightweight items, and more. Easily apply quantity-based fees to shipping classes: $39 for Bulky items, $21 for Lightweight items, and more.', 'woocommerce-conditional-product-fees-for-checkout' ),
        )
    ),
    array(
        'title' => esc_html__( 'Location-Based Conditional Fees', 'woocommerce-conditional-product-fees-for-checkout' ),
        'description' => esc_html__( 'You can set the shipping charges at checkout based on locational factors such as country, state, postal code, and zone.', 'woocommerce-conditional-product-fees-for-checkout' ),
        'popup_image' => esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-two-img.jpeg' ),
        'popup_content' => array(
        	esc_html__( 'Expand your fee configuration options with different locations such as country, state, postcodes, and city rules.', 'woocommerce-conditional-product-fees-for-checkout' ),
        ),
        'popup_examples' => array(
            esc_html__( 'Easy to apply country-based Fees: $10 for orders shipped to the United States or $15 for orders shipped to Canada.', 'woocommerce-conditional-product-fees-for-checkout' ),
            esc_html__( 'Capability to apply service area-based charges: $5 fee for deliveries within the town area or a $15 fee for orders shipped to remote areas.', 'woocommerce-conditional-product-fees-for-checkout' )
        )
    ),
    array(
        'title' => esc_html__( 'User Role-Based Checkout Fees', 'woocommerce-conditional-product-fees-for-checkout' ),
        'description' => esc_html__( 'Set conditional product fees based on user roles such as consumer, seller, shop manager, premium customer, and more.', 'woocommerce-conditional-product-fees-for-checkout' ),
        'popup_image' => esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-three-img.jpeg' ),
        'popup_content' => array(
        	esc_html__( 'It is easy to apply charges based on customer types. Set different delivery charges for consumers, sellers, shop managers, and premium customers.', 'woocommerce-conditional-product-fees-for-checkout' ),
        ),
        'popup_examples' => array(
            esc_html__( 'It is easy to apply the service charge on bulk orders: the vendor charges $49 for specific product orders exceeding $3000.', 'woocommerce-conditional-product-fees-for-checkout' ),
            esc_html__( 'Customize charges based on weight: $99 for items weighing between 0.01 - 99.99 and $149 for 100 pounds or more.', 'woocommerce-conditional-product-fees-for-checkout' )
        )
    ),
    array(
        'title' => esc_html__( 'Percentage Fees Based On Product Quantity', 'woocommerce-conditional-product-fees-for-checkout' ),
        'description' => esc_html__( 'Charge additional percentage fees for a specific range of items added to the cart by the users.', 'woocommerce-conditional-product-fees-for-checkout' ),
        'popup_image' => esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-four-img.jpeg' ),
        'popup_content' => array(
        	esc_html__( 'You have the power to charge percentage fees for special products. Define a 2% fee for high-value product items.', 'woocommerce-conditional-product-fees-for-checkout' ),
        ),
        'popup_examples' => array(
            esc_html__( 'An online store charges shipping fees based on product quantity: 3% for 1-10, 5% for 11-20, and 7% for 21+ for specific products.', 'woocommerce-conditional-product-fees-for-checkout' ),
            esc_html__( 'Tailor your fees based on product quantity: $15 for orders with 10 or more items.', 'woocommerce-conditional-product-fees-for-checkout' ),
        )
    ),
    array(
        'title' => esc_html__( 'Free Shipping Based Check-Out Fees', 'woocommerce-conditional-product-fees-for-checkout' ),
        'description' => esc_html__( 'Offer extra charges to your customers based on the order amount if they select the free shipping option.', 'woocommerce-conditional-product-fees-for-checkout' ),
        'popup_image' => esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-five-img.jpeg' ),
        'popup_content' => array(
        	esc_html__( 'You can offer extra charges to your customer based on the order amount for the free shipping option.', 'woocommerce-conditional-product-fees-for-checkout' ),
        ),
        'popup_examples' => array(
            esc_html__( 'Easy to apply dynamic fees for free shipping: $10 box charges for express free shipping and $21 charge for orders below $199.', 'woocommerce-conditional-product-fees-for-checkout' ),
            esc_html__( 'Customize country-specific charges for free delivery: $10 charges for the United States and $21 for Europe.', 'woocommerce-conditional-product-fees-for-checkout' )
        )
    ),
    array(
        'title' => esc_html__( 'Payment Gateway-Based Extra Fees', 'woocommerce-conditional-product-fees-for-checkout' ),
        'description' => esc_html__( 'Charge extra fees from the customers for choosing a specific payment gateway based on the order amount.', 'woocommerce-conditional-product-fees-for-checkout' ),
        'popup_image' => esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-six-img.jpeg' ),
        'popup_content' => array(
        	esc_html__( 'Power to generate extra revenue by collecting transaction fees for specific payment gateways.', 'woocommerce-conditional-product-fees-for-checkout' ),
        ),
        'popup_examples' => array(
            esc_html__( 'Customize charges based on payment type: 10 fees for payments made through Cash on Delivery.', 'woocommerce-conditional-product-fees-for-checkout' ),
            esc_html__( 'Easily apply a processing fee for cheque payments: a $5 fee per transaction.', 'woocommerce-conditional-product-fees-for-checkout' )
        )
    ),
    array(
        'title' => esc_html__( 'Advanced Extra Fees Rules', 'woocommerce-conditional-product-fees-for-checkout' ),
        'description' => esc_html__( 'Enhance your pricing strategy by adding advanced fee rules based on specific product or category subtotal ranges.', 'woocommerce-conditional-product-fees-for-checkout' ),
        'popup_image' => esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-seven-img.jpeg' ),
        'popup_content' => array(
        	esc_html__( 'Enhance your pricing strategy by adding advanced fee rules based on specific product or category subtotal ranges.', 'woocommerce-conditional-product-fees-for-checkout' ),
        ),
        'popup_examples' => array(
            esc_html__( 'Harness the power of charges on popular products: Apply a $19 fee for orders between $199 and $999, $29 for orders between $1999 and $5999, and $35 for orders exceeding $6000.', 'woocommerce-conditional-product-fees-for-checkout' ),
            esc_html__( 'Enhance fee options with additional charges for top products: Apply a 5% fee for orders below $1499 and 2% for orders above $1500.', 'woocommerce-conditional-product-fees-for-checkout' ),
        )
    ),
    array(
        'title' => esc_html__( 'Revenue Dashboard', 'woocommerce-conditional-product-fees-for-checkout' ),
        'description' => esc_html__( 'Develop a revenue-driven strategy by analyzing top fees, including yearly, monthly, and daily charges, and visualizing pie chart graphs.', 'woocommerce-conditional-product-fees-for-checkout' ),
        'popup_image' => esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-eight-img.jpeg' ),
        'popup_content' => array(
        	esc_html__( 'Develop a revenue-driven strategy by analyzing top fees, including yearly, monthly, and daily charges, and visualizing pie chart graphs.', 'woocommerce-conditional-product-fees-for-checkout' ),
        ),
        'popup_examples' => array(
            esc_html__( 'Track additional revenue: Visualize line charts based on fees and customize fee amounts based on generated revenue.', 'woocommerce-conditional-product-fees-for-checkout' ),
            esc_html__( 'Track and optimize top fees: Gain insights into the top 10 revenue-generating fees and refine your strategy accordingly.', 'woocommerce-conditional-product-fees-for-checkout' ),
        )
    )
);
?>
	<div class="dotstore-upgrade-dashboard">
		<div class="premium-benefits-section">
			<h2><?php esc_html_e( 'Upgrade to Unlock Premium Features', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h2>
			<p><?php esc_html_e( 'Check out the advanced features, Simplify fee management, drive more sales, and boost your revenue by upgrading to premium!', 'woocommerce-conditional-product-fees-for-checkout' ); ?></p>
		</div>
		<div class="premium-plugin-details">
			<div class="premium-key-fetures">
				<h3><?php esc_html_e( 'Discover Our Top Key Features', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h3>
				<ul>
					<?php 
					if ( isset( $plugin_key_features ) && ! empty( $plugin_key_features ) ) {
						foreach( $plugin_key_features as $key_feature ) {
							?>
							<li>
								<h4><?php echo esc_html( $key_feature['title'] ); ?><span class="premium-feature-popup"></span></h4>
								<p><?php echo esc_html( $key_feature['description'] ); ?></p>
								<div class="feature-explanation-popup-main">
									<div class="feature-explanation-popup-outer">
										<div class="feature-explanation-popup-inner">
											<div class="feature-explanation-popup">
												<span class="dashicons dashicons-no-alt popup-close-btn" title="<?php esc_attr_e('Close', 'woocommerce-conditional-product-fees-for-checkout'); ?>"></span>
												<div class="popup-body-content">
													<div class="feature-content">
														<h4><?php echo esc_html( $key_feature['title'] ); ?></h4>
														<?php 
														if ( isset( $key_feature['popup_content'] ) && ! empty( $key_feature['popup_content'] ) ) {
															foreach( $key_feature['popup_content'] as $feature_content ) {
																?>
																<p><?php echo esc_html( $feature_content ); ?></p>
																<?php
															}
														}
														?>
														<ul>
															<?php 
															if ( isset( $key_feature['popup_examples'] ) && ! empty( $key_feature['popup_examples'] ) ) {
																foreach( $key_feature['popup_examples'] as $feature_example ) {
																	?>
																	<li><?php echo esc_html( $feature_example ); ?></li>
																	<?php
																}
															}
															?>
														</ul>
													</div>
													<div class="feature-image">
														<img src="<?php echo esc_url( $key_feature['popup_image'] ); ?>" alt="<?php echo esc_attr( $key_feature['title'] ); ?>">
													</div>
												</div>
											</div>		
										</div>
									</div>
								</div>
							</li>
							<?php
						}
					}
					?>
				</ul>
			</div>
			<div class="premium-plugin-buy">
				<div class="premium-buy-price-box">
					<div class="price-box-top">
						<div class="pricing-icon">
							<img src="<?php echo esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/premium-upgrade-img/pricing-1.svg' ); ?>" alt="<?php esc_attr_e( 'Personal Plan', 'woocommerce-conditional-product-fees-for-checkout' ); ?>">
						</div>
						<h4><?php esc_html_e( 'Personal', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h4>
					</div>
					<div class="price-box-middle">
						<?php
						if ( ! empty( $annual_plugin_price ) ) {
							?>
							<div class="monthly-price-wrap"><?php echo esc_html( '$' . $monthly_plugin_price ); ?><span class="seprater">/</span><span><?php esc_html_e( 'month', 'woocommerce-conditional-product-fees-for-checkout' ); ?></span></div>
							<div class="yearly-price-wrap"><?php echo sprintf( esc_html__( 'Pay $%s today. Renews in 12 months.', 'woocommerce-conditional-product-fees-for-checkout' ), esc_html( $annual_plugin_price ) ); ?></div>
							<?php	
						}
						?>
						<span class="for-site"><?php esc_html_e( '1 site', 'woocommerce-conditional-product-fees-for-checkout' ); ?></span>
						<p class="price-desc"><?php esc_html_e( 'Great for website owners with a single WooCommerce Store', 'woocommerce-conditional-product-fees-for-checkout' ); ?></p>
					</div>
					<div class="price-box-bottom">
						<a href="javascript:void(0);" class="upgrade-now"><?php esc_html_e( 'Get The Premium Version', 'woocommerce-conditional-product-fees-for-checkout' ); ?></a>
						<p class="trusted-by"><?php esc_html_e( 'Trusted by 100,000+ store owners and WP experts!', 'woocommerce-conditional-product-fees-for-checkout' ); ?></p>
					</div>
				</div>
				<div class="premium-satisfaction-guarantee premium-satisfaction-guarantee-2">
					<div class="money-back-img">
						<img src="<?php echo esc_url(WCPFC_PRO_PLUGIN_URL . 'admin/images/premium-upgrade-img/14-Days-Money-Back-Guarantee.png'); ?>" alt="<?php esc_attr_e('14-Day money-back guarantee', 'woocommerce-conditional-product-fees-for-checkout'); ?>">
					</div>
					<div class="money-back-content">
						<h2><?php esc_html_e( '14-Day Satisfaction Guarantee', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h2>
						<p><?php esc_html_e( 'You are fully protected by our 100% Satisfaction Guarantee. If over the next 14 days you are unhappy with our plugin or have an issue that we are unable to resolve, we\'ll happily consider offering a 100% refund of your money.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></p>
					</div>
				</div>
				<div class="plugin-customer-review">
					<h3><?php esc_html_e( 'Exactly what we needed!', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h3>
					<p>
						<?php echo wp_kses( __( 'I needed to add a processing fee for credit card payments at checkout. The <strong>WooCommerce Extra Fees Plugin Pro was a perfect solution</strong>. Installation was a breeze, and it was up and <strong>running in just a few clicks!</strong>', 'woocommerce-conditional-product-fees-for-checkout' ), array(
				                'strong' => array(),
				            ) ); 
			            ?>
		            </p>
					<div class="review-customer">
						<div class="customer-img">
							<img src="<?php echo esc_url(WCPFC_PRO_PLUGIN_URL . 'admin/images/premium-upgrade-img/customer-profile-img.jpeg'); ?>" alt="<?php esc_attr_e('Customer Profile Image', 'woocommerce-conditional-product-fees-for-checkout'); ?>">
						</div>
						<div class="customer-name">
							<span><?php esc_html_e( 'Michaël Vorentjes', 'woocommerce-conditional-product-fees-for-checkout' ); ?></span>
							<div class="customer-rating-bottom">
								<div class="customer-ratings">
									<span class="dashicons dashicons-star-filled"></span>
									<span class="dashicons dashicons-star-filled"></span>
									<span class="dashicons dashicons-star-filled"></span>
									<span class="dashicons dashicons-star-filled"></span>
									<span class="dashicons dashicons-star-filled"></span>
								</div>
								<div class="verified-customer">
									<span class="dashicons dashicons-yes-alt"></span>
									<?php esc_html_e( 'Verified Customer', 'woocommerce-conditional-product-fees-for-checkout' ); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="upgrade-to-pro-faqs">
			<h2><?php esc_html_e( 'FAQs', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h2>
			<div class="upgrade-faqs-main">
				<div class="upgrade-faqs-list">
					<div class="upgrade-faqs-header">
						<h3><?php esc_html_e( 'Do you offer support for the plugin? What’s it like?', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h3>
					</div>
					<div class="upgrade-faqs-body">
						<p>
						<?php 
							echo sprintf(
							    esc_html__('Yes! You can read our %s or submit a %s. We are very responsive and strive to do our best to help you.', 'woocommerce-conditional-product-fees-for-checkout'),
							    '<a href="' . esc_url('https://docs.thedotstore.com/collection/95-extra-fees') . '" target="_blank">' . esc_html__('knowledge base', 'woocommerce-conditional-product-fees-for-checkout') . '</a>',
							    '<a href="' . esc_url('https://www.thedotstore.com/support-ticket/') . '" target="_blank">' . esc_html__('support ticket', 'woocommerce-conditional-product-fees-for-checkout') . '</a>',
							);

						?>
						</p>
					</div>
				</div>
				<div class="upgrade-faqs-list">
					<div class="upgrade-faqs-header">
						<h3><?php esc_html_e( 'What payment methods do you accept?', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h3>
					</div>
					<div class="upgrade-faqs-body">
						<p><?php esc_html_e( 'You can pay with your credit card using Stripe checkout. Or your PayPal account.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></p>
					</div>
				</div>
				<div class="upgrade-faqs-list">
					<div class="upgrade-faqs-header">
						<h3><?php esc_html_e( 'What’s your refund policy?', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h3>
					</div>
					<div class="upgrade-faqs-body">
						<p><?php esc_html_e( 'We have a 14-day money-back guarantee.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></p>
					</div>
				</div>
				<div class="upgrade-faqs-list">
					<div class="upgrade-faqs-header">
						<h3><?php esc_html_e( 'I have more questions…', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h3>
					</div>
					<div class="upgrade-faqs-body">
						<p>
						<?php 
							echo sprintf(
							    esc_html__('No problem, we’re happy to help! Please reach out at %s.', 'woocommerce-conditional-product-fees-for-checkout'),
							    '<a href="' . esc_url('mailto:hello@thedotstore.com') . '" target="_blank">' . esc_html('hello@thedotstore.com') . '</a>',
							);

						?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<div class="upgrade-to-premium-btn">
			<a href="javascript:void(0);" target="_blank" class="upgrade-now"><?php esc_html_e( 'Get The Premium Version', 'woocommerce-conditional-product-fees-for-checkout' ); ?><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="crown" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" class="svg-inline--fa fa-crown fa-w-20 fa-3x" width="22" height="20"><path fill="#000" d="M528 448H112c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16zm64-320c-26.5 0-48 21.5-48 48 0 7.1 1.6 13.7 4.4 19.8L476 239.2c-15.4 9.2-35.3 4-44.2-11.6L350.3 85C361 76.2 368 63 368 48c0-26.5-21.5-48-48-48s-48 21.5-48 48c0 15 7 28.2 17.7 37l-81.5 142.6c-8.9 15.6-28.9 20.8-44.2 11.6l-72.3-43.4c2.7-6 4.4-12.7 4.4-19.8 0-26.5-21.5-48-48-48S0 149.5 0 176s21.5 48 48 48c2.6 0 5.2-.4 7.7-.8L128 416h384l72.3-192.8c2.5.4 5.1.8 7.7.8 26.5 0 48-21.5 48-48s-21.5-48-48-48z" class=""></path></svg></a>
		</div>
	</div>
</div>
</div>
</div>
</div>
<?php 
