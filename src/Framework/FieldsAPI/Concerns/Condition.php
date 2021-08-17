<?php

namespace Give\Framework\FieldsAPI\Concerns;

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
