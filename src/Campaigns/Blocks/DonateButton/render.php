<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\DonationForms\V2\Models\DonationForm;

/**
 * @var array    $attributes
 * @var Campaign $campaign
 */

if (!isset($attributes['campaignId']) ||
    !($campaign = give(CampaignRepository::class)->getById($attributes['campaignId'])) ||
    !DonationForm::find($campaign->defaultFormId)->status->isPublished()
) {
    return;
}

$blockInlineStyles = sprintf(
    '--givewp-primary-color: %s;',
    esc_attr($campaign->primaryColor ?? '#0b72d9')
);

$params = [
    'formId' => ($attributes['useDefaultForm'] || ! isset($attributes['selectedForm']))
        ? $campaign->defaultFormId
        : $attributes['selectedForm'],
    'openFormButton' => $attributes['buttonText'],
    'formFormat' => 'modal',
];
?>

<div
    <?php
    echo wp_kses_data(get_block_wrapper_attributes(['class' => 'givewp-campaign-donate-button-block'])); ?>
    style="<?php
    echo esc_attr($blockInlineStyles); ?>">
    <?php
    echo (new BlockRenderController())->render($params); ?>
</div>
