<?php

namespace Give\DonorProfiles\Helpers;

/**
 * Normalize format of location type lists.
 * @since 2.8.0
 */
class SanitizeProfileData {

	public static function sanitizeInt( $avatarId ) {
		return intval( $avatarId );
	}

	public static function sanitizeAdditionalAddresses( $addresses ) {
		foreach ( $addresses as $key => $value ) {
			$addresses[ $key ] = self::sanitizeAddress( $value );
		}
		return $addresses;
	}

	public static function sanitizeAddress( $address ) {
		foreach ( $address as $key => $value ) {
			$address->{$key} = sanitize_text_field( $value );
		}
		return $address;
	}

	public static function sanitizeAdditionalEmails( $emails ) {
		foreach ( $emails as $key => $value ) {
			$emails[ $key ] = sanitize_email( $value );
		}
		return $emails;
	}
}
