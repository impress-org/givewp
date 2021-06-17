<?php

namespace Give\Framework\FieldsAPI\Fields\Contracts;

/**
 * The field can be required.
 *
 * @unreleased
 */
interface ValidatesRequired {

	/**
	 * Set if the field is required.
	 *
	 * @param bool $required
	 *
	 * @return $this
	 */
	public function required( $required = true );

	/**
	 * Check if the field is required.
	 *
	 * @return bool
	 */
	public function isRequired();

	/**
	 * Get the required error for the field.
	 *
	 * @return array
	 */
	public function getRequiredError();
}
