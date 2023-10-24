<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 3.0.0
 *
 * @method static DonationFormErrorTypes VALIDATION()
 * @method static DonationFormErrorTypes GATEWAY()
 * @method static DonationFormErrorTypes UNKNOWN()
 * @method bool isValidation()
 * @method bool isGateway()
 * @method bool isUnknown()
 */
class DonationFormErrorTypes extends Enum
{
    const VALIDATION = 'validation_error';
    const GATEWAY = 'gateway_error';
    const UNKNOWN = 'unknown_error';
}
