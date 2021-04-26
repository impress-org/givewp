<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\FieldCollection\Contract\Node;
use Give\Framework\FieldsAPI\FieldCollection\Contract\GroupNode;

/**
 * @since 2.10.2
 */
class FieldCollection implements GroupNode {

	use FieldCollection\NameCollision;
	use FieldCollection\InsertNode;
	use FieldCollection\MoveNode;
	use FieldCollection\RemoveNode;
	use FieldCollection\WalkNodes;

	/** @var string */
	protected $name;

	/** @var Node[] */
	protected $nodes = [];

	public function __construct( $name, array $nodes = [] ) {
		$this->name  = $name;
		$this->nodes = $nodes;
	}

	public function getName() {
		return $this->name;
	}

	public function append( Node $node ) {
		$this->insertAtIndex( $this->count(), $node );
		return $this;
	}

	public function getNodeIndexByName( $name ) {
		foreach ( $this->nodes as $index => $node ) {
			if ( $node->getName() === $name ) {
				return $index;
			}
		}
		return false;
	}

	public function getNodeByName( $name ) {
		foreach ( $this->nodes as $index => $node ) {
			if ( $node->getName() === $name ) {
				return $node;
			}
			if ( $node instanceof GroupNode ) {
				return $node->getNodeByName( $name );
			}
		}
		return false;
	}

	public function jsonserialize() {
		return array_map(
			function( $node ) {
				return $node->jsonserialize();
			},
			$this->nodes
		);
	}

	public function getFields() {
		return $this->nodes;
	}

	public function count() {
		return count( $this->getFields() );
	}
}
