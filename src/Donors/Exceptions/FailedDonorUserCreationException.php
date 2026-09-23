<?php

declare(strict_types=1);

namespace Give\Donors\Exceptions;

use Give\Donors\Models\Donor;
use Give\Framework\Exceptions\Primitives\Exception;

/**
 * @since 3.2.0
 */
class FailedDonorUserCreationException extends Exception
{
    protected $donor;

    /**
     * @since 4.17.0 Declare the nullable parameter explicitly.
     */
    public function __construct( ?Donor $donor = null, $code = 0, $previous = null ) {
        parent::__construct('Failed creating a user for the donor.', $code, $previous);
        $this->donor = $donor;
    }
}
