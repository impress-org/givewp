<?php
namespace Give\DonationForms\Rules;


use Closure;
use Give\Vendors\StellarWP\Validation\Config;
use Give\Vendors\StellarWP\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

use function is_numeric;

class Size implements ValidationRule, ValidatesOnFrontEnd
{
    /**
     * @var numeric
     */
    protected $size;

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'size';
    }

    /**
     * @since 3.0.0
     */
    public function __construct($size)
    {
        if ($size <= 0) {
            Config::throwInvalidArgumentException('Size validation rule requires a non-negative value');
        }

        $this->size = $this->sanitize($size);
    }

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
     * @since 3.0.0
     */
    public static function fromString(string $options = null): ValidationRule
    {
        if (!is_numeric($options)) {
            Config::throwInvalidArgumentException('Size validation rule requires a numeric value');
        }

        return new self($options);
    }

    /**
     * @since 3.0.0
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        $value = $this->sanitize($value);
        
        if (is_numeric($value)) {
            if ($value !== $this->getSize()) {
                $fail(sprintf(__('%s must be exactly %s', 'give'), '{field}', $this->getSize()));
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) !== $this->getSize()) {
                $fail(sprintf(__('%s must be exactly %d characters', 'give'), '{field}', $this->getSize()));
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
     * @param  numeric  $size
     *
     * @return void
     */
    public function size($size)
    {
        if ($size <= 0) {
            Config::throwInvalidArgumentException('Size validation rule requires a non-negative value');
        }

        $this->size = $size;
    }
}