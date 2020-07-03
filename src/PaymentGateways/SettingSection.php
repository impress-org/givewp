<?php
namespace  Give\PaymentGateways;

/**
 * Interface SettingSection
 * @package Give\Views\Admin\Settings
 *
 * @since 2.8.0
 */
interface SettingSection {
	/**
	 * Get section id.
	 * @return string
	 *
	 * @since 2.8.0
	 */
	public function getSectionId();

	/**
	 * Get section title.
	 * @return string
	 *
	 * @since 2.8.0
	 */
	public function getSectionTitle();

	/**
	 * Get section settings.
	 * @return array
	 *
	 * @since 2.8.0
	 */
	public function getSettings();
}
