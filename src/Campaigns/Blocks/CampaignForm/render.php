<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;

/**
 * @var array    $attributes
 * @var Campaign $campaign
 *
 * @unrelesed
 */

if (
    ! isset($attributes['campaignId']) ||
    ! ($campaign = give(CampaignRepository::class)->getById($attributes['campaignId']))
) {
    return;
}

echo (new BlockRenderController())->render([
    'formId'           => $attributes['id'] ?? null,
    'blockId'          => $attributes['blockId'] ?? null,
    'openFormButton'   => esc_html($attributes['continueButtonTitle'] ?? __('Donate Now', 'give')),
    'formFormat'       => $attributes['displayStyle'] ?? 'onpage',
]);

?>
