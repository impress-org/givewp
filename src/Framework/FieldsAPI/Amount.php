<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Concerns\HasLabel;

/**
 * Class Amount
 *
 * This class represents an amount field.
 *
 * @package YourNamespace
 */
class Amount extends Field
{
    use HasLabel;

    const TYPE = 'amount';

    /**
     * @var float[]
     */
    protected $levels = [];

    /**
     * @var null|float[]
     */
    protected $recurringLevels = null;

    /**
     * @var float|null
     */
    protected $recurringDefaultValue = null;

    /**
     * @var string
     */
    protected $customAmountText;

    /**
     * @var bool
     */
    protected $allowCustomAmount = false;
    /**
     * @var bool
     */
    protected $allowLevels = false;

    /**
     * @var float|int
     */
    protected $fixedAmountValue;

    /**
     * Set the preset donation levels. Provide levels in minor units.
     *
     * @since 3.0.0
     */
    public function levels(float ...$levels): self
    {
        $this->levels = $levels;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function getLevels(): array
    {
        return $this->levels;
    }

    /**
     * @since unreleased
     */
    public function recurringLevels(float ...$levels): self
    {
        $this->recurringLevels = $levels;

        return $this;
    }

    /**
     * @since unreleased
     */
    public function recurringDefaultLevel(float $level): self
    {
        $this->recurringDefaultValue = $level;

        return $this;
    }

    /**
     * @since unreleased
     */
    public function disableRecurringLevels(): self
    {
        $this->recurringLevels = null;

        return $this;
    }

    /**
     * @since unreleased
     *
     * @return array<float>} The recurring levels.
     */
    public function getRecurringLevels(): array
    {
        return $this->recurringLevels;
    }

    /**
     * @since 3.0.0
     */
    public function allowCustomAmount($allow = true): self
    {
        $this->allowCustomAmount = $allow;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function allowLevels($allow = true): self
    {
        $this->allowLevels = $allow;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function customAmountAllowed(): bool
    {
        return $this->allowCustomAmount;
    }

    /**
     * @since 3.0.0
     */
    public function customAmountText(string $customAmountText): Amount
    {
        $this->customAmountText = $customAmountText;

        return $this;
    }

    /**
     * @since 3.0.0
     *
     * @param float|int $amount
     */
    public function fixedAmountValue($amount): Amount
    {
        $this->fixedAmountValue = $amount;

        return $this;
    }

    /**
     * @since 3.0.0
     */
    public function getFixedAmountValue()
    {
        return $this->fixedAmountValue;
    }
}
