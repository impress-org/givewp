<?php

namespace Give\DonationForms\Rules\Concerns;

use function in_array;
use function is_numeric;

/**
 * Amounts an admin configured on the donation amount block, such as the donation levels or the fixed set
 * price. The custom amount minimum and maximum only constrain what a donor types into the custom amount
 * input, so those amounts are always accepted.
 *
 * @since TBD
 */
trait HasExemptAmounts
{
    /**
     * @since TBD
     *
     * @var float[]
     */
    protected $exemptAmounts = [];

    /**
     * @since TBD
     */
    public function exemptAmounts(float ...$amounts): self
    {
        $this->exemptAmounts = $amounts;

        return $this;
    }

    /**
     * @since TBD
     *
     * @return float[]
     */
    public function getExemptAmounts(): array
    {
        return $this->exemptAmounts;
    }

    /**
     * @since TBD
     *
     * @param mixed $value
     */
    protected function isExemptAmount($value): bool
    {
        return is_numeric($value) && in_array((float)$value, $this->exemptAmounts, true);
    }
}
