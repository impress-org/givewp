<?php

namespace Give\Framework\FieldsAPI\FormField;

trait FieldDefaultValue {

	/** @var string */
	protected $defaultValue;

	/**
	 * @param string $defaultValue
	 * @return $this
	 */
	public function defaultValue( $defaultValue ) {
		$this->defaultValue = $defaultValue;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultValue() {
		return $this->defaultValue;
	}
}
