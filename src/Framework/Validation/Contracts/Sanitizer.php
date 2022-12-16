<?php

declare(strict_types=1);

namespace Give\Framework\Validation\Contracts;

/**
 * Intended to be used as part of a Validation Rule to sanitize data after it is validated.
 *
 * @unreleased
 */
interface Sanitizer
{
    /**
     * @unreleased
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function sanitize($value);
}
