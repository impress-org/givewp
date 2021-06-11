<?php

namespace Give\Framework\FieldsAPI\FormField;

/**
 * Class FieldOption
 *
 * @unreleased
 */
class FieldOption {
	/** @var string */
	protected $value;

	/** @var string */
	protected $label;

	/**
	 * @param string $value
	 * @param ?string $label
	 */
	public function __construct( $value, $label = null ) {
		$this->value = $value;
		$this->label = $label;
	}

	/**
	 * Access the value
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Access the label
	 *
	 * @return string|null
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * Serialize
	 *
	 * @return object
	 */
	public function jsonserialize() {
		return (object) [
			'value' => $this->value,
			'label' => $this->label,
		];
	}
}
