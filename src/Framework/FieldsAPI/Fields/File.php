<?php

namespace Give\Framework\FieldsAPI\Fields;

use Give\Framework\FieldsAPI\Fields\Contracts\Field;

/**
 * A file upload field.
 *
 * @unreleased
 */
class File implements Field {

	// TODO: how would default values work for this and how would we serialize that? Do we want default values?
	//use Concerns\HasDefaultValue;

	use Concerns\HasEmailTag;
	use Concerns\HasHelpText;
	use Concerns\HasLabel;
	use Concerns\HasType;
	use Concerns\IsReadOnly;
	use Concerns\IsRequired;
	use Concerns\MakeFieldWithName;
	use Concerns\SerializeAsJson;

	// TODO: Not sure how these would work
	//use Concerns\ShowInReceipt;
	//use Concerns\StoreAsMeta;

	protected $type = 'file';

}
