<?php
namespace Give\DonationForms\Rules;


use Closure;
use Give\Vendors\StellarWP\Validation\Config;

use function is_numeric;

class Min extends \Give\Vendors\StellarWP\Validation\Rules\Min
{
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
     * @since 3.0.0
     **/
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        $value = $this->sanitize($value);

        if (is_numeric($value)) {
            if ($value < $this->getSize()) {
                $fail(sprintf(__('%s must be greater than or equal to %s', 'give'), '{field}', $this->getSize()));
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) < $this->getSize()) {
                $fail(sprintf(__('%s must be more than or equal to %d characters', 'give'), '{field}', $this->getSize()));
            }
        } else {
            Config::throwValidationException("Field value must be a number or string");
        }
    }
}