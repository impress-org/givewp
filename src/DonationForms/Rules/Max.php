<?php
namespace Give\DonationForms\Rules;


use Closure;
use Give\DonationForms\Rules\Concerns\HasExemptAmounts;
use Give\Vendors\StellarWP\Validation\Config;

use function is_numeric;

class Max extends \Give\Vendors\StellarWP\Validation\Rules\Max
{
    use HasExemptAmounts;

    /**
     * @since 3.0.0
     */
    public function sanitize($value)
    {
        if (is_numeric($value)) {
            if (strpos($value, '.') !== false) {
                return (float)$value;
            }

            return (int)$value;
        }

        return $value;
    }

    /**
     * @inheritDoc
     *
     * @since TBD Skip amounts the admin configured on the form, and report exceeding the maximum instead of
     *            repeating the minimum wording.
     * @since 3.0.0
     **/
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        $value = $this->sanitize($value);

        if ($this->isExemptAmount($value)) {
            return;
        }

        if (is_numeric($value)) {
            if ($value > $this->getSize()) {
                $fail(sprintf(__('%s must be less than or equal to %s', 'give'), '{field}', $this->getSize()));
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) > $this->getSize()) {
                $fail(sprintf(__('%s must be less than or equal to %d characters', 'give'), '{field}', $this->getSize()));
            }
        } else {
            Config::throwValidationException("Field value must be a number or string");
        }
    }
}
