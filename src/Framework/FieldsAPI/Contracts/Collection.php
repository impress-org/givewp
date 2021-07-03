<?php

namespace Give\Framework\FieldsAPI\Contracts;

use Give\Framework\FieldsAPI\Field;

interface Collection {

	/**
	 * Fluently append nodes to the collection.
	 *
	 * @param Node ...$nodes
	 *
	 * @return $this
	 */
	public function append( Node ...$nodes );

	/**
	 * Fluently remove a named node.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function remove( $name );

	/**
	 * Get all the nodes.
	 *
	 * @return Node[]
	 */
	public function all();

	/**
	 * Count all the nodes.
	 *
	 * @return int
	 */
	public function count();

	/**
	 * Get a node’s index by its name.
	 *
	 * @param string $name
	 *
	 * @return int
	 */
	public function getNodeIndexByName( $name );

	/**
	 * Get a node by its name.
	 *
	 * @param string $name
	 *
	 * @return Node
	 */
	public function getNodeByName( $name );

	/**
	 * Get only the field nodes.
	 *
	 * @return Field[]
	 */
	public function getFields();
}
