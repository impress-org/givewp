<?php

namespace Give\Campaigns\Actions;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\DonationForms\ValueObjects\GoalType;

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
            'settings' => FormSettings::fromArray([
                'enableDonationGoal' => true,
                'goalAmount' => $campaign->goal,
                'goalType' => $campaign->goalType->getValue(),
                'designId' => 'classic',
            ]),
        ]);

        give(CampaignRepository::class)->addCampaignForm($campaign, $defaultCampaignForm->id, true);
    }
}
