<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Contracts\Collection;
use Give\Framework\FieldsAPI\Exceptions\ReferenceNodeNotFoundException;

/**
 * @since 2.10.2
 */
trait InsertNode {

	/**
	 * @since 2.10.2
	 *
	 * @param string $siblingName
	 * @param Node $node
	 *
	 * @return $this
	 * @throws ReferenceNodeNotFoundException
	 */
	public function insertAfter( $siblingName, Node $node ) {
		$this->checkNameCollisionDeep( $node );
		$this->insertAfterRecursive( $siblingName, $node );
		return $this;
	}

	/**
	 * @since 2.10.2
	 *
	 * @param string $siblingName
	 * @param Node $node
	 *
	 * @throws ReferenceNodeNotFoundException
	 *
	 * @return void
	 */
	protected function insertAfterRecursive( $siblingName, Node $node ) {
		$siblingIndex = $this->getNodeIndexByName( $siblingName );
		if ( false !== $siblingIndex ) {
			$this->insertAtIndex(
				$siblingIndex + 1,
				$node
			);
			return;
		}

		if ( $this->nodes ) {
			foreach ( $this->nodes as $childNode ) {
				if ( $childNode instanceof Collection ) {
					$childNode->insertAfter( $siblingName, $node );
				}
			}
			return;
		}

		throw new ReferenceNodeNotFoundException( $siblingName );
	}

	/**
	 * @since 2.10.2
	 *
	 * @param string $siblingName
	 * @param Node $node
	 *
	 * @throws ReferenceNodeNotFoundException
	 *
	 * @return $this
	 */
	public function insertBefore( $siblingName, Node $node ) {
		$this->checkNameCollisionDeep( $node );
		$this->insertBeforeRecursive( $siblingName, $node );
		return $this;
	}

	/**
	 * @since 2.10.2
	 *
	 * @param string $siblingName
	 * @param Node $node
	 *
	 * @throws ReferenceNodeNotFoundException
	 *
	 * @return void
	 */
	protected function insertBeforeRecursive( $siblingName, Node $node ) {
		$siblingIndex = $this->getNodeIndexByName( $siblingName );
		if ( false !== $siblingIndex ) {
			$this->insertAtIndex(
				$siblingIndex - 1,
				$node
			);
			return;
		}

		if ( $this->nodes ) {
			foreach ( $this->nodes as $childNode ) {
				if ( $childNode instanceof Collection ) {
					$childNode->insertBefore( $siblingName, $node );
				}
			}
			return;
		}

		throw new ReferenceNodeNotFoundException( $siblingName );
	}

	/**
	 * @since 2.10.2
	 */
	protected function insertAtIndex( $index, $node ) {
		$this->checkNameCollisionDeep( $node );
		array_splice( $this->nodes, $index, 0, [ $node ] );
	}

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
