<?php

namespace Give\NextGen\DonationForm\Exceptions;

use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\Traits\Loggable;
use WP_Error;

class DonationFormFieldErrorsException extends \Exception implements LoggableException
{
    use Loggable;
    /**
     * @var WP_Error
     */
    protected $error;

    /**
     * @unreleased
     */
    public function setError(WP_Error $error)
    {
        $this->error = $error;
    }


    /**
     * @unreleased
     */
    public function getError(): WP_Error
    {
        return $this->error;
    }
}
