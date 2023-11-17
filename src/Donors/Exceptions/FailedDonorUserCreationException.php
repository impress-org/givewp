<?php

declare(strict_types=1);

namespace Give\Donors\Exceptions;

use Give\Donors\Models\Donor;
use Give\Framework\Exceptions\Primitives\Exception;

class FailedDonorUserCreationException extends Exception
{
    protected $donor;

    public function __construct( Donor $donor = null, $code = 0, $previous = null ) {
        parent::__construct('Failed creating a user for the donor.', $code, $previous);
        $this->donor = $donor;
    }
}
