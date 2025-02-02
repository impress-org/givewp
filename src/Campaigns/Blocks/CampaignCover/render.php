<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;

if ( ! isset($attributes['campaignId'])) {
    return;
}

/** @var Campaign $campaign */
$campaign = give(CampaignRepository::class)->getById($attributes['campaignId']);

if ( ! $campaign) {
    return;
}

$campaignMediaSetting = $campaign->image;

$altText = $attributes['alt'] ?? __('Campaign cover image', 'give');
$alignment = isset($attributes['align']) ? 'align' . $attributes['align'] : '';

// Only assign width and height if the alignment is NOT "full" or "wide"
if ($attributes['align'] !== 'full' && $attributes['align'] !== 'wide') {
    $width = isset($attributes['width']) ? $attributes['width'] : '100%';
    $height = isset($attributes['height']) ? $attributes['height'] : '100%';
} else {
    $width = 'auto';
    $height = 'auto';
}
?>


<figure class="wp-block-givewp-campaign-cover-block <?php echo esc_attr($alignment) ?>">
    <img
        src="<?php echo esc_url($campaign->image); ?>"
        alt="<?php echo esc_attr($altText); ?>"
        style="
            width:<?php echo $width ?>px;
            height: <?php echo $height ?>px;
            border-radius: 8px;"
    />
</figure>
