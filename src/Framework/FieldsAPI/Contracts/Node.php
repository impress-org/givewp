<?php

namespace Give\Framework\FieldsAPI\Contracts;

use JsonSerializable;

interface Node extends JsonSerializable {

	/**
	 * Get the field’s type.
	 *
	 * @return string
	 */
	public function getType();

	/**
	 * Get the node’s name.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize();
}
