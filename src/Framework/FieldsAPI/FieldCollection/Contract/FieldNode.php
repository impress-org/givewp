<?php

namespace Give\Framework\FieldsAPI\FieldCollection\Contract;

use JsonSerializable;

interface FieldNode extends JsonSerializable {
    public function getName();
    public function jsonserialize();
}