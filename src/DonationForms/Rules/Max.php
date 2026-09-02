<?php
namespace Give\DonationForms\Rules;


use Closure;
use Give\DonationForms\Rules\Concerns\HasExemptAmounts;
use Give\Vendors\StellarWP\Validation\Config;
use Give\Vendors\StellarWP\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

use function is_numeric;

/**
 * @since 4.16.8 Implement the rule directly instead of extending the vendor rule, whose size is an integer.
 * @since 3.0.0
 */
class Max implements ValidationRule, ValidatesOnFrontEnd
{
    use HasExemptAmounts;

    /**
     * @var numeric
     */
    protected $size;

    /**
     * @since 4.16.8
     *
     * @param numeric $size
     */
    public function __construct($size)
    {
        if ($size <= 0) {
            Config::throwInvalidArgumentException('Max validation rule requires a non-negative value');
        }

        $this->size = $this->sanitize($size);
    }

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'max';
    }

    /**
     * @inheritDoc
     */
    public static function fromString(?string $options = null): ValidationRule
    {
        if (!is_numeric($options)) {
            Config::throwInvalidArgumentException('Max validation rule requires a numeric value');
        }

        return new self($options);
    }

    /**
     * @since 3.0.0
     */
    public function sanitize($value)
    {
        if (is_numeric($value)) {
            if (strpos((string)$value, '.') !== false) {
                return (float)$value;
            }

            return (int)$value;
        }

        return $value;
    }

    /**
     * @inheritDoc
     *
     * @since 4.16.8 Skip amounts the admin configured on the form, and report exceeding the maximum instead of
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

    /**
     * @since 3.0.0
     *
     * @return numeric
     */
    public function serializeOption()
    {
        return $this->size;
    }

    /**
     * @since 3.0.0
     *
     * @return numeric
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @since 3.0.0
     *
     * @param numeric $size
     *
     * @return void
     */
    public function size($size)
    {
        if ($size <= 0) {
            Config::throwInvalidArgumentException('Max validation rule requires a non-negative value');
        }

        $this->size = $this->sanitize($size);
    }
}
