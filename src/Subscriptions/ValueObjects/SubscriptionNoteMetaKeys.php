<?php

namespace Give\Subscriptions\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use Give\Framework\Support\ValueObjects\EnumInteractsWithQueryBuilder;

/**
 * @since 4.8.0
 *
 * @method static SubscriptionNoteMetaKeys TYPE()
 * @method bool isType()
 */
class SubscriptionNoteMetaKeys extends Enum
{
    use EnumInteractsWithQueryBuilder;

    const TYPE = 'note_type';
}
