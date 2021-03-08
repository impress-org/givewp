<?php

namespace Give\Framework\FieldsAPI\FieldCollection\Contract;

use JsonSerializable;

interface GroupNode extends Node {
	public function getFields();
}
