<?php

declare(strict_types=1);

namespace Give\Framework\Validation\Rules;

use Closure;
use Give\Framework\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Framework\Validation\Contracts\ValidationRule;

class Numeric implements ValidationRule, ValidatesOnFrontEnd
{
    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public static function id(): string
    {
        return 'numeric';
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function serializeOption()
    {
        return null;
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        if (!is_numeric($value)) {
            $fail(sprintf(__('%s must be numeric', 'give'), '{field}'));
        }
    }
}
