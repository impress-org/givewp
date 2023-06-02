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
     * Sets the value the checkbox returns when checked
     *
     * @unreleased
     */
    public function value($value): self
    {
        $this->value = $value;

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
     * Makes sure that the default value is based on whether the checkbox is checked and whatever the underlying value
     * is. Without this, it would be necessary to always set the value before setting whether the checkbox is checked.
     *
     * @unreleased
     */
    public function getDefaultValue()
    {
        return $this->checked ? $this->value : null;
    }

    /**
     * Sets the checkbox as checked by default
     *
     * @unreleased
     */
    public function checked(bool $checked = true): self
    {
        $this->checked = $checked;

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
