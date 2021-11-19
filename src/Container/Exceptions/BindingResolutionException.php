<?php

namespace Give\Container\Exceptions;

use Exception;
use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\Traits\Loggable;

class BindingResolutionException extends Exception implements LoggableException
{
    use Loggable;
}
