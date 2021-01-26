<?php

namespace Give\Framework\FieldsAPI\FieldCollection;

use Give\Framework\FieldsAPI\FieldCollection;
use Give\Framework\FieldsAPI\FieldCollection\Contract\GroupNode;

/**
 * A declaritive iterator for each FieldNode in the tree.
 */
trait NodeWalker {

    /**
     * @param Callable $callback
     * 
     * @return void
     */
    public function walk( Callable $callback ) {
        $this->walkCollection( $this, $callback );
    }

    /**
     * @param FieldCollection $collection
     * @param Callable $callback
     * 
     * @return void
     */
    public function walkCollection( FieldCollection $collection, Callable $callback ) {
        foreach( $collection->getFields() as $node ) {
            if( $node instanceof GroupNode ) {
                $this->walkCollection( $node, $callback );
                continue;
            }
            $callback( $node );
        }
    }
}
