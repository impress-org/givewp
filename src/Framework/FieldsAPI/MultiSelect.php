<?php

namespace Give\Framework\FieldsAPI;

use InvalidArgumentException;

/**
 * @unlreased
 */
class MultiSelect extends Field
{
    use Concerns\AllowMultiple;
    use Concerns\HasEmailTag;
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\HasOptions;
    use Concerns\HasPlaceholder;
    use Concerns\HasDescription;

    protected $fieldType;

    const TYPE = 'multiSelect';

    /**
     * @unreleased
     */
    public function getFieldType(): string
    {
        return $this->fieldType;
    }

    /**
     * @unreleased
     */
    public function fieldType(string $fieldType): MultiSelect
    {
        if (!in_array($fieldType, ['checkbox', 'dropdown'])) {
            throw new InvalidArgumentException(__('Field type must be either "checkbox" or "dropdown".', 'give'));
        }

        $this->fieldType = $fieldType;

        return $this;
    }
}
