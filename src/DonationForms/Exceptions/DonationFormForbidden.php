<?php

namespace Give\DonationForms\Exceptions;

use Exception;
use Throwable;

/**
 * @unreleased
 */
class DonationFormForbidden extends Exception
{
    public function __construct($message = 'Forbidden', $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
