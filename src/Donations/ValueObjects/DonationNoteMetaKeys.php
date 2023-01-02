<?php

namespace Give\Donations\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use Give\Framework\Support\ValueObjects\EnumInteractsWithQueryBuilder;

/**
 * @unreleased
 *
 * @method static DonationMetaKeys NOTE_TYPE()
 * @method bool isNoteType()
 */
class DonationNoteMetaKeys extends Enum
{
    use EnumInteractsWithQueryBuilder;

    const TYPE = 'note_type';
}
