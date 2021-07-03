<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Contracts\GroupNode;

/**
 * A declarative iterator for each Node in the tree.
 */
trait WalkNodes {

	/**
	 * @param Callable $callback
	 *
	 * @return void
	 */
	public function walk( callable $callback ) {
		$this->walkCollection( $this, $callback );
	}

	/**
	 * @param GroupNode $collection
	 * @param Callable $callback
	 *
	 * @return void
	 */
	public function walkCollection( GroupNode $collection, callable $callback ) {
		foreach ( $collection->getFields() as $node ) {
			if ( $node instanceof GroupNode ) {
				$this->walkCollection( $node, $callback );
				continue;
			}
			$callback( $node );
		}
	}
}
