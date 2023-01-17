<?php

declare(strict_types=1);

namespace Give\Framework\ValidationRules\Rules;

use Closure;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Exceptions\Primitives\RuntimeException;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

/**
 * DO NOT USE:
 *
 * In its current state this validation rule is incomplete. We need to first build a system for passing a file through
 * to the validation system, the MIME type of which is easily detectable.
 *
 * In short, do not use this validation rule yet. It was made purely to support Form Field Manager, which currently
 * handles the validation.
 *
 * @since 2.24.0
 */
class AllowedTypes implements ValidationRule
{
    /**
     * @var string[] $allowedMimeTypes List of allowed MIME types
     */
    private $allowedMimeTypes;

    /**
     * @inheritDoc
     *
     * @since 2.24.0
     */
    public static function id(): string
    {
        return 'allowedTypes';
    }

    /**
     * @inheritDoc
     *
     * @since 2.24.0
     */
    public static function fromString(string $options = null): ValidationRule
    {
        $types = explode(',', $options);

        if (!$types) {
            throw new InvalidArgumentException(
                'AllowedTypes validation rule requires a comma separated list of MIME types'
            );
        }

        return new self($types);
    }

    /**
     * @since 2.24.0
     *
     * @param string[] $mimeTypes
     */
    public function __construct(array $mimeTypes)
    {
        foreach ($mimeTypes as $mimeType) {
            self::validateMimeType($mimeType);
        }

        $this->allowedMimeTypes = $mimeTypes;
    }

    /**
     * @inheritDoc
     *
     * @since 2.24.0
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        throw new RuntimeException('Do not use this validation rule yet. See class docblock for more information.');
    }

    /**
     * Overrides the allowed MIME types.
     *
     * @since 2.24.0
     *
     * @param string[] $allowedMimeTypes
     */
    public function setAllowedtypes(array $allowedMimeTypes): self
    {
        $this->allowedMimeTypes = $allowedMimeTypes;

        return $this;
    }

    /**
     * Returns the allowed MIME types.
     *
     * @since 2.24.0
     *
     * @return string[]
     */
    public function getAllowedTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    /**
     * A simple validator for MIME types. This is not a full MIME type validator, but it is sufficient for our purposes.
     *
     * @since 2.24.0
     *
     * @return void
     */
    private static function validateMimeType(string $type)
    {
        if (count(explode('/', $type)) !== 2) {
            throw new InvalidArgumentException('MIME type must be in the format of \'type/subtype\'');
        }
    }
}
