<?php
namespace Give\ValueObjects\Session;

use Give\Helpers\ArrayDataSet;
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

		if ( ! ArrayDataSet::hasRequiredKeys( $array, $expectedKeys ) ) {
			throw new InvalidArgumentException(
				'Invalid FormEntries object, must have the exact following keys: ' . implode( ', ', $expectedKeys )
			);
		}

		$formEntries = new self();

		foreach ( $array as $key => $value ) {
			$formEntries->{$key} = $value;
		}

		return $formEntries;
	}
}
