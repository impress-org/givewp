<?php

namespace Give\Onboarding;

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
     * @param SettingsRepository $settingsRepository
     *
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
     * @since 2.8.0
     * @return int Form ID
     *
     */
    protected function makeAndPersist()
    {
        $formID = $this->defaultFormFactory->make();

        $this->settingsRepository->set('form_id', $formID);
        $this->settingsRepository->save();

        return $formID;
    }
}
