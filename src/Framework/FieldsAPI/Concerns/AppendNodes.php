<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Contracts\Node;

trait AppendNodes {

	/**
	 * {@inheritdoc}
	 */
	public function append( Node ...$nodes ) {
		foreach ( $nodes as $node ) {
			$this->insertAtIndex( $this->count(), $node );
		}
		return $this;
	}
}
