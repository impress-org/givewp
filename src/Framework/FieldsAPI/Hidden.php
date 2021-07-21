<?php

namespace Give\Framework\FieldsAPI;

/**
 * @unreleased
 */
class Hidden extends Field {

	use Concerns\HasLabel;
	use Concerns\HasEmailTag;
	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	const TYPE = 'hidden';
}
