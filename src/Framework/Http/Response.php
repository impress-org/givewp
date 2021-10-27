<?php

namespace Give\Framework\Http;

use Give\Framework\Support\Facades\Facade;

/**
 * Class Response
 *
 * @unreleased
 *
 * @method static json(array $data, int $status_code = null, int $options = 0): void
 * @method static redirect(string $location, int $status = 302, string $x_redirect_by = 'WordPress'): void
 */
class Response extends Facade
{
    protected function getFacadeAccessor()
    {
        return ResponseFacade::class;
    }
}
