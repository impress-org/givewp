<?php

namespace Give\Framework\FieldsAPI\FormField;

/**
 * @unreleased
 */
trait FieldAttributes {

	/**
	 * @unreleased
	 * @var array
	 */
	public $attributes;

	/**
	 * @unreleased
	 * @param array $attributes
	 * @return $this
	 */
	public function setAttributes( $attributes ) {
		$this->attributes = $attributes;
		return $this;
	}

	/**
	 * @unreleased
	 * @return array
	 */
	public function getAttributes() {
		return $this->attributes;
	}
}
