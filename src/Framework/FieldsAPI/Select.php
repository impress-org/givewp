<?php

namespace Give\Framework\FieldsAPI;

/**
 * @unreleased
 */
class Select extends Field {

	use Concerns\AllowMultiple;
	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\HasOptions;
	use Concerns\HasPlaceholder;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	const TYPE = 'select';
}
