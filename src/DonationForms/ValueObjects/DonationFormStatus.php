<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 3.0.0
 *
 * @method static DonationFormStatus PUBLISHED()
 * @method static DonationFormStatus FUTURE()
 * @method static DonationFormStatus DRAFT()
 * @method static DonationFormStatus PENDING()
 * @method static DonationFormStatus TRASH()
 * @method static DonationFormStatus UPGRADED()
 * @method static DonationFormStatus PRIVATE()
 * @method bool isPublished()
 * @method bool isFuture()
 * @method bool isDraft()
 * @method bool isPending()
 * @method bool isTrash()
 * @method bool isUpgraded()
 * @method bool isPrivate()
 */
class DonationFormStatus extends Enum
{
    const PUBLISHED = 'publish';
    const FUTURE = 'future';
    const DRAFT = 'draft';
    const PENDING = 'pending';
    const TRASH = 'trash';
    const UPGRADED = 'upgraded';
    const PRIVATE = 'private';
}
