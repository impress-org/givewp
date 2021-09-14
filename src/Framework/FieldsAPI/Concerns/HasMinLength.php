<?php

namespace Give\Framework\FieldsAPI\Concerns;

/**
 * @unreleased
 *
 * @property ValidationRules $validationRules
 */
trait HasMinLength {

	/**
	 * Set the value’s minimum length.
	 *
	 * @unreleased
	 *
	 * @param int $minLength
	 *
	 * @return $this
	 */
	public function minLength( $minLength ) {
		$this->validationRules->rule( 'minLength', $minLength );

		return $this;
	}

	/**
	 * Get the value’s minimum length.
	 *
	 * @unreleased
	 *
	 * @return int|null
	 */
	public function getMinLength() {
		return $this->validationRules->getRule( 'minLength' );
	}
}
