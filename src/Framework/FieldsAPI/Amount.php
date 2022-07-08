<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

class Amount extends Field
{
    const TYPE = 'amount';

    /**
     * @var int[]
     */
    protected $levels = [];

    /**
     * @var bool
     */
    protected $allowCustomAmount = false;

    /**
     * Set the preset donation levels. Provide levels in minor units.
     *
     * @unreleased
     */
    public function levels(int ...$levels): self
    {
        $this->levels = $levels;

        return $this;
    }

    /**
     * @unreleased
     */
    public function getLevels(): array
    {
        return $this->levels;
    }

    /**
     * @unreleased
     */
    public function allowCustomAmount($allow = true): self
    {
        $this->allowCustomAmount = $allow;

        return $this;
    }

    /**
     * @unreleased
     */
    public function customAmountAllowed(): bool
    {
        return $this->allowCustomAmount;
    }
}
