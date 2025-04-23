<?php

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Actions\GenerateDonationFormViewRouteUrl;
use Give\DonationForms\Blocks\DonationFormBlock\Controllers\BlockRenderController;
use Give\Helpers\Frontend\ConfirmDonation;
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
    (new BlockRenderController())->render([
        'formId'           => $attributes['id'] ?? null,
        'blockId'          => $attributes['blockId'] ?? null,
        'openFormButton'   => esc_html($attributes['continueButtonTitle'] ?? __('Donate Now', 'give')),
        'formFormat'       => $attributes['displayStyle'] ?? 'onpage',
    ]);
} else {
    ob_start();

    if (!FormUtils::isLegacyForm($attributes['id'])) {
        $showIframeInModal = 'button' === $attributes['displayStyle'];
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
