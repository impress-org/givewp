<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Concerns\HasLabel;

class Amount extends Field
{
    use HasLabel;

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
     * This is a temporary overload in order to add the validation rule to the field. Once validation rules have been
     * improved in the Field API this will be removed.
     *
     * @unreleased
     *
     * @param $name
     */
    public static function make($name): self
    {
        $amount =  parent::make($name);
        $amount->validationRules->rule('numeric', true);
        return $amount;
    }

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
