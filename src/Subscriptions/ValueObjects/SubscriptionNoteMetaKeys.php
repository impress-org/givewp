<?php

namespace Give\Subscriptions\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use Give\Framework\Support\ValueObjects\EnumInteractsWithQueryBuilder;

/**
 * @unreleased
 *
 * @method static SubscriptionNoteMetaKeys TYPE()
 * @method bool isType()
 */
class SubscriptionNoteMetaKeys extends Enum
{
    use EnumInteractsWithQueryBuilder;

    const TYPE = 'note_type';
}
