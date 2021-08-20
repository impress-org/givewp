<?php

namespace Give\Framework\FieldsAPI\Conditions;

use JsonSerializable;

/**
 * @unreleased
 */
abstract class Condition implements JsonSerializable {

	/**
	 * @unreleased
	 *
	 * {@inheritDoc}
	 */
	abstract public function jsonSerialize();
}
