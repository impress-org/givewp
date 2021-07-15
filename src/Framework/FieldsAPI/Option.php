<?php

namespace Give\Framework\FieldsAPI;

use JsonSerializable;

/**
 * Class Option
 *
 * @unreleased
 */
class Option implements JsonSerializable {

	use Concerns\HasLabel;

	/** @var string */
	protected $value;

	/**
	 * @param string $value
	 * @param ?string $label
	 */
	public function __construct( $value, $label = null ) {
		$this->value = $value;
		$this->label = $label;
	}

	/**
	 * Create a new option.
	 *
	 * @return static
	 */
	public static function make( ...$args ) {
		return new static( ...$args );
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
	 * {@inheritdoc}
	 */
	public function jsonSerialize() {
		return [
			'value' => $this->getValue(),
			'label' => $this->getLabel(),
		];
	}
}
