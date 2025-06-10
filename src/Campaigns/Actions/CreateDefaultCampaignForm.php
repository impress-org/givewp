<?php

namespace Give\Campaigns\Actions;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\FormDesigns\MultiStepFormDesign\MultiStepFormDesign;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\DonationForms\ValueObjects\GoalSource;
use Give\FormBuilder\Actions\GenerateDefaultDonationFormBlockCollection;

/**
 * @since 4.0.0
 */
class CreateDefaultCampaignForm
{
    /**
     * @since 4.2.0 return if campaign already has default form set
     * @since 4.1.0 Added inheritCampaignColors property to FormSettings
     * @since      4.0.0
     *
     * @throws Exception
     */
    public function __invoke(Campaign $campaign)
    {
        if ($campaign->defaultFormId) {
            return;
        }

        $defaultCampaignForm = DonationForm::create([
            'title' => $campaign->title,
            'status' => DonationFormStatus::PUBLISHED(),
            'settings' => FormSettings::fromArray([
                'showHeader' => false,
                'enableDonationGoal' => false,
                'goalAmount' => $campaign->goal,
                'goalType' => $campaign->goalType->getValue(),
                'goalSource' => GoalSource::CAMPAIGN()->getValue(),
                'designId' => MultiStepFormDesign::id(),
                'inheritCampaignColors' => true,
            ]),
            'blocks' => (new GenerateDefaultDonationFormBlockCollection())(),
        ]);

        give(CampaignRepository::class)->addCampaignForm($campaign, $defaultCampaignForm->id, true);
    }
}
