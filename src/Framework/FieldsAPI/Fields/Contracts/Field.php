<?php

namespace Give\Framework\FieldsAPI\Fields\Contracts;

use Give\Framework\FieldsAPI\FieldCollection\Contract\Node;

interface Field extends Node {

	/**
	 * Get the field’s type.
	 *
	 * @return string
	 */
	public function getType();

	/**
	 * Make a named field.
	 *
	 * @param string $name
	 *
	 * @return static
	 */
	public static function make( $name );
}
