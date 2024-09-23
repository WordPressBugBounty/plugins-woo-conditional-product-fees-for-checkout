<?php

/**
 * Handles plugin import/export settings
 * 
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @since   3.1
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';
// Function for free plugin content
function wcpfc_free_import_export_settings_content() {
    ?>
	<div class="wcpfc-section-left">
		<div class="wcpfc-main-table res-cl wcpfc-import-export-section wcpfc-upgrade-pro-to-unlock">
			<h2><?php 
    echo esc_html__( 'Import &amp; Export Settings', 'woocommerce-conditional-product-fees-for-checkout' );
    ?><div class="wcpfc-pro-label"></div></h2>
			<table class="table-outer import-export-table">
				<tbody>
				<tr>
					<td><label for="blogname"><?php 
    echo esc_html__( 'Export Settings Data', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></label>
					</td>
					<td>
						<form method="post">
							<div class="wcpfc_main_container">
								<p class="wcpfc_button_container"><?php 
    submit_button(
        __( 'Export', 'woocommerce-conditional-product-fees-for-checkout' ),
        'primary',
        'submit',
        false
    );
    ?></p>
								<p class="wcpfc_content_container">
									<span><?php 
    esc_html_e( 'Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></span>
								</p>
							</div>
						</form>

					</td>
				</tr>
				<tr>
					<td><label for="blogname"><?php 
    echo esc_html__( 'Import Settings Data', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></label>
					</td>
					<td>
						<form method="post" enctype="multipart/form-data">
							<div class="wcpfc_main_container">
								<p>
									<input type="file" name="import_file"/>
								</p>
								<p class="wcpfc_button_container">
									<input type="submit" class="button button-primary" value="<?php 
    esc_attr_e( 'Import', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>">
								</p>
								<p class="wcpfc_content_container">
									<span><?php 
    esc_html_e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></span>
								</p>
							</div>
						</form>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?php 
}

wcpfc_free_import_export_settings_content();
?>
</div>
</div>
</div>
</div>
