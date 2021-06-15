<?php

namespace Give\Framework\FieldsAPI\FieldCollection\Contract;

interface GroupNode extends Node {

	/**
	 * Get the fields in the group.
	 *
	 * @return Node[]
	 */
	public function getFields();

	/**
	 * Append a node to the group.
	 *
	 * @param Node $node
	 *
	 * @return $this
	 */
	public function append( Node $node );

	/**
	 * Get a node’s index by its name.
	 *
	 * @param string $name
	 *
	 * @return false|int
	 */
	public function getNodeIndexByName( $name );

	/**
	 * Get a node by its name.
	 *
	 * @param string $name
	 *
	 * @return false|Node
	 */
	public function getNodeByName( $name );

	/**
	 * Count the nodes.
	 *
	 * @return int
	 */
	public function count();
}
