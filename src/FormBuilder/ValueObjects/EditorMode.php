<?php

namespace Give\FormBuilder\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 3.0.0
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
