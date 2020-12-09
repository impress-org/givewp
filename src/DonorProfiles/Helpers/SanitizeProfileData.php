<?php

namespace Give\DonorProfiles\Helpers;

/**
 * Normalize format of location type lists.
 * @since 2.8.0
 */
class SanitizeProfileData {

	/**
	 * Sanitize int passed, and return 0 if somehting other than an integer was passed
	 *
	 * @param string $avatarId
	 *
	 * @return int
	 *
	 * @since 2.11.0
	 */
	public static function sanitizeInt( $avatarId ) {
		return intval( $avatarId );
	}

	/**
	 * Sanitize array of addresses passed
	 *
	 * @param array $addresses
	 *
	 * @return array
	 *
	 * @since 2.11.0
	 */
	public static function sanitizeAdditionalAddresses( $addresses ) {
		foreach ( $addresses as $key => $value ) {
			$addresses[ $key ] = self::sanitizeAddress( $value );
		}
		return $addresses;
	}

	/**
	 * Sanitize address object passed
	 *
	 * @param object $address
	 *
	 * @return object
	 *
	 * @since 2.11.0
	 */
	public static function sanitizeAddress( $address ) {
		foreach ( $address as $key => $value ) {
			$address->{$key} = sanitize_text_field( $value );
		}
		return $address;
	}

	/**
	 * Sanitize array of emails passed
	 *
	 * @param array $emails
	 *
	 * @return array
	 *
	 * @since 2.11.0
	 */
	public static function sanitizeAdditionalEmails( $emails ) {
		foreach ( $emails as $key => $value ) {
			$emails[ $key ] = sanitize_email( $value );
		}
		return $emails;
	}
}
