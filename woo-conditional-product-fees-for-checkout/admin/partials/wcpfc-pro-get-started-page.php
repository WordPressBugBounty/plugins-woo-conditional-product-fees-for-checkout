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

if ( ! wcpffc_fs()->is__premium_only() && ! wcpffc_fs()->can_use_premium_code() ) {

    // Unlock morefeature section coupon code
    $discount_coupon = 'BONUS20';

    // Dotstore Marketing Free Plugins
    $dsmrkt_free_plugins = get_transient( '_dotstore_marketing_free_plugins' );

    require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

    if ( ! $dsmrkt_free_plugins ) {
        $info = plugins_api('query_plugins', array(
            'per_page' => 30,
            'author' => 'dots',
            'fields' =>
                array(
                    'short_description' => true,
                    'description' => false,
                    'sections' => false,
                    'tested' => false,
                    'requires' => true,
                    'ratings' => false,
                    'downloaded' => false,
                    'downloadlink' => true,
                    'last_updated' => false,
                    'added' => false,
                    'tags' => false,
                    'compatibility' => false,
                    'homepage' => false,
                    'versions' => false,
                    'donate_link' => false,
                    'reviews' => false,
                    'banners' => false,
                    'active_installs' => true,
                    'group' => false,
                    'contributors' => false,
                    'author' => false,
                    'requires_plugins' => false,
                )
            )
        );
        if ( is_wp_error( $info ) ) {
            $dsmrkt_free_plugins = array();
        } else {
            $dsmrkt_free_plugins = $info->plugins;
        }
        set_transient( '_dotstore_marketing_free_plugins', $dsmrkt_free_plugins, 7 * DAY_IN_SECONDS );
    }

    // Create a slug → plugin data map
    $lookup = [];
    foreach ( $dsmrkt_free_plugins as $free_plugin ) {
        $lookup[$free_plugin['slug']] = $free_plugin;
    }

    // Rebuild the array in the custom order
    $dsmrkt_sorted_plugins = $dsmrkt_free_plugins;

    if( !empty( WCPFC_OTHER_PLUGIN_IDS ) ) {
        $dsmrkt_sorted_plugins = [];
        foreach ( WCPFC_OTHER_PLUGIN_IDS as $slug ) {
            if ( isset( $lookup[$slug] ) ) {
                $dsmrkt_sorted_plugins[] = $lookup[$slug];
            }
        }
    } 
}
?>
<div class="wcpfc-section-left">
	<div class="wcpfc-main-table res-cl">
		
        <div class="dots-getting-started-main element-shadow">
	        <div class="getting-started-content">
	            <span><?php esc_html_e( 'How to Get Started', 'woocommerce-conditional-product-fees-for-checkout' ); ?></span>
	            <h3><?php esc_html_e( 'Welcome to Extra Fees Plugin', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h3>
	            <p><?php esc_html_e( 'Thank you for choosing our top-rated WooCommerce Extra Fees plugin. Our user-friendly interface makes it easy to set up different conditional fee rules.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></p>
	            <p>
	                <?php 
	                echo sprintf(
                        /* translators: %s: YouTube channel link */
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
        <?php if ( ! wcpffc_fs()->is__premium_only() && ! wcpffc_fs()->can_use_premium_code() ) { ?>
            <div class="spacer-3"></div>
            
            <div class="dots-getting-started-main element-shadow">
                <div class="getting-started-content">
                    <h3><img src="<?php echo esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/premium-upgrade-img/pro-feature-icon.svg' ); ?>" class="premium_title_icon" alt="<?php esc_attr_e( 'Premium title icon', 'woocommerce-conditional-product-fees-for-checkout' ); ?>" /><?php esc_html_e( 'Get the Extra Fee Pro and unlock more features.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h3>
                    <p><?php esc_html_e( 'Add advanced extra fees to your WooCommerce store easily and increase your sales by 15%.', 'woocommerce-conditional-product-fees-for-checkout' ); ?></p>
                    <span><strong><?php esc_html_e( 'Key Features', 'woocommerce-conditional-product-fees-for-checkout' ); ?></strong></span>
                    <div class="getting-started-feature-list">
                        <ul>
                            <li><?php esc_html_e( 'Schedule Custom Fees for Special Day or Holiday using Date and time range', 'woocommerce-conditional-product-fees-for-checkout' ); ?></li>
                            <li><?php esc_html_e( 'Set Optional Fees and Empower Customers with Easy Choices on Checkout', 'woocommerce-conditional-product-fees-for-checkout' ); ?></li>
                            <li><?php esc_html_e( 'Charge Fees Based on Per Additional Product Quantity', 'woocommerce-conditional-product-fees-for-checkout' ); ?></li>
                            <li><?php esc_html_e( 'Merge All Fees to One Total and Simplify Checkout Process', 'woocommerce-conditional-product-fees-for-checkout' ); ?></li>
                            <li><?php esc_html_e( 'Display Fees on the Product Page', 'woocommerce-conditional-product-fees-for-checkout' ); ?></li>
                            <li><?php esc_html_e( 'Apply Custom Tax For Extra Fees', 'woocommerce-conditional-product-fees-for-checkout' ); ?></li>
                        </ul>
                    </div>
                    <div class="getting-started-actions premium-actions">
                        <input type="hidden" class="getting-started-discount-code" value="<?php echo esc_attr( $discount_coupon ); ?>" />
                        <a class="upgrade-now" href="javascript:void(0);"><?php esc_html_e( 'Unlock Powerful Features', 'woocommerce-conditional-product-fees-for-checkout' ); ?></a>
                        <span class="bonus-tip"><?php printf( esc_html( '%3$sBonus:%2$s Extra Fees Lite users get %1$s20%% off the regular price%2$s, automatically applied at checkout.', 'woocommerce-conditional-product-fees-for-checkout' ), '<strong class="ds-offer">', '</strong>', '<strong>' ); ?></span>
                    </div>
                </div>
                <div class="getting-started-image">
                    <img src="<?php echo esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/premium-upgrade-img/UpSell Plugins Banner.png' ); ?>" alt="<?php esc_attr_e( 'Personal Plan', 'woocommerce-conditional-product-fees-for-checkout' ); ?>" class="element-shadow" />
                </div>
            </div>
            <?php if( !empty( $dsmrkt_sorted_plugins ) ) { ?>
                <div class="spacer-3"></div>
                
                <div class="dots-other-plugin-main element-shadow">
                    <h3><?php esc_html_e( 'Our Other Free Plugins', 'woocommerce-conditional-product-fees-for-checkout' ); ?></h3>
                    <div class="dsmrkt-free-plugins">
                        <?php foreach( $dsmrkt_sorted_plugins as $dsmrkt_free_plugin ) { 

                            if ( ! empty( $dsmrkt_free_plugin['icons']['svg'] ) ) {
                                $plugin_icon_url = $dsmrkt_free_plugin['icons']['svg'];
                            } elseif ( ! empty( $dsmrkt_free_plugin['icons']['2x'] ) ) {
                                $plugin_icon_url = $dsmrkt_free_plugin['icons']['2x'];
                            } elseif ( ! empty( $dsmrkt_free_plugin['icons']['1x'] ) ) {
                                $plugin_icon_url = $dsmrkt_free_plugin['icons']['1x'];
                            } else {
                                $plugin_icon_url = $dsmrkt_free_plugin['icons']['default'];
                            }
                            $plugins_allowedtags = array(
                                'a'       => array(
                                    'href'   => array(),
                                    'title'  => array(),
                                    'target' => array(),
                                ),
                                'abbr'    => array( 'title' => array() ),
                                'acronym' => array( 'title' => array() ),
                                'code'    => array(),
                                'pre'     => array(),
                                'em'      => array(),
                                'strong'  => array(),
                                'ul'      => array(),
                                'ol'      => array(),
                                'li'      => array(),
                                'p'       => array(),
                                'br'      => array(),
                            );
                            $plugin_title = wp_kses( $dsmrkt_free_plugin['name'], $plugins_allowedtags );
                            $version = wp_kses( $dsmrkt_free_plugin['version'], $plugins_allowedtags );
                            $name = wp_strip_all_tags( $plugin_title . ' ' . $version );
                
                            $requires_php = isset( $dsmrkt_free_plugin['requires_php'] ) ? $dsmrkt_free_plugin['requires_php'] : null;
                            $requires_wp  = isset( $dsmrkt_free_plugin['requires'] ) ? $dsmrkt_free_plugin['requires'] : null;

                            $compatible_php = is_php_version_compatible( $requires_php );
                            $compatible_wp  = is_wp_version_compatible( $requires_wp );

                            /** Action Button Start ( get whole 'wp_get_plugin_action_button' method here from WP core )*/
                            $action_button    = '';
                            $data             = (object) $dsmrkt_free_plugin;
                            $plugin_status    = install_plugin_install_status( $data );
                            $requires_plugins = $data->requires_plugins ?? array();

                            // Determine the status of plugin dependencies.
                            $installed_plugins                   = get_plugins();
                            $active_plugins                      = get_option( 'active_plugins', array() );
                            $plugin_dependencies_count           = count( $requires_plugins );
                            $installed_plugin_dependencies_count = 0;
                            $active_plugin_dependencies_count    = 0;
                            foreach ( $requires_plugins as $dependency ) {
                                foreach ( array_keys( $installed_plugins ) as $installed_plugin_file ) {
                                    if ( str_contains( $installed_plugin_file, '/' ) && explode( '/', $installed_plugin_file )[0] === $dependency ) {
                                        ++$installed_plugin_dependencies_count;
                                    }
                                }

                                foreach ( $active_plugins as $active_plugin_file ) {
                                    if ( str_contains( $active_plugin_file, '/' ) && explode( '/', $active_plugin_file )[0] === $dependency ) {
                                        ++$active_plugin_dependencies_count;
                                    }
                                }
                            }
                            $all_plugin_dependencies_installed = $installed_plugin_dependencies_count === $plugin_dependencies_count;
                            $all_plugin_dependencies_active    = $active_plugin_dependencies_count === $plugin_dependencies_count;

                            if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
                                switch ( $plugin_status['status'] ) {
                                    case 'install':
                                        if ( $plugin_status['url'] ) {
                                            if ( $compatible_php && $compatible_wp && $all_plugin_dependencies_installed && ! empty( $data->download_link ) ) {
                                                $action_button = sprintf(
                                                    '<a class="install-now button" data-slug="%s" href="%s" aria-label="%s" data-name="%s" role="button">%s</a>',
                                                    esc_attr( $data->slug ),
                                                    esc_url( $plugin_status['url'] ),
                                                    /* translators: %s: Plugin name and version. */
                                                    esc_attr( sprintf( _x( 'Install %s now', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' ), $name ) ),
                                                    esc_attr( $name ),
                                                    _x( 'Install Free Plugin', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' )
                                                );
                                            } else {
                                                $action_button = sprintf(
                                                    '<button type="button" class="install-now button button-disabled" disabled="disabled">%s</button>',
                                                    _x( 'Install Free Plugin', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' )
                                                );
                                            }
                                        }
                                        break;
                                    case 'update_available':
                                        if ( $plugin_status['url'] ) {
                                            if ( $compatible_php && $compatible_wp ) {
                                                $action_button = sprintf(
                                                    '<a class="update-now button aria-button-if-js" data-plugin="%s" data-slug="%s" href="%s" aria-label="%s" data-name="%s" role="button">%s</a>',
                                                    esc_attr( $plugin_status['file'] ),
                                                    esc_attr( $data->slug ),
                                                    esc_url( $plugin_status['url'] ),
                                                    /* translators: %s: Plugin name and version. */
                                                    esc_attr( sprintf( _x( 'Update %s now', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' ), $name ) ),
                                                    esc_attr( $name ),
                                                    _x( 'Update Free Plugin', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' )
                                                );
                                            } else {
                                                $action_button = sprintf(
                                                    '<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
                                                    _x( 'Update Free Plugin', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' )
                                                );
                                            }
                                        }
                                        break;
                                    case 'latest_installed':
                                    case 'newer_installed':
                                        if ( is_plugin_active( $plugin_status['file'] ) ) {
                                            $action_button = sprintf(
                                                '<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
                                                _x( 'Active', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' )
                                            );
                                        } elseif ( current_user_can( 'activate_plugin', $plugin_status['file'] ) ) {
                                            if ( $compatible_php && $compatible_wp && $all_plugin_dependencies_active ) {
                                                $button_text = _x( 'Activate', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' );
                                                /* translators: %s: Plugin name. */
                                                $button_label = _x( 'Activate %s', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' );
                                                $activate_url = add_query_arg(
                                                    array(
                                                        '_wpnonce' => wp_create_nonce( 'activate-plugin_' . $plugin_status['file'] ),
                                                        'action'   => 'activate',
                                                        'plugin'   => $plugin_status['file'],
                                                    ),
                                                    network_admin_url( 'plugins.php' )
                                                );

                                                if ( is_network_admin() ) {
                                                    $button_text = _x( 'Network Activate', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' );
                                                    /* translators: %s: Plugin name. */
                                                    $button_label = _x( 'Network Activate %s', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' );
                                                    $activate_url = add_query_arg( array( 'networkwide' => 1 ), $activate_url );
                                                }

                                                $action_button = sprintf(
                                                    '<a href="%1$s" data-name="%2$s" data-slug="%3$s" data-plugin="%4$s" class="button button-primary activate-now" aria-label="%5$s" role="button">%6$s</a>',
                                                    esc_url( $activate_url ),
                                                    esc_attr( $name ),
                                                    esc_attr( $data->slug ),
                                                    esc_attr( $plugin_status['file'] ),
                                                    esc_attr( sprintf( $button_label, $name ) ),
                                                    $button_text
                                                );
                                            } else {
                                                $action_button = sprintf(
                                                    '<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
                                                    is_network_admin() ? _x( 'Network Activate', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' ) : _x( 'Activate', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' )
                                                );
                                            }
                                        } else {
                                            $action_button = sprintf(
                                                '<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
                                                _x( 'Installed', 'plugin', 'woocommerce-conditional-product-fees-for-checkout' )
                                            );
                                        }
                                        break;
                                }
                            }
                            /** Action Button End */
                            
                            if( is_plugin_active($plugin_status['file']) ) {
                                
                                $actions = apply_filters( "plugin_action_links_{$plugin_status['file']}", array() );

                                $dom = new DOMDocument();
                                libxml_use_internal_errors(true); // Suppress warnings for malformed HTML
                                $dom->loadHTML($actions['configure']);
                                $manage_link = $dom->getElementsByTagName('a')->item(0);
                                $manage_url = $manage_link->getAttribute('href');
                                
                                $action_button = sprintf(
                                    '<a href="%1$s" class="button" aria-label="%2$s" role="button">%3$s</a>',
                                    esc_url( $manage_url ),
                                    esc_attr( sprintf( esc_html__( 'Manage %s', 'woocommerce-conditional-product-fees-for-checkout' ), $name ) ),
                                    esc_html__( 'Manage', 'woocommerce-conditional-product-fees-for-checkout' )
                                );
                            }
                            ?>
                            <div class="dsmrkt-free-plugin element-shadow">
                                <div class="dsmrkt-free-plugin-body">
                                    <div class="dsmrkt-free-plugin-image">
                                        <img src="<?php echo esc_url( $plugin_icon_url ); ?>" alt="<?php echo esc_attr( $dsmrkt_free_plugin['name'] ); ?>" />
                                    </div>
                                    <div class="dsmrkt-free-plugin-details">
                                        <h4><?php echo esc_html( $dsmrkt_free_plugin['name'] ); ?></h4>
                                        <p><?php echo esc_html( $dsmrkt_free_plugin['short_description'] ); ?></p>
                                    </div>
                                </div>
                                <div class="dsmrkt-free-plugin-footer">
                                    <div class="dsmrkt-free-plugin-footer-left">
                                        <div class="column-downloaded">
                                            <?php
                                            if ( $dsmrkt_free_plugin['active_installs'] >= 1000000 ) {
                                                $active_installs_millions = floor( $dsmrkt_free_plugin['active_installs'] / 1000000 );
                                                $active_installs_text     = sprintf(
                                                    /* translators: %s: Number of millions. */
                                                    _nx( '%s+ Million', '%s+ Millions', $active_installs_millions, 'Active plugin installations', 'woocommerce-conditional-product-fees-for-checkout' ),
                                                    number_format_i18n( $active_installs_millions )
                                                );
                                            } elseif ( 0 === $dsmrkt_free_plugin['active_installs'] ) {
                                                $active_installs_text = _x( 'Less Than 10', 'Active plugin installations', 'woocommerce-conditional-product-fees-for-checkout' );
                                            } else {
                                                $active_installs_text = number_format_i18n( $dsmrkt_free_plugin['active_installs'] ) . '+';
                                            }
                                            /* translators: %s: Number of installations. */
                                            printf( esc_html__( '%s Active Installations', 'woocommerce-conditional-product-fees-for-checkout' ), esc_html( $active_installs_text ) );
                                            ?>
                                        </div>
                                    </div>
                                    <div class="dsmrkt-free-plugin-footer-right">
                                        <?php echo $action_button; // phpcs:ignore ?>
                                    </div>
                                </div>
                            </div>
                        <?php
                    } ?>
                    </div>
                </div>
                <?php
            }
        } ?>
	</div>
</div>
</div>
</div>
</div>
</div>
<?php
