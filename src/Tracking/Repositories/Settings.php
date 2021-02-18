<?php

namespace Give\Tracking\Repositories;

/**
 * Class Settings
 * @package Give\Tracking\Repositories
 *
 * @since 2.10.0
 */
class Settings {
	/**
	 * Return "usage_tracking" give setting option key name.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	public function getUsageTrackingOptionKey() {
		return 'usage_tracking';
	}

	/**
	 * Return "give_hide_usage_tracking_notice" option key name which we use to show hide notice.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	public function getUsageTrackingNoticeNagOptionKey() {
		return 'give_telemetry_hide_usage_tracking_notice';
	}

	/**
	 * Return "usage_tracking" give setting option value.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	public function getUsageTrackingOptionValue() {
		return give_get_option( $this->getUsageTrackingOptionKey(), 'disabled' );
	}

	/**
	 * Get "give_telemetry_hide_usage_tracking_notice" option value.
	 *
	 * @since 2.10.0
	 *
	 * @return string
	 */
	public function getUsageTrackingNoticeNagOptionValue() {
		return get_option( $this->getUsageTrackingNoticeNagOptionKey(), null );
	}

	/**
	 * Store "usage_tracking" give setting option value.
	 *
	 * @since 2.10.0
	 *
	 * @param $optionValue
	 *
	 * @return boolean
	 */
	public function saveUsageTrackingOptionValue( $optionValue ) {
		return give_update_option( $this->getUsageTrackingOptionKey(), $optionValue );
	}

	/**
	 * Store "give_hide_usage_tracking_notice" option value.
	 *
	 * @since 2.10.0
	 *
	 * @param int $optionValue
	 *
	 * @return string
	 */
	public function saveUsageTrackingNoticeNagOptionValue( $optionValue ) {
		return update_option( $this->getUsageTrackingNoticeNagOptionKey(), $optionValue );
	}
}
