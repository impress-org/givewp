<?php

use Give\Campaigns\Blocks\CampaignComments\Controller\BlockRenderController;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;

$attributes = $attributes ?? [];

if (!isset($attributes['campaignId'])) {
    return;
}

/** @var Campaign $campaign */
$campaign = give(CampaignRepository::class)->getById($attributes['campaignId']);

if (!$campaign) {
    return;
}

echo (new BlockRenderController())->render($attributes);
?>
