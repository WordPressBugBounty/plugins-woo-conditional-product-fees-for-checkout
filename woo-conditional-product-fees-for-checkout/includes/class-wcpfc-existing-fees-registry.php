<?php
/**
 * Reads existing store fees for Best Fit de-duplication.
 *
 * @package Woocommerce_Conditional_Product_Fees_For_Checkout_Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalizes and compares existing fee rules against suggestions.
 */
class WCPFC_Existing_Fees_Registry {

	const POST_TYPE = 'wc_conditional_fee';

	/**
	 * @var array<int, array<string, mixed>>|null
	 */
	private static $cache = null;

	/**
	 * Load all existing fees (any status except trash).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function collect() {
		if ( null !== self::$cache ) {
			return self::$cache;
		}

		$post_ids = get_posts(
			array(
				'post_type'              => self::POST_TYPE,
				'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'posts_per_page'         => -1, //phpcs:ignore
				'fields'                 => 'ids',
				'orderby'                => 'date',
				'order'                  => 'DESC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);

		$fees = array();
		foreach ( $post_ids as $post_id ) {
			$fees[] = self::normalize_post( (int) $post_id );
		}

		self::$cache = $fees;

		return self::$cache;
	}

	/**
	 * @param int $post_id Fee post ID.
	 * @return array<string, mixed>
	 */
	public static function normalize_post( $post_id ) {
		$metabox = get_post_meta( $post_id, 'product_fees_metabox', true );
		if ( ! is_array( $metabox ) ) {
			$metabox = maybe_unserialize( $metabox );
		}

		$conditions = array();
		foreach ( (array) $metabox as $row ) {
			if ( empty( $row['product_fees_conditions_condition'] ) ) {
				continue;
			}
			$conditions[] = array(
				'condition' => sanitize_key( $row['product_fees_conditions_condition'] ),
				'operator'  => ! empty( $row['product_fees_conditions_is'] ) ? (string) $row['product_fees_conditions_is'] : 'is_equal_to',
				'values'    => $row['product_fees_conditions_values'] ?? '',
			);
		}

		return array(
			'id'             => (int) $post_id,
			'title'          => get_the_title( $post_id ),
			'fee_type'       => (string) get_post_meta( $post_id, 'fee_settings_select_fee_type', true ) ?: 'fixed',
			'amount'         => (string) get_post_meta( $post_id, 'fee_settings_product_cost', true ),
			'conditions'     => $conditions,
			'signatures'     => self::condition_signatures( $conditions ),
			'logic_families' => self::logic_families( $conditions ),
		);
	}

	/**
	 * @param array<int, array<string, mixed>> $conditions Conditions.
	 * @return array<int, string>
	 */
	public static function condition_signatures( $conditions ) {
		$signatures = array();
		foreach ( (array) $conditions as $condition ) {
			$signatures[] = self::signature_for_row( $condition );
		}
		sort( $signatures );

		return array_values( array_unique( array_filter( $signatures ) ) );
	}

	/**
	 * @param array<string, mixed> $row Condition row.
	 * @return string
	 */
	public static function signature_for_row( $row ) {
		$condition = sanitize_key( (string) ( $row['condition'] ?? '' ) );
		$operator  = (string) ( $row['operator'] ?? 'is_equal_to' );
		$values    = self::normalize_values( $row['values'] ?? '' );

		return $condition . '|' . $operator . '|' . implode( ',', $values );
	}

	/**
	 * @param array<int, array<string, mixed>> $conditions Conditions.
	 * @return array<int, string>
	 */
	public static function logic_families( $conditions ) {
		$families = array();
		foreach ( (array) $conditions as $condition ) {
			$condition_key = sanitize_key( (string) ( $condition['condition'] ?? '' ) );
			$operator      = (string) ( $condition['operator'] ?? 'is_equal_to' );
			if ( '' === $condition_key ) {
				continue;
			}
			$families[] = $condition_key . '|' . $operator;
		}

		return array_values( array_unique( $families ) );
	}

	/**
	 * @param mixed $values Raw values.
	 * @return array<int, string>
	 */
	private static function normalize_values( $values ) {
		if ( is_array( $values ) ) {
			$parts = array_map( 'strval', $values );
		} else {
			$parts = array( (string) $values );
		}

		$parts = array_values( array_unique( array_filter( array_map( 'trim', $parts ), 'strlen' ) ) );
		sort( $parts );

		return $parts;
	}

	/**
	 * @param array<string, mixed>             $suggestion    Candidate suggestion.
	 * @param array<int, array<string, mixed>> $existing_fees Existing fees.
	 * @return bool
	 */
	public static function is_duplicate_suggestion( $suggestion, $existing_fees ) {
		$suggestion_conditions = $suggestion['conditions'] ?? array();
		$suggestion_sigs       = self::condition_signatures( $suggestion_conditions );
		$suggestion_families   = self::logic_families( $suggestion_conditions );
		$suggestion_type       = sanitize_key( (string) ( $suggestion['suggestion_type'] ?? '' ) );

		foreach ( $existing_fees as $fee ) {
			if ( self::signatures_match( $suggestion_sigs, $fee['signatures'] ?? array() ) ) {
				return true;
			}

			if ( '' !== $suggestion_type && self::type_logic_exists( $suggestion_type, $suggestion_conditions, $fee ) ) {
				return true;
			}

			if ( self::families_overlap_same_target( $suggestion_conditions, $fee ) ) {
				return true;
			}

			if ( ! empty( $suggestion_families ) && self::single_family_covered( $suggestion_type, $suggestion_families, $fee['logic_families'] ?? array() ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array<int, string> $left  Left signatures.
	 * @param array<int, string> $right Right signatures.
	 * @return bool
	 */
	private static function signatures_match( $left, $right ) {
		if ( empty( $left ) || empty( $right ) ) {
			return false;
		}

		return $left === $right;
	}

	/**
	 * @param string                           $type       Suggestion type.
	 * @param array<int, array<string, mixed>> $conditions Suggestion conditions.
	 * @param array<string, mixed>             $fee        Existing fee.
	 * @return bool
	 */
	private static function type_logic_exists( $type, $conditions, $fee ) {
		$family_map = array(
			'small_order'   => array( 'cart_total|less_then' ),
			'premium_order' => array( 'cart_total|greater_then' ),
			'catalog'       => array( 'quantity|greater_equal_to' ),
		);

		if ( isset( $family_map[ $type ] ) ) {
			foreach ( $family_map[ $type ] as $family ) {
				if ( in_array( $family, (array) ( $fee['logic_families'] ?? array() ), true ) ) {
					return true;
				}
			}
		}

		if ( in_array( $type, array( 'country', 'product', 'category', 'payment' ), true ) ) {
			$target_condition = $type;
			if ( 'payment' === $type ) {
				$target_condition = 'payment';
			}

			foreach ( (array) $conditions as $row ) {
				if ( sanitize_key( (string) ( $row['condition'] ?? '' ) ) !== $target_condition ) {
					continue;
				}
				$target_sig = self::signature_for_row( $row );
				if ( in_array( $target_sig, (array) ( $fee['signatures'] ?? array() ), true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param array<int, array<string, mixed>> $suggestion_conditions Suggestion conditions.
	 * @param array<string, mixed>             $fee                   Existing fee.
	 * @return bool
	 */
	private static function families_overlap_same_target( $suggestion_conditions, $fee ) {
		foreach ( (array) $suggestion_conditions as $row ) {
			$signature = self::signature_for_row( $row );
			if ( in_array( $signature, (array) ( $fee['signatures'] ?? array() ), true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string               $type                Suggestion type.
	 * @param array<int, string>   $suggestion_families Suggestion families.
	 * @param array<int, string>   $existing_families   Existing families.
	 * @return bool
	 */
	private static function single_family_covered( $type, $suggestion_families, $existing_families ) {
		$single_family_types = array( 'small_order', 'premium_order', 'catalog' );
		if ( ! in_array( $type, $single_family_types, true ) ) {
			return false;
		}

		foreach ( $suggestion_families as $family ) {
			if ( in_array( $family, (array) $existing_families, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array<int, array<string, mixed>> $candidates    Candidate suggestions.
	 * @param array<int, array<string, mixed>> $existing_fees Existing fees.
	 * @param int                                $limit         Max suggestions.
	 * @return array<int, array<string, mixed>>
	 */
	public static function select_unique_suggestions( $candidates, $existing_fees, $limit ) {
		$selected            = array();
		$selected_signatures = array();

		foreach ( (array) $candidates as $candidate ) {
			if ( count( $selected ) >= $limit ) {
				break;
			}

			if ( self::is_duplicate_suggestion( $candidate, $existing_fees ) ) {
				continue;
			}

			$candidate_sigs = self::condition_signatures( $candidate['conditions'] ?? array() );
			if ( ! empty( $candidate_sigs ) && in_array( implode( '|', $candidate_sigs ), $selected_signatures, true ) ) {
				continue;
			}

			$selected[] = $candidate;
			if ( ! empty( $candidate_sigs ) ) {
				$selected_signatures[] = implode( '|', $candidate_sigs );
			}
		}

		return $selected;
	}

	/**
	 * @param array<string, mixed> $suggestion Suggestion.
	 * @return array<string, mixed>
	 */
	public static function normalize_suggestion( $suggestion ) {
		$conditions = $suggestion['conditions'] ?? array();

		return array(
			'id'             => 0,
			'title'          => $suggestion['title'] ?? '',
			'fee_type'       => $suggestion['fee_type'] ?? 'fixed',
			'amount'         => (string) ( $suggestion['amount'] ?? '' ),
			'conditions'     => $conditions,
			'signatures'     => self::condition_signatures( $conditions ),
			'logic_families' => self::logic_families( $conditions ),
		);
	}

	/**
	 * Summary for AI prompts.
	 *
	 * @param array<int, array<string, mixed>> $existing_fees Existing fees.
	 * @return array<int, array<string, mixed>>
	 */
	public static function summarize_for_prompt( $existing_fees ) {
		$summary = array();
		foreach ( (array) $existing_fees as $fee ) {
			$summary[] = array(
				'title'      => $fee['title'] ?? '',
				'fee_type'   => $fee['fee_type'] ?? 'fixed',
				'amount'     => $fee['amount'] ?? '',
				'conditions' => $fee['signatures'] ?? array(),
			);
		}

		return $summary;
	}

	/**
	 * Clear in-memory cache.
	 */
	public static function flush_cache() {
		self::$cache = null;
	}
}
