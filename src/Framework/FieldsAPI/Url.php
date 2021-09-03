<?php

namespace Give\Framework\FieldsAPI;

/**
 * @since 2.12.0
 * @unreleased add min/max length validation
 */
class Url extends Field {

	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\HasMaxLength;
	use Concerns\HasMinLength;
	use Concerns\HasPlaceholder;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	const TYPE = 'url';
}
