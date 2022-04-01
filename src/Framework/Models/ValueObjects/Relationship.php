<?php

namespace Give\Framework\Models\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * Model Relationships
 *
 * @since 2.19.6
 *
 * @method static HAS_ONE();
 * @method static HAS_MANY();
 * @method static MANY_TO_MANY();
 * @method static BELONGS_TO();
 * @method static BELONGS_TO_MANY();
 */
class Relationship extends Enum
{
    const HAS_ONE = 'has-one';
    const HAS_MANY = 'has-many';
    const MANY_TO_MANY = 'many-to-many';
    const BELONGS_TO = 'belongs-to';
    const BELONGS_TO_MANY = 'belongs-to-many';
}
