<?php

namespace Give\Framework\FieldsAPI;

use InvalidArgumentException;

/**
 * @since 3.0.0
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
     * @since 3.0.0
     */
    public function getFieldType(): string
    {
        return $this->fieldType;
    }

    /**
     * @since 3.0.0
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
