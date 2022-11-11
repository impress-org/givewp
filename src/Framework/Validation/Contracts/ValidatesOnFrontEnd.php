<?php

declare(strict_types=1);

namespace Give\Framework\Validation\Contracts;

interface ValidatesOnFrontEnd
{
    /**
     * Serializes the rule option for use on the front-end.
     *
     * @unreleased
     *
     * @return int|float|string|bool|array|null
     */
    public function serializeOption();
}
