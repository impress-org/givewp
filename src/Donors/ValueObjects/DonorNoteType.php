<?php

declare(strict_types=1);

namespace Give\Donors\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 4.4.0
 *
 * @method static DonorNoteType ADMIN()
 * @method static DonorNoteType DONOR()
 * @method bool isAdmin()
 * @method bool isDonor()
 */
class DonorNoteType extends Enum
{
    const ADMIN = 'admin';
    const DONOR = 'donor';
}
