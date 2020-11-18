<?php
namespace Give\Tracking;

/**
 * Class AdminActionHandler
 * @package Give\Tracking
 *
 * This class uses to handle actions in WP Backed.
 *
 * @since 2.10.0
 */
class AdminActionHandler {
	/**
	 * Boostrap
	 */
	public function boot() {
		add_action( 'give_opt_in_into_tracking', [ $this, 'optInToUsageTracking' ] );
		add_action( 'give_opt_out_into_tracking', [ $this, 'optOutFromUsageTracking' ] );
	}

	/**
	 * Handle opt_out_into_tracking give action.
	 *
	 * @since 2.10.0
	 */
	public function optOutFromUsageTracking() {
		if ( ! current_user_can( 'manage_give_setting' ) ) {
			return;
		}

		give_update_option( AdminSettings::USAGE_TRACKING_OPTION_NAME, 'disabled' );
	}

	/**
	 * Handle opt_in_into_tracking give action.
	 *
	 * @since 2.10.0
	 */
	public function optInToUsageTracking() {
		if ( ! current_user_can( 'manage_give_setting' ) ) {
			return;
		}

		give_update_option( AdminSettings::USAGE_TRACKING_OPTION_NAME, 'enabled' );
	}
}
