<?php

namespace Give\Donors\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 2.19.6
 *
 * @method static DonorType NEW()
 * @method static DonorType SUBSCRIBER()
 * @method static DonorType REPEAT()
 * @method static DonorType SINGLE()
 * @method bool isNew()
 * @method bool isSubscriber()
 * @method bool isRepeat()
 * @method bool isSingle()
 */
class DonorType extends Enum
{
    const NEW = 'new';
    const SUBSCRIBER = 'subscriber';
    const REPEAT = 'repeat';
    const SINGLE = 'single';

    /**
     * @return array
     */
    public static function labels(): array
    {
        return [
            self::NEW => __( 'No Donations', 'give' ),
            self::SUBSCRIBER => __( 'Subscriber', 'give' ),
            self::REPEAT => __( 'Repeat Donor', 'give' ),
            self::SINGLE => __( 'One-time Donor', 'give' ),
        ];
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return self::labels()[ $this->getValue() ];
    }
}
