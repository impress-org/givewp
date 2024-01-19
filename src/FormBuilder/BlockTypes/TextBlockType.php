<?php

namespace Give\FormBuilder\BlockTypes;

use Give\Framework\Blocks\BlockType;

/**
 * @unreleased
 *
 * @property string $label
 * @property string $description
 * @property string $placeholder
 * @property bool $isRequired
 * @property bool $displayInAdmin
 * @property bool $displayInReceipt
 * @property string $defaultValue
 * @property string $emailTag
 * @property string $fieldName
 * @property array $conditionalLogic
 * @property bool $storeAsDonorMeta
 */
class TextBlockType extends BlockType
{
    /**
     * @unreleased
     */
    public static function name(): string
    {
        return 'givewp/text';
    }

    /**
     * @unreleased
     */
    protected $properties = [
        'label' => 'string',
        'description' => 'string',
        'placeholder' => 'string',
        'isRequired' => 'bool',
        'conditionalLogic' => 'array',
        'storeAsDonorMeta' => 'bool',
        'displayInAdmin' => 'bool',
        'displayInReceipt' => 'bool',
        'defaultValue' => 'string',
        'emailTag' => 'string',
        'fieldName' => 'string',
    ];
}
