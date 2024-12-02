<?php

namespace Give\Campaigns\Actions;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\FormDesigns\ClassicFormDesign\ClassicFormDesign;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\DonationForms\ValueObjects\GoalType;
use Give\FormBuilder\Actions\GenerateDefaultDonationFormBlockCollection;

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
        $defaultCampaignForm = DonationForm::create([
            'title' => $campaign->title,
            'status' => DonationFormStatus::DRAFT(),
            'settings' => FormSettings::fromArray([
                'enableDonationGoal' => true,
                'goalAmount' => $campaign->goal,
                'goalType' => $campaign->goalType->getValue(),
                'designId' => ClassicFormDesign::id(),
            ]),
            'blocks' => (new GenerateDefaultDonationFormBlockCollection())(),
        ]);

        give(CampaignRepository::class)->addCampaignForm($campaign, $defaultCampaignForm->id, true);
    }
}
