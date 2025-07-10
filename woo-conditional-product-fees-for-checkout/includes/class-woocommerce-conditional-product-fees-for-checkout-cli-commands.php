<?php
/**
 * Handles plugin WP CLI commands.
 *
 * @since      3.9.2
 * @package    Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @subpackage Woocommerce_Conditional_Product_Fees_For_Checkout_Pro/includes
 * @author     Multidots <inquiry@multidots.in>
 */

class DS_Command_Line {
    
    /**
	 * Initialize the variable which we used in this class
	 *
	 * @since    3.9.2
	 */
	public function __construct() {
		$this->post_type = 'wc_conditional_fee';
	}

    /**
     * Check your site's environment
     * 
     */
	public function environment() {
        WP_CLI::log( sprintf( 'Environment: %s', wp_get_environment_type() ) );
	}

    /**
     * Check your plugin details
     * 
     */
    public function plugin_details( $args, $assoc_args ){
        WP_CLI::log( WP_CLI::colorize( "%C=== Welcome to theDotstore Plugin ===%n" ));

        $all_plugins = get_plugins();
        if( !empty($all_plugins) ){
            $plugin_details = $all_plugins['woocommerce-conditional-product-fees-for-checkout-premium/woocommerce-conditional-product-fees-for-checkout.php'];
        }
        $data = array(
                array( 
                    'label' => 'Plugin license type',
                    'value' => WCPFC_PRO_PREMIUM_VERSION
                ),
                array( 
                    'label' => 'Plugin name',
                    'value' => WCPFC_PRO_PLUGIN_NAME
                ),
                array( 
                    'label' => 'Plugin version',
                    'value' => WCPFC_PRO_PLUGIN_VERSION
                ),
            );
        if( !empty($plugin_details) ){
            $data = array_merge( $data, 
                        array(
                            array( 
                                'label' => 'Plugin WC compatibility',
                                'value' => $plugin_details['WC tested up to'],
                            ),
                            array( 
                                'label' => 'Plugin author',
                                'value' => $plugin_details['AuthorName'],
                            ),
                            array( 
                                'label' => 'Plugin textdomain',
                                'value' => $plugin_details['TextDomain'],
                            ),
                        )
                    );
        }
        $cli_formatter = new \WP_CLI\Formatter( $assoc_args, array(
            'label',
            'value'
        ));
        $cli_formatter->display_items($data);
    }

    /**
     * Enable all fees from listing
     * 
     * ## OPTIONS
     * 
     * [--id=<number>]
     * : Specific fee's ID to enable it in the plugin
     * 
     * ## EXAMPLES
     *  
     * wp dotstore enable_fees --id=28
     */
    public function enable_fees( $args, $assoc_args ){
        
        if( isset($assoc_args['id']) && !empty($assoc_args['id']) ){
            $all_fee_ids = intval($assoc_args['id']);
            if( $this->post_type !== get_post_type($all_fee_ids) ){
                WP_CLI::error( 'Please add '.$this->post_type.' post type fee id.' );
            }
            $all_fee_ids = array($all_fee_ids);
        } else {
            WP_CLI::confirm( "Are you sure you want to enable all fees?", $assoc_args );
            $post_args = array(
                'post_type' => $this->post_type,
                'post_status' => array( 'publish', 'draft' ),
                'post_per_pages' => -1,
                'fields'        => 'ids',
            );
            $all_fee = new WP_Query( $post_args );
            $all_fee_ids = $all_fee->posts ? $all_fee->posts : 0;
        }

        if( !empty($all_fee_ids) ){
            $count = count($all_fee_ids);
            $progress = \WP_CLI\Utils\make_progress_bar( 'Enabling fees', $count );
            foreach( $all_fee_ids as $all_fee_id ){
                $enable_post = array(
                    'post_type'   => $this->post_type,
                    'ID'          => $all_fee_id,
                    'post_status' => 'publish'
                );
                wp_update_post( $enable_post );
                update_post_meta( $all_fee_id, 'fee_settings_status', 'on' );
                $progress->tick();
            }
            $progress->finish();
        }
        WP_CLI::success( esc_html__( 'All fees have been enabled...!', 'woocommerce-conditional-product-fees-for-checkout' ) );
    }

    /**
     * Disable all fees from listing
     * 
     * ## OPTIONS
     * 
     * [--id=<number>]
     * : Specific fee's ID to disable it in the plugin
     * 
     * ## EXAMPLES
     *  
     * wp dotstore disable_fees --id=28
     */
    public function disable_fees( $args, $assoc_args ){
        
        if( isset($assoc_args['id']) && !empty($assoc_args['id']) ){
            $all_fee_ids = intval($assoc_args['id']);
            if( $this->post_type !== get_post_type($all_fee_ids) ){
                WP_CLI::error( 'Please add '.$this->post_type.' post type fee id.' );
            }
            $all_fee_ids = array($all_fee_ids);
        } else {
            WP_CLI::confirm( "Are you sure you want to disable all fees?", $assoc_args );
            $post_args = array(
                'post_type' => $this->post_type,
                'post_status' => array( 'publish', 'draft' ),
                'post_per_pages' => -1,
                'fields'        => 'ids',
            );
            $all_fee = new WP_Query( $post_args );
            $all_fee_ids = $all_fee->posts ? $all_fee->posts : 0;
        }

        if( $all_fee_ids > 0 ){
            $count = count($all_fee_ids);
            $progress = \WP_CLI\Utils\make_progress_bar( 'Disabling fees', $count );
            foreach( $all_fee_ids as $all_fee_id ){
                $disable_post = array(
                    'post_type'   => $this->post_type,
                    'ID'          => $all_fee_id,
                    'post_status' => 'draft'
                );
                wp_update_post( $disable_post );
                update_post_meta( $all_fee_id, 'fee_settings_status', 'off' );
                $progress->tick();
            }
            $progress->finish();
        }
        WP_CLI::success( esc_html__( 'All fees have been disabled...!', 'woocommerce-conditional-product-fees-for-checkout' ) );
    }

    /**
     * Delete all fees from listing
     * 
     * ## NOTE
     * 
     * This option will not undo so please be careful with it. We recommend you launch the export command before firing it, for backup purposes.
     * 
     * ## OPTIONS
     * 
     * [--id=<number>]
     * : Specific fee's ID to delete it in the plugin
     * 
     * ## EXAMPLES
     *  
     * wp dotstore delete_fees --id=28
     */
    public function delete_fees( $args, $assoc_args ) {

        if( isset($assoc_args['id']) && !empty($assoc_args['id']) ){
            $all_fee_ids = intval($assoc_args['id']);
            if( $this->post_type !== get_post_type($all_fee_ids) ){
                WP_CLI::error( 'Please add '.$this->post_type.' post type fee id.' );
            }
            $all_fee_ids = array($all_fee_ids);
        } else {
            WP_CLI::confirm( "Are you sure you want to delete all fees?", $assoc_args );
            $post_type = 'wc_conditional_fee';
            $post_args = array(
                'post_type' => $post_type,
                'post_status' => array( 'publish', 'draft' ),
                'post_per_pages' => -1,
                'fields'        => 'ids',
            );
            $all_fee = new WP_Query( $post_args );
            $all_fee_ids = $all_fee->posts ? $all_fee->posts : 0;
        }

        if( $all_fee_ids > 0 ){
            $count = count($all_fee_ids);
            $progress = \WP_CLI\Utils\make_progress_bar( 'Deleting fees', $count );
            foreach( $all_fee_ids as $all_fee_id ){
                wp_delete_post( $all_fee_id );
                $progress->tick();
            }
            $progress->finish();
        }
        WP_CLI::success( esc_html__( 'All fees have been deleted...!', 'woocommerce-conditional-product-fees-for-checkout' ) );
    }

    /**
     * We are asking a question and returning an answer as a string.
     *
     * @param $question
     *
     * @return string
     */
    protected function ask( $question ) {
        
        global $wp_filesystem;
        WP_Filesystem();
        // Adding space to question and showing it.
        $wp_filesystem->put_contents( 'php://stdout', $question . '' );
 
        return esc_html( trim( fgets( STDIN ) ) );
    }

    /**
     * Create new fee with basic configuration
     * 
     * ## NOTE
     * 
     * For ease of use this command we introduce wizard flow to easyly complete your needs with command.
     */
    public function create_fee() {

        //Ask for fee name
        $fee_name = $this->ask( "Please enter fee name: " );
        if ( '' === $fee_name ) {
            WP_CLI::error( "Sorry, please enter fee name!" );
            exit;
        }

        //Ask for fee type
        $fee_type = $this->ask( "Please enter fee amount type(percentage/fixed): " );
        if ( '' === $fee_type ) {
            WP_CLI::error( "Sorry, please enter type of fee amount!" );
            exit;
        }

        //Ask for fee status
        $fee_status = $this->ask( "Please enter fee status(on/off): " );
        if ( '' === $fee_status ) {
            WP_CLI::error( "Sorry, please enter status of fee!" );
            exit;
        }

        //Ask for fee amount
        $fee_amount = $this->ask( "Please enter fee amount: " );
        if ( '' === $fee_amount ) {
            WP_CLI::error( "Sorry, please enter fee amount!" );
            exit;
        }

        if ( "on" === $fee_status ) {
            $post_status = 'publish';
        } else {
            $post_status = 'draft';
        }
        delete_transient( "get_all_fees" );
        $fee_post = array(
            'post_title'  => wp_strip_all_tags( $fee_name ),
            'post_status' => $post_status,
            'post_type'   => 'wc_conditional_fee',
        );
        $post_id  = wp_insert_post( $fee_post );
        
        if ( '' !== $post_id && 0 !== $post_id ) {
            $feesArray = array();
            update_post_meta( $post_id, 'fee_settings_product_cost', $fee_amount );
            update_post_meta( $post_id, 'fee_settings_select_fee_type', $fee_type );
            $feesArray[] = array(
                'product_fees_conditions_condition' => 'country',
                'product_fees_conditions_is' => 'is_equal_to',
                'product_fees_conditions_values' => array()
            );
            update_post_meta( $post_id, 'product_fees_metabox', $feesArray );
            WP_CLI::success( sprintf( esc_html__("Your fee with name %s has been created with basic configuration!", 'woocommerce-conditional-product-fees-for-checkout' ), $fee_name ) );
        } else {
            WP_CLI::error( sprintf( esc_html__("Sorry! your fee %s is not created!", 'woocommerce-conditional-product-fees-for-checkout' ), $fee_name ) );
        }
    }

    /**
     * Import fee setings from JSON file.
     * 
     * ## OPTIONS
     * 
     * <json-file>...
     * : A file path to import fee data into database which must end with .json format.
     * 
     * ## EXAMPLES
     * 
     * wp dotstore import_fee ./wcpfc-settings.json
     */
    public function import_fee( $args ){
        if( empty($args[0]) ) {
            WP_CLI::line( WP_CLI::error( esc_html____( 'Please add a file path to import', 'woocommerce-conditional-product-fees-for-checkout' ) ) );
        }
        if( ! file_exists($args[0]) ){
            WP_CLI::line( WP_CLI::error( esc_html____( 'File not exist...', 'woocommerce-conditional-product-fees-for-checkout' ) ) );
        }
        $import_args = array( 'cli_type' => 'import', 'file' => $args[0] );
        $plugin_name = 'woocommerce-conditional-product-fees-for-checkout'; //This will use from plugin file
        $version     = WCPFC_PRO_PLUGIN_VERSION; //This will use from plugin file
        $plugin_admin = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Admin( $plugin_name, $version );
        WP_CLI::line( WP_CLI::success( $plugin_admin->wcpfc_pro_import_export_fees__premium_only($import_args) ) );
        
    }

    /**
     * Export fee settings to JSON file.
     *
     * ## NOTE
     * 
     * It will create json file in current directory where you launch this command.
     */
    public function export_fee() {
        $export_args = array( 'cli_type' => 'export' );
        $plugin_name = 'woocommerce-conditional-product-fees-for-checkout'; //This will use from plugin file
        $version     = WCPFC_PRO_PLUGIN_VERSION; //This will use from plugin file
        $plugin_admin = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Admin( $plugin_name, $version );
        $export_result = $plugin_admin->wcpfc_pro_import_export_fees__premium_only($export_args);
        WP_CLI::line( WP_CLI::success($export_result['message']) );
        WP_CLI::line( WP_CLI::colorize( esc_html__( 'Open in new tab and download: ', 'woocommerce-conditional-product-fees-for-checkout' )."%C".$export_result['download_path']."%n") );
    }

    /**
     * Using this command we can Clone/Duplicate Fee from exist fee.
     * 
     * ## OPTIONS
     * [--fee_id=<number>]
     * : A fee id from where we need to get configuration data to clone/duplicate fee.
     * 
     * [--author_mail=<string>]
     * : A valid user email id that must be exists on the site to allocate author to this clone/duplicate fee.
     * 
     * ## EXAMPLES
     * 
     * wp dotstore clone_fee --fee_id=405 --author_mail=contact@thedotstore.com
     */
    public function clone_fee( $args, $assoc_args ) {
        $original_fee = isset($assoc_args['fee_id']) && !empty($assoc_args['fee_id']) ? intval($assoc_args['fee_id']) : 0;
        $author_mail = isset($assoc_args['author_mail']) && !empty($assoc_args['author_mail']) ? sanitize_email($assoc_args['author_mail']) : '';
        $user_details = get_user_by( 'email', $author_mail );
        
        if( $this->post_type !== get_post_type($original_fee) || ! in_array( get_post_status($original_fee), array('draft', 'publish'), true ) ){
            WP_CLI::error( esc_html__( 'Original fee does not exist.', 'woocommerce-conditional-product-fees-for-checkout' ) );
        }

        if( ! $user_details ){
            WP_CLI::error( esc_html__( 'Author email does not exist. Please enter valid user email of this site.', 'woocommerce-conditional-product-fees-for-checkout' ) );
        }
        
        $new_post_author = isset( $user_details->ID ) && !empty( $user_details->ID ) ? intval($user_details->ID) : 0;
        $fee_obj = get_post( $original_fee );

        if ( isset( $fee_obj ) && null !== $fee_obj ) {
            /* New post data array */
            $args = array(
                'comment_status' => $fee_obj->comment_status,
                'ping_status'    => $fee_obj->ping_status,
                'post_author'    => $new_post_author,
                'post_content'   => $fee_obj->post_content,
                'post_excerpt'   => $fee_obj->post_excerpt,
                'post_name'      => $fee_obj->post_name,
                'post_parent'    => $fee_obj->post_parent,
                'post_password'  => $fee_obj->post_password,
                'post_status'    => 'draft',
                'post_title'     => $fee_obj->post_title . '-duplicate',
                'post_type'      => $this->post_type,
                'to_ping'        => $fee_obj->to_ping,
                'menu_order'     => $fee_obj->menu_order,
            );
            /* Duplicate the post by wp_insert_post() function */
            $new_post_id = wp_insert_post( $args );
            /* Duplicate all post meta-data */
            $post_meta_data = get_post_meta( $original_fee );
            if ( 0 !== count( $post_meta_data ) ) {
                foreach ( $post_meta_data as $meta_key => $meta_data ) {
                    if ( '_wp_old_slug' === $meta_key ) {
                        continue;
                    }
                    $meta_value = maybe_unserialize( $meta_data[0] );
                    update_post_meta( $new_post_id, $meta_key, $meta_value );
                }
            }
            WP_CLI::success( esc_html__( 'Your fee(#'.$new_post_id.') has been cloned with disable mode.', 'woocommerce-conditional-product-fees-for-checkout' ) );
        } else {
            WP_CLI::error( esc_html__( 'Trouble in getting fee details', 'woocommerce-conditional-product-fees-for-checkout' ) );
        }
    }
}
