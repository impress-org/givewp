<?php

namespace Give\Onboarding;

class SettingsRepository {

	/** @var array */
	protected $settings;

	/** @var callable */
	protected $persistCallback;

	/**
	 * @param array $settings
	 * @param callable $persistCallback
	 */
	public function __construct( array $settings, callable $persistCallback ) {
		$this->settings        = $settings;
		$this->persistCallback = $persistCallback;
	}

	/**
	 * @param string $name The setting name.
	 *
	 * @return mixed The setting value.
	 */
	public function get( $name ) {
		return $this->settings[ $name ];
	}

	/**
	 * @param string $name The setting name.
	 * @param mixed $value The setting value.
	 */
	public function set( $name, $value ) {
		$this->settings[ $name ] = $value;
	}

	/**
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public function save() {
		return $this->persistCallback->__invoke(
			$this->settings
		);
	}
}
