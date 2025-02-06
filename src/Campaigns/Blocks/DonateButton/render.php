<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;

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

$params = [
    'formId' => ($attributes['useDefaultForm'] || ! isset($attributes['selectedForm']))
        ? $campaign->defaultFormId
        : $attributes['selectedForm'],
    'openFormButton' => $attributes['buttonText'],
    'formFormat' => 'modal',
];

echo (new BlockRenderController())->render($params);
