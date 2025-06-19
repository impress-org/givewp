<?php

namespace Give\Donors\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use Give\Framework\Support\ValueObjects\EnumInteractsWithQueryBuilder;

/**
 * @since 4.4.0
 *
 * @method static DonorNoteMetaKeys TYPE()
 * @method bool isType()
 */
class DonorNoteMetaKeys extends Enum
{
    use EnumInteractsWithQueryBuilder;

    const TYPE = 'note_type';
}
