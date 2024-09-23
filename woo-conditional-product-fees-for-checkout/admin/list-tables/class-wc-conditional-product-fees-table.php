<?php
/**
 * Handles plugin rules listing form
 * 
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @since   3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * WC_Conditional_product_Fees_Table class.
 *
 * @extends WP_List_Table
 */
if ( ! class_exists( 'WC_Conditional_product_Fees_Table' ) ) {

	class WC_Conditional_product_Fees_Table extends WP_List_Table {

		const post_type = 'wc_conditional_fee';
		private static $wcpfc_found_items = 0;
        private static $wcpfc_object = null;
        private static $admin_object = null;
        public $_column_headers = null;

        /**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			parent::__construct( array(
				'singular' => 'post',
				'plural'   => 'posts',
				'ajax'     => false
			) );
            self::$wcpfc_object = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro( '', '' );
            self::$admin_object = new Woocommerce_Conditional_Product_Fees_For_Checkout_Pro_Admin( '', '' );

            add_filter( 'default_hidden_columns', array( $this, 'default_hidden_columns' ), 10, 2 );
		}

		/**
		 * get_columns function.
		 *
		 * @return  array
		 * @since 1.0.0
		 *
		 */
		public function get_columns() {
			$column_array = array(
				'cb'                => '<input type="checkbox" />',
				'title'             => esc_html__( 'Title', 'woocommerce-conditional-product-fees-for-checkout' ),
            );
            if( class_exists('WPML_Custom_Columns') ){
				global $sitepress;
				$lang_column = new WPML_Custom_Columns($sitepress);
				$column_array['icl_translations'] = $lang_column->get_flags_column();
			}
            $column_array += array(
				'amount'            => esc_html__( 'Fee', 'woocommerce-conditional-product-fees-for-checkout' ),
				'start_date'    	=> sprintf( '%1$s', esc_html__( 'Start Date', 'woocommerce-conditional-product-fees-for-checkout' ) ),
				'end_date' 			=> sprintf( '%1$s', esc_html__( 'End Date', 'woocommerce-conditional-product-fees-for-checkout' ) ),
				'status'            => esc_html__( 'Status', 'woocommerce-conditional-product-fees-for-checkout' ),
				'date'              => esc_html__( 'Date', 'woocommerce-conditional-product-fees-for-checkout' ),
			);
			return $column_array;
		}

		/**
		 * get_sortable_columns function.
		 *
		 * @return array
		 * @since 1.0.0
		 *
		 */
		protected function get_sortable_columns() {
			$columns = array(
				'title'  => array( 'title', true ),
				'date'   => array( 'date', false ),
			);

			return $columns;
		}

		/**
		 * Get Methods to display
		 *
		 * @since 1.0.0
		 */
		public function prepare_items() {
			$this->prepare_column_headers();
			$per_page = $this->get_items_per_page( 'chk_fees_per_page' );

			$get_search  = filter_input( INPUT_POST, 's', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$get_orderby = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$get_order   = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$get_status  = filter_input( INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			$args = array(
				'posts_per_page' => $per_page,
				'orderby'        => array(
                    'menu_order'    => 'ASC',
                    'post_date'     => 'DESC',
                ),
				'offset'         => ( $this->get_pagenum() - 1 ) * $per_page,
                'post_type'      => self::post_type,
			);

			if ( isset( $get_search ) && ! empty( $get_search ) ) {
				$new_url = esc_url_raw( add_query_arg('s', $get_search) );
				
				wp_safe_redirect($new_url);
				exit();
			} elseif( isset( $get_search ) && empty( $get_search ) ) {
				$new_url = esc_url_raw( remove_query_arg('s') );
				
				wp_safe_redirect($new_url);
				exit();
			} else {
				$get_search = filter_input( INPUT_GET, 's', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				if ( isset( $get_search ) && ! empty( $get_search ) ) {
					$args['s'] = trim( wp_unslash( $get_search ) );
				}
			}

			if ( isset( $get_orderby ) && ! empty( $get_orderby ) ) {
				if ( 'title' === $get_orderby ) {
					$args['orderby'] = 'title';
                } elseif ( 'date' === $get_orderby ) {
					$args['orderby'] = 'date';
				}
			}

			if ( isset( $get_order ) && ! empty( $get_order ) ) {
				if ( 'asc' === strtolower( $get_order ) ) {
					$args['order'] = 'ASC';
				} elseif ( 'desc' === strtolower( $get_order ) ) {
					$args['order'] = 'DESC';
				}
			}

			if( !empty($get_status) ){
                if( 'enable' === strtolower($get_status) ){
                    $args['post_status'] = 'publish';
                } elseif( 'disable' === strtolower($get_status) ) {
                    $args['post_status'] = 'draft';
                } else {
                    $args['post_status'] = 'all';
                }
            }

			$this->items = $this->wcpfc_find( $args );

			$total_items = $this->wcpfc_sj_count_method();

			$total_pages = ceil( $total_items / $per_page );

			$this->set_pagination_args( array(
				'total_items' => $total_items,
				'total_pages' => $total_pages,
				'per_page'    => $per_page,
			) );
		}

		/**
		 * Get Rules submenu
		 *
		 * @since 1.0.0
		 */
		public function views() {
            $status_links   = array();
            $all_args       = array( 'page' => 'wcpfc-pro-list', 'status' => 'all' );
            $num_posts      = wp_count_posts( self::post_type, 'readable' );
        
            if ( empty( $num_posts ) )
                return;
        
            $total_posts  = array_sum( (array) $num_posts );

            // Subtract post types that are not included in the admin all list.
            foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state ) {
                $total_posts -= $num_posts->$state;
            }

            $all_inner_html = sprintf(
                /* translators: %s: Number of posts. */
                _nx(
                    'All <span class="count">(%s)</span>',
                    'All <span class="count">(%s)</span>',
                    $total_posts,
                    'posts',
                    'woocommerce-conditional-product-fees-for-checkout'
                ),
                number_format_i18n( $total_posts )
            );

            $get_request_status  = filter_input( INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            $request_status = isset($get_request_status) && !empty($get_request_status) ? sanitize_text_field($get_request_status) : '';
            $status_links['all'] = array(
                'url'     => esc_url( add_query_arg( $all_args, admin_url( 'admin.php' ) ) ),
                'label'   => $all_inner_html,
                'current' => empty($request_status) || ( 'all' === strtolower($request_status) ),
            );

            foreach ( get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' ) as $status ) {
                $class = '';
    
                $status_name = $status->name;
    
                if ( empty( $num_posts->$status_name ) ) {
                    continue;
                }
    
                if( !empty($request_status) ){
                    if( 'enable' === strtolower($request_status) ){
                        $check_status = 'publish';
                        $query_status = 'enable';
                    } elseif( 'disable' === strtolower($request_status) ) {
                        $check_status = 'draft';
                        $query_status = 'disable';
                    } else {
                        $check_status = 'all';
                    } 
                }

                if ( isset( $check_status ) && $status_name === $check_status ) {
                    $class = 'current';
                }

                if( 'publish' === strtolower($status_name) ){
                    $query_status = 'enable';
                } elseif( 'draft' === strtolower($status_name) ) {
                    $query_status = 'disable';
                }

                $status_args = array(
					'page'   => 'wcpfc-pro-list',
					'status' => isset($query_status) && !empty($query_status) ? $query_status : 'all',
				);

                $status_label = sprintf(
                    translate_nooped_plural( $status->label_count, $num_posts->$status_name ),
                    number_format_i18n( $num_posts->$status_name )
                );
                
                $status_links[ $status_name ] = array(
                    'url'     => esc_url( add_query_arg( $status_args, admin_url( 'admin.php' ) ) ),
                    'label'   => $status_label,
                    'current' => isset( $check_status ) && $status_name === $check_status,
                );
            }

            $views = $this->get_views_links( $status_links );
            $allow_tags = array( 
                'li' => array( 
                    'class' => array()
                ),
                'a' => array(
                    'href' => array(),
                    'title' => array(),
                    'class' => array(),
                    'aria-current' => array()
                ),
            );
            echo "<div>";
            echo "<ul class='subsubsub'>";
            if ( !empty($views) && is_array($views) ) {
            	foreach ( $views as $class => $view ) {
	                $views[ $class ] = "<li class='$class'>$view";
	            }
	            echo wp_kses( implode( " |</li>", $views ) . "</li>", $allow_tags );
            }
            echo '</ul>';
            echo '</div>';
        }

		/**
		 */
		public function no_items() {
			esc_html_e( 'No fees rule found.', 'woocommerce-conditional-product-fees-for-checkout' );
		}

		/**
		 * Checkbox column
		 *
		 * @param string
		 *
		 * @return mixed
		 * @since 1.0.0
		 *
		 */
		public function column_cb( $item ) {
			if ( ! $item->ID ) {
				return;
			}

			return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', 'method_id_cb', esc_attr( $item->ID ) );
		}

		/**
		 * Output the shipping name column.
		 *
		 * @param object $item
		 *
		 * @return string
		 * @since 1.0.0
		 *
		 */
		public function column_title( $item ) {
			$editurl = add_query_arg( array(
				'page'   => 'wcpfc-pro-list',
				'action' => 'edit',
				'id'   => $item->ID,
			), admin_url( 'admin.php' ) );

			$method_name = '<strong>
                            <a href="' . wp_nonce_url( $editurl, 'edit_' . $item->ID, '_wpnonce' ) . '" class="row-title">' . esc_html( $item->post_title ) . '</a>
                        </strong>';

			echo wp_kses( $method_name, self::$wcpfc_object->allowed_html_tags() );
		}

		/**
		 * Generates and displays row action links.
		 *
		 * @param object $item Link being acted upon.
		 * @param string $column_name Current column name.
		 * @param string $primary Primary column name.
		 *
		 * @return string Row action output for links.
		 * @since 1.0.0
		 *
		 */
		protected function handle_row_actions( $item, $column_name, $primary ) {
			if ( $primary !== $column_name ) {
				return '';
			}

			$edit_method_url = add_query_arg( array(
				'page'   => 'wcpfc-pro-list',
				'action' => 'edit',
				'id'   => $item->ID
			), admin_url( 'admin.php' ) );
			$editurl         = wp_nonce_url( $edit_method_url, 'edit_' . $item->ID, '_wpnonce' );

			$delete_method_url = add_query_arg( array(
				'page'   => 'wcpfc-pro-list',
				'action' => 'delete',
				'id'   => $item->ID
			), admin_url( 'admin.php' ) );
			$delurl            = wp_nonce_url( $delete_method_url, 'del_' . $item->ID, '_wpnonce' );

            $duplicate_method_url = add_query_arg( array(
				'page'   => 'wcpfc-pro-list',
				'action' => 'duplicate',
				'id'   => $item->ID
			), admin_url( 'admin.php' ) );
			$duplicateurl      = wp_nonce_url( $duplicate_method_url, 'duplicate_' . $item->ID, '_wpnonce' );

			$actions            = array();
			$actions['edit']    = '<a href="' . esc_url($editurl) . '">' . __( 'Edit', 'woocommerce-conditional-product-fees-for-checkout' ) . '</a>';
			$actions['delete']  = '<a href="' . esc_url($delurl) . '">' . __( 'Delete', 'woocommerce-conditional-product-fees-for-checkout' ) . '</a>';
			$actions['duplicate']   = '<a href="' . esc_url($duplicateurl) . '">' . __( 'Duplicate', 'woocommerce-conditional-product-fees-for-checkout' ) . '</a>';

			return $this->row_actions( $actions );
		}

		/**
		 * Output the method enabled column.
		 *
		 * @param object $item
		 *
		 * @return string
		 */
		public function column_status( $item ) {
			if ( 0 === $item->ID ) {
				return esc_html__( 'Trash', 'woocommerce-conditional-product-fees-for-checkout' );
			}

			$item_status 		= get_post_meta( $item->ID, 'fee_settings_status', true );
			$fees_status     	= get_post_status( $item->ID );
			$fees_status_chk 	= ( ( ! empty( $fees_status ) && 'publish' === $fees_status ) || empty( $fees_status ) ) ? 'checked' : '';
			if ( 'on' === $item_status ) {
				$status = '<label class="switch">
								<input type="checkbox" name="fee_settings_status" id="fees_status_id" value="on" '.esc_attr( $fees_status_chk ).' data-smid="'. esc_attr( $item->ID ) .'">
								<div class="slider round"></div>
							</label>';
			} else {
				$status = '<label class="switch">
								<input type="checkbox" name="fee_settings_status" id="fees_status_id" value="on" '.esc_attr( $fees_status_chk ).' data-smid="'. esc_attr( $item->ID ) .'">
								<div class="slider round"></div>
							</label>';
			}

			return $status;
		}

        /**
		 * Output the method amount column.
		 *
		 * @param object $item
		 *
		 * @return int|float
		 * @since 1.0.0
		 *
		 */
		public function column_amount( $item ) {
			if ( 0 === $item->ID ) {
				return esc_html__( 'null', 'woocommerce-conditional-product-fees-for-checkout' );
			}
            $amount = get_post_meta( $item->ID, 'fee_settings_product_cost', true );
			if( !is_null($amount) ) {
				$amount_type  = get_post_meta( $item->ID, 'fee_settings_select_fee_type', true );
				if( 'fixed' === $amount_type ) {
					return esc_html( get_woocommerce_currency_symbol() ) . '' . $amount;
				} else if( 'both' === $amount_type && strpos($amount, '+') !== false ){
					$newamount = explode('+', $amount);
					return $newamount[0] . '% + ' . $newamount[1];
				} else {
					return $amount . '%';
				}
			} else {
				return esc_html__( 'N/A', 'woocommerce-conditional-product-fees-for-checkout' );
			}
		}

		/**
		 * Output the rule start date.
		 *
		 * @param object $item
		 *
		 * @return mixed
		 * @since 3.9.3
		 *
		 */
		public function column_start_date( $item ) {
            if ( 0 === $item->ID ) {
				return '-';
			}
            $getFeeStartDate = get_post_meta( $item->ID, 'fee_settings_start_date', true );
            if( !empty($getFeeStartDate) ){
            	$wpDateFormat = get_option('date_format');
				$newDate = gmdate($wpDateFormat, strtotime($getFeeStartDate));
                return $newDate;
            } else {
                return '-';
            }
        }

        /**
		 * Output the rule end date.
		 *
		 * @param object $item
		 *
		 * @return mixed
		 * @since 3.9.3
		 *
		 */
		public function column_end_date( $item ) {
            if ( 0 === $item->ID ) {
				return '-';
			}
            $getFeeEndDate   = get_post_meta( $item->ID, 'fee_settings_end_date', true );
            if( !empty($getFeeEndDate) ){
            	$wpDateFormat = get_option('date_format');
				$newDate = gmdate($wpDateFormat, strtotime($getFeeEndDate));
                return $newDate;
            } else {
                return '-';
            }
        }

		/**
		 * Output the method date column.
		 *
		 * @param object $item
		 *
		 * @return mixed $item->post_date;
		 * @since 1.0.0
		 *
		 */
		public function column_date( $item ) {
			if ( 0 === $item->ID ) {
				return esc_html__( 'N/A', 'woocommerce-conditional-product-fees-for-checkout' );
			}
            
            $date_obj = date_create($item->post_date);
            $new_format = sprintf( esc_html__( '%s at %s', 'woocommerce-conditional-product-fees-for-checkout' ), date_format( $date_obj, get_option('date_format')), date_format( $date_obj, get_option('time_format')));

			return $new_format;
		}

        /**
		 * Output the WPML translation column.
		 *
		 * @param object $item
		 *
		 * @return mixed WPML translation colum HTML;
		 * @since 1.0.0
		 *
		 */
        public function column_icl_translations( $item ){
			global $sitepress;
			$language_column = new WPML_Custom_Columns($sitepress);
			return $language_column->add_content_for_posts_management_column( 'icl_translations', $item->ID );
		}

		/**
		 * Display bulk action in filter
		 *
		 * @return array $actions
		 * @since 1.0.0
		 *
		 */
		public function get_bulk_actions() {
			$actions = array(
				'disable' => esc_html__( 'Disable', 'woocommerce-conditional-product-fees-for-checkout' ),
				'enable'  => esc_html__( 'Enable', 'woocommerce-conditional-product-fees-for-checkout' ),
				'delete'  => esc_html__( 'Delete', 'woocommerce-conditional-product-fees-for-checkout' )
			);

			return $actions;
		}

		/**
		 * Process bulk actions
		 *
		 * @since 1.0.0
		 */
		public function process_bulk_action() {
			$delete_nonce     = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$get_method_id_cb = filter_input( INPUT_POST, 'method_id_cb', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
			$method_id_cb     = ! empty( $get_method_id_cb ) ? array_map( 'sanitize_text_field', wp_unslash( $get_method_id_cb ) ) : array();

			$action = $this->current_action();

			if ( ! isset( $method_id_cb ) ) {
				return;
			}

			$deletenonce = wp_verify_nonce( $delete_nonce, 'bulk-shippingmethods' );

			if ( ! isset( $deletenonce ) && 1 !== $deletenonce ) {
				return;
			}

			$items = array_filter( array_map( 'absint', $method_id_cb ) );

			if ( ! $items ) {
				return;
			}

			if ( 'delete' === $action ) {
				foreach ( $items as $id ) {
					wp_delete_post( $id );
				}
				self::$admin_object->wcpfc_updated_message( 'deleted', '' );
			} elseif ( 'enable' === $action ) {

				foreach ( $items as $id ) {
					$enable_post = array(
						'post_type'   => self::post_type,
						'ID'          => $id,
						'post_status' => 'publish'
					);
					wp_update_post( $enable_post );
                    update_post_meta( $id, 'fee_settings_status', 'on' );
				}
				self::$admin_object->wcpfc_updated_message( 'enabled', '' );
			} elseif ( 'disable' === $action ) {
				foreach ( $items as $id ) {
					$disable_post = array(
						'post_type'   => self::post_type,
						'ID'          => $id,
						'post_status' => 'draft'
					);
					wp_update_post( $disable_post );
                    update_post_meta( $id, 'fee_settings_status', 'off' );
				}
				self::$admin_object->wcpfc_updated_message( 'disabled', '' );
			}
            delete_transient( 'get_top_ten_fees' );
			delete_transient( 'get_all_fees' );
			delete_transient( 'get_all_dashboard_fees' );
		}

		/**
		 * Find post data
		 *
		 * @param mixed $args
		 * @param string $get_orderby
		 *
		 * @return array $posts
		 * @since 1.0.0
		 *
		 */
		public static function wcpfc_find( $args = '' ) {
			$defaults = array(
				'post_status'    => 'any',
				'posts_per_page' => - 1,
				'offset'         => 0,
				'orderby'        => array (
                    'ID' => 'ASC',
                )
			);

			$args = wp_parse_args( $args, $defaults );

			$args['post_type'] = self::post_type;

			$wc_wcpfc_query = new WP_Query( $args );
			$posts          = $wc_wcpfc_query->query( $args );

            self::$wcpfc_found_items = $wc_wcpfc_query->found_posts;

			return $posts;
		}

        /**
		 * Count post data
		 *
		 * @return string
		 * @since 1.0.0
		 *
		 */
		public static function wcpfc_sj_count_method() {
			return self::$wcpfc_found_items;
		}

		/**
	     * Display the search box.
	     *
	     * @since 3.1.0
	     * @access public
	     *
	     * @param string $text    The 'submit' button label.
	     * @param string $input_id The input id.
	     */
	    public function search_box( $text, $input_id ) {
	    	$input_id = $input_id . '-search-input';
	        ?>
	        <p class="search-box">
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php esc_html_e( $text, 'woocommerce-conditional-product-fees-for-checkout' ); ?>:</label>
				<input type="search" id="<?php echo esc_attr( $input_id ); ?>" placeholder="<?php esc_attr_e( 'Fee title', 'woocommerce-conditional-product-fees-for-checkout' ) ?>" name="s" value="<?php _admin_search_query(); ?>" />
					<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
			</p>
        	<?php
    	}
	}
}
