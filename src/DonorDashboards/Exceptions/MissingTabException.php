<?php

namespace Give\DonorDashboards\Exceptions;

use Exception;
use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\Traits\Loggable;

/**
 * @since 2.10.0
 */
class MissingTabException extends Exception implements LoggableException
{
    use Loggable;

    /**
     * MissingTabException constructor.
     *
     * @since 2.10.0
     *
     * @param                $tabId
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($tabId, $code = 0, Exception $previous = null)
    {
        $message = __('No tab exists with the ID: ', 'give') . $tabId;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Allows the exception to be cast to a string
     *
     * @since 2.10.0
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . ": [$this->code]: $this->message\n";
    }
}
