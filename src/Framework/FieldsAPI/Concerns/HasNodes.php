<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Contracts\Collection;
use Give\Framework\FieldsAPI\Contracts\Node;
use Give\Framework\FieldsAPI\Field;

trait HasNodes {

	/** @var Node[] */
	protected $nodes = [];

	/**
	 * {@inheritdoc}
	 */
	public function getNodeIndexByName( $name ) {
		foreach ( $this->nodes as $index => $node ) {
			if ( $node->getName() === $name ) {
				return $index;
			}
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getNodeByName( $name ) {
		foreach ( $this->nodes as $node ) {
			if ( $node->getName() === $name ) {
				return $node;
			}
			if ( $node instanceof Collection ) {
				return $node->getNodeByName( $name );
			}
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function all() {
		return $this->nodes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFields() {
		return array_filter(
			$this->nodes,
			static function ( Node $node ) {
				return $node instanceof Field;
			}
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function count() {
		return count( $this->nodes );
	}

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize() {
		return array_map(
			static function( $node ) {
				return $node->jsonSerialize();
			},
			$this->nodes
		);
	}

}
