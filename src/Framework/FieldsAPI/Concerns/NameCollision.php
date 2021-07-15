<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Contracts\Collection;
use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;

/**
 * @since 2.10.2
 */
trait NameCollision {

	/**
	 * @param Node $node
	 * @throws NameCollisionException
	 */
	public function checkNameCollisionDeep( $node ) {
		$this->checkNameCollision( $node );
		if ( $node instanceof Collection ) {
			$node->walk( [ $this, 'checkNameCollision' ] );
		}
	}

	/**
	 * @param Node $node
	 * @throws NameCollisionException
	 */
	public function checkNameCollision( $node ) {
		if ( $this->getNodeByName( $node->getName() ) ) {
			throw new NameCollisionException( $node->getName() );
		}
	}
}
