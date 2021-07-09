<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Contracts\Collection;

/**
 * A declarative iterator for each Node in the tree.
 */
trait WalkNodes {

	/**
	 * @param callable $callback
	 *
	 * @return void
	 */
	public function walk( callable $callback ) {
		$this->walkCollection( $this, $callback );
	}

	/**
	 * @param Collection $collection
	 * @param callable $callback
	 *
	 * @return void
	 */
	public function walkCollection( Collection $collection, callable $callback ) {
		foreach ( $collection->all() as $node ) {
			$callback( $node );
			if ( $node instanceof Collection ) {
				$this->walkCollection( $node, $callback );
			}
		}
	}

	/**
	 * @param callable $callback
	 *
	 * @return void
	 */
	public function walkFields( callable $callback ) {
		foreach ( $this->getFields() as $field ) {
			$callback( $field );
		}
	}
}
