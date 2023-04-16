<?php

namespace Give\Framework\Http\ConnectServer\Client\Exceptions;

use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\Primitives\RuntimeException;
use Give\Framework\Exceptions\Traits\Loggable;

/**
 * @since 2.25.0
 */
class RequestException extends RuntimeException implements LoggableException
{
    use Loggable;
}
