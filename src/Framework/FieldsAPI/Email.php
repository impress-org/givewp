<?php

namespace Give\Framework\FieldsAPI;

class Email extends Field {

	use Concerns\AllowMultiple;
	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\HasPlaceholder;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	const TYPE = 'email';
}
