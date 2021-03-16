<?php

namespace Give\DonorDashboards\Helpers;

/**
 * Normalize format of location type lists.
 * @since 2.10.0
 */
class SanitizeProfileData {

	/**
	 * Sanitize int passed, and return 0 if somehting other than an integer was passed
	 *
	 * @param string $avatarId
	 *
	 * @return int
	 *
	 * @since 2.10.0
	 */
	public static function sanitizeInt( $avatarId ) {
		if ( ! empty( $avatarId ) ) {
			return intval( $avatarId );
		} else {
			return 0;
		}
	}

	/**
	 * Sanitize array of addresses passed
	 *
	 * @param array $addresses
	 *
	 * @return array
	 *
	 * @since 2.10.0
	 */
	public static function sanitizeAdditionalAddresses( $addresses ) {
		if ( ! empty( $addresses ) ) {
			return array_map( [ __CLASS__, 'sanitizeAddress' ], $addresses );
		} else {
			return [];
		}
	}

	/**
	 * Sanitize address object passed
	 *
	 * @param object $address
	 *
	 * @return object
	 *
	 * @since 2.10.0
	 */
	public static function sanitizeAddress( $address ) {
		if ( ! empty( $address ) ) {
			foreach ( $address as $key => $value ) {
				$address->{$key} = sanitize_text_field( $value );
			}
			return $address;
		} else {
			return [];
		}
	}

	/**
	 * Sanitize array of emails passed
	 *
	 * @param array $emails
	 *
	 * @return array
	 *
	 * @since 2.10.0
	 */
	public static function sanitizeAdditionalEmails( $emails ) {
		if ( ! empty( $emails ) ) {
			foreach ( $emails as $key => $value ) {
				$emails[ $key ] = sanitize_email( $value );
			}
			return $emails;
		} else {
			return [];
		}
	}
}
