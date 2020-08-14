<?php

namespace Give\Onboarding;

/**
 * @since 2.8.0
 */
class SettingsRepository {

	/** @var array */
	protected $settings;

	/** @var callable */
	protected $persistCallback;

	/**
	 * @param array $settings
	 * @param callable $persistCallback
	 *
	 * @since 2.8.0
	 */
	public function __construct( array $settings, callable $persistCallback ) {
		$this->settings        = $settings;
		$this->persistCallback = $persistCallback;
	}

	/**
	 * @param string $name The setting name.
	 *
	 * @return mixed The setting value.
	 *
	 * @since 2.8.0
	 */
	public function get( $name ) {
		return ( $this->has( $name ) )
			? $this->settings[ $name ]
			: null;
	}

	/**
	 * @param string $name The setting name.
	 * @param mixed $value The setting value.
	 *
	 * @return void
	 *
	 * @since 2.8.0
	 */
	public function set( $name, $value ) {
		$this->settings[ $name ] = $value;
	}

	/**
	 * @param string $name The setting name.
	 *
	 * @return bool
	 *
	 * @since 2.8.0
	 */
	public function has( $name ) {
		return isset( $this->settings[ $name ] );
	}

	/**
	 * @return bool False if value was not updated and true if value was updated.
	 *
	 * @since 2.8.0
	 */
	public function save() {
		return $this->persistCallback->__invoke(
			$this->settings
		);
	}
}
