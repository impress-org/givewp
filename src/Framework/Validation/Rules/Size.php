<?php

declare(strict_types=1);

namespace Give\Framework\Validation\Rules;

use Closure;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Framework\Validation\Contracts\ValidationRule;
use Give\Framework\Validation\Exceptions\ValidationException;

/**
 * @unreleased
 */
class Size implements ValidationRule, ValidatesOnFrontEnd
{
    /**
     * @var int
     */
    private $size;

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public static function id(): string
    {
        return 'size';
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public static function fromString(string $options = null): ValidationRule
    {
        if (!is_numeric($options)) {
            throw new InvalidArgumentException('Size validation rule requires a numeric value');
        }

        return new self((int)$options);
    }

    /**
     * @unreleased
     */
    public function __construct(int $size)
    {
        if ($size <= 0) {
            throw new InvalidArgumentException('Size validation rule requires a non-negative value');
        }

        $this->size = $size;
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     *
     * @throws ValidationException
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        if (is_int($value) || is_float($value)) {
            if ($value != $this->size) {
                $fail(sprintf(__('%s must be exactly %d', 'give'), '{field}', $this->size));
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) !== $this->size) {
                $fail(sprintf(__('%s must be exactly %d characters', 'give'), '{field}', $this->size));
            }
        } else {
            throw new ValidationException("Field value must be a number or string");
        }
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function serializeOption(): int
    {
        return $this->size;
    }

    /**
     * @unreleased
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function size(int $size)
    {
        if ($size <= 0) {
            throw new InvalidArgumentException('Size validation rule requires a non-negative value');
        }

        $this->size = $size;
    }
}
