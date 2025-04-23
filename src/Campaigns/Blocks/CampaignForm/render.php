<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\Helpers\Frontend\ConfirmDonation;
use Give\Log\Log;
use Give\Views\IframeView;
use Give\Helpers\Form\Utils as FormUtils;

/**
 * @var array    $attributes
 * @var Campaign $campaign
 *
 * @unrelesed
 */

if (! isset($attributes['campaignId']) ||
    ! ($campaign = give(CampaignRepository::class)->getById($attributes['campaignId']))
) {
    return;
}

if (FormUtils::isV3Form($attributes['id'])) {
    echo (new BlockRenderController())->render([
        'formId'           => $attributes['id'] ?? 0,
        'blockId'          => $attributes['blockId'] ?? '',
        'openFormButton'   => esc_html($attributes['continueButtonTitle'] ?? __('Donate Now', 'give')),
        'formFormat'       => $attributes['displayStyle'] ?? 'onpage',
    ]);
} else {
    ob_start();

    if (!FormUtils::isLegacyForm($attributes['id'])) {
        $showIframeInModal = 'modal' === $attributes['displayStyle'];
        $iframeView        = new IframeView();

        ConfirmDonation::storePostedDataInDonationSession();

        echo $iframeView->setFormId($attributes['id'])
            ->showInModal($showIframeInModal)
            ->setButtonTitle($attributes['continueButtonTitle'])
            ->render();
    } else {
        echo give_get_donation_form($attributes);
    }
}
