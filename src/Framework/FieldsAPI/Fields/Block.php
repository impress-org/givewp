<?php

namespace Give\Framework\FieldsAPI\Fields;

use Give\Framework\FieldsAPI\FieldCollection\Contract\Node;

abstract class Block extends Node {

	use Concerns\HasType;
	use Concerns\MakeFieldWithName;
	use Concerns\SerializeAsJson;
}
