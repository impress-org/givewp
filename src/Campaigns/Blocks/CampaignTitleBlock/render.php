<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;

if (empty($attributes) || !isset($attributes['campaignId'])) {
    return;
}

/** @var Campaign $campaign */
$campaign = give(CampaignRepository::class)->getById($attributes['campaignId']);

if (! $campaign) {
    return;
}

$headingLevel = isset($attributes['headingLevel']) ? (int) $attributes['headingLevel'] : 1;
$headingTag = 'h' . min(6, max(1, $headingLevel));
?>

<<?php echo $headingTag; ?> <?php echo wp_kses_data(get_block_wrapper_attributes()); ?>>
<?php echo esc_html($campaign->title); ?>
</<?php echo $headingTag; ?>>
