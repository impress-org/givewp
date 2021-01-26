<?php

namespace Give\Framework\FieldsAPI\FieldCollection\Contract;

use JsonSerializable;

interface GroupNode extends FieldNode {
    public function flatten();
    public function getFields();
}