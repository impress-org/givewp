<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\Exceptions\Primitives\RuntimeException;

/**
 * A single checkbox input. If supported, an indeterminate value is represented as a null value, as opposed to true or
 * false value.
 *
 * @unreleased
 */
class Checkbox extends Field
{
    use Concerns\HasHelpText;
    use Concerns\HasLabel;
    use Concerns\ShowInReceipt;
    use Concerns\StoreAsMeta;

    const TYPE = 'checkbox';

    /**
     * @var bool whether the checkbox is checked by default
     */
    protected $checked = false;

    /**
     * @var mixed the value of the checkbox when checked
     */
    protected $value;

    /**
     * Sets the value the checkbox returns when checked.
     *
     * The default value is also set because the getDefaultMethod() method is not called during serialization.
     *
     * @unreleased
     */
    public function value($value): self
    {
        $this->value = $value;
        $this->defaultValue = $this->checked ? $value : null;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Since the default value needs to reflect the value of the checkbox, this method is not supported.
     *
     * @unreleased
     */
    public function defaultValue($defaultValue)
    {
        throw new RuntimeException(
            'Do not set the default value. Instead, set the value and use the checked() method.'
        );
    }

    /**
     * Sets the checkbox as checked by default
     *
     * The default value is also set because the getDefaultMethod() method is not called during serialization.
     *
     * @unreleased
     */
    public function checked(bool $checked = true): self
    {
        $this->checked = $checked;
        $this->defaultValue = $this->checked ? $this->value : null;

        return $this;
    }

    /**
     * @unreleased
     */
    public function isChecked(): bool
    {
        return $this->checked;
    }
}
