<?php

namespace Give\Framework\FieldsAPI;

/**
 * @unreleased
 */
class Textarea extends Field {

	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\HasPlaceholder;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	const TYPE = 'textarea';
}
