<?php

namespace Give\Framework\FieldsAPI;

class Hidden extends Field {

	use Concerns\ShowInReceipt;
	use Concerns\StoreAsMeta;

	const TYPE = 'hidden';
}
