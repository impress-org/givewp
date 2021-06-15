<?php

namespace Give\Framework\FieldsAPI\FieldCollection\Contract;

use JsonSerializable;

interface Node extends JsonSerializable {

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
