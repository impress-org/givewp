<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;

/**
 * @since TBD Escape output.
 *
 * @var array    $attributes
 * @var Campaign $campaign
 */

if (
    ! isset($attributes['campaignId'])
    || ! $campaign = give(CampaignRepository::class)->getById($attributes['campaignId'])
) {
    return;
}

$blockInlineStyles = sprintf(
    '--givewp-primary-color: %s; --givewp-secondary-color: %s;',
    esc_attr($campaign->primaryColor ?? '#0b72d9'),
    esc_attr($campaign->secondaryColor ?? '#27ae60')
);

?>

<div <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() escapes its output. ?><?= get_block_wrapper_attributes(['style' => $blockInlineStyles]) ?>>
    <div data-givewp-campaign-goal data-id="<?= (int) $campaign->id ?>"></div>
</div>
