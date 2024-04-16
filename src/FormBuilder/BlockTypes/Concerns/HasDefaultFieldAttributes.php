<?php

namespace Give\FormBuilder\BlockTypes\Concerns;

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
trait HasDefaultFieldAttributes
{
    /**
     * @unreleased
     */
    protected function setDefaultProperties(): array
    {
        return [
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
}
