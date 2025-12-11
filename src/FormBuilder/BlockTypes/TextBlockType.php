<?php

namespace Give\FormBuilder\BlockTypes;

use Give\FormBuilder\BlockTypes\Concerns\HasDefaultFieldAttributes;
use Give\Framework\Blocks\BlockType;

/**
 * @since 3.8.0
 */
class TextBlockType extends BlockType
{
    use HasDefaultFieldAttributes;

    /**
     * @since 3.8.0
     */
    public static function name(): string
    {
        return 'givewp/text';
    }

    /**
     * @since 3.21.0
     */
    public function getFieldName(int $blockIndex): string
    {
        return !empty($this->fieldName) ? $this->fieldName : $this->block->getShortName() . '-' . $blockIndex;
    }
}
