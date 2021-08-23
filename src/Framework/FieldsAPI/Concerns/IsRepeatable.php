<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @unreleased
 */
trait IsRepeatable {

	/** @var bool */
	protected $repeatable = false;

	/**
	 * Set if the field should be repeatable.
	 *
	 * @unreleased
	 *
	 * @param bool $isRepeatable
	 *
	 * @return $this
	 */
	public function repeatable( $isRepeatable = true ) {
		$this->repeatable = $isRepeatable;
		return $this;
	}

	/**
	 * Get if the field should be repeatable.
	 *
	 * @unreleased
	 *
	 * @return bool
	 */
	public function isRepeatable() {
		return $this->repeatable;
	}
}
