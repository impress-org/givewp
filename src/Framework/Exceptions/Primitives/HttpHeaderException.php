<?php

namespace Give\Framework\Exceptions\Primitives;

use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\Traits\Loggable;

/**
 * @since 4.3.2 Extends \Exception instead of \HttpHeaderException
 */
class HttpHeaderException extends \Exception implements LoggableException
{
    use Loggable;
}
