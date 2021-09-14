<?php

namespace Give\Framework\FieldsAPI;

/**
 * @since 2.12.0
 * @unreleased Add support for min/max length
 */
class Textarea extends Field {

	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\HasPlaceholder;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;
	use Concerns\HasMaxLength;
	use Concerns\HasMinLength;

	const TYPE = 'textarea';
}
