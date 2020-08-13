<?php

namespace Give\Onboarding;

/**
 * @since 2.8.0
 */
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

	/**
	 * @return int Form ID
	 *
	 * @since 2.8.0
	 */
	public function getOrMake() {
		return $this->getDefaultFormID() ?: $this->makeAndPersist();
	}

	/**
	 * @return int Form ID
	 *
	 * @since 2.8.0
	 */
	public function getDefaultFormID() {
		$formID = $this->settingsRepository->get( 'form_id' );
		return $this->isFormAvailable( $formID ) ? $formID : 0;
	}

	/**
	 * @param int $formID
	 *
	 * @return bool
	 *
	 * @since 2.8.0
	 */
	protected function isFormAvailable( $formID ) {
		$status = get_post_status( $formID );
		return ! ( false === $status || 'trash' == $status );
	}

	/**
	 * @return int Form ID
	 *
	 * @since 2.8.0
	 */
	protected function makeAndPersist() {
		$formID = $this->defaultFormFactory->make();

		$this->settingsRepository->set( 'form_id', $formID );
		$this->settingsRepository->save();

		return $formID;
	}
}
