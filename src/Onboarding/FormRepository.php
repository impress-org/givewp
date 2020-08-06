<?php

namespace Give\Onboarding;

class FormRepository {

	/** @var SettingsRepository */
	protected $settingsRepository;

	/** @var DefaultFormFactory */
	protected $defaultFormFactory;

	/**
	 * @param SettingsRepository $settingsRepository
	 *
	 * @since 2.8.0
	 */
	public function __construct( SettingsRepositoryFactory $settingsRepositoryFactory, DefaultFormFactory $defaultFormFactory ) {
		$this->settingsRepository = $settingsRepositoryFactory->make( 'give_onboarding' );
		$this->defaultFormFactory = $defaultFormFactory;
	}

	public function getOrMake() {
		return $this->getDefaultFormID() ?: $this->makeAndPersist();
	}

	protected function getDefaultFormID() {
		$formID = $this->settingsRepository->get( 'form_id' );
		return $this->isFormAvailable( $formID ) ? $formID : 0;
	}

	protected function isFormAvailable( $formID ) {
		$status = get_post_status( $formID );
		return ! ( false === $status || 'trash' == $status );
	}

	protected function makeAndPersist() {
		$formID = $this->defaultFormFactory->make();

		$this->settingsRepository->set( 'form_id', $formID );
		$this->settingsRepository->save();

		return $formID;
	}
}
