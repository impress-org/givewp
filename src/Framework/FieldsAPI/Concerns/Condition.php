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
	 * @unreleased
	 *
	 * {@inheritDoc}
	 */
	abstract public function jsonSerialize();
}
