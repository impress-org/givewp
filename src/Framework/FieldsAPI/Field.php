<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Contracts\Node;

abstract class Field implements Node {

	use Concerns\HasDefaultValue;
	use Concerns\HasName;
	use Concerns\HasType;
	use Concerns\IsReadOnly;
	use Concerns\IsRequired;
	use Concerns\SerializeAsJson;

	/** @var ValidationRules */
	protected $validationRules;

	/**
	 * @param string $name
	 */
	protected function __construct( $name ) {
		$this->name            = $name;
		$this->validationRules = new ValidationRules();
	}

	/**
	 * Create a named field.
	 *
	 * @param string $name
	 *
	 * @return static
	 */
	public static function make( $name ) {
		return new static( $name );
	}
}
