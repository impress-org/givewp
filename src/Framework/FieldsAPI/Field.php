<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Concerns\ValidationRules;
use Give\Framework\FieldsAPI\Contracts\Node;

/**
 * @unreleased
 */
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
	public function __construct( $name ) {
		$this->name            = $name;
		$this->validationRules = new ValidationRules();
	}

	/**
	 * Create a named field.
	 *
	 * @unreleased
	 *
	 * @param string $name
	 *
	 * @return static
	 */
	public static function make( $name ) {
		return new static( $name );
	}
}
