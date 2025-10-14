<?php

namespace Give\Donors\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static DonorStatus ACTIVE()
 * @method static DonorStatus TRASH()
 * @method bool isActive()
 * @method bool isTrash()
 */
class DonorStatus extends Enum
{
    const ACTIVE = 'active';
    const TRASH = 'trash';

    /**
     * @return array
     */
    public static function labels(): array
    {
        return [
            self::ACTIVE => __('Active', 'give'),
            self::TRASH => __('Trash', 'give'),
        ];
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return self::labels()[$this->getValue()];
    }
}
