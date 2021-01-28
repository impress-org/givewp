<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\FieldCollection\Contract\FieldNode;
use Give\Framework\FieldsAPI\FieldCollection\Contract\GroupNode;

class FieldCollection implements GroupNode {

    use FieldCollection\MoveNode;
    use FieldCollection\RemoveNode;
    use FieldCollection\NodeWalker;

    /** @var string */
    protected $name;

    /** @var FieldNode[] */
    protected $nodes = [];

    public function __construct( $name, array $nodes ) {
        $this->name = $name;
        $this->nodes = $nodes;
    }

    public function getName() {
        return $this->name;
    }

    public function insertAfter( $siblingName, FieldNode $node ) {
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

    public function insertBefore( $siblingName, FieldNode $node ) {
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

    public function getNodeIndexByName( $name ) {
        foreach( $this->nodes as $index => $node ) {
            if( $node->getName() === $name ) {
                return $index;
            }
        }
        return false;
    }

    public function getNodeByName( $name ) {
        foreach( $this->nodes as $index => $node ) {
            if( $node->getName() === $name ) {
                return $node;
            }
            if( $node instanceof GroupNode ) {
                return $node->getNodeByName( $name );
            }
        }
        return false;
    }

    public function insertAtIndex( $index, $node ) {
        array_splice( $this->nodes, $index, 0, [ $node ] );
    }

    public function jsonserialize() {
        return array_map( function( $node ) {
            return $node->jsonserialize();
        }, $this->nodes );
    }

    public function flatten() {
        return array_reduce( $this->nodes, function( $carry, $node ) {
            if( $node instanceof GroupNode ) {
                return array_merge( $carry, $node->flatten() );
            }
            return array_merge( $carry, [ $node ] );
        }, [] );
    }

    public function getFields() {
        return $this->nodes;
    }
}
