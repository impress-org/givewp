<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static DonationFormStatus PENDING()
 * @method static DonationFormStatus PUBLISHED()
 * @method static DonationFormStatus PRIVATE()
 * @method static DonationFormStatus DRAFT()
 * @method static DonationFormStatus TRASH()
 * @method bool isPending()
 * @method bool isPublished()
 * @method bool isPrivate()
 * @method bool isDraft()
 * @method bool isTrashed()
 */
class DonationFormStatus extends Enum
{
    const PENDING = 'pending';
    const PUBLISHED = 'publish';
    const PRIVATE = 'private';
    const DRAFT = 'draft';
    const TRASH = 'trash';

    /**
     * @unreleased
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
        ];
    }

    /**
     * @unreleased
     *
     * @return string
     */
    public function label(): string
    {
        return self::labels()[ $this->getValue() ];
    }
}
