<?php

declare(strict_types=1);

namespace Give\Donations\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 2.25.0
 *
 * @method static DonationNoteType ADMIN()
 * @method static DonationNoteType DONOR()
 * @method bool isAdmin()
 * @method bool isDonor()
 */
class DonationNoteType extends Enum
{
    const ADMIN = 'admin';
    const DONOR = 'donor';
}
