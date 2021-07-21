<?php

namespace Give\Framework\FieldsAPI;

/**
 * @since 2.12.0
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
