<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;

/**
 * @var array    $attributes
 * @var Campaign $campaign
 */

if (
    ! isset($attributes['campaignId'])
    || ! $campaign = give(CampaignRepository::class)->getById($attributes['campaignId'])
) {
    return;
}

?>

<div data-givewp-campaign-goal data-id="<?= $campaign->id; ?>"></div>
