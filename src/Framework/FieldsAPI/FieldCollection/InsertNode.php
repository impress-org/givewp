<?php

namespace Give\Framework\FieldsAPI\FieldCollection;

use Give\Framework\FieldsAPI\FieldCollection\Contract\Node;

trait InsertNode {

    public function insertAfter( $siblingName, Node $node ) {
        $siblingIndex = $this->getNodeIndexByName( $siblingName );
        if( false !== $siblingIndex ) {
            $this->insertAtIndex(
                $siblingIndex + 1,
                $node
            );
        } else {
            foreach( $this->nodes as $childNode ) {
                if( $childNode instanceof GroupNode ) {
                    $childNode->insertAfter( $siblingName, $node );
                }
            }
        }
        return $this;
    }

    public function insertBefore( $siblingName, Node $node ) {
        $siblingIndex = $this->getNodeIndexByName( $siblingName );
        if( false !== $siblingIndex ) {
            $this->insertAtIndex(
                $siblingIndex - 1,
                $node
            );
        } else {
            foreach( $this->nodes as $childNode ) {
                if( $childNode instanceof GroupNode ) {
                    $childNode->insertBefore( $siblingName, $node );
                }
            }
        }
        return $this;
    }

    public function insertAtIndex( $index, $node ) {
        array_splice( $this->nodes, $index, 0, [ $node ] );
    }
}