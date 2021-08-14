<?php

namespace Give\Framework\FieldsAPI\Concerns;

use JsonSerializable;

/**
 * @unreleased
 */
abstract class Condition implements JsonSerializable {

	/**
	 * Invert the condition.
	 *
	 * @unreleased
	 *
	 * @return $this
	 */
	abstract public function invert();

	/**
	 * Create a new condition from an array.
	 *
	 * @unreleased
	 *
	 * @param array $array
	 *
	 * @return Condition
	 */
	public static function fromArray( $array ) {
		return new static( ...$array );
	}

	/**
	 * @unreleased
	 *
	 * {@inheritDoc}
	 */
	abstract public function jsonSerialize();
}
