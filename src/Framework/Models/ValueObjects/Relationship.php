<?php

namespace Give\Framework\Models\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * Model Relationships
 *
 * @unreleased
 *
 * @method static ONE_TO_ONE();
 * @method static ONE_TO_MANY();
 * @method static MANY_TO_MANY();
 * @method static BELONGS_TO();
 * @method static BELONGS_TO_MANY();
 */
class Relationship extends Enum
{
    const ONE_TO_ONE = 'one-to-one';
    const ONE_TO_MANY = 'one-to-many';
    const MANY_TO_MANY = 'many-to-many';
    const BELONGS_TO = 'belongs-to';
    const BELONGS_TO_MANY = 'belongs-to-many';
}
