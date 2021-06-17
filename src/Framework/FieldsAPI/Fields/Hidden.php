<?php

namespace Give\Framework\FieldsAPI\Fields;

use Give\Framework\FieldsAPI\Fields\Contracts\Field;

class Hidden implements Field {

	use Concerns\CreatesSelfWithName;
	use Concerns\HasType;
	use Concerns\HasName;
	use Concerns\HasDefaultValue;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	/** @var string */
	protected $type = 'hidden';

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize() {
		return [
			'type'         => $this->getType(),
			'name'         => $this->getName(),
			'defaultValue' => $this->getDefaultValue(),
		];
	}
}
