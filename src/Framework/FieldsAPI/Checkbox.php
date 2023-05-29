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
     * @var bool|null
     */
    protected $defaultValue = false;

    /**
     * @var bool
     */
    protected $supportIndeterminateValue = false;

    /**
     * @unreleased
     *
     * @param bool|null $defaultValue
     *
     * @return void
     */
    public function defaultValue($defaultValue = null)
    {
        if ($defaultValue === null && !$this->supportIndeterminateValue) {
            throw new InvalidArgumentException(
                'Checkbox field does not support intermediate value'
            );
        }

        if (!is_bool($defaultValue) && $defaultValue !== null) {
            throw new InvalidArgumentException(
                'Checkbox field only supports boolean or null value'
            );
        }

        $this->defaultValue = $defaultValue;
    }

    /**
     * Sets the checkbox as checked by default
     *
     * @unreleased
     */
    public function checked(bool $checked = true): self
    {
        $this->defaultValue($checked);

        return $this;
    }

    /**
     * Sets the checkbox as indeterminate by default
     *
     * @unreleased
     */
    public function indeterminate(): self
    {
        $this->defaultValue(null);

        return $this;
    }

    /**
     * @unreleased
     */
    public function isChecked(): bool
    {
        return (bool)$this->defaultValue;
    }

    /**
     * @unreleased
     */
    public function isIndeterminate(): bool
    {
        return $this->defaultValue === null;
    }

    /**
     * @unreleased
     */
    public function supportIndeterminateValue(bool $support = true): self
    {
        $this->supportIndeterminateValue = $support;

        return $this;
    }

    /**
     * @unreleased
     */
    public function supportsIndeterminateValue(): bool
    {
        return $this->supportIndeterminateValue;
    }
}
