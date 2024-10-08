<?php

namespace Give\Campaigns\Actions;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;

/**
 * @unreleased
 */
class CreateDefaultCampaignForm
{
    /**
     * @throws Exception
     */
    public function __invoke(Campaign $campaign)
    {
        $defaultCampaignForm = DonationForm::factory()->create([
            'title' => $campaign->title,
            'status' => DonationFormStatus::DRAFT(),
        ]);

        give(CampaignRepository::class)->addCampaignForm($campaign, $defaultCampaignForm, true);
    }
}
