<?php

namespace Give\Framework\FieldsAPI\FieldCollection;

use Give\Framework\FieldsAPI\FieldCollection\Contract\GroupNode;
use Give\Framework\FieldsAPI\FieldCollection\Exception\NameCollisionException;

trait NameCollision {
    public function checkNameCollisionDeep( $node ) {
        if( $node instanceof GroupNode ) {
            $node->walk([ $this, 'checkNameCollision' ]);
        }
        return $this->checkNameCollision( $node );
    }

    public function checkNameCollision( $node ) {
        if( $this->getNodeByName( $node->getName() ) ) {
            throw new NameCollisionException( $node->getName() );
        }
    }
}