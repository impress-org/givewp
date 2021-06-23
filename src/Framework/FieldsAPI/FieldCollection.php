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

	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function append( Node ...$nodes ) {
		foreach ( $nodes as $node ) {
			$this->insertAtIndex( $this->count(), $node );
		}
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNodeIndexByName( $name ) {
		foreach ( $this->nodes as $index => $node ) {
			if ( $node->getName() === $name ) {
				return $index;
			}
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNodeByName( $name ) {
		foreach ( $this->nodes as $node ) {
			if ( $node->getName() === $name ) {
				return $node;
			}
			if ( $node instanceof GroupNode ) {
				return $node->getNodeByName( $name );
			}
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize() {
		return array_map(
			static function( $node ) {
				return $node->jsonSerialize();
			},
			$this->nodes
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFields() {
		return $this->nodes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function count() {
		return count( $this->getFields() );
	}
}
