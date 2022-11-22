<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static DonationFormStatus PENDING()
 * @method static DonationFormStatus PUBLISHED()
 * @method static DonationFormStatus DRAFT()
 * @method bool isPending()
 * @method bool isPublished()
 * @method bool isDraft()
 */
class DonationFormStatus extends Enum
{
    const PENDING = 'pending';
    const PUBLISHED = 'publish';
    const DRAFT = 'draft';

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
            self::DRAFT => __( 'Draft', 'give' ),
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
