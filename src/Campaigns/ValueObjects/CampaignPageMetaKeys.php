<?php

namespace Give\Campaigns\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use Give\Framework\Support\ValueObjects\EnumInteractsWithQueryBuilder;

/**
 * @unreleased
 *
 * @method static CampaignPageMetaKeys CAMPAIGN_ID()
 */
class CampaignPageMetaKeys extends Enum
{
    use EnumInteractsWithQueryBuilder;

    public const CAMPAIGN_ID = 'give_campaign_id';
}
