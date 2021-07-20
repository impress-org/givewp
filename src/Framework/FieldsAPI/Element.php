<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Contracts\Node;

abstract class Element implements Node {

	use Concerns\HasType;
	use Concerns\HasName;
	use Concerns\MergeWithJsonSerializeFromTraits;

	/**
	 * @param string $name
	 */
	public function __construct( $name ) {
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

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize() {
		return $this->mergeWithJsonSerializeFromTraits(
			[
				'name' => $this->getName(),
				'type' => $this->getType(),
			]
		);
	}
}
