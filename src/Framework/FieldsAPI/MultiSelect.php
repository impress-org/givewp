<?php

namespace Give\Framework\FieldsAPI;

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
        $this->fieldType = $fieldType;

        return $this;
    }
}
