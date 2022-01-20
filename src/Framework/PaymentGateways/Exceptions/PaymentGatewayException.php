<?php

namespace Give\Framework\PaymentGateways\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;

/**
 * @since 2.18.0
 */
class PaymentGatewayException extends Exception
{
    /**
     * @param  string  $message  - a human readable error message
     * @param  int  $code  [optional] The Exception code.
     * @param  null  $previous  [optional] The previous throwable used for the exception chaining.
     */
    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
