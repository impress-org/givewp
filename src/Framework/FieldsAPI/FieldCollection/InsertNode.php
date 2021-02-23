<?php

namespace Give\Framework\FieldsAPI\FieldCollection;

use Give\Framework\FieldsAPI\FieldCollection\Contract\Node;
use Give\Framework\FieldsAPI\FieldCollection\Contract\GroupNode;
use Give\Framework\FieldsAPI\FieldCollection\Exception\ReferenceNodeNotFoundException;

/**
 * @unreleased
 */
trait InsertNode {

	public function insertAfter( $siblingName, Node $node ) {
		// Check that reference node exists.
		$this->checkNameCollisionDeep( $node );
		$this->_insertAfter( $siblingName, $node );
		return $this;
	}

	protected function _insertAfter( $siblingName, Node $node ) {
		$siblingIndex = $this->getNodeIndexByName( $siblingName );
		if ( false !== $siblingIndex ) {
			return $this->insertAtIndex(
				$siblingIndex + 1,
				$node
			);
		} elseif ( $this->nodes ) {
			foreach ( $this->nodes as $childNode ) {
				if ( $childNode instanceof GroupNode ) {
					$childNode->_insertAfter( $siblingName, $node );
				}
			}
			return;
		}
		throw new ReferenceNodeNotFoundException( $siblingName );
	}

	public function insertBefore( $siblingName, Node $node ) {
		// Check that reference node exists.
		$this->checkNameCollisionDeep( $node );
		$this->_insertBefore( $siblingName, $node );
		return $this;
	}

	protected function _insertBefore( $siblingName, Node $node ) {
		$siblingIndex = $this->getNodeIndexByName( $siblingName );
		if ( false !== $siblingIndex ) {
			return $this->insertAtIndex(
				$siblingIndex - 1,
				$node
			);
		} elseif ( $this->nodes ) {
			foreach ( $this->nodes as $childNode ) {
				if ( $childNode instanceof GroupNode ) {
					$childNode->_insertBefore( $siblingName, $node );
				}
			}
			return;
		}
		throw new ReferenceNodeNotFoundException( $siblingName );
	}

	protected function insertAtIndex( $index, $node ) {
		array_splice( $this->nodes, $index, 0, [ $node ] );
	}
}
