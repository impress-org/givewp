<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;

if (!isset($attributes['campaignId'])) {
    return;
}

/** @var Campaign $campaign */
$campaign = give(CampaignRepository::class)->getById($attributes['campaignId']);

if (!$campaign) {
    return;
}

$campaignMediaSetting = $campaign->image;
$altText = $attributes['alt'] ?? 'campaign cover image';

?>

<figure>
        <img src="<?php echo esc_url($campaign->image); ?>"
             alt="<?php echo esc_attr($altText); ?>"
             style="width: 100%; height: 100%; border-radius: 8px;"
        />
</figure>
