<?php

namespace Give\Onboarding;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;

/**
 * @since 2.8.0
 */
class FormRepository
{

    /** @var SettingsRepository */
    protected $settingsRepository;

    /** @var DefaultFormFactory */
    protected $defaultFormFactory;

    /**
     * @since 2.8.0
     *
     * @param SettingsRepositoryFactory $settingsRepositoryFactory
     * @param DefaultFormFactory        $defaultFormFactory
     */
    public function __construct(
        SettingsRepositoryFactory $settingsRepositoryFactory,
        DefaultFormFactory $defaultFormFactory
    ) {
        $this->settingsRepository = $settingsRepositoryFactory->make('give_onboarding');
        $this->defaultFormFactory = $defaultFormFactory;
    }

    /**
     * @since 2.8.0
     * @return int Form ID
     *
     */
    public function getOrMake()
    {
        return $this->getDefaultFormID() ?: $this->makeAndPersist();
    }

    /**
     * @since 2.8.0
     * @return int Form ID
     *
     */
    public function getDefaultFormID()
    {
        $formID = $this->settingsRepository->get('form_id');

        return $this->isFormAvailable($formID) ? $formID : 0;
    }

    /**
     * @since 2.8.0
     *
     * @param int $formID
     *
     * @return bool
     *
     */
    protected function isFormAvailable($formID)
    {
        $status = get_post_status($formID);

        return ! (false === $status || 'trash' == $status);
    }

    /**
     * @unreleased Replace "Donation Form" with "Campaign Form"
     * @since 3.15.0 Create the default v3 form.
     * @since 2.8.0
     * @return int Form ID
     *
     * @throws Exception
     */
    protected function makeAndPersist(): int
    {
        $campaign = Campaign::create([
            'type' => CampaignType::CORE(),
            'title' => __('GiveWP Donation Form', 'give'),
            'shortDescription' => '',
            'longDescription' => '',
            'logo' => '',
            'image' => '',
            'primaryColor' => '#0b72d9',
            'secondaryColor' => '#27ae60',
            'goal' => 1000,
            'goalType' => CampaignGoalType::AMOUNT(),
            'status' => CampaignStatus::ACTIVE(),
        ]);

        $form = DonationForm::find($campaign->defaultFormId);

        $form->title = $campaign->title;
        $form->status = DonationFormStatus::PUBLISHED();
        $form->settings->designId = 'multi-step';
        $form->settings->designSettingsImageUrl = GIVE_PLUGIN_URL . '/assets/dist/images/admin/onboarding/header-image.jpg';
        $form->settings->designSettingsImageStyle = 'above';
        $form->settings->designSettingsImageAlt = $campaign->title;

        $form->save();

        $this->settingsRepository->set('form_id', $form->id);
        $this->settingsRepository->save();

        return $form->id;
    }
}
