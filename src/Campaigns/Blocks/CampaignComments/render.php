<?php

use Give\Campaigns\Blocks\CampaignComments\Controller\BlockRenderController;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @since TBD Escape output.
 *
 * @var array    $attributes
 * @var Campaign $campaign
 */

if (!isset($attributes['campaignId'])
    || !$campaign = give(CampaignRepository::class)->getById($attributes['campaignId'])
) {
    return;
}

echo (new BlockRenderController())->render($attributes, $campaign->secondaryColor ?? '#27ae60'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render() escapes every interpolated value with esc_attr() into a fixed <div> template; wp_kses_post() was a no-op here.
