<?php
/**
 * AI Suggested Fee upgrade popup for free-plan users.
 *
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="wcpfc-bestfit-free-modal" class="wcpfc-bestfit-free-modal upgrade-to-pro-modal-new" title="<?php esc_attr_e( '✨ AI Suggested Fee', 'woocommerce-conditional-product-fees-for-checkout' ); ?>" style="display:none;">
	<div class="wcpfc-bestfit-free-layout">
		<div class="wcpfc-bestfit-free-copy">
			<h3 class="wcpfc-bestfit-free-title"><?php esc_html_e( 'Unlock AI Suggested Fee with Pro', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h3>
			<p class="wcpfc-bestfit-free-lead">
				<?php esc_html_e( 'Discover hidden revenue opportunities with AI-powered fee recommendations tailored to your WooCommerce store.', 'woocommerce-conditional-product-fees-for-checkout' ); ?>
			</p>
			<ul class="wcpfc-bestfit-free-list pro-feature-list">
				<li><?php esc_html_e( 'AI analyzes your store and recommends high-impact fees.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></li>
				<li><?php esc_html_e( 'Personalized rules based on your business and customer behavior.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></li>
				<li><?php esc_html_e( 'Revenue estimates before you create a fee.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></li>
				<li><?php esc_html_e( 'Create draft fee rules in one click.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></li>
				<li><?php esc_html_e( 'Review and publish when you\'re ready.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></li>
			</ul>
			<div class="wcpfc-bestfit-free-footer">
				<a class="pro-feature-trial-btn wcpfc-bestfit-free-upgrade" href="javascript:void(0);"><?php esc_html_e( 'Upgrade Now', 'woocommerce-conditional-product-fees-for-checkout' ); ?></a>
				<span><?php esc_html_e( '14-day, no-questions-asked money-back guarantee.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></span>
			</div>
		</div>
		<div class="wcpfc-bestfit-free-image">
			<img src="<?php echo esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/premium-upgrade-img/ai-suggested-fee-list.png' ); ?>" alt="<?php esc_attr_e( 'Upgrade to Pro', 'woocommerce-conditional-product-fees-for-checkout' ); ?>">
		</div>
	</div>
</div>
