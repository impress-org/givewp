<?php

use Give\Campaigns\Blocks\CampaignComments\Controller\BlockRenderController;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @var array    $attributes
 * @var Campaign $campaign
 */

if (!isset($attributes['campaignId'])
    || !$campaign = give(CampaignRepository::class)->getById($attributes['campaignId'])
) {
    return;
}

echo (new BlockRenderController())->render($attributes, $campaign->secondaryColor ?? '#27ae60');
