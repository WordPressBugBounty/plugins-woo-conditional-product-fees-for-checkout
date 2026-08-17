<?php
/**
 * Best Fit helper functions (loaded before init; no translations here).
 *
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current WordPress version supports AI Suggested Fee (7.0+).
 *
 * @return bool
 */
if ( ! function_exists( 'wcpfc_is_bestfit_wp_supported' ) ) {
	function wcpfc_is_bestfit_wp_supported() {
		return is_wp_version_compatible( '7.0' );
	}
}

/**
 * Whether the current site can use AI Best Fit features (Pro / premium only).
 *
 * @return bool
 */
if ( ! function_exists( 'wcpfc_can_use_best_fit' ) ) {
	function wcpfc_can_use_best_fit() {
		if ( ! function_exists( 'wcpfc_is_bestfit_wp_supported' ) || ! wcpfc_is_bestfit_wp_supported() ) {
			return false;
		}

		if ( ! function_exists( 'wcpffc_fs' ) ) {
			return false;
		}

		return wcpffc_fs()->is__premium_only() && wcpffc_fs()->can_use_premium_code();
	}
}

/**
 * Admin URL for configuring WordPress AI / API tool connections.
 *
 * @return string
 */
if ( ! function_exists( 'wcpfc_get_ai_settings_url' ) ) {
	function wcpfc_get_ai_settings_url() {
		if ( function_exists( 'WordPress\AI\has_ai_credentials' ) || function_exists( 'wp_get_connectors' ) ) {
			$url = admin_url( 'options-connectors.php' );
		} else {
			$url = admin_url( 'options-general.php?page=connectors' );
		}

		/**
		 * Filter the AI settings page URL shown in Best Fit admin notices.
		 *
		 * @param string $url Settings page URL.
		 */
		return apply_filters( 'wcpfc_bestfit_ai_settings_url', $url );
	}
}

/**
 * Whether at least one WordPress AI provider connector is configured.
 *
 * @return bool
 */
if ( ! function_exists( 'wcpfc_has_ai_provider_configured' ) ) {
	function wcpfc_has_ai_provider_configured() {
		if ( function_exists( 'WordPress\AI\has_ai_credentials' ) ) {
			return (bool) \WordPress\AI\has_ai_credentials();
		}

		/**
		 * Filter whether an AI provider is configured for Best Fit.
		 *
		 * @param bool $configured Whether a provider is configured.
		 */
		return (bool) apply_filters( 'wcpfc_bestfit_has_ai_provider', false );
	}
}

/**
 * Whether the current user dismissed the Best Fit AI connector notice.
 *
 * @return bool
 */
if ( ! function_exists( 'wcpfc_is_bestfit_ai_notice_dismissed' ) ) {
	function wcpfc_is_bestfit_ai_notice_dismissed() {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		return (bool) get_user_meta( $user_id, 'wcpfc_hide_bestfit_ai_notice', true );
	}
}

/**
 * Whether to show the Best Fit AI connector admin notice.
 *
 * @return bool
 */
if ( ! function_exists( 'wcpfc_should_show_bestfit_ai_connector_notice' ) ) {
	function wcpfc_should_show_bestfit_ai_connector_notice() {
		if ( ! function_exists( 'wcpfc_is_bestfit_wp_supported' ) || ! wcpfc_is_bestfit_wp_supported() ) {
			return false;
		}

		if ( ! function_exists( 'wcpfc_can_use_best_fit' ) || ! wcpfc_can_use_best_fit() ) {
			return false;
		}

		if ( function_exists( 'wcpfc_has_ai_provider_configured' ) && wcpfc_has_ai_provider_configured() ) {
			return false;
		}

		return ! wcpfc_is_bestfit_ai_notice_dismissed();
	}
}

/**
 * Persist dismissal when the notice close control is used.
 *
 * @return void
 */
if ( ! function_exists( 'wcpfc_process_bestfit_ai_notice_dismiss' ) ) {
	function wcpfc_process_bestfit_ai_notice_dismiss() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$hide_notice = filter_input( INPUT_GET, 'wcpfc-hide-bestfit-ai-notice', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$nonce       = filter_input( INPUT_GET, '_wcpfc_bestfit_ai_notice_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( 'wcpfc-hide-bestfit-ai' !== $hide_notice || empty( $nonce ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'wcpfc_bestfit_ai_notice_nonce' ) ) {
			return;
		}

		update_user_meta( get_current_user_id(), 'wcpfc_hide_bestfit_ai_notice', '1' );

		$redirect = remove_query_arg(
			array( 'wcpfc-hide-bestfit-ai-notice', '_wcpfc_bestfit_ai_notice_nonce' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}
}
add_action( 'admin_init', 'wcpfc_process_bestfit_ai_notice_dismiss' );

/**
 * Whether a fee was created by AI Best Fit.
 *
 * @param int $post_id Fee post ID.
 * @return bool
 */
if ( ! function_exists( 'wcpfc_is_bestfit_fee' ) ) {
	function wcpfc_is_bestfit_fee( $post_id ) {
		return '1' === get_post_meta( (int) $post_id, '_wcpfc_bestfit_generated', true );
	}
}

/**
 * Remove the AI Best Fit tag from a fee.
 *
 * @param int $post_id Fee post ID.
 * @return bool
 */
if ( ! function_exists( 'wcpfc_remove_bestfit_tag' ) ) {
	function wcpfc_remove_bestfit_tag( $post_id ) {
		return (bool) delete_post_meta( (int) $post_id, '_wcpfc_bestfit_generated' );
	}
}

/**
 * Clear cached AI Best Fit suggestions for the current site.
 *
 * @return bool
 */
if ( ! function_exists( 'wcpfc_clear_bestfit_ai_cache' ) ) {
	function wcpfc_clear_bestfit_ai_cache() {
		if ( class_exists( 'WCPFC_Fee_Suggestion_Engine' ) ) {
			return WCPFC_Fee_Suggestion_Engine::clear_ai_cache();
		}

		$blog_id = get_current_blog_id();

		for ( $day = 0; $day < 2; $day++ ) {
			$date = gmdate( 'Y-m-d', strtotime( '-' . $day . ' days' ) );
			delete_transient( 'wcpfc_bestfit_ai_' . $blog_id . '_' . $date );
		}

		return true;
	}
}

/**
 * Whether AI connectors are available for Best Fit research fees.
 *
 * @return bool
 */
if ( ! function_exists( 'wcpfc_is_bestfit_ai_available' ) ) {
	function wcpfc_is_bestfit_ai_available() {
		if ( class_exists( 'WCPFC_Fee_Suggestion_Engine' ) ) {
			return WCPFC_Fee_Suggestion_Engine::is_ai_available();
		}

		$available = function_exists( 'wp_supports_ai' )
			&& wp_supports_ai()
			&& function_exists( 'wp_ai_client_prompt' )
			&& ( ! function_exists( 'wcpfc_has_ai_provider_configured' ) || wcpfc_has_ai_provider_configured() );

		return (bool) apply_filters( 'wcpfc_bestfit_ai_available', $available );
	}
}
