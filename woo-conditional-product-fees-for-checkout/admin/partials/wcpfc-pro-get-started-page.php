<?php
/**
 * Handles plugin about page
 * 
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once( plugin_dir_path( __FILE__ ) . 'header/plugin-header.php' );
?>
<div class="wcpfc-section-left">
	<div class="wcpfc-main-table res-cl">
		<div class="dots-getting-started-main">
	        <div class="getting-started-content">
	            <span><?php esc_html_e( 'How to Get Started', 'woocommerce-conditional-product-fees-for-checkout' ); ?></span>
	            <h3><?php esc_html_e( 'Welcome to Extra Fees Plugin', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h3>
	            <p><?php esc_html_e( 'Thank you for choosing our top-rated WooCommerce Extra Fees plugin. Our user-friendly interface makes it easy to set up different conditional fee rules.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></p>
	            <p>
	                <?php 
	                echo sprintf(
	                    esc_html__('To help you get started, watch the quick tour video on the right. For more help, explore our help documents or visit our %s for detailed video tutorials.', 'woocommerce-conditional-product-fees-for-checkout'),
	                    '<a href="' . esc_url('https://www.youtube.com/@Dotstore16') . '" target="_blank">' . esc_html__('YouTube channel', 'woocommerce-conditional-product-fees-for-checkout') . '</a>',
	                );
	                ?>
	            </p>
	            <div class="getting-started-actions">
	                <a href="<?php echo esc_url(add_query_arg(array('page' => 'wcpfc-pro-list'), admin_url('admin.php'))); ?>" class="quick-start"><?php esc_html_e( 'Manage Fees Rules', 'woocommerce-conditional-product-fees-for-checkout' ); ?><span class="dashicons dashicons-arrow-right-alt"></span></a>
	                <a href="https://docs.thedotstore.com/article/949-beginners-guide-for-extra-fees" target="_blank" class="setup-guide"><span class="dashicons dashicons-book-alt"></span><?php esc_html_e( 'Read the Setup Guide', 'woocommerce-conditional-product-fees-for-checkout' ); ?></a>
	            </div>
	        </div>
	        <div class="getting-started-video">
	            <iframe width="960" height="600" src="<?php echo esc_url('https://www.youtube.com/embed/xoLP2yjVoJs'); ?>" title="<?php esc_attr_e( 'Plugin Tour', 'woocommerce-conditional-product-fees-for-checkout' ); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
	        </div>
	    </div>
	</div>
</div>
</div>
</div>
</div>
</div>
<?php
