<?php
namespace Give\Tracking;

/**
 * Class OnBoarding
 * @package Give\Tracking
 *
 * This class uses to setup notice nag to website administrator if admin is not opt in for usage tracking and gives admin an option to directly opt-in.
 *
 * @since 2.10.0
 */
class UsageTrackingOnBoarding {
	const ANONYMOUS_USAGE_TRACING_NOTICE_ID = 'usage-tracking-nag';

	/**
	 * Bootstrap
	 *
	 * @since 2.10.0
	 */
	public function boot() {
		add_action( 'admin_notices', [ $this, 'addNotice' ] );
	}

	/**
	 * Register notice.
	 *
	 * @sicne 2.10.0
	 */
	public function addNotice() {
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			return;
		}

		$notice = $this->getNotice();

		$isAdminOptedIn = give_is_setting_enabled( give_get_option( AdminSettings::USAGE_TRACKING_OPTION_NAME, 'disabled' ) );
		if ( $isAdminOptedIn || give()->notices->is_notice_dismissed( $notice ) ) {
			return;
		}

		give()->notices->register_notice( $notice );
	}

	/**
	 * Get option name of notice.
	 *
	 * We use this option key to disable notice nag for specific user for a interval.
	 *
	 * @since 2.10.0
	 * @return string
	 */
	public function getNoticeOptionKey() {
		return give()->notices->get_notice_key( self::ANONYMOUS_USAGE_TRACING_NOTICE_ID, 'permanent' );
	}

	/**
	 * Get notice.
	 *
	 * @since 2.10.0
	 *
	 * @return string[]
	 */
	private function getNotice() {
		$notice       = esc_html__( 'You can contribute to improve GiveWP. If you opt-in to "Usage Tracking" then we will track non-sensitive data of your website. We will use this information to improve plugin.', 'give' );
		$readMoreLink = sprintf(
			'<a href="#" target="_blank">%1$s</a>',
			esc_html__( 'Read more GiveWP.com Usage Tracking.', 'give' )
		);

		$allowTrackingLink = sprintf(
			'<br><br><a href="%3$s" class="button-secondary">%1$s</a>&nbsp;&nbsp;<a href="%4$s" class="button-secondary">%2$s</a>',
			esc_html__( 'Yes', 'give' ),
			esc_html__( 'No', 'give' ),
			add_query_arg( [ 'give_action' => 'opt_in_into_tracking' ] ),
			add_query_arg( [ 'give_action' => 'opt_out_into_tracking' ] )
		);

			return [
				'id'               => self::ANONYMOUS_USAGE_TRACING_NOTICE_ID,
				'type'             => 'info',
				'description'      => "{$notice} {$readMoreLink} {$allowTrackingLink}",
				'dismissible_type' => 'all',
				'dismiss_interval' => 'permanent',
			];
	}
}
