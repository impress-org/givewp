<?php

namespace Give\FormBuilder\BlockTypes;

use Give\FormBuilder\BlockTypes\Concerns\HasDefaultFieldAttributes;
use Give\Framework\Blocks\BlockType;

/**
 * @unreleased
 */
class TextBlockType extends BlockType
{
    use HasDefaultFieldAttributes;

    /**
     * @unreleased
     */
    public static function name(): string
    {
        return 'givewp/text';
    }
}
