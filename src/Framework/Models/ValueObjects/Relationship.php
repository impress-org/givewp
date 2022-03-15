<?php

namespace Give\Framework\Models\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @method static ONE_TO_ONE();
 * @method static ONE_TO_MANY();
 */
class Relationship extends Enum {
    const ONE_TO_ONE = 'one-to-one';
    const ONE_TO_MANY = 'one-to-many';
}
