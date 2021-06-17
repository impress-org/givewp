<?php

namespace Give\Framework\FieldsAPI\Fields;

use Give\Framework\FieldsAPI\Fields\Contracts\Field;

class Textarea implements Field {

	use Concerns\HasDefaultValue;
	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\HasPlaceholder;
	use Concerns\HasType;
	use Concerns\IsReadOnly;
	use Concerns\IsRequired;
	use Concerns\MakeFieldWithName;
	use Concerns\SerializeAsJson;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	/** @var string */
	protected $type = 'textarea';
}
