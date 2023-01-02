<?php

declare(strict_types=1);

namespace Give\Donations\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static DonationType ADMIN()
 * @method static DonationType DONOR()
 * @method bool isAdmin()
 * @method bool isDonor()
 */
class DonationNoteType extends Enum
{
    const ADMIN = 'admin';
    const DONOR = 'donor';
}
