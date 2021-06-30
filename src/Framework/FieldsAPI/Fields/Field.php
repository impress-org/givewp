<?php

namespace Give\Framework\FieldsAPI\Fields;

use Give\Framework\FieldsAPI\ValidationRules;

abstract class Field implements Contracts\Field {

	use Concerns\HasDefaultValue;
	use Concerns\HasHtmlRootElementClassName;
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
