<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Contracts\Collection;
use Give\Framework\FieldsAPI\Contracts\Node;

/**
 * Stores an reference to the node being moved for a fluent API.
 * Combines `remove` and `insert*` methods for a declarative API.
 */
class MoveNodeProxy {

	/** @var Collection */
	protected $collection;

	/** @var Node */
	protected $targetNode;

	/**
	 * @param Collection $collection
	 */
	public function __construct( Collection $collection ) {
		$this->collection = $collection;
	}

	/**
	 * @param Node $node
	 */
	public function move( $node ) {
		$this->targetNode = $node;
	}

	/**
	 * @param string $name The name of the node after which the target node should be inserted.
	 */
	public function after( $name ) {
		$this->collection->remove( $this->targetNode->getName() );
		$this->collection->insertAfter( $name, $this->targetNode );
	}

	/**
	 * @param string $name The name of the node before which the target node should be inserted.
	 */
	public function before( $name ) {
		$this->collection->remove( $this->targetNode->getName() );
		$this->collection->insertBefore( $name, $this->targetNode );
	}
}
