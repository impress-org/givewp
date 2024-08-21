<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;

/**
 * @unreleased
 */
class CreateParentCampaignForDonationForm
{
    /**
     * @unreleased
     */
    public function __invoke(DonationForm &$form)
    {
        $campaign = Campaign::create([
            'title' => $form->title,
        ]);

//        $form->campaignId = $campaign->id;
//        $form->save();

        return $campaign;
    }
}
