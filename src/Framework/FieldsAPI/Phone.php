<?php

namespace Give\Framework\FieldsAPI;

/**
 * @unreleased
 */
class Phone extends Field {

	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\HasPlaceholder;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	const TYPE = 'phone';
}
