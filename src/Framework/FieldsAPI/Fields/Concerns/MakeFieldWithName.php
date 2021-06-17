<?php

namespace Give\Framework\FieldsAPI\Fields\Concerns;

/**
 * Make a name for yourself
 */
trait MakeFieldWithName {

	use HasName;

	/**
	 * @param string $name
	 */
	protected function __construct( $name ) {
		$this->name = $name;
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
