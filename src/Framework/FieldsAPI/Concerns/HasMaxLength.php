<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @unreleased
 *
 * @property ValidationRules $validationRules
 */
trait HasMaxLength {

	/**
	 * Set the value’s maximum length.
	 *
	 * @unreleased
	 *
	 * @param int $maxLength
	 *
	 * @return $this
	 */
	public function maxLength( $maxLength ) {
		$this->validationRules->rule( 'maxLength', $maxLength );

		return $this;
	}

	/**
	 * Get the value’s maximum length.
	 *
	 * @unreleased
	 *
	 * @return int|null
	 */
	public function getMaxLength() {
		return $this->validationRules->getRule( 'maxLength' );
	}
}
