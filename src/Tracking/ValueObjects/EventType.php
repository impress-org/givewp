<?php

namespace Give\Tracking\ValueObjects;

/**
 * Class EventType
 * @package Give\Tracking\ValueObjects
 *
 * @since 2.10.0
 */
class EventType {
	private $createToken           = 'create-token';
	private $pluginSettingsUpdated = 'plugin-settings-updated';
	private $themeSwitched         = 'theme-switched';
	private $themeUpdated          = 'theme-updated';
	private $pluginListUpdated     = 'plugin-list-updated';
	private $donationMetrics       = 'donation-metrics';
	private $donationFormUpdated   = 'donation-form-updated';
	private $siteUpdated           = 'site-updated';

	/**
	 * @return string
	 * @since 2.10.0
	 */
	public function getCreateToken() {
		return $this->createToken;
	}

	/**
	 * @return string
	 * @since 2.10.0
	 */
	public function getPluginSettingsUpdated() {
		return $this->pluginSettingsUpdated;
	}

	/**
	 * @return string
	 * @since 2.10.0
	 */
	public function getThemeSwitched() {
		return $this->themeSwitched;
	}

	/**
	 * @return string
	 * @since 2.10.0
	 */
	public function getThemeUpdated() {
		return $this->themeUpdated;
	}

	/**
	 * @return string
	 * @since 2.10.0
	 */
	public function getPluginListUpdated() {
		return $this->pluginListUpdated;
	}

	/**
	 * @return string
	 * @since 2.10.0
	 */
	public function getDonationMetrics() {
		return $this->donationMetrics;
	}

	/**
	 * @return string
	 * @since 2.10.0
	 */
	public function getDonationFormUpdated() {
		return $this->donationFormUpdated;
	}

	/**
	 * @return string
	 * @since 2.10.0
	 */
	public function getSiteUpdated() {
		return $this->donationFormUpdated;
	}

}
