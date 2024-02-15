<?php

namespace Give\EventTickets\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unlreased
 *
 * @method static ENABLED()
 * @method static DISABLED()
 * @method bool isEnabled()
 * @method bool isDisabled()
 */
class EventTicketTypeStatus extends Enum
{
    const ENABLED = 'enabled';
    const DISABLED = 'disabled';
}
