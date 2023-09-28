<?php

namespace Give\FormBuilder\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static EditorMode DESIGN()
 * @method static EditorMode SCHEMA()
 * @method bool isDesign()
 * @method bool isSchema()
 */
class EditorMode extends Enum
{
    const DESIGN = 'design';
    const SCHEMA = 'schema';
}
