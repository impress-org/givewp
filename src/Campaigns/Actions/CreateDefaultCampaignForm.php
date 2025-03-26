<?php

namespace Give\Campaigns\Actions;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\FormDesigns\ClassicFormDesign\ClassicFormDesign;
use Give\DonationForms\FormDesigns\MultiStepFormDesign\MultiStepFormDesign;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\FormBuilder\Actions\GenerateDefaultDonationFormBlockCollection;

/**
 * @since 4.0.0
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
            'status' => DonationFormStatus::PUBLISHED(),
            'settings' => FormSettings::fromArray([
                'showHeader' => false,
                'enableDonationGoal' => false,
                'goalAmount' => $campaign->goal,
                'goalType' => $campaign->goalType->getValue(),
                'designId' => MultiStepFormDesign::id(),
            ]),
            'blocks' => (new GenerateDefaultDonationFormBlockCollection())(),
        ]);

        give(CampaignRepository::class)->addCampaignForm($campaign, $defaultCampaignForm->id, true);
    }
}
