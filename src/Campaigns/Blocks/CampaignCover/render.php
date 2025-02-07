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
    $widthStyle = isset($attributes['width']) ? "width: {$attributes['width']}px;" : '';
    $heightStyle = isset($attributes['height']) ? "max-height: {$attributes['height']}px;" : '';
} else {
    $widthStyle = 'width: auto;';
    $heightStyle = 'height: auto;';
}
?>


<figure class="wp-block-givewp-campaign-cover-block <?php echo esc_attr($alignment) ?>">
    <img
        src="<?php echo esc_url($campaign->image); ?>"
        alt="<?php echo esc_attr($altText); ?>"
        style="
            <?php echo $widthStyle ?>
            <?php echo $heightStyle; ?>
            border-radius: 8px;"
    />
</figure>
