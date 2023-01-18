<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Validation\Exceptions;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Vendors\StellarWP\Validation\Exceptions\Contracts\ValidationExceptionInterface;

class ValidationException extends Exception implements ValidationExceptionInterface
{

}
