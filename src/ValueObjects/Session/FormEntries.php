<?php
namespace Give\ValueObjects\Session;

use Give\ValueObjects\ValueObjects;
use InvalidArgumentException;

class FormEntries implements ValueObjects {
	/**
	 * Take array and return object.
	 *
	 * @param $array
	 *
	 * @return FormEntries
	 */
	public static function fromArray( $array ) {
		$expectedKeys = [ 'formId', 'formTitle', 'currentUrl', 'priceId', 'amount', 'first', 'email', 'gateway' ];

		$hasRequiredKeys = (bool) array_intersect_key( $array, array_flip( $expectedKeys ) );

		if ( ! $hasRequiredKeys ) {
			throw new InvalidArgumentException(
				'Invalid FormEntries object, must have the exact following keys: ' . implode( ', ', $expectedKeys )
			);
		}

		$formEntries = new self();
		foreach ( $array as $key => $value ) {
			$formEntries->{$key} = $value;
		}

		/**
		 * Filter the form entries object
		 *
		 * @param FormEntries $formEntries
		 */
		return apply_filters( 'give_session_form_entries_object', $formEntries, $array );
	}
}
