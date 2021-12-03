<?php

namespace Give\Framework\Exceptions\Primitives;

use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\Traits\Loggable;

class RuntimeException extends \RuntimeException implements LoggableException
{
    use Loggable;
}
