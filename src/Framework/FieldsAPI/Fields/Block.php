<?php

namespace Give\Framework\FieldsAPI\Fields;

use Give\Framework\FieldsAPI\FieldCollection\Contract\Node;

abstract class Block implements Node {

	use Concerns\HasType;
	use Concerns\HasName;
	use Concerns\SerializeAsJson;

	/**
	 * @param string $name
	 */
	protected function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * Create a named block.
	 *
	 * @param string $name
	 *
	 * @return static
	 */
	public static function make( $name ) {
		return new static( $name );
	}
}
