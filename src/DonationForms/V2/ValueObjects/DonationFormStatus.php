<?php

namespace Give\DonationForms\V2\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 2.24.0
 *
 * @method static DonationFormStatus PENDING()
 * @method static DonationFormStatus PUBLISHED()
 * @method static DonationFormStatus PRIVATE ()
 * @method static DonationFormStatus DRAFT()
 * @method static DonationFormStatus TRASH()
 * @method static DonationFormStatus UPGRADED()
 * @method bool isPending()
 * @method bool isPublished()
 * @method bool isPrivate()
 * @method bool isDraft()
 * @method bool isTrash()
 * @method bool isUpgraded()
 */
class DonationFormStatus extends Enum
{
    const PENDING = 'pending';
    const PUBLISHED = 'publish';
    const PRIVATE = 'private';
    const DRAFT = 'draft';
    const TRASH = 'trash';
    const UPGRADED = 'upgraded';

    /**
     * @since 2.24.0
     *
     * @return array
     */
    public static function labels(): array
    {
        return [
            self::PENDING => __( 'Pending', 'give' ),
            self::PUBLISHED => __( 'Published', 'give' ),
            self::PRIVATE => __( 'Private', 'give' ),
            self::DRAFT => __( 'Draft', 'give' ),
            self::TRASH => __( 'Trash', 'give' ),
            self::UPGRADED => __( 'Upgraded', 'give' ),
        ];
    }

    /**
     * @since 2.24.0
     *
     * @return string
     */
    public function label(): string
    {
        return self::labels()[ $this->getValue() ];
    }
}
