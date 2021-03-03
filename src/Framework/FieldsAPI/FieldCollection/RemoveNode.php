<?php

namespace Give\Framework\FieldsAPI\FieldCollection;

trait RemoveNode {

	public function remove( $name ) {
		foreach ( $this->nodes as $index => $node ) {
			if ( $node->getName() === $name ) {
				unset( $this->nodes[ $index ] );
				return $this;
			}
			if ( $node instanceof GroupNode ) {
				return $node->remove( $name );
			}
		}

		// Maybe need to throw an exception of no node is removed.
		return $this;
	}
}
