<?php

/**
 * Dashboard template structure
 *
 * @package    Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 * @subpackage Woocommerce_Conditional_Product_Fees_For_Checkout_Pro/admin/partials
 * @author     Multidots <inquiry@multidots.in>
 * @link       https://www.multidots.com
 * @since      3.7.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';
// Function for free plugin content
function wcpfc_free_dashboard_settings_content() {
    $currency_symbol = ( get_woocommerce_currency_symbol() ? get_woocommerce_currency_symbol() : '$' );
    $free_revenue_fees = array(
        array(
            'no'        => '1',
            'name'      => 'International fees',
            'revenue'   => $currency_symbol . '0',
            'bar_color' => '180, 7, 45',
        ),
        array(
            'no'        => '2',
            'name'      => 'Insurance fees',
            'revenue'   => $currency_symbol . '0',
            'bar_color' => '32, 61, 65',
        ),
        array(
            'no'        => '3',
            'name'      => 'Deposite fee',
            'revenue'   => $currency_symbol . '0',
            'bar_color' => '126, 56, 39',
        ),
        array(
            'no'        => '4',
            'name'      => 'Rush processing fee',
            'revenue'   => $currency_symbol . '0',
            'bar_color' => '64, 246, 161',
        ),
        array(
            'no'        => '5',
            'name'      => 'Building a box',
            'revenue'   => $currency_symbol . '0',
            'bar_color' => '27, 170, 178',
        ),
        array(
            'no'        => '6',
            'name'      => 'Shipping fees',
            'revenue'   => $currency_symbol . '0',
            'bar_color' => '176, 68, 136',
        ),
        array(
            'no'        => '7',
            'name'      => 'Checkout fee',
            'revenue'   => $currency_symbol . '0',
            'bar_color' => '125, 188, 28',
        )
    );
    ?>
    <div class="wcpfc-section-full wcpfc-upgrade-pro-to-unlock">
        <div class="wcpfc-grid-layout">
            <div class="wcpfc-card wcpfc-main-chart" style="grid-column: span 2 / auto;">
                <div class="content">
                    <div class="wcpfc-mini-chart">
                        <div class="header">
                            <div class="title"><?php 
    esc_html_e( 'Total revenue 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></div>
                        </div>
                        <div class="content">
                            <div class="amount"><?php 
    echo esc_html( $currency_symbol . '0' );
    ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wcpfc-card wcpfc-main-chart" style="grid-column: span 2 / auto;">
                <div class="content">
                    <div class="wcpfc-mini-chart">
                        <div class="header">
                            <div class="title"><?php 
    esc_html_e( 'This year revenue 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></div>
                        </div>
                        <div class="content">
                            <div class="amount"><?php 
    echo esc_html( $currency_symbol . '0' );
    ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wcpfc-card wcpfc-main-chart" style="grid-column: span 2 / auto;">
                <div class="content">
                    <div class="wcpfc-mini-chart">
                        <div class="header">
                            <div class="title"><?php 
    esc_html_e( 'Last month revenue 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></div>
                        </div>
                        <div class="content">
                            <div class="amount"><?php 
    echo esc_html( $currency_symbol . '0' );
    ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wcpfc-card wcpfc-main-chart" style="grid-column: span 2 / auto;">
                <div class="content">
                    <div class="wcpfc-mini-chart">
                        <div class="header">
                            <div class="title"><?php 
    esc_html_e( 'This month revenue 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></div>
                        </div>
                        <div class="content">
                            <div class="amount"><?php 
    echo esc_html( $currency_symbol . '0' );
    ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wcpfc-card wcpfc-main-chart" style="grid-column: span 2 / auto;">
                <div class="content">
                    <div class="wcpfc-mini-chart">
                        <div class="header">
                            <div class="title"><?php 
    esc_html_e( 'Yesterday revenue 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></div>
                        </div>
                        <div class="content">
                            <div class="amount"><?php 
    echo esc_html( $currency_symbol . '0' );
    ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wcpfc-card wcpfc-main-chart" style="grid-column: span 2 / auto;">
                <div class="content">
                    <div class="wcpfc-mini-chart">
                        <div class="header">
                            <div class="title"><?php 
    esc_html_e( 'Today revenue 🔒', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></div>
                        </div>
                        <div class="content">
                            <div class="amount"><?php 
    echo esc_html( $currency_symbol . '0' );
    ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wcpfc-filter-fee wcpfc-main-chart" style="grid-column: span 12 / auto;">
                <div class="wcpfc-period-selector">
                    <div class="wcpfc-datepicker">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <input type="text" id="wcpfc-custom-from" name="wcpfc-custom-from" value="<?php 
    echo esc_attr( gmdate( "m/d/Y", strtotime( "-30 day" ) ) );
    ?>" />
                        <span class="dashicons dashicons-arrow-right-alt"></span>
                        <input type="text" id="wcpfc-custom-to" name="wcpfc-custom-to" value="<?php 
    echo esc_attr( gmdate( "m/d/Y", strtotime( "today" ) ) );
    ?>" />
                    </div>
                    <div class="wcpfc-filter-specific">
                        <button class="primary button" data-start="<?php 
    echo esc_attr( gmdate( "Y-m-d", strtotime( "today" ) ) );
    ?>" data-end="<?php 
    echo esc_attr( gmdate( "Y-m-d", strtotime( "today" ) ) );
    ?>"><?php 
    esc_html_e( 'Day', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></button>
                        <button class="primary button" data-start="<?php 
    echo esc_attr( gmdate( "Y-m-d", strtotime( "-7 day" ) ) );
    ?>" data-end="<?php 
    echo esc_attr( gmdate( "Y-m-d", strtotime( "today" ) ) );
    ?>"><?php 
    esc_html_e( 'Week', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></button>
                        <button class="primary button" data-start="<?php 
    echo esc_attr( gmdate( "Y-m-d", strtotime( "-30 day" ) ) );
    ?>" data-end="<?php 
    echo esc_attr( gmdate( "Y-m-d", strtotime( "today" ) ) );
    ?>"><?php 
    esc_html_e( 'Month', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></button>
                        <button class="primary button" data-start="<?php 
    echo esc_attr( gmdate( "Y-m-d", strtotime( "-365 day" ) ) );
    ?>" data-end="<?php 
    echo esc_attr( gmdate( "Y-m-d", strtotime( "today" ) ) );
    ?>"><?php 
    esc_html_e( 'Year', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></button>
                        <button class="primary button all-data" data-start="<?php 
    echo esc_attr( 'all' );
    ?>" data-end="<?php 
    echo esc_attr( 'all' );
    ?>"><?php 
    esc_html_e( 'All Time', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></button>
                        <div class="wcpfc-pro-label"></div>
                    </div>
                </div>
                <img src="<?php 
    echo esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/premium-upgrade-img/fee-data-graph-img.png' );
    ?>" alt="<?php 
    esc_attr_e( 'Fees Revenue Graph', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>">
            </div>
            <div class="wcpfc-top-ten wcpfc-main-chart" style="grid-column: span 6 / auto;">
                <div class="content">
                    <div class="wcpfc-table-title">
                        <span class="wcpfc-title"><?php 
    esc_html_e( 'Revenue Breakdown', 'woocommerce-conditional-product-fees-for-checkout' );
    ?><div class="wcpfc-pro-label"></div></span>
                        <button class="button primary export-all-fees"><?php 
    esc_html_e( 'Export CSV', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></button>
                    </div>
                    <div class="wcpfc-table">
                        <div class="wcpfc-table-header">
                            <div class="wcpfc-table-label"><?php 
    esc_html_e( 'No', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>.</div>
                            <div class="wcpfc-table-label"><?php 
    esc_html_e( 'Fee Name', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></div>
                            <div class="wcpfc-table-label"><?php 
    esc_html_e( 'Revenue', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></div>
                        </div>
                        <?php 
    if ( isset( $free_revenue_fees ) && !empty( $free_revenue_fees ) ) {
        foreach ( $free_revenue_fees as $revenue_fees ) {
            ?>
                                <div class="wcpfc-table-row">
                                    <div class="wcpfc-table-row-item"><?php 
            echo esc_html( $revenue_fees['no'] );
            ?></div>
                                    <div class="wcpfc-table-row-item"><?php 
            echo esc_html( $revenue_fees['name'] );
            ?></div>
                                    <div class="wcpfc-table-row-item"><?php 
            echo esc_html( $revenue_fees['revenue'] );
            ?></div>
                                </div>
                                <?php 
        }
    }
    ?>
                    </div>
                </div>
            </div>
            <div class="wcpfc-top-ten wcpfc-main-chart" style="grid-column: span 6 / auto;">
                <div class="content">
                    <div class="wcpfc-chart-title">
                        <span class="wcpfc-chart-title-text"><?php 
    esc_html_e( 'Top 10 Fees', 'woocommerce-conditional-product-fees-for-checkout' );
    ?><div class="wcpfc-pro-label"></div></span>
                    </div>
                    <div class="topFeeChart-wrap">
                        <img src="<?php 
    echo esc_url( WCPFC_PRO_PLUGIN_URL . 'admin/images/premium-upgrade-img/fee-data-round-graph-img.png' );
    ?>" alt="<?php 
    esc_attr_e( 'Fees Revenue Graph', 'woocommerce-conditional-product-fees-for-checkout' );
    ?>">
                    </div>
                    <div class="wcpfc-chart-legend">
                        <?php 
    if ( isset( $free_revenue_fees ) && !empty( $free_revenue_fees ) ) {
        foreach ( $free_revenue_fees as $revenue_fees ) {
            ?>
                                <div class="item">
                                    <div class="bar" style="background: rgb(<?php 
            echo esc_attr( $revenue_fees['bar_color'] );
            ?>);"></div>
                                    <div class="label"><?php 
            echo esc_html( $revenue_fees['name'] );
            ?></div>
                                    <div class="data"><?php 
            echo esc_html( $revenue_fees['revenue'] );
            ?></div>
                                </div>
                                <?php 
        }
    }
    ?>
                    </div>
                </div>
            </div>
        </div>
        <button class="button-primary button reset-cache" style="float:right;margin-top:30px;"><?php 
    esc_html_e( 'Refresh Data', 'woocommerce-conditional-product-fees-for-checkout' );
    ?></button>
    </div>
    <?php 
}

wcpfc_free_dashboard_settings_content();
?>
</div>
</div>
</div>
</div>