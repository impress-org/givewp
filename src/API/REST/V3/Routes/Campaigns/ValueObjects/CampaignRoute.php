<?php

namespace Give\API\REST\V3\Routes\Campaigns\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 4.0.0
 *
 * @method static CampaignRoute NAMESPACE()
 * @method static CampaignRoute CAMPAIGN()
 * @method static CampaignRoute CAMPAIGNS()
 * @method bool isNamespace()
 * @method bool isCampaign()
 * @method bool isCampaigns()
 */
class CampaignRoute extends Enum
{
    const NAMESPACE = 'givewp/v3';
    const CAMPAIGN = 'campaigns/(?P<id>[0-9]+)';
    const CAMPAIGNS = 'campaigns';
}
