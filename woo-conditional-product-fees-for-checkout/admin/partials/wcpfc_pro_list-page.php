<?php

/**
 * Handles plugin rules listing
 * 
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @since   3.9.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
////////////////////////
////// New Layout //////
////////////////////////
/**
 * WCPFC_Rule_Listing_Page class.
 */
if ( !class_exists( 'WCPFC_Rule_Listing_Page' ) ) {
    class WCPFC_Rule_Listing_Page {
        /**
         * Output the Admin UI
         *
         * @since 3.9.0
         */
        const wcpfc_post_type = 'wc_conditional_fee';

        private static $admin_object = null;

        /**
         * Display output
         *
         * @since 3.5
         *
         * @uses wcpfc_sj_save_method
         * @uses wcpfc_sj_add_extra_fee_form
         * @uses wcpfc_sj_edit_method_screen
         * @uses wcpfc_sj_delete_method
         * @uses wcpfc_sj_duplicate_method
         * @uses wcpfc_sj_list_methods_screen
         *
         * @access   public
         */
        public static function wcpfc_sj_output() {
            self::$admin_object = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Admin('', '');
            $action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $post_id_request = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
            $get_wcpfc_add = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            if ( isset( $action ) && !empty( $action ) ) {
                if ( 'add' === $action ) {
                    self::wcpfc_sj_save_method();
                    self::wcpfc_sj_add_extra_fee_form();
                } elseif ( 'edit' === $action ) {
                    if ( isset( $get_wcpfc_add ) && !empty( $get_wcpfc_add ) ) {
                        $getnonce = wp_verify_nonce( $get_wcpfc_add, 'edit_' . $post_id_request );
                        if ( isset( $getnonce ) && 1 === $getnonce ) {
                            self::wcpfc_sj_save_method( $post_id_request );
                            self::wcpfc_sj_edit_method();
                        } else {
                            self::$admin_object->wcpfc_updated_message( 'nonce_check', "" );
                        }
                    }
                } elseif ( 'delete' === $action ) {
                    self::wcpfc_sj_delete_method( $post_id_request );
                } elseif ( 'duplicate' === $action ) {
                    self::wcpfc_sj_duplicate_method( $post_id_request );
                } else {
                    self::wcpfc_sj_list_methods_screen();
                }
            } else {
                self::wcpfc_sj_list_methods_screen();
            }
        }

        /**
         * Save extra fee method when add or edit
         *
         * @param int $method_id
         *
         * @return bool false when nonce is not verified, $zone id, $zone_type is blank, Country also blank, Postcode field also blank, saving error when form submit
         * @uses dpad_sm_count_method()
         *
         * @since    3.5
         *
         * @uses Woocommerce_Dynamic_Pricing_And_Discount_Pro_Admin::dpad_updated_message()
         */
        public static function wcpfc_sj_save_method( $method_id = 0 ) {
            if ( is_network_admin() ) {
                $admin_url = admin_url( 'admin.php' );
            } else {
                $admin_url = admin_url( 'admin.php' );
            }
            $action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $submit_fee = filter_input( INPUT_POST, 'submitFee', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $wcpfc_pro_conditions_save = filter_input( INPUT_POST, 'wcpfc_pro_fees_conditions_save', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            if ( isset( $submit_fee ) && !empty( $submit_fee ) ) {
                if ( isset( $wcpfc_pro_conditions_save ) && wp_verify_nonce( sanitize_text_field( $wcpfc_pro_conditions_save ), 'wcpfc_pro_fees_conditions_save_action' ) && self::wcpfc_post_type === $post_type ) {
                    if ( isset( $action ) && !empty( $action ) ) {
                        delete_transient( "get_all_fees" );
                        $get_method_id = filter_input( INPUT_POST, 'fee_post_id', FILTER_SANITIZE_NUMBER_INT );
                        $get_fee_settings_product_fee_title = filter_input( INPUT_POST, 'fee_settings_product_fee_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                        $get_fee_settings_product_cost = filter_input( INPUT_POST, 'fee_settings_product_cost', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                        $get_fee_settings_select_fee_type = filter_input( INPUT_POST, 'fee_settings_select_fee_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                        $get_fee_settings_start_date = filter_input( INPUT_POST, 'fee_settings_start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                        $get_fee_settings_end_date = filter_input( INPUT_POST, 'fee_settings_end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                        $get_fee_settings_status = filter_input( INPUT_POST, 'fee_settings_status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                        $get_display_fees_in_product_page = filter_input( INPUT_POST, 'display_fees_in_product_page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                        $get_fee_settings_select_taxable = filter_input( INPUT_POST, 'fee_settings_select_taxable', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                        $get_fee_settings_taxable_type = filter_input( INPUT_POST, 'fee_settings_taxable_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                        $get_fee_show_on_checkout_only = filter_input( INPUT_POST, 'fee_show_on_checkout_only', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                        $get_fees_on_cart_total = filter_input( INPUT_POST, 'fees_on_cart_total', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                        $get_ds_time_from = filter_input( INPUT_POST, 'ds_time_from', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                        $get_ds_time_to = filter_input( INPUT_POST, 'ds_time_to', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                        $get_ds_select_day_of_week = filter_input(
                            INPUT_POST,
                            'ds_select_day_of_week',
                            FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                            FILTER_REQUIRE_ARRAY
                        );
                        $method_id = ( !empty( $get_method_id ) ? intval( $get_method_id ) : 0 );
                        $fee_settings_product_fee_title = ( isset( $get_fee_settings_product_fee_title ) ? sanitize_text_field( $get_fee_settings_product_fee_title ) : '' );
                        $fee_settings_product_cost = ( isset( $get_fee_settings_product_cost ) ? sanitize_text_field( $get_fee_settings_product_cost ) : '' );
                        $fee_settings_select_fee_type = ( isset( $get_fee_settings_select_fee_type ) ? sanitize_text_field( $get_fee_settings_select_fee_type ) : '' );
                        $fee_settings_start_date = ( isset( $get_fee_settings_start_date ) ? sanitize_text_field( $get_fee_settings_start_date ) : '' );
                        $fee_settings_end_date = ( isset( $get_fee_settings_end_date ) ? sanitize_text_field( $get_fee_settings_end_date ) : '' );
                        $fee_settings_status = ( isset( $get_fee_settings_status ) ? sanitize_text_field( $get_fee_settings_status ) : 'off' );
                        $display_fees_in_product_page = ( isset( $get_display_fees_in_product_page ) ? sanitize_text_field( $get_display_fees_in_product_page ) : '' );
                        $fee_settings_select_taxable = ( isset( $get_fee_settings_select_taxable ) ? sanitize_text_field( $get_fee_settings_select_taxable ) : '' );
                        $fee_settings_taxable_type = ( isset( $get_fee_settings_taxable_type ) ? sanitize_text_field( $get_fee_settings_taxable_type ) : '' );
                        $fee_show_on_checkout_only = ( isset( $get_fee_show_on_checkout_only ) ? sanitize_text_field( $get_fee_show_on_checkout_only ) : '' );
                        $fees_on_cart_total = ( isset( $get_fees_on_cart_total ) ? sanitize_text_field( $get_fees_on_cart_total ) : '' );
                        $ds_time_from = ( isset( $get_ds_time_from ) ? sanitize_text_field( $get_ds_time_from ) : '' );
                        $ds_time_to = ( isset( $get_ds_time_to ) ? sanitize_text_field( $get_ds_time_to ) : '' );
                        $ds_select_day_of_week = ( isset( $get_ds_select_day_of_week ) ? array_map( 'sanitize_text_field', $get_ds_select_day_of_week ) : array() );
                        if ( !empty( $fee_settings_product_fee_title ) ) {
                            $fee_exist = post_exists(
                                $fee_settings_product_fee_title,
                                '',
                                '',
                                self::wcpfc_post_type
                            );
                            if ( $fee_exist > 0 && $action === 'add' || $fee_exist > 0 && $fee_exist !== $method_id && $action === 'edit' ) {
                                wp_safe_redirect( add_query_arg( array(
                                    'page'    => 'wcpfc-pro-list',
                                    'message' => 'exist',
                                ), $admin_url ) );
                                exit;
                            }
                        }
                        $get_condition_key = filter_input(
                            INPUT_POST,
                            'condition_key',
                            FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                            FILTER_REQUIRE_ARRAY
                        );
                        $fees = filter_input(
                            INPUT_POST,
                            'fees',
                            FILTER_SANITIZE_FULL_SPECIAL_CHARS,
                            FILTER_REQUIRE_ARRAY
                        );
                        if ( isset( $fee_settings_status ) && !empty( $fee_settings_status ) && "on" === $fee_settings_status ) {
                            $post_status = 'publish';
                        } else {
                            $post_status = 'draft';
                        }
                        $wcpfc_fee_count = self::wcpfc_sj_count_method();
                        if ( '' === $method_id || 0 === $method_id ) {
                            $fee_post = array(
                                'post_title'  => wp_strip_all_tags( $fee_settings_product_fee_title ),
                                'post_status' => $post_status,
                                'post_type'   => self::wcpfc_post_type,
                                'menu_order'  => $wcpfc_fee_count + 1,
                            );
                            $post_id = wp_insert_post( $fee_post );
                        } else {
                            $fee_post = array(
                                'ID'          => sanitize_text_field( $method_id ),
                                'post_title'  => wp_strip_all_tags( $fee_settings_product_fee_title ),
                                'post_status' => $post_status,
                                'post_type'   => self::wcpfc_post_type,
                            );
                            $post_id = wp_update_post( $fee_post );
                        }
                        if ( '' !== $post_id && 0 !== $post_id ) {
                            if ( $post_id > 0 ) {
                                $feesArray = array();
                                $conditionsValuesArray = array();
                                $condition_key = ( isset( $get_condition_key ) ? $get_condition_key : array() );
                                $fees_conditions = $fees['product_fees_conditions_condition'];
                                $conditions_is = $fees['product_fees_conditions_is'];
                                $conditions_values = ( isset( $fees['product_fees_conditions_values'] ) && !empty( $fees['product_fees_conditions_values'] ) ? $fees['product_fees_conditions_values'] : array() );
                                $size = count( $fees_conditions );
                                foreach ( array_keys( $condition_key ) as $key ) {
                                    if ( !array_key_exists( $key, $conditions_values ) ) {
                                        $conditions_values[$key] = array();
                                    }
                                }
                                uksort( $conditions_values, 'strnatcmp' );
                                foreach ( $conditions_values as $v ) {
                                    $conditionsValuesArray[] = $v;
                                }
                                for ($i = 0; $i < $size; $i++) {
                                    $feesArray[] = array(
                                        'product_fees_conditions_condition' => $fees_conditions[$i],
                                        'product_fees_conditions_is'        => $conditions_is[$i],
                                        'product_fees_conditions_values'    => $conditionsValuesArray[$i],
                                    );
                                }
                                update_post_meta( $post_id, 'fee_settings_product_cost', $fee_settings_product_cost );
                                update_post_meta( $post_id, 'fee_settings_select_fee_type', $fee_settings_select_fee_type );
                                update_post_meta( $post_id, 'fee_settings_start_date', $fee_settings_start_date );
                                update_post_meta( $post_id, 'fee_settings_end_date', $fee_settings_end_date );
                                update_post_meta( $post_id, 'fee_settings_status', $fee_settings_status );
                                update_post_meta( $post_id, 'display_fees_in_product_page', $display_fees_in_product_page );
                                update_post_meta( $post_id, 'fee_settings_select_taxable', $fee_settings_select_taxable );
                                update_post_meta( $post_id, 'fee_settings_taxable_type', $fee_settings_taxable_type );
                                update_post_meta( $post_id, 'fee_show_on_checkout_only', $fee_show_on_checkout_only );
                                update_post_meta( $post_id, 'fees_on_cart_total', $fees_on_cart_total );
                                update_post_meta( $post_id, 'ds_time_from', $ds_time_from );
                                update_post_meta( $post_id, 'ds_time_to', $ds_time_to );
                                update_post_meta( $post_id, 'ds_select_day_of_week', $ds_select_day_of_week );
                                update_post_meta( $post_id, 'product_fees_metabox', $feesArray );
                            } else {
                                echo '<div class="updated error"><p>' . esc_html__( 'Error saving Fees.', 'woocommerce-conditional-product-fees-for-checkout' ) . '</p></div>';
                                return false;
                            }
                        }
                        if ( 'add' === $action ) {
                            $message = 'created';
                        }
                        if ( 'edit' === $action ) {
                            $message = 'saved';
                        }
                        wp_safe_redirect( add_query_arg( array(
                            'page'     => 'wcpfc-pro-list',
                            'action'   => 'edit',
                            'id'       => $post_id,
                            '_wpnonce' => wp_create_nonce( 'edit_' . $post_id ),
                            'message'  => $message,
                        ), $admin_url ) );
                        exit;
                    }
                } else {
                    ?>
                    <div id="message" class="notice notice-error is-dismissible">
                        <p><?php 
                    esc_html_e( 'There is an error with the security check.', 'woocommerce-conditional-product-fees-for-checkout' );
                    ?></p>
                        <button type="button" class="notice-dismiss">
                            <span class="screen-reader-text"><?php 
                    esc_html_e( 'Dismiss this notice.', 'woocommerce-conditional-product-fees-for-checkout' );
                    ?></span>
                        </button>
                    </div>
                    <?php 
                    wp_die();
                }
            }
        }

        /**
         * Edit discount rule
         *
         * @since    3.9.0
         */
        public static function wcpfc_sj_edit_method() {
            require_once plugin_dir_path( __FILE__ ) . 'wcpfc-pro-add-new-page.php';
        }

        /**
         * Add discount rule
         *
         * @since    3.9.0
         */
        public static function wcpfc_sj_add_extra_fee_form() {
            require_once plugin_dir_path( __FILE__ ) . 'wcpfc-pro-add-new-page.php';
        }

        /**
         * Delete shipping method
         *
         * @param int $id
         *
         * @access   public
         *
         * @since    3.9.0
         *
         */
        public static function wcpfc_sj_delete_method( $id ) {
            $_wpnonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $getnonce = wp_verify_nonce( $_wpnonce, 'del_' . $id );
            if ( isset( $getnonce ) && 1 === $getnonce ) {
                wp_delete_post( $id );
                wp_safe_redirect( add_query_arg( array(
                    'page'   => 'wcpfc-pro-list',
                    'delete' => 'true',
                ), admin_url( 'admin.php' ) ) );
                exit;
            } else {
                self::$admin_object->wcpfc_updated_message( 'nonce_check', "" );
            }
        }

        /**
         * Duplicate shipping method
         *
         * @param int $id
         *
         * @access   public
         * @uses Woocommerce_Dynamic_Pricing_And_Discount_Pro_Admin::dpad_updated_message()
         *
         * @since    1.0.0
         *
         */
        public static function wcpfc_sj_duplicate_method( $post_id ) {
            $_wpnonce = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $getnonce = wp_verify_nonce( $_wpnonce, 'duplicate_' . $post_id );
            if ( isset( $getnonce ) && 1 === $getnonce ) {
                /* Get all the original post data */
                $post = get_post( $post_id );
                /* Get current user and make it new post user (duplicate post) */
                $current_user = wp_get_current_user();
                $new_post_author = $current_user->ID;
                /* If post data exists, duplicate the data into new duplicate post */
                if ( isset( $post ) && null !== $post ) {
                    /* New post data array */
                    $args = array(
                        'comment_status' => $post->comment_status,
                        'ping_status'    => $post->ping_status,
                        'post_author'    => $new_post_author,
                        'post_content'   => $post->post_content,
                        'post_excerpt'   => $post->post_excerpt,
                        'post_name'      => $post->post_name,
                        'post_parent'    => $post->post_parent,
                        'post_password'  => $post->post_password,
                        'post_status'    => 'draft',
                        'post_title'     => $post->post_title . '-duplicate',
                        'post_type'      => self::wcpfc_post_type,
                        'to_ping'        => $post->to_ping,
                        'menu_order'     => $post->menu_order,
                    );
                    /* Duplicate the post by wp_insert_post() function */
                    $new_post_id = wp_insert_post( $args );
                    /* Duplicate all post meta-data */
                    $post_meta_data = get_post_meta( $post_id );
                    if ( 0 !== count( $post_meta_data ) ) {
                        foreach ( $post_meta_data as $meta_key => $meta_data ) {
                            if ( '_wp_old_slug' === $meta_key ) {
                                continue;
                            }
                            $meta_value = maybe_unserialize( $meta_data[0] );
                            update_post_meta( $new_post_id, $meta_key, $meta_value );
                        }
                    }
                }
                $wcpfcnonce = wp_create_nonce( 'edit_' . $new_post_id );
                wp_safe_redirect( add_query_arg( array(
                    'page'     => 'wcpfc-pro-list',
                    'id'       => $new_post_id,
                    'action'   => 'edit',
                    '_wpnonce' => esc_attr( $wcpfcnonce ),
                ), admin_url( 'admin.php' ) ) );
                exit;
            } else {
                self::$admin_object->wcpfc_updated_message( 'nonce_check', "" );
            }
        }

        /**
         * Count total shipping method
         *
         * @return int $count_method
         * @since    3.5
         *
         */
        public static function wcpfc_sj_count_method() {
            $shipping_method_args = array(
                'post_type'      => self::wcpfc_post_type,
                'post_status'    => array('publish', 'draft'),
                'posts_per_page' => -1,
                'orderby'        => 'ID',
                'order'          => 'DESC',
            );
            $sm_post_query = new WP_Query($shipping_method_args);
            $shipping_method_list = $sm_post_query->posts;
            return count( $shipping_method_list );
        }

        /**
         * list_shipping_methods function.
         *
         * @since    3.9.0
         *
         * @uses WC_Conditional_product_Fees_Table class
         * @uses WC_Conditional_product_Fees_Table::process_bulk_action()
         * @uses WC_Conditional_product_Fees_Table::prepare_items()
         * @uses WC_Conditional_product_Fees_Table::search_box()
         * @uses WC_Conditional_product_Fees_Table::display()
         *
         * @access public
         *
         */
        public static function wcpfc_sj_list_methods_screen() {
            if ( !class_exists( 'WC_Conditional_product_Fees_Table' ) ) {
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'list-tables/class-wc-conditional-product-fees-table.php';
            }
            $link = add_query_arg( array(
                'page'   => 'wcpfc-pro-list',
                'action' => 'add',
            ), admin_url( 'admin.php' ) );
            require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';
            ?>
            <div class="wrap">
                <form method="post" enctype="multipart/form-data">
                    <div class="wcpfc-section-left">
                        <div class="wcpfc-main-table res-cl wcpfc-add-rule-page">
                            <h1 class="wp-heading-inline"><?php 
            esc_html_e( 'Product Fees', 'woocommerce-conditional-product-fees-for-checkout' );
            ?></h1>
                            <?php 
            if ( !(wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code()) ) {
                $valid_rules = get_option( 'wcpfc_limited_allowed_rules', '' );
                if ( !empty( $valid_rules ) && intval( $valid_rules ) >= 10 ) {
                    ?>
                                    <a class="page-title-action dots-btn-with-brand-color upgrade-now" href="javascript:void(0);"><?php 
                    esc_html_e( 'Add New 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
                    ?></a>
                                    <?php 
                } else {
                    ?>
                                    <a class="page-title-action dots-btn-with-brand-color" href="<?php 
                    echo esc_url( $link );
                    ?>"><?php 
                    esc_html_e( 'Add New', 'woocommerce-conditional-product-fees-for-checkout' );
                    ?></a>
                                    <?php 
                }
            } else {
                ?>
                                <a class="page-title-action dots-btn-with-brand-color" href="<?php 
                echo esc_url( $link );
                ?>"><?php 
                esc_html_e( 'Add New', 'woocommerce-conditional-product-fees-for-checkout' );
                ?></a>
                                <?php 
            }
            $request_s = filter_input( INPUT_GET, 's', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            if ( isset( $request_s ) && !empty( $request_s ) ) {
                echo sprintf( '<span class="subtitle">' . esc_html__( 'Search results for &#8220;%s&#8221;', 'woocommerce-conditional-product-fees-for-checkout' ) . '</span>', esc_html( $request_s ) );
            }
            wp_nonce_field( 'sorting_conditional_fee_action', 'sorting_conditional_fee' );
            $WC_Conditional_product_Fees_Table = new WC_Conditional_product_Fees_Table();
            $WC_Conditional_product_Fees_Table->process_bulk_action();
            $WC_Conditional_product_Fees_Table->prepare_items();
            $WC_Conditional_product_Fees_Table->search_box( esc_html__( 'Search', 'woocommerce-conditional-product-fees-for-checkout' ), 'shipping-method' );
            $WC_Conditional_product_Fees_Table->display();
            ?>
                        </div>
                    </div>
                </form>
            </div>
            </div>
            </div>
            </div>
            </div>
            <?php 
        }

    }

}