<?php

use Give\Campaigns\Blocks\CampaignComments\Controller\BlockRenderController;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;

/**
 * @var array    $attributes
 * @var Campaign $campaign
 */

if (!isset($attributes['campaignId'])
    || !$campaign = give(CampaignRepository::class)->getById($attributes['campaignId'])
) {
    return;
}

$secondaryColor = esc_attr($campaign->secondaryColor ?? '#27ae60');

echo (new BlockRenderController())->render($attributes, $secondaryColor);
