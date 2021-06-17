<?php

namespace Give\Framework\FieldsAPI\Fields;

use Give\Framework\FieldsAPI\Fields\Contracts\Field;

class Hidden implements Field {

	use Concerns\HasDefaultValue;
	use Concerns\HasType;
	use Concerns\MakeFieldWithName;
	use Concerns\SerializeAsJson;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	/** @var string */
	protected $type = 'hidden';
}
