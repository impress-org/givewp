<?php

namespace Give\Onboarding;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\FormBuilder\Actions\GenerateDefaultDonationFormBlockCollection;
use Give\Log\Log;

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
     * @since 3.15.0 Create the default v3 form.
     * @since 2.8.0
     * @return int Form ID
     *
     */
    protected function makeAndPersist()
    {
        $form = new DonationForm([
            'title'    => __('GiveWP Donation Form', 'give'),
            'status'   => DonationFormStatus::PUBLISHED(),
            'settings' => FormSettings::fromArray([
                'designId' => 'multi-step',
                'designSettingsImageUrl' => GIVE_PLUGIN_URL . '/assets/dist/images/admin/onboarding/header-image.jpg',
                'designSettingsImageStyle' => 'above',
                'designSettingsImageAlt' => 'GiveWP Onboarding Donation Form',
            ]),
            'blocks'   => (new GenerateDefaultDonationFormBlockCollection())(),
        ]);

        $form->save();

        $this->settingsRepository->set('form_id', $form->id);
        $this->settingsRepository->save();

        return $form->id;
    }
}
