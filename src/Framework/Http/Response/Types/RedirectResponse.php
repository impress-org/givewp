<?php

namespace Give\Framework\Http\Response\Types;

use Give\Framework\Http\Response\Traits\ResponseTrait;
use Symfony\Component\HttpFoundation\RedirectResponse as BaseRedirectResponse;

/**
 * @since 2.18.0
 */
class RedirectResponse extends BaseRedirectResponse
{
    use ResponseTrait;
}
