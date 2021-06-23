<?php

namespace Give\Framework\FieldsAPI\Fields;

abstract class Field implements Contracts\Field {

	use Concerns\HasDefaultValue;
	use Concerns\HasType;
	use Concerns\IsReadOnly;
	use Concerns\IsRequired;
	use Concerns\MakeFieldWithName;
	use Concerns\SerializeAsJson;
}
