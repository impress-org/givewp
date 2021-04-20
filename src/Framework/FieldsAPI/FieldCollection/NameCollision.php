<?php

namespace Give\Framework\FieldsAPI\FieldCollection;

use Give\Framework\FieldsAPI\FieldCollection\Contract\GroupNode;
use Give\Framework\FieldsAPI\FieldCollection\Exception\NameCollisionException;

/**
 * @since 2.10.2
 */
trait NameCollision {

	/**
	 * @param Node $node
	 */
	public function checkNameCollisionDeep( $node ) {
		if ( $node instanceof GroupNode ) {
			$node->walk( [ $this, 'checkNameCollision' ] );
		}
		return $this->checkNameCollision( $node );
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
