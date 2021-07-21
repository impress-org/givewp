<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Contracts\Node;

/**
 * @since 2.12.0
 */
abstract class Element implements Node {

	use Concerns\HasType;
	use Concerns\HasName;
	use Concerns\SerializeAsJson;

	/**
	 * @param string $name
	 */
	public function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * Create a named block.
	 *
	 * @since 2.12.0
	 *
	 * @param string $name
	 *
	 * @return static
	 */
	public static function make( $name ) {
		return new static( $name );
	}
}
