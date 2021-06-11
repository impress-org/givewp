<?php

namespace Give\Framework\FieldsAPI\FormField;

use JsonSerializable;

/**
 * Class FieldOption
 *
 * @unreleased
 */
class FieldOption implements JsonSerializable {
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
	 * {@inheritdoc}
	 */
	public function jsonSerialize() {
		return [
			'value' => $this->value,
			'label' => $this->label,
		];
	}
}
