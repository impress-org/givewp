<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Contracts\Collection;
use Give\Framework\FieldsAPI\Contracts\Node;

class Form implements Node, Collection {

	use Concerns\AppendNodes;
	use Concerns\HasLabel;
	use Concerns\HasNodes;
	use Concerns\HasType;
	use Concerns\InsertNode;
	use Concerns\RemoveNode;
	use Concerns\WalkNodes;

	const TYPE = 'form';

	/** @var string */
	protected $name = 'root';

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return static
	 */
	public static function make() {
		return new static();
	}
}
