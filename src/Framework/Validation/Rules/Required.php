<?php

declare(strict_types=1);

namespace Give\Framework\Validation\Rules;

use Closure;
use Give\Framework\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Framework\Validation\Contracts\ValidationRule;

class Required implements ValidationRule, ValidatesOnFrontEnd
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'required';
    }

    /**
     * @inheritDoc
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }

    /**
     * @inheritDoc
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        if (!isset($values[$key]) || $value === null || $value === '') {
            $fail(__(sprintf('%s is required', '{field}'), 'give'));
        }
    }

    /**
     * @inheritDoc
     */
    public function serializeOption(): bool
    {
        return true;
    }
}
