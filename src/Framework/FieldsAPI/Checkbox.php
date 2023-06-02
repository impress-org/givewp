<?php

namespace Give\Framework\FieldsAPI;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

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
