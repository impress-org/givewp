<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;

if (!isset($attributes['campaignId'])) {
    return;
}

/** @var Campaign $campaign */
$campaign = give(CampaignRepository::class)->getById($attributes['campaignId']);

if (!$campaign || !$campaign->image) {
    return;
}

$campaignMediaSetting = $campaign->image;

$altText = $attributes['alt'] ?? __('Campaign cover image', 'give');
$alignment = isset($attributes['align']) ? 'align' . $attributes['align'] : '';
$borderRadius = "border-radius: 8px;";

// Only assign width and height if the alignment is NOT "full" or "wide"
if ($attributes['align'] !== 'full' && $attributes['align'] !== 'wide') {
    $widthStyle = isset($attributes['width']) ? "width: {$attributes['width']}px;" : '';
    $heightStyle = isset($attributes['height']) ? "max-height: {$attributes['height']}px;" : '';
} else {
    $widthStyle = 'width: 100%;';
    $heightStyle = 'height: auto;';
    $borderRadius = '';
}
?>

<div
    <?php echo wp_kses_data(get_block_wrapper_attributes()); ?>
>
    <figure class="givewp-campaign-cover-block__figure">
        <img
            src="<?php echo esc_url($campaign->image); ?>"
            alt="<?php echo esc_attr($altText); ?>"
            style="<?php echo trim(esc_attr($widthStyle) . esc_attr($heightStyle) . esc_attr($borderRadius)); ?>"
        />
    </figure>
</div>
