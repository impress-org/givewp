<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @unreleased
 *
 * @property ValidationRules $validationRules
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

	/**
	 * Set how many times this field can repeat.
	 *
	 * @unreleased
	 *
	 * @param int|null $maxRepeatable
	 *
	 * @return $this
	 */
	public function maxRepeatable( $maxRepeatable ) {
		$this->validationRules->rule( 'maxRepeatable', $maxRepeatable );

		return $this;
	}

	/**
	 * Get how many times this field can repeat.
	 *
	 * @unreleased
	 *
	 * @return int|null
	 */
	public function getMaxRepeatable() {
		return $this->validationRules->getRule( 'maxRepeatable' );
	}
}
