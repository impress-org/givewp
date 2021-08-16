<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Contracts\Collection;
use Give\Framework\FieldsAPI\Contracts\Node;

/**
 * @since 2.12.0
 * @unreleased Support visibility conditions
 */
class Group implements Node, Collection {

	use Concerns\HasLabel;
	use Concerns\HasName;
	use Concerns\HasNodes;
	use Concerns\HasType;
	use Concerns\HasVisibilityConditions;
	use Concerns\InsertNode;
	use Concerns\MoveNode;
	use Concerns\NameCollision;
	use Concerns\RemoveNode;
	use Concerns\SerializeAsJson;
	use Concerns\WalkNodes;

	/**
	 * @since 2.12.2
	 */
	const TYPE = 'group';

	public function __construct( $name ) {
		$this->name = $name;
	}

	public static function make( $name ) {
		return new static( $name );
	}
}
