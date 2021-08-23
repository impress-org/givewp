<?php

namespace Give\Framework\FieldsAPI;

/**
 * @since 2.12.0
 * @unreleased Allow field to be repeated
 */
class Text extends Field {

	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\HasPlaceholder;
	use Concerns\IsRepeatable;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	const TYPE = 'text';
}
