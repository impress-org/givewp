<?php

namespace Give\Framework\Http\Response\Types;

use Give\Framework\Http\Response\Traits\Macroable;
use Give\Framework\Http\Response\Traits\ResponseTrait;
use Symfony\Component\HttpFoundation\RedirectResponse as BaseRedirectResponse;


class RedirectResponse extends BaseRedirectResponse
{
    use ResponseTrait, Macroable {
        Macroable::__call as macroCall;
    }
}