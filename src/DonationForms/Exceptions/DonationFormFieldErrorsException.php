<?php

namespace Give\DonationForms\Exceptions;

use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\Traits\Loggable;
use Throwable;
use WP_Error;

class DonationFormFieldErrorsException extends \Exception implements LoggableException
{
    use Loggable;

    /**
     * @var WP_Error
     */
    protected $error;

    /**
     * @since 3.0.0
     */
    public function __construct(WP_Error $error, Throwable $previous = null)
    {
        parent::__construct('Form field validation error', 0, $previous);
        $this->error = $error;
    }

    /**
     * @since 3.0.0
     */
    public function getError(): WP_Error
    {
        return $this->error;
    }
}
