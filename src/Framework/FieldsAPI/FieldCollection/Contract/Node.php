<?php

namespace Give\Framework\FieldsAPI\FieldCollection\Contract;

use JsonSerializable;

interface Node extends JsonSerializable {
	public function getName();
	public function jsonserialize();
}
