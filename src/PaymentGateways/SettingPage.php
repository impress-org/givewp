<?php
namespace  Give\PaymentGateways;

/**
 * Interface SettingSection
 * @package Give\Views\Admin\Settings
 *
 * @since 2.8.0
 */
interface SettingPage {
	/**
	 * Get section id.
	 * @return string
	 *
	 * @since 2.8.0
	 */
	public function getId();

	/**
	 * Get section title.
	 * @return string
	 *
	 * @since 2.8.0
	 */
	public function getName();

	/**
	 * Get section settings.
	 * @return array
	 *
	 * @since 2.8.0
	 */
	public function getSettings();

	/**
	 * Boot functionality
	 *
	 * @since 2.8.0
	 */
	public function boot();
}
